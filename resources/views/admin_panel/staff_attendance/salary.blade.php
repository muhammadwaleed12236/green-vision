@include('admin_panel.include.header_include')

<style>
    .info-card { border-radius: 12px; border: 1px solid #e5e7eb; background: #fff; }
    .stat-mini { padding: 12px 15px; border-radius: 8px; text-align: center; }
    .stat-mini h5 { margin: 0; font-weight: 700; }
    .stat-mini small { color: #6b7280; font-size: 11px; }
    .alert-overlap { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
    .deduction-row { background: #fefce8; border-radius: 8px; padding: 12px; margin-bottom: 10px; }
    .badge-period { background: #e0f2fe; color: #0369a1; padding: 4px 10px; border-radius: 15px; font-size: 11px; }
    .attendance-grid { display: flex; flex-wrap: wrap; gap: 5px; }
    .att-day {
        width: 48px;
        padding: 5px;
        text-align: center;
        border-radius: 6px;
        font-size: 11px;
        border: 1px solid #e5e7eb;
    }
    .att-day.present { background: #dcfce7; border-color: #86efac; }
    .att-day.absent { background: #fee2e2; border-color: #fca5a5; }
    .att-day.half_day { background: #fef3c7; border-color: #fcd34d; }
    .att-day.leave { background: #dbeafe; border-color: #93c5fd; }
    .pay-option {
        padding: 12px;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pay-option:hover { border-color: #3b82f6; }
    .pay-option.selected { border-color: #22c55e; background: #f0fdf4; }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Header --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Staff Salary Payment</h4>
                    <p class="text-muted mb-0">Pay salary with advance deduction (Weekly / Monthly)</p>
                </div>
                <div>
                    <a href="{{ route('staff-attendance.index') }}" class="btn btn-outline-primary btn-sm me-1">
                        <i class="fa fa-calendar-check"></i> Attendance
                    </a>
                    <a href="{{ route('staff-advance.index') }}" class="btn btn-outline-success btn-sm">
                        <i class="fa fa-money-bill"></i> Advances
                    </a>
                </div>
            </div>

            <div class="row">
                {{-- Payment Form --}}
                <div class="col-md-5">
                    <div class="card info-card">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0"><i class="fa fa-wallet text-success me-2"></i>Pay Salary</h5>
                        </div>
                        <div class="card-body">
                            <form id="salaryForm">
                                @csrf

                                {{-- Staff Selection --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Select Staff <span class="text-danger">*</span></label>
                                    <select name="staff_id" id="staffSelect" class="form-select" required>
                                        <option value="">-- Select Staff --</option>
                                        @foreach($staffs as $staff)
                                            <option value="{{ $staff->id }}"
                                                    data-salary="{{ $staff->salary ?? 0 }}"
                                                    data-name="{{ $staff->name }}">
                                                {{ $staff->name }} - {{ $staff->designation }} (PKR {{ number_format($staff->salary ?? 0, 0) }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Date Range --}}
                                <div class="row mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">From Date <span class="text-danger">*</span></label>
                                        <input type="date" name="from_date" id="fromDate" class="form-control" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold">To Date <span class="text-danger">*</span></label>
                                        <input type="date" name="to_date" id="toDate" class="form-control" value="{{ date('Y-m-d') }}" required>
                                    </div>
                                </div>

                                {{-- Last Payment Info --}}
                                <div class="mb-3 p-2 rounded" style="background: #f0fdf4; display: none;" id="lastPaymentInfo">
                                    <small class="text-success">
                                        <i class="fa fa-check-circle me-1"></i>
                                        <strong>Last Paid Till:</strong> <span id="lastPaidDate">-</span>
                                    </small>
                                </div>

                                {{-- Overlap Warning --}}
                                <div class="alert alert-overlap mb-3" id="overlapAlert" style="display: none;">
                                    <i class="fa fa-exclamation-triangle me-2"></i>
                                    <span id="overlapMessage"></span>
                                </div>

                                {{-- Salary Summary --}}
                                <div id="summaryBox" style="display: none;">

                                    {{-- Attendance History Grid --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold mb-2">Attendance History</label>
                                        <div class="attendance-grid" id="attendanceGrid">
                                            {{-- Filled via AJAX --}}
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-3">
                                            <div class="stat-mini" style="background: #ecfdf5;">
                                                <h5 class="text-success" id="dispPresent">0</h5>
                                                <small>Present</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="stat-mini" style="background: #fef2f2;">
                                                <h5 class="text-danger" id="dispAbsent">0</h5>
                                                <small>Absent</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="stat-mini" style="background: #fef3c7;">
                                                <h5 class="text-warning" id="dispHalfDay">0</h5>
                                                <small>Half Day</small>
                                            </div>
                                        </div>
                                        <div class="col-3">
                                            <div class="stat-mini" style="background: #eff6ff;">
                                                <h5 class="text-primary" id="dispTotal">0</h5>
                                                <small>Total</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Payment Options --}}
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold mb-2">Payment Option</label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="pay-option selected" id="optionDeducted" onclick="selectPayOption('deducted')">
                                                    <div class="d-flex align-items-center">
                                                        <input type="radio" name="pay_option" value="deducted" checked class="me-2">
                                                        <div>
                                                            <strong>After Deduction</strong><br>
                                                            <small class="text-muted">Absent days deducted</small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 text-success fw-bold" id="deductedAmount">PKR 0</div>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="pay-option" id="optionFull" onclick="selectPayOption('full')">
                                                    <div class="d-flex align-items-center">
                                                        <input type="radio" name="pay_option" value="full" class="me-2">
                                                        <div>
                                                            <strong>Full Payment</strong><br>
                                                            <small class="text-muted">No deduction</small>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 text-primary fw-bold" id="fullAmount">PKR 0</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Calculation Box --}}
                                    <div class="p-3 mb-3 rounded" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Set Salary:</span>
                                            <strong id="setSalary">PKR 0</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Selected Period:</span>
                                            <span><span id="totalDays">0</span> days</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Per Day Rate:</span>
                                            <span id="perDaySalary">PKR 0</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between mb-2 text-danger" id="absentRow">
                                            <span>Absent Deduction (<span id="absentDays">0</span> days):</span>
                                            <span id="absentDeduction">- PKR 0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2 text-success">
                                            <span>Salary After Absent:</span>
                                            <strong id="grossSalary">PKR 0</strong>
                                        </div>
                                        <hr class="my-2">

                                        {{-- Advances Deductions --}}
                                        <div class="deduction-row">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <input type="checkbox" name="deduct_salary_advance" id="deductSalary" value="1" checked>
                                                    <label for="deductSalary" class="mb-0 ms-1">Salary Advance (Full Deduct)</label>
                                                </div>
                                                <strong class="text-danger" id="salaryAdvance">- PKR 0</strong>
                                            </div>
                                        </div>

                                        <div class="deduction-row">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <input type="checkbox" name="deduct_additional_advance" id="deductAdditional" value="1">
                                                    <label for="deductAdditional" class="mb-0 ms-1">Additional Loan</label>
                                                </div>
                                                <span class="text-muted" id="additionalAdvanceTotal">Balance: PKR 0</span>
                                            </div>
                                            <div id="additionalAmountBox" style="display: none;">
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text">Deduct Amount</span>
                                                    <input type="number" name="additional_deduct_amount" id="additionalDeductAmount"
                                                           class="form-control" placeholder="Enter amount" min="0">
                                                    <button type="button" class="btn btn-outline-secondary" onclick="setFullAdditional()">Full</button>
                                                </div>
                                                <small class="text-muted">Enter how much to deduct from additional loan</small>
                                            </div>
                                        </div>

                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between">
                                            <strong>Net Payable:</strong>
                                            <strong class="text-success fs-5" id="netPayable">PKR 0</strong>
                                        </div>
                                    </div>
                                </div>

                                {{-- Payment Date --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                                    <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>

                                {{-- Amount --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Amount to Pay <span class="text-danger">*</span></label>
                                    <input type="number" name="amount" id="payAmount" class="form-control" placeholder="Enter amount" required min="0">
                                </div>

                                {{-- Remarks --}}
                                <div class="mb-3">
                                    <label class="form-label">Remarks</label>
                                    <input type="text" name="remarks" class="form-control" placeholder="Optional notes">
                                </div>

                                <button type="submit" class="btn btn-success w-100" id="submitBtn">
                                    <i class="fa fa-check me-1"></i> Pay Salary
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Payment History --}}
                <div class="col-md-7">
                    <div class="card info-card">
                        <div class="card-header bg-white border-0 pb-0">
                            <h5 class="mb-0"><i class="fa fa-history text-primary me-2"></i>Payment History</h5>
                        </div>
                        <div class="card-body table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Staff</th>
                                        <th>Period</th>
                                        <th>Amount</th>
                                        <th>Deductions</th>
                                        <th>Date</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($payments as $k => $payment)
                                        <tr>
                                            <td>{{ $payments->firstItem() + $k }}</td>
                                            <td>
                                                <strong>{{ $payment->staff->name ?? 'N/A' }}</strong><br>
                                                <small class="text-muted">{{ $payment->staff->designation ?? '' }}</small>
                                            </td>
                                            <td>
                                                @if($payment->from_date && $payment->to_date)
                                                    <span class="badge-period">
                                                        {{ \Carbon\Carbon::parse($payment->from_date)->format('d M') }} -
                                                        {{ \Carbon\Carbon::parse($payment->to_date)->format('d M') }}
                                                    </span>
                                                @else
                                                    {{ \Carbon\Carbon::parse($payment->payment_month . '-01')->format('M Y') }}
                                                @endif
                                            </td>
                                            <td><strong class="text-success">{{ number_format($payment->amount_paid, 0) }}</strong></td>
                                            <td>
                                                @php
                                                    $totalDed = ($payment->advance_deducted ?? 0) + ($payment->additional_advance_deducted ?? 0);
                                                @endphp
                                                @if($totalDed > 0)
                                                    <span class="text-danger">-{{ number_format($totalDed, 0) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M') }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" onclick="editPayment({{ $payment->id }})" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </button>
                                                <a href="{{ route('staff-salary.receipt', $payment->id) }}"
                                                   class="btn btn-sm btn-outline-secondary me-1" target="_blank" title="Print">
                                                    <i class="fa fa-print"></i>
                                                </a>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deletePayment({{ $payment->id }})" title="Delete">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">
                                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                                No salary payments yet
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            @if($payments->hasPages())
                                <div class="mt-3">{{ $payments->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Edit Salary Payment</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editPaymentForm">
                    @csrf
                    <input type="hidden" id="editPaymentId">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Staff Name</label>
                        <input type="text" class="form-control" id="editStaffName" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Period</label>
                        <input type="text" class="form-control" id="editPeriod" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount Paid <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="editAmount" name="amount" required min="0">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="editPaymentDate" name="payment_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" class="form-control" id="editRemarks" name="remarks" placeholder="Optional notes">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updatePayment()">
                    <i class="fa fa-save me-1"></i> Update
                </button>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
let staffData = {};
let payOption = 'deducted'; // 'deducted' or 'full'
let editPaymentModal;

document.addEventListener('DOMContentLoaded', function() {
    editPaymentModal = new bootstrap.Modal(document.getElementById('editPaymentModal'));
});

// Edit Payment
function editPayment(id) {
    $.get('/staff-salary/' + id, function(res) {
        if(res.success) {
            let data = res.data;
            $('#editPaymentId').val(data.id);
            $('#editStaffName').val(data.staff ? data.staff.name : 'N/A');
            $('#editPeriod').val(formatDate(data.from_date) + ' - ' + formatDate(data.to_date));
            $('#editAmount').val(data.amount_paid);
            $('#editPaymentDate').val(data.payment_date);
            $('#editRemarks').val(data.remarks || '');
            editPaymentModal.show();
        }
    });
}

// Update Payment
function updatePayment() {
    let id = $('#editPaymentId').val();
    $.ajax({
        url: '/staff-salary/' + id,
        method: 'PUT',
        data: {
            _token: '{{ csrf_token() }}',
            amount: $('#editAmount').val(),
            payment_date: $('#editPaymentDate').val(),
            remarks: $('#editRemarks').val()
        },
        success: function(res) {
            if(res.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: res.message,
                    confirmButtonColor: '#22c55e'
                }).then(() => location.reload());
            }
        },
        error: function(xhr) {
            Swal.fire('Error', xhr.responseJSON?.message || 'Failed to update', 'error');
        }
    });
}

// Delete Payment
function deletePayment(id) {
    Swal.fire({
        title: 'Delete Payment?',
        text: 'This will restore any deducted advances. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        confirmButtonText: 'Yes, Delete'
    }).then((result) => {
        if(result.isConfirmed) {
            $.ajax({
                url: '/staff-salary/' + id,
                method: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    if(res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => location.reload());
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error', xhr.responseJSON?.message || 'Failed to delete', 'error');
                }
            });
        }
    });
}

// Format date helper
function formatDate(dateStr) {
    if(!dateStr) return '-';
    let d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
}

function selectPayOption(option) {
    payOption = option;
    $('.pay-option').removeClass('selected');
    $('#option' + (option === 'full' ? 'Full' : 'Deducted')).addClass('selected');
    $('input[name="pay_option"][value="' + option + '"]').prop('checked', true);

    // Show/hide absent row
    if(option === 'full') {
        $('#absentRow').hide();
    } else {
        $('#absentRow').show();
    }

    recalculateNet();
}

$(document).ready(function() {
    // Calculate when staff or dates change
    $('#staffSelect, #fromDate, #toDate').on('change', fetchSalaryInfo);

    // Recalculate net when checkboxes change
    $('#deductSalary, #deductAdditional').on('change', recalculateNet);

    function fetchSalaryInfo() {
        let staffId = $('#staffSelect').val();
        let fromDate = $('#fromDate').val();
        let toDate = $('#toDate').val();

        if(!staffId || !fromDate || !toDate) {
            $('#summaryBox').hide();
            $('#lastPaymentInfo').hide();
            return;
        }

        $.get("{{ url('staff-salary/info') }}/" + staffId + "?from_date=" + fromDate + "&to_date=" + toDate, function(data) {
            staffData = data;

            // Show last payment info
            if(data.last_paid_date) {
                $('#lastPaidDate').text(data.last_paid_formatted);
                $('#lastPaymentInfo').show();
            } else {
                $('#lastPaymentInfo').hide();
            }

            // Check for overlap
            if(data.overlap && data.overlap.has_overlap) {
                $('#overlapMessage').text(data.overlap.message);
                $('#overlapAlert').show();
                $('#submitBtn').prop('disabled', true);
            } else {
                $('#overlapAlert').hide();
                $('#submitBtn').prop('disabled', false);
            }

            // Build attendance history grid
            let gridHtml = '';
            if(data.attendance_history && data.attendance_history.length > 0) {
                data.attendance_history.forEach(function(att) {
                    gridHtml += '<div class="att-day ' + att.status + '">';
                    gridHtml += '<div><strong>' + att.date + '</strong></div>';
                    gridHtml += '<small>' + att.day + '</small>';
                    gridHtml += '</div>';
                });
            } else {
                gridHtml = '<div class="text-muted">No attendance records for this period</div>';
            }
            $('#attendanceGrid').html(gridHtml);

            // Update stats
            $('#dispPresent').text(data.days_present);
            $('#dispAbsent').text(data.days_absent);
            $('#dispHalfDay').text(data.days_half_day);
            $('#dispTotal').text(data.total_days);

            // Update calculations
            // Update calculations
            $('#setSalary').text('PKR ' + data.set_salary.toLocaleString());
            $('#perDaySalary').text('PKR ' + data.per_day_salary.toLocaleString());
            $('#totalDays').text(data.total_days);
            $('#absentDays').text(data.days_absent);
            $('#absentDeduction').text('- PKR ' + data.absent_deduction.toLocaleString());
            $('#grossSalary').text('PKR ' + data.gross_salary.toLocaleString());

            // Update payment options
            $('#deductedAmount').text('PKR ' + data.gross_salary.toLocaleString());
            $('#fullAmount').text('PKR ' + data.full_period_salary.toLocaleString());

            // Update advances
            $('#salaryAdvance').text('- PKR ' + (data.salary_advance || 0).toLocaleString());
            $('#additionalAdvanceTotal').text('Balance: PKR ' + (data.additional_advance || 0).toLocaleString());
            $('#additionalDeductAmount').attr('max', data.additional_advance || 0);

            // If no salary advance, uncheck and disable
            if(data.salary_advance <= 0) {
                $('#deductSalary').prop('checked', false).prop('disabled', true);
            } else {
                $('#deductSalary').prop('disabled', false).prop('checked', true);
            }

            if(data.additional_advance <= 0) {
                $('#deductAdditional').prop('checked', false).prop('disabled', true);
                $('#additionalAmountBox').hide();
            } else {
                $('#deductAdditional').prop('disabled', false);
            }

            // Reset to deducted option if there are absents
            if(data.days_absent > 0) {
                selectPayOption('deducted');
            } else {
                selectPayOption('full');
            }

            recalculateNet();
            $('#summaryBox').show();
        }).fail(function(xhr) {
            console.error('Error fetching info', xhr);
        });
    }

    // Show/hide additional amount input when checkbox changes
    $('#deductAdditional').on('change', function() {
        if($(this).is(':checked')) {
            $('#additionalAmountBox').show();
            $('#additionalDeductAmount').val('').focus();
        } else {
            $('#additionalAmountBox').hide();
            $('#additionalDeductAmount').val('');
        }
        recalculateNet();
    });

    // Set full additional amount
    function setFullAdditional() {
        $('#additionalDeductAmount').val(staffData.additional_advance || 0);
        recalculateNet();
    }
    window.setFullAdditional = setFullAdditional;

    // Recalculate when additional amount changes
    $('#additionalDeductAmount').on('input', function() {
        let max = staffData.additional_advance || 0;
        let val = parseFloat($(this).val()) || 0;
        if(val > max) {
            $(this).val(max);
        }
        recalculateNet();
    });

    function recalculateNet() {
        // Base salary based on option
        let baseSalary = (payOption === 'full') ?
            (staffData.full_period_salary || 0) :
            (staffData.gross_salary || 0);

        let salaryDed = $('#deductSalary').is(':checked') ? (staffData.salary_advance || 0) : 0;

        // Additional loan - use entered amount instead of full
        let additionalDed = 0;
        if($('#deductAdditional').is(':checked')) {
            additionalDed = parseFloat($('#additionalDeductAmount').val()) || 0;
        }

        let net = baseSalary - salaryDed - additionalDed;
        net = Math.max(0, net);

        $('#netPayable').text('PKR ' + net.toLocaleString());
        $('#payAmount').val(net);
    }

    // Make recalculateNet globally accessible
    window.recalculateNet = recalculateNet;

    // Submit form
    $('#salaryForm').on('submit', function(e) {
        e.preventDefault();

        if($('#submitBtn').prop('disabled')) {
            return;
        }

        $.ajax({
            url: "{{ route('staff-salary.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: res.message,
                        confirmButtonColor: '#22c55e'
                    }).then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                let msg = xhr.responseJSON?.message || 'Failed to process payment';
                Swal.fire('Error', msg, 'error');
            }
        });
    });
});
</script>
