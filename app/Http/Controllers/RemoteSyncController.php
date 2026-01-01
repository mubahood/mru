<?php

namespace App\Http\Controllers;

use App\Models\RemoteDataBaseSync;
use App\Services\RemoteSyncService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * RemoteSyncController
 * 
 * Web controller for managing remote database synchronization.
 * Provides real-time sync monitoring and control interface.
 * 
 * @package App\Http\Controllers
 */
class RemoteSyncController extends Controller
{
    /**
     * Sync service instance
     */
    protected $syncService;

    /**
     * Constructor
     */
    public function __construct(RemoteSyncService $syncService)
    {
        $this->syncService = $syncService;
    }

    /**
     * Display sync dashboard
     */
    public function index()
    {
        $syncs = RemoteDataBaseSync::with([])
            ->latest()
            ->paginate(20);
        
        $stats = [
            'total' => RemoteDataBaseSync::count(),
            'completed' => RemoteDataBaseSync::completed()->count(),
            'failed' => RemoteDataBaseSync::failed()->count(),
            'processing' => RemoteDataBaseSync::processing()->count(),
            'total_records' => RemoteDataBaseSync::sum('number_of_records_synced'),
        ];
        
        // Get available tables
        $remoteTables = $this->syncService->getRemoteTables();
        
        return view('sync.index', compact('syncs', 'stats', 'remoteTables'));
    }

    /**
     * Show sync page for specific table with real-time updates
     */
    public function show($id)
    {
        $sync = RemoteDataBaseSync::findOrFail($id);
        
        return view('sync.show', compact('sync'));
    }

    /**
     * Start new sync operation
     */
    public function sync(Request $request)
    {
        $request->validate([
            'table_name' => 'required|string',
            'range_limit' => 'nullable|integer|min:100|max:10000',
        ]);
        
        $tableName = $request->input('table_name');
        $config = [
            'range_limit' => $request->input('range_limit', 1000),
        ];
        
        try {
            // Create sync record
            $sync = RemoteDataBaseSync::createNewSync($tableName, $config);
            
            // Return sync page URL
            return response()->json([
                'success' => true,
                'message' => 'Sync initiated',
                'sync_id' => $sync->id,
                'url' => route('sync.monitor', $sync->id),
            ]);
            
        } catch (Exception $e) {
            Log::error("Failed to start sync: " . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to start sync: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Monitor sync progress in real-time
     */
    public function monitor($id)
    {
        $sync = RemoteDataBaseSync::findOrFail($id);
        
        return view('sync.monitor', compact('sync'));
    }

    /**
     * Process sync (called from monitor page)
     */
    public function process($id)
    {
        // Configure PHP for long-running operation
        set_time_limit(0); // No time limit
        ini_set('memory_limit', '512M'); // Increase memory limit
        ini_set('max_execution_time', 0); // No execution time limit
        
        $sync = RemoteDataBaseSync::findOrFail($id);
        
        // Return error if already processing
        if ($sync->status === 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'Sync is already processing',
            ], 400);
        }
        
        // Return error if already completed
        if ($sync->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Sync has already been completed',
            ], 400);
        }
        
        try {
            // Process the existing sync record - this will handle marking as started, processing, and completed/failed
            $this->syncService->processSyncRecord($sync);
            
            // Get fresh sync data after completion
            $sync->refresh();
            
            return response()->json([
                'success' => true,
                'message' => 'Sync completed successfully',
            ]);
            
        } catch (Exception $e) {
            Log::error("Sync process failed for sync #{$id}: " . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Refresh to get the failed status that was set by the service
            $sync->refresh();
            
            return response()->json([
                'success' => false,
                'message' => $sync->message ?? $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get sync status (AJAX endpoint for real-time updates)
     */
    public function status($id)
    {
        $sync = RemoteDataBaseSync::findOrFail($id);
        
        return response()->json([
            'id' => $sync->id,
            'table_name' => $sync->table_name,
            'status' => $sync->status,
            'message' => $sync->message,
            'progress' => $sync->progress_percentage,
            'total_records' => $sync->total_records,
            'synced' => $sync->number_of_records_synced,
            'inserted' => $sync->records_inserted,
            'updated' => $sync->records_updated,
            'skipped' => $sync->records_skipped,
            'failed' => $sync->records_failed,
            'duration' => $sync->duration_seconds,
            'started_at' => $sync->sync_started_at?->format('Y-m-d H:i:s'),
            'completed_at' => $sync->sync_completed_at?->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Test remote connection
     */
    public function testConnection()
    {
        $result = $this->syncService->testRemoteConnection();
        
        return response()->json($result);
    }

    /**
     * Get sync statistics
     */
    public function statistics(Request $request)
    {
        $tableName = $request->input('table_name');
        $stats = $this->syncService->getSyncStatistics($tableName);
        
        return response()->json($stats);
    }

    /**
     * Delete sync record
     */
    public function destroy($id)
    {
        $sync = RemoteDataBaseSync::findOrFail($id);
        
        // Don't allow deletion of processing syncs
        if ($sync->status === 'processing') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a sync that is currently processing',
            ], 400);
        }
        
        $sync->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Sync record deleted successfully',
        ]);
    }
}
