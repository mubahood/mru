<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspNetRole extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'my_aspnet_roles';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'applicationId',
        'name',
        'description'
    ];

    /**
     * Get the users that belong to this role.
     */
    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'my_aspnet_usersinroles',
            'roleId',
            'userId'
        );
    }

    /**
     * Map ASP.NET role to Laravel-Admin role.
     *
     * @return string
     */
    public function toLaravelRole()
    {
        // Map ASP.NET roles to Laravel-Admin roles
        $roleMapping = [
            'Administrator' => 'administrator',
            'System Admin' => 'administrator',
            'Dean' => 'dean',
            'Head of Department' => 'hod',
            'Accountant' => 'accountant',
            'Secretary' => 'secretary',
            'Librarian' => 'librarian',
            'Teacher' => 'teacher',
            'Student' => 'student',
        ];

        return $roleMapping[$this->name] ?? strtolower($this->name);
    }
}
