@include('admin_panel.include.header_include')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

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

                    {{-- StockOut Table --}}
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
                                @foreach($stockOutSummaries as $summary)
                                    @php
                                        $ls = $localSales[$summary->local_sales_id] ?? null;
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <span class="badge bg-primary">
                                                {{ $ls->invoice_number ?? 'N/A' }}
                                            </span>
                                        </td>

                                        <td>
                                            @php
                                                if ($ls) {
                                                    if ($ls->party_type === 'customer') {
                                                        echo $ls->customer->customer_name ?? 'N/A';
                                                    } elseif ($ls->party_type === 'vendor') {
                                                        echo $ls->vendor->Party_name ?? 'N/A';
                                                    } else {
                                                        echo $ls->customer_shopname ?? 'Walk-in';
                                                    }
                                                } else {
                                                    echo 'N/A';
                                                }
                                            @endphp
                                        </td>

                                        <td><span class="badge bg-info">{{ $summary->item_count }} Items</span></td>

                                        <td>
                                            <span class="badge bg-danger">
                                                {{ number_format($summary->total_stock_out, 0) }}
                                            </span>
                                        </td>

                                        <td>{{ \Carbon\Carbon::parse($summary->latest_date)->format('d-M-Y') }}</td>

                                        <td>
                                            <a href="{{ route('stockout-details', $summary->local_sales_id) }}"
                                               class="btn btn-sm text-white btn-info">
                                                Details
                                            </a>

                                            <button class="btn btn-sm btn-danger text-white deleteJobStockOutBtn"
                                                    data-id="{{ $summary->local_sales_id }}">
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

{{-- ========================= --}}
{{-- ADD STOCKOUT MODAL --}}
{{-- ========================= --}}
<div class="modal fade" id="addStockOutModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Add StockOut</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('store-stockout') }}" method="POST">
                @csrf

                <div class="modal-body">

                    {{-- DATE RANGE FILTER --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label class="form-label">From Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="from_date" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="to_date" required>
                        </div>
                    </div>

                    {{-- JOB SELECT --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Job Number <span class="text-danger">*</span></label>
                            <select class="form-control" name="local_sales_id" id="add_job_number" required>
                                <option value="">Select Job</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control bg-light" id="add_customer_name" readonly>
                        </div>
                    </div>

                    {{-- PRODUCTS TABLE --}}
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
                                <tr>
                                    <td>
                                        <select class="form-control product-select" name="products[0][product_id]" required>
                                            <option value="">Select Product</option>
                                        </select>
                                    </td>

                                    <td><input type="text" class="form-control bg-light unit-input" readonly></td>
                                    <td><input type="number" class="form-control opening-stock" readonly></td>
                                    <td><input type="number" class="form-control used-stock" name="products[0][used_stock]" required></td>
                                    <td><input type="text" class="form-control closing-stock bg-light" readonly></td>

                                    <td>
                                        <button type="button" class="btn btn-danger btn-sm remove-row" disabled>Delete</button>
                                    </td>
                                </tr>
                            </tbody>

                        </table>
                    </div>

                    <div class="mt-3">
                        <strong>Total Closing Stock: </strong>
                        <span id="grandTotal">0</span>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save StockOut</button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- ========================= --}}
{{-- JS SECTION --}}
{{-- ========================= --}}
@push('scripts')
<script>
$(document).ready(function () {

    // Cache products so we only fetch once
    let productsCache = null;

    // Load products via AJAX and populate all product dropdowns
    function loadProducts() {
        if (productsCache) {
            populateProductDropdowns(productsCache);
            return;
        }
        $.get('/get-products', function (response) {
            productsCache = response;
            populateProductDropdowns(response);
        });
    }

    function populateProductDropdowns(products) {
        $('.product-select').each(function () {
            let currentVal = $(this).val();
            let html = '<option value="">Select Product</option>';
            products.forEach(function (p) {
                html += `<option value="${p.id}" data-unit="${p.unit}" data-stock="${p.available_stock}">${p.item_name}</option>`;
            });
            $(this).html(html);
            if (currentVal) $(this).val(currentVal);
        });
    }

    // Track the in-flight AJAX request so we can abort stale ones
    let pendingJobsReq = null;

    // Helper to safely destroy Select2
    function destroySelect2(el) {
        if (el && el.length && el.data('select2')) {
            try { el.select2('destroy'); } catch(e) {}
        }
    }

    // Load jobs based on selected date range
    function loadJobs(fromDate, toDate) {
        if (pendingJobsReq) {
            pendingJobsReq.abort();
        }

        let dropdown = $('#add_job_number');
        destroySelect2(dropdown);
        dropdown.html('<option value="">Select Job</option>');
        $('#add_customer_name').val('');

        pendingJobsReq = $.ajax({
            url: '/get-invoices-by-date',
            data: { from_date: fromDate, to_date: toDate },
            dataType: 'json',
            success: function (response) {
                pendingJobsReq = null;

                if (!response || response.length === 0) {
                    dropdown.append('<option value="" disabled>No jobs found for this date range</option>');
                    return;
                }

                response.forEach(function (sale) {
                    let customerName = sale.customer_name || 'N/A';
                    dropdown.append(`
                        <option value="${sale.id}"
                            data-customer="${customerName}"
                            data-job="${sale.invoice_number}"
                            data-invoice="${sale.invoice_number}">
                            ${sale.invoice_number} - ${customerName}
                        </option>
                    `);
                });

                dropdown.select2({
                    dropdownParent: $('#addStockOutModal'),
                    width: '100%',
                    placeholder: 'Search Job...'
                });
            },
            error: function (xhr, status) {
                if (status === 'abort') return;
                pendingJobsReq = null;
                console.error('Failed to load jobs');
                dropdown.html('<option value="" disabled>Error loading jobs</option>');
            }
        });
    }

    // Initialize when modal is shown - load products + jobs
    $('#addStockOutModal').on('shown.bs.modal', function () {
        loadProducts();
        let today = new Date().toISOString().split('T')[0];
        let thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
        $('#from_date').val(thirtyDaysAgo);
        $('#to_date').val(today);
        loadJobs(thirtyDaysAgo, today);
    });

    // Destroy Select2 when modal is hidden
    $('#addStockOutModal').on('hidden.bs.modal', function () {
        if (pendingJobsReq) {
            pendingJobsReq.abort();
            pendingJobsReq = null;
        }
        destroySelect2($('#add_job_number'));
        $('#add_customer_name').val('');
    });

    // Listen for date range changes
    function reloadJobs() {
        let fromDate = $('#from_date').val();
        let toDate = $('#to_date').val();
        if (fromDate && toDate) {
            loadJobs(fromDate, toDate);
        }
    }

    $(document).on('change', '#from_date, #to_date', reloadJobs);

    // Listen for job selection (delegated, works with Select2)
    $(document).on('change', '#add_job_number', function () {
        let val = $(this).val();
        if (!val) { $('#add_customer_name').val(''); return; }
        let selected = $(this).find('option:selected');
        let customerName = selected.data('customer') || '-';
        $('#add_customer_name').val(customerName);
    });

    // Handle product selection
    $(document).on('change', '.product-select', function () {
        let row = $(this).closest('tr');
        let selectedOption = $(this).find('option:selected');
        let unit = selectedOption.data('unit') || '';
        let stock = selectedOption.data('stock') || 0;

        row.find('.unit-input').val(unit);
        row.find('.opening-stock').val(stock);
        row.find('.closing-stock').val(stock);
    });

    // Handle used stock input change
    $(document).on('input', '.used-stock', function () {
        let row = $(this).closest('tr');
        let openingStock = parseFloat(row.find('.opening-stock').val()) || 0;
        let usedStock = parseFloat($(this).val()) || 0;
        let closingStock = openingStock - usedStock;

        if (closingStock < 0) closingStock = 0;

        row.find('.closing-stock').val(closingStock.toFixed(2));
        calculateGrandTotal();
    });

    // Calculate grand total
    function calculateGrandTotal() {
        let grandTotal = 0;
        $('.closing-stock').each(function () {
            let value = parseFloat($(this).val()) || 0;
            grandTotal += value;
        });
        $('#grandTotal').text(grandTotal.toFixed(2));
    }

});
</script>
@endpush

@include('admin_panel.include.footer_include')