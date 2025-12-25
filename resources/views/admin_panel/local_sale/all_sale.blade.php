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
</div>

<div class="card">
<div class="card-body p-0">

<div class="table-responsive">
<table class="table table-bordered align-middle mb-0">
<thead class="table-light text-center">
<tr>
    <th>Job No</th>
    <th>Date</th>
    <th>Party</th>
    <th>Phone</th>
    <th>Items</th>
    <th>Net Amount</th>
    <th>Status</th>
    <th width="220">Actions</th>
</tr>
</thead>

<tbody>
@forelse ($Sales as $sale)
@php
    $items = json_decode($sale->item, true);
@endphp
<tr>
    <td class="fw-bold text-center">{{ $sale->invoice_number }}</td>
    <td class="text-center">{{ \Carbon\Carbon::parse($sale->Date)->format('d-m-Y') }}</td>

    <td>
        {{ $sale->customer->shop_name
            ?? $sale->customer_shopname
            ?? 'Walk-in' }}
    </td>

    <td class="text-center">{{ $sale->customer_phone ?? '-' }}</td>

    <td>
        <small>{{ is_array($items) ? implode(', ', $items) : '-' }}</small>
    </td>

    <td class="fw-bold text-end">{{ number_format($sale->net_amount, 2) }}</td>

    <td class="text-center">
        <span class="badge
            {{ $sale->job_status == 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
            {{ ucfirst($sale->job_status) }}
        </span>
    </td>

    <td class="text-center">
        <a href="{{ route('show-local-sale', $sale->id) }}"
           class="btn btn-sm btn-info">View</a>

        <a href="{{ route('local.sale.invoice', $sale->id) }}"
           class="btn btn-sm btn-secondary">Invoice</a>

        <a href="{{ route('local.sale.edit', $sale->id) }}"
           class="btn btn-sm btn-primary">Edit</a>

        <a href="{{ route('local.sale.delete', $sale->id) }}"
           onclick="return confirm('Delete this job order?')"
           class="btn btn-sm btn-danger">Delete</a>

        <button class="btn btn-sm btn-success"
            data-bs-toggle="modal"
            data-bs-target="#assignJobModal"
            data-id="{{ $sale->id }}"
            data-job="{{ $sale->invoice_number }}"
            data-amount="{{ $sale->net_amount }}">
            Assign
        </button>
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
</div>

</div>
</div>

</div>
</div>
</div>

@include('admin_panel.include.footer_include')
