<?php namespace Frontend\User\Controllers;

use Flash;
use BackendMenu;
use Backend\Classes\Controller;
use Frontend\User\Models\UserGroup;

/**
 * User Groups Back-end Controller
 */
class UserGroups extends Controller
{
    public $implement = [
        'Backend.Behaviors.FormController',
        'Backend.Behaviors.ListController'
    ];

    public $formConfig = 'config_form.yaml';
    public $listConfig = 'config_list.yaml';

    public $requiredPermissions = ['frontend.users.access_groups'];

    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('Frontend.User', 'user', 'usergroups');
    }
}
