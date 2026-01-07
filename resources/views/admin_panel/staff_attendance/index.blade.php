@include('admin_panel.include.header_include')

<style>
    .summary-card {
        border-left: 5px solid;
        padding: 15px;
        border-radius: 6px;
        background: #fff
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
        font-weight: 600
    }

    .table thead th {
        white-space: nowrap
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- ================= HEADER ================= --}}
            <div class="page-header d-flex justify-content-between align-items-center">
                <div>
                    <h4>Staff Attendance</h4>
                    <h6>Attendance, History & Filters</h6>
                </div>
                <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addAttendanceModal">
                    + Mark Attendance
                </button>
            </div>

            {{-- ================= SUMMARY ================= --}}
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="summary-card summary-present">
                        <h6>Present</h6>
                        <h4>{{ $records->where('status', 'present')->count() }}</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-card summary-absent">
                        <h6>Absent</h6>
                        <h4>{{ $records->where('status', 'absent')->count() }}</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-card summary-leave">
                        <h6>Leave</h6>
                        <h4>{{ $records->where('status', 'leave')->count() }}</h4>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="summary-card summary-half">
                        <h6>Half Day</h6>
                        <h4>{{ $records->where('status', 'half_day')->count() }}</h4>
                    </div>
                </div>
            </div>

            {{-- ================= FILTERS ================= --}}
            <form method="GET" class="card p-3 mb-3">
                <div class="row g-2">

                    <div class="col-md-3">
                        <label>Staff</label>
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
                        <label>Status</label>
                        <select name="status" class="form-control">
                            <option value="">All</option>
                            <option value="present" {{ request('status') == 'present' ? 'selected' : '' }}>Present</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent</option>
                            <option value="leave" {{ request('status') == 'leave' ? 'selected' : '' }}>Leave</option>
                            <option value="half_day" {{ request('status') == 'half_day' ? 'selected' : '' }}>Half Day</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>From</label>
                        <input type="date" name="from" value="{{ request('from') }}" class="form-control">
                    </div>

                    <div class="col-md-2">
                        <label>To</label>
                        <input type="date" name="to" value="{{ request('to') }}" class="form-control">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary w-100">Apply</button>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <a href="{{ route('staff-attendance.index') }}" class="btn btn-secondary w-100">Reset</a>
                    </div>

                </div>
            </form>

            {{-- ================= LIST ================= --}}
            <div class="card">
                <div class="card-body table-responsive">

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Staff</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Over Time</th>
                                <th>Reason</th>
                                <th width="180">Action</th>
                            </tr>
                        </thead>

<tbody>
    @forelse($records as $k => $row)
        <tr>
            <td>{{ $k + 1 }}</td>
            <td>{{ $row->staff->name }}</td>
            <td>{{ \Carbon\Carbon::parse($row->attendence_date)->format('d-M-Y') }}</td>

            <td>
                <span class="badge
                    {{ $row->status == 'present' ? 'bg-success' :
                        ($row->status == 'absent' ? 'bg-danger' :
                            ($row->status == 'leave' ? 'bg-warning' : 'bg-info')) }}">
                    {{ ucfirst(str_replace('_', ' ', $row->status)) }}
                </span>
            </td>

            {{-- Check In --}}
            <td>
                @if($row->check_in)
                    {{ $row->check_in }}
                @else
                    <span class="badge 
                        {{ $row->status == 'absent' ? 'bg-danger' : 
                           ($row->status == 'leave' ? 'bg-warning' : 
                           ($row->status == 'half_day' ? 'bg-info' : 'bg-secondary')) }}">
                        {{ $row->status == 'absent' ? 'Absent' : 
                           ($row->status == 'leave' ? 'Leave' : 
                           ($row->status == 'half_day' ? 'Half Day' : '-')) }}
                    </span>
                @endif
            </td>

            {{-- Check Out --}}
            <td>
                @if($row->check_out)
                    {{ $row->check_out }}
                @else
                    <span class="badge 
                        {{ $row->status == 'absent' ? 'bg-danger' : 
                           ($row->status == 'leave' ? 'bg-warning' : 
                           ($row->status == 'half_day' ? 'bg-info' : 'bg-secondary')) }}">
                        {{ $row->status == 'absent' ? 'Absent' : 
                           ($row->status == 'leave' ? 'Leave' : 
                           ($row->status == 'half_day' ? 'Half Day' : '-')) }}
                    </span>
                @endif
            </td>

            {{-- Overtime --}}
            <td>
                @if($row->overtime_hours)
                    {{ $row->overtime_hours }}
                @else
                    <span class="badge 
                        {{ $row->status == 'absent' ? 'bg-danger' : 
                           ($row->status == 'leave' ? 'bg-warning' : 
                           ($row->status == 'half_day' ? 'bg-info' : 'bg-secondary')) }}">
                        {{ $row->status == 'absent' ? 'Absent' : 
                           ($row->status == 'leave' ? 'Leave' : 
                           ($row->status == 'half_day' ? 'Half Day' : '-')) }}
                    </span>
                @endif
            </td>

            {{-- Remarks --}}
            <td>
                @if($row->remarks)
                    {{ $row->remarks }}
                @else
                    <span class="badge 
                        {{ $row->status == 'absent' ? 'bg-danger' : 
                           ($row->status == 'leave' ? 'bg-warning' : 
                           ($row->status == 'half_day' ? 'bg-info' : 'bg-secondary')) }}">
                        {{ $row->status == 'absent' ? 'Absent' : 
                           ($row->status == 'leave' ? 'Leave' : 
                           ($row->status == 'half_day' ? 'Half Day' : '-')) }}
                    </span>
                @endif
            </td>

            <td>
                <button class="btn btn-sm btn-info viewHistoryBtn"
                    data-staff="{{ $row->staff_id }}">History</button>
                <button class="btn btn-sm btn-primary editBtn"
                    data-id="{{ $row->id }}">Edit</button>
                <button class="btn btn-sm btn-danger deleteAttendanceBtn"
                    data-id="{{ $row->id }}">Delete</button>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="9" class="text-center">No Records</td>
        </tr>
    @endforelse
</tbody>
                    </table>

                    <div class="mt-2">
                        {{ $records->withQueryString()->links() }}
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

{{-- ================= STAFF HISTORY MODAL ================= --}}
<div class="modal fade" id="historyModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5>Staff Attendance History</h5>
                <button class="btn-close text-black" data-bs-dismiss="modal">X</button>
            </div>

            <div class="modal-body">
                <div class="row mb-3" id="historySummary"></div>

                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Status</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Over Time</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody"></tbody>
                </table>
            </div>

        </div>
    </div>
</div>

{{-- ================= ADD ATTENDANCE MODAL ================= --}}
<div class="modal fade" id="addAttendanceModal">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <form id="attendanceForm">
                @csrf

                <div class="modal-header">
                    <h5>Add Attendance</h5>
                    <button class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>

                <div class="modal-body">

                    <div class="col-md-4 mb-3">
                        <label>Date</label>
                        <input type="date" name="attendance_date" value="{{ date('Y-m-d') }}" class="form-control"
                            required>
                    </div>

                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Staff</th>
                                <th>Status</th>
                                <th>In</th>
                                <th>Out</th>
                                <th>Over Time</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody id="staffRows"></tbody>
                    </table>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary">Save Attendance</button>
                </div>

            </form>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- ================= JS ================= --}}
<script>
    // ================= FORM SUBMIT =================
    $('#attendanceForm').on('submit', function (e) {
        e.preventDefault();

        let filled = false;
        $('.status').each(function () {
            if ($(this).val() !== '') { filled = true; }
        });

        if (!filled) {
            alert('Please mark attendance for at least one staff');
            return;
        }

        $.post("{{ route('staff-attendance.store') }}",
            $(this).serialize(),
            function (res) {
                if (res.success) {
                    alert(res.message);
                    $('#addAttendanceModal').modal('hide');
                    location.reload();
                }
            }
        ).fail(function (xhr) {
            alert('Error: ' + (xhr.responseJSON?.message || 'Something went wrong'));
        });
    });

    let staffs = @json($staffs);

    // ================= LOAD STAFF =================
    $('#addAttendanceModal').on('shown.bs.modal', () => {
        let html = '';
        staffs.forEach(s => {
            html += `
        <tr>
            <td>${s.name}</td>
            <td>
                <select name="attendance[${s.id}][status]" class="form-control status">
                    <option value="">Select</option>
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
        </tr>`;
        });
        $('#staffRows').html(html);
    });

    // ================= STATUS RULES =================
    $(document).on('change', '.status', function () {
        let tr = $(this).closest('tr');
        let v = $(this).val();

        let ci = tr.find('.ci'),
            co = tr.find('.co'),
            ot = tr.find('.ot'),
            rm = tr.find('.rm');

        // Reset all fields
        ci.add(co).add(ot).add(rm).prop('readonly', false).val('');

        // PRESENT
        if (v === 'present') {
            rm.prop('readonly', true).val('');
        }

        // HALF DAY - all allowed
        if (v === 'half_day') {
            // All fields enabled
        }

        // ABSENT
        if (v === 'absent') {
            ci.add(co).add(ot).val('').prop('readonly', true);
            rm.prop('readonly', true).val('');
        }

        // LEAVE
        if (v === 'leave') {
            ci.add(co).add(ot).val('').prop('readonly', true);
            rm.prop('readonly', false);
        }
    });

    // ================= VIEW HISTORY =================
    $(document).on('click', '.viewHistoryBtn', function () {
        let staffId = $(this).data('staff');

        $.get("/staff-attendance/history/" + staffId, function (res) {

            // Summary Cards
            let summaryHtml = `
            <div class="col-md-3">
                <div class="summary-card summary-present">
                    <h6>Present</h6>
                    <h4>${res.summary.present}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card summary-absent">
                    <h6>Absent</h6>
                    <h4>${res.summary.absent}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card summary-leave">
                    <h6>Leave</h6>
                    <h4>${res.summary.leave}</h4>
                </div>
            </div>
            <div class="col-md-3">
                <div class="summary-card summary-half">
                    <h6>Half Day</h6>
                    <h4>${res.summary.half_day}</h4>
                </div>
            </div>
        `;
            $('#historySummary').html(summaryHtml);

            // History Table
            let bodyHtml = '';
            res.records.forEach(r => {
                let statusClass = r.status === 'present' ? 'bg-success' :
                    r.status === 'absent' ? 'bg-danger' :
                        r.status === 'leave' ? 'bg-warning' : 'bg-info';

                let date = new Date(r.attendence_date);
                let formattedDate = date.toLocaleDateString('en-GB', {
                    day: '2-digit',
                    month: 'short',
                    year: 'numeric'
                });

                bodyHtml += `
                <tr>
                    <td>${formattedDate}</td>
                    <td><span class="badge ${statusClass}">${r.status.replace('_', ' ').toUpperCase()}</span></td>
                    <td>${r.check_in || '-'}</td>
                    <td>${r.check_out || '-'}</td>
                    <td>${r.overtime_hours || '-'}</td>
                    <td>${r.remarks || '-'}</td>
                </tr>
            `;
            });

            $('#historyBody').html(bodyHtml || '<tr><td colspan="6" class="text-center">No history found</td></tr>');

            $('#historyModal').modal('show');
        }).fail(function () {
            alert('Failed to load history');
        });
    });

    // ================= EDIT BUTTON =================
    $(document).on('click', '.editBtn', function () {
        let id = $(this).data('id');

        $.get("/staff-attendance/edit/" + id, function (attendance) {
            // Create edit modal dynamically or populate existing one
            let editHtml = `
            <div class="modal fade" id="editAttendanceModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form id="editAttendanceForm">
                            <div class="modal-header">
                                <h5>Edit Attendance - ${attendance.staff.name}</h5>
                                <button class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <input type="hidden" name="attendance_id" value="${attendance.id}">

                                <div class="mb-3">
                                    <label>Status</label>
                                    <select name="status" class="form-control edit-status" required>
                                        <option value="present" ${attendance.status === 'present' ? 'selected' : ''}>Present</option>
                                        <option value="absent" ${attendance.status === 'absent' ? 'selected' : ''}>Absent</option>
                                        <option value="leave" ${attendance.status === 'leave' ? 'selected' : ''}>Leave</option>
                                        <option value="half_day" ${attendance.status === 'half_day' ? 'selected' : ''}>Half Day</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label>Check In</label>
                                    <input type="time" name="check_in" value="${attendance.check_in || ''}" class="form-control edit-ci">
                                </div>

                                <div class="mb-3">
                                    <label>Check Out</label>
                                    <input type="time" name="check_out" value="${attendance.check_out || ''}" class="form-control edit-co">
                                </div>

                                <div class="mb-3">
                                    <label>Overtime Hours</label>
                                    <input type="text" name="overtime_hours" value="${attendance.overtime_hours || ''}" class="form-control edit-ot">
                                </div>

                                <div class="mb-3">
                                    <label>Remarks</label>
                                    <input type="text" name="remarks" value="${attendance.remarks || ''}" class="form-control edit-rm">
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;

            $('#editAttendanceModal').remove();
            $('body').append(editHtml);
            $('#editAttendanceModal').modal('show');

            // Trigger status change to apply rules
            $('.edit-status').trigger('change');
        });
    });

    // Edit form status change
    $(document).on('change', '.edit-status', function () {
        let v = $(this).val();
        let ci = $('.edit-ci'), co = $('.edit-co'), ot = $('.edit-ot'), rm = $('.edit-rm');

        ci.add(co).add(ot).add(rm).prop('readonly', false);

        if (v === 'present') { rm.prop('readonly', true).val(''); }
        if (v === 'absent') { ci.add(co).add(ot).add(rm).prop('readonly', true).val(''); }
        if (v === 'leave') { ci.add(co).add(ot).prop('readonly', true).val(''); }
    });

    // Edit form submit
    $(document).on('submit', '#editAttendanceForm', function (e) {
        e.preventDefault();

        $.post("/staff-attendance/update", $(this).serialize(), function (res) {
            if (res.success) {
                alert(res.message);
                $('#editAttendanceModal').modal('hide');
                location.reload();
            }
        });
    });

    // ================= DELETE BUTTON =================
    $(document).on('click', '.deleteAttendanceBtn', function () {
        if (!confirm('Are you sure you want to delete this record?')) return;

        let id = $(this).data('id');

        $.ajax({
            url: "/staff-attendance/delete/" + id,
            type: 'DELETE',
            data: { _token: '{{ csrf_token() }}' },
            success: function (res) {
                alert(res.message);
                location.reload();
            }
        });
    });
</script>