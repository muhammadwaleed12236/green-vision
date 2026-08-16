@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="fw-bold mb-0">Users</h4>
                    <a href="{{ route('rbac.users.create') }}" class="btn btn-success btn-sm"><i class="fas fa-plus"></i> Create User</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif

                    <form method="GET" class="row g-2 mb-3">
                        <div class="col-md-4"><input type="text" name="search" class="form-control" placeholder="Search name, username, email..." value="{{ request('search') }}"></div>
                        <div class="col-md-3">
                            <select name="role_id" class="form-control">
                                <option value="">All Roles</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-outline-secondary me-1" type="submit"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('rbac.users.index') }}" class="btn btn-outline-danger"><i class="fas fa-undo"></i> Reset</a>
                        </div>
                    </form>

                    <div class="table-responsive rbac-users-wrap">
                        <style>
                            .rbac-users-wrap { overflow-x: hidden; }
                            .rbac-users-wrap .table { width: 100%; table-layout: fixed; margin-bottom: 0; }
                            .rbac-users-wrap .table thead th,
                            .rbac-users-wrap .table tbody td { white-space: nowrap; vertical-align: middle; overflow: hidden; text-overflow: ellipsis; padding-left: 6px; padding-right: 6px; }
                            .rbac-users-wrap .table thead th:nth-child(1), .rbac-users-wrap .table tbody td:nth-child(1) { width: 9%; }
                            .rbac-users-wrap .table thead th:nth-child(2), .rbac-users-wrap .table tbody td:nth-child(2) { width: 13%; }
                            .rbac-users-wrap .table thead th:nth-child(3), .rbac-users-wrap .table tbody td:nth-child(3) { width: 13%; }
                            .rbac-users-wrap .table thead th:nth-child(4), .rbac-users-wrap .table tbody td:nth-child(4) { width: 8%; }
                            .rbac-users-wrap .table thead th:nth-child(5), .rbac-users-wrap .table tbody td:nth-child(5) { width: 10%; }
                            .rbac-users-wrap .table thead th:nth-child(6), .rbac-users-wrap .table tbody td:nth-child(6) { width: 14%; }
                            .rbac-users-wrap .table thead th:nth-child(7), .rbac-users-wrap .table tbody td:nth-child(7) { width: 11%; }
                            .rbac-users-wrap .table thead th:nth-child(8), .rbac-users-wrap .table tbody td:nth-child(8) { width: 22%; }
                            .rbac-users-wrap .table tbody tr td:last-child .btn { padding: .2rem .45rem; font-size: .75rem; }
                            .rbac-users-wrap .table tbody td .badge { padding: .3em .55em; font-size: .72em; }
                            .rbac-users-wrap .table tbody tr td:first-child .d-flex { flex-wrap: nowrap; }
                            .rbac-users-wrap .table tbody tr td:first-child .d-flex > div:last-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; }
                            @media (max-width: 767.98px) {
                                .rbac-users-wrap { overflow: hidden; }
                                .rbac-users-wrap .table { display: block; width: 100%; min-width: 0; }
                                .rbac-users-wrap .table tbody { display: block; width: 100%; }
                                .rbac-users-wrap thead { display: none; }
                                .rbac-users-wrap tbody tr {
                                    display: block;
                                    background: #fff;
                                    border: 1px solid #e9ecef;
                                    border-radius: 8px;
                                    padding: 12px;
                                    margin-bottom: 12px;
                                    box-shadow: 0 1px 3px rgba(0, 0, 0, .08);
                                }
                                .rbac-users-wrap tbody tr td {
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
                                .rbac-users-wrap tbody tr td::before {
                                    content: attr(data-label);
                                    font-weight: 600;
                                    color: #6c757d;
                                    flex-shrink: 0;
                                }
                                .rbac-users-wrap tbody tr td:not(:last-child) { border-bottom: 1px dashed #dee2e6; }
                                .rbac-users-wrap tbody tr td:last-child { padding-bottom: 2px; }
                                .rbac-users-wrap tbody tr td[colspan] {
                                    display: block;
                                    text-align: center;
                                    border: none;
                                }
                                .rbac-users-wrap tbody tr td[colspan]::before { content: none; }
                                .rbac-users-wrap .table tbody tr td:last-child .btn { padding: .25rem .5rem; font-size: .8rem; }
                                .rbac-users-wrap .table tbody tr td:first-child .d-flex > div:last-child { overflow: visible; text-overflow: clip; }
                            }
                        </style>
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>User</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Last Login</th>
                                    <th>Created</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                <tr>
                                    <td data-label="User">
                                        <div class="d-flex align-items-center">
                                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center me-2" style="width:36px;height:36px;font-size:14px;">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                            <div>{{ $user->name }}</div>
                                        </div>
                                    </td>
                                    <td data-label="Username">{{ $user->username ?? '-' }}</td>
                                    <td data-label="Email">{{ $user->email }}</td>
                                    <td data-label="Role"><span class="badge bg-primary">{{ $user->role->name ?? 'N/A' }}</span></td>
                                    <td data-label="Status">
                                        <a href="{{ route('rbac.users.toggle-status', $user) }}" class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }} text-decoration-none">
                                            {{ $user->status ?? 'active' }}
                                        </a>
                                    </td>
                                    <td data-label="Last Login">{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() : 'Never' }}</td>
                                    <td data-label="Created">{{ $user->created_at->format('d M Y') }}</td>
                                    <td class="text-end" data-label="Actions">
                                        <a href="{{ route('rbac.users.show', $user) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('rbac.users.edit', $user) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                        <button class="btn btn-sm btn-outline-warning" data-bs-toggle="modal" data-bs-target="#resetPwdModal{{ $user->id }}"><i class="fas fa-key"></i></button>
                                        <form action="{{ route('rbac.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                <div class="modal fade" id="resetPwdModal{{ $user->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('rbac.users.reset-password', $user) }}" class="modal-content" autocomplete="off">
                                            @csrf
                                            <div class="modal-header"><h5 class="modal-title">Reset Password - {{ $user->name }}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">New Password</label>
                                                    <div class="input-group">
                                                        <input type="password" id="passwordReset{{ $user->id }}" name="password" class="form-control" required autocomplete="new-password">
                                                        <button class="btn btn-outline-secondary" type="button" onclick="toggleResetPasswordVisibility({{ $user->id }})"><i class="fas fa-eye" id="toggleIconReset{{ $user->id }}"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer"><button type="submit" class="btn btn-primary">Reset</button></div>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <tr><td colspan="8" class="text-center text-muted py-4">No users found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end">{{ $users->withQueryString()->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function toggleResetPasswordVisibility(userId) {
    const pwd = document.getElementById('passwordReset' + userId);
    const icon = document.getElementById('toggleIconReset' + userId);
    if (pwd.type === 'password') {
        pwd.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        pwd.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>
@include('admin_panel.include.footer_include')
