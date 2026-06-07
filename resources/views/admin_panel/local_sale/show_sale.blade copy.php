@include('admin_panel.include.header_include')

<style>
    .info-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    .info-card:hover {
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    .table-hover tbody tr:hover {
        background-color: #f8f9fa;
    }
    .job-card {
        border-radius: 10px;
        border: 1px solid #e0e0e0;
        transition: all 0.3s ease;
    }
    .job-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-3px);
    }
    .amount-highlight {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 20px;
        border-radius: 10px;
    }
    .worker-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: bold;
    }
</style>

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

{{-- ================= HEADER WITH STATUS ================= --}}
<div class="page-header mb-4">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h3 class="mb-1"><i class="fa fa-file-invoice me-2 text-primary"></i>Job Order Details</h3>
            <div class="d-flex align-items-center gap-2 mt-2">
                <span class="badge bg-secondary">{{ $sale->invoice_number }}</span>
                <span class="badge
                    @if($sale->job_status === 'pending') bg-warning text-dark
                    @elseif($sale->job_status === 'ready') bg-success
                    @elseif($sale->job_status === 'completed') bg-primary
                    @else bg-secondary
                    @endif">
                    {{ ucfirst($sale->job_status ?? 'pending') }}
                </span>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('local.sale.invoice', $sale->id) }}" class="btn btn-secondary">
                <i class="fa fa-print me-1"></i> Print Invoice
            </a>
            <a href="{{ route('local.sale.edit', $sale->id) }}" class="btn btn-warning">
                <i class="fa fa-edit me-1"></i> Edit
            </a>
            <a href="{{ route('all-local-sale') }}" class="btn btn-dark">
                <i class="fa fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>
</div>

<div class="row">
    {{-- ================= LEFT COLUMN ================= --}}
    <div class="col-lg-8">

        {{-- PARTY INFORMATION --}}
        <div class="card info-card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fa fa-user-circle me-2"></i>Party Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-calendar text-primary me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Date</small>
                                <strong>{{ \Carbon\Carbon::parse($sale->sale_date)->format('d M, Y') }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-tag text-primary me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Party Type</small>
                                <strong>{{ ucfirst($sale->party_type) }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-store text-primary me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Party Name</small>
                                <strong>
                                    @if($sale->party_type === 'customer' && $sale->customer)
                                        {{ $sale->customer->customer_name ?? $sale->customer->shop_name }}
                                    @elseif($sale->party_type === 'vendor' && $sale->vendor)
                                        {{ $sale->vendor->Party_name }}
                                    @else
                                        {{ $sale->customer_shopname ?? 'Walk-in Customer' }}
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-phone text-primary me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Phone</small>
                                <strong>
                                    @if($sale->party_type === 'customer' && $sale->customer)
                                        {{ $sale->customer->phone_number ?? '-' }}
                                    @elseif($sale->party_type === 'vendor' && $sale->vendor)
                                        {{ $sale->vendor->Party_phone ?? '-' }}
                                    @else
                                        {{ $sale->customer_phone ?? '-' }}
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="d-flex align-items-start">
                            <i class="fa fa-map-marker-alt text-primary me-2 mt-1"></i>
                            <div>
                                <small class="text-muted d-block">Address</small>
                                <strong>
                                    @if($sale->party_type === 'customer' && $sale->customer)
                                        {{ $sale->customer->address ?? '-' }}
                                    @elseif($sale->party_type === 'vendor' && $sale->vendor)
                                        {{ $sale->vendor->Party_address ?? '-' }}
                                    @else
                                        {{ $sale->customer_address ?? '-' }}
                                    @endif
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- JOB ITEMS --}}
        @php
            $items   = json_decode($sale->item, true) ?? [];
            $heights = json_decode($sale->height, true) ?? [];
            $widths  = json_decode($sale->width, true) ?? [];
            $units   = json_decode($sale->unit, true) ?? [];
            $qtys    = json_decode($sale->qty, true) ?? [];
            $amounts = json_decode($sale->amount, true) ?? [];
        @endphp

        <div class="card info-card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fa fa-list-alt me-2"></i>Job Items</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">#</th>
                            <th>Item</th>
                            <th class="text-center">Height</th>
                            <th class="text-center">Width</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($items as $i => $item)
                        <tr>
                            <td class="text-muted">{{ $i + 1 }}</td>
                            <td><strong>{{ $item }}</strong></td>
                            <td class="text-center">{{ $heights[$i] ?? '-' }}</td>
                            <td class="text-center">{{ $widths[$i] ?? '-' }}</td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ strtoupper($units[$i] ?? '-') }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary">{{ $qtys[$i] ?? 1 }}</span>
                            </td>
                            <td class="text-end"><strong>Rs. {{ number_format($amounts[$i] ?? 0, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="fa fa-inbox fa-2x mb-2 d-block"></i>
                                No Items Found
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- JOB ASSIGNMENTS --}}
        <div class="card info-card mb-4">
            <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-users me-2"></i>Job Assignments</h5>
                <a href="{{ route('job-assignments') }}" class="btn btn-sm btn-dark">
                    <i class="fa fa-tasks me-1"></i> Manage All
                </a>
            </div>
            <div class="card-body">
                @if($jobOrders->count() > 0)
                    <div class="row g-3">
                        @foreach($jobOrders as $job)
                            <div class="col-md-6">
                                <div class="job-card p-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="worker-avatar me-3">
                                            @if($job->assignee_type === 'salesman' && $job->salesman)
                                                {{ strtoupper(substr($job->salesman->staff_name, 0, 1)) }}
                                            @elseif($job->assignee_type === 'contractor' && $job->contractor)
                                                {{ strtoupper(substr($job->contractor->contractor_name, 0, 1)) }}
                                            @else
                                                ?
                                            @endif
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="mb-0">
                                                @if($job->assignee_type === 'salesman' && $job->salesman)
                                                    {{ $job->salesman->staff_name }}
                                                @elseif($job->assignee_type === 'contractor' && $job->contractor)
                                                    {{ $job->contractor->contractor_name }}
                                                @else
                                                    Unknown Worker
                                                @endif
                                            </h6>
                                            <small class="text-muted">{{ ucfirst($job->assignee_type ?? 'N/A') }}</small>
                                        </div>
                                        <span class="badge
                                            @if($job->assignment_status === 'pending') bg-warning text-dark
                                            @elseif($job->assignment_status === 'in_progress') bg-info
                                            @elseif($job->assignment_status === 'completed') bg-success
                                            @else bg-secondary
                                            @endif">
                                            {{ ucfirst(str_replace('_', ' ', $job->assignment_status ?? 'pending')) }}
                                        </span>
                                    </div>

                                    <div class="row text-center">
                                        <div class="col-6 border-end">
                                            <small class="text-muted d-block">Amount</small>
                                            <strong class="text-success">Rs. {{ number_format($job->total_amount, 2) }}</strong>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted d-block">Paid</small>
                                            <strong class="text-primary">Rs. {{ number_format($job->paid_amount, 2) }}</strong>
                                        </div>
                                    </div>

                                    @if($job->expected_return_date)
                                        <div class="mt-2 pt-2 border-top">
                                            <small class="text-muted">
                                                <i class="fa fa-clock me-1"></i>
                                                Expected: {{ \Carbon\Carbon::parse($job->expected_return_date)->format('d M, Y') }}
                                            </small>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fa fa-user-plus fa-3x mb-3 d-block opacity-50"></i>
                        <p class="mb-0">No workers assigned yet</p>
                        <small>Click "Manage All" to assign workers to this job</small>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- ================= RIGHT COLUMN (SUMMARY) ================= --}}
    <div class="col-lg-4">

        {{-- PAYMENT SUMMARY --}}
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fa fa-calculator me-2"></i>Payment Summary</h5>
            </div>
            <div class="card-body">
                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Grand Total:</span>
                        <strong>Rs. {{ number_format($sale->grand_total, 2) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Discount:</span>
                        <span class="text-danger">- Rs. {{ number_format($sale->discount_value, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Net Amount:</span>
                        <strong class="text-primary">Rs. {{ number_format($sale->net_amount, 2) }}</strong>
                    </div>
                </div>

                <div class="mb-3 pb-3 border-bottom">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Advance Paid:</span>
                        <span class="text-success">Rs. {{ number_format($sale->advance_amount ?? 0, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Remaining:</span>
                        <strong class="text-warning">Rs. {{ number_format($sale->remaining_amount ?? 0, 2) }}</strong>
                    </div>
                </div>

                <div class="amount-highlight text-center">
                    <small class="d-block mb-1 opacity-75">Balance Due</small>
                    <h3 class="mb-0">Rs. {{ number_format($sale->remaining_amount ?? 0, 2) }}</h3>
                </div>
            </div>
        </div>

        {{-- QUICK STATS --}}
        <div class="card">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0"><i class="fa fa-chart-bar me-2"></i>Quick Stats</h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-list text-primary me-2"></i>Total Items:</span>
                        <span class="badge bg-primary">{{ count($items) }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-users text-success me-2"></i>Workers Assigned:</span>
                        <span class="badge bg-success">{{ $jobOrders->count() }}</span>
                    </div>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-check-circle text-info me-2"></i>Completed Jobs:</span>
                        <span class="badge bg-info">{{ $jobOrders->where('assignment_status', 'completed')->count() }}</span>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fa fa-spinner text-warning me-2"></i>Pending Jobs:</span>
                        <span class="badge bg-warning">{{ $jobOrders->whereIn('assignment_status', ['pending', 'in_progress'])->count() }}</span>
                    </div>
                </div>

                @php
                    $progress = $jobOrders->count() > 0
                        ? ($jobOrders->where('assignment_status', 'completed')->count() / $jobOrders->count()) * 100
                        : 0;
                @endphp
                <div class="mt-3 pt-3 border-top">
                    <small class="text-muted d-block mb-2">Job Progress</small>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-success" role="progressbar"
                             style="width: {{ $progress }}%;"
                             aria-valuenow="{{ $progress }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ round($progress) }}%
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

</div>
</div>
</div>

@include('admin_panel.include.footer_include')
