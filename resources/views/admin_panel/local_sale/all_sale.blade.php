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
    <div class="ms-3">
        <form method="GET" action="{{ route('all-local-sale') }}" class="d-flex">
            <input type="text" name="q" value="{{ $query ?? request('q') }}" class="form-control form-control-sm me-2" placeholder="Search invoice, customer, vendor or items">
            <button class="btn btn-sm btn-primary">Search</button>
        </form>
    </div>
</div>

<div class="card">
<div class="card-body p-0">

<table class="table table-bordered align-middle mb-0" style="table-layout:fixed">
<colgroup>
    <col style="width:75px">
    <col style="width:95px">
    <col style="width:110px">
    <col style="width:95px">
    <col>
    <col style="width:105px">
    <col style="width:80px">
    <col style="width:90px">
</colgroup>
<thead class="table-light text-center">
<tr>
    <th>Job No</th>
    <th>Date</th>
    <th>Party</th>
    <th>Phone</th>
    <th>Items</th>
    <th>Net Amount</th>
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
            {{ $sale->customer->customer_name
                ?? $sale->customer->shop_name
                ?? $sale->customer->business_name
                ?? $sale->customer_shopname
                ?? 'Walk-in' }}
        @endif
    </td>

    <td class="text-center">{{ $sale->customer_phone ?? '-' }}</td>

    <td style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis" title="{{ is_array($items) ? implode(', ', $items) : '' }}">
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
                        <i class="fa fa-edit me-2"></i>Edit
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                @if($sale->job_status == 'ready')
                    <li>
                        <a class="dropdown-item text-success mark-complete-btn" href="javascript:void(0);"
                           data-sale-id="{{ $sale->id }}">
                            <i class="fa fa-check-circle me-2"></i>Mark Complete
                        </a>
                    </li>
                @endif
                <li>
                    <a class="dropdown-item text-primary"
                       href="{{ route('job-assignments', ['q' => $sale->invoice_number]) }}">
                        <i class="fa fa-user-plus me-2"></i>Assign Job
                    </a>
                </li>
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
    <td colspan="8" class="text-center text-muted py-4">
        No Job Orders Found
    </td>
</tr>
@endforelse
</tbody>
</table>

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

@include('admin_panel.include.footer_include')