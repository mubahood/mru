<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AspNetMembership extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'my_aspnet_membership';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'userId';

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
        'userId',
        'Email',
        'Password',
        'PasswordFormat',
        'PasswordKey',
        'IsApproved',
        'IsLockedOut',
        'CreateDate',
        'LastLoginDate',
        'LastPasswordChangedDate',
        'LastLockoutDate',
        'FailedPasswordAttemptCount',
        'FailedPasswordAttemptWindowStart',
        'FailedPasswordAnswerAttemptCount',
        'FailedPasswordAnswerAttemptWindowStart',
        'Comment'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'IsApproved' => 'boolean',
        'IsLockedOut' => 'boolean',
        'CreateDate' => 'datetime',
        'LastLoginDate' => 'datetime',
        'LastPasswordChangedDate' => 'datetime',
        'LastLockoutDate' => 'datetime',
    ];

    /**
     * Get the user that owns the membership.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'userId', 'id');
    }
}
