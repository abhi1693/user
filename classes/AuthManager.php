<?php namespace Frontend\User\Classes;

use October\Rain\Auth\Manager as FAuthManager;
use Frontend\User\Models\Settings as UserSettings;
use Frontend\User\Models\UserGroup as UserGroupModel;
use October\Rain\Auth\AuthException;

class AuthManager extends FAuthManager
{
    protected static $instance;

    protected $sessionKey = 'user_auth';

    protected $userModel = 'Frontend\User\Models\User';

    protected $groupModel = 'Frontend\User\Models\UserGroup';

    protected $throttleModel = 'Frontend\User\Models\Throttle';

    public function init()
    {
        $this->useThrottle = UserSettings::get('use_throttle', $this->useThrottle);
        $this->requireActivation = UserSettings::get('require_activation', $this->requireActivation);
        parent::init();
    }

    /**
     * {@inheritDoc}
     */
    public function extendUserQuery($query)
    {
        $query->withTrashed();
    }

    /**
     * {@inheritDoc}
     */
    public function login($user, $remember = true)
    {
        if ($user->is_guest) {
            $login = $user->getLogin();
            throw new AuthException(sprintf(
                'Cannot login user "%s" as they are not registered.', $login
            ));
        }

        parent::login($user, $remember);
    }

    /**
     * {@inheritDoc}
     */
    public function register(array $credentials, $activate = false)
    {
        if ($guest = $this->findGuestUserByCredentials($credentials)) {
            return $this->convertGuestToUser($guest, $credentials, $activate);
        }

        return parent::register($credentials, $activate);
    }

    //
    // Guest users
    //

    public function findGuestUserByCredentials(array $credentials)
    {
        if ($email = array_get($credentials, 'email')) {
            return $this->findGuestUser($email);
        }

        return null;
    }

    public function findGuestUser($email)
    {
        $query = $this->createUserModelQuery();

        return $user = $query
            ->where('email', $email)
            ->where('is_guest', 1)
            ->first();
    }

    /**
     * Registers a guest user by giving the required credentials.
     *
     * @param array $credentials
     * @return Models\User
     */
    public function registerGuest(array $credentials)
    {
        $user = $this->findGuestUserByCredentials($credentials);
        $newUser = false;

        if (!$user) {
            $user = $this->createUserModel();
            $newUser = true;
        }

        $user->fill($credentials);
        $user->is_guest = true;
        $user->save();

        // Add user to guest group
        if ($newUser && $group = UserGroupModel::getGuestGroup()) {
            $user->groups()->add($group);
        }

        // Prevents revalidation of the password field
        // on subsequent saves to this model object
        $user->password = null;

        return $this->user = $user;
    }

    /**
     * Converts a guest user to a registered user.
     *
     * @param Models\User $user
     * @param array $credentials
     * @param bool $activate
     * @return Models\User
     */
    public function convertGuestToUser($user, $credentials, $activate = false)
    {
        $user->fill($credentials);
        $user->convertToRegistered(false);

        // Remove user from guest group
        if ($group = UserGroupModel::getGuestGroup()) {
            $user->groups()->remove($group);
        }

        if ($activate) {
            $user->attemptActivation($user->getActivationCode());
        }

        // Prevents revalidation of the password field
        // on subsequent saves to this model object
        $user->password = null;

        return $this->user = $user;
    }
}
