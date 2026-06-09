@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4><i class="fa fa-tasks me-2"></i>Job Assignments - Track All Jobs</h4>
                    <h6>Manage and track all job assignments grouped by job number</h6>
                </div>
            </div>

            @php
                $totalJobs = $groupedJobs->count();
                $pendingCount = 0;
                $inProgressCount = 0;
                $completedCount = 0;

                foreach($groupedJobs as $jobGroup) {
                    $allCompleted = $jobGroup->every(fn($j) => $j->status === 'completed');
                    $anyInProgress = $jobGroup->contains(fn($j) => $j->status === 'in_progress');

                    if ($allCompleted) {
                        $completedCount++;
                    } elseif ($anyInProgress) {
                        $inProgressCount++;
                    } else {
                        $pendingCount++;
                    }
                }
            @endphp

            <!-- Status Summary Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-sm-6">
                    <div class="card bg-warning bg-gradient text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ $pendingCount }}</h3>
                            <p class="mb-0"><i class="fa fa-clock me-1"></i>Pending Jobs</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card bg-info bg-gradient text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ $inProgressCount }}</h3>
                            <p class="mb-0"><i class="fa fa-sync me-1"></i>In Progress</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card bg-success bg-gradient text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ $completedCount }}</h3>
                            <p class="mb-0"><i class="fa fa-check-circle me-1"></i>Completed</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6">
                    <div class="card bg-primary bg-gradient text-white">
                        <div class="card-body">
                            <h3 class="mb-0">{{ $totalJobs }}</h3>
                            <p class="mb-0"><i class="fa fa-list me-1"></i>Total Jobs</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search Filter -->
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('job-assignments') }}" class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Search Job</label>
                            <input type="text" name="q" class="form-control" placeholder="Search by Job #, Invoice #, or Party name..." value="{{ request('q') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-search me-2"></i>Search
                            </button>
                            @if(request('q'))
                                <a href="{{ route('job-assignments') }}" class="btn btn-secondary">
                                    <i class="fa fa-times me-2"></i>Clear
                                </a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Jobs Table -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datatable">
                            <thead class="table-light">
                                <tr>
                                    <th>Invoice Number</th>
                                    <th>Party</th>
                                    <th>Assigned To</th>
                                    <th>Assignment Type</th>
                                    <th>Expected Return</th>
                                    <th>Sale Status</th>
                                    <th>Job Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($groupedJobs as $jobNumber => $jobs)
                                    @php
                                        $firstJob = $jobs->first();
                                        $totalAssignments = $jobs->count();
                                        $completedAssignments = $jobs->where('status', 'completed')->count();
                                        $allCompleted = $completedAssignments === $totalAssignments;
                                        $anyInProgress = $jobs->contains(fn($j) => $j->status === 'in_progress');

                                        if ($allCompleted) {
                                            $overallStatus = 'completed';
                                            $statusBadge = 'bg-success';
                                            $statusIcon = 'fa-check-circle';
                                        } elseif ($anyInProgress) {
                                            $overallStatus = 'in_progress';
                                            $statusBadge = 'bg-info';
                                            $statusIcon = 'fa-sync';
                                        } else {
                                            $overallStatus = 'pending';
                                            $statusBadge = 'bg-warning';
                                            $statusIcon = 'fa-clock';
                                        }
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong class="text-primary d-block">{{ $jobNumber }}</strong>
                                            @if($firstJob->sale)
                                                <a href="{{ route('show-local-sale', $firstJob->sale_id) }}" class="text-decoration-none small">
                                                    {{ $firstJob->sale->invoice_number }}
                                                </a>
                                            @endif
                                            @if($totalAssignments > 1)
                                                <br><span class="badge bg-secondary">{{ $totalAssignments }} assignments</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($firstJob->sale)
                                                @if($firstJob->sale->party_type === 'vendor' && $firstJob->sale->vendor)
                                                    <strong class="text-info">{{ $firstJob->sale->vendor->Party_name }}</strong>
                                                    <br>
                                                    <small class="text-muted"><i class="fa fa-store me-1"></i>Vendor</small>
                                                @elseif($firstJob->sale->party_type === 'customer' && $firstJob->sale->customer)
                                                    <strong>{{ $firstJob->sale->customer->customer_name }}</strong>
                                                    <br>
                                                    <small class="text-muted"><i class="fa fa-user me-1"></i>{{ $firstJob->sale->customer->shop_name ?? 'Customer' }}</small>
                                                @elseif($firstJob->sale->party_type === 'walkin' || !$firstJob->sale->party_type)
                                                    <strong>{{ $firstJob->sale->customer_shopname ?? 'Walk-in' }}</strong>
                                                    <br>
                                                    <small class="text-muted"><i class="fa fa-user-o me-1"></i>Walk-in</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($totalAssignments > 1)
                                                <button class="btn btn-sm btn-outline-primary view-assignments-btn"
                                                        data-job-number="{{ $jobNumber }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#assignmentsModal">
                                                    <i class="fa fa-users me-1"></i>{{ $totalAssignments }} People
                                                </button>
                                            @else
                                                <strong>{{ $firstJob->assigned_to_name }}</strong>
                                            @endif
                                        </td>
                                        <td>
                                            @if($totalAssignments > 1)
                                                <span class="badge bg-secondary"><i class="fa fa-users me-1"></i>Multiple</span>
                                            @else
                                                @if($firstJob->assignee_type === 'inhouse')
                                                    <span class="badge bg-primary"><i class="fa fa-user me-1"></i>In-House</span>
                                                @elseif($firstJob->assignee_type === 'contractor')
                                                    <span class="badge bg-warning"><i class="fa fa-hard-hat me-1"></i>Contractor</span>
                                                @elseif($firstJob->assignee_type === 'vendor')
                                                    <span class="badge bg-info"><i class="fa fa-store me-1"></i>Vendor</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($firstJob->expected_return_date)
                                                @php
                                                    $returnDate = \Carbon\Carbon::parse($firstJob->expected_return_date);
                                                    $daysLeft = \Carbon\Carbon::now()->diffInDays($returnDate, false);
                                                    $isOverdue = $daysLeft < 0;
                                                @endphp
                                                <div>
                                                    <small class="d-block">{{ $returnDate->format('d M Y') }}</small>
                                                    @if($isOverdue)
                                                        <span class="badge bg-danger">Overdue</span>
                                                    @elseif($daysLeft == 0)
                                                        <span class="badge bg-warning text-dark">Today</span>
                                                    @else
                                                        <span class="badge bg-success">{{ abs($daysLeft) }} days left</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-muted">Not set</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($firstJob->sale)
                                                @if($firstJob->sale->job_status === 'pending')
                                                    <span class="badge bg-secondary"><i class="fa fa-clock me-1"></i>Pending</span>
                                                @elseif($firstJob->sale->job_status === 'ready')
                                                    <span class="badge bg-success"><i class="fa fa-check me-1"></i>Ready</span>
                                                @elseif($firstJob->sale->job_status === 'completed')
                                                    <span class="badge bg-primary"><i class="fa fa-check-double me-1"></i>Completed</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $statusBadge }}">
                                                <i class="fa {{ $statusIcon }} me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $overallStatus)) }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $completedAssignments }}/{{ $totalAssignments }} done</small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info manage-job-btn"
                                                    data-job-number="{{ $jobNumber }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#manageJobModal">
                                                <i class="fa fa-cog me-1"></i>Manage
                                            </button>
                                            @if($firstJob->sale && $firstJob->sale->job_status === 'ready')
                                                <button class="btn btn-sm btn-success mark-delivered-btn mt-1" data-sale-id="{{ $firstJob->sale_id }}">
                                                    <i class="fa fa-truck me-1"></i>Deliver
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fa fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                            <h5 class="text-muted">No job assignments found</h5>
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
</div>

<!-- Manage Job Modal -->
<div class="modal fade" id="manageJobModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa fa-tasks me-2"></i>Manage Job: <span id="modalJobNumber"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="jobDetailsContainer">
                <div class="text-center py-5">
                    <i class="fa fa-spinner fa-spin fa-3x text-primary"></i>
                    <p class="mt-3 text-muted">Loading assignments...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Assignments Modal -->
<div class="modal fade" id="assignmentsModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fa fa-users me-2"></i>Assigned People
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="assignmentsListContainer">
                <div class="text-center py-3">
                    <i class="fa fa-spinner fa-spin fa-2x text-info"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.assignment-item {
    border-left: 4px solid #dee2e6;
    transition: all 0.3s;
}
.assignment-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const groupedJobsData = @json($groupedJobs);

    // Manage/View buttons
    document.querySelectorAll('.manage-job-btn, .view-assignments-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jobNumber = this.dataset.jobNumber;
            const jobs = groupedJobsData[jobNumber];

            if (this.classList.contains('manage-job-btn')) {
                showManageModal(jobNumber, jobs);
            } else {
                showAssignmentsModal(jobNumber, jobs);
            }
        });
    });

    function showAssignmentsModal(jobNumber, jobs) {
        let html = '<div class="list-group">';
        jobs.forEach(job => {
            const typeColors = {'inhouse': 'primary', 'contractor': 'warning', 'vendor': 'info'};
            const typeColor = typeColors[job.assignee_type] || 'secondary';

            html += `
                <div class="list-group-item">
                    <h6 class="mb-1">${job.assigned_to_name}</h6>
                    <span class="badge bg-${typeColor}">${job.assigned_type_label}</span>
                </div>
            `;
        });
        html += '</div>';
        document.getElementById('assignmentsListContainer').innerHTML = html;
    }

    function showManageModal(jobNumber, jobs) {
        document.getElementById('modalJobNumber').textContent = jobNumber;

        let html = '<div class="row g-3">';
        jobs.forEach(job => {
            const statusColors = {
                'pending': { bg: 'warning', icon: 'clock' },
                'in_progress': { bg: 'info', icon: 'sync' },
                'completed': { bg: 'success', icon: 'check-circle' }
            };
            const statusInfo = statusColors[job.status] || { bg: 'secondary', icon: 'question' };

            html += `
                <div class="col-12">
                    <div class="card assignment-item border-start border-4 border-${statusInfo.bg}">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <h6 class="mb-1">${job.assigned_to_name}</h6>
                                    <small class="text-muted">${job.assigned_type_label}</small>
                                </div>
                                <div class="col-md-5">
                                    <label class="small text-muted mb-1">Change Status</label>
                                    <select class="form-select form-select-sm job-status-update" data-job-id="${job.id}" data-sale-id="${job.sale_id}" data-original="${job.status}">
                                        <option value="pending" ${job.status === 'pending' ? 'selected' : ''}>Pending</option>
                                        <option value="in_progress" ${job.status === 'in_progress' ? 'selected' : ''}>In Progress</option>
                                        <option value="completed" ${job.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-end">
                                    ${job.completed_at ? `<small class="text-success"><i class="fa fa-check-circle"></i> ${new Date(job.completed_at).toLocaleDateString()}</small>` : '<small class="text-muted">Not completed</small>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        document.getElementById('jobDetailsContainer').innerHTML = html;

        // Status update listeners
        document.querySelectorAll('.job-status-update').forEach(select => {
            select.addEventListener('change', function() {
                updateJobStatus(this.dataset.jobId, this.value, this);
            });
        });
    }

    function updateJobStatus(jobId, newStatus, selectElement) {
        const originalValue = selectElement.dataset.original;

            const url = "{{ route('job-assignments.update-status', ':id') }}".replace(':id', jobId);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ status: newStatus })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: 'Status changed successfully',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => location.reload());
            } else {
                Swal.fire('Error', 'Failed to update', 'error');
                selectElement.value = originalValue;
            }
        })
        .catch(error => {
            Swal.fire('Error', 'Network error', 'error');
            selectElement.value = originalValue;
        });
    }

    // Mark delivered
    document.querySelectorAll('.mark-delivered-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.saleId;

            Swal.fire({
                title: 'Confirm Delivery',
                text: 'Mark order as delivered?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, Deliver'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = "{{ route('sales.mark-completed', ':id') }}".replace(':id', saleId);

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Success', 'Order completed!', 'success').then(() => location.reload());
                        } else {
                            Swal.fire('Error', data.message, 'error');
                        }
                    });
                }
            });
        });
    });
});
</script>

@include('admin_panel.include.footer_include')