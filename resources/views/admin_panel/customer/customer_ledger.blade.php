@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Customer Ledger Management</h4>
                    <h6>Manage Customer Ledger Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover datanew">
                            <thead>
                                <tr>
                                    <th>Customer ID</th>
                                    <th>Customer Name</th>
                                    <th>Opening Balance</th>
                                    <th>Previous Balance</th>
                                    <th>Closing Balance</th>
                                    <th>Last Updated</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($CustomerLedgers as $ledger)
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary">{{ $ledger->customer_id }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-success text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                    {{ strtoupper(substr($ledger->Customer ? $ledger->Customer->customer_name : 'N', 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold">{{ $ledger->Customer ? $ledger->Customer->customer_name : '-' }}</span>
                                            </div>
                                        </td>
                                        <td>Rs. {{ number_format($ledger->opening_balance, 0) }}</td>
                                        <td>Rs. {{ number_format($ledger->previous_balance, 0) }}</td>
                                        <td id="closing_balance_{{ $ledger->id }}">
                                            <span class="fw-bold text-danger">Rs. {{ number_format($ledger->closing_balance, 0) }}</span>
                                        </td>
                                        <td><small class="text-muted">{{ $ledger->updated_at->format('d M Y h:i A') }}</small></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button class="btn btn-info quick-view-btn"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#historyModal"
                                                    data-customer-id="{{ $ledger->customer_id }}"
                                                    data-customer-name="{{ $ledger->Customer ? $ledger->Customer->customer_name : 'N/A' }}"
                                                    title="Payment History">
                                                    <i class="fa fa-history"></i>
                                                </button>
                                                <button class="btn btn-success" data-bs-toggle="modal"
                                                    data-bs-target="#recoveryModal"
                                                    data-id="{{ $ledger->id }}"
                                                    data-customer-name="{{ $ledger->Customer ? $ledger->Customer->customer_name : 'N/A' }}"
                                                    data-closing-balance="{{ $ledger->closing_balance }}"
                                                    title="Add Recovery">
                                                    <i class="fa fa-plus"></i> Recovery
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No customer ledgers found</td>
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

<!-- Recovery Modal -->
<div class="modal fade" id="recoveryModal" tabindex="-1" aria-labelledby="recoveryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="recoveryModalLabel">
                    <i class="fa fa-money-bill-wave me-2"></i>Add Customer Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Customer:</strong> <span id="customer_name_display"></span>
                </div>
                <form id="recoveryForm">
                    @csrf
                    <input type="hidden" id="ledger_id" name="ledger_id">
                    <div class="mb-3">
                        <label for="closing_balance" class="form-label fw-bold">Current Balance</label>
                        <input type="text" class="form-control form-control-lg fw-bold text-danger" id="closing_balance" name="closing_balance" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="amount_paid" class="form-label fw-bold">Amount Paid <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount_paid" name="amount_paid" placeholder="Enter amount" required>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label fw-bold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="date" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label for="remarks" class="form-label fw-bold">Remarks</label>
                        <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Optional notes..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="fa fa-check me-2"></i>Save Payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="historyModalLabel">
                    <i class="fa fa-history me-2"></i>Payment History
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6 class="fw-bold">Customer: <span id="history_customer_name" class="text-primary"></span></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Payment Date</th>
                                <th>Amount Paid</th>
                                <th>Remarks</th>
                                <th>Recorded At</th>
                            </tr>
                        </thead>
                        <tbody id="history_body">
                            <tr>
                                <td colspan="5" class="text-center">
                                    <div class="spinner-border spinner-border-sm" role="status"></div>
                                    Loading...
                                </td>
                            </tr>
                        </tbody>
                    </table>
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
        background-color: rgba(40, 167, 69, 0.05);
    }
    .card {
        border: none;
        border-radius: 10px;
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Recovery Modal
        var recoveryModal = document.getElementById('recoveryModal');
        recoveryModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var ledgerId = button.getAttribute('data-id');
            var closingBalance = button.getAttribute('data-closing-balance');
            var customerName = button.getAttribute('data-customer-name');

            document.getElementById('ledger_id').value = ledgerId;
            document.getElementById('closing_balance').value = 'Rs. ' + Number(closingBalance).toLocaleString();
            document.getElementById('customer_name_display').textContent = customerName;
        });

        // Recovery Form Submit
        document.getElementById('recoveryForm').addEventListener('submit', function (event) {
            event.preventDefault();

            var formData = new FormData(this);
            fetch("{{ route('customer-recovery-store') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name="_token"]').value
                },
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        var ledgerId = document.getElementById('ledger_id').value;
                        var newClosingBalance = data.new_closing_balance;
                        document.getElementById('closing_balance_' + ledgerId).innerHTML =
                            '<span class="fw-bold text-danger">Rs. ' + newClosingBalance + '</span>';

                        var recoveryModalInstance = bootstrap.Modal.getInstance(document.getElementById('recoveryModal'));
                        recoveryModalInstance.hide();

                        Swal.fire({
                            icon: 'success',
                            title: 'Payment Added!',
                            text: 'Customer payment recorded successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: data.message || 'Something went wrong!',
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred while processing your request.',
                    });
                });
        });

        // Payment History Modal
        $(document).on('click', '.quick-view-btn', function () {
            let customerId = $(this).data('customer-id');
            let customerName = $(this).data('customer-name');

            $('#history_customer_name').text(customerName);
            $('#history_body').html('<tr><td colspan="5" class="text-center"><div class="spinner-border spinner-border-sm"></div> Loading...</td></tr>');

            // Fetch payment history
            $.ajax({
                url: "{{ route('customer-payment-history') }}",
                type: "GET",
                data: { customer_id: customerId },
                success: function(response) {
                    let tbody = $('#history_body');
                    tbody.empty();

                    if (response.success && response.payments.length > 0) {
                        response.payments.forEach((payment, index) => {
                            tbody.append(`
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${payment.date}</td>
                                    <td class="fw-bold text-success">Rs. ${Number(payment.amount_paid).toLocaleString()}</td>
                                    <td>${payment.remarks || '-'}</td>
                                    <td><small class="text-muted">${payment.created_at}</small></td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="5" class="text-center text-muted">No payment history found</td></tr>');
                    }
                },
                error: function() {
                    $('#history_body').html('<tr><td colspan="5" class="text-center text-danger">Error loading payment history</td></tr>');
                }
            });
        });
    });
</script>
