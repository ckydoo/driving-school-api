@extends('admin.layouts.app')

@section('title', 'Log Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-eye"></i> Log Details
        </h1>
        <div>
            <a href="{{ route('admin.logs.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Logs
            </a>
            <form method="POST" 
                  action="{{ route('admin.logs.destroy', $log->id) }}" 
                  style="display: inline;"
                  onsubmit="return confirm('Are you sure you want to delete this log entry?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <!-- Log Details Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle"></i> Log Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-primary">Basic Information</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Log ID:</strong></td>
                                    <td>{{ $log->id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Timestamp:</strong></td>
                                    <td>
                                        {{ $log->timestamp->format('F d, Y \a\t H:i:s T') }}
                                        <br>
                                        <small class="text-muted">{{ $log->timestamp->diffForHumans() }}</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>User:</strong></td>
                                    <td>
                                        <span class="font-weight-bold">{{ $log->user }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Action:</strong></td>
                                    <td>
                                        @php
                                            $actionClass = match(true) {
                                                str_contains(strtolower($log->action), 'login') => 'success',
                                                str_contains(strtolower($log->action), 'logout') => 'info',
                                                str_contains(strtolower($log->action), 'delete') => 'danger',
                                                str_contains(strtolower($log->action), 'create') => 'primary',
                                                str_contains(strtolower($log->action), 'update') => 'warning',
                                                default => 'secondary'
                                            };
                                        @endphp
                                        <span class="badge badge-{{ $actionClass }}">{{ $log->action }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-primary">Technical Details</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>IP Address:</strong></td>
                                    <td>
                                        <code>{{ $log->ip_address ?? '127.0.0.1' }}</code>
                                        <br>
                                        <small class="text-muted">
                                            @if($log->ip_address === '127.0.0.1')
                                                Local server
                                            @else
                                                External connection
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Session ID:</strong></td>
                                    <td>
                                        <code class="small">{{ $log->session_id ?? 'N/A' }}</code>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Request Method:</strong></td>
                                    <td>
                                        <span class="badge badge-info">{{ $log->method ?? 'GET' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>URL:</strong></td>
                                    <td>
                                        <code class="small">{{ $log->url ?? 'N/A' }}</code>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($log->details)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary">Action Details</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0">{{ $log->details }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($log->user_agent)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-primary">User Agent</h6>
                            <div class="bg-light p-3 rounded">
                                <code class="small">{{ $log->user_agent }}</code>
                            </div>
                            <div class="mt-2">
                                @php
                                    $userAgent = $log->user_agent;
                                    $browser = 'Unknown';
                                    $os = 'Unknown';
                                    
                                    // Simple browser detection
                                    if (str_contains($userAgent, 'Chrome')) $browser = 'Chrome';
                                    elseif (str_contains($userAgent, 'Firefox')) $browser = 'Firefox';
                                    elseif (str_contains($userAgent, 'Safari')) $browser = 'Safari';
                                    elseif (str_contains($userAgent, 'Edge')) $browser = 'Edge';
                                    
                                    // Simple OS detection
                                    if (str_contains($userAgent, 'Windows')) $os = 'Windows';
                                    elseif (str_contains($userAgent, 'Mac OS')) $os = 'macOS';
                                    elseif (str_contains($userAgent, 'Linux')) $os = 'Linux';
                                    elseif (str_contains($userAgent, 'Android')) $os = 'Android';
                                    elseif (str_contains($userAgent, 'iOS')) $os = 'iOS';
                                @endphp
                                <small class="text-muted">
                                    <strong>Browser:</strong> {{ $browser }} | 
                                    <strong>Operating System:</strong> {{ $os }}
                                </small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if(isset($log->metadata))
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-primary">Additional Metadata</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0">{{ json_encode($log->metadata, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar with related information -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-tools"></i> Quick Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.logs.index', ['search' => $log->user]) }}" 
                           class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user"></i> View All Logs from {{ $log->user }}
                        </a>
                        
                        <a href="{{ route('admin.logs.index', ['action_type' => strtolower(explode(' ', $log->action)[0])]) }}" 
                           class="btn btn-outline-info btn-sm">
                            <i class="fas fa-list"></i> View Similar Actions
                        </a>
                        
                        @if($log->ip_address && $log->ip_address !== '127.0.0.1')
                        <a href="{{ route('admin.logs.index', ['search' => $log->ip_address]) }}" 
                           class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-globe"></i> View Logs from This IP
                        </a>
                        @endif
                        
                        <button type="button" 
                                class="btn btn-outline-secondary btn-sm"
                                onclick="copyLogDetails()">
                            <i class="fas fa-copy"></i> Copy Log Details
                        </button>
                    </div>
                </div>
            </div>

            <!-- Log Statistics -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-chart-bar"></i> Log Statistics
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>User Activity Today:</strong></span>
                            <span class="badge badge-primary">5 actions</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>Similar Actions:</strong></span>
                            <span class="badge badge-info">12 times</span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <div class="d-flex justify-content-between">
                            <span><strong>From This IP:</strong></span>
                            <span class="badge badge-warning">8 requests</span>
                        </div>
                    </div>
                    <div>
                        <div class="d-flex justify-content-between">
                            <span><strong>Risk Level:</strong></span>
                            @php
                                $riskLevel = 'low';
                                $riskClass = 'success';
                                
                                // Simple risk assessment
                                if (str_contains(strtolower($log->action), 'delete') || 
                                    str_contains(strtolower($log->action), 'admin')) {
                                    $riskLevel = 'high';
                                    $riskClass = 'danger';
                                } elseif (str_contains(strtolower($log->action), 'update') || 
                                         str_contains(strtolower($log->action), 'create')) {
                                    $riskLevel = 'medium';
                                    $riskClass = 'warning';
                                }
                            @endphp
                            <span class="badge badge-{{ $riskClass }}">{{ ucfirst($riskLevel) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Related Logs -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history"></i> Recent Activity
                    </h6>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @php
                            // Mock related logs - in real implementation, fetch from database
                            $relatedLogs = collect([
                                (object)[
                                    'id' => $log->id - 1,
                                    'action' => 'User Login',
                                    'timestamp' => $log->timestamp->subMinutes(15),
                                    'user' => $log->user
                                ],
                                (object)[
                                    'id' => $log->id - 2,
                                    'action' => 'Profile Updated',
                                    'timestamp' => $log->timestamp->subHours(2),
                                    'user' => $log->user
                                ],
                                (object)[
                                    'id' => $log->id - 3,
                                    'action' => 'Settings Changed',
                                    'timestamp' => $log->timestamp->subHours(4),
                                    'user' => $log->user
                                ]
                            ]);
                        @endphp
                        
                        @foreach($relatedLogs as $relatedLog)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    @php
                                        $icon = match(true) {
                                            str_contains(strtolower($relatedLog->action), 'login') => 'fa-sign-in-alt text-success',
                                            str_contains(strtolower($relatedLog->action), 'update') => 'fa-edit text-warning',
                                            str_contains(strtolower($relatedLog->action), 'create') => 'fa-plus text-primary',
                                            str_contains(strtolower($relatedLog->action), 'delete') => 'fa-trash text-danger',
                                            default => 'fa-circle text-info'
                                        };
                                    @endphp
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="small text-muted">{{ $relatedLog->timestamp->format('H:i:s') }}</div>
                                    <div>{{ $relatedLog->action }}</div>
                                    <div class="small text-muted">by {{ $relatedLog->user }}</div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                        
                        <div class="text-center mt-3">
                            <a href="{{ route('admin.logs.index', ['search' => $log->user]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                View All Activity
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyLogDetails() {
    const logDetails = `
Log ID: {{ $log->id }}
Timestamp: {{ $log->timestamp->format('Y-m-d H:i:s T') }}
User: {{ $log->user }}
Action: {{ $log->action }}
IP Address: {{ $log->ip_address ?? 'N/A' }}
User Agent: {{ $log->user_agent ?? 'N/A' }}
Details: {{ $log->details ?? 'N/A' }}
    `.trim();
    
    navigator.clipboard.writeText(logDetails).then(function() {
        alert('Log details copied to clipboard!');
    }, function(err) {
        console.error('Could not copy text: ', err);
        
        // Fallback method
        const textArea = document.createElement('textarea');
        textArea.value = logDetails;
        document.body.appendChild(textArea);
        textArea.select();
        try {
            document.execCommand('copy');
            alert('Log details copied to clipboard!');
        } catch (err) {
            alert('Failed to copy log details');
        }
        document.body.removeChild(textArea);
    });
}

// Auto-refresh related logs every 30 seconds
setInterval(function() {
    // In a real implementation, you might update the related logs via AJAX
    // without refreshing the entire page
}, 30000);
</script>
@endpush

@push('styles')
<style>
.timeline-item {
    position: relative;
}

.timeline-item:not(:last-child)::after {
    content: '';
    position: absolute;
    left: 8px;
    top: 24px;
    bottom: -12px;
    width: 2px;
    background-color: #e3e6f0;
}

.table td {
    padding: 0.5rem 0;
    border: none;
    vertical-align: top;
}

.table td:first-child {
    width: 140px;
    font-weight: 500;
}

pre {
    font-size: 0.875rem;
    color: #495057;
    white-space: pre-wrap;
    word-wrap: break-word;
}

code {
    color: #6c757d;
    background-color: #f8f9fa;
    padding: 0.2rem 0.4rem;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.bg-light {
    background-color: #f8f9fa !important;
    border: 1px solid #e9ecef;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}

.badge {
    font-size: 0.75em;
}

.d-grid .btn {
    text-align: left;
}

.timeline {
    max-height: 400px;
    overflow-y: auto;
}
</style>
@endpush
@endsection