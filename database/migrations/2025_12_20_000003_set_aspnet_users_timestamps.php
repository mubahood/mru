<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SetAspnetUsersTimestamps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Set created_at from my_aspnet_membership.CreationDate
        DB::statement("
            UPDATE my_aspnet_users u
            LEFT JOIN my_aspnet_membership m ON u.id = m.userId
            SET u.created_at = COALESCE(
                m.CreationDate,
                '2020-01-01 00:00:00'
            )
            WHERE u.created_at IS NULL
        ");
        
        // Set updated_at from lastActivityDate or current timestamp
        DB::statement("
            UPDATE my_aspnet_users 
            SET updated_at = COALESCE(lastActivityDate, NOW())
            WHERE updated_at IS NULL
        ");
        
        echo "✅ Set timestamps for " . DB::table('my_aspnet_users')->whereNotNull('created_at')->count() . " users\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('my_aspnet_users')->update([
            'created_at' => null,
            'updated_at' => null
        ]);
    }
}
