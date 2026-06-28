@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">Role: {{ $role->name }}</h4>
                    <div>
                        <a href="{{ route('rbac.roles.edit', $role) }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                        <a href="{{ route('rbac.roles.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4"><strong>Name:</strong> {{ $role->name }}</div>
                        <div class="col-md-4"><strong>Users:</strong> {{ $role->users_count }}</div>
                        <div class="col-md-4"><strong>Permissions:</strong> {{ $role->permissions_count }}</div>
                        @if($role->description)
                            <div class="col-12 mt-2"><strong>Description:</strong> {{ $role->description }}</div>
                        @endif
                    </div>
                    <h5 class="fw-bold mb-3">Assigned Permissions</h5>
                    @foreach($modules as $module => $actions)
                        @php $modulePerms = $permissions->get($module, collect()); @endphp
                        @if($modulePerms->count())
                        <div class="card mb-3 border">
                            <div class="card-header bg-light py-2"><strong>{{ $module }}</strong></div>
                            <div class="card-body py-2">
                                @php $assigned = $modulePerms->whereIn('id', $rolePermissions); @endphp
                                @if($assigned->count())
                                    @foreach($assigned as $perm)
                                        <span class="badge bg-success me-1 mb-1">{{ $perm->action }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted small">No permissions assigned.</span>
                                @endif
                            </div>
                        </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
