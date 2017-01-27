<?php namespace Frontend\User;

use App;
use Event;
use Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Illuminate\Foundation\AliasLoader;
use Frontend\User\Models\MailBlocker;

class Plugin extends PluginBase
{
    /**
     * @var boolean Determine if this plugin should have elevated privileges.
     */
    public $elevated = true;

    public function pluginDetails()
    {
        return [
            'name'        => 'User',
            'description' => 'Front-end user management',
            'author'      => 'Abhimanyu',
            'icon'        => 'icon-user',
            'homepage'    => 'https://github.com/abhi1693/user'
        ];
    }

    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Auth', 'Abhimanyu\User\Facades\Auth');

        App::singleton('user.auth', function () {
            return \Abhimanyu\User\Classes\AuthManager::instance();
        });

        /*
         * Apply user-based mail blocking
         */
        Event::listen('mailer.prepareSend', function ($mailer, $view, $message) {
            return MailBlocker::filterMessage($view, $message);
        });
    }

    public function registerComponents()
    {
        return [
            'Abhimanyu\User\Components\Session'       => 'session',
            'Abhimanyu\User\Components\Account'       => 'account',
            'Abhimanyu\User\Components\ResetPassword' => 'resetPassword'
        ];
    }

    public function registerPermissions()
    {
        return [
            'abhimanyu.users.access_users'    => ['tab' => 'User', 'label' => 'Access Users'],
            'abhimanyu.users.access_groups'   => ['tab' => 'User', 'label' => 'Access Groups'],
            'abhimanyu.users.access_settings' => ['tab' => 'User', 'label' => 'Access Settings']
        ];
    }

    public function registerNavigation()
    {
        return [
            'user' => [
                'label'       => 'Users',
                'url'         => Backend::url('frontend/user/users'),
                'icon'        => 'icon-user',
                'iconSvg'     => 'plugins/abhimanyu/user/assets/images/user-icon.svg',
                'permissions' => ['abhimanyu.users.*'],
                'order'       => 500,
            ]
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'Users',
                'description' => 'Manage users settings',
                'category'    => SettingsManager::CATEGORY_USERS,
                'icon'        => 'icon-cog',
                'class'       => 'Abhimanyu\User\Models\Settings',
                'order'       => 500,
                'permissions' => ['abhimanyu.users.access_settings'],
            ]
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'abhimanyu.user::mail.activate'   => 'Activation email sent to new users.',
            'abhimanyu.user::mail.welcome'    => 'Welcome email sent when a user is activated.',
            'abhimanyu.user::mail.restore'    => 'Password reset instructions for front-end users.',
            'abhimanyu.user::mail.new_user'   => 'Sent to administrators when a new user joins.',
            'abhimanyu.user::mail.reactivate' => 'Notification for users who reactivate their account.',
        ];
    }
}
