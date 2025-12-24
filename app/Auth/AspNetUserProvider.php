<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AspNetUserProvider implements UserProvider
{
    /**
     * The Eloquent user model.
     *
     * @var string
     */
    protected $model;

    /**
     * Create a new database user provider.
     *
     * @param  string  $model
     * @return void
     */
    public function __construct($model)
    {
        $this->model = $model;
    }

    /**
     * Retrieve a user by their unique identifier.
     *
     * @param  mixed  $identifier
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveById($identifier)
    {
        return $this->createModel()->newQuery()->find($identifier);
    }

    /**
     * Retrieve a user by their unique identifier and "remember me" token.
     *
     * @param  mixed  $identifier
     * @param  string  $token
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByToken($identifier, $token)
    {
        $model = $this->createModel();

        return $model->newQuery()
            ->where($model->getAuthIdentifierName(), $identifier)
            ->where($model->getRememberTokenName(), $token)
            ->first();
    }

    /**
     * Update the "remember me" token for the given user in storage.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $token
     * @return void
     */
    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
        $user->timestamps = false;
        $user->save();
    }

    /**
     * Retrieve a user by the given credentials.
     *
     * @param  array  $credentials
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function retrieveByCredentials(array $credentials)
    {
        if (empty($credentials) ||
            (count($credentials) === 1 &&
             array_key_exists('password', $credentials))) {
            return;
        }

        // Build query to find user
        $query = $this->createModel()->newQuery();

        foreach ($credentials as $key => $value) {
            if (! str_contains($key, 'password')) {
                $query->where($key, $value);
            }
        }

        return $query->first();
    }

    /**
     * Validate a user against the given credentials.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  array  $credentials
     * @return bool
     */
    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain = $credentials['password'];

        // First, try Laravel bcrypt password if it exists
        if (!empty($user->password_laravel)) {
            return Hash::check($plain, $user->password_laravel);
        }

        // Fall back to ASP.NET password verification
        return $this->validateAspNetPassword($user, $plain);
    }

    /**
     * Validate ASP.NET membership password.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $plain
     * @return bool
     */
    protected function validateAspNetPassword($user, $plain)
    {
        // Get membership record
        $membership = DB::table('my_aspnet_membership')
            ->where('userId', $user->id)
            ->first();

        if (!$membership || empty($membership->Password)) {
            return false;
        }

        // Check if account is approved and not locked out
        if (!$membership->IsApproved || $membership->IsLockedOut) {
            return false;
        }

        // ASP.NET uses base64(SHA256(salt + password))
        // PasswordFormat: 1 = Hashed (SHA256 + salt)
        if ($membership->PasswordFormat == 1) {
            $salt = $membership->PasswordKey ?? '';
            $hashedPassword = base64_encode(hash('sha256', $salt . $plain, true));
            
            if ($hashedPassword === $membership->Password) {
                // Auto-migrate to Laravel bcrypt on successful login
                $this->migratePasswordToBcrypt($user, $plain);
                return true;
            }
        }

        return false;
    }

    /**
     * Migrate ASP.NET password to Laravel bcrypt.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @param  string  $plain
     * @return void
     */
    protected function migratePasswordToBcrypt($user, $plain)
    {
        try {
            DB::table('my_aspnet_users')
                ->where('id', $user->id)
                ->update([
                    'password_laravel' => Hash::make($plain),
                    'updated_at' => now()
                ]);
        } catch (\Exception $e) {
            \Log::error('Failed to migrate password for user ' . $user->id . ': ' . $e->getMessage());
        }
    }

    /**
     * Create a new instance of the model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function createModel()
    {
        $class = '\\'.ltrim($this->model, '\\');

        return new $class;
    }

    /**
     * Gets the name of the Eloquent user model.
     *
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * Sets the name of the Eloquent user model.
     *
     * @param  string  $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }
}
