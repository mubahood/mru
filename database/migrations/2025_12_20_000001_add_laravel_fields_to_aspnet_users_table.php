<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLaravelFieldsToAspnetUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('my_aspnet_users', function (Blueprint $table) {
            // Laravel bcrypt password (separate from ASP.NET Password)
            $table->string('password_laravel', 255)->nullable()
                ->comment('Laravel bcrypt password - used when set, falls back to ASP.NET');
            
            // Remember me token for persistent login
            $table->string('remember_token', 100)->nullable();
            
            // Enterprise ID for multi-tenancy support
            $table->integer('enterprise_id')->default(1)->index()
                ->comment('Multi-tenancy: all MRU users default to enterprise 1');
            
            // Email field (will be populated from my_aspnet_membership)
            $table->string('email', 255)->nullable()->index();
            
            // Laravel timestamps
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            
            // User type classification (admin, teacher, student, employee, etc.)
            $table->string('user_type', 50)->default('user')->index()
                ->comment('User classification for Laravel-Admin');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('my_aspnet_users', function (Blueprint $table) {
            $table->dropColumn([
                'password_laravel',
                'remember_token',
                'enterprise_id',
                'email',
                'created_at',
                'updated_at',
                'user_type',
            ]);
        });
    }
}
