<?php namespace Frontend\User;

use App;
use Event;
use Backend;
use System\Classes\PluginBase;
use System\Classes\SettingsManager;
use Illuminate\Foundation\AliasLoader;
use Frontend\User\Models\MailBlocker;
use Frontend\User\Models\User;
use Backend\Widgets\Form;
use Frontend\User\Classes\ProviderManager;

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

    public function boot() {
      User::extend(function($model){
        $model->hasMany['sociallogin_providers'] = ['Frontend\User\Models\Provider'];
      });

      Event::listen('backend.form.extendFields', function(Form $form){
        if(!$form->getController() instanceof \System\Controllers\Settings) return;
        if(!$form->model instanceof \Frontend\User\Models\Social) return;

        foreach ( ProviderManager::instance()->listProviders() as $class => $details ) {
          $classObj = $class::instance();
          $classObj->extendSettingsForm($form);
        }
      });

      Event::listen('backend.form.extendFields', function($widget){
        if(!$widget->getController() instanceof \Frontend\User\Controllers\User) return;
        if(!$widget->getContext() != 'update') return;

        $widget->addFields([
          'sociallogin_providers' => [
            'label' => 'Social Providers',
            'type' => 'Frontend\User\FormWidgets\LoginProviders'
          ]
        ], 'secondary');
      });
    }

    public function register()
    {
        $alias = AliasLoader::getInstance();
        $alias->alias('Auth', 'Frontend\User\Facades\Auth');

        App::singleton('user.auth', function () {
            return \Frontend\User\Classes\AuthManager::instance();
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
            'Frontend\User\Components\Session'       => 'session',
            'Frontend\User\Components\Account'       => 'account',
            'Frontend\User\Components\ResetPassword' => 'resetPassword',
            'Frontend\User\Components\SocialLogin'   => 'sociallogin'
        ];
    }

    public function registerPermissions()
    {
        return [
            'frontend.users.access_users'       => ['tab' => 'User', 'label' => 'Access Users'],
            'frontend.users.access_groups'      => ['tab' => 'User', 'label' => 'Access Groups'],
            'frontend.users.access_settings'    => ['tab' => 'User', 'label' => 'Access Settings'],
            'frontend.users.access_sociallogin' => ['tab' => 'User', 'label' => 'Access Social Login']
        ];
    }

    public function registerNavigation()
    {
        return [
            'user' => [
                'label'       => 'Users',
                'url'         => Backend::url('frontend/user/users'),
                'icon'        => 'icon-user',
                'iconSvg'     => 'plugins/frontend/user/assets/images/user-icon.svg',
                'permissions' => ['frontend.users.*'],
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
                'class'       => 'Frontend\User\Models\Settings',
                'order'       => 500,
                'permissions' => ['frontend.users.access_settings'],
            ],
            'social' => [
              'label'       => 'Social Login',
				      'description' => 'Manage Social Login providers.',
              'category'    => SettingsManager::CATEGORY_USERS,
              'icon'        => 'icon-users',
              'class'       => 'Frontend\User\Models\Social',
              'order'       => 600,
              'permissions' => ['frontend.users.access_sociallogin'],
            ]
        ];
    }

    public function registerMailTemplates()
    {
        return [
            'frontend.user::mail.activate'   => 'Activation email sent to new users.',
            'frontend.user::mail.welcome'    => 'Welcome email sent when a user is activated.',
            'frontend.user::mail.restore'    => 'Password reset instructions for front-end users.',
            'frontend.user::mail.new_user'   => 'Sent to administrators when a new user joins.',
            'frontend.user::mail.reactivate' => 'Notification for users who reactivate their account.',
        ];
    }

    function register_sociallogin_providers() {
      return [
        '\\Frontend\\User\\SocialLoginProviders\\Google' => [
          'label' => 'Google',
          'alias' => 'Google',
          'description' => 'Sign in with Google'
        ],

        '\\Frontend\\User\\SocialLoginProviders\\Facebook' => [
          'label' => 'Facebook',
          'alias' => 'Facebook',
          'description' => 'Sign in with Facebook'
        ],

        '\\Frontend\\User\\SocialLoginProviders\\Twitter' => [
          'label' => 'Twitter',
          'alias' => 'Twitter',
          'description' => 'Sign in with Twitter'
        ],
      ];
    }
}
