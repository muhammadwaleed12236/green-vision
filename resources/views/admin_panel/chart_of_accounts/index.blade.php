@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    
<div class="page-wrapper">
    <div class="content">
        <div class="page-header coa-head">
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

        <style>
            .coa-head .page-btn {
                flex-wrap: wrap;
                gap: 8px;
                justify-content: center;
            }
            .coa-wrap {
                overflow-x: hidden;
            }
            .coa-wrap .table {
                table-layout: fixed;
                width: 100%;
                min-width: 0;
            }
            .coa-wrap .table th,
            .coa-wrap .table td {
                white-space: normal;
                word-break: break-word;
                overflow-wrap: break-word;
            }
            .coa-wrap .table th:nth-child(1) { width: 16%; }
            .coa-wrap .table th:nth-child(2) { width: 24%; }
            .coa-wrap .table th:nth-child(3) { width: 12%; }
            .coa-wrap .table th:nth-child(4) { width: 10%; }
            .coa-wrap .table th:nth-child(5) { width: 10%; }
            .coa-wrap .table th:nth-child(6) { width: 28%; }
            .coa-wrap .coa-val {
                display: contents;
            }
            .coa-wrap .table .coa-actions a,
            .coa-wrap .table .coa-actions .btn {
                white-space: nowrap;
            }

            @media (max-width: 767.98px) {
                .coa-wrap .table,
                .coa-wrap .table tbody,
                .coa-wrap .table tbody tr {
                    display: block;
                }
                .coa-wrap .table thead {
                    display: none;
                }
                .coa-wrap .table tbody tr {
                    margin-bottom: 12px;
                    border: 1px solid #eef2f7 !important;
                    border-radius: 8px;
                    background: #fff;
                }
                .coa-wrap .table tbody tr td {
                    display: flex !important;
                    width: 100% !important;
                    align-items: center;
                    justify-content: space-between;
                    gap: 10px;
                    padding: 7px 10px !important;
                    border: 0 !important;
                    border-bottom: 1px dashed #eef2f7 !important;
                    text-align: right;
                }
                .coa-wrap .table tbody tr td:last-child {
                    border-bottom: 0 !important;
                }
                .coa-wrap .table tbody tr td[data-label]::before {
                    content: attr(data-label);
                    flex-shrink: 0;
                    color: #94a3b8;
                    font-size: 0.68rem;
                    font-weight: 700;
                    text-transform: uppercase;
                    letter-spacing: 0.05em;
                    text-align: left;
                }
                .coa-wrap .table tbody tr td .coa-val {
                    display: block;
                    flex: 1 1 auto;
                    min-width: 0;
                    text-align: right;
                    overflow-wrap: break-word;
                }
                .coa-wrap .table tbody tr td.coa-blank {
                    display: none;
                }
                .coa-wrap .table tbody tr td.coa-actions .coa-val {
                    display: flex;
                    justify-content: flex-end;
                    flex-wrap: wrap;
                    gap: 8px;
                    align-items: center;
                }
            }
        </style>

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
                <div class="table-responsive coa-wrap">
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
                                    <td colspan="5" class="fw-bold text-primary" data-label="Category">
                                        <span class="coa-val">
                                            {{ $category->name }}
                                            @if($category->description)
                                                <span class="text-muted small fw-normal ms-2">({{ $category->description }})</span>
                                            @endif
                                        </span>
                                    </td>
                                    <td class="text-end coa-actions" data-label="Actions">
                                        <span class="coa-val">
                                            <a class="me-3" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editCategoryModal{{ $category->id }}">
                                                <img src="{{ asset('assets/img/icons/edit.svg') }}" alt="img">
                                            </a>
                                        </span>
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
                                    <td class="coa-blank" data-label="Category"></td>
                                    <td data-label="Account">
                                        <span class="coa-val">
                                            {{ $account->name }}
                                            <div class="small text-muted mt-1">Opening: {{ number_format($account->opening_balance, 2) }}</div>
                                        </span>
                                    </td>
                                    <td class="fw-bold" data-label="Current Balance"><span class="coa-val">{{ number_format($account->calculated_balance, 2) }}</span></td>
                                    <td data-label="Type"><span class="coa-val"><span class="badge {{ $account->balance_type == 'debit' ? 'bg-info' : 'bg-secondary' }}">{{ ucfirst($account->balance_type) }}</span></span></td>
                                    <td data-label="Status">
                                        <span class="coa-val">
                                            <span class="badge {{ $account->status ? 'bg-success' : 'bg-danger' }}">
                                                {{ $account->status ? 'Active' : 'Inactive' }}
                                            </span>
                                        </span>
                                    </td>
                                    <td class="text-end coa-actions" data-label="Actions">
                                        <span class="coa-val">
                                            <a class="me-3" href="{{ route('chart-of-accounts.ledger', $account->id) }}" title="View Ledger">
                                                <i class="fas fa-book text-info" style="font-size: 1.2rem;"></i>
                                            </a>
                                            <a class="me-3" href="javascript:void(0);" data-bs-toggle="modal" data-bs-target="#editAccountModal{{ $account->id }}">
                                                <img src="{{ asset('assets/img/icons/edit.svg') }}" alt="img">
                                            </a>
                                            <form action="{{ route('chart-of-accounts.account.toggle', $account->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-{{ $account->status ? 'danger' : 'success' }}" title="{{ $account->status ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $account->status ? 'ban' : 'check' }}"></i> {{ $account->status ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        </span>
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
