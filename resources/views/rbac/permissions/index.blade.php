@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                    <h4 class="fw-bold mb-0">Permissions</h4>
                    <form action="{{ route('rbac.permissions.sync') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('This will scan all routes and update permissions. Continue?')">
                            <i class="fas fa-sync"></i> Sync Permissions
                        </button>
                    </form>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
                    @endif
                    <div class="mb-3">
                        <input type="text" id="searchPermissions" class="form-control" placeholder="Search permissions..." onkeyup="filterPermissions(this.value)">
                    </div>
                    <div class="row" id="permissionsContainer">
                        @foreach($modules as $module => $actions)
                            @php $modulePerms = $permissions->get($module, collect()); @endphp
                            @if($modulePerms->count())
                            <div class="col-md-6 col-lg-4 mb-3 permission-module" data-module="{{ strtolower($module) }}">
                                <div class="card border h-100">
                                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                        <strong>{{ $module }}</strong>
                                        <span class="badge bg-secondary">{{ $modulePerms->count() }}</span>
                                    </div>
                                    <div class="card-body py-2">
                                        @foreach($modulePerms as $perm)
                                            <div class="permission-item" data-name="{{ strtolower($perm->name) }}">
                                                <span class="badge bg-light text-dark border me-1 mb-1">{{ $perm->action }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
function filterPermissions(value) {
    const q = value.toLowerCase();
    document.querySelectorAll('.permission-module').forEach(card => {
        const module = card.dataset.module;
        const items = card.querySelectorAll('.permission-item');
        let moduleMatch = module.includes(q);
        items.forEach(item => {
            const match = item.dataset.name.includes(q);
            item.style.display = (match || moduleMatch) ? '' : 'none';
            if (q && !match && !moduleMatch) item.style.display = 'none';
        });
        const visible = [...items].some(el => el.style.display !== 'none');
        card.style.display = (q === '' || moduleMatch || visible) ? '' : 'none';
    });
}
</script>
@include('admin_panel.include.footer_include')
