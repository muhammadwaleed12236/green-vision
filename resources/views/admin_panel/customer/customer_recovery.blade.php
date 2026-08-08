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

    /* Customer recovery table never scrolls horizontally - cells wrap to fit */
    .cr-wrap {
        overflow-x: hidden;
    }
    .cr-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .cr-wrap .datanew td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .cr-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .cr-wrap .datanew th:nth-child(1) { width: 4% !important; }
    .cr-wrap .datanew th:nth-child(2) { width: 9% !important; }
    .cr-wrap .datanew th:nth-child(3) { width: 12% !important; }
    .cr-wrap .datanew th:nth-child(4) { width: 13% !important; }
    .cr-wrap .datanew th:nth-child(5) { width: 9% !important; }
    .cr-wrap .datanew th:nth-child(6) { width: 11% !important; }
    .cr-wrap .datanew th:nth-child(7) { width: 12% !important; }
    .cr-wrap .datanew th:nth-child(8) { width: 20% !important; }
    .cr-wrap .datanew th:nth-child(9) { width: 10% !important; }
    .cr-wrap .dataTables_wrapper {
        max-width: 100%;
    }

    /* Empty-state message: keep it a full-width centered table cell */
    .cr-wrap .datanew td.dataTables_empty {
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
        .cr-wrap .datanew td:last-child:not(.dataTables_empty) {
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
        .cr-wrap .dataTables_filter { margin-bottom: 8px; }
        .cr-wrap .dataTables_filter input { max-width: 130px; }
        .cr-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- Customer recovery table -> stacked cards (phone & small tablet) ---------- */
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
        .cr-wrap .dataTables_filter,
        .cr-wrap .dataTables_length,
        .cr-wrap .dataTables_info,
        .cr-wrap .dataTables_paginate {
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
                    <h4>Customer Recoveries</h4>
                    <h6>Track all recoveries from salesmen</h6>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif

                    <div class="table-responsive cr-wrap">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Shop</th>
                                    <th>Name</th>
                                    <th>Area</th>
                                    <th>Salesman</th>
                                    <th>Amount Paid</th>
                                    <th>Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Recoveries as $key => $recovery)
                                    <tr id="recovery-row-{{ $recovery->id }}">
                                        <td data-label="#">#{{ $key + 1 }}</td>
                                        <td data-label="Date">{{ $recovery->date }}</td>
                                        <td data-label="Shop">{{ $recovery->customer->shop_name ?? 'N/A' }}</td>
                                        <td data-label="Name">{{ $recovery->customer->customer_name ?? 'N/A' }}</td>
                                        <td data-label="Area">{{ $recovery->customer->area ?? 'N/A' }}</td>
                                        <td data-label="Salesman">{{ $recovery->salesman }}</td>
                                        <td data-label="Amount Paid" class="amount_paid">{{ number_format($recovery->amount_paid, 0) }}</td>
                                        <td data-label="Remarks" class="remarks">{{ $recovery->remarks }}</td>
                                        <td data-label="Actions">
                                            <button type="button" class="btn btn-sm btn-primary text-white"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editRecoveryModal{{ $recovery->id }}">
                                                Edit
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade" id="editRecoveryModal{{ $recovery->id }}" tabindex="-1"
                                                aria-labelledby="editRecoveryModalLabel{{ $recovery->id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form method="POST"
                                                            action="{{ route('customer_recovery.update', $recovery->id) }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <div class="modal-header">
                                                                <h5 class="modal-title"
                                                                    id="editRecoveryModalLabel{{ $recovery->id }}">Edit
                                                                    Customer Recovery</h5>
                                                                <button type="button" class="btn-close text-black"
                                                                    data-bs-dismiss="modal" aria-label="Close">X</button>
                                                            </div>

                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label">Customer</label>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $recovery->customer->customer_name ?? 'N/A' }}"
                                                                        readonly>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Salesman</label>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ $recovery->salesman }}" readonly>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Current Amount Paid</label>
                                                                    <input type="text" class="form-control"
                                                                        value="{{ number_format($recovery->amount_paid, 0) }}"
                                                                        readonly>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Adjustment Type</label>
                                                                    <select name="adjust_type" class="form-select" required>
                                                                        <option value="">Select Type</option>
                                                                        <option value="plus">Plus (+)</option>
                                                                        <option value="minus">Minus (-)</option>
                                                                    </select>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Adjustment Amount</label>
                                                                    <input type="number" name="adjust_amount"
                                                                        class="form-control" min="0" step="any" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Date</label>
                                                                    <input type="date" name="date" class="form-control"
                                                                        value="{{ $recovery->date }}" required>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label">Remarks</label>
                                                                    <textarea name="remarks"
                                                                        class="form-control">{{ $recovery->remarks }}</textarea>
                                                                </div>

                                                                <div class="alert alert-danger d-none"
                                                                    id="editRecoveryError{{ $recovery->id }}"></div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <button type="submit" class="btn btn-primary">Update
                                                                    Recovery</button>
                                                                <button type="button" class="btn btn-secondary"
                                                                    data-bs-dismiss="modal">Cancel</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>

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

@include('admin_panel.include.footer_include')
