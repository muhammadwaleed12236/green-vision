@include('admin_panel.include.header_include')
<style>
    :root {
        --primary-blue: #3B82F6;
        --primary-blue-dark: #2563EB;
        --success-green: #10B981;
        --danger-red: #EF4444;
        --warning-yellow: #F59E0B;
        --gray-50: #F9FAFB;
    }

    .page-header-custom {
        background: linear-gradient(135deg, var(--primary-blue), var(--primary-blue-dark));
        color: white;
        padding: 25px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
    }

    .mini-stat {
        background: white;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border-left: 4px solid var(--primary-blue);
    }

    .mini-stat.green { border-left-color: var(--success-green); }
    .mini-stat.red { border-left-color: var(--danger-red); }
    .mini-stat.orange { border-left-color: var(--warning-yellow); }

    .summary-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        margin-bottom: 20px;
    }

    .voucher-table th {
        background: var(--primary-blue);
        color: white;
        font-weight: 600;
        border: none;
        padding: 12px 15px;
    }

    .btn-close-day {
        background: linear-gradient(135deg, var(--success-green), #059669);
        border: none;
        padding: 12px 30px;
        font-weight: 600;
        font-size: 16px;
    }

    .closing-form {
        background: #F8FAFC;
        border: 2px dashed var(--primary-blue);
        border-radius: 10px;
        padding: 20px;
        margin: 20px 0;
    }
</style>

<body>
    @include('admin_panel.include.navbar_include')

    <div class="main-wrapper">
        @include('admin_panel.include.admin_sidebar_include')

        <div class="page-wrapper">
            <div class="content">
                <!-- Page Header -->
                <div class="page-header-custom">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h3 class="mb-0"><i class="fas fa-calculator me-2"></i>Daily Closing</h3>
                            <p class="mb-0 opacity-75">Close your day with complete cash control</p>
                        </div>
                        <div class="col-md-6 text-end">
                            <h4 class="mb-0">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</h4>
                            <small class="opacity-75">{{ \Carbon\Carbon::parse($date)->format('l') }}</small>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row">
                    <div class="col-md-3">
                        <div class="mini-stat green">
                            <h4 class="text-success mb-1">PKR {{ number_format($totalReceipts) }}</h4>
                            <p class="mb-0 text-muted">Total Receipts</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mini-stat red">
                            <h4 class="text-danger mb-1">PKR {{ number_format($totalPayments) }}</h4>
                            <p class="mb-0 text-muted">Total Payments</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mini-stat {{ $netCashFlow >= 0 ? 'green' : 'red' }}">
                            <h4 class="{{ $netCashFlow >= 0 ? 'text-success' : 'text-danger' }} mb-1">PKR {{ number_format($netCashFlow) }}</h4>
                            <p class="mb-0 text-muted">Net Cash Flow</p>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="mini-stat orange">
                            <h4 class="text-warning mb-1">PKR {{ number_format($currentClosingBalance) }}</h4>
                            <p class="mb-0 text-muted">Expected Closing</p>
                        </div>
                    </div>
                </div>

                <!-- Business Metrics Summary -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="summary-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h5 class="mb-0"><i class="fas fa-chart-bar text-primary me-2"></i>Today's Business Summary</h5>
                                <span class="badge bg-primary">{{ \Carbon\Carbon::parse($date)->format('d M Y') }}</span>
                            </div>

                            <div class="row">
                                <!-- Jobs Summary -->
                                <div class="col-md-6">
                                    <div class="border rounded p-3 mb-3" style="border-color: #e3f2fd !important; background: #f8fffe;">
                                        <h6 class="text-primary mb-3"><i class="fas fa-briefcase me-2"></i>Jobs Summary</h6>
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <h4 class="text-dark mb-1">{{ $businessMetrics['total_jobs'] }}</h4>
                                                    <small class="text-muted">Total Jobs</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <h4 class="text-success mb-1">PKR {{ number_format($businessMetrics['total_job_amount']) }}</h4>
                                                    <small class="text-muted">Total Amount</small>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <h5 class="text-info mb-1">{{ $businessMetrics['assigned_jobs'] }}</h5>
                                                    <small class="text-muted">Assigned Jobs</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <h5 class="text-warning mb-1">{{ $businessMetrics['partially_assigned'] }}</h5>
                                                    <small class="text-muted">Partial Assigned</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payments Summary -->
                                <div class="col-md-6">
                                    <div class="border rounded p-3 mb-3" style="border-color: #fff3e0 !important; background: #fffef7;">
                                        <h6 class="text-warning mb-3"><i class="fas fa-money-bill-wave me-2"></i>Payments Summary</h6>
                                        <div class="row">
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <h6 class="text-danger mb-1">{{ number_format($businessMetrics['contractor_payments']) }}</h6>
                                                    <small class="text-muted">Contractor</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <h6 class="text-danger mb-1">{{ number_format($businessMetrics['vendor_payments']) }}</h6>
                                                    <small class="text-muted">Vendor</small>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="text-center">
                                                    <h6 class="text-danger mb-1">{{ number_format($businessMetrics['expense_payments']) }}</h6>
                                                    <small class="text-muted">Expense</small>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="my-2">
                                        <div class="row">
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <h5 class="text-success mb-1">{{ number_format($businessMetrics['customer_recoveries']) }}</h5>
                                                    <small class="text-muted">Customer Recovery</small>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="text-center">
                                                    <h5 class="text-danger mb-1">{{ number_format($businessMetrics['staff_payments']) }}</h5>
                                                    <small class="text-muted">Staff Payment</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Daily Summary Calculation -->
                            <div class="border rounded p-3" style="border-color: #e8f5e8 !important; background: #f8fff8;">
                                <h6 class="text-success mb-3"><i class="fas fa-calculator me-2"></i>Daily Calculation</h6>
                                <div class="row">
                                    <div class="col-md-8">
                                        <small class="text-muted">
                                            Jobs: {{ number_format($businessMetrics['total_job_amount']) }} |
                                            Paid Out: {{ number_format($businessMetrics['contractor_payments'] + $businessMetrics['vendor_payments'] + $businessMetrics['expense_payments'] + $businessMetrics['staff_payments']) }} |
                                            Received: {{ number_format($businessMetrics['customer_recoveries']) }}
                                        </small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <strong class="text-success">
                                            Cash Remaining: PKR {{ number_format($businessMetrics['customer_recoveries'] - ($businessMetrics['contractor_payments'] + $businessMetrics['vendor_payments'] + $businessMetrics['expense_payments'] + $businessMetrics['staff_payments'])) }}
                                        </strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Party-wise Summary -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="summary-card">
                            <h5 class="mb-3"><i class="fas fa-users me-2"></i>Party-wise Summary</h5>
                            <div class="table-responsive">
                                <table class="table table-borderless">
                                    <thead>
                                        <tr class="voucher-table">
                                            <th>Party Type</th>
                                            <th>Receipts</th>
                                            <th>Payments</th>
                                            <th>Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($partySummary as $summary)
                                        <tr>
                                            <td><i class="fas fa-circle text-primary me-2"></i>{{ ucfirst($summary['party_type']) }}</td>
                                            <td class="text-success">PKR {{ number_format($summary['total_receipts']) }}</td>
                                            <td class="text-danger">PKR {{ number_format($summary['total_payments']) }}</td>
                                            <td><span class="badge bg-light text-dark">{{ $summary['count'] }}</span></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">No transactions for today</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Day Status -->
                        <div class="summary-card">
                            <h5 class="mb-3"><i class="fas fa-status me-2"></i>Day Status</h5>
                            @if($isDayClosed)
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle me-2"></i>Day is already closed
                                </div>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-clock me-2"></i>Day is still open
                                </div>

                                <!-- Closing Form -->
                                <div class="closing-form">
                                    <h6 class="mb-3">Close Today</h6>
                                    <form id="closingForm">
                                        @csrf
                                        <input type="hidden" name="date" value="{{ $date }}">

                                        <div class="mb-3">
                                            <label class="form-label">Actual Cash in Hand</label>
                                            <input type="number" class="form-control" name="cash_in_hand" step="0.01" required>
                                            <small class="text-muted">Expected: PKR {{ number_format($currentClosingBalance) }}</small>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Remarks</label>
                                            <textarea class="form-control" name="remarks" rows="2" placeholder="Any variance explanation..."></textarea>
                                        </div>

                                        <button type="submit" class="btn btn-close-day text-white w-100">
                                            <i class="fas fa-lock me-2"></i>Close Day
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Today's Vouchers -->
                <div class="summary-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Today's Vouchers ({{ $vouchers->count() }})</h5>
                        <div>
                            <a href="{{ route('journal-voucher.closing-history') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-history me-1"></i>Closing History
                            </a>
                        </div>
                    </div>

                    @if($vouchers->count() > 0)
                    <div class="table-responsive">
                        <table class="table voucher-table">
                            <thead>
                                <tr>
                                    <th>Voucher No</th>
                                    <th>Type</th>
                                    <th>Party</th>
                                    <th>Amount</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($vouchers as $voucher)
                                <tr>
                                    <td>{{ $voucher->voucher_no }}</td>
                                    <td>
                                        <span class="badge {{ $voucher->voucher_type == 'receipt' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($voucher->voucher_type) }}
                                        </span>
                                    </td>
                                    <td>{{ $voucher->party_name }}</td>
                                    <td>PKR {{ number_format($voucher->debit_amount + $voucher->credit_amount) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($voucher->created_at)->format('h:i A') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No vouchers for today</h5>
                        <p class="text-muted">All transactions will appear here as they are created</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        $(document).ready(function() {
            $('#closingForm').on('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(this);

                $.ajax({
                    url: '{{ route("journal-voucher.close-day") }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.success) {
                            Swal.fire({
                                title: 'Day Closed Successfully!',
                                text: 'Closing balance: PKR ' + response.closing_balance.toLocaleString() +
                                      (response.variance != 0 ? '\nVariance: PKR ' + response.variance.toLocaleString() : ''),
                                icon: 'success',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                location.reload();
                            });
                        }
                    },
                    error: function(xhr) {
                        const error = xhr.responseJSON;
                        Swal.fire({
                            title: 'Error!',
                            text: error.message || 'Failed to close day',
                            icon: 'error'
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
