@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between">
                <div class="page-title">
                    <h4>Staff Salary Ledger: {{ $salesman->name }}</h4>
                    <h6>Track total earned, total paid, and current balance</h6>
                </div>
                <div>
                    <a href="{{ route('salesmen') }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left me-1"></i> Back to Staff List
                    </a>
                </div>
            </div>

            <div class="row">
                <!-- Summary Cards -->
                <div class="col-md-4">
                    <div class="card mb-4" style="background: #f8fafc; border-left: 4px solid #3b82f6;">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Earned</h6>
                            <h3 class="text-primary mb-0">PKR {{ number_format($ledgerSummary['total_earned']) }}</h3>
                            <small class="text-muted d-block mt-2">
                                Since {{ \Carbon\Carbon::parse($salesman->joining_date)->format('d M Y') }}
                                @if($salesman->status == 0 && $salesman->end_date)
                                    (Until {{ \Carbon\Carbon::parse($salesman->end_date)->format('d M Y') }})
                                @endif
                            </small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4" style="background: #f0fdf4; border-left: 4px solid #22c55e;">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Total Paid</h6>
                            <h3 class="text-success mb-0">PKR {{ number_format($ledgerSummary['total_paid']) }}</h3>
                            <small class="text-muted d-block mt-2">Sum of all salary payments</small>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    @php $bal = $ledgerSummary['balance']; @endphp
                    <div class="card mb-4" style="background: {{ $bal > 0 ? '#fef2f2' : ($bal < 0 ? '#f0fdf4' : '#f8fafc') }}; border-left: 4px solid {{ $bal > 0 ? '#ef4444' : ($bal < 0 ? '#22c55e' : '#94a3b8') }};">
                        <div class="card-body">
                            <h6 class="text-muted mb-2">Remaining Balance</h6>
                            @if($bal > 0)
                                <h3 class="text-danger mb-0">PKR {{ number_format($bal) }}</h3>
                                <small class="text-muted d-block mt-2">Amount owed to staff</small>
                            @elseif($bal < 0)
                                <h3 class="text-success mb-0">PKR {{ number_format(abs($bal)) }}</h3>
                                <small class="text-muted d-block mt-2">Advance paid (Overpaid)</small>
                            @else
                                <h3 class="text-muted mb-0">PKR 0</h3>
                                <small class="text-muted d-block mt-2">Account is settled</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profile Info -->
            <div class="card mb-4">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3"><strong>Designation:</strong> {{ $salesman->designation }}</div>
                        <div class="col-md-3"><strong>Pay Basis:</strong> <span class="badge bg-info text-dark">{{ ucfirst($salesman->salary_type) }}</span></div>
                        <div class="col-md-3"><strong>Set Salary:</strong> PKR {{ number_format($salesman->salary) }}</div>
                        <div class="col-md-3"><strong>Status:</strong> 
                            {!! $salesman->status == 1 ? '<span class="badge bg-success">Active</span>' : '<span class="badge bg-danger">Inactive</span>' !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ledger Table -->
            <div class="card">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fa fa-list-alt text-primary me-2"></i>Payment History</h5>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Payment Date</th>
                                <th>Period Covered</th>
                                <th>Pay Basis Used</th>
                                <th>Daily Rate</th>
                                <th>Days Paid</th>
                                <th>Deductions</th>
                                <th>Amount Paid</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($salesman->salaryPayments as $k => $payment)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                                    <td>
                                        @if($payment->from_date && $payment->to_date)
                                            <span class="badge bg-light text-dark border">
                                                {{ \Carbon\Carbon::parse($payment->from_date)->format('d M') }} -
                                                {{ \Carbon\Carbon::parse($payment->to_date)->format('d M Y') }}
                                            </span>
                                        @else
                                            {{ \Carbon\Carbon::parse($payment->payment_month . '-01')->format('M Y') }}
                                        @endif
                                    </td>
                                    <td>{{ ucfirst($payment->pay_basis ?? 'Monthly') }}</td>
                                    <td>{{ number_format($payment->daily_rate ?? 0, 2) }}</td>
                                    <td>{{ $payment->days_paid ?? ($payment->days_present + ($payment->days_half_day ?? 0)*0.5) }}</td>
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
                                    <td><strong class="text-success">+ PKR {{ number_format($payment->amount_paid, 0) }}</strong></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">
                                        <i class="fa fa-folder-open fa-2x mb-2 d-block"></i>
                                        No payments recorded yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
