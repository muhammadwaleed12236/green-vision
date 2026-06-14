@include('admin_panel.include.header_include')

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
                    <button class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#addUnitModal">
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
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ $p->item_name }}</td>
                                    <td>{{ $p->unit ?? '—' }}</td>
                                    <td>Rs. {{ number_format($p->wholesale_price, 2) }}</td>
                                    <td>Rs. {{ number_format($p->retail_price, 2) }}</td>
                                    
                                        <td>
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
                                    <td>
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

<!-- {{-- ADD PRODUCT MODAL --}} -->
<div class="modal fade" id="addProductModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <form method="POST" action="{{ route('store-product') }}" id="addProductForm">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>

                <div class="modal-body">

                    <!-- {{-- ITEM NAME & UNIT --}} -->
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

                    <!-- {{-- PRICES --}} -->
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
        <input
            type="number"
            class="form-control"
            name="stock"
            id="add_stock"
            min="0"
            required>
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
<div class="modal fade" id="editProductModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="" id="editProductForm">
                @csrf
                <input type="hidden" name="product_id" id="edit_product_id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Product</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>

                <div class="modal-body">
                    {{-- ITEM NAME & UNIT --}}
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

                    {{-- PRICES --}}
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

                    {{-- STOCK --}}
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
<div class="modal fade" id="addUnitModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addUnitForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Unit</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
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
    $(document).on("click", ".editProductBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let unit = $(this).data("unit");
        let wholesale = $(this).data("wholesale");
        let retail = $(this).data("retail");
        let stock = $(this).data("stock");

        // Set the form action URL with the product ID
        $("#editProductForm").attr("action", "{{ url('/product/update') }}");
        
        $("#edit_product_id").val(id);
        $("#edit_item_name").val(name);
        $("#edit_unit").val(unit);
        $("#edit_wholesale_price").val(wholesale);
        $("#edit_retail_price").val(retail);
        $("#edit_stock").val(stock);
    });


    function calculateEditMeasurement() {
        let h = parseFloat($('#edit_height').val()) || 0;
        let w = parseFloat($('#edit_width').val()) || 0;
        let area = h * w;

        if (area > 0) {
            $('#edit_area').val(area.toFixed(2));
        } else {
            $('#edit_area').val('');
        }

        let purchaseRate = parseFloat($('#edit_wholesale_price').val()) || 0;
        let saleRate = parseFloat($('#edit_retail_price').val()) || 0;

        $('#edit_purchase_total').val(
            area > 0 && purchaseRate > 0 ? (area * purchaseRate).toFixed(2) : ''
        );

        $('#edit_sale_total').val(
            area > 0 && saleRate > 0 ? (area * saleRate).toFixed(2) : ''
        );
    }

$('#edit_height, #edit_width, #edit_wholesale_price, #edit_retail_price').on('input', calculateEditMeasurement);

    // AJAX Form Submit for Add Unit
    $('#addUnitForm').on('submit', function(e) {
        e.preventDefault();
        let btn = $('#saveUnitBtn');
        btn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: "{{ route('store-unit') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    // Add new option to unit dropdowns
                    let newOptionHTML = `<option value="${response.unit.name}" selected>${response.unit.name}</option>`;
                    $('.unitDropdown').append(newOptionHTML).trigger('change');
                    
                    // Close modal & reset form
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
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Failed to add unit'
                });
            },
            complete: function() {
                btn.prop('disabled', false).text('Save Unit');
            }
        });
    });

    // AJAX Form Submit for Add Product
    $('#addProductForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');
        let originalText = btn.text();
        btn.prop('disabled', true).text('Saving...');
        
        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    $('#addProductModal').modal('hide');
                    form[0].reset();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Product added successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to add product';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            },
            complete: function() {
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // AJAX Form Submit for Edit Product
    $('#editProductForm').on('submit', function(e) {
        e.preventDefault();
        let form = $(this);
        let btn = form.find('button[type="submit"]');
        let originalText = btn.text();
        btn.prop('disabled', true).text('Updating...');
        
        $.ajax({
            url: form.attr('action'),
            type: "POST",
            data: form.serialize(),
            success: function(response) {
                if (response.status === 'success') {
                    $('#editProductModal').modal('hide');
                    form[0].reset();
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: response.message || 'Product updated successfully',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                }
            },
            error: function(xhr) {
                let errorMsg = 'Failed to update product';
                if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMsg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            },
            complete: function() {
                btn.prop('disabled', false).text(originalText);
            }
        });
    });
</script>

<script>
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
                data: {
                    _token: "{{ csrf_token() }}"
                },
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
