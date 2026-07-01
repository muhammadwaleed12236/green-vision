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

<div class="table-responsive">
<table class="table table-bordered table-hover align-middle mb-0" style="font-size: 13px;">
<thead class="table-dark text-center">
<tr>
    <th style="white-space:nowrap;">Job No</th>
    <th style="white-space:nowrap;">Party Type</th>
    <th style="white-space:nowrap;">Sale Date &amp; Time</th>
    <th style="white-space:nowrap;">Customer / Party</th>
    <th style="white-space:nowrap;">Phone</th>
    <th style="white-space:nowrap;">Address</th>
    <th style="white-space:nowrap;">Items</th>
    <th style="white-space:nowrap;">Net Amount</th>
    <th style="white-space:nowrap;">Status</th>
    <th style="white-space:nowrap;" width="190">Actions</th>
</tr>
</thead>

<tbody>
@forelse ($Sales as $sale)
@php
    $items = json_decode($sale->item, true);

    /* ── Party Type badge ── */
    $partyType  = $sale->party_type ?? 'walkin';
    $badgeClass = match($partyType) {
        'customer' => 'bg-success',
        'vendor'   => 'bg-primary',
        default    => 'bg-secondary',
    };
    $partyLabel = match($partyType) {
        'customer' => 'Customer',
        'vendor'   => 'Vendor',
        default    => 'Walk-in',
    };

    /* ── Customer / Party name ── */
    if ($partyType === 'vendor' && $sale->vendor) {
        $partyName = $sale->vendor->Party_name
            ?? $sale->vendor->business_name
            ?? $sale->vendor->name
            ?? 'Vendor';
    } elseif ($partyType === 'customer' && $sale->customer) {
        $partyName = $sale->customer->customer_name
            ?? $sale->customer->shop_name
            ?? $sale->customer->business_name
            ?? $sale->customer_shopname
            ?? 'Customer';
    } else {
        $partyName = $sale->customer_shopname ?? 'Walk-in';
    }

    /* ── Phone ── */
    if ($partyType === 'customer' && $sale->customer) {
        $phone = $sale->customer->phone
            ?? $sale->customer->mobile
            ?? $sale->customer_phone
            ?? '-';
    } elseif ($partyType === 'vendor' && $sale->vendor) {
        $phone = $sale->vendor->phone
            ?? $sale->vendor->mobile
            ?? $sale->customer_phone
            ?? '-';
    } else {
        $phone = $sale->customer_phone ?? '-';
    }

    /* ── Address ── */
    if ($partyType === 'customer' && $sale->customer) {
        $address = $sale->customer->address
            ?? $sale->customer->shop_address
            ?? $sale->customer_address
            ?? '-';
    } elseif ($partyType === 'vendor' && $sale->vendor) {
        $address = $sale->vendor->address
            ?? $sale->customer_address
            ?? '-';
    } else {
        $address = $sale->customer_address ?? '-';
    }

    /* ── Sale Date & Time ── */
    $saleDateTime = $sale->sale_date
        ? \Carbon\Carbon::parse($sale->sale_date)->format('d-m-Y  H:i')
        : \Carbon\Carbon::parse($sale->created_at)->format('d-m-Y  H:i');
@endphp

<tr>
    {{-- Job No --}}
    <td class="fw-bold text-center" style="white-space:nowrap;">
        {{ $sale->invoice_number }}
    </td>

    {{-- Party Type --}}
    <td class="text-center" style="white-space:nowrap;">
        <span class="badge {{ $badgeClass }}">{{ $partyLabel }}</span>
    </td>

    {{-- Sale Date & Time --}}
    <td class="text-center" style="white-space:nowrap;">
        {{ $saleDateTime }}
    </td>

    {{-- Customer / Party --}}
    <td style="white-space:nowrap;">
        {{ $partyName }}
    </td>

    {{-- Phone --}}
    <td class="text-center" style="white-space:nowrap;">
        {{ $phone }}
    </td>

    {{-- Address --}}
    <td style="max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;" title="{{ $address }}">
        {{ $address }}
    </td>

    {{-- Items --}}
    <td>
        @php
            $itemsArray = is_array($items) ? $items : [];
            $previewCount = 3;
        @endphp
        @if(count($itemsArray) === 0)
            <small class="text-muted">-</small>
        @else
            @php
                $preview  = implode(', ', array_slice($itemsArray, 0, $previewCount));
                $hasMore  = count($itemsArray) > $previewCount;
            @endphp
            <small>
                <span id="items-preview-{{ $sale->id }}">{{ $preview }}@if($hasMore) …@endif</span>
                @if($hasMore)
                    <span id="items-full-{{ $sale->id }}" style="display:none;">{{ implode(', ', $itemsArray) }}</span>
                    <a href="javascript:void(0)" class="ms-1 toggle-items-link" data-sale-id="{{ $sale->id }}">More</a>
                @endif
            </small>
        @endif
    </td>

    {{-- Net Amount --}}
    <td class="fw-bold text-end" style="white-space:nowrap;">
        {{ number_format($sale->net_amount, 2) }}
    </td>

    {{-- Status --}}
    <td class="text-center" style="white-space:nowrap;">
        @php
            $statusClass = match($sale->job_status) {
                'pending'   => 'bg-secondary',
                'ready'     => 'bg-success',
                'completed' => 'bg-primary',
                'paid'      => 'bg-info',
                default     => 'bg-warning text-dark',
            };
        @endphp
        <span class="badge {{ $statusClass }}">{{ ucfirst($sale->job_status) }}</span>
    </td>

    {{-- Actions --}}
    <td class="text-center" style="white-space:nowrap;">
        <a href="{{ route('show-local-sale', $sale->id) }}"
           class="btn btn-sm btn-info text-white">
            <i class="fa fa-eye"></i> View
        </a>

        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-secondary dropdown-toggle" data-bs-toggle="dropdown">
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
                @if($sale->sale_type == 'booking' && $sale->job_status != 'completed')
                <li>
                    <a class="dropdown-item text-success mark-complete-btn" href="javascript:void(0);"
                       data-sale-id="{{ $sale->id }}">
                        <i class="fa fa-check-circle me-2"></i>Mark Complete
                    </a>
                </li>
                @endif
                {{-- <li>
                    <button class="dropdown-item text-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#assignJobModal"
                            data-id="{{ $sale->id }}"
                            data-job="{{ $sale->invoice_number }}"
                            data-amount="{{ $sale->net_amount }}">
                        <i class="fa fa-user-plus me-2"></i>Assign Job
                    </button>
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
    <td colspan="10" class="text-center text-muted py-4">
        No Job Orders Found
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="d-flex justify-content-end mt-3 px-3 pb-3">
    {{ $Sales->links('pagination::bootstrap-4') }}
</div>

</div>
</div>

</div>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    /* ── Mark as Complete ── */
    document.querySelectorAll('.mark-complete-btn').forEach(btn => {
        btn.addEventListener('click', function () {
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
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({ icon: 'success', title: 'Success!', text: 'Order marked as completed', timer: 1500, showConfirmButton: false })
                                .then(() => location.reload());
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'Failed to mark as completed' });
                        }
                    })
                    .catch(() => Swal.fire({ icon: 'error', title: 'Error', text: 'An error occurred' }));
                }
            });
        });
    });

    /* ── Toggle Items expand/collapse ── */
    document.querySelectorAll('.toggle-items-link').forEach(link => {
        link.addEventListener('click', function () {
            const id      = this.dataset.saleId;
            const preview = document.getElementById('items-preview-' + id);
            const full    = document.getElementById('items-full-' + id);
            if (!preview || !full) return;
            if (full.style.display === 'none') {
                preview.style.display = 'none';
                full.style.display    = 'inline';
                this.textContent      = 'Less';
            } else {
                preview.style.display = 'inline';
                full.style.display    = 'none';
                this.textContent      = 'More';
            }
        });
    });

});
</script>

@include('admin_panel.include.footer_include')