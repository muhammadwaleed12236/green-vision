@include('admin_panel.include.header_include')

<style>
    .summary-card {
        border-left: 5px solid;
        padding: 20px;
        border-radius: 10px;
        background: linear-gradient(135deg, #fff 0%, #f8f9fa 100%);
        transition: all 0.3s ease;
    }

    .summary-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
    }

    .summary-present {
        border-color: #198754
    }

    .summary-absent {
        border-color: #dc3545
    }

    .summary-leave {
        border-color: #ffc107
    }

    .summary-half {
        border-color: #0dcaf0
    }

    .summary-card h4 {
        margin: 0;
        font-weight: 700;
        font-size: 32px;
    }

    .summary-card h6 {
        color: #6c757d;
        margin-bottom: 5px;
    }

    .mode-selector {
        background: #fff;
        padding: 5px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    }

    .mode-btn {
        border: none;
        padding: 10px 24px;
        border-radius: 6px;
        transition: all 0.3s ease;
        background: transparent;
        color: #6c757d;
    }

    .mode-btn.active {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .staff-card {
        border-radius: 12px;
        transition: all 0.3s ease;
        border: 2px solid #e9ecef;
    }

    .staff-card:hover {
        border-color: #667eea;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.2);
    }

    .advance-badge {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        color: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 12px;
        font-weight: 600;
    }

    .weekly-stats {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 15px;
    }

    .btn-gradient {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        color: white;
    }

    .btn-gradient:hover {
        background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- ================= HEADER ================= --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h3><i class="fa fa-user-check text-primary me-2"></i>Staff Attendance Management</h3>
                    <p class="text-muted mb-0">Mark attendance, manage advances & track weekly performance</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#advanceModal">
                        <i class="fa fa-money-bill-wave me-1"></i> Record Advance
                    </button>
                    <button class="btn btn-gradient" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                        <i class="fa fa-plus me-1"></i> Mark Attendance
                    </button>
                </div>
            </div>

            {{-- ================= ATTENDANCE MODE SELECTOR ================= --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fa fa-cog me-2"></i>Attendance Mode</h5>
                        <div class="mode-selector">
                            <button class="mode-btn active" id="bulkModeBtn" data-mode="bulk">
                                <i class="fa fa-users me-1"></i> Bulk Mode
                            </button>
                            <button class="mode-btn" id="singleModeBtn" data-mode="single">
                                <i class="fa fa-user me-1"></i> Single Mode
                            </button>
                        </div>
                    </div>
                    <p class="text-muted mb-0 mt-2">
                        <small>
                            <strong>Bulk Mode:</strong> Mark attendance for all staff at once |
                            <strong>Single Mode:</strong> Mark attendance for individual staff members
                        </small>
                    </p>
                </div>
            </div>

            {{-- ================= SUMMARY CARDS ================= --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="summary-card summary-present">
                        <h6><i class="fa fa-check-circle me-1"></i>Present Today</h6>
                        <h4>{{ $records->where('status', 'present')->count() }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card summary-absent">
                        <h6><i class="fa fa-times-circle me-1"></i>Absent</h6>
                        <h4>{{ $records->where('status', 'absent')->count() }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card summary-leave">
                        <h6><i class="fa fa-calendar-times me-1"></i>On Leave</h6>
                        <h4>{{ $records->where('status', 'leave')->count() }}</h4>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-card summary-half">
                        <h6><i class="fa fa-clock me-1"></i>Half Day</h6>
                        <h4>{{ $records->where('status', 'half_day')->count() }}</h4>
                    </div>
                </div>
            </div>

            {{-- ================= WEEKLY STAFF OVERVIEW ================= --}}
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fa fa-calendar-week me-2"></i>Current Week Overview</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($staffs as $staff)
                            <div class="col-md-4">
                                <div class="staff-card p-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="mb-1"><i class="fa fa-user-circle text-primary me-1"></i>{{ $staff->name }}</h6>
                                            <small class="text-muted">{{ $staff->designation }}</small>
                                        </div>
                                        <button class="btn btn-sm btn-outline-primary quick-mark-btn"
                                                data-staff-id="{{ $staff->id }}"
                                                data-staff-name="{{ $staff->name }}">
                                            <i class="fa fa-check-square"></i>
                                        </button>
                                    </div>

                                    @php
                                        $summary = $weeklySummary[$staff->id] ?? ['present' => 0, 'absent' => 0, 'leave' => 0, 'half_day' => 0];
                                    @endphp

                                    <div class="weekly-stats mb-2">
                                        <div class="row text-center">
                                            <div class="col-3">
                                                <small class="d-block opacity-75">Present</small>
                                                <strong>{{ $summary['present'] }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="d-block opacity-75">Absent</small>
                                                <strong>{{ $summary['absent'] }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="d-block opacity-75">Leave</small>
                                                <strong>{{ $summary['leave'] }}</strong>
                                            </div>
                                            <div class="col-3">
                                                <small class="d-block opacity-75">Half</small>
                                                <strong>{{ $summary['half_day'] }}</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center">
                                        <button class="btn btn-sm btn-success view-advances-btn"
                                                data-staff-id="{{ $staff->id }}"
                                                data-staff-name="{{ $staff->name }}">
                                            <i class="fa fa-eye me-1"></i> Advances
                                        </button>
                                        <button class="btn btn-sm btn-info view-history-btn"
                                                data-staff-id="{{ $staff->id }}">
                                            <i class="fa fa-history me-1"></i> History
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ================= FILTERS ================= --}}
            <form method="GET" class="card p-3 mb-3">
                <div class="row g-2">
                    <div class="col-md-3">
                        <label><i class="fa fa-user me-1"></i>Staff</label>
                        <select name="staff_id" class="form-control">
                            <option value="">All Staff</option>
                            @foreach($staffs as $s)
                                <option value="{{ $s->id }}" {{ request('staff_id') == $s->id ? 'selected' : '' }}>
                                    {{ $s->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label><i class="fa fa-filter me-1"></i>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label><i class="fa fa-calendar me-1"></i>From</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label><i class="fa fa-calendar me-1"></i>To</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">
                            <i class="fa fa-search me-1"></i> Apply
                        </button>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <a href="{{ route('staff-attendance.index') }}" class="btn btn-secondary w-100">
                            <i class="fa fa-redo"></i>
                        </a>
                    </div>
                </div>
            </form>

            {{-- ================= ATTENDANCE LIST ================= --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-list me-2"></i>Attendance Records</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Overtime</th>
                                <th>Remarks</th>
                                <th width="150">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $k => $row)
                                <tr>
                                    <td>{{ $records->firstItem() + $k }}</td>
                                    <td>
                                        <strong>{{ $row->staff->name }}</strong><br>
                                        <small class="text-muted">{{ $row->staff->designation }}</small>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($row->attendence_date)->format('d M, Y') }}</td>
                                    <td>
                                        <span class="badge
                                            @if($row->status == 'present') bg-success
                                            @elseif($row->status == 'absent') bg-danger
                                            @elseif($row->status == 'leave') bg-warning text-dark
                                            @else bg-info
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $row->check_in ?? '-' }}</td>
                                    <td>{{ $row->check_out ?? '-' }}</td>
                                    <td>{{ $row->overtime_hours ?? '-' }}</td>
                                    <td>{{ $row->remarks ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-warning editAttendanceBtn"
                                                data-id="{{ $row->id }}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger deleteAttendanceBtn"
                                                data-id="{{ $row->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <i class="fa fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                        <h6 class="text-muted">No attendance records found</h6>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($records->hasPages())
                        <div class="mt-3">
                            {{ $records->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================= ADD ATTENDANCE MODAL (BULK MODE) ================= --}}
<div class="modal fade" id="addAttendanceModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="attendanceForm">
                @csrf
                <div class="modal-header bg-gradient text-white">
                    <h5><i class="fa fa-calendar-check me-2"></i><span id="modalTitle">Mark Attendance</span></h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="fw-bold"><i class="fa fa-calendar me-1"></i>Date</label>
                            <input type="date" name="attendance_date" value="{{ date('Y-m-d') }}"
                                   class="form-control" required>
                        </div>
                        <div class="col-md-8 d-flex align-items-end">
                            <div class="alert alert-info mb-0 w-100">
                                <i class="fa fa-info-circle me-2"></i>
                                <strong>Note:</strong> Only fill the rows for staff you want to mark attendance for. Leave others empty.
                            </div>
                        </div>
                    </div>

                    <div id="bulkAttendanceSection">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="200">Staff</th>
                                    <th width="150">Status</th>
                                    <th>Check In</th>
                                    <th>Check Out</th>
                                    <th>Overtime (hrs)</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="staffRows"></tbody>
                        </table>
                    </div>

                    <div id="singleAttendanceSection" class="d-none">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Select Staff</label>
                                <select name="single_staff_id" id="singleStaffSelect" class="form-control">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Status</label>
                                <select name="single_status" id="singleStatus" class="form-control">
                                    <option value="">Select</option>
                                    <option value="present">Present</option>
                                    <option value="absent">Absent</option>
                                    <option value="leave">Leave</option>
                                    <option value="half_day">Half Day</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Check In</label>
                                <input type="time" name="single_check_in" id="singleCheckIn" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Check Out</label>
                                <input type="time" name="single_check_out" id="singleCheckOut" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Overtime (hours)</label>
                                <input type="text" name="single_overtime" id="singleOvertime" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label>Remarks</label>
                                <input type="text" name="single_remarks" id="singleRemarks" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fa fa-times me-1"></i> Cancel
                    </button>
                    <button type="submit" class="btn btn-gradient">
                        <i class="fa fa-save me-1"></i> Save Attendance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= ADVANCE PAYMENT MODAL ================= --}}
<div class="modal fade" id="advanceModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="advanceForm">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5><i class="fa fa-money-bill-wave me-2"></i>Record Advance Payment</h5>
                    <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="fw-bold">Staff Member</label>
                        <select name="staff_id" class="form-control" required>
                            <option value="">-- Select Staff --</option>
                            @foreach($staffs as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Amount</label>
                        <input type="number" name="amount" class="form-control" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Date</label>
                        <input type="date" name="date" value="{{ date('Y-m-d') }}" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold">Remarks</label>
                        <textarea name="remarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-check me-1"></i> Save Advance
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= VIEW ADVANCES MODAL ================= --}}
<div class="modal fade" id="viewAdvancesModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5><i class="fa fa-list me-2"></i>Advances - <span id="advanceStaffName"></span></h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <strong>Current Week Total:</strong> Rs. <span id="totalAdvanceAmount">0.00</span>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="advancesList"></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ================= EDIT MODAL ================= --}}
<div class="modal fade" id="editAttendanceModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAttendanceForm">
                @csrf
                <input type="hidden" name="id" id="editId">
                <div class="modal-header bg-warning">
                    <h5><i class="fa fa-edit me-2"></i>Edit Attendance</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="editStatus" class="form-control" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">Leave</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Check In</label>
                        <input type="time" name="check_in" id="editCheckIn" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Check Out</label>
                        <input type="time" name="check_out" id="editCheckOut" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Overtime</label>
                        <input type="text" name="overtime_hours" id="editOvertime" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label>Remarks</label>
                        <textarea name="remarks" id="editRemarks" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    const staffs = @json($staffs);
    let currentMode = 'bulk';

    // Mode Selector
    $('.mode-btn').click(function() {
        currentMode = $(this).data('mode');
        $('.mode-btn').removeClass('active');
        $(this).addClass('active');

        if(currentMode === 'bulk') {
            $('#bulkAttendanceSection').removeClass('d-none');
            $('#singleAttendanceSection').addClass('d-none');
            $('#modalTitle').text('Mark Attendance (Bulk Mode)');
        } else {
            $('#bulkAttendanceSection').addClass('d-none');
            $('#singleAttendanceSection').removeClass('d-none');
            $('#modalTitle').text('Mark Attendance (Single Mode)');
        }
    });

    // Load staff rows in bulk mode
    $('#addAttendanceModal').on('shown.bs.modal', function() {
        if(currentMode === 'bulk') {
            let html = '';
            staffs.forEach(s => {
                html += `
                    <tr>
                        <td><strong>${s.name}</strong></td>
                        <td>
                            <select name="attendance[${s.id}][status]" class="form-control status-select">
                                <option value="">-</option>
                                <option value="present">Present</option>
                                <option value="absent">Absent</option>
                                <option value="leave">Leave</option>
                                <option value="half_day">Half Day</option>
                            </select>
                        </td>
                        <td><input type="time" name="attendance[${s.id}][check_in]" class="form-control ci"></td>
                        <td><input type="time" name="attendance[${s.id}][check_out]" class="form-control co"></td>
                        <td><input type="text" name="attendance[${s.id}][overtime]" class="form-control ot"></td>
                        <td><input type="text" name="attendance[${s.id}][remarks]" class="form-control rm"></td>
                    </tr>
                `;
            });
            $('#staffRows').html(html);
        }
    });

    // Status change logic
    $(document).on('change', '.status-select, #singleStatus', function() {
        let v = $(this).val();
        let row = $(this).closest('tr');

        let ci, co, ot, rm;

        if($(this).attr('id') === 'singleStatus') {
            ci = $('#singleCheckIn');
            co = $('#singleCheckOut');
            ot = $('#singleOvertime');
            rm = $('#singleRemarks');
        } else {
            ci = row.find('.ci');
            co = row.find('.co');
            ot = row.find('.ot');
            rm = row.find('.rm');
        }

        // Reset all
        ci.add(co).add(ot).add(rm).prop('readonly', false).val('');

        if(v === 'absent') {
            ci.add(co).add(ot).add(rm).prop('readonly', true).val('');
        }
        if(v === 'leave') {
            ci.add(co).add(ot).prop('readonly', true).val('');
        }
    });

    // Submit attendance form
    $('#attendanceForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '/staff-attendance/save',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                location.reload();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
            }
        });
    });

    // Submit advance form
    $('#advanceForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '/staff-attendance/advance',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                $('#advanceModal').modal('hide');
                $('#advanceForm')[0].reset();
            },
            error: function(xhr) {
                alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
            }
        });
    });

    // View advances
    $('.view-advances-btn').click(function() {
        let staffId = $(this).data('staff-id');
        let staffName = $(this).data('staff-name');

        $('#advanceStaffName').text(staffName);

        $.get('/staff-attendance/advances/' + staffId, function(res) {
            $('#totalAdvanceAmount').text(res.total.toFixed(2));

            let html = '';
            if(res.advances.length > 0) {
                res.advances.forEach(adv => {
                    html += `
                        <tr>
                            <td>${adv.date}</td>
                            <td>Rs. ${parseFloat(adv.adjust_amount).toFixed(2)}</td>
                            <td>${adv.remarks || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                html = '<tr><td colspan="3" class="text-center">No advances this week</td></tr>';
            }

            $('#advancesList').html(html);
            $('#viewAdvancesModal').modal('show');
        });
    });

    // Edit attendance
    $('.editAttendanceBtn').click(function() {
        let id = $(this).data('id');

        $.get('/staff-attendance/edit/' + id, function(res) {
            $('#editId').val(res.id);
            $('#editStatus').val(res.status);
            $('#editCheckIn').val(res.check_in);
            $('#editCheckOut').val(res.check_out);
            $('#editOvertime').val(res.overtime_hours);
            $('#editRemarks').val(res.remarks);
            $('#editAttendanceModal').modal('show');
        });
    });

    // Update attendance
    $('#editAttendanceForm').submit(function(e) {
        e.preventDefault();

        $.ajax({
            url: '/staff-attendance/update',
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                alert(res.message);
                location.reload();
            }
        });
    });

    // Delete attendance
    $('.deleteAttendanceBtn').click(function() {
        if(!confirm('Are you sure?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: '/staff-attendance/delete/' + id,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function(res) {
                alert(res.message);
                location.reload();
            }
        });
    });

    // Quick mark button
    $('.quick-mark-btn').click(function() {
        let staffId = $(this).data('staff-id');
        let staffName = $(this).data('staff-name');

        // Switch to single mode and prefill
        currentMode = 'single';
        $('.mode-btn').removeClass('active');
        $('#singleModeBtn').addClass('active');
        $('#bulkAttendanceSection').addClass('d-none');
        $('#singleAttendanceSection').removeClass('d-none');
        $('#modalTitle').text('Mark Attendance - ' + staffName);

        $('#singleStaffSelect').val(staffId);
        $('#addAttendanceModal').modal('show');
    });

    // View history
    $('.view-history-btn').click(function() {
        let staffId = $(this).data('staff-id');
        window.location.href = '{{ route("staff-attendance.index") }}?staff_id=' + staffId;
    });
</script>
