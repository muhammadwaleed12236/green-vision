@include('admin_panel.include.header_include')

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

<div class="page-header d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">Job Orders</h4>
        <small class="text-muted">All Job Orders (Read Only)</small>
    </div>
    
    <!-- <div class="ms-3">
        <form method="GET" action="{{ route('all-local-sale') }}" class="d-flex">
            <input type="text" name="q" value="{{ $query ?? request('q') }}" class="form-control form-control-sm me-2" placeholder="Search invoice, customer, vendor or items">
            <button class="btn btn-sm btn-primary">Search</button>
        </form>
        <div>
            <a class="btn btn-success" href="{{ route('local-sale') }}" role="button">+ Add New Sale</a>
        </div>
        
    </div> -->
    

<div class="d-flex justify-content-between align-items-center mb-3">

    <form method="GET" action="{{ route('all-local-sale') }}" class="d-flex flex-grow-1 me-3">
        <input type="text"
               name="q"
               value="{{ $query ?? request('q') }}"
               class="form-control me-2"
               placeholder="Search invoice, customer, vendor or items">
        <button class="btn btn-primary">Search</button>
    </form>

    <a class="btn btn-success" href="{{ route('local-sale') }}">
        + Add New Sale
    </a>

</div>
</div>

<div class="card">
<div class="card-body p-0">

<div class="table-responsive" style="min-height: 350px;">
<table class="table table-bordered align-middle mb-0 text-nowrap">
<thead class="table-light text-center">
<tr>
    <th>Job No</th>
    <th>Date</th>
    <th>Party</th>
    <th>Phone</th>
    <th>Items</th>
    <th>Net Amount</th>
    <th>Type</th>
    <th>Status</th>
    <th>Actions</th>
</tr>
</thead>

<tbody>
@forelse ($Sales as $sale)
@php
    $items = json_decode($sale->item, true);
@endphp
<tr>
    <td class="fw-bold text-center">{{ $sale->invoice_number }}</td>
    <td class="text-center">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</td>

    <td>
        @if($sale->party_type === 'vendor' && $sale->vendor)
            {{ $sale->vendor->Party_name ?? $sale->vendor->business_name ?? $sale->vendor->name ?? 'Vendor' }}
        @else
            <span style="white-space: normal; display: block; min-width: 120px;">
                {{ $sale->customer->customer_name
                    ?? $sale->customer->shop_name
                    ?? $sale->customer->business_name
                    ?? $sale->customer_shopname
                    ?? 'Walk-in' }}
            </span>
        @endif
    </td>

    <td class="text-center">
        @if($sale->party_type === 'vendor' && $sale->vendor)
            {{ $sale->vendor->Party_phone ?? $sale->vendor->phone_number ?? '-' }}
        @elseif($sale->party_type === 'walkin')
            {{ $sale->customer_phone ?? '-' }}
        @else
            {{ optional($sale->customer)->phone_number ?? $sale->customer_phone ?? '-' }}
        @endif
    </td>

    <td style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ is_array($items) ? implode(', ', $items) : '' }}">
        @php
            $itemsArray = is_array($items) ? $items : [];
        @endphp
        <small>
            @if(count($itemsArray) === 0)
                -
            @else
                {{ implode(', ', array_slice($itemsArray, 0, 2)) }}@if(count($itemsArray) > 2) ... @endif
            @endif
        </small>
    </td>

    <td class="fw-bold text-end">{{ number_format($sale->net_amount, 2) }}</td>

    <td class="text-center">
        @if($sale->sale_type == 'estimate')
            <span class="badge bg-info">Estimate</span>
        @elseif($sale->sale_type == 'booking')
            <span class="badge bg-warning text-dark">Booking</span>
        @elseif($sale->sale_type == 'sale')
            <span class="badge bg-success">Sale</span>
        @else
            <span class="badge bg-secondary">{{ ucfirst($sale->sale_type ?? 'Estimate') }}</span>
        @endif
    </td>

    <td class="text-center">
        @if($sale->job_status == 'pending')
            <span class="badge bg-secondary">Pending</span>
        @elseif($sale->job_status == 'ready')
            <span class="badge bg-success">Ready</span>
        @elseif($sale->job_status == 'completed')
            <span class="badge bg-primary">Completed</span>
        @else
            <span class="badge bg-warning text-dark">{{ ucfirst($sale->job_status) }}</span>
        @endif
    </td>

    <td class="text-center" style="white-space:nowrap">
        <a href="{{ route('show-local-sale', $sale->id) }}"
           class="btn btn-sm btn-info" title="View">
            <i class="fa fa-eye"></i>
        </a>

        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle px-2" data-bs-toggle="dropdown" title="More">
                <i class="fa fa-ellipsis-v"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('local.sale.invoice', $sale->id) }}">
                        <i class="fa fa-file-invoice me-2"></i>Invoice
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('local.sale.edit', $sale->id) }}">
                        <i class="fa fa-edit me-2"></i>Update Invoice
                    </a>
                </li>
                @if($sale->sale_type == 'booking' && $sale->job_status != 'completed')
                    <li>
                        <a class="dropdown-item text-success mark-complete-btn" href="javascript:void(0);"
                           data-sale-id="{{ $sale->id }}">
                            <i class="fa fa-check-circle me-2"></i>Mark Complete
                        </a>
                    </li>
                @endif
                {{-- <li>
                    <a class="dropdown-item text-primary"
                       href="{{ route('job-assignments', ['q' => $sale->invoice_number]) }}">
                        <i class="fa fa-user-plus me-2"></i>Assign Job
                    </a>
                </li> --}}
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger"
                       href="{{ route('local.sale.delete', $sale->id) }}"
                       onclick="return confirm('Delete this job order?')">
                        <i class="fa fa-trash me-2"></i>Delete
                    </a>
                </li>
            </ul>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="9" class="text-center text-muted py-4">
        No Job Orders Found
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="d-flex justify-content-end mt-3">
    <style>
        /* Small fixes for pagination icon sizing on this page */
        .pagination { margin: 0; }
        .pagination .page-link { padding: .35rem .6rem; line-height: 1; }
        .pagination svg, .pagination i {
            width: 1em !important;
            height: 1em !important;
            font-size: 1em !important;
            vertical-align: middle;
        }
    </style>

    {{ $Sales->links('pagination::bootstrap-4') }}
</div>

</div>
</div>

</div>
</div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark Sale as Complete
    document.querySelectorAll('.mark-complete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const saleId = this.dataset.saleId;

            Swal.fire({
                title: 'Mark as Completed?',
                text: 'This will mark the order as completed/delivered',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, Mark Complete',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/sales/mark-completed/${saleId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Order marked as completed',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Failed to mark as completed'
                            });
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'An error occurred'
                        });
                    });
                }
            });
        });
    });
});
</script>

<!-- Convert Estimate Modal -->
<div class="modal fade" id="convertEstimateModal" tabindex="-1" aria-labelledby="convertEstimateModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form id="convertEstimateForm" method="POST" action="">
            @csrf
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="convertEstimateModalLabel">Convert Estimate to Sale</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select Plan Option</label>
                        <select name="selected_plan" id="selectedPlanDropdown" class="form-select">
                            <option value="">None (Convert Common Items Only)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Convert to Sale</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Convert Estimate to Sale Modal -->
<div class="modal fade" id="convertEstimateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="convertEstimateForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Convert Estimate to Sale</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="fw-bold mb-2">Selected Plan</label>
                        <select name="selected_plan" id="selectedPlanDropdown" class="form-select">
                            <option value="">None (Only Common Items)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Convert Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Convert Estimate Button
        $('.convert-to-sale-btn').on('click', function() {
            let saleId = $(this).data('sale-id');
            let plansJson = $(this).attr('data-plans');
            let plans = [];
            if (plansJson) {
                try { plans = JSON.parse(plansJson); } catch (e) {}
            }
            
            let dropdown = $('#selectedPlanDropdown');
            dropdown.empty();
            dropdown.append('<option value="">None (Only Common Items)</option>');
            
            if (plans && Array.isArray(plans)) {
                plans.forEach((plan, index) => {
                    dropdown.append(`<option value="${index}">${plan.name}</option>`);
                });
            }
            
            $('#convertEstimateForm').attr('action', `/local-sale/convert/${saleId}/sale`);
            $('#convertEstimateModal').modal('show');
        });

        $('.convert-btn').on('click', function() {
            let saleId = $(this).data('id');
            let plansJson = $(this).attr('data-plans');
            
            let $dropdown = $('#selectedPlanDropdown');
            $dropdown.empty();
            $dropdown.append('<option value="">None (Convert Common Items Only)</option>');
            
            if (plansJson) {
                try {
                    let plans = JSON.parse(plansJson);
                    if (Array.isArray(plans)) {
                        plans.forEach(function(plan, idx) {
                            $dropdown.append('<option value="' + idx + '">' + (plan.name || 'Plan ' + (idx+1)) + '</option>');
                        });
                    }
                } catch (e) {
                    console.error("Error parsing plans: ", e);
                }
            }
            
            let url = "{{ route('local.sale.convert', [':id', 'sale']) }}";
            url = url.replace(':id', saleId);
            $('#convertEstimateForm').attr('action', url);
            
            let myModal = new bootstrap.Modal(document.getElementById('convertEstimateModal'));
            myModal.show();
        });
    });
</script>

@include('admin_panel.include.footer_include')

