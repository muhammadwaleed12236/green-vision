@include('admin_panel.include.header_include')

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       Design/colors stay identical on every device.
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

    /* Voucher table never scrolls horizontally - cells wrap to fit */
    .jv-wrap {
        overflow-x: hidden;
    }
    .jv-table {
        table-layout: fixed;
        width: 100%;
    }
    .jv-wrap .jv-table td {
        white-space: normal;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .jv-wrap .jv-table th {
        white-space: normal;
        word-break: break-word;
    }
    .jv-table th:nth-child(1) { width: 5%; }
    .jv-table th:nth-child(2) { width: 11%; }
    .jv-table th:nth-child(3) { width: 10%; }
    .jv-table th:nth-child(4) { width: 8%; }
    .jv-table th:nth-child(5) { width: 9%; }
    .jv-table th:nth-child(6) { width: 20%; }
    .jv-table th:nth-child(7) { width: 11%; }
    .jv-table th:nth-child(8) { width: 14%; }
    .jv-table th:nth-child(9) { width: 12%; }

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

        .card .card-body {
            padding: 14px;
        }

        .page-title h4 { font-size: 1.05rem; }
        .page-title h6 { font-size: 0.85rem; }

        /* Stats values readable on tiny screens */
        .card-body h3 { font-size: 1.15rem; }

        /* Filter labels + button full width */
        .filter-card .form-group { margin-bottom: 12px; }
    }

    /* ---------- Voucher table -> stacked cards (phone & small tablet) ---------- */
    @media (max-width: 767.98px) {
        .table-responsive .jv-table thead {
            display: none !important;
        }
        .table-responsive .jv-table,
        .table-responsive .jv-table tbody,
        .table-responsive .jv-table tr,
        .table-responsive .jv-table td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .table-responsive .jv-table {
            border: 0 !important;
        }
        .table-responsive .jv-table tbody {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
        }
        .table-responsive .jv-table tbody tr {
            background: #fff;
            border: 1px solid #eef2f7 !important;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 4px 14px;
            margin: 0 !important;
        }
        .table-responsive .jv-table tbody tr:hover {
            background: #fff;
        }
        .table-responsive .jv-table td {
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
        .table-responsive .jv-table td:last-child {
            border-bottom: 0 !important;
        }
        .table-responsive .jv-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .table-responsive .jv-table td .btn {
            padding: 0.35rem 0.6rem;
        }
        .table-responsive .jv-table td .btn i {
            font-size: 0.8rem;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- PAGE HEADER --}}
            <div class="page-header">
                <div class="page-title">
                    <h4>📒 Journal Vouchers</h4>
                    <h6>Manage all Payment & Receipt Vouchers</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-arrow-up"></i> Payment Voucher
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#receiptModal">
                        <i class="fas fa-arrow-down"></i> Receipt Voucher
                    </button>
                </div>
            </div>

            {{-- STATS CARDS --}}
            <div class="row">
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-danger">Today's Payments</h5>
                            <h3>₨ {{ number_format($stats['today_payments'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-success">Today's Receipts</h5>
                            <h3>₨ {{ number_format($stats['today_receipts'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-danger">Month Payments</h5>
                            <h3>₨ {{ number_format($stats['total_payments'], 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="text-success">Month Receipts</h5>
                            <h3>₨ {{ number_format($stats['total_receipts'], 2) }}</h3>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILTERS --}}
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('journal-voucher.index') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ request('from_date', now()->startOfMonth()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-3">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ request('to_date', now()->format('Y-m-d')) }}">
                            </div>
                            <div class="col-md-2">
                                <label>Voucher Type</label>
                                <select name="voucher_type" class="form-control">
                                    <option value="">All</option>
                                    <option value="payment" {{ request('voucher_type') == 'payment' ? 'selected' : '' }}>Payment</option>
                                    <option value="receipt" {{ request('voucher_type') == 'receipt' ? 'selected' : '' }}>Receipt</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Party Type</label>
                                <select name="party_type" class="form-control">
                                    <option value="">All</option>
                                    <option value="vendor" {{ request('party_type') == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                    <option value="customer" {{ request('party_type') == 'customer' ? 'selected' : '' }}>Customer</option>
                                    <option value="contractor" {{ request('party_type') == 'contractor' ? 'selected' : '' }}>Contractor</option>
                                    <option value="staff" {{ request('party_type') == 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="expense" {{ request('party_type') == 'expense' ? 'selected' : '' }}>Expense</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">Filter</button>
                            </div>
                        </div>
                        
                        {{-- FILTERED TOTALS --}}
                        @if(request()->has('from_date') || request()->has('to_date') || request()->has('voucher_type') || request()->has('party_type'))
                            <div class="row mt-3 pt-3 border-top">
                                <div class="col-md-6">
                                    <h5 class="text-danger">📤 Total Payments: ₨ {{ number_format($filteredTotals['filtered_payments'], 2) }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="text-success">📥 Total Receipts: ₨ {{ number_format($filteredTotals['filtered_receipts'], 2) }}</h5>
                                </div>
                                <div class="col-md-12 mt-2">
                                    <h6 class="text-primary">💰 Net: ₨ {{ number_format($filteredTotals['filtered_receipts'] - $filteredTotals['filtered_payments'], 2) }}</h6>
                                </div>
                            </div>
                        @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- VOUCHERS TABLE --}}
            <div class="card">
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive jv-wrap">
                    <table class="table datanew jv-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Voucher No</th>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Party Type</th>
                                <th>Party Name</th>
                                <th>Amount</th>
                                <th>Narration</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($vouchers as $k => $v)
                                <tr>
                                    <td data-label="No.">{{ $k + 1 }}</td>
                                    <td data-label="Voucher No"><strong>{{ $v->voucher_no }}</strong></td>
                                    <td data-label="Date">{{ \Carbon\Carbon::parse($v->voucher_date)->format('d M Y') }}</td>
                                    <td data-label="Type">
                                        @if($v->voucher_type == 'payment')
                                            <span class="badge bg-danger">Payment</span>
                                        @else
                                            <span class="badge bg-success">Receipt</span>
                                        @endif
                                    </td>
                                    <td data-label="Party Type"><span class="badge bg-info">{{ ucfirst($v->party_type) }}</span></td>
                                    <td data-label="Party Name">{{ $v->party_name }}</td>
                                    <td data-label="Amount">
                                        @if($v->voucher_type == 'payment')
                                            <span class="text-danger">₨ {{ number_format($v->debit_amount, 2) }}</span>
                                        @else
                                            <span class="text-success">₨ {{ number_format($v->credit_amount, 2) }}</span>
                                        @endif
                                    </td>
                                    <td data-label="Narration">{{ Str::limit($v->narration, 30) }}</td>
                                    <td data-label="Action">
                                        <a href="{{ route('journal-voucher.print', $v->id) }}" class="btn btn-sm btn-primary" target="_blank">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <button class="btn btn-sm btn-danger deleteVoucherBtn" data-id="{{ $v->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center">No vouchers found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $vouchers->appends(request()->query())->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- PAYMENT VOUCHER MODAL --}}
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="paymentForm">
                @csrf
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">↑ Payment Voucher (Money Out)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Payment:</strong> Pay to Vendor, Contractor, Staff, or record Expense
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Party Type <span class="text-danger">*</span></label>
                            <select name="party_type" id="payment_party_type" class="form-control" required>
                                <option value="">Select Party Type</option>
                                <option value="vendor">Vendor (Supplier)</option>
                                <option value="contractor">Contractor</option>
                                <option value="staff">Staff</option>
                                <option value="expense">Expense</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Select Party <span class="text-danger">*</span></label>
                            <select name="party_id" id="payment_party_id" class="form-control" required>
                                <option value="">Select party type first</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Enter amount" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="voucher_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Account / Bank <span class="text-danger">*</span></label>
                        <select class="form-select" name="account_id" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->category->name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="narration" class="form-control" rows="3" placeholder="Payment details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-save"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- RECEIPT VOUCHER MODAL --}}
<div class="modal fade" id="receiptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="receiptForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">↓ Receipt Voucher (Money In)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> <strong>Receipt:</strong> Receive from Customer, Contractor, Vendor, or Staff
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Party Type <span class="text-danger">*</span></label>
                            <select name="party_type" id="receipt_party_type" class="form-control" required>
                                <option value="">Select Party Type</option>
                                <option value="customer">Customer</option>
                                <option value="vendor">Vendor (Refund/Return)</option>
                                <option value="contractor">Contractor</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Select Party <span class="text-danger">*</span></label>
                            <select name="party_id" id="receipt_party_id" class="form-control" required>
                                <option value="">Select party type first</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label>Amount <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control" placeholder="Enter amount" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Date <span class="text-danger">*</span></label>
                            <input type="date" name="voucher_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Account / Bank <span class="text-danger">*</span></label>
                        <select class="form-select" name="account_id" required>
                            <option value="">Select Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->name }} ({{ $account->category->name ?? '' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Description</label>
                        <textarea name="narration" class="form-control" rows="3" placeholder="Receipt details..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Save Receipt
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- JAVASCRIPT --}}
<script>
$(document).ready(function() {
    // Load parties when party type changes - PAYMENT
    $('#payment_party_type').on('change', function() {
        let type = $(this).val();
        if (!type) {
            $('#payment_party_id').html('<option value="">Select party type first</option>');
            return;
        }

        $.ajax({
            url: '{{ url("/journal-voucher/parties") }}/' + type,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Select Party</option>';
                    response.parties.forEach(function(party) {
                        let bal = parseFloat(party.balance) || 0;
                        let balStr = bal === 0 ? '0' : Math.abs(bal) + (bal < 0 ? ' Dr' : ' Cr');
                        options += `<option value="${party.id}">${party.name} (Balance: ₨${balStr})</option>`;
                    });
                    $('#payment_party_id').html(options);
                } else {
                    Swal.fire('Error', 'Could not load parties', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Could not load parties', 'error');
            }
        });
    });

    // Load parties when party type changes - RECEIPT
    $('#receipt_party_type').on('change', function() {
        let type = $(this).val();
        if (!type) {
            $('#receipt_party_id').html('<option value="">Select party type first</option>');
            return;
        }

        $.ajax({
            url: '{{ url("/journal-voucher/parties") }}/' + type,
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Select Party</option>';
                    response.parties.forEach(function(party) {
                        let bal = parseFloat(party.balance) || 0;
                        let balStr = bal === 0 ? '0' : Math.abs(bal) + (bal < 0 ? ' Dr' : ' Cr');
                        options += `<option value="${party.id}">${party.name} (Balance: ₨${balStr})</option>`;
                    });
                    $('#receipt_party_id').html(options);
                } else {
                    Swal.fire('Error', 'Could not load parties', 'error');
                }
            },
            error: function() {
                Swal.fire('Error', 'Could not load parties', 'error');
            }
        });
    });

    // Submit Payment Form
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("journal-voucher.payment") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errorMsg = xhr.responseJSON?.message || 'Something went wrong';
                let debug = xhr.responseJSON?.debug;
                if (debug) {
                    errorMsg += '<br><small>Line: ' + debug.line + ' in ' + debug.file + '</small>';
                }
                Swal.fire('Error', errorMsg, 'error');
            }

        });
    });

    // Submit Receipt Form
    $('#receiptForm').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '{{ route("journal-voucher.receipt") }}',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                if (response.success) {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function(xhr) {
                let errors = xhr.responseJSON?.errors;
                if (errors) {
                    let errorMsg = Object.values(errors).flat().join('<br>');
                    Swal.fire('Validation Error', errorMsg, 'error');
                } else {
                    Swal.fire('Error', 'Something went wrong', 'error');
                }
            }
        });
    });

    // Delete Voucher
    $(document).on('click', '.deleteVoucherBtn', function() {
        let id = $(this).data('id');
        
        Swal.fire({
            title: 'Are you sure?',
            text: "This voucher will be deleted!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{ url("/journal-voucher") }}/' + id,
                    method: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Deleted!', response.message, 'success').then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Something went wrong', 'error');
                    }
                });
            }
        });
    });
});
</script>
