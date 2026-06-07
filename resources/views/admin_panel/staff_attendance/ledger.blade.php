@include('admin_panel.include.header_include')

<style>
    .ledger-card { border-radius: 10px; border: 1px solid #e5e7eb; transition: all 0.2s; }
    .ledger-card:hover { border-color: #3b82f6; box-shadow: 0 4px 12px rgba(59,130,246,0.15); }
    .stat-box { padding: 15px; border-radius: 8px; text-align: center; }
    .stat-box h4 { margin: 0; font-weight: 700; }
    .stat-box small { color: #6b7280; }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Header --}}
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="mb-1">Staff Ledger</h4>
                    <p class="text-muted mb-0">Complete ledger and summary of each staff</p>
                </div>
                <div>
                    <a href="{{ route('staff-attendance.index') }}" class="btn btn-outline-primary me-2">
                        <i class="fa fa-calendar-check"></i> Attendance
                    </a>
                    <a href="{{ route('staff-advance.index') }}" class="btn btn-outline-success">
                        <i class="fa fa-money-bill"></i> Advances
                    </a>
                </div>
            </div>

            {{-- Staff Selection --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label class="form-label">Select Staff</label>
                            <select id="staffSelect" class="form-select">
                                <option value="">-- Select Staff to View Ledger --</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}" {{ request('staff_id') == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }} ({{ $staff->designation }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" id="fromDate" class="form-control" value="{{ request('from', date('Y-m-01')) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" id="toDate" class="form-control" value="{{ request('to', date('Y-m-d')) }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="loadLedger" style="margin-top: 28px;">
                                <i class="fa fa-search me-1"></i> Load
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            @if(isset($selectedStaff))
                {{-- Staff Summary --}}
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="stat-box" style="background: #d1fae5;">
                            <h4>{{ $attendanceSummary['present'] ?? 0 }}</h4>
                            <small>Days Present</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box" style="background: #fee2e2;">
                            <h4>{{ $attendanceSummary['absent'] ?? 0 }}</h4>
                            <small>Days Absent</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box" style="background: #fef3c7;">
                            <h4>PKR {{ number_format($advanceSummary['salary'] ?? 0, 0) }}</h4>
                            <small>Salary Advance</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-box" style="background: #dbeafe;">
                            <h4>PKR {{ number_format($advanceSummary['additional'] ?? 0, 0) }}</h4>
                            <small>Additional Loan</small>
                        </div>
                    </div>
                </div>

                {{-- Staff Info Card --}}
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5><i class="fa fa-user-circle text-primary me-2"></i>{{ $selectedStaff->name }}</h5>
                                <p class="mb-1"><strong>Designation:</strong> {{ $selectedStaff->designation }}</p>
                                <p class="mb-1"><strong>Phone:</strong> {{ $selectedStaff->phone_number ?? 'N/A' }}</p>
                                <p class="mb-0"><strong>Salary:</strong> PKR {{ number_format($selectedStaff->salary ?? 0, 0) }}</p>
                            </div>
                            <div class="col-md-6 text-end">
                                <div class="p-3" style="background: #fef2f2; border-radius: 10px; display: inline-block;">
                                    <h6 class="mb-1">Total Pending Balance</h6>
                                    <h3 class="text-danger mb-0">PKR {{ number_format($totalPending ?? 0, 0) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    {{-- Attendance Ledger --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fa fa-calendar-check text-success me-2"></i>Attendance History</h5>
                            </div>
                            <div class="card-body table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>In/Out</th>
                                            <th>Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($attendanceRecords as $record)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($record->attendence_date)->format('d M') }}</td>
                                                <td>
                                                    <span class="badge
                                                        @if($record->status == 'present') bg-success
                                                        @elseif($record->status == 'absent') bg-danger
                                                        @elseif($record->status == 'leave') bg-warning text-dark
                                                        @else bg-info @endif">
                                                        {{ ucfirst(str_replace('_', ' ', $record->status)) }}
                                                    </span>
                                                </td>
                                                <td>{{ $record->check_in ?? '-' }} / {{ $record->check_out ?? '-' }}</td>
                                                <td>{{ $record->remarks ?? '-' }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No records</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Advance Ledger --}}
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-white">
                                <h5 class="mb-0"><i class="fa fa-money-bill text-warning me-2"></i>Advance Ledger</h5>
                            </div>
                            <div class="card-body table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <table class="table table-sm table-hover">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Date</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($advanceRecords as $adv)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($adv->date)->format('d M') }}</td>
                                                <td>
                                                    @if($adv->advance_type == 'salary')
                                                        <span class="badge" style="background: #fef3c7; color: #92400e;">Salary</span>
                                                    @else
                                                        <span class="badge" style="background: #dbeafe; color: #1e40af;">Additional</span>
                                                    @endif
                                                </td>
                                                <td><strong>{{ number_format($adv->amount, 0) }}</strong></td>
                                                <td>
                                                    @if($adv->status == 'cleared')
                                                        <span class="badge bg-success">Cleared</span>
                                                    @else
                                                        <span class="badge bg-warning">Pending</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="4" class="text-center text-muted">No records</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Payment History --}}
                <div class="card mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fa fa-history text-info me-2"></i>Payment & Recovery History</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Description</th>
                                    <th>Debit</th>
                                    <th>Credit</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentHistory as $payment)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($payment->date)->format('d M Y') }}</td>
                                        <td>{{ $payment->type }}</td>
                                        <td>{{ $payment->description ?? '-' }}</td>
                                        <td class="text-danger">{{ $payment->debit > 0 ? number_format($payment->debit, 0) : '-' }}</td>
                                        <td class="text-success">{{ $payment->credit > 0 ? number_format($payment->credit, 0) : '-' }}</td>
                                        <td><strong>{{ number_format($payment->balance, 0) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted">No payment history</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fa fa-user-circle fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">Select a staff member to view their ledger</h5>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
$(document).ready(function() {
    $('#loadLedger').on('click', function() {
        let staffId = $('#staffSelect').val();
        let from = $('#fromDate').val();
        let to = $('#toDate').val();

        if(!staffId) {
            Swal.fire('Error', 'Please select a staff member', 'warning');
            return;
        }

        let url = "{{ route('staff-ledger-view') }}?staff_id=" + staffId;
        if(from) url += '&from=' + from;
        if(to) url += '&to=' + to;

        window.location.href = url;
    });

    $('#staffSelect').on('change', function() {
        if($(this).val()) {
            $('#loadLedger').click();
        }
    });
});
</script>
