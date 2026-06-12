@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <!-- Page Header -->
            <div class="page-header mb-4">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Weekly Staff Payment</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Weekly Staff Payment</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="container mt-4">

                <!-- Overall Summary Cards -->
                @if(isset($summary) && $summary->total_weeks > 0)
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card border bg-light text-dark">
                            <div class="card-body">
                                <h4 class="text-dark mb-3"><i class="fas fa-chart-line"></i> Overall Payment Summary</h4>
                                <div class="row text-center">
                                    <div class="col-md-2">
                                        <h5>{{ $summary->total_staff ?? 0 }}</h5>
                                        <small>Total Staff</small>
                                    </div>
                                    <div class="col-md-2">
                                        <h5>{{ $summary->total_weeks ?? 0 }}</h5>
                                        <small>Total Weeks</small>
                                    </div>
                                    <div class="col-md-2">
                                        <h5>Rs. {{ number_format($summary->total_weekly_amount ?? 0, 0) }}</h5>
                                        <small>Total Weekly Amount</small>
                                    </div>
                                    <div class="col-md-2">
                                        <h5>Rs. {{ number_format($summary->total_advances ?? 0, 0) }}</h5>
                                        <small>Total Advances</small>
                                    </div>
                                    <div class="col-md-2">
                                        <h5>Rs. {{ number_format($summary->total_paid ?? 0, 0) }}</h5>
                                        <small>Total Paid</small>
                                    </div>
                                    <div class="col-md-2">
                                        <h5 class="{{ ($summary->total_balance ?? 0) > 0 ? 'text-warning' : 'text-success' }}">
                                            Rs. {{ number_format($summary->total_balance ?? 0, 0) }}
                                        </h5>
                                        <small>Total Balance</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Help Guide -->
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <h5 class="alert-heading"><i class="fas fa-info-circle"></i> How to Pay Staff Weekly</h5>
                    <ol class="mb-0">
                        <li><strong>Select Staff</strong> - Choose the staff member from dropdown</li>
                        <li><strong>Week Auto-Selects</strong> - System automatically sets next week dates</li>
                        <li><strong>Attendance & Advances</strong> - System shows days present and advances taken automatically</li>
                        <li><strong>Enter Paid Amount</strong> - Enter how much you're paying this week</li>
                        <li><strong>Check Balance</strong> - Green = remaining to pay, Red = overpaid</li>
                        <li><strong>Save</strong> - Click "Save Weekly Payment Entry" button</li>
                        <li><strong>⚠️ Important:</strong> Cannot add payment twice for same week!</li>
                    </ol>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>

                <div class="card mb-3">
                    <div class="card-header fw-bold bg-light text-dark">
                        <i class="fas fa-users"></i> Staff Selection
                    </div>
                    <div class="card-body row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Select Staff</label>
                            <select class="form-select" id="staff_id">
                                <option value="">-- Select Staff --</option>
                                @if(isset($staffs) && count($staffs) > 0)
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}"
                                            data-joining="{{ $staff->created_at ? $staff->created_at->format('Y-m-d') : '' }}"
                                            data-weekly="{{ $staff->salary ?? 0 }}">
                                            {{ $staff->name }} {{ $staff->designation ? '('.$staff->designation.')' : '' }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="" disabled>No staff found - Please add staff first</option>
                                @endif
                            </select>
                            @if(isset($staffs))
                                <small class="text-muted">{{ count($staffs) }} staff member(s) available</small>
                            @endif
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Joining Date</label>
                            <input type="text" id="joining_date" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Weekly Amount</label>
                            <input type="text" id="weekly_amount" class="form-control" readonly>
                        </div>

                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-header fw-bold bg-light text-dark">Week Details & Payment</div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Week Start</label>
                                <input type="date" id="week_start" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Week End</label>
                                <input type="date" id="week_end" class="form-control" onchange="loadAttendance()">
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Days Present</label>
                                <input type="number" id="days_present" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Days Absent</label>
                                <input type="number" id="days_absent" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-success">Previous Balance</label>
                                <input type="number" id="previous_balance" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-primary">Weekly Amount</label>
                                <input type="number" id="weekly_amount_display" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-danger">Advance Taken</label>
                                <input type="number" id="advance_taken" class="form-control" readonly>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold text-warning">Paid Amount</label>
                                <input type="number" id="paid" class="form-control calc" placeholder="Enter paid amount">
                            </div>
                        </div>

                        <div class="row g-3 mt-3">
                            <div class="col-md-12">
                                <div class="alert alert-info mb-0">
                                    <strong>Calculation:</strong>
                                    <span id="calculation_text">Previous Balance + Weekly Amount - Advance - Paid = New Balance</span>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label class="form-label fw-bold" style="font-size: 1.2rem;">New Balance (Remaining)</label>
                                <input type="number" id="balance" class="form-control form-control-lg" readonly style="font-size: 1.3rem; font-weight: bold;">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Additional Note</label>
                                <input type="text" id="note" class="form-control" placeholder="Optional note">
                            </div>
                        </div>
                    </div>

                    <div class="card-footer text-end bg-light">
                        <button class="btn btn-success btn-lg" id="saveEntry">
                            <i class="fas fa-save"></i> Save Weekly Payment Entry
                        </button>
                    </div>
                </div>

                <!-- WEEKLY HISTORY -->
                <div class="card">
                    <div class="card-header fw-bold bg-light text-dark d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-history"></i> Payment History</span>
                        <button class="btn btn-outline-dark btn-sm" id="viewAllStaffSummary">
                            <i class="fas fa-list"></i> View All Staff Balances
                        </button>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="table-light">
                                    <tr class="text-center">
                                        <th>#</th>
                                        <th>Week Period</th>
                                        <th>Days</th>
                                        <th>Weekly Amt</th>
                                        <th>Advance</th>
                                        <th>Paid</th>
                                        <th>Balance</th>
                                        <th>Note</th>
                                    </tr>
                                </thead>
                                <tbody id="historyRows">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">Select staff to view payment history</td>
                                    </tr>
                                </tbody>
                                <tfoot class="table-light">
                                    <tr class="fw-bold">
                                        <td colspan="3" class="text-end">TOTAL:</td>
                                        <td id="total_weekly" class="text-end">0</td>
                                        <td id="total_advance" class="text-end">0</td>
                                        <td id="total_paid" class="text-end">0</td>
                                        <td id="total_balance" class="text-end">0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<!-- All Staff Balances Modal -->
<div class="modal fade" id="allStaffModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-light text-dark">
                <h5 class="modal-title"><i class="fas fa-users"></i> All Staff Payment Summary</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr class="text-center">
                                <th>#</th>
                                <th>Staff Name</th>
                                <th>Designation</th>
                                <th>Total Weeks</th>
                                <th>Total Weekly Amount</th>
                                <th>Total Advances</th>
                                <th>Total Paid</th>
                                <th>Current Balance</th>
                            </tr>
                        </thead>
                        <tbody id="allStaffSummaryRows">
                            <tr>
                                <td colspan="8" class="text-center">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    const csrf = "{{ csrf_token() }}";
    let lastWeekEnd = null;
    let previousBalance = 0;
    let currentStaffId = null;

    document.getElementById('staff_id').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];
        currentStaffId = this.value;

        $('#joining_date').val(opt.dataset.joining || '');
        $('#weekly_amount').val(opt.dataset.weekly || '');

        // Reset fields
        $('#paid').val('');
        $('#advance_taken').val(0);
        $('#days_present').val(0);
        $('#days_absent').val(0);
        $('#note').val('');

        loadHistory(this.value);
    });

    $('.calc').on('input', calculateBalance);

    function calculateBalance() {
        const weekly = Number($('#weekly_amount').val() || 0);
        const paid = Number($('#paid').val() || 0);
        const advanceTaken = Number($('#advance_taken').val() || 0);

        // Previous Balance + Weekly Amount - Advance - Paid
        const balance = previousBalance + weekly - advanceTaken - paid;

        $('#balance').val(balance.toFixed(2));
        $('#weekly_amount_display').val(weekly.toFixed(2));
        $('#previous_balance').val(previousBalance.toFixed(2));

        // Update calculation text
        $('#calculation_text').html(`
            ${previousBalance.toFixed(2)} + ${weekly.toFixed(2)} - ${advanceTaken.toFixed(2)} - ${paid.toFixed(2)} =
            <strong>${balance.toFixed(2)}</strong>
        `);

        // Color coding
        if (balance > 0) {
            $('#balance').css('color', 'green');
        } else if (balance < 0) {
            $('#balance').css('color', 'red');
        } else {
            $('#balance').css('color', 'black');
        }
    }

    // Load attendance for selected week
    function loadAttendance() {
        const staffId = $('#staff_id').val();
        const weekStart = $('#week_start').val();
        const weekEnd = $('#week_end').val();

        if (!staffId || !weekStart || !weekEnd) return;

        $.post("{{ route('staff.weekly.attendance') }}", {
            _token: csrf,
            staff_id: staffId,
            week_start: weekStart,
            week_end: weekEnd
        }, function (res) {
            $('#days_present').val(res.present);
            $('#days_absent').val(res.absent);
            $('#advance_taken').val(res.advance || 0);

            calculateBalance();
        });
    }

    // Load payment history
    function loadHistory(staffId) {
        if (!staffId) return;

        $.post("{{ route('staff.weekly.history') }}", {
            _token: csrf,
            staff_id: staffId
        }, function (res) {

            let rows = '';
            let validEntries = res.filter(r => r.week_start && r.week_end);

            let totalWeekly = 0, totalAdvance = 0, totalPaid = 0, totalBalance = 0;

            if (validEntries.length === 0) {
                rows = `<tr><td colspan="8" class="text-center text-muted">No payment record found</td></tr>`;
                lastWeekEnd = $('#joining_date').val();
                previousBalance = 0;
            } else {
                lastWeekEnd = validEntries[0].week_end;
                previousBalance = Number(validEntries[0].balance || 0);

                validEntries.forEach((r, index) => {
                    let balanceColor = '';

                    if (r.balance > 0) {
                        balanceColor = 'text-success fw-bold';
                    } else if (r.balance < 0) {
                        balanceColor = 'text-danger fw-bold';
                    }

                    // Totals
                    totalWeekly += Number(r.weekly_amount || 0);
                    totalAdvance += Number(r.advance || 0);
                    totalPaid += Number(r.paid || 0);
                    totalBalance = r.balance; // Latest balance

                    rows += `
                    <tr>
                        <td class="text-center">${validEntries.length - index}</td>
                        <td class="text-center">${formatDate(r.week_start)} → ${formatDate(r.week_end)}</td>
                        <td class="text-center">${r.days_present || '-'} / ${r.days_absent || '-'}</td>
                        <td class="text-end">${Number(r.weekly_amount || 0).toFixed(2)}</td>
                        <td class="text-end">${Number(r.advance || 0).toFixed(2)}</td>
                        <td class="text-end">${Number(r.paid || 0).toFixed(2)}</td>
                        <td class="text-end ${balanceColor}">${Number(r.balance || 0).toFixed(2)}</td>
                        <td>${r.note || '-'}</td>
                    </tr>`;
                });
            }

            $('#historyRows').html(rows);

            // Update totals
            $('#total_weekly').text(totalWeekly.toFixed(2));
            $('#total_advance').text(totalAdvance.toFixed(2));
            $('#total_paid').text(totalPaid.toFixed(2));
            $('#total_balance').text(totalBalance.toFixed(2));

            setMinimumWeekStart();
            calculateBalance();
        });
    }

    // Format date to readable format
    function formatDate(dateStr) {
        if (!dateStr) return '';
        const date = new Date(dateStr);
        return date.toLocaleDateString('en-GB', { day: '2-digit', month: 'short' });
    }

    // Set minimum week start date
    function setMinimumWeekStart() {
        if (lastWeekEnd) {
            let nextDate = new Date(lastWeekEnd);
            nextDate.setDate(nextDate.getDate() + 1);

            let minDate = nextDate.toISOString().split('T')[0];
            $('#week_start').attr('min', minDate);
            $('#week_start').val(minDate);

            let weekEndDate = new Date(nextDate);
            weekEndDate.setDate(weekEndDate.getDate() + 6);
            $('#week_end').val(weekEndDate.toISOString().split('T')[0]);

            // Load attendance for default week
            loadAttendance();
        }
    }

    // Save weekly payment entry
    $('#saveEntry').click(function () {
        const staffId = $('#staff_id').val();
        const weekStart = $('#week_start').val();
        const weekEnd = $('#week_end').val();

        if (!staffId) {
            alert('⚠️ Please select staff');
            return;
        }

        if (!weekStart || !weekEnd) {
            alert('⚠️ Please select week dates');
            return;
        }

        // Disable button to prevent double click
        const $btn = $(this);
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

        $.post("{{ route('staff.weekly.save') }}", {
            _token: csrf,
            staff_id: staffId,
            week_start: weekStart,
            week_end: weekEnd,
            weekly_amount: $('#weekly_amount').val(),
            days_present: $('#days_present').val() || 0,
            days_absent: $('#days_absent').val() || 0,
            advance: $('#advance_taken').val() || 0,
            paid: $('#paid').val() || 0,
            balance: $('#balance').val(),
            note: $('#note').val()
        }, function (response) {
            // Reset input fields
            $('#paid').val('');
            $('#note').val('');

            // Reload history
            loadHistory(staffId);

            // Re-enable button
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Weekly Payment Entry');

            alert('✓ ' + (response.message || 'Weekly payment entry saved successfully!'));
        }).fail(function(xhr) {
            // Re-enable button
            $btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Weekly Payment Entry');

            // Show error message
            let errorMsg = 'Error saving entry. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            alert('❌ ' + errorMsg);
        });
    });

    // View all staff balances summary
    $('#viewAllStaffSummary').click(function() {
        // Show modal
        $('#allStaffModal').modal('show');

        // Load data
        $('#allStaffSummaryRows').html('<tr><td colspan=\"8\" class=\"text-center\"><i class=\"fas fa-spinner fa-spin\"></i> Loading...</td></tr>');

        $.get("{{ url('/staff-all-summary') }}", {
            _token: csrf
        }, function(data) {
            let rows = '';

            if (data.length === 0) {
                rows = '<tr><td colspan=\"8\" class=\"text-center text-muted\">No payment records found</td></tr>';
            } else {
                data.forEach((staff, index) => {
                    let balanceClass = '';
                    if (staff.current_balance > 0) {
                        balanceClass = 'text-danger fw-bold';
                    } else if (staff.current_balance < 0) {
                        balanceClass = 'text-success fw-bold';
                    }

                    rows += `<tr>
                        <td class="text-center">${index + 1}</td>
                        <td>${staff.staff_name || 'N/A'}</td>
                        <td class="text-center">${staff.designation || '-'}</td>
                        <td class="text-center">${staff.total_weeks || 0}</td>
                        <td class="text-end">Rs. ${Number(staff.total_weekly || 0).toLocaleString()}</td>
                        <td class="text-end">Rs. ${Number(staff.total_advance || 0).toLocaleString()}</td>
                        <td class="text-end">Rs. ${Number(staff.total_paid || 0).toLocaleString()}</td>
                        <td class="text-end ${balanceClass}">Rs. ${Number(staff.current_balance || 0).toLocaleString()}</td>
                    </tr>`;
                });
            }

            $('#allStaffSummaryRows').html(rows);
        }).fail(function() {
            $('#allStaffSummaryRows').html('<tr><td colspan=\"8\" class=\"text-center text-danger\">Error loading data</td></tr>');
        });
    });

</script>
