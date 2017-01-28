<?php namespace Frontend\User\SocialLoginProviders;

use Backend\Widgets\Form;
use Frontend\User\SocialLoginProviders\SocialLoginProviderBase;
use Frontend\User\Models\Social;
use Hybrid_Endpoint;
use Hybrid_Auth;
use URL;

class Facebook extends SocialLoginProviderBase
{
	use \October\Rain\Support\Traits\Singleton;

	/**
	 * Initialize the singleton free from constructor parameters.
	 */
	protected function init()
	{
		parent::init();
	}

	public function isEnabled()
	{
		$providers = $this->settings->get('providers', []);

		return !empty($providers['Facebook']['enabled']);
	}

	public function extendSettingsForm(Form $form)
	{
		$form->addFields([
			'noop' => [
				'type' => 'partial',
				'path' => '$/frontend/user/partials/backend/forms/settings/_facebook_info.htm',
				'tab' => 'Facebook',
			],

			'providers[Facebook][enabled]' => [
				'label' => 'Enabled?',
				'type' => 'checkbox',
				'default' => 'true',
				'tab' => 'Facebook',
			],

			'providers[Facebook][app_id]' => [
				'label' => 'App ID',
				'type' => 'text',
				'tab' => 'Facebook',
			],

			'providers[Facebook][app_secret]' => [
				'label' => 'App Secret',
				'type' => 'text',
				'tab' => 'Facebook',
			],
		], 'primary');
	}

	public function login($provider_name, $action)
	{
		// check URL segment
		if ($action == "auth") {
			Hybrid_Endpoint::process();

			return;
		}

		$providers = $this->settings->get('providers', []);

		// create a HybridAuth object
		$socialAuth = new Hybrid_Auth([
			"base_url" => URL::route('sociallogin_provider', [$provider_name, 'auth']),
			"providers" => [
				'Facebook' => [
					"enabled" => true,
					"keys"    => array ( "id" => @$providers['Facebook']['app_id'], "secret" => @$providers['Facebook']['app_secret'] ),
					"scope"   => "email, user_about_me",
				]
			],
		]);

		// authenticate with Facebook
		$provider = $socialAuth->authenticate($provider_name);

		// fetch user profile
		$userProfile = $provider->getUserProfile();

		$provider->logout();

		return [
			'token' => $userProfile->identifier,
			'email' => $userProfile->email,
			'username' => $userProfile->username,
			'name' => $userProfile->firstName,
			'avatar' => $userProfile->photoURL
		];
	}
}
