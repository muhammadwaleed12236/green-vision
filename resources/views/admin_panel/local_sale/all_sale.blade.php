@include('admin_panel.include.header_include')

<style>
    .as2-head {
        flex-direction: row;
    }
    .as2-wrap {
        overflow-x: hidden;
    }
    .as2-wrap .table {
        table-layout: fixed;
        width: 100%;
        min-width: 0;
    }
    .as2-wrap th,
    .as2-wrap td {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .as2-wrap .as2-val {
        display: contents;
    }
    .as2-wrap th:nth-child(1), .as2-wrap td:nth-child(1) { width: 11%; }
    .as2-wrap th:nth-child(2), .as2-wrap td:nth-child(2) { width: 11%; }
    .as2-wrap th:nth-child(3), .as2-wrap td:nth-child(3) { width: 20%; }
    .as2-wrap th:nth-child(4), .as2-wrap td:nth-child(4) { width: 13%; }
    .as2-wrap th:nth-child(5), .as2-wrap td:nth-child(5) { width: 17%; }
    .as2-wrap th:nth-child(6), .as2-wrap td:nth-child(6) { width: 12%; }
    .as2-wrap th:nth-child(7), .as2-wrap td:nth-child(7) { width: 8%; }
    .as2-wrap th:nth-child(8), .as2-wrap td:nth-child(8) { width: 8%; }
    .as2-wrap th:nth-child(9), .as2-wrap td:nth-child(9) { width: 12%; }

    @media (max-width: 575.98px) {
        .as2-head {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }
        .as2-head .as2-search {
            width: 100%;
            flex-wrap: wrap;
        }
        .as2-head .as2-search form {
            width: 100%;
        }
        .as2-head .as2-search form input {
            min-width: 0;
        }
    }

    @media (max-width: 767.98px) {
        .as2-wrap .table,
        .as2-wrap .table tbody,
        .as2-wrap .table tbody tr {
            display: block;
        }
        .as2-wrap .table thead {
            display: none;
        }
        .as2-wrap .table tbody tr {
            display: block;
            margin-bottom: 12px;
            border: 1px solid #eef2f7 !important;
            border-radius: 8px;
            background: #fff;
        }
        .as2-wrap .table tbody tr td {
            display: flex !important;
            width: 100% !important;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 10px !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            text-align: right;
        }
        .as2-wrap .table tbody tr td:last-child {
            border-bottom: 0 !important;
        }
        .as2-wrap .table tbody tr td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .as2-wrap .table tbody tr td .as2-val {
            display: block;
            flex: 1 1 auto;
            min-width: 0;
            text-align: right;
            overflow-wrap: break-word;
        }
        .as2-wrap .table tbody tr td .as2-val .badge {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
        }
        .as2-wrap .table tbody tr td.as2-actions .as2-val {
            display: flex;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 6px;
        }
        .as2-wrap .table tbody tr td .btn-group .dropdown-menu {
            white-space: nowrap;
        }
    }
</style>

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

<div class="page-header as2-head d-flex justify-content-between align-items-center mb-3">
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
    

<div class="d-flex justify-content-between align-items-center mb-3 as2-search">

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

<div class="table-responsive as2-wrap" style="min-height: 350px;">
<table class="table table-bordered align-middle mb-0">
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
    <td class="fw-bold text-center" data-label="Job No"><span class="as2-val">{{ $sale->invoice_number }}</span></td>
    <td class="text-center" data-label="Date"><span class="as2-val">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y') }}</span></td>

    <td data-label="Party">
        <span class="as2-val">
            @if($sale->party_type === 'vendor' && $sale->vendor)
                {{ $sale->vendor->Party_name ?? $sale->vendor->business_name ?? $sale->vendor->name ?? 'Vendor' }}
            @else
                {{ $sale->customer->customer_name
                    ?? $sale->customer->shop_name
                    ?? $sale->customer->business_name
                    ?? $sale->customer_shopname
                    ?? 'Walk-in' }}
            @endif
        </span>
    </td>

    <td class="text-center" data-label="Phone">
        <span class="as2-val">
            @if($sale->party_type === 'vendor' && $sale->vendor)
                {{ $sale->vendor->Party_phone ?? $sale->vendor->phone_number ?? '-' }}
            @elseif($sale->party_type === 'walkin')
                {{ $sale->customer_phone ?? '-' }}
            @else
                {{ optional($sale->customer)->phone_number ?? $sale->customer_phone ?? '-' }}
            @endif
        </span>
    </td>

    <td data-label="Items" title="{{ is_array($items) ? implode(', ', $items) : '' }}">
        <span class="as2-val">
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
        </span>
    </td>

    <td class="fw-bold text-end" data-label="Net Amount"><span class="as2-val">{{ number_format($sale->net_amount, 2) }}</span></td>

    <td class="text-center" data-label="Type">
        <span class="as2-val">
            @if($sale->sale_type == 'estimate')
                <span class="badge bg-info">Estimate</span>
            @elseif($sale->sale_type == 'booking')
                <span class="badge bg-warning text-dark">Booking</span>
            @elseif($sale->sale_type == 'sale')
                <span class="badge bg-success">Sale</span>
            @else
                <span class="badge bg-secondary">{{ ucfirst($sale->sale_type ?? 'Estimate') }}</span>
            @endif
        </span>
    </td>

    <td class="text-center" data-label="Status">
        <span class="as2-val">
            @if($sale->job_status == 'pending')
                <span class="badge bg-secondary">Pending</span>
            @elseif($sale->job_status == 'ready')
                <span class="badge bg-success">Ready</span>
            @elseif($sale->job_status == 'completed')
                <span class="badge bg-primary">Completed</span>
            @else
                <span class="badge bg-warning text-dark">{{ ucfirst($sale->job_status) }}</span>
            @endif
        </span>
    </td>

    <td class="text-center as2-actions" data-label="Actions">
        <span class="as2-val">
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
                        <a class="dropdown-item" href="{{ route('local.sale.receipt', $sale->id) }}" target="_blank">
                            <i class="fa fa-receipt me-2"></i>Thermal Receipt
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
        </span>
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

@include('admin_panel.include.footer_include')
