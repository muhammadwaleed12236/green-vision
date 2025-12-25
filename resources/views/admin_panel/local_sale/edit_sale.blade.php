@include('admin_panel.include.header_include')

<style>
.readonly-box{background:#f1f3f5;font-weight:600}
.table td,.table th{vertical-align:middle}
.qty-box{display:flex;gap:4px}
</style>

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

<h4 class="mb-3">✏️ Edit Job Order</h4>

<form method="POST" action="{{ route('local.sale.update',$original->id) }}">
@csrf
@method('PUT')

{{-- ================= PARTY ================= --}}
<div class="card mb-3">
<div class="card-body">
<div class="row g-3">

<div class="col-md-3">
<label>Party Type</label>
<input class="form-control readonly-box"
value="{{ ucfirst($original->party_type) }}" readonly>
</div>

<div class="col-md-3">
<label>Customer</label>
<input class="form-control readonly-box"
value="{{ $original->customer_shopname }}" readonly>
<input type="hidden" name="customer_id" value="{{ $original->customer_id }}">
</div>

<div class="col-md-3">
<label>Phone</label>
<input class="form-control readonly-box"
value="{{ $original->customer_phone }}" readonly>
</div>

<div class="col-md-3">
<label>Address</label>
<input class="form-control readonly-box"
value="{{ $original->customer_address }}" readonly>
</div>

</div>
</div>
</div>

{{-- ================= ITEMS ================= --}}
@php
$items   = json_decode($original->item, true) ?? [];
$heights = json_decode($original->height, true) ?? [];
$widths  = json_decode($original->width, true) ?? [];
$units   = json_decode($original->unit, true) ?? [];
$rates   = json_decode($original->rate, true) ?? [];
$qtys    = json_decode($original->qty, true) ?? [];
$amounts = json_decode($original->amount, true) ?? [];
@endphp

<div class="card mb-3">
<div class="card-body p-0">
<div class="table-responsive">

<table class="table table-bordered text-center mb-0">
<thead class="table-light">
<tr>
<th>Item</th>
<th>H</th>
<th>W</th>
<th>Unit</th>
<th>Area</th>
<th>Rate</th>
<th>Qty</th>
<th>Total</th>
</tr>
</thead>

<tbody id="saleTableBody">

@foreach($items as $i => $item)
<tr class="sale-row">

<td>
<input name="item[]" class="form-control readonly-box"
value="{{ $item }}" readonly>
</td>

<td>
<input name="height[]" class="form-control height"
value="{{ $heights[$i] ?? '' }}">
</td>

<td>
<input name="width[]" class="form-control width"
value="{{ $widths[$i] ?? '' }}">
</td>

<td>
<select name="unit[]" class="form-control unit">
<option value="ft" {{ ($units[$i] ?? '')=='ft'?'selected':'' }}>Feet</option>
<option value="inch" {{ ($units[$i] ?? '')=='inch'?'selected':'' }}>Inch</option>
</select>
</td>

<td>
<input class="form-control area readonly-box" readonly>
</td>

<td>
<input name="rate[]" class="form-control rate"
value="{{ $rates[$i] ?? 0 }}">
</td>

<td>
<div class="qty-box">
<button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>
<input name="qty[]" class="form-control qty text-center"
value="{{ $qtys[$i] ?? 1 }}">
<button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
</div>
</td>

<td>
<input name="amount[]" class="form-control item-total readonly-box"
value="{{ $amounts[$i] ?? 0 }}" readonly>
</td>

</tr>
@endforeach

</tbody>
</table>

</div>
</div>
</div>

{{-- ================= TOTAL ================= --}}
<div class="card mb-3">
<div class="card-body">
<div class="row g-3">

<div class="col-md-3">
<label>Grand Total</label>
<input id="grandTotal" name="grand_total"
class="form-control readonly-box"
value="{{ $original->grand_total }}" readonly>
</div>

<div class="col-md-3">
<label>Discount</label>
<input name="discount_value" class="form-control"
value="{{ $original->discount_value }}">
</div>

<div class="col-md-3">
<label>Advance</label>
<input name="advance_amount" class="form-control"
value="{{ $original->advance_amount }}">
</div>

<div class="col-md-3">
<label>Net Amount</label>
<input id="netAmount" name="net_amount"
class="form-control readonly-box"
value="{{ $original->net_amount }}" readonly>
</div>

</div>
</div>
</div>

<button class="btn btn-primary">Update Sale</button>

</form>
</div>
</div>
</div>

@include('admin_panel.include.footer_include')

{{-- ================= JS ================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
function toFeet(value, unit){
    if(!value) return 0;
    value = value.toString().trim();
    let parts = value.split('.');
    let whole = parseFloat(parts[0]) || 0;
    let decimal = parts[1] ? parseFloat(parts[1]) : 0;

    if(unit === 'ft'){
        return whole + (decimal / 12);
    }
    let inches = whole + (decimal / 25.4);
    return inches / 12;
}

function calcRow(row){
    let h = toFeet(row.find('.height').val(), row.find('.unit').val());
    let w = toFeet(row.find('.width').val(), row.find('.unit').val());
    let rate = parseFloat(row.find('.rate').val()) || 0;
    let qty  = parseFloat(row.find('.qty').val()) || 1;

    let area = h * w;
    row.find('.area').val(area ? area.toFixed(2) : '');
    row.find('.item-total').val((area * rate * qty).toFixed(2));

    calcGrand();
}

function calcGrand(){
    let total = 0;
    $('.item-total').each(function(){
        total += parseFloat(this.value) || 0;
    });
    $('#grandTotal').val(total.toFixed(2));
    $('#netAmount').val(total.toFixed(2));
}

$(document).on('input change','.height,.width,.unit,.rate,.qty',function(){
    calcRow($(this).closest('tr'));
});

$(document).on('click','.qty-plus',function(){
    let r=$(this).closest('tr');
    r.find('.qty').val(+r.find('.qty').val()+1);
    calcRow(r);
});

$(document).on('click','.qty-minus',function(){
    let r=$(this).closest('tr');
    r.find('.qty').val(Math.max(1,+r.find('.qty').val()-1));
    calcRow(r);
});

// 🔥 calculate all rows on load
$(document).ready(function(){
    $('.sale-row').each(function(){
        calcRow($(this));
    });
});
</script>
