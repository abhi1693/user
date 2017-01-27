<?php namespace Abhimanyu\User\Updates;

use Schema;
use October\Rain\Database\Updates\Migration;
use Abhimanyu\User\Models\User;

class UsersAddLoginColumn extends Migration
{
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->string('login')->nullable()->index();
        });

        /*
         * Set login for existing users
         */
        $users = User::withTrashed()->get();
        foreach ($users as $user) {
            $user->login = $user->email;
            $user->save();
        }

        Schema::table('users', function($table)
        {
            $table->unique('login');
        });
    }

    public function down()
    {
        Schema::table('users', function($table)
        {
            $table->dropColumn('login');
        });
    }

}
