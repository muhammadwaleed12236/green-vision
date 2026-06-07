@include('admin_panel.include.header_include')

<style>
    .price-container {
        background: #fff;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .price-header {
        background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        padding: 25px;
        color: #fff;
    }
    .price-header h4 {
        margin: 0;
        font-weight: 600;
    }
    .price-table {
        width: 100%;
    }
    .price-table th {
        background: #f8fafc;
        padding: 15px 20px;
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #e5e7eb;
        text-align: left;
    }
    .price-table td {
        padding: 15px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .price-table tr:hover {
        background: #f8fafc;
    }
    .price-tag {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        color: #fff;
        padding: 6px 15px;
        border-radius: 20px;
        font-weight: 700;
        font-size: 14px;
        display: inline-block;
    }
    .action-btn {
        width: 35px;
        height: 35px;
        border-radius: 8px;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .action-btn.edit { background: #dbeafe; color: #2563eb; }
    .action-btn.edit:hover { background: #2563eb; color: #fff; }
    .action-btn.delete { background: #fee2e2; color: #dc2626; }
    .action-btn.delete:hover { background: #dc2626; color: #fff; }
    .btn-add {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        border: none;
        color: #fff;
        padding: 10px 25px;
        border-radius: 25px;
        font-weight: 600;
        transition: all 0.3s;
    }
    .btn-add:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(34, 197, 94, 0.4);
        color: #fff;
    }
    .empty-state {
        padding: 60px 20px;
        text-align: center;
    }
    .empty-state i {
        font-size: 70px;
        color: #e5e7eb;
        margin-bottom: 20px;
    }
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
    .page-item.active .page-link {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    .page-link {
        color: #3b82f6;
    }
    .search-box {
        position: relative;
    }
    .search-box input {
        border-radius: 25px;
        padding-left: 45px;
        border: 2px solid #e5e7eb;
    }
    .search-box input:focus {
        border-color: #3b82f6;
        box-shadow: none;
    }
    .search-box i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content container-fluid">

            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title"><i class="fas fa-tags text-primary"></i> Price List</h3>
                    </div>
                    <div class="col-auto">
                        <button class="btn btn-add" onclick="openAddModal()">
                            <i class="fas fa-plus me-2"></i> Add Item
                        </button>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" class="form-control" id="searchInput" placeholder="Search product..." onkeyup="searchTable()">
                    </div>
                </div>
                <div class="col-md-8 text-end">
                    <span class="text-muted">Total Items: <strong>{{ $priceLists->total() }}</strong></span>
                </div>
            </div>

            <!-- Price List Table -->
            <div class="price-container">
                <table class="price-table" id="priceTable">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th width="50%">Product Name</th>
                            <th width="25%">Price</th>
                            <th width="20%" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($priceLists as $index => $item)
                        <tr>
                            <td>{{ $priceLists->firstItem() + $index }}</td>

                            <td>
                                <strong>{{ $item->product_name }}</strong>
                                @if($item->description)
                                    <br><small class="text-muted">{{ $item->description }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="price-tag">Rs. {{ number_format($item->rate, 0) }}</span>
                                @if($item->unit)
                                    <small class="text-muted ms-2">/ {{ $item->unit }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <button class="action-btn edit" onclick="editItem({{ $item->id }})" title="Edit">
                                    <i class="fas fa-pen"></i>
                                </button>
                                <button class="action-btn delete" onclick="deleteItem({{ $item->id }})" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <i class="fas fa-tags"></i>
                                    <h5 class="text-muted">No Price Items Found</h5>
                                    <p class="text-muted">Click "Add Item" to create your first price entry</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $priceLists->links() }}
            </div>

        </div>
    </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal fade" id="priceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
            <div class="modal-header" style="background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff;">
                <h5 class="modal-title" id="modalTitle">
                    <i class="fas fa-plus-circle me-2"></i> Add New Item
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <form id="priceForm">
                    <input type="hidden" id="itemId">

                    <!-- Category/Header field hidden for now -->
                    <input type="hidden" id="header" value="">
                    <datalist id="headerOptions"></datalist>


                    <div class="mb-3">
                        <label class="form-label fw-bold">Product Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="productName" required placeholder="Enter product name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Price (Rs.) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="rate" required placeholder="Enter price" min="0" step="0.01">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Unit <small class="text-muted">(Optional)</small></label>
                        <input type="text" class="form-control" id="unit" placeholder="e.g., per sqft, per kg, each">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Description <small class="text-muted">(Optional)</small></label>
                        <textarea class="form-control" id="description" rows="2" placeholder="Brief description"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveItem()" id="saveBtn">
                    <i class="fas fa-save me-1"></i> Save
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
let priceModal;

document.addEventListener('DOMContentLoaded', function() {
    priceModal = new bootstrap.Modal(document.getElementById('priceModal'));
});

// Search Table
function searchTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.querySelectorAll('#priceTable tbody tr');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
    });
}

// Open Add Modal
function openAddModal() {
    document.getElementById('priceForm').reset();
    document.getElementById('itemId').value = '';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle me-2"></i> Add New Item';
    loadHeaders();
    priceModal.show();
}

function loadHeaders() {
    $.get('{{ url("/price-list/headers") }}', function(response) {
        let options = '';
        response.headers.forEach(h => {
            options += `<option value="${h}">`;
        });
        document.getElementById('headerOptions').innerHTML = options;
    });
}

// Edit Item
function editItem(id) {
    $.get(`{{ url('/price-list') }}/${id}`, function(response) {
        if(response.success) {
            const item = response.data;
            document.getElementById('itemId').value = item.id;
            document.getElementById('productName').value = item.product_name;
            document.getElementById('rate').value = item.rate;
            document.getElementById('unit').value = item.unit || '';
            document.getElementById('header').value = item.header || '';
            document.getElementById('description').value = item.description || '';
            document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit me-2"></i> Edit Item';
            loadHeaders();
            priceModal.show();
        }
    });
}

// Save Item
function saveItem() {
    const id = document.getElementById('itemId').value;
    const data = {
        product_name: document.getElementById('productName').value,
        rate: document.getElementById('rate').value,
        unit: document.getElementById('unit').value,
        header: document.getElementById('header').value,
        description: document.getElementById('description').value,
        _token: '{{ csrf_token() }}'
    };

    if(!data.product_name || !data.rate) {
        Swal.fire('Error', 'Product name and price are required', 'error');
        return;
    }

    const baseUrl = '{{ url("/price-list") }}';
    const url = id ? `${baseUrl}/${id}` : baseUrl;
    const method = id ? 'PUT' : 'POST';

    $.ajax({
        url: url,
        type: method,
        data: data,

        success: function(response) {
            if(response.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: id ? 'Item updated successfully' : 'Item added successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            }
        },
        error: function(xhr) {
            console.error('AJAX Error:', xhr);
            console.error('Status:', xhr.status);
            console.error('Response:', xhr.responseJSON);
            console.error('Response Text:', xhr.responseText);
            
            let errorMessage = 'Something went wrong';
            
            if (xhr.responseJSON) {
                if (xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON.errors) {
                    errorMessage = Object.values(xhr.responseJSON.errors).flat().join('<br>');
                }
            } else if (xhr.responseText) {
                errorMessage = 'Server Error: ' + xhr.status;
            }
            
            Swal.fire('Error', errorMessage, 'error');
        }
    });
}

// Delete Item
function deleteItem(id) {
    Swal.fire({
        title: 'Delete Item?',
        text: 'Are you sure you want to delete this item?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, Delete',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if(result.isConfirmed) {
            $.ajax({
                url: `{{ url('/price-list') }}/${id}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(response) {
                    if(response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'Item has been deleted',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', 'Failed to delete item', 'error');
                }
            });
        }
    });
}
</script>
