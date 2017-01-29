<?php namespace Frontend\User\SocialLoginProviders;

use Backend\Widgets\Form;
use Frontend\User\SocialLoginProviders\SocialLoginProviderBase;
use Frontend\User\Models\Social;
use Google_Client;
use Google_Service_Plus;
use Redirect;
use URL;
use Input;
use Session;

class Google extends SocialLoginProviderBase
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

		return !empty($providers['Google']['enabled']);
	}

	public function extendSettingsForm(Form $form)
	{
		$form->addFields([
			'noop' => [
				'type' => 'partial',
				'path' => '$/frontend/user/partials/backend/forms/settings/_google_info.htm',
				'tab' => 'Google',
			],

			'providers[Google][enabled]' => [
				'label' => 'Enabled?',
				'type' => 'checkbox',
				'default' => 'true',
				'tab' => 'Google',
			],

			'providers[Google][app_name]' => [
				'label' => 'Application Name',
				'type' => 'text',
				'default' => 'Social Login',
				'comment' => 'This appears on the Google login screen. Usually your site name.',
				'tab' => 'Google',
			],

			'providers[Google][client_id]' => [
				'label' => 'Client ID',
				'type' => 'text',
				'tab' => 'Google',
			],

			'providers[Google][client_secret]' => [
				'label' => 'Client Secret',
				'type' => 'text',
				'tab' => 'Google',
			],
		], 'primary');
	}

	protected function getClient()
	{
		$providers = $this->settings->get('providers', []);

		$client = new Google_Client();
		$client->setApplicationName(@$providers['Google']['app_name'] ?: 'Social Login');
		$client->setApprovalPrompt('auto');
		$client->setAccessType('offline');
		// Visit https://code.google.com/apis/console?api=plus to generate your
		// oauth2_client_id, oauth2_client_secret, and to register your oauth2_redirect_uri.
		$client->setClientId( @$providers['Google']['client_id'] );
		$client->setClientSecret( @$providers['Google']['client_secret'] );
		$client->setRedirectUri( URL::route('sociallogin_provider', ['Google'], true) );
		$client->addScope('profile');

		return $client;
	}



	public function login($provider_name, $action)
	{
		$client = $this->getClient();

		if ( Input::has('logout') )
		{
			Session::forget('access_token');
			return;
		}

		if ( Input::has('code') )
		{
			$client->authenticate( Input::get('code') );
			Session::put('access_token', $client->getAccessToken());
		}

		if ( Session::has('access_token') )
			$client->setAccessToken( Session::get('access_token') );
		else
		{
			$authUrl = $client->createAuthUrl();
			// Redirect::to() doesn't work here. Send header manually.
			header("Location: $authUrl");
			exit;
		}

		// http://stackoverflow.com/questions/9241213/how-to-refresh-token-with-google-api-client
		if ( $client->isAccessTokenExpired() )
		{
			$decoded_token = json_decode($client->getAccessToken());
			$refresh_token = $decoded_token->refresh_token;
			$client->refreshToken($refresh_token);
		}

		$data = $client->verifyIdToken();

		Session::forget('access_token');

		$access_token = $client->getAccessToken();

		return [
			'token' => $access_token['access_token'],
			'name' => implode(' ', explode(' ', $data['name'], -1)),
			'email' => $data['email'],
			'avatar' => preg_replace('/(s[0-9]+[-]c)/', 's512', $data['picture'])
		];
	}
}
