@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="fw-bold mb-0">User Details</h4>
                    <a href="{{ route('rbac.users.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 text-center mb-3">
                            <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center mx-auto" style="width:100px;height:100px;font-size:36px;">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <h5 class="mt-2">{{ $user->name }}</h5>
                        </div>
                        <div class="col-md-9">
                            <table class="table table-bordered">
                                <tr><th style="width:200px;">Username</th><td>{{ $user->username ?? '-' }}</td></tr>
                                <tr><th>Email</th><td>{{ $user->email }}</td></tr>
                                <tr><th>Phone</th><td>{{ $user->phone ?? '-' }}</td></tr>
                                <tr><th>Role</th><td><span class="badge bg-primary">{{ $user->role->name ?? 'N/A' }}</span></td></tr>
                                <tr><th>Status</th><td><span class="badge {{ $user->status === 'active' ? 'bg-success' : 'bg-secondary' }}">{{ $user->status ?? 'active' }}</span></td></tr>
                                <tr><th>Last Login</th><td>{{ $user->last_login_at ? \Carbon\Carbon::parse($user->last_login_at)->format('d M Y g:i A') : 'Never' }}</td></tr>
                                <tr><th>Created</th><td>{{ $user->created_at->format('d M Y g:i A') }}</td></tr>
                            </table>
                            <div class="mt-3">
                                <a href="{{ route('rbac.users.edit', $user) }}" class="btn btn-primary"><i class="fas fa-edit"></i> Edit</a>
                                <form action="{{ route('rbac.users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
