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
                    <div class="table-responsive rbac-roles-wrap">
                        <style>
                            .rbac-roles-wrap { overflow-x: hidden; }
                            .rbac-roles-wrap .table { width: 100%; table-layout: fixed; margin-bottom: 0; }
                            .rbac-roles-wrap .table thead th,
                            .rbac-roles-wrap .table tbody td { white-space: nowrap; vertical-align: middle; overflow: hidden; text-overflow: ellipsis; padding-left: 8px; padding-right: 8px; }
                            .rbac-roles-wrap .table thead th:nth-child(1), .rbac-roles-wrap .table tbody td:nth-child(1) { width: 18%; }
                            .rbac-roles-wrap .table thead th:nth-child(2), .rbac-roles-wrap .table tbody td:nth-child(2) { width: 17%; }
                            .rbac-roles-wrap .table thead th:nth-child(3), .rbac-roles-wrap .table tbody td:nth-child(3) { width: 10%; }
                            .rbac-roles-wrap .table thead th:nth-child(4), .rbac-roles-wrap .table tbody td:nth-child(4) { width: 17%; }
                            .rbac-roles-wrap .table thead th:nth-child(5), .rbac-roles-wrap .table tbody td:nth-child(5) { width: 16%; }
                            .rbac-roles-wrap .table thead th:nth-child(6), .rbac-roles-wrap .table tbody td:nth-child(6) { width: 22%; }
                            .rbac-roles-wrap .table tbody tr td:last-child .btn { padding: .2rem .45rem; font-size: .75rem; }
                            @media (max-width: 767.98px) {
                                .rbac-roles-wrap { overflow: hidden; }
                                .rbac-roles-wrap .table { display: block; width: 100%; min-width: 0; }
                                .rbac-roles-wrap .table tbody { display: block; width: 100%; }
                                .rbac-roles-wrap thead { display: none; }
                                .rbac-roles-wrap tbody tr {
                                    display: block;
                                    background: #fff;
                                    border: 1px solid #e9ecef;
                                    border-radius: 8px;
                                    padding: 12px;
                                    margin-bottom: 12px;
                                    box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
                                }
                                .rbac-roles-wrap tbody tr td {
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    width: auto !important;
                                    gap: 12px;
                                    padding: 6px 0;
                                    border: none;
                                    white-space: normal;
                                    overflow: visible;
                                    text-overflow: clip;
                                }
                                .rbac-roles-wrap tbody tr td::before {
                                    content: attr(data-label);
                                    font-weight: 600;
                                    color: #6c757d;
                                    flex-shrink: 0;
                                }
                                .rbac-roles-wrap tbody tr td:not(:last-child) { border-bottom: 1px dashed #dee2e6; }
                                .rbac-roles-wrap tbody tr td:last-child { padding-bottom: 2px; }
                                .rbac-roles-wrap tbody tr td[colspan] {
                                    display: block;
                                    text-align: center;
                                    border: none;
                                }
                                .rbac-roles-wrap tbody tr td[colspan]::before { content: none; }
                                .rbac-roles-wrap .table tbody tr td:last-child .btn { padding: .25rem .5rem; font-size: .8rem; }
                            }
                        </style>
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
                                    <td data-label="Role Name"><strong>{{ $role->name }}</strong></td>
                                    <td data-label="Description">{{ $role->description ?? '-' }}</td>
                                    <td class="text-center" data-label="Users"><span class="badge bg-info">{{ $role->users_count }}</span></td>
                                    <td class="text-center" data-label="Permissions"><span class="badge bg-secondary">{{ $role->permissions_count }}</span></td>
                                    <td data-label="Created">{{ $role->created_at->format('d M Y') }}</td>
                                    <td class="text-end" data-label="Actions">
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
