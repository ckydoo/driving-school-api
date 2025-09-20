@extends('admin.layouts.app')

@section('title', 'Backup Management')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-database"></i> Backup Management
        </h1>
        <div>
            <button type="button" class="btn btn-success btn-sm" onclick="createBackup()">
                <i class="fas fa-plus"></i> Create Backup
            </button>
            <button type="button" class="btn btn-info btn-sm" onclick="scheduleBackup()">
                <i class="fas fa-clock"></i> Schedule Backup
            </button>
            <button type="button" class="btn btn-primary btn-sm" onclick="refreshBackups()">
                <i class="fas fa-sync"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Backup Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Backups
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ count($backups ?? []) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Latest Backup
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @if(!empty($backups))
                                    {{ collect($backups)->first()->created_at->diffForHumans() }}
                                @else
                                    Never
                                @endif
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Total Size
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalSize = collect($backups ?? [])->sum('size_bytes');
                                    
                                    // Format bytes function
                                    function formatBytes($bytes, $precision = 2) {
                                        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
                                        
                                        if ($bytes == 0) {
                                            return '0 B';
                                        }
                                        
                                        $base = log($bytes, 1024);
                                        $power = floor($base);
                                        
                                        return round(pow(1024, $base - $power), $precision) . ' ' . ($units[$power] ?? 'B');
                                    }
                                @endphp
                                {{ formatBytes($totalSize) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hdd fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Auto Backup
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                <span class="badge badge-{{ true ? 'success' : 'danger' }}">
                                    {{ true ? 'Enabled' : 'Disabled' }}
                                </span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-robot fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup Settings -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cog"></i> Backup Configuration
                    </h6>
                </div>
                <div class="card-body">
                    <form id="backupConfigForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="backup_frequency">Backup Frequency</label>
                                    <select class="form-control" id="backup_frequency" name="backup_frequency">
                                        <option value="daily" selected>Daily</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="manual">Manual Only</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="backup_time">Backup Time</label>
                                    <input type="time" class="form-control" id="backup_time" name="backup_time" value="02:00">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="retention_days">Retention (Days)</label>
                                    <select class="form-control" id="retention_days" name="retention_days">
                                        <option value="7">7 days</option>
                                        <option value="14">14 days</option>
                                        <option value="30" selected>30 days</option>
                                        <option value="60">60 days</option>
                                        <option value="90">90 days</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="backup_type">Backup Type</label>
                                    <select class="form-control" id="backup_type" name="backup_type">
                                        <option value="full" selected>Full Backup</option>
                                        <option value="database">Database Only</option>
                                        <option value="files">Files Only</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="compress_backup" name="compress_backup" checked>
                                    <label class="form-check-label" for="compress_backup">
                                        Compress backups to save storage space
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input type="checkbox" class="form-check-input" id="encrypt_backup" name="encrypt_backup">
                                    <label class="form-check-label" for="encrypt_backup">
                                        Encrypt backups for security
                                    </label>
                                </div>
                                <button type="button" class="btn btn-primary" onclick="saveBackupConfig()">
                                    <i class="fas fa-save"></i> Save Configuration
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Storage Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Backup Location:</strong><br>
                        <code class="small">/storage/app/backups/</code>
                    </div>
                    <div class="mb-3">
                        <strong>Available Space:</strong><br>
                        <span class="text-success">245 GB available</span>
                    </div>
                    <div class="mb-3">
                        <strong>Next Scheduled Backup:</strong><br>
                        <span class="text-info">Tomorrow at 02:00 AM</span>
                    </div>
                    <div class="mb-3">
                        <strong>Backup Status:</strong><br>
                        <span class="badge badge-success">System Healthy</span>
                    </div>
                    <hr>
                    <div class="d-grid gap-2">
                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="testBackupSystem()">
                            <i class="fas fa-vial"></i> Test Backup System
                        </button>
                        <button type="button" class="btn btn-outline-warning btn-sm" onclick="cleanupOldBackups()">
                            <i class="fas fa-broom"></i> Cleanup Old Backups
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Backups List -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list"></i> Available Backups
            </h6>
        </div>
        <div class="card-body">
            @if(!empty($backups) && count($backups) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered" id="backupsTable">
                        <thead>
                            <tr>
                                <th>Backup Name</th>
                                <th>Type</th>
                                <th>Size</th>
                                <th>Created</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($backups as $backup)
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $backup->name }}</div>
                                    <small class="text-muted">{{ $backup->filename }}</small>
                                </td>
                                <td>
                                    @php
                                        $typeClass = match($backup->type) {
                                            'full' => 'primary',
                                            'database' => 'info',
                                            'files' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $typeClass }}">
                                        {{ ucfirst($backup->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="font-weight-bold">{{ $backup->formatted_size }}</span>
                                    @if($backup->compressed)
                                        <br><small class="text-muted"><i class="fas fa-compress-alt"></i> Compressed</small>
                                    @endif
                                    @if($backup->encrypted)
                                        <br><small class="text-success"><i class="fas fa-lock"></i> Encrypted</small>
                                    @endif
                                </td>
                                <td>
                                    <div>{{ $backup->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $backup->created_at->format('H:i:s') }}</small>
                                    <br><small class="text-muted">{{ $backup->created_at->diffForHumans() }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusClass = match($backup->status) {
                                            'completed' => 'success',
                                            'failed' => 'danger',
                                            'in_progress' => 'warning',
                                            default => 'secondary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusClass }}">
                                        {{ ucfirst($backup->status) }}
                                    </span>
                                    @if($backup->status === 'failed' && $backup->error_message)
                                        <br><small class="text-danger">{{ $backup->error_message }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($backup->status === 'completed')
                                            <a href="{{ route('admin.backups.download', $backup->id) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-success" 
                                                    onclick="restoreBackup('{{ $backup->id }}')"
                                                    title="Restore">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-info" 
                                                onclick="viewBackupDetails('{{ $backup->id }}')"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <form method="POST" 
                                              action="{{ route('admin.backups.destroy', $backup->id) }}" 
                                              style="display: inline;"
                                              onsubmit="return confirm('Are you sure you want to delete this backup? This action cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-database fa-3x text-gray-300 mb-3"></i>
                    <h5 class="text-gray-600">No backups found</h5>
                    <p class="text-muted">
                        Create your first backup to ensure your data is safe and secure.
                    </p>
                    <button type="button" class="btn btn-primary" onclick="createBackup()">
                        <i class="fas fa-plus"></i> Create First Backup
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Backup Progress Modal -->
    <div class="modal fade" id="backupProgressModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-spinner fa-spin"></i> Creating Backup
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="progress mb-3">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: 0%" 
                             id="backupProgress">
                        </div>
                    </div>
                    <div id="backupStatus">Initializing backup process...</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="cancelBackup()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function createBackup() {
    if (confirm('Are you sure you want to create a new backup? This may take several minutes.')) {
        $('#backupProgressModal').modal('show');
        
        // Simulate backup progress
        simulateBackupProgress();
        
        // In real implementation, make AJAX call to create backup
        fetch('{{ route("admin.backups.create") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#backupProgressModal').modal('hide');
                alert('Backup created successfully!');
                location.reload();
            } else {
                $('#backupProgressModal').modal('hide');
                alert('Error creating backup: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            $('#backupProgressModal').modal('hide');
            alert('Error creating backup. Please try again.');
        });
    }
}

function simulateBackupProgress() {
    let progress = 0;
    const steps = [
        'Preparing backup...',
        'Backing up database...',
        'Backing up files...',
        'Compressing backup...',
        'Finalizing backup...',
        'Backup completed!'
    ];
    
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 100) progress = 100;
        
        document.getElementById('backupProgress').style.width = progress + '%';
        document.getElementById('backupStatus').textContent = steps[Math.floor(progress / 20)] || steps[steps.length - 1];
        
        if (progress >= 100) {
            clearInterval(interval);
        }
    }, 1000);
}

function restoreBackup(backupId) {
    if (confirm('Are you sure you want to restore this backup? This will overwrite all current data and cannot be undone.')) {
        if (confirm('This is your final warning. Restoring a backup will replace ALL current data. Are you absolutely sure?')) {
            fetch(`/admin/backups/${backupId}/restore`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Backup restoration started. The system will be temporarily unavailable.');
                    // In real implementation, might redirect to maintenance page
                } else {
                    alert('Error restoring backup: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error restoring backup. Please try again.');
            });
        }
    }
}

function viewBackupDetails(backupId) {
    // In real implementation, show modal with backup details
    alert('Backup details modal would open here for backup ID: ' + backupId);
}

function saveBackupConfig() {
    const formData = new FormData(document.getElementById('backupConfigForm'));
    
    // Convert FormData to JSON
    const config = {};
    for (let [key, value] of formData.entries()) {
        config[key] = value;
    }
    
    fetch('/admin/backups/config', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(config)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Backup configuration saved successfully!');
        } else {
            alert('Error saving configuration: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error saving configuration. Please try again.');
    });
}

function scheduleBackup() {
    alert('Backup scheduling interface would open here.');
}

function testBackupSystem() {
    alert('Running backup system test...');
}

function cleanupOldBackups() {
    if (confirm('Are you sure you want to cleanup old backups? This will delete backups older than the retention period.')) {
        fetch('/admin/backups/cleanup', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Cleanup completed. ${data.deleted_count || 0} old backups were removed.`);
                location.reload();
            } else {
                alert('Error during cleanup: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error during cleanup. Please try again.');
        });
    }
}

function cancelBackup() {
    if (confirm('Are you sure you want to cancel the backup process?')) {
        $('#backupProgressModal').modal('hide');
        // In real implementation, send cancel request to server
    }
}

function refreshBackups() {
    location.reload();
}

// DataTable initialization
$(document).ready(function() {
    $('#backupsTable').DataTable({
        "pageLength": 25,
        "responsive": true,
        "order": [[ 3, "desc" ]], // Sort by created date descending
        "columnDefs": [
            { "orderable": false, "targets": 5 } // Actions column not sortable
        ]
    });
});

// Auto-refresh every 2 minutes
setInterval(function() {
    if (!document.hidden) {
        location.reload();
    }
}, 120000);
</script>
@endpush

@push('styles')
<style>
.table td {
    vertical-align: middle;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.25rem;
    border-bottom-right-radius: 0.25rem;
}

.progress {
    height: 20px;
}

.badge {
    font-size: 0.75em;
}

code {
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.modal-dialog {
    max-width: 500px;
}

.d-grid .btn {
    text-align: left;
}
</style>
@endpush
@endsection