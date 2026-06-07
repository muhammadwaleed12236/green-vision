@include('admin_panel.include.header_include')

<style>
    .advance-card { border-radius: 10px; border: 1px solid #e5e7eb; }
    .advance-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .type-salary { background: #fef3c7; color: #92400e; }
    .type-additional { background: #dbeafe; color: #1e40af; }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Header --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Staff Advance Management</h4>
                    <p class="text-muted mb-0">Manage salary advances and additional loans</p>
                </div>
                <div>
                    <a href="{{ route('staff-attendance.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fa fa-calendar-check"></i> Attendance
                    </a>
                    <a href="{{ route('staff-ledger-view') }}" class="btn btn-outline-success">
                        <i class="fa fa-book"></i> Staff Ledger
                    </a>
                </div>
            </div>

            <div class="row">
                {{-- Add Advance Form --}}
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Record Advance</h5>
                        </div>
                        <div class="card-body">
                            <form id="advanceForm">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Select Staff <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staffSelect" class="form-select" required>
                                        <option value="">-- Select Staff --</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->designation }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Advance Type <span class="text-danger">*</span></label>
                                    <select name="advance_type" class="form-select" required>
                                        <option value="salary">Salary Advance (Will deduct from salary)</option>
                                        <option value="additional">Additional Loan (Separate ledger)</option>
                                    </select>
                                    <small class="text-muted">Salary advance = staff ki salary se minus hoga</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Amount (PKR) <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" class="form-control" placeholder="Enter amount" required min="1">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <textarea name="remarks" class="form-control" rows="2" placeholder="Optional notes"></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fa fa-save me-1"></i> Save Advance
                                </button>
                            </form>
                        </div>
                    </div>

                    {{-- Staff Balance Summary --}}
                    <div class="card mt-3" id="balanceCard" style="display: none;">
                        <div class="card-body">
                            <h6 class="mb-3">Staff Balance Summary</h6>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Salary Advance:</span>
                                <strong class="text-warning" id="salaryAdvance">PKR 0</strong>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Additional Loan:</span>
                                <strong class="text-primary" id="additionalLoan">PKR 0</strong>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between">
                                <span><strong>Total Pending:</strong></span>
                                <strong class="text-danger" id="totalPending">PKR 0</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Advance List --}}
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Advance Records</h5>
                            <div class="d-flex gap-2">
                                <select id="filterStaff" class="form-select" style="width: 180px;">
                                    <option value="">All Staff</option>
                                    @foreach($staffs as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                                <select id="filterType" class="form-select" style="width: 150px;">
                                    <option value="">All Types</option>
                                    <option value="salary">Salary Advance</option>
                                    <option value="additional">Additional Loan</option>
                                </select>
                            </div>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover" id="advanceTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Staff</th>
                                        <th>Type</th>
                                        <th>Amount</th>
                                        <th>Date</th>
                                        <th>Remarks</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($advances as $k => $adv)
                                        <tr>
                                            <td>{{ $advances->firstItem() + $k }}</td>
                                            <td>
                                                <strong>{{ $adv->staff->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $adv->staff->designation ?? '' }}</small>
                                            </td>
                                            <td>
                                                <span class="badge {{ $adv->advance_type == 'salary' ? 'type-salary' : 'type-additional' }}">
                                                    {{ $adv->advance_type == 'salary' ? 'Salary Advance' : 'Additional Loan' }}
                                                </span>
                                            </td>
                                            <td><strong>PKR {{ number_format($adv->amount, 0) }}</strong></td>
                                            <td>{{ \Carbon\Carbon::parse($adv->date)->format('d M Y') }}</td>
                                            <td>{{ $adv->remarks ?? '-' }}</td>
                                            <td>
                                                @if($adv->status == 'pending')
                                                    <span class="badge bg-warning">Pending</span>
                                                @elseif($adv->status == 'partially_paid')
                                                    <span class="badge bg-info">Partial</span>
                                                @else
                                                    <span class="badge bg-success">Cleared</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($adv->status != 'cleared')
                                                    <button class="btn btn-sm btn-outline-success recover-btn"
                                                            data-id="{{ $adv->id }}"
                                                            data-amount="{{ $adv->remaining_amount ?? $adv->amount }}">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                @endif
                                                <button class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $adv->id }}">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No advance records found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @if($advances->hasPages())
                                <div class="mt-3">{{ $advances->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- Recovery Modal --}}
<div class="modal fade" id="recoverModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="recoverForm">
                @csrf
                <input type="hidden" name="advance_id" id="recoverAdvanceId">
                <div class="modal-header">
                    <h5 class="modal-title">Recover Advance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Remaining Amount</label>
                        <input type="text" id="remainingAmount" class="form-control" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Recovery Amount <span class="text-danger">*</span></label>
                        <input type="number" name="recovery_amount" id="recoveryAmount" class="form-control" required min="1">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Recover</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
$(document).ready(function() {
    // Staff Select - Show Balance
    $('#staffSelect').on('change', function() {
        let staffId = $(this).val();
        if(staffId) {
            $.get("{{ url('staff-advance/balance') }}/" + staffId, function(data) {
                $('#salaryAdvance').text('PKR ' + (data.salary_advance || 0));
                $('#additionalLoan').text('PKR ' + (data.additional_loan || 0));
                $('#totalPending').text('PKR ' + (data.total || 0));
                $('#balanceCard').show();
            });
        } else {
            $('#balanceCard').hide();
        }
    });

    // Save Advance
    $('#advanceForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('staff-advance.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to save advance', 'error');
            }
        });
    });

    // Recover Button
    $('.recover-btn').on('click', function() {
        let id = $(this).data('id');
        let amount = $(this).data('amount');
        $('#recoverAdvanceId').val(id);
        $('#remainingAmount').val('PKR ' + amount);
        $('#recoveryAmount').attr('max', amount).val(amount);
        $('#recoverModal').modal('show');
    });

    // Submit Recovery
    $('#recoverForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('staff-advance.recover') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                }
            }
        });
    });

    // Delete Advance
    $('.delete-btn').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete this advance?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url: "{{ url('staff-advance/delete') }}/" + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function(res) {
                        Swal.fire('Deleted!', res.message, 'success').then(() => location.reload());
                    }
                });
            }
        });
    });

    // Filter
    $('#filterStaff, #filterType').on('change', function() {
        let staff = $('#filterStaff').val();
        let type = $('#filterType').val();
        let url = "{{ route('staff-advance.index') }}?";
        if(staff) url += 'staff_id=' + staff + '&';
        if(type) url += 'type=' + type;
        window.location.href = url;
    });
});
</script>
