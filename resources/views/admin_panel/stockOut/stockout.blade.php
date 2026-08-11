@include('admin_panel.include.header_include')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       ============================================ */

    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
    }

    img, canvas, table {
        max-width: 100%;
    }

    /* StockOut table never scrolls horizontally - cells wrap to fit */
    .so-wrap {
        overflow-x: hidden;
    }
    .so-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .so-wrap .datanew td,
    .so-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .so-wrap .datanew th:nth-child(1) { width: 4% !important; }
    .so-wrap .datanew th:nth-child(2) { width: 15% !important; }
    .so-wrap .datanew th:nth-child(3) { width: 24% !important; }
    .so-wrap .datanew th:nth-child(4) { width: 12% !important; }
    .so-wrap .datanew th:nth-child(5) { width: 13% !important; }
    .so-wrap .datanew th:nth-child(6) { width: 14% !important; }
    .so-wrap .datanew th:nth-child(7) { width: 18% !important; }
    .so-wrap .dataTables_wrapper {
        max-width: 100%;
    }

    /* Empty-state message: keep it a full-width centered table cell */
    .so-wrap .datanew td.dataTables_empty {
        display: table-cell !important;
        width: 100% !important;
        text-align: center !important;
        padding: 28px !important;
        color: #94a3b8;
        font-weight: 500;
        border: 0 !important;
    }

    /* Actions cell: keep button wrapping inside the cell on desktop */
    @media (min-width: 768px) {
        .so-wrap .datanew td:last-child:not(.dataTables_empty) {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: flex-start;
        }
    }

    /* ---------- Phone & below (576px) ---------- */
    @media (max-width: 575.98px) {
        .page-title h4 { font-size: 1.05rem; }
        .page-title h6 { font-size: 0.85rem; }
        .so-wrap .dataTables_filter { margin-bottom: 8px; }
        .so-wrap .dataTables_filter input { max-width: 130px; }
        .so-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- StockOut table -> stacked cards (phone & small tablet) ---------- */
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
        .so-wrap .dataTables_filter,
        .so-wrap .dataTables_length,
        .so-wrap .dataTables_info,
        .so-wrap .dataTables_paginate {
            max-width: 100%;
        }
    }

    /* ---------- Add StockOut modal products table ---------- */
    .so-modal-wrap {
        overflow-x: hidden;
    }
    .so-modal-wrap .so-products {
        table-layout: fixed;
        width: 100%;
        min-width: 0;
    }
    .so-modal-wrap .so-products th,
    .so-modal-wrap .so-products td {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .so-modal-wrap .so-products th:nth-child(1), .so-modal-wrap .so-products td:nth-child(1) { width: 22%; }
    .so-modal-wrap .so-products th:nth-child(2), .so-modal-wrap .so-products td:nth-child(2) { width: 10%; }
    .so-modal-wrap .so-products th:nth-child(3), .so-modal-wrap .so-products td:nth-child(3) { width: 13%; }
    .so-modal-wrap .so-products th:nth-child(4), .so-modal-wrap .so-products td:nth-child(4) { width: 12%; }
    .so-modal-wrap .so-products th:nth-child(5), .so-modal-wrap .so-products td:nth-child(5) { width: 14%; }
    .so-modal-wrap .so-products th:nth-child(6), .so-modal-wrap .so-products td:nth-child(6) { width: 13%; }
    .so-modal-wrap .so-products th:nth-child(7), .so-modal-wrap .so-products td:nth-child(7) { width: 16%; }

    @media (max-width: 767.98px) {
        .so-modal-wrap .so-products thead {
            display: none;
        }
        .so-modal-wrap .so-products tbody tr {
            display: block;
            margin-bottom: 12px;
            border: 1px solid #eef2f7 !important;
            border-radius: 8px;
            background: #fff;
        }
        .so-modal-wrap .so-products tbody tr td {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 10px !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            background: transparent !important;
            text-align: right;
        }
        .so-modal-wrap .so-products tbody tr td:last-child {
            border-bottom: 0 !important;
        }
        .so-modal-wrap .so-products tbody tr td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .so-modal-wrap .so-products tbody tr td .form-control {
            width: 100%;
            max-width: 100%;
        }
    }
</style>

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
                    <div class="table-responsive so-wrap">
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
                                    <th>Unit (UOM)</th>
                                    <th>Available Stock</th>
                                    <th>Job Quantity</th>
                                    <th>Used Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>

                            <tbody id="productTableBody">
                                <!-- Auto-populated by JS -->
                            </tbody>
                        </table>
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
        $('#productTableBody').empty();

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

    // Initialize when modal is shown
    $('#addStockOutModal').on('shown.bs.modal', function () {
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
        $('#productTableBody').empty();
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
        let tbody = $('#productTableBody');
        tbody.empty();
        
        if (!val) { 
            $('#add_customer_name').val(''); 
            return; 
        }
        
        let selected = $(this).find('option:selected');
        let customerName = selected.data('customer') || '-';
        $('#add_customer_name').val(customerName);

        // Fetch products for this job
        tbody.html('<tr><td colspan="7" class="text-center">Loading products...</td></tr>');
        
        $.ajax({
            url: '/get-job-products/' + val,
            type: 'GET',
            success: function(response) {
                tbody.empty();
                if(response.length === 0) {
                    tbody.html('<tr><td colspan="7" class="text-center">No products found for this job.</td></tr>');
                    return;
                }

                response.forEach(function(product, index) {
                    let stockDisplay = product.available_stock;
                    let manualNote = product.is_manual ? '<br><small class="text-danger">Not in inventory</small>' : '';
                    let usedStockHtml = product.is_manual 
                        ? `<input type="number" class="form-control" name="products[${index}][used_stock]" min="0" step="any" placeholder="Manual Item" disabled>`
                        : `<input type="number" class="form-control used-stock" name="products[${index}][used_stock]" required min="0" step="any" placeholder="Enter qty">`;

                    tbody.append(`
                        <tr>
                            <td>
                                <input type="hidden" name="products[${index}][product_id]" value="${product.product_id}">
                                <input type="text" class="form-control bg-light" value="${product.item_name}" readonly>
                                ${manualNote}
                            </td>
                            <td><input type="text" class="form-control bg-light" value="${product.unit}" readonly></td>
                            <td><input type="text" class="form-control bg-light available-stock" value="${stockDisplay}" readonly></td>
                            <td><input type="number" class="form-control bg-light" value="${product.job_quantity}" readonly></td>
                            <td>${usedStockHtml}</td>
                            <td><input type="number" class="form-control bg-light" value="${product.unit_price}" readonly></td>
                            <td><input type="number" class="form-control bg-light" value="${product.total_price}" readonly></td>
                        </tr>
                    `);
                });
            },
            error: function() {
                tbody.html('<tr><td colspan="7" class="text-center text-danger">Failed to load products.</td></tr>');
            }
        });
    });

    // Handle used stock input change and validation
    $(document).on('input', '.used-stock', function () {
        let row = $(this).closest('tr');
        let availableStock = parseFloat(row.find('.available-stock').val()) || 0;
        let usedStock = parseFloat($(this).val());

        if (isNaN(usedStock)) return;

        if (usedStock < 0) {
            alert('Used quantity cannot be negative.');
            $(this).val(0);
        } else if (usedStock > availableStock) {
            alert('Used quantity cannot exceed Available Stock (' + availableStock + ').');
            $(this).val(availableStock);
        }
    });

    // CSRF token setup for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Delete all stock out records for a job
    $(document).on("click", ".deleteJobStockOutBtn", function (e) {
        e.preventDefault();
        let jobId = $(this).data("id");
        let deleteUrl = "{{ route('delete-job-stockout') }}";

        Swal.fire({
            title: "Are you sure?",
            text: "This will delete all stock out records for this job and restore product stock!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete all!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: deleteUrl,
                    type: "DELETE",
                    data: { job_id: jobId },
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
@endpush

@include('admin_panel.include.footer_include')