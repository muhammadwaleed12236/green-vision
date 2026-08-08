@include('admin_panel.include.header_include')

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
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

    /* Returns table never scrolls horizontally - cells wrap to fit */
    .pch3-wrap {
        overflow-x: hidden;
    }
    .pch3-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .pch3-wrap .datanew td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .pch3-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .pch3-wrap .datanew th:nth-child(1) { width: 15% !important; }
    .pch3-wrap .datanew th:nth-child(2) { width: 14% !important; }
    .pch3-wrap .datanew th:nth-child(3) { width: 22% !important; }
    .pch3-wrap .datanew th:nth-child(4) { width: 15% !important; }
    .pch3-wrap .datanew th:nth-child(5) { width: 19% !important; }
    .pch3-wrap .datanew th:nth-child(6) { width: 15% !important; }
    .pch3-wrap .dataTables_wrapper {
        max-width: 100%;
    }
    .pch3-wrap .collapse .table {
        table-layout: fixed;
        width: 100%;
    }
    .pch3-wrap .collapse .table th,
    .pch3-wrap .collapse .table td {
        white-space: normal;
        word-break: break-word;
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
        .page-title h6 { font-size: 0.85rem; }
        .pch3-wrap .dataTables_filter { margin-bottom: 8px; }
        .pch3-wrap .dataTables_filter input { max-width: 130px; }
        .pch3-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- Returns table -> stacked cards (phone & small tablet) ---------- */
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
        .pch3-wrap .dataTables_filter,
        .pch3-wrap .dataTables_length,
        .pch3-wrap .dataTables_info,
        .pch3-wrap .dataTables_paginate {
            max-width: 100%;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">
                    <h4>Purchase Returns</h4>
                    <h6>View all purchase return records</h6>
                </div>
                <a href="{{ route('purchase.return.form') }}" class="btn btn-primary shadow-sm">
                    <i class="fa fa-plus me-1"></i>Add Purchase Return
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive pch3-wrap">
                        <table class="table table-hover datanew">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Return Date</th>
                                    <th>Vendor Name</th>
                                    <th>Items</th>
                                    <th class="text-end">Total Return Amount</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($Purchases as $purchase)
                                    @php
                                        $items = json_decode($purchase->item ?? '[]', true) ?: [];
                                        $returnQtys = json_decode($purchase->return_qty ?? '[]', true) ?: [];
                                        $itemCount = is_array($items) ? count($items) : 0;
                                    @endphp
                                    <tr>
                                        <td data-label="Invoice #">
                                            <span class="fw-bold text-primary">{{ $purchase->purchase->invoice_number ?? 'N/A' }}</span>
                                        </td>
                                        <td data-label="Return Date">
                                            {{ \Carbon\Carbon::parse($purchase->return_date)->format('d M Y') }}
                                        </td>
                                        <td data-label="Vendor">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-danger text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                    {{ strtoupper(substr($purchase->purchase->vendor?->Party_name ?? 'N', 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold">{{ $purchase->purchase->vendor?->Party_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td data-label="Items">
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#items-{{ $purchase->id }}">
                                                <i class="fa fa-list me-1"></i>{{ $itemCount }} Items
                                            </button>
                                            <div class="collapse mt-2" id="items-{{ $purchase->id }}">
                                                <div class="card card-body p-2">
                                                    <table class="table table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Rate</th>
                                                                <th>Discount</th>
                                                                <th>Return Qty</th>
                                                                <th>Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if(is_array($items))
                                                                @foreach($items as $index => $item)
                                                                    @php
                                                                        $rates = json_decode($purchase->rate ?? '[]', true) ?: [];
                                                                        $discounts = json_decode($purchase->discount ?? '[]', true) ?: [];
                                                                        $amounts = json_decode($purchase->return_amount ?? '[]', true) ?: [];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $item }}</td>
                                                                        <td>PKR {{ $rates[$index] ?? 0 }}</td>
                                                                        <td>PKR {{ $discounts[$index] ?? 0 }}</td>
                                                                        <td>{{ $returnQtys[$index] ?? 0 }}</td>
                                                                        <td>PKR {{ number_format($amounts[$index] ?? 0, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Total Return" class="text-end">
                                            <span class="fw-bold text-danger fs-6">PKR {{ number_format($purchase->total_return_amount ?? 0) }}</span>
                                        </td>
                                        <td data-label="Created At">
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($purchase->created_at)->format('d M Y h:i A') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No purchase returns found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<style>
    .avatar {
        font-weight: 600;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(220, 53, 69, 0.05);
    }
    .card {
        border: none;
        border-radius: 10px;
    }
</style>
