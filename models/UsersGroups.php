<?php namespace Frontend\User\Models;

use Model;

class UsersGroups extends Model {

    /**
     * @var string The database table used by the model.
     */
    public $table = 'users_groups';

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];

    /**
     * @var array Relations
     */
    public $belongsTo = [
        'user' => ['Frontend\User\Models\User', 'key' => 'user_id'],
        'group' => ['Frontend\User\Models\UserGroup', 'key' => 'user_group_id'],
    ];

    /**
     * Adds a relation row to users groups.
     * @param $userObj
     * @param $groupId
     * @return bool
     */
    public static function addUser($userObj, $groupId) {
        if(UsersGroups::where('user_id', $userObj->id)->where('user_group_id', $groupId)->count() > 0)
            return false;
        $row = new UsersGroups();
        $row->user_id = $userObj->id;
        $row->user_group_id = $groupId;
        $row->save();

        return true;
    }
}
