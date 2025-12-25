@include('admin_panel.include.header_include')

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

{{-- ================= HEADER ================= --}}
<div class="page-header mb-3 d-flex justify-content-between">
    <div>
        <h4>Job Order Details</h4>
        <small class="text-muted">Invoice #{{ $sale->invoice_number }}</small>
    </div>

    <div class="d-flex gap-2">
        <a href="{{ route('local.sale.invoice', $sale->id) }}" class="btn btn-secondary btn-sm">Print</a>
        <a href="{{ route('local.sale.edit', $sale->id) }}" class="btn btn-primary btn-sm">Edit</a>
        <a href="{{ route('all-local-sale') }}" class="btn btn-dark btn-sm">Back</a>
    </div>
</div>

{{-- ================= PARTY INFO ================= --}}
<div class="card mb-3">
<div class="card-body">

<table class="table table-sm mb-0">
<tr>
    <th width="160">Date</th>
    <td>{{ \Carbon\Carbon::parse($sale->Date)->format('d-m-Y') }}</td>
</tr>
<tr>
    <th>Party Type</th>
    <td>{{ ucfirst($sale->party_type) }}</td>
</tr>
<tr>
    <th>Party Name</th>
    <td>
        {{ $sale->customer->shop_name
            ?? $sale->customer_shopname
            ?? 'Walk-in Customer' }}
    </td>
</tr>
<tr>
    <th>Phone</th>
    <td>{{ $sale->customer_phone ?? '-' }}</td>
</tr>
<tr>
    <th>Address</th>
    <td>{{ $sale->customer_address ?? '-' }}</td>
</tr>
</table>

</div>
</div>

{{-- ================= ITEMS ================= --}}
@php
    $items   = json_decode($sale->item, true) ?? [];
    $heights = json_decode($sale->height, true) ?? [];
    $widths  = json_decode($sale->width, true) ?? [];
    $units   = json_decode($sale->unit, true) ?? [];
    $qtys    = json_decode($sale->qty, true) ?? [];
    $amounts = json_decode($sale->amount, true) ?? [];
@endphp

<div class="card mb-3">
<div class="card-header fw-bold">Job Items</div>

<div class="table-responsive">
<table class="table table-bordered text-center mb-0">
<thead class="table-light">
<tr>
    <th>#</th>
    <th>Item</th>
    <th>Height</th>
    <th>Width</th>
    <th>Unit</th>
    <th>Qty</th>
    <th>Total</th>
</tr>
</thead>

<tbody>
@forelse ($items as $i => $item)
<tr>
    <td>{{ $i + 1 }}</td>
    <td>{{ $item }}</td>
    <td>{{ $heights[$i] ?? '-' }}</td>
    <td>{{ $widths[$i] ?? '-' }}</td>
    <td>{{ strtoupper($units[$i] ?? '-') }}</td>
    <td>{{ $qtys[$i] ?? 1 }}</td>
    <td class="fw-bold">{{ number_format($amounts[$i] ?? 0, 2) }}</td>
</tr>
@empty
<tr>
    <td colspan="7" class="text-muted">No Items Found</td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- ================= PAYMENT ================= --}}
<div class="card mb-3">
<div class="card-header fw-bold">Payment Summary</div>

<div class="card-body">
<table class="table table-sm mb-0">
<tr>
    <th width="200">Grand Total</th>
    <td>{{ number_format($sale->grand_total, 2) }}</td>
</tr>
<tr>
    <th>Discount</th>
    <td>{{ number_format($sale->discount_value, 2) }}</td>
</tr>
<tr>
    <th>Advance</th>
    <td>{{ number_format($sale->advance_amount ?? 0, 2) }}</td>
</tr>
<tr>
    <th>Remaining</th>
    <td class="fw-bold">{{ number_format($sale->remaining_amount ?? 0, 2) }}</td>
</tr>
</table>
</div>
</div>

{{-- ================= ASSIGN JOB ================= --}}
<div class="card mb-3">
<div class="card-header fw-bold d-flex justify-content-between">
    <span>Job Assignment</span>
    <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#assignJobModal">
        Assign Job
    </button>
</div>

<div class="card-body p-0">
<table class="table table-bordered mb-0 text-center">
<thead class="table-light">
<tr>
    <th>Worker</th>
    <th>Type</th>
    <th>Amount</th>
    <th>Status</th>
</tr>
</thead>
<tbody>
<tr>
    <td colspan="4" class="text-muted">No workers assigned yet</td>
</tr>
</tbody>
</table>
</div>
</div>

{{-- ================= ASSIGN MODAL ================= --}}
<div class="modal fade" id="assignJobModal">
<div class="modal-dialog modal-md">
<div class="modal-content">

<div class="modal-header">
    <h5 class="modal-title">Assign Job</h5>
    <button class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="mb-3 p-2 bg-light border rounded">
    <strong>Job No:</strong> {{ $sale->invoice_number }} <br>
    <strong>Net Amount:</strong> {{ number_format($sale->net_amount,2) }}
</div>

<div class="mb-3">
    <label>Worker</label>
    <select class="form-control" id="workerType">
        <option value="">Select</option>
        <option value="salary">Ali (Salary)</option>
        <option value="contractor">Ahmed (Contractor)</option>
    </select>
</div>

<div class="mb-3 d-none" id="jobAmountBox">
    <label>Contractor Amount</label>
    <input type="number" class="form-control">
</div>

<div class="mb-3">
    <label>Notes</label>
    <textarea class="form-control" rows="2"></textarea>
</div>

</div>

<div class="modal-footer">
    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button class="btn btn-primary">Assign</button>
</div>

</div>
</div>
</div>

</div>
</div>
</div>

@include('admin_panel.include.footer_include')

<script>
document.getElementById('workerType')?.addEventListener('change', function () {
    document.getElementById('jobAmountBox')
        .classList.toggle('d-none', this.value !== 'contractor');
});
</script>
