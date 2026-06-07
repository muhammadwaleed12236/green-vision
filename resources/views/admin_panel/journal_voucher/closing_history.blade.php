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

    .filter-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border: 1px solid #E5E7EB;
        margin-bottom: 20px;
    }

    .history-table th {
        background: var(--primary-blue);
        color: white;
        font-weight: 600;
        border: none;
        padding: 12px 15px;
    }

    .history-table tbody tr:hover {
        background-color: var(--gray-50);
    }

    .variance-badge {
        font-size: 12px;
        padding: 4px 8px;
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
                        <div class="col-md-8">
                            <h3 class="mb-0"><i class="fas fa-history me-2"></i>Daily Closing History</h3>
                            <p class="mb-0 opacity-75">Complete record of all daily closings</p>
                        </div>
                        <div class="col-md-4 text-end">
                            <a href="{{ route('journal-voucher.daily-closing') }}" class="btn btn-light">
                                <i class="fas fa-calculator me-1"></i>Daily Closing
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="filter-card">
                    <form method="GET" action="{{ route('journal-voucher.closing-history') }}">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">From Date</label>
                                <input type="date" class="form-control" name="from_date" value="{{ request('from_date') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">To Date</label>
                                <input type="date" class="form-control" name="to_date" value="{{ request('to_date') }}">
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-filter me-1"></i>Filter
                                </button>
                                <a href="{{ route('journal-voucher.closing-history') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times me-1"></i>Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Closing History Table -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2"></i>Closing Records
                            <span class="badge bg-primary">{{ $closings->total() }} total</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if($closings->count() > 0)
                        <div class="table-responsive">
                            <table class="table history-table mb-0">
                                <thead>
                                    <tr>
                                        <th width="5%"></th>
                                        <th>Date</th>
                                        <th>Opening</th>
                                        <th>Receipts</th>
                                        <th>Payments</th>
                                        <th>Expected</th>
                                        <th>Actual Cash</th>
                                        <th>Variance</th>
                                        <th>Jobs</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($closings as $closing)
                                    <tr class="clickable-row" data-toggle="row-{{ $closing->id }}">
                                        <td>
                                            <i class="fas fa-plus-circle text-primary expand-icon" style="cursor: pointer;"></i>
                                        </td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($closing->closing_date)->format('d M Y') }}</strong>
                                            <br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($closing->closing_date)->format('l') }}</small>
                                        </td>
                                        <td>PKR {{ number_format($closing->opening_balance) }}</td>
                                        <td class="text-success">PKR {{ number_format($closing->total_receipts) }}</td>
                                        <td class="text-danger">PKR {{ number_format($closing->total_payments) }}</td>
                                        <td>PKR {{ number_format($closing->calculated_closing) }}</td>
                                        <td class="fw-bold">PKR {{ number_format($closing->actual_cash_in_hand) }}</td>
                                        <td>
                                            @if($closing->variance == 0)
                                                <span class="badge bg-success variance-badge">Perfect</span>
                                            @elseif($closing->variance > 0)
                                                <span class="badge bg-warning variance-badge">+PKR {{ number_format(abs($closing->variance)) }}</span>
                                            @else
                                                <span class="badge bg-danger variance-badge">-PKR {{ number_format(abs($closing->variance)) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-info">{{ $closing->total_jobs ?? 0 }}</span>
                                            <br>
                                            <small class="text-muted">{{ $closing->assigned_jobs ?? 0 }} assigned</small>
                                        </td>
                                        <td>
                                            @if($closing->remarks)
                                                <button class="btn btn-outline-info btn-sm" data-bs-toggle="tooltip"
                                                        title="{{ $closing->remarks }}">
                                                    <i class="fas fa-comment"></i>
                                                </button>
                                            @endif

                                            @php
                                            $canReopen = !DB::table('daily_closings')
                                                ->where('admin_or_user_id', auth()->id())
                                                ->where('closing_date', '>', $closing->closing_date)
                                                ->exists();
                                            @endphp

                                            @if($canReopen && \Carbon\Carbon::parse($closing->closing_date)->isToday())
                                            <button class="btn btn-outline-warning btn-sm" onclick="reopenDay('{{ $closing->closing_date }}')">
                                                <i class="fas fa-unlock"></i>
                                            </button>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- Expandable Business Details Row -->
                                    <tr class="expandable-row" id="row-{{ $closing->id }}" style="display: none;">
                                        <td colspan="10">
                                            <div class="p-3" style="background: #f8f9fa; border-left: 4px solid #007bff;">
                                                <h6 class="text-primary mb-3">
                                                    <i class="fas fa-chart-bar me-2"></i>Business Summary for {{ \Carbon\Carbon::parse($closing->closing_date)->format('d M Y') }}
                                                </h6>

                                                <div class="row">
                                                    <!-- Jobs Summary -->
                                                    <div class="col-md-4">
                                                        <div class="border rounded p-2 bg-white">
                                                            <h6 class="text-info mb-2"><i class="fas fa-briefcase me-1"></i>Jobs</h6>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <small class="text-muted">Total Jobs:</small><br>
                                                                    <strong>{{ $closing->total_jobs ?? 0 }}</strong>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted">Job Amount:</small><br>
                                                                    <strong class="text-success">{{ number_format($closing->total_job_amount ?? 0) }}</strong>
                                                                </div>
                                                            </div>
                                                            <hr class="my-1">
                                                            <small class="text-muted">Assigned:</small> <strong class="text-primary">{{ $closing->assigned_jobs ?? 0 }}</strong>
                                                        </div>
                                                    </div>

                                                    <!-- Payments Summary -->
                                                    <div class="col-md-4">
                                                        <div class="border rounded p-2 bg-white">
                                                            <h6 class="text-danger mb-2"><i class="fas fa-money-bill me-1"></i>Payments</h6>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <small class="text-muted">Contractor:</small><br>
                                                                    <strong>{{ number_format($closing->contractor_payments ?? 0) }}</strong>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted">Vendor:</small><br>
                                                                    <strong>{{ number_format($closing->vendor_payments ?? 0) }}</strong>
                                                                </div>
                                                            </div>
                                                            <div class="row">
                                                                <div class="col-6">
                                                                    <small class="text-muted">Expense:</small><br>
                                                                    <strong>{{ number_format($closing->expense_payments ?? 0) }}</strong>
                                                                </div>
                                                                <div class="col-6">
                                                                    <small class="text-muted">Staff:</small><br>
                                                                    <strong>{{ number_format($closing->staff_payments ?? 0) }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- Receipts Summary -->
                                                    <div class="col-md-4">
                                                        <div class="border rounded p-2 bg-white">
                                                            <h6 class="text-success mb-2"><i class="fas fa-hand-holding-usd me-1"></i>Receipts</h6>
                                                            <div class="text-center">
                                                                <small class="text-muted">Customer Recovery:</small><br>
                                                                <h5 class="text-success mb-1">{{ number_format($closing->customer_recoveries ?? 0) }}</h5>
                                                            </div>
                                                            <hr class="my-1">
                                                            <div class="row">
                                                                <div class="col-6 text-center">
                                                                    <small class="text-muted">Total In:</small><br>
                                                                    <strong class="text-success">{{ number_format($closing->customer_recoveries ?? 0) }}</strong>
                                                                </div>
                                                                <div class="col-6 text-center">
                                                                    <small class="text-muted">Total Out:</small><br>
                                                                    <strong class="text-danger">{{ number_format(($closing->contractor_payments ?? 0) + ($closing->vendor_payments ?? 0) + ($closing->expense_payments ?? 0) + ($closing->staff_payments ?? 0)) }}</strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="row mt-2">
                                                    <div class="col-md-12">
                                                        <div class="border rounded p-2 bg-light">
                                                            <div class="row">
                                                                <div class="col-md-8">
                                                                    <small class="text-muted">
                                                                        <strong>Summary:</strong>
                                                                        {{ $closing->vouchers_count }} vouchers |
                                                                        Closed: {{ \Carbon\Carbon::parse($closing->closed_at)->format('d M Y h:i A') }}
                                                                        @if($closing->remarks)
                                                                        | <em>{{ $closing->remarks }}</em>
                                                                        @endif
                                                                    </small>
                                                                </div>
                                                                <div class="col-md-4 text-end">
                                                                    <strong class="text-primary">
                                                                        Net Cash: PKR {{ number_format(($closing->customer_recoveries ?? 0) - (($closing->contractor_payments ?? 0) + ($closing->vendor_payments ?? 0) + ($closing->expense_payments ?? 0) + ($closing->staff_payments ?? 0))) }}
                                                                    </strong>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div class="p-3">
                            {{ $closings->withQueryString()->links() }}
                        </div>

                        @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No closing records found</h5>
                            <p class="text-muted">Daily closing records will appear here once you start closing days</p>
                            <a href="{{ route('journal-voucher.daily-closing') }}" class="btn btn-primary">
                                <i class="fas fa-calculator me-1"></i>Go to Daily Closing
                            </a>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Summary Cards -->
                @if($closings->count() > 0)
                <div class="row mt-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-primary">{{ $closings->count() }}</h4>
                                <p class="text-muted mb-0">Days Closed</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-success">PKR {{ number_format($closings->sum('total_receipts')) }}</h4>
                                <p class="text-muted mb-0">Total Receipts</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <h4 class="text-danger">PKR {{ number_format($closings->sum('total_payments')) }}</h4>
                                <p class="text-muted mb-0">Total Payments</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                @php $totalVariance = $closings->sum('variance'); @endphp
                                <h4 class="{{ $totalVariance >= 0 ? 'text-success' : 'text-danger' }}">
                                    PKR {{ number_format(abs($totalVariance)) }}
                                </h4>
                                <p class="text-muted mb-0">Total Variance</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    @include('admin_panel.include.footer_include')

    <script>
        // Initialize tooltips
        $(document).ready(function() {
            $('[data-bs-toggle="tooltip"]').tooltip();

            // Handle row expansion
            $('.clickable-row').click(function(e) {
                // Don't expand if clicking on buttons
                if ($(e.target).closest('button').length > 0) {
                    return;
                }

                const rowId = $(this).data('toggle');
                const expandableRow = $('#' + rowId);
                const expandIcon = $(this).find('.expand-icon');

                if (expandableRow.is(':visible')) {
                    expandableRow.fadeOut();
                    expandIcon.removeClass('fa-minus-circle').addClass('fa-plus-circle');
                } else {
                    expandableRow.fadeIn();
                    expandIcon.removeClass('fa-plus-circle').addClass('fa-minus-circle');
                }
            });
        });

        // Reopen day function
        function reopenDay(date) {
            Swal.fire({
                title: 'Reopen Day?',
                text: 'Are you sure you want to reopen ' + date + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Reopen',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("journal-voucher.reopen-day") }}',
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            date: date
                        },
                        success: function(response) {
                            if(response.success) {
                                Swal.fire({
                                    title: 'Day Reopened!',
                                    text: response.message,
                                    icon: 'success'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            const error = xhr.responseJSON;
                            Swal.fire({
                                title: 'Error!',
                                text: error.message || 'Failed to reopen day',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        }
    </script>
</body>
</html>
