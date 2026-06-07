@include('admin_panel.include.header_include')

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

                    <table class="table datanew">
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
                                    <td>{{ $k + 1 }}</td>
                                    <td><strong>{{ $v->voucher_no }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($v->voucher_date)->format('d M Y') }}</td>
                                    <td>
                                        @if($v->voucher_type == 'payment')
                                            <span class="badge bg-danger">Payment</span>
                                        @else
                                            <span class="badge bg-success">Receipt</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-info">{{ ucfirst($v->party_type) }}</span></td>
                                    <td>{{ $v->party_name }}</td>
                                    <td>
                                        @if($v->voucher_type == 'payment')
                                            <span class="text-danger">₨ {{ number_format($v->debit_amount, 2) }}</span>
                                        @else
                                            <span class="text-success">₨ {{ number_format($v->credit_amount, 2) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($v->narration, 30) }}</td>
                                    <td>
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
