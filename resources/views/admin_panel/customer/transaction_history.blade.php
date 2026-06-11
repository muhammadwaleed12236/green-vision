@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Page Header --}}
            <div class="page-header d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h4 class="mb-1 fw-bold">
                        <i class="fa fa-history me-2 text-primary"></i>Transaction History
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('customer') }}">Customers</a></li>
                            <li class="breadcrumb-item active">{{ $customer->customer_name }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('customer') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back to Customers
                </a>
            </div>

            {{-- Customer Info + Summary Cards --}}
            <div class="row g-3 mb-4">
                {{-- Customer Info Card --}}
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #4f46e5 !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-primary text-white me-3">
                                    {{ strtoupper(substr($customer->customer_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $customer->customer_name }}</h5>
                                    @if($customer->shop_name)
                                        <small class="text-muted">{{ $customer->shop_name }}</small>
                                    @endif
                                </div>
                            </div>
                            <div class="info-row">
                                <i class="fa fa-phone text-muted me-2"></i>
                                <span>{{ $customer->phone_number ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row mt-1">
                                <i class="fa fa-map-marker-alt text-muted me-2"></i>
                                <span>{{ $customer->address ?? 'N/A' }}</span>
                            </div>
                            @if($customer->city)
                            <div class="info-row mt-1">
                                <i class="fa fa-city text-muted me-2"></i>
                                <span>{{ $customer->city }} @if($customer->area) &mdash; {{ $customer->area }} @endif</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="col-md-8">
                    <div class="row g-3 h-100">
                        @php
                            $totalLocalSales   = $localSales->sum('net_amount');
                            $totalTransactions = $localSales->count();
                            $completedCount    = $localSales->where('job_status', 'completed')->count();
                            $currentBalance    = $ledger ? $ledger->closing_balance : 0;
                        @endphp
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-blue">
                                <div class="stat-icon"><i class="fa fa-receipt"></i></div>
                                <div class="stat-value">{{ $totalTransactions }}</div>
                                <div class="stat-label">Total Orders</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-green">
                                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                                <div class="stat-value">{{ $completedCount }}</div>
                                <div class="stat-label">Completed</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-purple">
                                <div class="stat-icon"><i class="fa fa-rupee-sign"></i></div>
                                <div class="stat-value" style="font-size:13px;">Rs.{{ number_format($totalLocalSales, 0) }}</div>
                                <div class="stat-label">Total Amount</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card {{ $currentBalance > 0 ? 'stat-card-red' : 'stat-card-teal' }}">
                                <div class="stat-icon"><i class="fa fa-wallet"></i></div>
                                <div class="stat-value" style="font-size:13px;">Rs.{{ number_format($currentBalance, 0) }}</div>
                                <div class="stat-label">Current Balance</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Search / Filter Bar --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body py-3">
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <input type="text" id="searchInput" class="form-control form-control-sm"
                                placeholder="🔍  Search invoice, item, date...">
                        </div>
                        <div class="col-md-3">
                            <select id="typeFilter" class="form-select form-select-sm">
                                <option value="all">All Status</option>
                                <option value="completed">Completed</option>
                                <option value="ready">Ready</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="fromDate" class="form-control form-control-sm" placeholder="From">
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="toDate" class="form-control form-control-sm" placeholder="To">
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Transactions Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-list-alt me-2 text-primary"></i>All Job Orders
                    </h6>
                    <span class="badge bg-primary" id="rowCount">0 records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="txTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Date</th>
                                    <th>Job No</th>
                                    <th>Items</th>
                                    <th class="text-end">Amount</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="txBody">

                                {{-- ── Job Orders (Local Sales) ── --}}
                                @foreach($localSales as $sale)
                                @php
                                    $items = json_decode($sale->item, true) ?? [];
                                    $itemPreview = is_array($items) ? implode(', ', array_slice($items, 0, 3)) . (count($items) > 3 ? '...' : '') : '-';
                                @endphp
                                <tr class="tx-row"
                                    data-type="job"
                                    data-status="{{ $sale->job_status }}"
                                    data-date="{{ \Carbon\Carbon::parse($sale->sale_date)->format('Y-m-d') }}"
                                    data-search="{{ strtolower($sale->invoice_number . ' ' . $itemPreview . ' ' . \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y')) }}">
                                    <td><span class="tx-index"></span></td>
                                    <td>
                                        <span class="text-dark fw-semibold">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M Y') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold">{{ $sale->invoice_number }}</span>
                                    </td>
                                    <td>
                                        <small class="text-muted">{{ $itemPreview ?: '-' }}</small>
                                    </td>
                                    <td class="text-end fw-bold text-success">
                                        Rs.&nbsp;{{ number_format($sale->net_amount, 0) }}
                                    </td>
                                    <td>
                                        @if($sale->job_status == 'completed')
                                            <span class="badge bg-success">Completed</span>
                                        @elseif($sale->job_status == 'ready')
                                            <span class="badge bg-info">Ready</span>
                                        @elseif($sale->job_status == 'pending')
                                            <span class="badge bg-secondary">Pending</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ ucfirst($sale->job_status ?? 'N/A') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('show-local-sale', $sale->id) }}"
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('local.sale.invoice', $sale->id) }}"
                                           class="btn btn-sm btn-outline-dark" title="Invoice">
                                            <i class="fa fa-file-invoice"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach

                                {{-- Empty State --}}
                                @if($localSales->isEmpty())
                                <tr id="emptyRow">
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fa fa-inbox fa-3x mb-3 d-block" style="opacity:0.3;"></i>
                                        No transactions found for this customer.
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Footer Totals --}}
                <div class="card-footer bg-white border-top d-flex justify-content-between flex-wrap gap-2 py-3">
                    <div class="text-muted small" id="visibleCount">Showing all records</div>
                    <div>
                        <strong>Grand Total:
                            <span class="text-primary">Rs.&nbsp;{{ number_format($totalLocalSales, 0) }}</span>
                        </strong>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<style>
    .avatar-circle {
        width: 48px; height: 48px;
        border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; font-weight: 700;
        flex-shrink: 0;
    }
    .info-row { font-size: 13.5px; color: #444; }

    /* Stat Cards */
    .stat-card {
        border-radius: 12px;
        padding: 16px;
        color: #fff;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        text-align: center;
        height: 100%;
        min-height: 100px;
        box-shadow: 0 2px 8px rgba(0,0,0,.1);
    }
    .stat-card-blue   { background: linear-gradient(135deg, #4f46e5, #7c3aed); }
    .stat-card-green  { background: linear-gradient(135deg, #059669, #10b981); }
    .stat-card-purple { background: linear-gradient(135deg, #7c3aed, #a855f7); }
    .stat-card-red    { background: linear-gradient(135deg, #dc2626, #ef4444); }
    .stat-card-teal   { background: linear-gradient(135deg, #0d9488, #14b8a6); }
    .stat-icon { font-size: 22px; margin-bottom: 6px; opacity: .85; }
    .stat-value { font-size: 22px; font-weight: 700; line-height: 1; }
    .stat-label { font-size: 11px; opacity: .85; margin-top: 4px; }

    /* Table */
    #txTable thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; font-weight: 600; }
    #txTable tbody tr { transition: background .15s; }
    .card { border-radius: 12px; }
    .card-header { border-radius: 12px 12px 0 0 !important; }
    .page-header .breadcrumb-item a { color: #4f46e5; text-decoration: none; }
    .page-header .breadcrumb-item a:hover { text-decoration: underline; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows   = document.querySelectorAll('.tx-row');
    const search = document.getElementById('searchInput');
    const type   = document.getElementById('typeFilter');
    const from   = document.getElementById('fromDate');
    const to     = document.getElementById('toDate');

    function applyFilters() {
        const q    = search.value.toLowerCase().trim();
        const t    = type.value;  // job status filter
        const fd   = from.value;
        const td   = to.value;
        let visible = 0;

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowDate   = row.dataset.date;
            const rowSearch = row.dataset.search;

            const matchStatus = (t === 'all') || (rowStatus === t);
            const matchSearch = !q || rowSearch.includes(q);
            const matchFrom   = !fd || rowDate >= fd;
            const matchTo     = !td || rowDate <= td;

            const show = matchStatus && matchSearch && matchFrom && matchTo;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        // Reindex visible rows
        let idx = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') {
                row.querySelector('.tx-index').textContent = idx++;
            }
        });

        document.getElementById('rowCount').textContent    = visible + ' records';
        document.getElementById('visibleCount').textContent = 'Showing ' + visible + ' of ' + rows.length + ' records';
    }

    // Initial index
    let i = 1;
    rows.forEach(r => r.querySelector('.tx-index').textContent = i++);
    document.getElementById('rowCount').textContent     = rows.length + ' records';
    document.getElementById('visibleCount').textContent = 'Showing all ' + rows.length + ' records';

    search.addEventListener('input', applyFilters);
    type.addEventListener('change', applyFilters);
    from.addEventListener('change', applyFilters);
    to.addEventListener('change', applyFilters);
});

function resetFilters() {
    document.getElementById('searchInput').value = '';
    document.getElementById('typeFilter').value  = 'all';
    document.getElementById('fromDate').value    = '';
    document.getElementById('toDate').value      = '';
    document.querySelectorAll('.tx-row').forEach(r => r.style.display = '');
    let i = 1;
    document.querySelectorAll('.tx-row').forEach(r => r.querySelector('.tx-index').textContent = i++);
    const total = document.querySelectorAll('.tx-row').length;
    document.getElementById('rowCount').textContent     = total + ' records';
    document.getElementById('visibleCount').textContent = 'Showing all ' + total + ' records';
}
</script>
