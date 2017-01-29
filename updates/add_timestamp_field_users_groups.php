<?php namespace Frontend\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;

class AddTimestampFieldUsersGroups extends Migration
{

    public function up()
    {
        Schema::table('users_groups', function($table) {
            $table->timestamps();
        });
    }

    public function down() {}

}
