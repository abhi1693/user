<?php namespace Frontend\User\SocialLoginProviders;

use Backend\Widgets\Form;
use Frontend\User\Models\Social;

abstract class SocialLoginProviderBase
{
	protected $settings;

	/**
	 * Initialize the singleton free from constructor parameters.
	 */
	protected function init()
	{
		$this->settings = Social::instance();
	}

	/**
	 * Return true if the settings form has the 'enabled' box checked.
	 *
	 * @return boolean
	 */
	abstract public function isEnabled();

	/**
	 * Add any provider-specific settings to the settings form. Add a partial
	 * with a set of steps to follow to retrieve the credentials, an enabled
	 * checkbox and the settings fields like so:
	 *
	 * $form->addFields([
	 *		'noop' => [
	 *			'type' => 'partial',
	 *			'path' => '$/frontend/user/partials/backend/forms/settings/_google_info.htm',
	 *			'tab' => 'Google',
	 *		],
	 *
	 *		'providers[Google][enabled]' => [
	 *			'label' => 'Enabled?',
	 *			'type' => 'checkbox',
	 *			'default' => 'true',
	 *			'tab' => 'Google',
	 *		],
	 *
	 *		'providers[Google][client_id]' => [
	 *			'label' => 'Client ID',
	 *			'type' => 'text',
	 *			'tab' => 'Google',
	 *		],
	 *
	 *		...
	 *	], 'primary');
	 *
	 * @param  Form   $form
	 *
	 * @return void
	 */
	abstract public function extendSettingsForm(Form $form);


	abstract public function login($provider_name, $action);
}
