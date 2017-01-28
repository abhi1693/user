<?php namespace Frontend\User\Components;

use Session;
use URL;
use Cms\Classes\ComponentBase;
use Frontend\User\Models\Social;
use Frontend\User\Classes\ProviderManager;
use Illuminate\Support\ViewErrorBag;

class SocialLogin extends ComponentBase
{

	public function componentDetails()
	{
		return [
			'name'        => 'Social Login',
			'description' => 'Adds social_login_link($provider, $success_url, $error_url) method.'
		];
	}

	/**
	 * Executed when this component is bound to a page or layout.
	 */
	public function onRun()
	{
		$providers = ProviderManager::instance()->listProviders();

		$social_login_links = [];
		foreach ( $providers as $provider_class => $provider_details )
			if ( $provider_class::instance()->isEnabled() )
				$social_login_links[$provider_details['alias']] = URL::route('sociallogin_provider', [$provider_details['alias']]);

		$this->page['social_login_links'] = $social_login_links;

		$this->page['errors'] = Session::get('errors', new ViewErrorBag);
	}
}
