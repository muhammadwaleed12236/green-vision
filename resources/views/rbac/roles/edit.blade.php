@include('admin_panel.include.header_include')
<style>
.module-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    background: #fff;
    overflow: hidden;
}
.module-card:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    border-color: #cbd5e1;
}
.module-card .card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 20px;
    cursor: pointer;
    user-select: none;
    transition: background 0.2s ease;
}
.module-card .card-header:hover {
    background: #f1f5f9;
}
.permissions-body {
    padding: 20px;
    background: #fff;
}
.permission-badge-item {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 8px 14px;
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
}
.permission-badge-item:hover {
    background: #e2e8f0;
    border-color: #cbd5e1;
}
.permission-badge-item.active {
    background: rgba(40, 167, 69, 0.1);
    border-color: rgba(40, 167, 69, 0.3);
    color: #1e7e34;
}
.permission-checkbox {
    margin-right: 8px;
    width: 16px;
    height: 16px;
    cursor: pointer;
    accent-color: #28a745;
}
.select-all {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #28a745;
}
.chevron-icon {
    transition: transform 0.25s ease;
    color: #64748b;
}
.module-card.collapsed .permissions-body {
    display: none !important;
}
.module-card.collapsed .chevron-icon {
    transform: rotate(-90deg);
}
.sticky-bar {
    position: sticky;
    bottom: 0;
    z-index: 100;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border-top: 1px solid #e2e8f0;
    padding: 16px 24px;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.03);
    border-radius: 0 0 12px 12px;
}
.search-wrapper {
    position: relative;
}
.search-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
}
.search-input {
    padding-left: 40px;
    border-radius: 10px;
    border-color: #e2e8f0;
    transition: all 0.2s ease;
}
.search-input:focus {
    box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
    border-color: #28a745;
}
</style>
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white py-3 border-0"><h4 class="fw-bold text-dark mb-0"><i class="fas fa-user-shield text-success me-2"></i>Edit Role: {{ $role->name }}</h4></div>
                <div class="card-body pt-0">
                    <form method="POST" action="{{ route('rbac.roles.update', $role) }}" id="roleForm">
                        @csrf @method('PUT')
                        <div class="row mb-4">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-secondary">Role Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control form-control-lg rounded-3" placeholder="e.g. Sales Manager" value="{{ old('name', $role->name) }}" required>
                                @error('name') <small class="text-danger d-block mt-1">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-secondary">Description</label>
                                <textarea name="description" class="form-control rounded-3" rows="2" placeholder="Briefly describe this role's purpose...">{{ old('description', $role->description) }}</textarea>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-4">
                            <div class="search-wrapper flex-grow-1 max-w-md" style="max-width: 400px;">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" id="searchPerms" class="form-control search-input" placeholder="Search modules or permissions..." onkeyup="filterModules(this.value)">
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="expandAll(true)"><i class="fas fa-folder-open me-1"></i> Expand All</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary px-3 rounded-pill" onclick="expandAll(false)"><i class="fas fa-folder me-1"></i> Collapse All</button>
                            </div>
                        </div>

                        <div id="permissionsContainer" class="mb-5">
                            @foreach($modules as $module => $actions)
                                @php
                                    $modulePerms = $permissions->get($module, collect());
                                    $parentPerm = $modulePerms->firstWhere('action', 'All');
                                    $childPerms = $modulePerms->where('action', '!=', 'All');
                                @endphp
                                @if($modulePerms->count())
                                <div class="card module-card mb-3 permission-group" data-module="{{ strtolower($module) }}" id="card_{{ Str::slug($module) }}">
                                    <div class="card-header d-flex justify-content-between align-items-center" onclick="toggleModule('{{ Str::slug($module) }}')">
                                        <div class="d-flex align-items-center" onclick="event.stopPropagation()">
                                            @if($parentPerm)
                                                @php $parentChecked = in_array($parentPerm->id, $rolePermissions); @endphp
                                                <input type="checkbox" name="permissions[]" value="{{ $parentPerm->id }}" class="select-all me-3" id="selectAll_{{ Str::slug($module) }}" onclick="toggleModulePermissions(this, '{{ Str::slug($module) }}')" {{ $parentChecked ? 'checked' : '' }}>
                                            @else
                                                <input type="checkbox" class="select-all me-3" id="selectAll_{{ Str::slug($module) }}" onclick="toggleModulePermissions(this, '{{ Str::slug($module) }}')">
                                            @endif
                                            <span class="fs-5 fw-bold text-dark">{{ $module }}</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge rounded-pill bg-light text-secondary border px-2 py-1"><span id="moduleCount_{{ Str::slug($module) }}">0</span>/{{ $childPerms->count() }} selected</span>
                                            <i class="fas fa-chevron-down chevron-icon ms-2"></i>
                                        </div>
                                    </div>
                                    <div class="card-body permissions-body" id="body_{{ Str::slug($module) }}">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($childPerms as $perm)
                                                @php $hasIt = in_array($perm->id, $rolePermissions); @endphp
                                                <label class="permission-badge-item permission-item {{ $hasIt ? 'active' : '' }}" id="label_{{ $perm->id }}" data-name="{{ strtolower($perm->name) }}">
                                                    <input type="checkbox" name="permissions[]" value="{{ $perm->id }}"
                                                        class="permission-checkbox permission-{{ Str::slug($module) }}"
                                                        onchange="updateSelectAll('{{ Str::slug($module) }}', this)"
                                                        {{ $hasIt ? 'checked' : '' }}>
                                                    {{ $perm->action }}
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="sticky-bar d-flex justify-content-between align-items-center rounded-4">
                            <div>
                                <span class="badge bg-success rounded-pill px-3 py-2 fs-6"><span id="selectedCount" class="fw-bold">0</span> permissions selected</span>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary px-4 btn-lg rounded-3"><i class="fas fa-save me-1"></i> Update Role</button>
                                <a href="{{ route('rbac.roles.index') }}" class="btn btn-light px-4 btn-lg border rounded-3">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')
<script>
function toggleModule(slug) {
    const card = document.getElementById('card_' + slug);
    card.classList.toggle('collapsed');
}

function expandAll(expand) {
    document.querySelectorAll('.module-card').forEach(card => {
        if (expand) {
            card.classList.remove('collapsed');
        } else {
            card.classList.add('collapsed');
        }
    });
}

function toggleModulePermissions(selectAll, module) {
    document.querySelectorAll('.permission-' + module).forEach(cb => {
        cb.checked = selectAll.checked;
        const label = document.getElementById('label_' + cb.value);
        if (label) {
            if (cb.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        }
    });
    updateSelectedCount();
    updateModuleCount(module);
}

function updateSelectAll(module, cbElement) {
    if (cbElement) {
        const label = document.getElementById('label_' + cbElement.value);
        if (label) {
            if (cbElement.checked) {
                label.classList.add('active');
            } else {
                label.classList.remove('active');
            }
        }
    }
    const perms = document.querySelectorAll('.permission-' + module);
    const checked = document.querySelectorAll('.permission-' + module + ':checked').length;
    document.getElementById('selectAll_' + module).checked = checked === perms.length;
    updateSelectedCount();
    updateModuleCount(module);
}

function updateModuleCount(module) {
    const checked = document.querySelectorAll('.permission-' + module + ':checked').length;
    const counter = document.getElementById('moduleCount_' + module);
    if (counter) {
        counter.textContent = checked;
    }
}

function updateSelectedCount() {
    const total = document.querySelectorAll('.permission-checkbox:checked').length + document.querySelectorAll('.select-all:checked').length;
    document.getElementById('selectedCount').textContent = total;
}

function filterModules(value) {
    const q = value.toLowerCase();
    document.querySelectorAll('.permission-group').forEach(group => {
        const module = group.dataset.module;
        const items = group.querySelectorAll('.permission-item');
        let moduleMatch = module.includes(q);
        
        let visibleCount = 0;
        items.forEach(item => {
            const name = item.dataset.name;
            if (name.includes(q) || moduleMatch) {
                item.style.setProperty('display', 'inline-flex', 'important');
                visibleCount++;
            } else {
                item.style.setProperty('display', 'none', 'important');
            }
        });
        
        if (q === '' || moduleMatch || visibleCount > 0) {
            group.style.setProperty('display', 'block', 'important');
        } else {
            group.style.setProperty('display', 'none', 'important');
        }
    });
}

// Initialize on page load
document.querySelectorAll('.select-all').forEach(cb => {
    const module = cb.id.replace('selectAll_', '');
    updateSelectAll(module);
});
</script>
