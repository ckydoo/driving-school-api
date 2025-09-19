{{-- resources/views/components/school-selector.blade.php --}}

@if(Auth::user()->isSuperAdmin())
<div class="school-selector-widget mb-3">
    <div class="card border-primary">
        <div class="card-header bg-primary text-white py-2">
            <h6 class="mb-0">
                <i class="fas fa-school"></i> School Selector
                <small class="float-end">Super Admin Mode</small>
            </h6>
        </div>
        <div class="card-body py-3">
            <form method="GET" action="{{ url()->current() }}" id="schoolSelectorForm" class="row g-2">
                {{-- Preserve existing query parameters --}}
                @foreach(request()->except(['school_filter', 'page']) as $key => $value)
                    @if(is_array($value))
                        @foreach($value as $item)
                            <input type="hidden" name="{{ $key }}[]" value="{{ $item }}">
                        @endforeach
                    @else
                        <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                    @endif
                @endforeach

                <div class="col-md-6">
                    <select name="school_filter" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">🌐 All Schools (System-wide View)</option>
                        @foreach(\App\Models\School::orderBy('name')->get() as $school)
                            <option value="{{ $school->id }}"
                                    {{ request('school_filter') == $school->id ? 'selected' : '' }}>
                                {{ $school->name }}
                                <span class="text-muted">({{ $school->city }})</span>
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6">
                    <div class="btn-group w-100" role="group">
                        @if(request('school_filter'))
                            @php $selectedSchool = \App\Models\School::find(request('school_filter')) @endphp
                            @if($selectedSchool)
                                <a href="{{ route('admin.schools.show', $selectedSchool) }}"
                                   class="btn btn-outline-info btn-sm">
                                    <i class="fas fa-eye"></i> View School
                                </a>
                                <a href="{{ route('admin.schools.login-as', $selectedSchool) }}"
                                   class="btn btn-outline-warning btn-sm"
                                   onclick="return confirm('Login as {{ $selectedSchool->name }} admin?')">
                                    <i class="fas fa-sign-in-alt"></i> Login As
                                </a>
                            @endif
                        @else
                            <a href="{{ route('admin.schools.index') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-cog"></i> Manage Schools
                            </a>
                            <a href="{{ route('admin.schools.create') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-plus"></i> Add School
                            </a>
                        @endif
                    </div>
                </div>
            </form>

            {{-- Current Filter Display --}}
            @if(request('school_filter'))
                @php $selectedSchool = \App\Models\School::find(request('school_filter')) @endphp
                @if($selectedSchool)
                    <div class="mt-2 p-2 bg-light rounded">
                        <small class="text-muted d-block">Currently viewing:</small>
                        <strong class="text-primary">
                            <i class="fas fa-filter"></i> {{ $selectedSchool->name }}
                        </strong>
                        <span class="badge badge-{{ $selectedSchool->status === 'active' ? 'success' : 'warning' }} ms-2">
                            {{ ucfirst($selectedSchool->status) }}
                        </span>
                        <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary ms-2">
                            <i class="fas fa-times"></i> Clear Filter
                        </a>
                    </div>
                @endif
            @else
                <div class="mt-2 p-2 bg-light rounded">
                    <small class="text-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>System-wide view:</strong> You're seeing data from all schools.
                        Select a specific school above to filter results.
                    </small>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.school-selector-widget .card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.school-selector-widget .badge-success {
    background-color: #28a745 !important;
}
.school-selector-widget .badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}
</style>
@endif

{{-- Usage in your views: --}}
{{-- @include('components.school-selector') --}}
