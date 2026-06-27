@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="fw-bold mb-0">Roles</h4>
                    <a href="{{ route('rbac.roles.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Create Role</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Role Name</th>
                                    <th>Description</th>
                                    <th class="text-center">Users</th>
                                    <th class="text-center">Permissions</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $role)
                                <tr>
                                    <td><strong>{{ $role->name }}</strong></td>
                                    <td>{{ $role->description ?? '-' }}</td>
                                    <td class="text-center"><span class="badge bg-info">{{ $role->users_count }}</span></td>
                                    <td class="text-center"><span class="badge bg-secondary">{{ $role->permissions_count }}</span></td>
                                    <td>{{ $role->created_at->format('d M Y') }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('rbac.roles.show', $role) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('rbac.roles.edit', $role) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('rbac.roles.duplicate', $role) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-secondary"><i class="fas fa-copy"></i></button>
                                        </form>
                                        <form action="{{ route('rbac.roles.destroy', $role) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this role?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" {{ $role->slug === 'super-admin' ? 'disabled' : '' }}><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="6" class="text-center text-muted py-4">No roles defined.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
