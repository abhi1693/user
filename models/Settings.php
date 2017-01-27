<?php namespace Frontend\User\Models;

use Lang;
use Model;
use System\Models\MailTemplate;
use Frontend\User\Models\User as UserModel;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'user_settings';
    public $settingsFields = 'fields.yaml';

    const ACTIVATE_AUTO = 'auto';
    const ACTIVATE_USER = 'user';
    const ACTIVATE_ADMIN = 'admin';

    const LOGIN_EMAIL = 'email';
    const LOGIN_USERNAME = 'username';

    public function initSettingsData()
    {
        $this->require_activation = true;
        $this->activate_mode = self::ACTIVATE_AUTO;
        $this->use_throttle = true;
        $this->block_persistence = false;
        $this->allow_registration = true;
        $this->welcome_template = 'frontend.user::mail.welcome';
        $this->login_attribute = self::LOGIN_EMAIL;
    }

    public function getActivateModeOptions()
    {
        return [
            self::ACTIVATE_AUTO => ['Automatic', 'Activated automatically upon registration'],
            self::ACTIVATE_USER => ['User', 'The user activates their own account using mail'],
            self::ACTIVATE_ADMIN => ['Administrator', 'Only an Administrator can activate a user'],
        ];
    }

    public function getLoginAttributeOptions()
    {
        return [
            self::LOGIN_EMAIL => ['Email'],
            self::LOGIN_USERNAME => ['Username'],
        ];
    }

    public function getActivateModeAttribute($value)
    {
        if (!$value) {
            return self::ACTIVATE_AUTO;
        }

        return $value;
    }

    public function getWelcomeTemplateOptions()
    {
        $codes = array_keys(MailTemplate::listAllTemplates());
        $result = [''=>'- Do not send a notification -'];
        $result += array_combine($codes, $codes);
        return $result;
    }
}
