<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PopulateAspnetUsersEmailFromMembership extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Copy email addresses from my_aspnet_membership to my_aspnet_users
        DB::statement("
            UPDATE my_aspnet_users u
            INNER JOIN my_aspnet_membership m ON u.id = m.userId
            SET u.email = m.Email
            WHERE m.Email IS NOT NULL 
            AND m.Email != ''
            AND u.email IS NULL
        ");
        
        echo "✅ Populated " . DB::table('my_aspnet_users')->whereNotNull('email')->count() . " email addresses\n";
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Set emails back to NULL (non-destructive)
        DB::table('my_aspnet_users')->update(['email' => null]);
    }
}
