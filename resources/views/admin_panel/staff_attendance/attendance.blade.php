@include('admin_panel.include.header_include')

<style>
    .status-badge { padding: 6px 14px; border-radius: 20px; font-weight: 500; }
    .badge-present { background: #d1fae5; color: #065f46; }
    .badge-absent { background: #fee2e2; color: #991b1b; }
    .badge-leave { background: #fef3c7; color: #92400e; }
    .badge-half { background: #dbeafe; color: #1e40af; }
    .summary-box { padding: 20px; border-radius: 10px; text-align: center; }
    .summary-box h3 { margin: 0; font-weight: 700; }
    .summary-box small { color: #6b7280; }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Header --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Staff Attendance</h4>
                    <p class="text-muted mb-0">Mark daily attendance for your staff</p>
                </div>
                <div>
                    <a href="{{ route('staff-advance.index') }}" class="btn btn-outline-success me-2">
                        <i class="fa fa-money-bill"></i> Advances
                    </a>
                    <a href="{{ route('staff-ledger-view') }}" class="btn btn-outline-primary">
                        <i class="fa fa-book"></i> Staff Ledger
                    </a>
                </div>
            </div>

            {{-- Tabs Navigation --}}
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="daily-tab" data-bs-toggle="tab" data-bs-target="#dailyTab" type="button">
                        <i class="fa fa-calendar-day me-1"></i> Daily Attendance
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="staff-tab" data-bs-toggle="tab" data-bs-target="#staffTab" type="button">
                        <i class="fa fa-user-clock me-1"></i> Staff-Wise History
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                {{-- Daily Attendance Tab --}}
                <div class="tab-pane fade show active" id="dailyTab" role="tabpanel">

            {{-- Today's Summary --}}
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="summary-box" style="background: #d1fae5;">
                        <h3 id="presentCount">{{ $todaySummary['present'] ?? 0 }}</h3>
                        <small>Present</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-box" style="background: #fee2e2;">
                        <h3 id="absentCount">{{ $todaySummary['absent'] ?? 0 }}</h3>
                        <small>Absent</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-box" style="background: #fef3c7;">
                        <h3 id="leaveCount">{{ $todaySummary['leave'] ?? 0 }}</h3>
                        <small>On Leave</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="summary-box" style="background: #dbeafe;">
                        <h3 id="halfCount">{{ $todaySummary['half_day'] ?? 0 }}</h3>
                        <small>Half Day</small>
                    </div>
                </div>
            </div>

            {{-- Mark Attendance Card --}}
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Mark Attendance</h5>
                    <input type="date" id="attendanceDate" class="form-control" style="width: 200px;" value="{{ date('Y-m-d') }}">
                </div>
                <div class="card-body">
                    <form id="bulkAttendanceForm">
                        @csrf
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="30">#</th>
                                    <th>Staff Name</th>
                                    <th>Designation</th>
                                    <th width="150">Status</th>
                                    <th width="120">Check In</th>
                                    <th width="120">Check Out</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($staffs as $index => $staff)
                                    @php
                                        $todayRecord = $todayAttendance->where('staff_id', $staff->id)->first();
                                    @endphp
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $staff->name }}</strong>
                                            @if($todayRecord)
                                                <span class="badge bg-success ms-2">Marked</span>
                                            @endif
                                        </td>
                                        <td>{{ $staff->designation }}</td>
                                        <td>
                                            <select name="attendance[{{ $staff->id }}][status]" class="form-select form-select-sm status-select"
                                                    data-staff-id="{{ $staff->id }}" {{ $todayRecord ? 'disabled' : '' }}>
                                                <option value="">--Select--</option>
                                                <option value="present" {{ $todayRecord && $todayRecord->status == 'present' ? 'selected' : '' }}>Present</option>
                                                <option value="absent" {{ $todayRecord && $todayRecord->status == 'absent' ? 'selected' : '' }}>Absent</option>
                                                <option value="leave" {{ $todayRecord && $todayRecord->status == 'leave' ? 'selected' : '' }}>Leave</option>
                                                <option value="half_day" {{ $todayRecord && $todayRecord->status == 'half_day' ? 'selected' : '' }}>Half Day</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="time" name="attendance[{{ $staff->id }}][check_in]"
                                                   class="form-control form-control-sm" value="{{ $todayRecord->check_in ?? '' }}"
                                                   {{ $todayRecord ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="time" name="attendance[{{ $staff->id }}][check_out]"
                                                   class="form-control form-control-sm" value="{{ $todayRecord->check_out ?? '' }}"
                                                   {{ $todayRecord ? 'disabled' : '' }}>
                                        </td>
                                        <td>
                                            <input type="text" name="attendance[{{ $staff->id }}][remarks]"
                                                   class="form-control form-control-sm" placeholder="Optional"
                                                   value="{{ $todayRecord->remarks ?? '' }}" {{ $todayRecord ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>

                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save me-1"></i> Save Attendance
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Attendance History --}}
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Attendance Records</h5>
                    <div class="d-flex gap-2">
                        <select id="filterStaff" class="form-select" style="width: 180px;">
                            <option value="">All Staff</option>
                            @foreach($staffs as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        <input type="date" id="filterFrom" class="form-control" style="width: 150px;">
                        <input type="date" id="filterTo" class="form-control" style="width: 150px;">
                        <button class="btn btn-primary" id="filterBtn"><i class="fa fa-search"></i></button>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover" id="attendanceTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Check In</th>
                                <th>Check Out</th>
                                <th>Remarks</th>
                                <th width="100">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $k => $row)
                                <tr>
                                    <td>{{ $records->firstItem() + $k }}</td>
                                    <td>
                                        <strong>{{ $row->staff->name ?? 'N/A' }}</strong><br>
                                        <small class="text-muted">{{ $row->staff->designation ?? '' }}</small>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($row->attendence_date)->format('d M Y') }}</td>
                                    <td>
                                        <span class="status-badge
                                            @if($row->status == 'present') badge-present
                                            @elseif($row->status == 'absent') badge-absent
                                            @elseif($row->status == 'leave') badge-leave
                                            @else badge-half @endif">
                                            {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                                        </span>
                                    </td>
                                    <td>{{ $row->check_in ?? '-' }}</td>
                                    <td>{{ $row->check_out ?? '-' }}</td>
                                    <td>{{ $row->remarks ?? '-' }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-warning edit-btn" data-id="{{ $row->id }}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger delete-btn" data-id="{{ $row->id }}">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No records found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if($records->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $records->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>

                </div>
                {{-- End Daily Tab --}}

                {{-- Staff-Wise History Tab --}}
                <div class="tab-pane fade" id="staffTab" role="tabpanel">
                    <div class="card">
                        <div class="card-header bg-white">
                            <h5 class="mb-0">Staff-Wise Attendance History</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold">Select Staff Member</label>
                                    <select id="staffWiseFilter" class="form-select">
                                        <option value="">-- Select Staff --</option>
                                        @foreach($staffs as $s)
                                            <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->designation }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">From Date</label>
                                    <input type="date" id="staffWiseFrom" class="form-control">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">To Date</label>
                                    <input type="date" id="staffWiseTo" class="form-control">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label fw-bold">&nbsp;</label>
                                    <button class="btn btn-primary w-100" id="loadStaffHistory">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label fw-bold">&nbsp;</label>
                                    <button class="btn btn-danger w-100" id="exportPDF" style="display: none;">
                                        <i class="fa fa-file-pdf"></i>
                                    </button>
                                </div>
                            </div>

                            <div id="staffHistoryResult" style="display: none;">
                                <div class="alert alert-info mb-3" id="staffSummary">
                                    <h6 class="mb-2" id="staffNameHeader"></h6>
                                    <div class="row text-center">
                                        <div class="col">
                                            <strong class="text-success" id="staffPresentCount">0</strong>
                                            <small class="d-block">Present</small>
                                        </div>
                                        <div class="col">
                                            <strong class="text-danger" id="staffAbsentCount">0</strong>
                                            <small class="d-block">Absent</small>
                                        </div>
                                        <div class="col">
                                            <strong class="text-warning" id="staffLeaveCount">0</strong>
                                            <small class="d-block">Leave</small>
                                        </div>
                                        <div class="col">
                                            <strong class="text-info" id="staffHalfCount">0</strong>
                                            <small class="d-block">Half Day</small>
                                        </div>
                                        <div class="col">
                                            <strong class="text-primary" id="staffTotalCount">0</strong>
                                            <small class="d-block">Total Days</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover" id="staffHistoryTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Date</th>
                                                <th>Day</th>
                                                <th>Status</th>
                                                <th>Check In</th>
                                                <th>Check Out</th>
                                                <th>Working Hours</th>
                                                <th>Remarks</th>
                                            </tr>
                                        </thead>
                                        <tbody id="staffHistoryBody">
                                            <tr>
                                                <td colspan="8" class="text-center text-muted py-4">
                                                    Select a staff member and click Search
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div id="noStaffData" class="alert alert-warning text-center" style="display: none;">
                                <i class="fa fa-exclamation-triangle me-2"></i>
                                No attendance records found for the selected staff member
                            </div>
                        </div>
                    </div>
                </div>
                {{-- End Staff-Wise Tab --}}

            </div>
            {{-- End Tab Content --}}

        </div>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editForm">
                @csrf
                <input type="hidden" name="attendance_id" id="editAttendanceId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Attendance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="leave">Leave</option>
                            <option value="half_day">Half Day</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Check In</label>
                            <input type="time" name="check_in" id="editCheckIn" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Check Out</label>
                            <input type="time" name="check_out" id="editCheckOut" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <input type="text" name="remarks" id="editRemarks" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
$(document).ready(function() {
    // Save Attendance
    $('#bulkAttendanceForm').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        formData += '&attendance_date=' + $('#attendanceDate').val();

        $.ajax({
            url: "{{ route('staff-attendance.store') }}",
            method: 'POST',
            data: formData,
            success: function(res) {
                if(res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                } else {
                    Swal.fire('Error', res.message || 'Something went wrong', 'error');
                }
            },
            error: function(xhr) {
                Swal.fire('Error', 'Failed to save attendance', 'error');
            }
        });
    });

    // Edit Button
    $('.edit-btn').on('click', function() {
        let id = $(this).data('id');
        $.get("{{ url('staff-attendance/edit') }}/" + id, function(data) {
            $('#editAttendanceId').val(data.id);
            $('#editStatus').val(data.status);
            $('#editCheckIn').val(data.check_in);
            $('#editCheckOut').val(data.check_out);
            $('#editRemarks').val(data.remarks);
            $('#editModal').modal('show');
        });
    });

    // Update Attendance
    $('#editForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('staff-attendance.update') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: function(res) {
                if(res.success) {
                    Swal.fire('Success', res.message, 'success').then(() => location.reload());
                }
            }
        });
    });

    // Delete Button
    $('.delete-btn').on('click', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete this record?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete'
        }).then((result) => {
            if(result.isConfirmed) {
                $.ajax({
                    url: "{{ url('staff-attendance/delete') }}/" + id,
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
    $('#filterBtn').on('click', function() {
        let staff = $('#filterStaff').val();
        let from = $('#filterFrom').val();
        let to = $('#filterTo').val();
        let url = "{{ route('staff-attendance.index') }}?";
        if(staff) url += 'staff_id=' + staff + '&';
        if(from) url += 'from=' + from + '&';
        if(to) url += 'to=' + to;
        window.location.href = url;
    });

    // Staff-Wise History
    let currentStaffData = null;
    $('#loadStaffHistory').on('click', function() {
        let staffId = $('#staffWiseFilter').val();
        let from = $('#staffWiseFrom').val();
        let to = $('#staffWiseTo').val();

        if(!staffId) {
            Swal.fire('Error', 'Please select a staff member', 'error');
            return;
        }

        // Show loading
        $('#staffHistoryBody').html('<tr><td colspan="8" class="text-center py-4"><i class="fa fa-spinner fa-spin"></i> Loading...</td></tr>');
        $('#staffHistoryResult').show();
        $('#noStaffData').hide();
        $('#exportPDF').hide();

        $.ajax({
            url: "{{ route('staff-attendance.index') }}",
            method: 'GET',
            data: {
                staff_id: staffId,
                from: from,
                to: to,
                ajax: 1
            },
            success: function(data) {
                currentStaffData = data;
                
                if(data.records) {
                    let html = '';
                    let presentCount = 0, absentCount = 0, leaveCount = 0, halfCount = 0, offCount = 0;
                    
                    // Create a map of existing attendance records
                    let attendanceMap = {};
                    data.records.forEach(record => {
                        attendanceMap[record.attendence_date] = record;
                    });

                    // Generate date range
                    let startDate = from ? new Date(from) : new Date(data.records[data.records.length - 1]?.attendence_date || new Date());
                    let endDate = to ? new Date(to) : new Date();
                    
                    let index = 0;
                    for(let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
                        let dateStr = d.toISOString().split('T')[0];
                        let record = attendanceMap[dateStr];
                        index++;

                        if(record) {
                            // Count status
                            if(record.status === 'present') presentCount++;
                            else if(record.status === 'absent') absentCount++;
                            else if(record.status === 'leave') leaveCount++;
                            else if(record.status === 'half_day') halfCount++;

                            let statusClass = '';
                            if(record.status === 'present') statusClass = 'badge-present';
                            else if(record.status === 'absent') statusClass = 'badge-absent';
                            else if(record.status === 'leave') statusClass = 'badge-leave';
                            else statusClass = 'badge-half';

                            let workingHours = '-';
                            if(record.check_in && record.check_out) {
                                let checkIn = new Date('1970-01-01T' + record.check_in);
                                let checkOut = new Date('1970-01-01T' + record.check_out);
                                let diff = (checkOut - checkIn) / (1000 * 60 * 60);
                                workingHours = diff.toFixed(1) + ' hrs';
                            }

                            html += `<tr>
                                <td>${index}</td>
                                <td>${new Date(record.attendence_date).toLocaleDateString('en-GB')}</td>
                                <td>${new Date(record.attendence_date).toLocaleDateString('en-GB', { weekday: 'short' })}</td>
                                <td><span class="status-badge ${statusClass}">${record.status.replace('_', ' ').toUpperCase()}</span></td>
                                <td>${record.check_in || '-'}</td>
                                <td>${record.check_out || '-'}</td>
                                <td>${workingHours}</td>
                                <td>${record.remarks || '-'}</td>
                            </tr>`;
                        } else {
                            // Show OFF for missing days
                            offCount++;
                            html += `<tr style="background-color: #f8f9fa;">
                                <td>${index}</td>
                                <td>${d.toLocaleDateString('en-GB')}</td>
                                <td>${d.toLocaleDateString('en-GB', { weekday: 'short' })}</td>
                                <td><span class="status-badge" style="background: #e5e7eb; color: #4b5563;">OFF</span></td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>No attendance marked</td>
                            </tr>`;
                        }
                    }

                    $('#staffHistoryBody').html(html);
                    $('#staffNameHeader').text(data.staff_name);
                    $('#staffPresentCount').text(presentCount);
                    $('#staffAbsentCount').text(absentCount);
                    $('#staffLeaveCount').text(leaveCount);
                    $('#staffHalfCount').text(halfCount);
                    $('#staffTotalCount').text(index);
                    $('#staffHistoryResult').show();
                    $('#noStaffData').hide();
                    $('#exportPDF').show();
                } else {
                    $('#staffHistoryResult').hide();
                    $('#noStaffData').show();
                    $('#exportPDF').hide();
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to load attendance history', 'error');
                $('#staffHistoryBody').html('<tr><td colspan="8" class="text-center text-danger py-4">Failed to load data</td></tr>');
                $('#exportPDF').hide();
            }
        });
    });

    // Export to PDF
    $('#exportPDF').on('click', function() {
        if(!currentStaffData) return;
        
        let staffId = $('#staffWiseFilter').val();
        let from = $('#staffWiseFrom').val();
        let to = $('#staffWiseTo').val();
        
        window.open(`{{ url('staff-attendance/export-pdf') }}?staff_id=${staffId}&from=${from}&to=${to}`, '_blank');
    });
});
</script>
