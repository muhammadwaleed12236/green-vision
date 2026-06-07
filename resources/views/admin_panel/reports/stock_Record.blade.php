@include('admin_panel.include.header_include')

<style>
    .report-title {
        color: #333;
        font-weight: 700;
        border-bottom: 2px solid #55acee;
        display: inline-block;
        padding-bottom: 5px;
        margin-bottom: 20px;
    }
    .filter-box {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #eee;
        margin-bottom: 25px;
    }
    .table thead th {
        background-color: #f1f4f6;
        color: #444;
        font-weight: 600;
        text-align: center;
        border-bottom: 2px solid #dee2e6;
    }
    .table tbody td {
        text-align: center;
        vertical-align: middle;
    }
    .stock-plus { color: #28a745; font-weight: bold; }
    .stock-minus { color: #dc3545; font-weight: bold; }
    .balance-box {
        background-color: #eef2f7;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 4px;
        color: #2c3e50;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h3 class="report-title">Item Stock Report</h3>

                    <div class="filter-box">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">From Date</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">To Date</label>
                                <input type="date" id="end_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Item Name / Code</label>
                                <input type="text" id="item_search" class="form-control" placeholder="Search item...">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button class="btn btn-primary w-100 fw-bold" id="btn_search">
                                    <i data-feather="search" class="me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th style="text-align: left; width: 30%;">Item Name</th>
                                    <th>Measurements</th>
                                    <th>Purchase Qty</th>
                                    <th>Sold Qty</th>
                                    <th>Available Balance</th>
                                    <th>Purchase Price</th>
                                    <th>Stock Value</th>
                                </tr>
                            </thead>
                            <tbody id="report_body">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Please select dates and click search.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 p-3 bg-light rounded d-flex justify-content-between align-items-center border">
                        <div>
                            <span class="text-muted fw-bold">Stock Summary:</span>
                            <span class="ms-3 text-secondary">Total Stock Value: <span class="text-primary fw-bold">Rs. <span id="grand_total_value">0.00</span></span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).ready(function() {

        // Auto-fetch if needed or wait for button
        // fetchStock();

        // Search-as-you-type (Google styles)
        let debounceTimer;
        $('#item_search').on('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                fetchStock();
            }, 300); // 300ms delay
        });

        $('#btn_search').on('click', function() {
            fetchStock();
        });

        function fetchStock() {
            let start = $('#start_date').val();
            let end = $('#end_date').val();
            let search = $('#item_search').val();

            $('#report_body').html('<tr><td colspan="7" class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div> Loading...</td></tr>');

            $.ajax({
                url: "{{ route('get.item.details') }}",
                type: "GET",
                data: {
                    start_date: start,
                    end_date: end,
                    search: search
                },
                success: function(items) {
                    let html = '';
                    let totalVal = 0;

                    if (items.length === 0) {
                        $('#report_body').html('<tr><td colspan="7" class="text-center py-4 text-muted">No data found.</td></tr>');
                        return;
                    }

                    items.forEach(item => {
                        totalVal += Number(item.stock_value ?? 0);

                        html += `
                            <tr>
                                <td style="text-align: left;">
                                    <span class="fw-bold">${item.item_name}</span><br>
                                    <small class="text-muted">${item.item_code}</small>
                                </td>
                                <td><small>${item.height} x ${item.width} (${item.area})</small></td>
                                <td class="stock-plus">${item.total_stock_in ?? 0}</td>
                                <td class="stock-minus">${item.total_stock_out ?? 0}</td>
                                <td><span class="balance-box">${item.balance_stock ?? 0}</span></td>
                                <td>Rs. ${Number(item.avg_purchase_rate ?? 0).toLocaleString()}</td>
                                <td class="fw-bold text-dark">Rs. ${Number(item.stock_value ?? 0).toLocaleString()}</td>
                            </tr>
                        `;
                    });

                    $('#report_body').html(html);
                    $('#grand_total_value').text(totalVal.toLocaleString(undefined, {minimumFractionDigits: 2}));
                },
                error: function() {
                    $('#report_body').html('<tr><td colspan="7" class="text-center py-4 text-danger">Error loading data.</td></tr>');
                }
            });
        }
    });
</script>
