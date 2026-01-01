<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Remote Database Sync - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sync-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .stats-card {
            border-radius: 10px;
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .sync-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .badge {
            padding: 0.5em 0.8em;
        }
        .btn-sync {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        .btn-sync:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="sync-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h1><i class="fas fa-sync-alt"></i> Remote Database Sync</h1>
                    <p class="mb-0">Manage synchronization with Campus Dynamics</p>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-light" onclick="testConnection()">
                        <i class="fas fa-plug"></i> Test Connection
                    </button>
                    <a href="#" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newSyncModal">
                        <i class="fas fa-plus"></i> New Sync
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card stats-card text-center p-3">
                    <div class="text-muted mb-2"><i class="fas fa-database"></i> Total Syncs</div>
                    <h2 class="mb-0">{{ $stats['total'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center p-3">
                    <div class="text-success mb-2"><i class="fas fa-check-circle"></i> Completed</div>
                    <h2 class="mb-0 text-success">{{ $stats['completed'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center p-3">
                    <div class="text-danger mb-2"><i class="fas fa-times-circle"></i> Failed</div>
                    <h2 class="mb-0 text-danger">{{ $stats['failed'] }}</h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card text-center p-3">
                    <div class="text-info mb-2"><i class="fas fa-spinner"></i> Processing</div>
                    <h2 class="mb-0 text-info">{{ $stats['processing'] }}</h2>
                </div>
            </div>
        </div>

        <!-- Sync History Table -->
        <div class="card sync-table">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0"><i class="fas fa-history"></i> Sync History</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Table Name</th>
                                <th>Status</th>
                                <th>Records Synced</th>
                                <th>Progress</th>
                                <th>Duration</th>
                                <th>Triggered By</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($syncs as $sync)
                            <tr>
                                <td>{{ $sync->id }}</td>
                                <td><strong>{{ $sync->table_name }}</strong></td>
                                <td>
                                    <span class="badge bg-{{ $sync->status_color }}">
                                        @if($sync->status === 'processing')
                                            <i class="fas fa-spinner fa-spin"></i>
                                        @elseif($sync->status === 'completed')
                                            <i class="fas fa-check"></i>
                                        @elseif($sync->status === 'failed')
                                            <i class="fas fa-times"></i>
                                        @endif
                                        {{ ucfirst($sync->status) }}
                                    </span>
                                </td>
                                <td>
                                    {{ number_format($sync->number_of_records_synced) }}
                                    @if($sync->total_records)
                                        / {{ number_format($sync->total_records) }}
                                    @endif
                                </td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ $sync->progress_percentage }}%;"
                                             aria-valuenow="{{ $sync->progress_percentage }}" 
                                             aria-valuemin="0" aria-valuemax="100">
                                            {{ $sync->progress_percentage }}%
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($sync->duration_seconds)
                                        {{ gmdate('H:i:s', $sync->duration_seconds) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $sync->triggered_by ?? 'System' }}</td>
                                <td>{{ $sync->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <a href="{{ route('sync.monitor', $sync->id) }}" 
                                       class="btn btn-sm btn-primary" 
                                       target="_blank">
                                        <i class="fas fa-eye"></i> Monitor
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No sync records found. Start a new sync to begin.</p>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="mt-3">
                    {{ $syncs->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- New Sync Modal -->
    <div class="modal fade" id="newSyncModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus"></i> Start New Sync</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="newSyncForm">
                        <div class="mb-3">
                            <label class="form-label">Table Name</label>
                            <select class="form-select" name="table_name" required>
                                <option value="">Select a table...</option>
                                @foreach($remoteTables as $table)
                                    <option value="{{ $table }}">{{ $table }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Batch Size (records per batch)</label>
                            <input type="number" class="form-control" name="range_limit" 
                                   value="1000" min="100" max="10000">
                            <small class="text-muted">Recommended: 1000-5000 records</small>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sync" onclick="startSync()">
                        <i class="fas fa-play"></i> Start Sync
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function testConnection() {
            $.ajax({
                url: '{{ route("sync.test-connection") }}',
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        alert('✅ ' + response.message + '\nDatabase: ' + response.database);
                    } else {
                        alert('❌ ' + response.message);
                    }
                },
                error: function() {
                    alert('❌ Failed to test connection');
                }
            });
        }

        function startSync() {
            const form = $('#newSyncForm');
            const data = {
                table_name: form.find('[name="table_name"]').val(),
                range_limit: form.find('[name="range_limit"]').val()
            };

            if (!data.table_name) {
                alert('Please select a table');
                return;
            }

            $.ajax({
                url: '{{ route("sync.sync") }}',
                method: 'POST',
                data: data,
                success: function(response) {
                    if (response.success) {
                        // Close modal
                        $('#newSyncModal').modal('hide');
                        
                        // Open monitor page in new tab
                        window.open(response.url, '_blank');
                        
                        // Reload page after short delay
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    }
                },
                error: function(xhr) {
                    alert('Failed to start sync: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }

        // Auto-refresh processing syncs every 5 seconds
        setInterval(function() {
            const processingRows = $('.badge.bg-info').closest('tr');
            if (processingRows.length > 0) {
                location.reload();
            }
        }, 5000);
    </script>
</body>
</html>
