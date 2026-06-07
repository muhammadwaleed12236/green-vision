@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <div class="page-header">
                <div class="page-title">
                    <h4>Job Order Detail</h4>
                    <h6>Job No: {{ $job->job_order_number }}</h6>
                </div>
                <div>
                    <a href="{{ route('job-orders.index') }}" class="btn btn-secondary btn-sm">
                        ← Back
                    </a>
                </div>
            </div>

            <!-- JOB SUMMARY -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="fw-semibold">Job Date</label>
                           <div>{{ \Carbon\Carbon::parse($job->order_date)->format('d-M-Y') }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="fw-semibold">Contractor / Staff</label>
                            <div>
                                @if($job->staff_type === 'contract')
                                    <span class="text-warning fw-bold">{{ $job->contractor->contractor_name ?? 'N/A' }}</span>
                                    <div class="small text-muted">{{ $job->contractor->phone_number ?? '' }}</div>
                                @else
                                    <span class="text-success fw-bold">In-House</span>
                                @endif
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="fw-semibold">Total Amount</label>
                            <div>{{ number_format($job->total_amount) }}</div>
                        </div>

                        <div class="col-md-3 text-success">
                            <label class="fw-semibold">Paid</label>
                            <div>{{ number_format($job->paid_amount) }}</div>
                        </div>

                        <div class="col-md-3 text-danger">
                            <label class="fw-semibold">Remaining</label>
                            <div>{{ number_format($job->remaining_amount) }}</div>
                        </div>

                        <div class="col-md-3">
                            <label class="fw-semibold">Status</label>
                            <div>
                                <span class="badge bg-{{ $job->status === 'completed' ? 'success' : 'warning' }}">
                                    {{$job->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @foreach($jobItems as $workType => $items)
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-semibold">
                        Work Type: {{ $workType }}
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Item</th>
                                    <th class="text-center">Qty</th>
                                    <th class="text-end">Rate</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $subTotal = 0; @endphp
                                @foreach($items as $k => $item)
                                    @php $subTotal += $item->total; @endphp
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $item->item_name }}</td>
                                        <td class="text-center">{{ number_format($item->qty) }}</td>
                                        <td class="text-end">{{ number_format($item->rate) }}</td>
                                        <td class="text-end">{{ number_format($item->total) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="4" class="text-end">Sub Total</th>
                                    <th class="text-end">{{ number_format($subTotal) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
