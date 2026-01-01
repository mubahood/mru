<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * RemoteDataBaseSync Model
 * 
 * Manages synchronization between remote Campus Dynamics database and local database.
 * Tracks sync history, status, and statistics for each table sync operation.
 * 
 * @package App\Models
 * @property int $id
 * @property string $table_name
 * @property \Carbon\Carbon|null $last_synced_at
 * @property int $start_id
 * @property int $range_limit
 * @property string $status
 * @property string|null $message
 * @property string|null $remote_data
 * @property int $number_of_records_synced
 * @property int|null $total_records
 * @property int $records_inserted
 * @property int $records_updated
 * @property int $records_skipped
 * @property int $records_failed
 * @property \Carbon\Carbon|null $sync_started_at
 * @property \Carbon\Carbon|null $sync_completed_at
 * @property int|null $duration_seconds
 * @property string|null $triggered_by
 * @property string|null $sync_config
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class RemoteDataBaseSync extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'remote_database_syncs';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'table_name',
        'last_synced_at',
        'start_id',
        'range_limit',
        'status',
        'message',
        'remote_data',
        'number_of_records_synced',
        'total_records',
        'records_inserted',
        'records_updated',
        'records_skipped',
        'records_failed',
        'sync_started_at',
        'sync_completed_at',
        'duration_seconds',
        'triggered_by',
        'sync_config',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'last_synced_at' => 'datetime',
        'sync_started_at' => 'datetime',
        'sync_completed_at' => 'datetime',
        'start_id' => 'integer',
        'range_limit' => 'integer',
        'number_of_records_synced' => 'integer',
        'total_records' => 'integer',
        'records_inserted' => 'integer',
        'records_updated' => 'integer',
        'records_skipped' => 'integer',
        'records_failed' => 'integer',
        'duration_seconds' => 'integer',
    ];

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    /**
     * Scope for pending syncs
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for processing syncs
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }

    /**
     * Scope for completed syncs
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for failed syncs
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific table
     */
    public function scopeForTable($query, $tableName)
    {
        return $query->where('table_name', $tableName);
    }

    /**
     * Scope for recent syncs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /*
    |--------------------------------------------------------------------------
    | Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Mark sync as started
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'processing',
            'sync_started_at' => now(),
            'message' => 'Sync started...',
        ]);
    }

    /**
     * Mark sync as completed
     */
    public function markAsCompleted($message = 'Sync completed successfully')
    {
        $this->update([
            'status' => 'completed',
            'sync_completed_at' => now(),
            'last_synced_at' => now(),
            'duration_seconds' => $this->sync_started_at ? now()->diffInSeconds($this->sync_started_at) : null,
            'message' => $message,
        ]);
    }

    /**
     * Mark sync as failed
     */
    public function markAsFailed($message = 'Sync failed')
    {
        $this->update([
            'status' => 'failed',
            'sync_completed_at' => now(),
            'duration_seconds' => $this->sync_started_at ? now()->diffInSeconds($this->sync_started_at) : null,
            'message' => $message,
        ]);
    }

    /**
     * Update sync progress
     */
    public function updateProgress($data = [])
    {
        $this->update(array_merge([
            'number_of_records_synced' => $this->number_of_records_synced + ($data['count'] ?? 0),
        ], $data));
    }

    /**
     * Increment counters
     */
    public function incrementInserted($count = 1)
    {
        $this->increment('records_inserted', $count);
        $this->increment('number_of_records_synced', $count);
    }

    public function incrementUpdated($count = 1)
    {
        $this->increment('records_updated', $count);
        $this->increment('number_of_records_synced', $count);
    }

    public function incrementSkipped($count = 1)
    {
        $this->increment('records_skipped', $count);
    }

    public function incrementFailed($count = 1)
    {
        $this->increment('records_failed', $count);
    }

    /**
     * Get remote database connection
     */
    public static function getRemoteConnection()
    {
        return DB::connection('remote_mysql');
    }

    /**
     * Get sync configuration as array
     */
    public function getSyncConfigAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Set sync configuration
     */
    public function setSyncConfigAttribute($value)
    {
        $this->attributes['sync_config'] = is_array($value) ? json_encode($value) : $value;
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentageAttribute()
    {
        if (!$this->total_records || $this->total_records == 0) {
            return 0;
        }
        return min(100, round(($this->number_of_records_synced / $this->total_records) * 100, 2));
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute()
    {
        return [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'paused' => 'secondary',
        ][$this->status] ?? 'default';
    }

    /**
     * Get latest sync for a table
     */
    public static function getLatestForTable($tableName)
    {
        return static::forTable($tableName)->latest()->first();
    }

    /**
     * Create new sync record
     */
    public static function createNewSync($tableName, $config = [])
    {
        $latest = static::getLatestForTable($tableName);
        
        return static::create([
            'table_name' => $tableName,
            'start_id' => $latest ? $latest->start_id + $latest->range_limit : 0,
            'range_limit' => $config['range_limit'] ?? 1000,
            'status' => 'pending',
            'triggered_by' => auth()->user()->name ?? 'System',
            'sync_config' => $config,
        ]);
    }
}
