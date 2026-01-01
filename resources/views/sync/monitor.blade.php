<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sync Monitor: {{ $sync->table_name }} - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f5f6fa;
            min-height: 100vh;
            padding: 1rem 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        }
        .monitor-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 0.75rem;
        }
        .monitor-card {
            background: white;
            border-radius: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            overflow: hidden;
            border: 1px solid #e5e7eb;
        }
        .monitor-header {
            background: #fff;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 1rem;
        }
        .monitor-header h2 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.125rem;
        }
        .monitor-header p {
            font-size: 0.813rem;
            color: #6b7280;
        }
        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 6px;
        }
        .status-pending { background-color: #f59e0b; }
        .status-processing { 
            background-color: #3b82f6;
            animation: pulse 2s infinite;
        }
        .status-completed { background-color: #10b981; }
        .status-failed { background-color: #ef4444; }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.625rem;
            background: #f3f4f6;
            border-radius: 0;
            font-size: 0.813rem;
            font-weight: 500;
            color: #374151;
        }
        
        .progress-section {
            background: #f9fafb;
            padding: 0.875rem;
            border-radius: 0;
            margin-bottom: 1rem;
        }
        
        .progress-section h5 {
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
        }
        
        .progress {
            height: 28px;
            background-color: #e5e7eb;
            border-radius: 0;
            overflow: hidden;
        }
        .progress-bar {
            background-color: #3b82f6;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.813rem;
            transition: width 0.3s ease;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .stat-box {
            background: #f9fafb;
            padding: 0.75rem;
            border-radius: 0;
            border: 1px solid #e5e7eb;
            transition: border-color 0.2s;
        }
        .stat-box:hover {
            border-color: #d1d5db;
        }
        .stat-box i {
            font-size: 1.25rem;
            margin-bottom: 0.5rem;
            color: #6b7280;
        }
        .stat-value {
            font-size: 1.625rem;
            font-weight: 600;
            color: #1f2937;
            line-height: 1;
            margin-bottom: 0.375rem;
            transition: all 0.3s ease;
        }
        .stat-value.flash-update {
            color: #3b82f6;
            transform: scale(1.05);
        }
        .stat-label {
            color: #6b7280;
            font-size: 0.688rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-weight: 500;
        }
        .log-container {
            background: #1f2937;
            color: #d1d5db;
            padding: 0.75rem;
            border-radius: 0;
            max-height: 250px;
            overflow-y: auto;
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Mono', 'Courier New', monospace;
            font-size: 0.75rem;
            line-height: 1.4;
            border: 1px solid #374151;
        }
        .log-container::-webkit-scrollbar {
            width: 6px;
        }
        .log-container::-webkit-scrollbar-track {
            background: #1f2937;
        }
        .log-container::-webkit-scrollbar-thumb {
            background: #4b5563;
            border-radius: 0;
        }
        .log-container::-webkit-scrollbar-thumb:hover {
            background: #6b7280;
        }
        .log-entry {
            margin-bottom: 0.125rem;
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-3px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .btn-start-sync {
            background: #3b82f6;
            border: none;
            color: white;
            padding: 0.5rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 500;
            border-radius: 0;
            transition: all 0.2s;
        }
        .btn-start-sync:hover:not(:disabled) {
            background: #2563eb;
            color: white;
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }
        .alert-info {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            border-radius: 0;
            padding: 0.625rem 0.875rem;
            margin-bottom: 1rem;
            font-size: 0.875rem;
        }
        .alert-info strong {
            font-weight: 600;
        }
        .section-title {
            font-size: 0.75rem;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
    </style>
</head>
<body>
    <div class="monitor-container">
        <div class="monitor-card">
            <!-- Header -->
            <div class="monitor-header">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2><i class="fas fa-database"></i> {{ $sync->table_name }}</h2>
                        <p class="mb-0">Sync ID: #{{ $sync->id }} | Started: {{ $sync->created_at->format('Y-m-d H:i:s') }}</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="status-badge">
                            <span class="status-indicator status-{{ $sync->status }}"></span>
                            {{ ucfirst($sync->status) }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-4">
                <!-- Progress Bar -->
                <div class="progress-section">
                    <h5>Sync Progress</h5>
                    <div class="progress">
                        <div id="progressBar" class="progress-bar" 
                             role="progressbar" 
                             style="width: {{ $sync->progress_percentage }}%;">
                            <span id="progressText">{{ $sync->progress_percentage }}%</span>
                        </div>
                    </div>
                    <div class="text-center mt-2">
                        <small class="text-muted">
                            <span id="syncedCount">{{ number_format($sync->number_of_records_synced) }}</span> 
                            @if($sync->total_records)
                                / {{ number_format($sync->total_records) }}
                            @endif
                            records synced
                        </small>
                    </div>
                </div>

                <!-- Start Sync Button (shown only if pending) -->
                @if($sync->status === 'pending')
                <div class="text-center mb-4">
                    <button class="btn btn-start-sync" onclick="startProcessing()" id="startButton">
                        <i class="fas fa-play"></i> Start Synchronization
                    </button>
                </div>
                @elseif($sync->status === 'processing')
                <div class="text-center mb-4">
                    <button class="btn btn-start-sync" disabled style="background: #6b7280; cursor: not-allowed;">
                        <i class="fas fa-spinner fa-spin"></i> Processing...
                    </button>
                </div>
                @elseif($sync->status === 'completed')
                <div class="text-center mb-4">
                    <button class="btn btn-start-sync" disabled style="background: #10b981; cursor: not-allowed;">
                        <i class="fas fa-check"></i> Completed
                    </button>
                </div>
                @elseif($sync->status === 'failed')
                <div class="text-center mb-4">
                    <button class="btn btn-start-sync" disabled style="background: #ef4444; cursor: not-allowed;">
                        <i class="fas fa-times"></i> Failed
                    </button>
                </div>
                @endif

                <!-- Statistics Grid -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="text-center">
                            <i class="fas fa-plus-circle"></i>
                            <div class="stat-value" id="insertedCount">{{ number_format($sync->records_inserted) }}</div>
                            <div class="stat-label">Inserted</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="text-center">
                            <i class="fas fa-edit"></i>
                            <div class="stat-value" id="updatedCount">{{ number_format($sync->records_updated) }}</div>
                            <div class="stat-label">Updated</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="text-center">
                            <i class="fas fa-forward"></i>
                            <div class="stat-value" id="skippedCount">{{ number_format($sync->records_skipped) }}</div>
                            <div class="stat-label">Skipped</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="text-center">
                            <i class="fas fa-times-circle"></i>
                            <div class="stat-value" id="failedCount">{{ number_format($sync->records_failed) }}</div>
                            <div class="stat-label">Failed</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="text-center">
                            <i class="fas fa-clock"></i>
                            <div class="stat-value" id="duration">
                                @if($sync->duration_seconds)
                                    {{ gmdate('H:i:s', $sync->duration_seconds) }}
                                @else
                                    00:00:00
                                @endif
                            </div>
                            <div class="stat-label">Duration</div>
                        </div>
                    </div>
                    <div class="stat-box">
                        <div class="text-center">
                            <i class="fas fa-tachometer-alt"></i>
                            <div class="stat-value" id="speed">
                                @if($sync->duration_seconds && $sync->number_of_records_synced > 0)
                                    {{ number_format($sync->number_of_records_synced / $sync->duration_seconds, 2) }}
                                @else
                                    0
                                @endif
                            </div>
                            <div class="stat-label">Records/sec</div>
                        </div>
                    </div>
                </div>

                <!-- Message -->
                <div class="alert alert-info mt-4" id="statusMessage">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Status:</strong> 
                    <span id="messageText">{{ $sync->message ?? 'Ready to start' }}</span>
                </div>

                <!-- Live Log -->
                <div class="mt-4">
                    <h5 class="section-title"><i class="fas fa-terminal"></i> Live Log</h5>
                    <div class="log-container" id="logContainer">
                        <div class="log-entry">[{{ now()->format('H:i:s') }}] Sync monitor initialized</div>
                        <div class="log-entry">[{{ now()->format('H:i:s') }}] Table: {{ $sync->table_name }}</div>
                        <div class="log-entry">[{{ now()->format('H:i:s') }}] Batch size: {{ $sync->range_limit }} records</div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="mt-4 d-flex gap-2 justify-content-center">
                    <a href="{{ admin_url('remote-database-syncs-admin') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Syncs
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const syncId = {{ $sync->id }};
        const baseUrl = '{{ url("/") }}';
        let updateInterval;
        let startTime = null;
        let lastSyncedCount = {{ $sync->number_of_records_synced }};

        // CSRF Token setup
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // Initialize UI with current state on load
        $(document).ready(function() {
            @if($sync->status === 'processing')
                // If processing, start monitoring immediately
                startMonitoring();
            @elseif($sync->status === 'completed')
                addLog('Sync already completed', 'success');
            @elseif($sync->status === 'failed')
                addLog('Sync previously failed: {{ $sync->message ?? "Unknown error" }}', 'error');
            @endif
        });

        // Start processing sync
        function startProcessing() {
            // Disable button immediately
            const btn = $('#startButton');
            btn.prop('disabled', true)
               .html('<i class="fas fa-spinner fa-spin"></i> Starting...')
               .css('background', '#6b7280');
            
            addLog('Starting sync process...');
            startTime = Date.now();
            
            $.ajax({
                url: baseUrl + '/sync/' + syncId + '/process',
                method: 'POST',
                success: function(response) {
                    if (response.success) {
                        addLog('Sync process initiated successfully', 'success');
                        btn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                        // Start monitoring after successful initiation
                        startMonitoring();
                        // Force immediate update
                        updateStatus();
                    } else {
                        const errorMsg = response.message || 'Sync failed';
                        addLog('ERROR: ' + errorMsg, 'error');
                        btn.html('<i class="fas fa-times"></i> Failed').css('background', '#ef4444');
                        $('#messageText').text(errorMsg);
                        $('.status-indicator')
                            .removeClass('status-pending status-processing status-completed status-failed')
                            .addClass('status-failed');
                        $('.status-badge').html(
                            '<span class="status-indicator status-failed"></span>Failed'
                        );
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON?.message || xhr.statusText || 'Sync failed';
                    addLog('ERROR: ' + message, 'error');
                    console.error('Sync error:', xhr);
                    
                    btn.html('<i class="fas fa-times"></i> Failed').css('background', '#ef4444');
                    $('#messageText').text(message);
                    $('.status-indicator')
                        .removeClass('status-pending status-processing status-completed status-failed')
                        .addClass('status-failed');
                    $('.status-badge').html(
                        '<span class="status-indicator status-failed"></span>Failed'
                    );
                }
            });
        }

        // Start real-time monitoring
        function startMonitoring() {
            addLog('Monitoring started...');
            updateInterval = setInterval(updateStatus, 2000); // Update every 2 seconds
        }

        // Update sync status
        function updateStatus() {
            $.ajax({
                url: baseUrl + '/sync/' + syncId + '/status',
                method: 'GET',
                success: function(data) {
                    // Update progress bar
                    const progress = data.progress || 0;
                    $('#progressBar').css('width', progress + '%');
                    $('#progressText').text(progress + '%');
                    
                    // Update counts (handle null/undefined)
                    $('#syncedCount').text(numberFormat(data.synced || 0));
                    $('#insertedCount').text(numberFormat(data.inserted || 0));
                    $('#updatedCount').text(numberFormat(data.updated || 0));
                    $('#skippedCount').text(numberFormat(data.skipped || 0));
                    $('#failedCount').text(numberFormat(data.failed || 0));
                    
                    // Update duration
                    const duration = data.duration || 0;
                    if (duration > 0) {
                        const hours = Math.floor(duration / 3600);
                        const minutes = Math.floor((duration % 3600) / 60);
                        const seconds = duration % 60;
                        $('#duration').text(pad(hours) + ':' + pad(minutes) + ':' + pad(seconds));
                        
                        // Update speed
                        const speed = (data.synced || 0) / duration;
                        $('#speed').text(speed.toFixed(2));
                    } else {
                        $('#duration').text('00:00:00');
                        $('#speed').text('0');
                    }
                    
                    // Update message
                    if (data.message) {
                        $('#messageText').text(data.message);
                    }
                    
                    // Add log entry for significant changes with animation
                    const currentCount = data.synced || 0;
                    if (currentCount > lastSyncedCount && currentCount % 100 === 0) {
                        addLog('Processed ' + numberFormat(currentCount) + ' records...', 'info');
                        // Flash the synced count
                        $('#syncedCount').addClass('flash-update');
                        setTimeout(() => $('#syncedCount').removeClass('flash-update'), 500);
                    }
                    lastSyncedCount = currentCount;
                    
                    // Check if completed or failed
                    if (data.status === 'completed' || data.status === 'failed') {
                        clearInterval(updateInterval);
                        const statusMsg = data.status === 'completed' 
                            ? 'Sync completed successfully!' 
                            : 'Sync failed: ' + (data.message || 'Unknown error');
                        addLog(statusMsg, data.status === 'completed' ? 'success' : 'error');
                        
                        // Update button
                        const btn = $('#startButton');
                        if (data.status === 'completed') {
                            btn.html('<i class="fas fa-check"></i> Completed')
                               .css('background', '#10b981');
                        } else {
                            btn.html('<i class="fas fa-times"></i> Failed')
                               .css('background', '#ef4444');
                        }
                        
                        // Update status indicator and badge
                        $('.status-indicator')
                            .removeClass('status-pending status-processing status-completed status-failed')
                            .addClass('status-' + data.status);
                        $('.status-badge').html(
                            '<span class="status-indicator status-' + data.status + '"></span>' +
                            capitalizeFirst(data.status)
                        );
                        
                        // Reset failure count
                        window.failureCount = 0;
                    }
                    
                    // Update status for processing
                    if (data.status === 'processing') {
                        $('.status-indicator')
                            .removeClass('status-pending status-processing status-completed status-failed')
                            .addClass('status-processing');
                        $('.status-badge').html(
                            '<span class="status-indicator status-processing"></span>Processing'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Status update failed:', {xhr, status, error});
                    addLog('Failed to fetch status update: ' + (xhr.statusText || error), 'error');
                    
                    // Stop monitoring after multiple failures
                    if (!window.failureCount) window.failureCount = 0;
                    window.failureCount++;
                    
                    if (window.failureCount >= 5) {
                        clearInterval(updateInterval);
                        addLog('Stopped monitoring after multiple failures', 'error');
                    }
                }
            });
        }

        // Add log entry
        function addLog(message, type = 'info') {
            const time = new Date().toLocaleTimeString();
            const icon = type === 'error' ? '✗' : type === 'success' ? '✓' : '•';
            const color = type === 'error' ? '#f87171' : type === 'success' ? '#34d399' : '#d1d5db';
            
            const logEntry = $('<div class="log-entry"></div>')
                .css('color', color)
                .html(`[${time}] ${icon} ${message}`);
            
            $('#logContainer').append(logEntry);
            
            // Auto-scroll to bottom
            $('#logContainer').scrollTop($('#logContainer')[0].scrollHeight);
        }

        // Utility functions
        function numberFormat(num) {
            if (num === null || num === undefined || isNaN(num)) return '0';
            return Number(num).toLocaleString();
        }

        function pad(num) {
            return String(num).padStart(2, '0');
        }

        function capitalizeFirst(str) {
            if (!str) return '';
            return str.charAt(0).toUpperCase() + str.slice(1);
        }

        // Auto-start if already processing
        @if($sync->status === 'processing')
            startMonitoring();
        @endif
    </script>
</body>
</html>
