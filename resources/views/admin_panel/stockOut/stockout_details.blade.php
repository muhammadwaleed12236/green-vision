@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>StockOut Details</h4>
                    <h6>Job #{{ $localSale->invoice_number ?? 'N/A' }}</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('stockout.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>Success!</strong> {{ session('success') }}.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Error!</strong> {{ session('error') }}.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Job Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Job Number:</label>
                                <p class="mb-0">
                                    <span class="badge bg-primary fs-6">
                                        {{ $localSale->invoice_number ?? 'N/A' }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Customer Name:</label>
                                <p class="mb-0">{{ $localSale->customer->customer_name ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Total Items:</label>
                                <p class="mb-0">
                                    <span class="badge bg-info fs-6">{{ $stockOuts->count() }} Items</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Total Stock Used:</label>
                                <p class="mb-0">
                                    <span class="badge bg-danger fs-6">
                                        {{ number_format($stockOuts->sum('total_stock'), 0) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Material Usage Details</h5>
                </div>
                <div class="card-body">
                    @if($stockOuts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Product Name</th>
                                        <th>Height</th>
                                        <th>Width</th>
                                        <th>Opening Stock</th>
                                        <th>Closing Stock</th>
                                        <th>Stock Used</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stockOuts as $key => $stockOut)
                                        <tr>
                                            <td>{{ $key + 1 }}</td>
                                            <td>
                                                <strong>{{ $stockOut->product->item_name ?? 'N/A' }}</strong>
                                            </td>
                                            <td>{{ $stockOut->product->height ?? '-' }}</td>
                                            <td>{{ $stockOut->product->width ?? '-' }}</td>
                                            <td>
                                                <span class="badge bg-success">
                                                    {{ number_format($stockOut->current_stock, 0) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning">
                                                    {{ number_format($stockOut->close_stock, 0) }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-danger fs-6">
                                                    {{ number_format($stockOut->total_stock, 0) }}
                                                </span>
                                            </td>

                                            <td>{{ $stockOut->created_at->format('d-M-Y') }}</td>

                                            <td>

                                                <button class="btn btn-sm btn-primary editStockOutBtn text-white"
                                                    data-id="{{ $stockOut->id }}" data-product="{{ $stockOut->product_id }}"
                                                    data-current="{{ $stockOut->current_stock }}"
                                                    data-close="{{ $stockOut->close_stock }}" data-bs-toggle="modal"
                                                    data-bs-target="#editStockOutModal">
                                                    Edit
                                                </button>

                                                <button class="btn btn-sm btn-danger deleteStockOutBtn text-white"
                                                    data-id="{{ $stockOut->id }}">
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>

                                <tfoot class="table-light">
                                    <tr>
                                        <td colspan="6" class="text-end"><strong>Grand Total Stock Used:</strong></td>
                                        <td colspan="3">
                                            <span class="badge bg-danger fs-5">
                                                {{ number_format($stockOuts->sum('total_stock'), 0) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tfoot>

                            </table>
                        </div>
                    @else
                        <div class="alert alert-info text-center">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p class="mb-0">No stock out records found for this job.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editStockOutModal" tabindex="-1" aria-labelledby="editStockOutModalLabel"
    aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit StockOut</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('update-stockout') }}" method="POST">
                @csrf
                <input type="hidden" name="stockout_id" id="edit_stockout_id">
                <input type="hidden" name="product_id" id="edit_product_id">
                <input type="hidden" name="local_sales_id" value="{{ $localSale->id }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Product</label>
                        <input type="text" class="form-control bg-light" id="edit_product_name" readonly>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Opening Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="current_stock" id="edit_current_stock"
                                min="0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Closing Stock <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="close_stock" id="edit_close_stock" min="0"
                                required>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <strong>Stock Used:</strong> <span id="edit_total_display" class="fs-5 text-danger">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).ready(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(document).on("click", ".editStockOutBtn", function () {
            let id = $(this).data("id");
            let productId = $(this).data("product");
            let current = $(this).data("current");
            let close = $(this).data("close");
            let productName = $(this).closest('tr').find('td:eq(1) strong').text();

            $('#edit_stockout_id').val(id);
            $('#edit_product_id').val(productId);
            $('#edit_product_name').val(productName);
            $('#edit_current_stock').val(current);
            $('#edit_close_stock').val(close);
            $('#edit_total_display').text(current - close);
        });

        $('#edit_current_stock, #edit_close_stock').on('input', function () {
            let current = parseInt($('#edit_current_stock').val()) || 0;
            let close = parseInt($('#edit_close_stock').val()) || 0;
            let total = current - close;
            $('#edit_total_display').text(total);
        });

        $(document).on("click", ".deleteStockOutBtn", function (e) {
            e.preventDefault();
            let id = $(this).data("id");
            let deleteUrl = "{{ route('delete-stockout') }}";

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: "DELETE",
                        data: { id: id },
                        success: function (response) {
                            Swal.fire("Deleted!", response.success, "success")
                                .then(() => location.reload());
                        },
                        error: function (xhr) {
                            Swal.fire("Error!", "Something went wrong!", "error");
                        }
                    });
                }
            });
        });
    });
</script>
