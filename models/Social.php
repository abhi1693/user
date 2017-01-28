<?php namespace Frontend\User\Models;

use Model;

class Social extends Model {
  public $implement = ['System.Behaviors.SettingsModel'];

  public $settingsCode = 'sociallogin_settings';

  public $settingsFields = 'fields.yaml';

  protected $cache = [];
}
