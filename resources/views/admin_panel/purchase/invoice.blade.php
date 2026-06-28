@include('admin_panel.include.header_include')

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

<div class="card p-4" id="invoice">
<div class="card-body">

{{-- ================= HEADER ================= --}}
<div class="row align-items-center mb-3" style="border-bottom:3px solid #000">
    <div class="col-md-4 d-flex align-items-center">
        @if($appSettings['company_logo'])
            <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" style="max-width: 180px;">
        @endif
    </div>
    <div class="col-md-4 text-center">
        <small>{{ $appSettings['company_address'] }}</small><br>
        <small>{{ $appSettings['company_phone'] }}</small>
    </div>
    <div class="col-md-4 text-end">
        <h6 class="fw-bold">PURCHASE INVOICE</h6>
    </div>
</div>

{{-- ================= PARTY INFO ================= --}}
<div class="row mb-3">
    <div class="col-md-6">
        <strong>Invoice #:</strong> {{ $purchase->invoice_number }} <br>
        <strong>Date:</strong> {{ $purchase->purchase_date }}
    </div>
    <div class="col-md-6 text-end">
        <strong>Vendor:</strong> <span class="text-capitalize">{{ $purchase->vendor->Party_name ?? '' }}</span> <br>
        <strong>Party Code:</strong> {{ $purchase->party_code }}
    </div>
</div>

{{-- ================= ITEMS TABLE ================= --}}
<table class="table table-bordered">
<thead class="table-light text-center">
<tr>
    <th>Item</th>
    <th>UOM</th>
    <th>Quantity</th>
    <th>Rate</th>
    <th>Amount</th>
</tr>
</thead>

<tbody>
@php
    $invItems = is_array($purchase->item) ? $purchase->item : (json_decode($purchase->item, true) ?? []);
    $invModes = is_array($purchase->product_mode) ? $purchase->product_mode : (json_decode($purchase->product_mode, true) ?? []);
    $invPcs = is_array($purchase->pcs) ? $purchase->pcs : (json_decode($purchase->pcs, true) ?? []);
    $invRates = is_array($purchase->rate) ? $purchase->rate : (json_decode($purchase->rate, true) ?? []);
    $invAmounts = is_array($purchase->amount) ? $purchase->amount : (json_decode($purchase->amount, true) ?? []);
@endphp
@foreach($invItems as $i => $item)
<tr>
    <td class="text-center">{{ $item }}</td>
    <td class="text-center">{{ empty($invModes[$i]) ? '-' : $invModes[$i] }}</td>
    <td class="text-center">{{ ($invPcs[$i] ?? 0) == 0 ? '-' : $invPcs[$i] }}</td>
    <td class="text-center">{{ number_format($invRates[$i] ?? 0, 2) }}</td>
    <td class="text-center">{{ number_format($invAmounts[$i] ?? 0, 2) }}</td>
</tr>
@endforeach
</tbody>
</table>

{{-- ================= TOTALS ================= --}}

<table class="table table-bordered w-50 ms-auto">
{{-- <tr>
    <th class="text-end">Opening Balance</th>
    <td class="text-end">{{ number_format($openingBalance,2) }}</td>
</tr> --}}
<tr>
    <th class="text-end">Previous Balance</th>
    <td class="text-end">{{ number_format($previousBalance,2)  }}</td>
</tr>
<tr>
    <th class="text-end">Current Purchase</th>
    <td class="text-end">{{ number_format($netTotal,2) }}</td>
</tr>
<tr>
    <th class="text-end">Closing Balance</th>
    <td class="text-end fw-bold">{{ number_format($closingBalance,2) }}</td>
</tr>
</table>

{{-- ================= FOOTER ================= --}}
<div class="row mt-4">
    <div class="col-md-6">
            <span class="ms-1"> ________________</span> <br>
        <strong>Receiver Signature</strong>
    </div>
    <div class="col-md-6 text-end">
            <span class="text-end"> ________________ </span><br

        <strong>Authorized Signature</strong>
    </div>
</div>

</div>
</div>

<div class="text-end mt-3 no-print">
<button onclick="window.print()" class="btn btn-danger">
<i class="fa fa-print"></i> Print Invoice
</button>
</div>

</div>
</div>
</div>

@include('admin_panel.include.footer_include')

<style>
@media print {
    body * { visibility: hidden; }
    #invoice, #invoice * { visibility: visible; }
    #invoice { position:absolute; left:0; top:0; width:100%; }
    .no-print { display:none; }
}
</style>
