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
                        <i class="fa fa-history me-2 text-warning"></i>Vendor Purchase History
                    </h4>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('vendors') }}">Vendors</a></li>
                            <li class="breadcrumb-item active">{{ $vendor->Party_name }}</li>
                        </ol>
                    </nav>
                </div>
                <a href="{{ route('vendors') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left me-1"></i> Back to Vendors
                </a>
            </div>

            {{-- Vendor Info + Summary Cards --}}
            <div class="row g-3 mb-4">
                {{-- Vendor Info Card --}}
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #d97706 !important;">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-circle bg-warning text-white me-3">
                                    {{ strtoupper(substr($vendor->Party_name, 0, 1)) }}
                                </div>
                                <div>
                                    <h5 class="mb-0 fw-bold">{{ $vendor->Party_name }}</h5>
                                    <small class="text-muted">Code: {{ $vendor->Party_code }}</small>
                                </div>
                            </div>
                            <div class="info-row">
                                <i class="fa fa-phone text-muted me-2"></i>
                                <span>{{ $vendor->Party_phone ?? 'N/A' }}</span>
                            </div>
                            <div class="info-row mt-1">
                                <i class="fa fa-map-marker-alt text-muted me-2"></i>
                                <span>{{ $vendor->Party_address ?? 'N/A' }}</span>
                            </div>
                            @if($vendor->City)
                            <div class="info-row mt-1">
                                <i class="fa fa-city text-muted me-2"></i>
                                <span>{{ $vendor->City }} @if($vendor->Area) &mdash; {{ $vendor->Area }} @endif</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Stats Cards --}}
                <div class="col-md-8">
                    <div class="row g-3 h-100">
                        @php
                            $totalPurchases   = $purchases->sum('grand_total');
                            $paidPurchases    = $purchases->where('status', 'Paid')->count();
                            $unpaidPurchases  = $purchases->where('status', 'Unpaid')->count();
                            $currentBalance   = $ledger ? $ledger->closing_balance : 0;
                        @endphp
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-amber">
                                <div class="stat-icon"><i class="fa fa-shopping-cart"></i></div>
                                <div class="stat-value">{{ $purchases->count() }}</div>
                                <div class="stat-label">Total Purchases</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-green">
                                <div class="stat-icon"><i class="fa fa-check-circle"></i></div>
                                <div class="stat-value">{{ $paidPurchases }}</div>
                                <div class="stat-label">Paid</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-red">
                                <div class="stat-icon"><i class="fa fa-clock"></i></div>
                                <div class="stat-value">{{ $unpaidPurchases }}</div>
                                <div class="stat-label">Unpaid</div>
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <div class="stat-card stat-card-indigo">
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
                        <div class="col-md-5">
                            <input type="text" id="searchInput" class="form-control form-control-sm"
                                placeholder="🔍  Search invoice, item, date...">
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="fromDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <input type="date" id="toDate" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <select id="statusFilter" class="form-select form-select-sm">
                                <option value="all">All Status</option>
                                <option value="Paid">Paid</option>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Returned">Returned</option>
                            </select>
                        </div>
                        <div class="col-md-1">
                            <button class="btn btn-sm btn-outline-secondary w-100" onclick="resetFilters()">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Purchases Table --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold">
                        <i class="fa fa-list-alt me-2 text-warning"></i>All Purchases
                    </h6>
                    <span class="badge bg-warning text-dark" id="rowCount">0 records</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="txTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width:50px;">#</th>
                                    <th>Date</th>
                                    <th>Invoice #</th>
                                    <th>Items</th>
                                    <th class="text-end">Grand Total</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="txBody">
                                @forelse($purchases as $purchase)
                                @php
                                    $items       = $purchase->item ?? [];
                                    $itemPreview = is_array($items) ? implode(', ', array_slice($items, 0, 3)) . (count($items) > 3 ? '...' : '') : '-';
                                    $statusVal   = $purchase->return_status == 1 ? 'Returned' : ($purchase->status ?? 'Completed');
                                @endphp
                                <tr class="tx-row"
                                    data-date="{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('Y-m-d') }}"
                                    data-status="{{ $statusVal }}"
                                    data-search="{{ strtolower($purchase->invoice_number . ' ' . $itemPreview . ' ' . \Carbon\Carbon::parse($purchase->purchase_date)->format('d-m-Y')) }}">
                                    <td><span class="tx-index"></span></td>
                                    <td>
                                        <span class="text-dark fw-semibold">
                                            {{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border fw-bold">
                                            {{ $purchase->invoice_number }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $itemPreview ?: '-' }}
                                            @if(is_array($items) && count($items) > 0)
                                                <span class="badge bg-secondary ms-1">{{ count($items) }} items</span>
                                            @endif
                                        </small>
                                    </td>
                                    <td class="text-end fw-bold text-dark">
                                        Rs.&nbsp;{{ number_format($purchase->grand_total, 0) }}
                                    </td>
                                    <td>
                                        @if($statusVal === 'Returned')
                                            <span class="badge bg-danger">Returned</span>
                                        @elseif($statusVal === 'Paid')
                                            <span class="badge bg-success">Paid</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $statusVal }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('purchase.invoice', $purchase->id) }}"
                                           class="btn btn-sm btn-outline-dark" title="Invoice">
                                            <i class="fa fa-file-invoice"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fa fa-inbox fa-3x mb-3 d-block" style="opacity:0.3;"></i>
                                        No purchases found from this vendor.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                {{-- Footer Totals --}}
                <div class="card-footer bg-white border-top d-flex justify-content-between flex-wrap gap-2 py-3">
                    <div class="text-muted small" id="visibleCount">Showing all records</div>
                    <div>
                        <strong class="me-3">Grand Total Purchased:
                            <span class="text-dark">Rs.&nbsp;{{ number_format($totalPurchases, 0) }}</span>
                        </strong>
                        <strong>Balance Due:
                            <span class="text-danger">Rs.&nbsp;{{ number_format($currentBalance, 0) }}</span>
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
    .stat-card-amber  { background: linear-gradient(135deg, #d97706, #f59e0b); }
    .stat-card-green  { background: linear-gradient(135deg, #059669, #10b981); }
    .stat-card-red    { background: linear-gradient(135deg, #dc2626, #ef4444); }
    .stat-card-indigo { background: linear-gradient(135deg, #4338ca, #6366f1); }
    .stat-icon { font-size: 22px; margin-bottom: 6px; opacity: .85; }
    .stat-value { font-size: 22px; font-weight: 700; line-height: 1; }
    .stat-label { font-size: 11px; opacity: .85; margin-top: 4px; }

    #txTable thead th { font-size: 12px; text-transform: uppercase; letter-spacing: .5px; color: #6b7280; font-weight: 600; }
    .card { border-radius: 12px; }
    .card-header { border-radius: 12px 12px 0 0 !important; }
    .page-header .breadcrumb-item a { color: #d97706; text-decoration: none; }
    .page-header .breadcrumb-item a:hover { text-decoration: underline; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows   = document.querySelectorAll('.tx-row');
    const search = document.getElementById('searchInput');
    const status = document.getElementById('statusFilter');
    const from   = document.getElementById('fromDate');
    const to     = document.getElementById('toDate');

    function applyFilters() {
        const q   = search.value.toLowerCase().trim();
        const s   = status.value;
        const fd  = from.value;
        const td  = to.value;
        let visible = 0;

        rows.forEach(row => {
            const rowStatus = row.dataset.status;
            const rowDate   = row.dataset.date;
            const rowSearch = row.dataset.search;

            const matchStatus = (s === 'all') || (rowStatus === s);
            const matchSearch = !q || rowSearch.includes(q);
            const matchFrom   = !fd || rowDate >= fd;
            const matchTo     = !td || rowDate <= td;

            const show = matchStatus && matchSearch && matchFrom && matchTo;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        let idx = 1;
        rows.forEach(row => {
            if (row.style.display !== 'none') row.querySelector('.tx-index').textContent = idx++;
        });

        document.getElementById('rowCount').textContent     = visible + ' records';
        document.getElementById('visibleCount').textContent = 'Showing ' + visible + ' of ' + rows.length + ' records';
    }

    let i = 1;
    rows.forEach(r => r.querySelector('.tx-index').textContent = i++);
    document.getElementById('rowCount').textContent     = rows.length + ' records';
    document.getElementById('visibleCount').textContent = 'Showing all ' + rows.length + ' records';

    search.addEventListener('input', applyFilters);
    status.addEventListener('change', applyFilters);
    from.addEventListener('change', applyFilters);
    to.addEventListener('change', applyFilters);
});

function resetFilters() {
    document.getElementById('searchInput').value  = '';
    document.getElementById('statusFilter').value = 'all';
    document.getElementById('fromDate').value     = '';
    document.getElementById('toDate').value       = '';
    document.querySelectorAll('.tx-row').forEach(r => r.style.display = '');
    let i = 1;
    document.querySelectorAll('.tx-row').forEach(r => r.querySelector('.tx-index').textContent = i++);
    const total = document.querySelectorAll('.tx-row').length;
    document.getElementById('rowCount').textContent     = total + ' records';
    document.getElementById('visibleCount').textContent = 'Showing all ' + total + ' records';
}
</script>
