@include('admin_panel.include.header_include')

<style>
    /* Premium Animations */
    @keyframes fadeInSlide {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-fade-in {
        animation: fadeInSlide 0.4s ease-out forwards;
    }

    .assignment-card {
        border: none;
        border-radius: 12px;
        transition: all 0.3s ease;
        background: #fff;
    }

    .assignment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
    }

    /* Type Specific Styling */
    .assignment-card.labour-type {
        border-left: 5px solid #28c76f;
        background: linear-gradient(to right, #f0fdf4, #ffffff);
    }

    .assignment-card.contract-type {
        border-left: 5px solid #ff9f43;
        background: linear-gradient(to right, #fff8f1, #ffffff);
    }

    .assignment-card.vendor-type {
        border-left: 5px solid #00cfe8;
        background: linear-gradient(to right, #e0f7fa, #ffffff);
    }

    /* Table Styling */
    .custom-table th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        background: #f8f9fa;
        color: #6c757d;
        border-bottom: 2px solid #eaedf1;
    }

    .custom-table td {
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .form-control-sm {
        border-radius: 6px;
        border: 1px solid #e0e0e0;
        padding: 6px 10px;
    }

    .form-control-sm:focus {
        border-color: #7367f0;
        box-shadow: 0 0 0 3px rgba(115, 103, 240, 0.1);
    }

    .btn-add-manual {
        font-size: 0.8rem;
        font-weight: 500;
        border-style: dashed;
        color: #7367f0;
        border-color: #7367f0;
    }

    .btn-add-manual:hover {
        background: rgba(115, 103, 240, 0.05);
    }

    /* ===== GROUPED ASSIGNEE VIEW STYLES ===== */
    .assignee-group-card {
        border-radius: 14px;
        border: none;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
        transition: all 0.25s ease;
        overflow: hidden;
    }
    .assignee-group-card:hover {
        box-shadow: 0 6px 24px rgba(0,0,0,0.12);
        transform: translateY(-1px);
    }
    .assignee-group-header {
        cursor: pointer;
        padding: 16px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        user-select: none;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        transition: background 0.2s;
    }
    .assignee-group-header:hover {
        background: rgba(0,0,0,0.02);
    }
    .assignee-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .assignee-stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.78rem;
        font-weight: 600;
    }
    .group-jobs-table {
        font-size: 0.85rem;
    }
    .group-jobs-table thead th {
        background: #f8f9fb;
        font-size: 0.73rem;
        text-transform: uppercase;
        letter-spacing: 0.4px;
        color: #888;
        font-weight: 700;
        border: none;
        padding: 10px 14px;
    }
    .group-jobs-table td {
        vertical-align: middle;
        padding: 10px 14px;
        border-color: #f0f0f0;
    }
    .group-collapse {
        display: none;
    }
    .group-collapse.show {
        display: block;
        animation: fadeInSlide 0.3s ease-out;
    }
    .chevron-icon {
        transition: transform 0.3s ease;
        color: #adb5bd;
    }
    .assignee-group-header[aria-expanded="true"] .chevron-icon {
        transform: rotate(180deg);
    }
    .view-toggle-btn {
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.85rem;
        transition: all 0.2s;
    }
    .view-toggle-btn.active {
        background: #7367f0;
        color: #fff;
        border-color: #7367f0;
    }
    .progress-thin {
        height: 5px;
        border-radius: 10px;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Job Order List</h4>
                    <h6>Manage Job Orders</h6>
                </div>
                <div class="page-btn d-flex gap-2">
                    <!-- View Toggle Buttons -->
                    <div class="btn-group me-2" role="group">
                        <button id="btnTableView" class="btn btn-outline-secondary view-toggle-btn active" onclick="switchView('table')" title="Table View">
                            <i class="fa fa-list me-1"></i> Table View
                        </button>
                        <button id="btnGroupView" class="btn btn-outline-secondary view-toggle-btn" onclick="switchView('group')" title="Grouped by Assignee">
                            <i class="fa fa-layer-group me-1"></i> By Person
                        </button>
                        <button id="btnJobView" class="btn btn-outline-secondary view-toggle-btn" onclick="switchView('job')" title="Job-wise View">
                            <i class="fa fa-project-diagram me-1"></i> By Job
                        </button>
                    </div>
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addJobModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Job Order
                    </button>
                </div>
            </div>

            <!-- Search Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('job-orders.index') }}" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Search Order</label>
                            <input type="text" name="q" class="form-control" placeholder="Search by Invoice #, Job #, or Customer/Vendor name..." value="{{ request('q') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search me-2"></i>Search
                            </button>
                            @if(request('q'))
                                <a href="{{ route('job-orders.index') }}" class="btn btn-secondary">
                                    <i class="fa fa-times me-2"></i>Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- GROUPED BY ASSIGNEE VIEW (hidden by default) --}}
            {{-- ============================================================ --}}
            <div id="groupedView" style="display:none;">
                @if(count($groupedByAssignee) === 0)
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="fa fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Koi job order nahi mila.</p>
                        </div>
                    </div>
                @else
                    {{-- Summary row at top --}}
                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <div class="alert alert-light border d-flex align-items-center gap-2 mb-0" style="border-radius:12px;">
                                <i class="fa fa-info-circle text-primary"></i>
                                <span class="fw-semibold">Total {{ count($groupedByAssignee) }} Assignees |</span>
                                <span>{{ $jobOrders->count() }} Jobs Total</span>
                                <span class="ms-3"><i class="fa fa-circle text-warning me-1" style="font-size:8px"></i>Pending: {{ $jobOrders->where('status','pending')->count() }}</span>
                                <span class="ms-2"><i class="fa fa-circle text-success me-1" style="font-size:8px"></i>Completed: {{ $jobOrders->where('status','completed')->count() }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                    @foreach($groupedByAssignee as $groupKey => $group)
                        @php
                            $totalJobs      = count($group['jobs']);
                            $completedCount = $group['completed_count'];
                            $pendingCount   = $group['pending_count'];
                            $progress       = $totalJobs > 0 ? round(($completedCount / $totalJobs) * 100) : 0;
                            $colorMap = [
                                'warning' => ['bg' => '#fff8f1', 'icon_bg' => '#ff9f43', 'text' => '#ff9f43'],
                                'info'    => ['bg' => '#e8f9fd', 'icon_bg' => '#00cfe8', 'text' => '#00cfe8'],
                                'success' => ['bg' => '#f0fdf4', 'icon_bg' => '#28c76f', 'text' => '#28c76f'],
                            ];
                            $cm = $colorMap[$group['color']] ?? $colorMap['success'];
                        @endphp
                        <div class="col-12">
                            <div class="assignee-group-card card" style="background: {{ $cm['bg'] }};">
                                {{-- Header - Clickable --}}
                                <div class="assignee-group-header"
                                     onclick="toggleGroup('{{ $groupKey }}')"
                                     id="hdr-{{ $groupKey }}"
                                     aria-expanded="false">
                                    <div class="d-flex align-items-center gap-3">
                                        {{-- Icon --}}
                                        <div class="assignee-icon-wrap" style="background: {{ $cm['icon_bg'] }}22; color: {{ $cm['icon_bg'] }};">
                                            <i class="fa {{ $group['icon'] }}"></i>
                                        </div>
                                        {{-- Name & Type --}}
                                        <div>
                                            <div class="fw-bold fs-6 text-dark">{{ $group['name'] }}</div>
                                            <span class="badge" style="background:{{ $cm['icon_bg'] }}22; color:{{ $cm['icon_bg'] }}; font-size:0.72rem;">
                                                {{ $group['type'] }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Stats --}}
                                    <div class="d-flex align-items-center gap-3 flex-wrap">
                                        <span class="assignee-stat-pill" style="background:#f0f0f5; color:#555;">
                                            <i class="fa fa-file-alt" style="color:{{ $cm['icon_bg'] }}"></i>
                                            {{ $totalJobs }} Jobs
                                        </span>
                                        <span class="assignee-stat-pill" style="background:#fff8e1; color:#e6a817;">
                                            <i class="fa fa-clock"></i>
                                            {{ $pendingCount }} Pending
                                        </span>
                                        <span class="assignee-stat-pill" style="background:#e6f9f0; color:#28c76f;">
                                            <i class="fa fa-check-circle"></i>
                                            {{ $completedCount }} Done
                                        </span>
                                        <span class="assignee-stat-pill" style="background:#f0f0ff; color:#7367f0;">
                                            <i class="fa fa-rupee-sign"></i>
                                            Total: {{ number_format($group['total_amount']) }}
                                        </span>
                                        <span class="assignee-stat-pill" style="background:#fff0f0; color:#ea5455;">
                                            <i class="fa fa-exclamation-circle"></i>
                                            Baki: {{ number_format($group['remaining_amount']) }}
                                        </span>
                                        {{-- Progress --}}
                                        <div style="width:80px;">
                                            <div class="progress progress-thin" title="{{ $progress }}% Completed">
                                                <div class="progress-bar" style="width:{{ $progress }}%; background:{{ $cm['icon_bg'] }};" role="progressbar"></div>
                                            </div>
                                            <div class="text-center" style="font-size:0.7rem; color:#999; margin-top:2px;">{{ $progress }}%</div>
                                        </div>
                                        <i class="fa fa-chevron-down chevron-icon ms-2"></i>
                                    </div>
                                </div>

                                {{-- Collapsible Job List --}}
                                <div class="group-collapse" id="grp-{{ $groupKey }}">
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table group-jobs-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>Job No</th>
                                                        <th>Invoice No</th>
                                                        <th>Party (Customer/Vendor)</th>
                                                        <th>Items</th>
                                                        <th>Date</th>
                                                        <th>Total</th>
                                                        <th>Paid</th>
                                                        <th>Baki</th>
                                                        <th>Status</th>
                                                        <th>Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                @foreach($group['jobs'] as $gIdx => $job)
                                                    @php
                                                        $jobItems = json_decode($job->items_json, true) ?? [];
                                                        $itemNames = collect($jobItems)->pluck('name')->implode(', ');
                                                    @endphp
                                                    <tr>
                                                        <td class="text-muted">{{ $gIdx + 1 }}</td>
                                                        <td>
                                                            <strong class="text-primary">{{ $job->job_order_number }}</strong>
                                                        </td>
                                                        <td>
                                                            @if($job->sale)
                                                                <a href="{{ route('show-local-sale', $job->sale_id) }}" class="text-decoration-none">
                                                                    {{ $job->sale->invoice_number }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($job->sale)
                                                                @if($job->sale->party_type === 'vendor' && $job->sale->vendor)
                                                                    <span class="badge bg-info">Vendor</span>
                                                                    {{ $job->sale->vendor->Party_name }}
                                                                @elseif($job->sale->party_type === 'customer' && $job->sale->customer)
                                                                    <span class="badge bg-primary">Customer</span>
                                                                    {{ $job->sale->customer->customer_name ?? $job->sale->customer->shop_name }}
                                                                @else
                                                                    <span class="badge bg-secondary">Walk-in</span>
                                                                    {{ $job->sale->customer_shopname ?? 'Walk-in' }}
                                                                @endif
                                                            @else
                                                                <span class="text-muted">N/A</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($itemNames)
                                                                <small class="text-muted">{{ Str::limit($itemNames, 40) }}</small>
                                                            @else
                                                                <small class="text-muted">-</small>
                                                            @endif
                                                        </td>
                                                        <td class="text-nowrap">{{ \Carbon\Carbon::parse($job->order_date)->format('d-m-Y') }}</td>
                                                        <td class="fw-semibold">{{ number_format($job->total_amount) }}</td>
                                                        <td class="text-success fw-semibold">{{ number_format($job->paid_amount) }}</td>
                                                        <td class="text-danger fw-semibold">{{ number_format($job->remaining_amount) }}</td>
                                                        <td>
                                                            @if($job->status === 'completed')
                                                                <span class="badge bg-success"><i class="fa fa-check me-1"></i>Done</span>
                                                            @elseif($job->status === 'in_progress')
                                                                <span class="badge bg-info">In Progress</span>
                                                            @else
                                                                <span class="badge bg-warning text-dark"><i class="fa fa-clock me-1"></i>Pending</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            <a href="{{ route('job-orders.show', $job->id) }}"
                                                            data-id="{{ $job->id }}"
                                                            data-job-no="{{ $job->job_order_number }}"
                                                            data-date="{{ $job->order_date }}"
                                                            data-total="{{ $job->total_amount }}"
                                                            data-paid="{{ $job->paid_amount }}"
                                                            data-status="{{ $job->status }}"
                                                            data-party-type="{{ $job->sale->party_type ?? '' }}"
                                                            data-vendor-id="{{ $job->sale->vendor_id ?? '' }}"
                                                            data-customer-id="{{ $job->sale->customer_id ?? '' }}"
                                                            data-shop-name="{{ $job->sale->customer_shopname ?? '' }}"
                                                            data-phone="{{ $job->sale->customer_phone ?? '' }}"
                                                            data-address="{{ $job->sale->customer_address ?? '' }}"
                                                               class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size:0.78rem;"
                                                               title="View Details">
                                                                <i class="fa fa-eye me-1"></i>View
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                                </tbody>
                                                <tfoot style="background:#f8f9fb;">
                                                    <tr>
                                                        <td colspan="6" class="text-end fw-bold text-muted" style="font-size:0.8rem;">TOTAL</td>
                                                        <td class="fw-bold text-dark">{{ number_format($group['total_amount']) }}</td>
                                                        <td class="fw-bold text-success">{{ number_format($group['paid_amount']) }}</td>
                                                        <td class="fw-bold text-danger">{{ number_format($group['remaining_amount']) }}</td>
                                                        <td colspan="2"></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    </div>
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- JOB-WISE VIEW (By Job / Sale) - hidden by default --}}
            {{-- ============================================================ --}}
            <div id="jobView" style="display:none;">
                @if(count($groupedBySale) === 0)
                    <div class="card">
                        <div class="card-body text-center py-5 text-muted">
                            <i class="fa fa-inbox fa-3x mb-3 opacity-25"></i>
                            <p class="mb-0">Koi job order nahi mila.</p>
                        </div>
                    </div>
                @else
                    {{-- Top summary --}}
                    <div class="alert alert-light border d-flex align-items-center gap-3 mb-4" style="border-radius:12px;">
                        <i class="fa fa-project-diagram text-primary fa-lg"></i>
                        <div>
                            <span class="fw-bold">{{ count($groupedBySale) }} Orders</span> mein kaam assign hai &mdash;
                            <span class="text-warning fw-semibold"><i class="fa fa-clock me-1"></i>{{ $jobOrders->where('status','pending')->count() }} assignments pending</span> &nbsp;|&nbsp;
                            <span class="text-success fw-semibold"><i class="fa fa-check-circle me-1"></i>{{ $jobOrders->where('status','completed')->count() }} completed</span>
                        </div>
                    </div>

                    @foreach($groupedBySale as $saleKey => $saleGroup)
                    @php
                        $totalAssignees  = count($saleGroup['assignments']);
                        $doneCount       = $saleGroup['completed_count'];
                        $pendingCount    = $saleGroup['pending_count'];
                        $progress        = $totalAssignees > 0 ? round(($doneCount / $totalAssignees) * 100) : 0;
                    @endphp
                    <div class="card mb-3 border-0 shadow-sm" style="border-radius:14px; overflow:hidden;">
                        {{-- Card Header - Invoice & Party --}}
                        <div class="card-header d-flex align-items-center justify-content-between py-3 px-4"
                             style="background: linear-gradient(135deg,#7367f0,#9a8dff); cursor:pointer; border:none;"
                             onclick="toggleGroup('sale_{{ $saleKey }}')"
                             id="hdr-sale_{{ $saleKey }}"
                             aria-expanded="false">
                            <div class="d-flex align-items-center gap-3">
                                {{-- Invoice badge --}}
                                <div class="d-flex align-items-center justify-content-center"
                                     style="background:rgba(255,255,255,0.2); border-radius:10px; width:48px; height:48px;">
                                    <i class="fa fa-file-invoice text-white fa-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-white fs-6">{{ $saleGroup['invoice_no'] }}</div>
                                    <div class="text-white" style="font-size:0.82rem; opacity:0.85;">
                                        {{ $saleGroup['party_label'] }}
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                {{-- Assignees count - KEY INFO --}}
                                <div class="text-center" style="background:rgba(255,255,255,0.15); border-radius:10px; padding:6px 14px;">
                                    <div class="text-white fw-bold fs-5">{{ $totalAssignees }}</div>
                                    <div class="text-white" style="font-size:0.7rem; opacity:0.85;">Assignees</div>
                                </div>
                                <span class="badge bg-warning text-dark px-3 py-2" style="font-size:0.8rem; border-radius:8px;">
                                    <i class="fa fa-clock me-1"></i>{{ $pendingCount }} Pending
                                </span>
                                <span class="badge bg-success px-3 py-2" style="font-size:0.8rem; border-radius:8px;">
                                    <i class="fa fa-check me-1"></i>{{ $doneCount }} Done
                                </span>
                                <div style="width:70px;">
                                    <div class="progress" style="height:6px; border-radius:10px; background:rgba(255,255,255,0.3);">
                                        <div class="progress-bar bg-white" style="width:{{$progress}}%;" role="progressbar"></div>
                                    </div>
                                    <div class="text-white text-center" style="font-size:0.7rem; margin-top:3px;">{{ $progress }}%</div>
                                </div>
                                <i class="fa fa-chevron-down text-white chevron-icon"></i>
                            </div>
                        </div>

                        {{-- Collapsible: All assignments for this sale --}}
                        <div class="group-collapse" id="grp-sale_{{ $saleKey }}">
                            <div class="card-body p-0">
                                @foreach($saleGroup['assignments'] as $aIdx => $assign)
                                @php
                                    $job        = $assign['job'];
                                    $aType      = $assign['assignee_type'];
                                    $aName      = $assign['assignee_name'];
                                    $typeColors = [
                                        'contractor' => ['bg'=>'#fff8f1','border'=>'#ff9f43','badge'=>'warning','icon'=>'fa-briefcase','label'=>'Contractor'],
                                        'vendor'     => ['bg'=>'#e8f9fd','border'=>'#00cfe8','badge'=>'info','icon'=>'fa-truck','label'=>'Vendor'],
                                        'inhouse'    => ['bg'=>'#f0fdf4','border'=>'#28c76f','badge'=>'success','icon'=>'fa-user','label'=>'In-House'],
                                    ];
                                    $tc = $typeColors[$aType] ?? $typeColors['inhouse'];
                                    $jobItems = json_decode($job->items_json, true) ?? [];
                                @endphp
                                <div class="mx-3 mb-3 {{ $aIdx === 0 ? 'mt-3' : '' }} p-3 rounded-3"
                                     style="background: {{ $tc['bg'] }}; border-left: 4px solid {{ $tc['border'] }};">
                                    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
                                        {{-- Left: Who got the work --}}
                                        <div class="d-flex align-items-center gap-3">
                                            <div style="width:40px;height:40px;border-radius:10px;background:{{ $tc['border'] }}22;display:flex;align-items:center;justify-content:center;color:{{ $tc['border'] }};font-size:1.1rem; flex-shrink:0;">
                                                <i class="fa {{ $tc['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold" style="font-size:1rem; color:#2d3748;">
                                                    {{ $aName }}
                                                </div>
                                                <span class="badge bg-{{ $tc['badge'] }} me-2" style="font-size:0.72rem;">{{ $tc['label'] }}</span>
                                                <span class="text-muted" style="font-size:0.78rem;">Job: <strong>{{ $job->job_order_number }}</strong></span>
                                                <span class="ms-2 text-muted" style="font-size:0.78rem;">{{ \Carbon\Carbon::parse($job->order_date)->format('d-M-Y') }}</span>
                                            </div>
                                        </div>

                                        {{-- Right: Amounts & Status --}}
                                        <div class="d-flex align-items-center gap-3 flex-wrap">
                                            <div class="text-center">
                                                <div class="fw-bold text-dark">{{ number_format($job->total_amount) }}</div>
                                                <div class="text-muted" style="font-size:0.7rem;">Total Bill</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="fw-bold text-success">{{ number_format($job->paid_amount) }}</div>
                                                <div class="text-muted" style="font-size:0.7rem;">Paid</div>
                                            </div>
                                            <div class="text-center">
                                                <div class="fw-bold text-danger">{{ number_format($job->remaining_amount) }}</div>
                                                <div class="text-muted" style="font-size:0.7rem;">Baki</div>
                                            </div>
                                            @if($job->status === 'completed')
                                                <span class="badge bg-success px-3 py-2"><i class="fa fa-check me-1"></i>Done</span>
                                            @elseif($job->status === 'in_progress')
                                                <span class="badge bg-info px-3 py-2">In Progress</span>
                                            @else
                                                <span class="badge bg-warning text-dark px-3 py-2"><i class="fa fa-clock me-1"></i>Pending</span>
                                            @endif
                                            <a href="{{ route('job-orders.show', $job->id) }}"
                                               class="btn btn-sm btn-outline-primary py-1 px-3" style="font-size:0.78rem; border-radius:8px;">
                                                <i class="fa fa-eye me-1"></i>View
                                            </a>
                                        </div>
                                    </div>

                                    {{-- Items assigned to this person --}}
                                    @if(count($jobItems) > 0)
                                    <div class="mt-2 pt-2" style="border-top: 1px dashed {{ $tc['border'] }}88;">
                                        <div class="d-flex flex-wrap gap-2">
                                            @foreach($jobItems as $jItem)
                                            <span class="badge" style="background:{{ $tc['border'] }}18; color:{{ $tc['border'] }}; border:1px solid {{ $tc['border'] }}44; font-size:0.77rem; font-weight:600; padding:5px 10px; border-radius:20px;">
                                                {{ $jItem['name'] ?? 'Item' }} &times; {{ $jItem['qty'] ?? 0 }}
                                            </span>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif
                                </div>
                                @endforeach

                                {{-- Footer totals for this sale --}}
                                <div class="px-3 pb-3">
                                    <div class="d-flex justify-content-end gap-4 p-3 rounded-3" style="background:#f8f9fb;">
                                        <div class="text-center">
                                            <div class="fw-bold text-dark">{{ number_format($saleGroup['total_amount']) }}</div>
                                            <div class="text-muted" style="font-size:0.72rem;">Grand Total</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-bold text-success">{{ number_format($saleGroup['paid_amount']) }}</div>
                                            <div class="text-muted" style="font-size:0.72rem;">Total Paid</div>
                                        </div>
                                        <div class="text-center">
                                            <div class="fw-bold text-danger">{{ number_format($saleGroup['remaining_amount']) }}</div>
                                            <div class="text-muted" style="font-size:0.72rem;">Total Baki</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- ============================================================ --}}
            {{-- NORMAL TABLE VIEW --}}
            {{-- ============================================================ --}}
            <div id="tableView">
            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job No</th>
                                    <th>Invoice No</th>
                                    <th>Party</th>
                                    <th>Assigned To</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining</th>
                                    <th>Assignment Status</th>
                                    <th>Sale Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobOrders as $key => $job)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><strong class="text-primary">{{ $job->job_order_number }}</strong></td>
                                        <td>
                                            @if($job->sale)
                                                <a href="{{ route('show-local-sale', $job->sale_id) }}" class="text-decoration-none">
                                                    {{ $job->sale->invoice_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($job->sale)
                                                @if($job->sale->party_type === 'vendor' && $job->sale->vendor)
                                                    <span class="badge bg-info mb-2">Vendor</span><br>
                                                    <span class="fw-bold text-info">{{ $job->sale->vendor->Party_name }}</span>
                                                    <br><small class="text-muted">{{ $job->sale->vendor->Party_phone }}</small>
                                                @elseif($job->sale->party_type === 'customer' && $job->sale->customer)
                                                    <span class="badge bg-primary mb-2">Customer</span><br>
                                                    <span class="fw-bold">{{ $job->sale->customer->customer_name }}</span>
                                                    <br><small class="text-muted">{{ $job->sale->customer->shop_name }}</small>
                                                @elseif($job->sale->party_type === 'walkin' || !$job->sale->party_type)
                                                    <span class="badge bg-secondary mb-2">Walk-in</span><br>
                                                    <span class="fw-bold">{{ $job->sale->customer_shopname ?? 'Walk-in' }}</span>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($job->assignee_type === 'contractor')
                                                <span class="badge bg-warning mb-1">Contractor</span><br>
                                                <span class="fw-bold">{{ $job->contractor->contractor_name ?? 'N/A' }}</span>
                                            @elseif($job->assignee_type === 'vendor')
                                                <span class="badge bg-info mb-1">Vendor</span><br>
                                                <span class="fw-bold">{{ $job->vendor->Party_name ?? 'N/A' }}</span>
                                            @else
                                                <span class="badge bg-success">In-House</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($job->order_date)->format('d-m-Y') }}</td>
                                        <td>{{ number_format($job->total_amount) }}</td>
                                        <td class="text-success">{{ number_format($job->paid_amount) }}</td>
                                        <td class="text-danger">{{ number_format($job->remaining_amount) }}</td>
                                        <td>
                                            @if($job->assignment_status === 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="fa fa-clock me-1"></i>Pending
                                                </span>
                                            @elseif($job->assignment_status === 'in_progress')
                                                <span class="badge bg-info">
                                                    <i class="fa fa-sync me-1"></i>In Progress
                                                </span>
                                            @elseif($job->assignment_status === 'completed')
                                                <span class="badge bg-success">
                                                    <i class="fa fa-check-circle me-1"></i>Completed
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($job->sale)
                                                @if($job->sale->job_status === 'pending')
                                                    <span class="badge bg-secondary">
                                                        <i class="fa fa-clock me-1"></i>Pending
                                                    </span>
                                                @elseif($job->sale->job_status === 'ready')
                                                    <span class="badge bg-success">
                                                        <i class="fa fa-check me-1"></i>Ready
                                                    </span>
                                                @elseif($job->sale->job_status === 'completed')
                                                    <span class="badge bg-primary">
                                                        <i class="fa fa-check-double me-1"></i>Completed
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>

                                        <td>
                                            <a href="{{ route('job-orders.show', $job->id) }}"
                                                class="btn btn-sm btn-info" title="View Details">
                                                <i class="fa fa-eye"></i> View
                                            </a>

                                            <div class="btn-group">
                                                <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fa fa-ellipsis-v"></i> More
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('job-assignments') }}">
                                                            <i class="fa fa-tasks me-2"></i>Manage Status
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-warning editJobBtn" href="javascript:void(0);"
                                                           data-id="{{ $job->id }}"
                                                           data-job-no="{{ $job->job_order_number }}"
                                                           data-date="{{ $job->order_date }}"
                                                           data-total="{{ $job->total_amount }}"
                                                           data-paid="{{ $job->paid_amount }}"
                                                           data-status="{{ $job->status }}"
                                                           data-party-type="{{ $job->sale->party_type ?? 'walkin' }}"
                                                           data-vendor-id="{{ $job->sale->vendor_id ?? '' }}"
                                                           data-customer-id="{{ $job->sale->customer_id ?? '' }}"
                                                           data-shop-name="{{ $job->sale->customer_shopname ?? '' }}"
                                                           data-phone="{{ $job->sale->customer_phone ?? '' }}"
                                                           data-address="{{ $job->sale->customer_address ?? '' }}"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#editJobModal">
                                                            <i class="fa fa-edit me-2"></i>Edit Amount
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger deleteJobBtn" href="javascript:void(0);"
                                                           data-id="{{ $job->id }}">
                                                            <i class="fa fa-trash me-2"></i>Delete
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Job Order Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1" aria-labelledby="addJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Job Assignment</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>

            <div class="modal-body">
                <!-- Top Section: Select Order -->
                <div class="card bg-light mb-3">
                    <div class="card-body py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <label class="fw-bold small">Select Customer Order</label>
                                <input type="text" id="jobSearch" class="form-control mb-2" placeholder="Search orders...">
                                <select class="form-select" id="jobSelect">
                                    <option value="">-- Choose Order --</option>
                                    @foreach($localSales as $sale)
                                        @if($sale->party_type === 'vendor')
                                            <option value="{{ $sale->id }}">
                                                {{ $sale->invoice_number }} - [VENDOR] {{ $sale->vendor->Party_name ?? 'Unknown' }}
                                            </option>
                                        @elseif($sale->party_type === 'customer')
                                            <option value="{{ $sale->id }}">
                                                {{ $sale->invoice_number }} - [CUSTOMER] {{ $sale->customer->customer_name ?? $sale->customer->shop_name ?? 'Unknown' }}
                                            </option>
                                        @else
                                            <option value="{{ $sale->id }}">
                                                {{ $sale->invoice_number }} - [WALK-IN] {{ $sale->customer_shopname ?? 'Walk-in' }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 text-end">
                                <label class="fw-bold small d-block">Job Date</label>
                                <input type="date" id="jobDate" value="{{ date('Y-m-d') }}" class="form-control d-inline-block w-auto">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Party Information Display -->
                <div id="partyInfoBox" class="alert alert-info mb-3 d-none">
                    <strong>Party Type:</strong> <span id="partyTypeDisplay"></span><br>
                    <strong>Name:</strong> <span id="partyNameDisplay"></span>
                </div>

                <!-- Assignment Container -->
                <div id="assignmentsContainer"></div>

                <!-- Add Another Button -->
                <div class="text-center mt-3 d-none" id="addAnotherBtnContainer">
                    <button class="btn btn-outline-primary btn-sm" id="addAnotherAssignment">
                        + Add Another Assignment (Split Job)
                    </button>
                </div>
            </div>

            <div class="modal-footer justify-content-between">
                <div>
                     <span id="grandTotalLabel" class="fw-bold text-primary d-none">Total Cost: <span id="grandTotalDisplay">0</span></span>
                </div>
                <button type="button" class="btn btn-primary px-4" id="saveJobBtn">Save All Assignments</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Job Order Modal -->
<div class="modal fade" id="editJobModal" tabindex="-1" aria-labelledby="editJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('job-orders.update') }}" method="POST">
                @csrf
                <input type="hidden" name="job_id" id="editJobId">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Job Order: <span id="editJobNoDisplay" class="text-primary"></span></h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Job Date</label>
                        <input type="date" name="job_date" id="editJobDate" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Total Amount</label>
                        <input type="number" name="total_amount" id="editJobTotal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Paid Amount (Advance)</label>
                        <input type="number" name="paid_amount" id="editJobPaid" class="form-control" required>
                    </div>
                    <!-- New Party Details -->
                    <div class="mb-3">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Party Type</label>
                            <select class="form-select" name="party_type" id="editPartyType" required>
                                <option value="">Select Party Type</option>
                                <option value="vendor">Vendor</option>
                                <option value="customer">Customer</option>
                                <option value="walkin">Walk-in</option>
                            </select>
                        </div>
                        <div class="mb-3 d-none" id="partyNameDiv">
                            <label class="form-label fw-bold">Party Name</label>
                            <input type="text" class="form-control" name="party_name" id="editPartyName" placeholder="Party Name">
                        </div>
                    </div>
                    <div class="mb-3 d-none" id="vendorSelectDiv">
                        <label class="form-label fw-bold">Vendor</label>
                        <select class="form-select" name="vendor_id" id="editVendorId">
                            <option value="">Select Vendor</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->Party_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="customerSelectDiv">
                        <label class="form-label fw-bold">Customer</label>
                        <select class="form-select" name="customer_id" id="editCustomerId">
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->customer_name ?? $customer->shop_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 d-none" id="walkinDiv">
                        <label class="form-label fw-bold">Shop Name (Walk-in)</label>
                        <input type="text" class="form-control" name="shop_name" id="editShopName" placeholder="Enter shop name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Phone</label>
                        <input type="text" class="form-control" name="phone" id="editPhone" placeholder="Phone number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Address</label>
                        <input type="text" class="form-control" name="address" id="editAddress" placeholder="Address">
                    </div>
                    <div class="alert alert-info">
                         Note: Editing total or paid amounts will automatically update the contractor ledger balance.
                    </div>
                         Note: Editing total or paid amounts will automatically update the contractor ledger balance.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Job Order</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    // ============================================================
    // View Switch: Table View <-> Grouped Assignee View <-> Job View
    // ============================================================
    function switchView(mode) {
        // Hide all views
        document.getElementById('tableView').style.display   = 'none';
        document.getElementById('groupedView').style.display = 'none';
        document.getElementById('jobView').style.display     = 'none';

        // Remove active from all toggle buttons
        document.getElementById('btnTableView').classList.remove('active');
        document.getElementById('btnGroupView').classList.remove('active');
        document.getElementById('btnJobView').classList.remove('active');

        if (mode === 'group') {
            document.getElementById('groupedView').style.display = 'block';
            document.getElementById('btnGroupView').classList.add('active');
        } else if (mode === 'job') {
            document.getElementById('jobView').style.display = 'block';
            document.getElementById('btnJobView').classList.add('active');
        } else {
            document.getElementById('tableView').style.display = 'block';
            document.getElementById('btnTableView').classList.add('active');
        }

        localStorage.setItem('jobOrderView', mode);
    }

    // Restore last chosen view on page load
    document.addEventListener('DOMContentLoaded', function () {
        var savedView = localStorage.getItem('jobOrderView') || 'table';
        switchView(savedView);
    });

    // ============================================================
    // Toggle individual assignee group card expand/collapse
    // ============================================================
    function toggleGroup(key) {
        var collapseEl = document.getElementById('grp-' + key);
        var headerEl   = document.getElementById('hdr-' + key);
        if (!collapseEl) return;

        if (collapseEl.classList.contains('show')) {
            collapseEl.classList.remove('show');
            headerEl.setAttribute('aria-expanded', 'false');
        } else {
            collapseEl.classList.add('show');
            headerEl.setAttribute('aria-expanded', 'true');
        }
    }

    // Templates
    const assignmentTemplate = `
    <div class="card shadow-sm mb-4 assignment-card animate-fade-in border-start-primary">
        <div class="card-header bg-transparent border-bottom-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary rounded-circle p-2" style="width: 32px; height: 32px; display: flex; align-items: center; justify-content: center;"><span class="assign-num">1</span></span>
                <h6 class="mb-0 fw-bold text-dark">Job Assignment</h6>
            </div>
            <button type="button" class="btn btn-light btn-sm text-danger removeAssignment rounded-circle" style="display:none;"><i class="fa fa-times"></i></button>
        </div>
        <div class="card-body pt-0">
            <!-- Staff & Work Selection -->
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Assignment Type</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check assignTypeRadio" name="assignType_RAND" id="inHouse_RAND" value="inhouse" checked>
                        <label class="btn btn-outline-success" for="inHouse_RAND"><i class="fa fa-user"></i> In-House</label>

                        <input type="radio" class="btn-check assignTypeRadio" name="assignType_RAND" id="contract_RAND" value="contractor">
                        <label class="btn btn-outline-warning" for="contract_RAND"><i class="fa fa-briefcase"></i> Contractor</label>

                        <input type="radio" class="btn-check assignTypeRadio" name="assignType_RAND" id="vendor_RAND" value="vendor">
                        <label class="btn btn-outline-info" for="vendor_RAND"><i class="fa fa-truck"></i> Vendor</label>
                    </div>
                    <input type="hidden" class="assignType" value="inhouse">
                </div>
                <div class="col-md-4 contractorBox d-none animate-fade-in">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Select Contractor</label>
                    <select class="form-select contractor-select shadow-sm">
                        <option value="">-- Choose Contractor --</option>
                        @foreach($contractors as $contractor)
                            <option value="{{ $contractor->id }}" data-balance="{{ $contractor->ledger->closing_balance ?? $contractor->opening_balance }}">{{ $contractor->contractor_name }} - Bal: Rs. {{ number_format($contractor->ledger->closing_balance ?? $contractor->opening_balance) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 vendorBox d-none animate-fade-in">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Select Vendor</label>
                    <select class="form-select vendor-select shadow-sm">
                        <option value="">-- Choose Vendor --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" data-balance="{{ $vendor->ledger->closing_balance ?? $vendor->opening_balance ?? 0 }}">{{ $vendor->Party_name }} - Bal: Rs. {{ number_format($vendor->ledger->closing_balance ?? $vendor->opening_balance ?? 0) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted text-uppercase mb-1">Expected Return Date <span class="text-danger">*</span></label>
                    <input type="date" class="form-control expected-return-date shadow-sm" required>
                    <small class="text-muted">When should work be completed?</small>
                </div>
            </div>

            <!-- Items Table -->
            <div class="table-responsive rounded-3 border">
                <table class="table custom-table table-borderless mb-0">
                    <thead>
                        <tr>
                            <th width="40%" class="ps-3">Item Name</th>
                            <th width="15%" class="text-center">Order Qty</th>
                            <th width="15%" class="text-center">Remaining</th>
                            <th width="15%">Assign Qty</th>
                            <th width="15%" class="rate-col d-none">Rate</th>
                            <th width="5%" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody class="items-tbody"></tbody>
                </table>
            </div>

            <!-- Manual Add Button (Only for Contractor) -->
             <div class="mt-2 text-start manual-add-box d-none">
                 <button type="button" class="btn btn-sm btn-add-manual w-100 addManualItem">
                    + Add Custom Row / Extra Charges
                 </button>
             </div>

            <!-- Totals (Hidden for In-House) -->
             <div class="row justify-content-end mt-3 totals-row d-none animate-fade-in bg-light p-3 rounded mx-0">

                <!-- Info Column -->
                <div class="col-md-3 text-end border-end">
                     <p class="mb-1 small text-muted">A/C Balance</p>
                     <h6 class="fw-bold text-secondary mb-0 prev-balance-display">0.00</h6>
                     <input type="hidden" class="prev-balance" value="0">
                </div>

                <div class="col-md-3">
                     <label class="small fw-bold text-muted mb-1">Total Bill</label>
                     <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white border-end-0">Rs.</span>
                        <input type="text" class="form-control border-start-0 fw-bold text-dark total-bill" readonly value="0" style="background:#fff;">
                    </div>
                </div>

                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1">Paid (Now)</label>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white border-end-0">Rs.</span>
                        <input type="number" class="form-control border-start-0 paid-amount fw-bold" placeholder="0">
                    </div>
                </div>

                <div class="col-md-3 border-start">
                     <p class="mb-1 small text-muted">Final Balance</p>
                     <h5 class="fw-bold text-primary mb-0 final-remaining-display">0.00</h5>
                </div>
            </div>
        </div>
    </div>`;

    let currentSaleItems = [];

    $(document).ready(function () {

        // Filter dropdown when typing
        $("#jobSearch").on('input', function() {
            let term = $(this).val().toLowerCase();
            $('#jobSelect option').each(function() {
                let txt = $(this).text().toLowerCase();
                $(this).toggle(txt.indexOf(term) !== -1);
            });
        });

        // Load Order Items
        $("#jobSelect").change(function () {
            let saleId = $(this).val();
            $("#assignmentsContainer").empty();
            $("#addAnotherBtnContainer").addClass('d-none');
            $("#partyInfoBox").addClass('d-none');
            currentSaleItems = [];

            if (!saleId) return;

            // Fetch
            $.get(`{{ url('/job-orders/get-sale-details') }}/${saleId}`)
                .done(function (res) {
                    if (res.status && res.items.length > 0) {
                        currentSaleItems = res.items; // items have 'qty' as remaining
                        
                        // Display party information
                        if (res.party_type && res.party_name) {
                            let typeLabel = res.party_type === 'vendor' ? 'Vendor' : 
                                           res.party_type === 'customer' ? 'Customer' : 'Walk-in';
                            $("#partyTypeDisplay").text(typeLabel);
                            $("#partyNameDisplay").text(res.party_name);
                            $("#partyInfoBox").removeClass('d-none');
                        }
                        
                        addAssignmentBlock();
                        $("#addAnotherBtnContainer").removeClass('d-none');
                    } else {
                        alert("No remaining items to assign for this order.");
                    }
                })
                .fail(function() {
                    alert("Error loading items.");
                });
        });

        // Add Assignment Block
        function addAssignmentBlock() {
            let count = $(".assignment-card").length + 1;
            let uniqueId = Date.now() + Math.random().toString(36).substr(2, 5);

            let tempHtml = assignmentTemplate.replace(/_RAND/g, '_' + uniqueId);
            let $card = $(tempHtml);

            $card.find('.assign-num').text(count);
            if (count > 1) $card.find('.removeAssignment').show();

            // Check what is already assigned in previous cards
            let currentlyAssignedMap = {};
            $(".assignment-card").each(function() {
                $(this).find("tbody tr").each(function() {
                     let name = $(this).data('name');
                     let qty = parseFloat($(this).find(".assign-qty").val()) || 0;
                     if (!currentlyAssignedMap[name]) currentlyAssignedMap[name] = 0;
                     currentlyAssignedMap[name] += qty;
                });
            });

            // Populate Items
            let tbody = $card.find('.items-tbody');
            let hasItems = false;

            currentSaleItems.forEach(item => {
                let used = currentlyAssignedMap[item.item] || 0;
                let remainingForThisCard = item.qty - used;

                // Don't show if fully assigned
                if (remainingForThisCard <= 0.0001) return; 

                hasItems = true;

                let row = `
                <tr data-name="${item.item}" class="item-row" data-original-remaining="${item.qty}">
                    <td class="ps-3 fw-medium text-dark">
                        <input type="text" class="form-control form-control-sm border-0 bg-transparent p-0 item-name-input" value="${item.item}" readonly>
                    </td>
                    <td class="text-center text-muted small">${item.total_qty}</td>
                    <td class="text-center fw-bold text-primary remaining-cell">${remainingForThisCard}</td>
                    <td>
                        <input type="number" class="form-control form-control-sm assign-qty text-center fw-bold"
                            max="${remainingForThisCard}" value="${remainingForThisCard}">
                    </td>
                    <td class="rate-col d-none">
                        <input type="number" class="form-control form-control-sm assign-rate text-end" placeholder="0">
                    </td>
                     <td class="text-center">
                    </td>
                </tr>`;
                tbody.append(row);
            });

            if (!hasItems) {
                alert("All items have been fully assigned in the cards above.");
                return;
            }

            // Initial Styling for In-House
            $card.addClass('labour-type');

            $("#assignmentsContainer").append($card);

            // Scroll to new card smoothly
            $card[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

            recalculateGlobalRemaining();
        }

        $("#addAnotherAssignment").click(function() {
            addAssignmentBlock();
        });

        $(document).on("click", ".removeAssignment", function() {
            $(this).closest(".assignment-card").fadeOut(300, function(){
                $(this).remove();
                recalculateGlobalRemaining();
            });
        });

        // Handle Type Change (Radio)
        $(document).on("change", ".assignTypeRadio", function() {
            let val = $(this).val();
            let $card = $(this).closest(".assignment-card");

            $card.find(".assignType").val(val);
            updateVisibility($card, val);
        });

        function updateVisibility($card, type) {
            // Visuals
            $card.removeClass('labour-type contract-type vendor-type');
            if (type === 'inhouse') $card.addClass('labour-type');
            else if (type === 'contractor') $card.addClass('contract-type');
            else if (type === 'vendor') $card.addClass('vendor-type');

            // Fields
            if (type === 'contractor') {
                $card.find(".contractorBox").removeClass('d-none');
                $card.find(".vendorBox").addClass('d-none');
                $card.find(".rate-col").removeClass('d-none');
                $card.find(".totals-row").removeClass('d-none');
                $card.find(".manual-add-box").removeClass('d-none');
            } else if (type === 'vendor') {
                $card.find(".vendorBox").removeClass('d-none');
                $card.find(".contractorBox").addClass('d-none');
                $card.find(".rate-col").removeClass('d-none');
                $card.find(".totals-row").removeClass('d-none');
                $card.find(".manual-add-box").removeClass('d-none');
            } else {
                $card.find(".contractorBox").addClass('d-none');
                $card.find(".vendorBox").addClass('d-none');
                $card.find(".rate-col").addClass('d-none');
                $card.find(".totals-row").addClass('d-none');
                $card.find(".manual-add-box").addClass('d-none');
            }
        }
        
        // Add Manual Item logic
        $(document).on("click", ".addManualItem", function() {
            let $card = $(this).closest(".assignment-card");
            let tbody = $card.find('.items-tbody');

            let row = `
                <tr class="item-row manual-row animate-fade-in" style="background: #fdfdfd;">
                    <td class="ps-3">
                        <input type="text" class="form-control form-control-sm item-name-input" placeholder="e.g. Extra Labour, Fitting" value="">
                    </td>
                    <td class="text-center text-muted small">-</td>
                    <td class="text-center small">-</td>
                    <td>
                        <input type="number" class="form-control form-control-sm assign-qty text-center fw-bold" value="1">
                    </td>
                    <td class="rate-col">
                        <input type="number" class="form-control form-control-sm assign-rate text-end" placeholder="0">
                    </td>
                     <td class="text-center">
                        <button type="button" class="btn btn-xs text-danger removeRow p-0"><i class="fa fa-times"></i></button>
                    </td>
                </tr>`;

            tbody.append(row);
        });

        $(document).on("click", ".removeRow", function() {
            let $row = $(this).closest('tr');
            $row.fadeOut(200, function(){
                $row.remove();
                $(".assign-qty").trigger('input');
            });
        });

        // -----------------------------------------------------
        // Contractor/Vendor Balance Fetch
        // -----------------------------------------------------
        $(document).on("change", ".contractor-select", function() {
            let contractorId = $(this).val();
            let $card = $(this).closest(".assignment-card");

            if (contractorId) {
                $.get(`{{ url('/job-orders/contractor-balance') }}/${contractorId}`, function(res){
                     if(res.status) {
                         let balance = parseFloat(res.balance);
                         $card.find('.prev-balance').val(balance);
                         $card.find('.prev-balance-display').text(balance.toLocaleString());
                         calculateCardTotal($card);
                     }
                });
            } else {
                $card.find('.prev-balance').val(0);
                $card.find('.prev-balance-display').text("0.00");
                calculateCardTotal($card);
            }
        });

        $(document).on("change", ".vendor-select", function() {
            let selectedOption = $(this).find('option:selected');
            let balance = parseFloat(selectedOption.data('balance')) || 0;
            let $card = $(this).closest(".assignment-card");

            $card.find('.prev-balance').val(balance);
            $card.find('.prev-balance-display').text(balance.toLocaleString());
            calculateCardTotal($card);
        });



        // Calculations
        $(document).on("input", ".assign-qty, .assign-rate, .paid-amount", function() {
            let $card = $(this).closest(".assignment-card");
            calculateCardTotal($card);
            recalculateGlobalRemaining(); // Check limits on input
        });

        function calculateCardTotal($card) {
            let totalBill = 0;
            $card.find("tbody tr").each(function() {
                 let qty = parseFloat($(this).find(".assign-qty").val()) || 0;
                 let rate = parseFloat($(this).find(".assign-rate").val()) || 0;
                 totalBill += (qty * rate);
            });

            // Adjust calculations
            let prevBalance = parseFloat($card.find(".prev-balance").val()) || 0;
            let paid = parseFloat($card.find(".paid-amount").val()) || 0;

            let finalRemaining = prevBalance + totalBill - paid;

            $card.find(".total-bill").val(totalBill);
            $card.find(".final-remaining-display").text(finalRemaining.toLocaleString());
        }

        // Global Validations (Frontend)
        function recalculateGlobalRemaining() {
            // Reset visual errors
            $(".assign-qty").removeClass('is-invalid');

            // Map: Item Name -> Total Assigned Across ALL Cards
            let assignedTotal = {};

            // Iterate all cards and manual rows are excluded effectively by name check usually,
            // but manual rows might duplicate names if user types same name.
            // We only care about ensuring Sale Items don't exceed limit.

            $(".assignment-card").each(function() {
                $(this).find("tbody tr").each(function() {
                     // Only track rows that came from original sale (have data-original-remaining)
                     let limit = $(this).data('original-remaining');
                     if (limit !== undefined) {
                         let name = $(this).data('name'); // Use fixed name
                         let qty = parseFloat($(this).find(".assign-qty").val()) || 0;

                         if (!assignedTotal[name]) assignedTotal[name] = 0;
                         assignedTotal[name] += qty;
                     }
                });
            });

            // Now check limits
             $(".assignment-card").each(function() {
                $(this).find("tbody tr").each(function() {
                     let limit = $(this).data('original-remaining');
                     if (limit !== undefined) {
                         let name = $(this).data('name');
                         // If totalassigned > limit
                         if (assignedTotal[name] > limit) {
                              $(this).find(".assign-qty").addClass('is-invalid');
                         }
                     }
                });
            });
        }

        // Save
        $("#saveJobBtn").click(function() {
            let saleId = $("#jobSelect").val();
            if(!saleId) { alert("Select an order first"); return; }

            let assignments = [];
            let isValid = true;
            let messages = [];

            // 1. Check Limits (Re-run logic just in case)
            $(".assign-qty.is-invalid").each(function(){
                isValid = false;
                messages.push("Quantity limit exceeded for some items. Please check red boxes.");
                return false;
            });
            if(!isValid) { alert(messages[0]); return; }

            $(".assignment-card").each(function() {
                let $card = $(this);
                let type = $card.find(".assignType").val();
                let contractorId = $card.find(".contractor-select").val();
                let vendorId = $card.find(".vendor-select").val();
                let expectedReturnDate = $card.find(".expected-return-date").val();

                // Validation: Expected Return Date is required
                if (!expectedReturnDate) {
                    alert("Expected Return Date is required for all assignments.");
                    isValid = false;
                    return false;
                }

                if (type === 'contractor') {
                     if (!contractorId) {
                        alert("Select a contractor for all contractor assignments.");
                        isValid = false;
                        return false;
                    }

                    let bill = parseFloat($card.find(".total-bill").val()) || 0;
                    if (bill <= 0) {
                        alert("Total Bill cannot be zero for Contractor assignments. Please enter rates.");
                        isValid = false;
                        return false;
                    }
                }

                if (type === 'vendor') {
                     if (!vendorId) {
                        alert("Select a vendor for all vendor assignments.");
                        isValid = false;
                        return false;
                    }

                    let bill = parseFloat($card.find(".total-bill").val()) || 0;
                    if (bill <= 0) {
                        alert("Total Bill cannot be zero for Vendor assignments. Please enter rates.");
                        isValid = false;
                        return false;
                    }
                }

                let items = [];
                let hasItems = false;

                $card.find("tbody tr").each(function() {
                    let nameInput = $(this).find(".item-name-input");
                    let name = nameInput.val() ? nameInput.val() : $(this).data('name');

                    let qty = parseFloat($(this).find(".assign-qty").val()) || 0;
                    let rate = parseFloat($(this).find(".assign-rate").val()) || 0;

                    // Allow 0 qty? No.
                    if (qty > 0) {
                        items.push({ name: name, qty: qty, rate: rate });
                        hasItems = true;
                    }
                });

                if (hasItems) {
                    assignments.push({
                         assign_type: type,
                         contractor_id: contractorId,
                         vendor_id: vendorId,
                         expected_return_date: expectedReturnDate,
                         items: items,
                         total_amount: parseFloat($card.find(".total-bill").val()) || 0,
                         paid_amount: parseFloat($card.find(".paid-amount").val()) || 0,
                    });
                }
            });

            if(!isValid) return;
            if(assignments.length === 0) { alert("Assign at least one item."); return; }

            // Double check total qty for safety before ajax (already done via red box check)

            $.ajax({
                url: '{{ url("/job-orders/store") }}',

                method: 'POST',
                data: {
                    sale_id: saleId,
                    job_date: $("#jobDate").val(),
                    assignments: assignments,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                     Swal.fire({
                        title: 'Success!',
                        text: 'Job assignments created successfully.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        location.reload();
                    });
                },
                error: function(xhr) {
                     let msg = "Something went wrong!";
                     if(xhr.responseJSON && xhr.responseJSON.message) {
                         msg = xhr.responseJSON.message;
                     }
                     Swal.fire('Error', msg, 'error');
                }
            });
        });

        // Toggle Status - REMOVED (Now managed in Job Assignments page)
        // Status dropdown removed from this page

        // Populate Edit Modal
        <script>
    // Populate Edit Modal with job details
    $(document).on("click", ".editJobBtn", function() {
        let id = $(this).data('id');
        let jobNo = $(this).data('job-no');
        let date = $(this).data('date');
        let total = $(this).data('total');
        let paid = $(this).data('paid');
        let partyType = $(this).data('party-type');
        let vendorId = $(this).data('vendor-id');
        let customerId = $(this).data('customer-id');
        let shopName = $(this).data('shop-name');
        let phone = $(this).data('phone');
        let address = $(this).data('address');

        $("#editJobId").val(id);
        $("#editJobNoDisplay").text(jobNo);
        $("#editJobDate").val(date.split(' ')[0]);
        $("#editJobTotal").val(total);
        $("#editJobPaid").val(paid);
        $("#editPartyType").val(partyType);
        $("#editVendorId").val(vendorId);
        $("#editCustomerId").val(customerId);
        $("#editShopName").val(shopName);
        $("#editPhone").val(phone);
        $("#editAddress").val(address);
        // Set Party Name based on type
        var partyName = '';
        if (partyType === 'vendor') {
            partyName = $("#editVendorId option[value='" + vendorId + "']").text();
        } else if (partyType === 'customer') {
            partyName = $("#editCustomerId option[value='" + customerId + "']").text();
        } else if (partyType === 'walkin') {
            partyName = shopName;
        }
        $("#editPartyName").val(partyName);
        togglePartyFields(partyType);
    });

    // Show/hide party specific fields based on selected type
    function togglePartyFields(type) {
        $("#vendorSelectDiv").addClass('d-none');
        $("#customerSelectDiv").addClass('d-none');
        $("#walkinDiv").addClass('d-none');
        $("#partyNameDiv").addClass('d-none');
        if (type === 'vendor') {
            $("#vendorSelectDiv").removeClass('d-none');
            $("#partyNameDiv").removeClass('d-none');
        } else if (type === 'customer') {
            $("#customerSelectDiv").removeClass('d-none');
            $("#partyNameDiv").removeClass('d-none');
        } else if (type === 'walkin') {
            $("#walkinDiv").removeClass('d-none');
            $("#partyNameDiv").removeClass('d-none');
        }
    }

    // Change handler for Party Type dropdown in modal
    $("#editPartyType").on('change', function() {
        togglePartyFields($(this).val());
    });

         // Delete
        $(document).on("click", ".deleteJobBtn", function (e) {
            e.preventDefault();
            let id = $(this).data("id");

            Swal.fire({
                title: "Are you sure?",
                text: "Legder entries will be reversed!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/job-orders/delete/" + id,
                        type: "DELETE",
                        data: { _token: "{{ csrf_token() }}" },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire("Deleted!", "Job Order deleted.", "success").then(() => location.reload());
                            } else {
                                Swal.fire("Error!", "Delete failed", "error");
                            }
                        },
                        error: function () {
                            Swal.fire("Error!", "Something went wrong", "error");
                        }
                    });
                }
            });
        });

    });
</script>
