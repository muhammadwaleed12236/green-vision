@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>StockOut List</h4>
                    <h6>Manage StockOut</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addStockOutModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add StockOut
                    </button>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <strong>Success!</strong> {{ session('success') }}.
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job Number</th>
                                    <th>Customer Name</th>
                                    <th>Total Items</th>
                                    <th>Total Stock Out</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $groupedStockOuts = $stockOuts->groupBy('local_sales_id');
                                @endphp
                                @foreach($groupedStockOuts as $saleId => $items)
                                    @php
                                        $firstItem = $items->first();
                                        $totalStockOut = $items->sum('total_stock');
                                        $itemCount = $items->count();
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $firstItem->localSale->invoice_number ?? 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $ls = $firstItem->localSale;
                                                if ($ls->party_type === 'customer') {
                                                    echo $ls->customer->customer_name ?? 'N/A';
                                                } elseif ($ls->party_type === 'vendor') {
                                                    echo $ls->vendor->Party_name ?? 'N/A';
                                                } else {
                                                    echo $ls->customer_shopname ?? 'Walk-in';
                                                }
                                            @endphp
                                        </td>
                                        <td><span class="badge bg-info">{{ $itemCount }} Items</span></td>
                                        <td>
                                            <span class="badge bg-danger">
                                                {{ number_format($totalStockOut, 0) }}
                                            </span>
                                        </td>
                                        <td>{{ $firstItem->created_at->format('d-M-Y') }}</td>
                                        <td>
                                            <a href="{{ route('stockout-details', $saleId) }}"
                                                class="btn btn-sm text-white btn-info" title="View Details">
                                                Details
                                            </a>

                                            <button class="btn btn-sm btn-danger text-white deleteJobStockOutBtn"
                                                data-id="{{ $saleId }}" title="Delete All">
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
</div>

<!-- Add StockOut Modal -->
<div class="modal fade" id="addStockOutModal" tabindex="-1" aria-labelledby="addStockOutModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add StockOut</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('store-stockout') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Invoice # <span class="text-danger">*</span></label>
                            <select class="form-control" name="local_sales_id" id="add_local_sales_id" required>
                                <option value="">Select Invoice</option>
                                @foreach($localSales as $sale)
                                    @php
                                        if ($sale->party_type === 'customer') {
                                            $partyName = $sale->customer->customer_name ?? 'N/A';
                                        } elseif ($sale->party_type === 'vendor') {
                                            $partyName = $sale->vendor->Party_name ?? 'N/A';
                                        } else {
                                            $partyName = $sale->customer_shopname ?? 'Walk-in';
                                        }
                                    @endphp
                                    <option value="{{ $sale->id }}" 
                                        data-customer="{{ $partyName }}"
                                        data-job-number="{{ $sale->job_number ?? '' }}"
                                        data-invoice="{{ $sale->invoice_number ?? '' }}">
                                        {{ $sale->invoice_number ?? 'N/A' }} - {{ $partyName }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Job Number</label>
                            <input type="text" class="form-control bg-light" id="add_job_number" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control bg-light" id="add_customer_name" readonly>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Unit</th>
                                    <th>Opening Stock</th>
                                    <th>Used Stock</th>
                                    <th>Closing Stock</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="productTableBody">
                                <tr class="product-row">
                                    <td>
                                        <select class="form-control product-select" style="min-width: 150px;"
                                            name="products[0][product_id]" required>
                                            <option value="">Select Product</option>
                                            @foreach($products as $product)
                                                <option value="{{ $product->id }}"
                                                    data-unit="{{ $product->unit ?? '' }}"
                                                    data-stock="{{ $product->available_stock ?? 0 }}">
                                                    {{ $product->item_name }} @if(isset($product->available_stock)) (Stock:
                                                    {{ $product->available_stock }} {{ $product->unit ?? '' }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control bg-light unit-input" readonly></td>
                                    <td><input type="number" class="form-control opening-stock"
                                            name="products[0][current_stock]" min="0" readonly></td>
                                    <td><input type="number" class="form-control used-stock"
                                            name="products[0][used_stock]" min="0" placeholder="" required></td>
                                    <td><input type="text"
                                            class="form-control bg-light closing-stock fw-bold text-success" readonly
                                            value="0"></td>
                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" disabled>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="alert alert-info">
                        <strong>Total Closing Stock:</strong> <span id="grandTotal" class="fs-5 text-success">0</span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save StockOut</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).ready(function () {
        let rowIndex = 1;

        $('#add_local_sales_id').on('change', function () {
            let selected = $(this).find('option:selected');
            $('#add_customer_name').val(selected.data('customer') || '-');
            $('#add_job_number').val(selected.data('job-number') || '-');
        });

        // When product is selected, auto-fill opening stock
        $(document).on('change', '.product-select', function () {
            let selected = $(this).find('option:selected');
            let row = $(this).closest('tr');

            let unit = selected.attr('data-unit');
            let stock = selected.attr('data-stock');

            row.find('.unit-input').val(unit || '-');
            row.find('.opening-stock').val(stock || 0);

            // Reset used and closing
            row.find('.used-stock').val('');
            row.find('.closing-stock').val(stock || 0);
        });

        // ✅ Calculate: Opening - Used = Closing (allow stockout even when opening stock is 0)
        $(document).on('input', '.used-stock', function () {
            let row = $(this).closest('tr');
            let opening = parseFloat(row.find('.opening-stock').val()) || 0;
            let used = parseFloat(row.find('.used-stock').val()) || 0;

            let closing = opening - used;

            // Clamp closing to 0 — no alert, no reset, entry is allowed
            if (closing < 0) {
                closing = 0;
            }
            row.find('.closing-stock').val(closing).removeClass('text-danger');

            calculateGrandTotal();

            // Auto-add new row if this is the last row and all fields are filled
            let isLastRow = row.is('#productTableBody tr:last');
            let hasProduct = row.find('.product-select').val() != '';
            let hasUsedStock = row.find('.used-stock').val() != '';

            if (isLastRow && hasProduct && hasUsedStock) {
                addNewRow();
            }
        });

        function addNewRow() {
            let newRow = `
            <tr class="product-row">
                <td>
                    <select class="form-control product-select" style="min-width: 150px;" name="products[${rowIndex}][product_id]" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                data-unit="{{ $product->unit ?? '' }}"
                                data-stock="{{ $product->available_stock ?? 0 }}">
                                {{ $product->item_name }} @if(isset($product->available_stock)) (Stock: {{ $product->available_stock }} {{ $product->unit ?? '' }}) @endif
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="text" class="form-control bg-light unit-input" readonly></td>
                <td><input type="number" class="form-control opening-stock" name="products[${rowIndex}][current_stock]" min="0" readonly></td>
                <td><input type="number" class="form-control used-stock" name="products[${rowIndex}][used_stock]" min="0" placeholder="Enter used stock" required></td>
                <td><input type="text" class="form-control bg-light closing-stock fw-bold text-success" readonly value="0"></td>
                <td><button type="button" class="btn btn-danger btn-sm remove-row">Delete</button></td>
            </tr>
        `;
            $('#productTableBody').append(newRow);
            rowIndex++;
            updateRemoveButtons();
        }

        function calculateGrandTotal() {
            let grandTotal = 0;
            $('#productTableBody tr').each(function () {
                let closing = parseFloat($(this).find('.closing-stock').val()) || 0;
                grandTotal += closing;
            });
            $('#grandTotal').text(grandTotal);
        }

        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            updateRemoveButtons();
            calculateGrandTotal();
        });

        function updateRemoveButtons() {
            let rowCount = $('#productTableBody tr').length;
            if (rowCount === 1) {
                $('.remove-row').prop('disabled', true);
            } else {
                $('.remove-row').prop('disabled', false);
            }
        }
    });
</script>


<script>
    $(document).on("click", ".deleteJobStockOutBtn", function (e) {
        e.preventDefault();
        let saleId = $(this).data("id");
        let deleteUrl = "{{ route('delete-job-stockout') }}"; // Route create karna hoga

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete all stock out records for this job!",
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
                    data: {
                        sale_id: saleId,
                        _token: '{{ csrf_token() }}'
                    },
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
</script>
