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

    /* Ledger table never scrolls horizontally - cells wrap to fit */
    .vl-wrap {
        overflow-x: hidden;
    }
    .vl-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .vl-wrap .datanew td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .vl-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .vl-wrap .datanew th:nth-child(1) { width: 6% !important; }
    .vl-wrap .datanew th:nth-child(2) { width: 14% !important; }
    .vl-wrap .datanew th:nth-child(3) { width: 14% !important; }
    .vl-wrap .datanew th:nth-child(4) { width: 22% !important; }
    .vl-wrap .datanew th:nth-child(5) { width: 15% !important; }
    .vl-wrap .datanew th:nth-child(6) { width: 15% !important; }
    .vl-wrap .datanew th:nth-child(7) { width: 14% !important; }
    .vl-wrap .dataTables_wrapper {
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
        .page-title h6 { font-size: 0.85rem; }
        .vl-wrap .dataTables_filter { margin-bottom: 8px; }
        .vl-wrap .dataTables_filter input { max-width: 130px; }
        .vl-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- Ledger table -> stacked cards (phone & small tablet) ---------- */
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
        .vl-wrap .dataTables_filter,
        .vl-wrap .dataTables_length,
        .vl-wrap .dataTables_info,
        .vl-wrap .dataTables_paginate {
            max-width: 100%;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Vendors Ledger Management</h4>
                    <h6>Manage Vendors Ledger Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif

                    <div class="table-responsive vl-wrap">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Party Code</th>
                                    <th>Party Name</th>
                                    <th>Opening Balance</th>
                                    <th>Previous Balance</th>
                                    <th>Closing Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if($VendorLedgers->isEmpty())
                                    <script>
                                        document.addEventListener("DOMContentLoaded", function () {
                                            document.getElementById("global-loader").style.display = "none";
                                        });
                                    </script>
                                @endif
                                @forelse($VendorLedgers as $ledger)
                                    <tr>
                                        <td data-label="ID">{{ $ledger->vendor_id }}</td>
                                        <td data-label="Date">{{ $ledger->updated_at->format('Y-m-d') }}</td>
                                        <td data-label="Party Code">{{ $ledger->vendor->Party_code }}</td>
                                        <td data-label="Party Name">{{ $ledger->vendor->Party_name }}</td>
                                        <td data-label="Opening">{{ number_format($ledger->opening_balance, 0) }}</td>
                                        <td data-label="Previous">{{ number_format($ledger->previous_balance, 0) }}</td>
                                        <td data-label="Closing" id="closing_balance_{{ $ledger->id }}">
                                            {{ number_format($ledger->closing_balance, 0) }}</td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No records found.</td>
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
</div>

@include('admin_panel.include.footer_include')
