@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    
<div class="page-wrapper">
    <div class="content">
        <div class="page-header">
            <div class="page-title">
                <h4>Chart of Accounts</h4>
                <h6>Manage your financial accounts and categories</h6>
            </div>
            <div class="page-btn d-flex">
                <a class="btn btn-added me-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
                    <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="img" class="me-1">Add Category
                </a>
                <a class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addAccountModal">
                    <img src="{{ asset('assets/img/icons/plus.svg') }}" alt="img" class="me-1">Add Account
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th>Account Name</th>
                                <th>Current Balance</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($categories as $category)
                                <tr class="bg-light">
                                    <td colspan="5" class="fw-bold text-primary">
                                        {{ $category->name }}
                                        @if($category->description)
                                            <span class="text-muted small fw-normal ms-2">({{ $category->description }})</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a class="me-3" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                            <img src="{{ asset('assets/img/icons/edit.svg') }}" alt="img">
                                        </a>
                                    </td>
                                </tr>
                                
                                <!-- Edit Category Modal -->
                                <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editCategoryModalLabel{{ $category->id }}">Edit Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('chart-of-accounts.category.update', $category->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="name" value="{{ $category->name }}" required>
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="form-label">Description</label>
                                                        <textarea class="form-control" name="description" rows="3">{{ $category->description }}</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Category</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                @forelse($category->accounts as $account)
                                <tr>
                                    <td></td>
                                    <td>
                                        {{ $account->name }}
                                        <div class="small text-muted mt-1">Opening: {{ number_format($account->opening_balance, 2) }}</div>
                                    </td>
                                    <td class="fw-bold">{{ number_format($account->calculated_balance, 2) }}</td>
                                    <td><span class="badge {{ $account->balance_type == 'debit' ? 'bg-info' : 'bg-secondary' }}">{{ ucfirst($account->balance_type) }}</span></td>
                                    <td>
                                        <span class="badge {{ $account->status ? 'bg-success' : 'bg-danger' }}">
                                            {{ $account->status ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a class="me-3" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editAccountModal{{ $account->id }}">
                                            <img src="{{ asset('assets/img/icons/edit.svg') }}" alt="img">
                                        </a>
                                        <form action="{{ route('chart-of-accounts.account.toggle', $account->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-{{ $account->status ? 'danger' : 'success' }}" title="{{ $account->status ? 'Deactivate' : 'Activate' }}">
                                                <i class="fas fa-{{ $account->status ? 'ban' : 'check' }}"></i> {{ $account->status ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Edit Account Modal -->
                                <div class="modal fade" id="editAccountModal{{ $account->id }}" tabindex="-1" aria-labelledby="editAccountModalLabel{{ $account->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content text-start">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editAccountModalLabel{{ $account->id }}">Edit Account</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('chart-of-accounts.account.update', $account->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Account Category <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="account_category_id" required>
                                                            @foreach($categories as $cat)
                                                            <option value="{{ $cat->id }}" {{ $cat->id == $account->account_category_id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Account Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" name="name" value="{{ $account->name }}" required>
                                                    </div>
                                                    <div class="form-group mb-3">
                                                        <label class="form-label">Opening Balance</label>
                                                        <input type="number" step="0.01" class="form-control" name="opening_balance" value="{{ $account->opening_balance }}">
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="form-label">Balance Type <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="balance_type" required>
                                                            <option value="debit" {{ $account->balance_type == 'debit' ? 'selected' : '' }}>Debit</option>
                                                            <option value="credit" {{ $account->balance_type == 'credit' ? 'selected' : '' }}>Credit</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update Account</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No accounts under this category</td>
                                </tr>
                                @endforelse
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">No categories or accounts found. Click "Add Category" to get started.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Add Category</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chart-of-accounts.category.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Bank & Cash">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Optional"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Account Modal -->
<div class="modal fade" id="addAccountModal" tabindex="-1" aria-labelledby="addAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addAccountModalLabel">Add Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('chart-of-accounts.account.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="form-label">Account Category <span class="text-danger">*</span></label>
                        <select class="form-select" name="account_category_id" required>
                            <option value="">Select Category</option>
                            @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Account Name / Head <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. HBL Bank">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" step="0.01" class="form-control" name="opening_balance" value="0">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Balance Type <span class="text-danger">*</span></label>
                        <select class="form-select" name="balance_type" required>
                            <option value="debit">Debit</option>
                            <option value="credit">Credit</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
@include('admin_panel.include.footer_include')
