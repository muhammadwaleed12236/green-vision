@include('admin_panel.include.header_include')

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       Design/colors stay identical on every device.
       ============================================ */

    /* Never allow accidental horizontal scrolling */
    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
    }

    /* Keep media flexible so nothing overflows */
    img, canvas, table {
        max-width: 100%;
    }

    /* Product table never scrolls horizontally - cells wrap to fit */
    .pp-wrap {
        overflow-x: hidden;
    }
    .pp-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .pp-wrap .datanew td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .pp-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .pp-wrap .datanew th:nth-child(1) { width: 5% !important; }
    .pp-wrap .datanew th:nth-child(2) { width: 30% !important; }
    .pp-wrap .datanew th:nth-child(3) { width: 10% !important; }
    .pp-wrap .datanew th:nth-child(4) { width: 15% !important; }
    .pp-wrap .datanew th:nth-child(5) { width: 15% !important; }
    .pp-wrap .datanew th:nth-child(6) { width: 10% !important; }
    .pp-wrap .datanew th:nth-child(7) { width: 15% !important; }
    .pp-wrap .dataTables_wrapper {
        max-width: 100%;
    }

    /* ---------- Phone & below (576px) ---------- */
    @media (max-width: 575.98px) {
        .page-header .page-btn {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        .page-header .page-btn .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .page-title h4 { font-size: 1.05rem; }
        .pp-wrap .dataTables_filter { margin-bottom: 8px; }
        .pp-wrap .dataTables_filter input { max-width: 130px; }
        .pp-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- Product table -> stacked cards (phone & small tablet) ---------- */
    @media (max-width: 767.98px) {
        .table-responsive .datanew thead {
            display: none !important;
        }
        .table-responsive .datanew,
        .table-responsive .datanew tbody,
        .table-responsive .datanew tr,
        .table-responsive .datanew td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .table-responsive .datanew {
            border: 0 !important;
        }
        .table-responsive .datanew tbody {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
        }
        .table-responsive .datanew tbody tr {
            background: #fff;
            border: 1px solid #eef2f7 !important;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 4px 14px;
            margin: 0 !important;
        }
        .table-responsive .datanew tbody tr:hover {
            background: #fff;
        }
        .table-responsive .datanew td {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0 !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            background: transparent !important;
            font-size: 0.85rem !important;
            color: #1e293b;
            text-align: right;
            white-space: normal !important;
            word-break: break-word;
        }
        .table-responsive .datanew td:last-child {
            border-bottom: 0 !important;
        }
        .table-responsive .datanew td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .table-responsive .datanew td .btn {
            padding: 0.35rem 0.6rem;
        }
        .pp-wrap .dataTables_filter,
        .pp-wrap .dataTables_length,
        .pp-wrap .dataTables_info,
        .pp-wrap .dataTables_paginate {
            max-width: 100%;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- PAGE HEADER --}}
            <div class="page-header">
                <div class="page-title">
                    <h4>Product List</h4>
                </div>
                <div class="page-btn d-flex gap-2">
                    <button id="addUnitBtn" type="button" class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
                        + Add Unit
                    </button>
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        + Add Product
                    </button>
                </div>
            </div>

            {{-- PRODUCT TABLE --}}
            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif
                    <div class="table-responsive pp-wrap">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Item Name</th>
                                    <th>Unit</th>
                                    <th>Purchase</th>
                                    <th>Sale</th>
                                    <th>Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $k => $p)
                                    <tr>
                                        <td data-label="#">#{{ $k + 1 }}</td>
                                        <td data-label="Item Name">{{ $p->item_name }}</td>
                                        <td data-label="Unit">{{ $p->unit ?? '—' }}</td>
                                        <td data-label="Purchase">PKR {{ number_format($p->wholesale_price, 2) }}</td>
                                        <td data-label="Sale">PKR {{ number_format($p->retail_price, 2) }}</td>
                                        <td data-label="Stock">
                                            @php
                                                $stock = $p->initial_stock ?? 0;
                                                if ($stock <= 0) {
                                                    $badgeClass = 'badge bg-danger';
                                                } elseif ($stock <= 10) {
                                                    $badgeClass = 'badge bg-warning';
                                                } elseif ($stock <= 50) {
                                                    $badgeClass = 'badge bg-info';
                                                } else {
                                                    $badgeClass = 'badge bg-success';
                                                }
                                            @endphp
                                            <span class="{{ $badgeClass }} px-3 py-2">
                                                {{ $stock }}
                                            </span>
                                        </td>
                                        <td data-label="Action">
                                            <div class="d-flex gap-1 justify-content-end">
                                                <button class="btn btn-sm btn-primary editProductBtn"
                                                        data-id="{{ $p->id }}"
                                                        data-name="{{ $p->item_name }}"
                                                        data-unit="{{ $p->unit }}"
                                                        data-wholesale="{{ $p->wholesale_price }}"
                                                        data-retail="{{ $p->retail_price }}"
                                                        data-stock="{{ $p->initial_stock ?? 0 }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editProductModal">
                                                    Edit
                                                </button>
                                                <button class="btn btn-sm btn-danger deleteProductBtn"
                                                        data-id="{{ $p->id }}">
                                                    Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
{{-- ^^^ main-wrapper ends here. All modals are OUTSIDE it below ^^^ --}}

{{-- ADD PRODUCT MODAL --}}
<div class="modal fade" id="addProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="{{ route('store-product') }}" id="addProductForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit</label>
                            <select class="form-control unitDropdown" name="unit">
                                <option value="">Select Unit</option>
                                @foreach($units ?? [] as $u)
                                    <option value="{{ $u->name }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" name="wholesale_price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" step="0.01" class="form-control" name="retail_price" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" id="add_stock" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT PRODUCT MODAL --}}
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" id="editProductForm">
                @csrf
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Item Name</label>
                            <input type="text" class="form-control" name="item_name" id="edit_item_name" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit</label>
                            <select class="form-control unitDropdown" name="unit" id="edit_unit">
                                <option value="">Select Unit</option>
                                @foreach($units ?? [] as $u)
                                    <option value="{{ $u->name }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Price</label>
                            <input type="number" step="0.01" class="form-control" name="wholesale_price" id="edit_wholesale_price" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sale Price</label>
                            <input type="number" step="0.01" class="form-control" name="retail_price" id="edit_retail_price" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Stock</label>
                            <input type="number" class="form-control" name="stock" id="edit_stock" min="0" required placeholder="Enter stock quantity">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ADD UNIT MODAL --}}
<div class="modal fade" id="addUnitModal" tabindex="-1" aria-labelledby="addUnitModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addUnitForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="addUnitModalLabel">Add Unit</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Unit Name</label>
                        <input type="text" class="form-control" name="name" id="new_unit_name" required placeholder="e.g. Kg, Box, Pcs">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveUnitBtn">Save Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- JS --}}
<script>
    // Populate edit modal fields when Edit button is clicked
    $(document).on("click", ".editProductBtn", function () {
        let id        = $(this).data("id");
        let name      = $(this).data("name");
        let unit      = $(this).data("unit");
        let wholesale = $(this).data("wholesale");
        let retail    = $(this).data("retail");
        let stock     = $(this).data("stock");

        $("#editProductForm").attr("action", "{{ url('/product/update') }}");
        $("#edit_product_id").val(id);
        $("#edit_item_name").val(name);
        $("#edit_unit").val(unit);
        $("#edit_wholesale_price").val(wholesale);
        $("#edit_retail_price").val(retail);
        $("#edit_stock").val(stock);
    });

    // AJAX - Add Unit
    $('#addUnitForm').on('submit', function (e) {
        e.preventDefault();
        let btn = $('#saveUnitBtn');
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: "{{ route('store-unit') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    let newOption = `<option value="${response.unit.name}" selected>${response.unit.name}</option>`;
                    $('.unitDropdown').append(newOption).trigger('change');

                    $('#addUnitModal').modal('hide');
                    $('#addUnitForm')[0].reset();

                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: 'Unit added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to add unit'
                });
            },
            complete: function () {
                btn.prop('disabled', false).text('Save Unit');
            }
        });
    });

    // AJAX - Add Product
    $('#addProductForm').on('submit', function (e) {
        e.preventDefault();
        let form         = $(this);
        let btn          = form.find('button[type="submit"]');
        let originalText = btn.text();
        btn.prop('disabled', true).text('Saving...');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    $('#addProductModal').modal('hide');
                    form[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Product added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to add product';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
            },
            complete: function () {
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // AJAX - Edit Product
    $('#editProductForm').on('submit', function (e) {
        e.preventDefault();
        let form         = $(this);
        let btn          = form.find('button[type="submit"]');
        let originalText = btn.text();
        btn.prop('disabled', true).text('Updating...');

        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            success: function (response) {
                if (response.status === 'success') {
                    $('#editProductModal').modal('hide');
                    form[0].reset();
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Product updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to update product';
                if (xhr.status === 422 && xhr.responseJSON?.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON?.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
            },
            complete: function () {
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // AJAX - Delete Product
    $(document).on("click", ".deleteProductBtn", function (e) {
        e.preventDefault();
        let productId = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "This product will be permanently deleted!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('product.delete', ':id') }}".replace(':id', productId),
                    type: "DELETE",
                    data: { _token: "{{ csrf_token() }}" },
                    success: function (response) {
                        if (response.status === 'success') {
                            Swal.fire("Deleted!", response.message, "success")
                                .then(() => location.reload());
                        } else {
                            Swal.fire("Error!", response.message, "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    });
</script>