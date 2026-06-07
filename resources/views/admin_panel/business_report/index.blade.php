@include('admin_panel.include.header_include')
<style>
    :root {
        --primary-blue: #3B82F6;
        --primary-blue-dark: #2563EB;
        --success-green: #10B981;
        --danger-red: #EF4444;
        --warning-yellow: #F59E0B;
        --purple: #8B5CF6;
        --indigo: #6366F1;
        --cyan: #06B6D4;
        --orange: #F97316;
        --gray-50: #F9FAFB;
        --gray-100: #F3F4F6;
        --gray-600: #4B5563;
    }

    .page-header-custom {
        background: linear-gradient(135deg, var(--primary-blue), var(--indigo));
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-blue);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
    }

    .stat-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }

    .stat-box.blue { border-left-color: var(--primary-blue); }
    .stat-box.green { border-left-color: var(--success-green); }
    .stat-box.red { border-left-color: var(--danger-red); }
    .stat-box.yellow { border-left-color: var(--warning-yellow); }
    .stat-box.purple { border-left-color: var(--purple); }
    .stat-box.cyan { border-left-color: var(--cyan); }
    .stat-box.orange { border-left-color: var(--orange); }

    .stat-box .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-box .stat-icon.blue { background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark)); }
    .stat-box .stat-icon.green { background: linear-gradient(135deg, var(--success-green), #059669); }
    .stat-box .stat-icon.red { background: linear-gradient(135deg, var(--danger-red), #DC2626); }
    .stat-box .stat-icon.yellow { background: linear-gradient(135deg, var(--warning-yellow), #D97706); }
    .stat-box .stat-icon.purple { background: linear-gradient(135deg, var(--purple), #7C3AED); }
    .stat-box .stat-icon.cyan { background: linear-gradient(135deg, var(--cyan), #0891B2); }
    .stat-box .stat-icon.orange { background: linear-gradient(135deg, var(--orange), #EA580C); }

    .stat-value {
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--gray-600);
    }

    .stat-label {
        font-size: 0.85rem;
        color: #9CA3AF;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .section-card {
        background: white;
        border-radius: 15px;
        padding: 25px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 25px;
    }

    .section-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--primary-blue);
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--primary-blue);
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 10px;
    }

    .profit-card {
        background: linear-gradient(135deg, var(--success-green), #059669);
        color: white;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
    }

    .profit-card.loss {
        background: linear-gradient(135deg, var(--danger-red), #DC2626);
    }

    .profit-value {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .dues-box {
        background: linear-gradient(135deg, #F8FAFC, #F1F5F9);
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        border: 1px solid #E2E8F0;
    }

    .dues-box .value {
        font-size: 1.5rem;
        font-weight: 700;
    }

    .dues-box .label {
        font-size: 0.85rem;
        color: var(--gray-600);
    }

    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        margin-bottom: 25px;
    }

    .table-custom th {
        background: var(--primary-blue);
        color: white;
        font-weight: 600;
        padding: 12px 15px;
        border: none;
    }

    .table-custom td {
        padding: 12px 15px;
        vertical-align: middle;
    }

    .table-custom tbody tr:hover {
        background: #F8FAFC;
    }

    .profit-positive { color: var(--success-green); font-weight: 600; }
    .profit-negative { color: var(--danger-red); font-weight: 600; }

    .count-badge {
        background: var(--primary-blue);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
</style>
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <!-- Page Header -->
            <div class="page-header-custom">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-1"><i class="fas fa-chart-pie me-2"></i>Business Report</h3>
                        <p class="mb-0 opacity-75">Complete Overview - Jobs, Expenses, Payments & Profit</p>
                    </div>
                    <div>
                        <span class="badge bg-white text-primary px-3 py-2">
                            {{ \Carbon\Carbon::parse($fromDate)->format('d M') }} - {{ \Carbon\Carbon::parse($toDate)->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Date Filter -->
            <div class="filter-card">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    </div>
                    <div class="col-md-4">
                        <button type="submit" class="btn w-100" style="background: var(--primary-blue); color: white; padding: 10px;">
                            <i class="fas fa-filter me-1"></i>Apply Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Summary Stats Row 1: Jobs -->
            <div class="section-card">
                <div class="section-title"><i class="fas fa-briefcase"></i>Jobs Summary (Invoices)</div>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box blue">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value">{{ number_format($summaryStats['total_jobs']) }}</div>
                                    <div class="stat-label">Total Jobs</div>
                                </div>
                                <div class="stat-icon blue"><i class="fas fa-clipboard-list"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box green">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value">{{ number_format($summaryStats['total_jobs_amount']) }}</div>
                                    <div class="stat-label">Jobs Amount</div>
                                </div>
                                <div class="stat-icon green"><i class="fas fa-money-bill-wave"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box cyan">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value">{{ number_format($summaryStats['total_jobs_received']) }}</div>
                                    <div class="stat-label">Amount Received</div>
                                </div>
                                <div class="stat-icon cyan"><i class="fas fa-hand-holding-usd"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="stat-box yellow">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value">{{ number_format($summaryStats['total_jobs_pending']) }}</div>
                                    <div class="stat-label">Amount Pending</div>
                                </div>
                                <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Stats Row 2: Expenses -->
            <div class="section-card">
                <div class="section-title"><i class="fas fa-money-check-alt"></i>Expenses & Payments</div>
                <div class="row g-3">
                    <!-- Job Expense (Accrued) -->
                    <div class="col-lg-2 col-md-4">
                        <div class="stat-box red">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value" style="font-size: 1.2rem;">{{ number_format($summaryStats['job_assignment_expense']) }}</div>
                                    <div class="stat-label" style="font-size: 0.75rem;">Job Exp (Accrued)</div>
                                </div>
                                <div class="stat-icon red" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-file-invoice"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Payments -->
                    <div class="col-lg-2 col-md-4">
                        <div class="stat-box red">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value" style="font-size: 1.2rem;">{{ number_format($summaryStats['vendor_payments']) }}</div>
                                    <div class="stat-label" style="font-size: 0.75rem;">Vendor Paid</div>
                                </div>
                                <div class="stat-icon red" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-truck"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Contractor Payments -->
                    <div class="col-lg-2 col-md-4">
                        <div class="stat-box warning">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value" style="font-size: 1.2rem;">{{ number_format($summaryStats['contractor_payments']) }}</div>
                                    <div class="stat-label" style="font-size: 0.75rem;">Contractor Paid</div>
                                </div>
                                <div class="stat-icon yellow" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-hard-hat"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Other Expenses -->
                    <div class="col-lg-2 col-md-4">
                        <div class="stat-box orange">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value" style="font-size: 1.2rem;">{{ number_format($summaryStats['other_expenses']) }}</div>
                                    <div class="stat-label" style="font-size: 0.75rem;">Other Expenses</div>
                                </div>
                                <div class="stat-icon orange" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-receipt"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Staff Payments -->
                    <div class="col-lg-2 col-md-4">
                        <div class="stat-box purple">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value" style="font-size: 1.2rem;">{{ number_format($summaryStats['staff_payments']) }}</div>
                                    <div class="stat-label" style="font-size: 0.75rem;">Staff Payments</div>
                                </div>
                                <div class="stat-icon purple" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-users"></i></div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Out -->
                    <div class="col-lg-2 col-md-4">
                        <div class="stat-box red">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="stat-value" style="font-size: 1.2rem;">{{ number_format($summaryStats['total_payments_out']) }}</div>
                                    <div class="stat-label" style="font-size: 0.75rem;">Total OutFlow</div>
                                </div>
                                <div class="stat-icon red" style="width: 40px; height: 40px; font-size: 1rem;"><i class="fas fa-arrow-up"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Net Profit Box -->
            <div class="row mb-4">
                <div class="col-lg-6">
                    <div class="profit-card {{ $summaryStats['net_profit'] >= 0 ? '' : 'loss' }}">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="text-start">
                                <h5 class="mb-1">{{ $summaryStats['net_profit'] >= 0 ? 'NET PROFIT' : 'NET LOSS' }}</h5>
                                <small style="opacity:0.8">Jobs Amount - All Expenses</small>
                            </div>
                            <div class="profit-value">
                                Rs. {{ number_format(abs($summaryStats['net_profit'])) }}
                            </div>
                            <div style="font-size:3rem; opacity:0.5">
                                <i class="fas {{ $summaryStats['net_profit'] >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down' }}"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="section-card h-100 mb-0">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <div class="dues-box">
                                    <div class="value text-success">{{ number_format($summaryStats['total_receipts_in']) }}</div>
                                    <div class="label">Total Receipts In</div>
                                </div>
                            </div>
                            <div class="col-6 mb-3">
                                <div class="dues-box">
                                    <div class="value text-danger">{{ number_format($summaryStats['total_payments_out']) }}</div>
                                    <div class="label">Total Payments Out</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Dues & Balances -->
            <div class="section-card">
                <div class="section-title"><i class="fas fa-balance-scale"></i>Outstanding Dues & Balances</div>
                <div class="row g-3">
                    <div class="col-lg-3 col-md-6">
                        <div class="dues-box">
                            <div class="value text-primary">{{ number_format($duesStats['customer_dues']) }}</div>
                            <div class="label"><i class="fas fa-users me-1"></i>Customer Dues</div>
                            <hr class="my-2">
                            <small class="text-muted">Total Customers: {{ $duesStats['total_customers'] }}</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="dues-box">
                            <div class="value text-danger">{{ number_format($duesStats['vendor_dues']) }}</div>
                            <div class="label"><i class="fas fa-truck me-1"></i>Vendor Dues</div>
                            <hr class="my-2">
                            <small class="text-muted">Total Vendors: {{ $duesStats['total_vendors'] }}</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="dues-box">
                            <div class="value text-warning">{{ number_format($duesStats['contractor_dues']) }}</div>
                            <div class="label"><i class="fas fa-hard-hat me-1"></i>Contractor Dues</div>
                            <hr class="my-2">
                            <small class="text-muted">Total Contractors: {{ $duesStats['total_contractors'] }}</small>
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <div class="dues-box">
                            <div class="value text-purple" style="color: var(--purple);">{{ number_format($duesStats['staff_advances']) }}</div>
                            <div class="label"><i class="fas fa-user-tie me-1"></i>Staff Advances</div>
                            <hr class="my-2">
                            <small class="text-muted">Total Staff: {{ $duesStats['total_staff'] }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Date-wise Table -->
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-calendar-alt"></i>Date-wise Breakdown
                    <span class="count-badge ms-auto">{{ count($dateWiseData) }} Days</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Day</th>
                                <th class="text-center">Jobs</th>
                                <th class="text-end">Jobs Amount</th>
                                <th class="text-end">Job Exp (Accr)</th>
                                <th class="text-end">Vendor Paid</th>
                                <th class="text-end">Contractor Paid</th>
                                <th class="text-end">Other Expense</th>
                                <th class="text-end">Staff Payment</th>
                                <th class="text-end">Customer Receipt</th>
                                <th class="text-end">Day Profit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dateWiseData as $day)
                            <tr>
                                <td><strong>{{ $day['formatted_date'] }}</strong></td>
                                <td><span class="text-muted">{{ $day['day_name'] }}</span></td>
                                <td class="text-center">
                                    @if($day['jobs_count'] > 0)
                                        <span class="count-badge">{{ $day['jobs_count'] }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['jobs_amount'] > 0)
                                        <span class="text-success fw-bold">{{ number_format($day['jobs_amount']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['job_expense'] > 0)
                                        <span class="text-muted small">{{ number_format($day['job_expense']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['vendor_payment'] > 0)
                                        <span class="text-danger">{{ number_format($day['vendor_payment']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['contractor_payment'] > 0)
                                        <span class="text-warning">{{ number_format($day['contractor_payment']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['other_expense'] > 0)
                                        <span class="text-warning">{{ number_format($day['other_expense']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['staff_payment'] > 0)
                                        <span class="text-purple" style="color: var(--purple);">{{ number_format($day['staff_payment']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['customer_receipt'] > 0)
                                        <span class="text-info">{{ number_format($day['customer_receipt']) }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($day['profit'] >= 0)
                                        <span class="profit-positive">+{{ number_format($day['profit']) }}</span>
                                    @else
                                        <span class="profit-negative">{{ number_format($day['profit']) }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="11" class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3 d-block"></i>
                                    <h5 class="text-muted">No data for selected period</h5>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                        @if(count($dateWiseData) > 0)
                        <tfoot style="background: #F8FAFC;">
                            <tr>
                                <td colspan="2"><strong>TOTAL</strong></td>
                                <td class="text-center"><strong>{{ $summaryStats['total_jobs'] }}</strong></td>
                                <td class="text-end"><strong class="text-success">{{ number_format($summaryStats['total_jobs_amount']) }}</strong></td>
                                <td class="text-end"><strong class="text-muted small">{{ number_format($summaryStats['job_assignment_expense']) }}</strong></td>
                                <td class="text-end"><strong class="text-danger">{{ number_format($summaryStats['vendor_payments']) }}</strong></td>
                                <td class="text-end"><strong class="text-warning">{{ number_format($summaryStats['contractor_payments']) }}</strong></td>
                                <td class="text-end"><strong class="text-warning">{{ number_format($summaryStats['other_expenses']) }}</strong></td>
                                <td class="text-end"><strong style="color: var(--purple);">{{ number_format($summaryStats['staff_payments']) }}</strong></td>
                                <td class="text-end"><strong class="text-info">{{ number_format($summaryStats['total_receipts_in']) }}</strong></td>
                                <td class="text-end">
                                    @if($summaryStats['net_profit'] >= 0)
                                        <strong class="profit-positive">+{{ number_format($summaryStats['net_profit']) }}</strong>
                                    @else
                                        <strong class="profit-negative">{{ number_format($summaryStats['net_profit']) }}</strong>
                                    @endif
                                </td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
