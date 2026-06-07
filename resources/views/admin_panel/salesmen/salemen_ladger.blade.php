@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Staff Ledger</h4>
                    <h6>Manage Staff Payments</h6>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success">
                    <strong>Success!</strong> {{ session('success') }}.
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Staff Name</th>
                                    <th>Opening Balance</th>
                                    <th>Previous Balance</th>
                                    <th>Closing Balance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($StaffLedgers as $key => $ledger)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $ledger->staff_name ?? 'N/A' }}</td>
                                        <td>0</td>
                                        <td>{{ number_format($ledger->previous_balance ?? 0, 0) }}</td>
                                        <td>{{ number_format($ledger->closing_balance ?? 0, 0) }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary paymentBtn" data-id="{{ $ledger->id }}"
                                                data-name="{{ $ledger->staff_name ?? 'N/A' }}"
                                                data-balance="{{ $ledger->closing_balance ?? 0 }}" data-bs-toggle="modal"
                                                data-bs-target="#paymentModal">
                                                Add Payment
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

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Staff Payment</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
            </div>
            <form id="paymentForm">
                @csrf
                <input type="hidden" id="ledger_id" name="ledger_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Staff Name</label>
                        <input type="text" class="form-control" id="staff_name" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Current Balance</label>
                        <input type="text" class="form-control" id="current_balance" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount Paid</label>
                        <input type="number" class="form-control" name="amount_paid" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).on("click", ".paymentBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");
        let balance = $(this).data("balance");

        $("#ledger_id").val(id);
        $("#staff_name").val(name);
        $("#current_balance").val(balance);
    });

    $("#paymentForm").on("submit", function (e) {
        e.preventDefault();

        $.ajax({
            url: "{{ route('staff-recovery-store') }}",
            type: "POST",
            data: $(this).serialize(),
            success: function (response) {
                if (response.success) {
                    alert("Payment saved successfully!");
                    location.reload();
                }
            },
            error: function () {
                alert("Error saving payment");
            }
        });
    });
</script>
