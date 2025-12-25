@include('admin_panel.include.header_include')

<div class="main-wrapper">
@include('admin_panel.include.navbar_include')
@include('admin_panel.include.admin_sidebar_include')

<div class="page-wrapper">
<div class="content">

{{-- PAGE HEADER --}}
<div class="page-header">
    <div class="page-title">
        <h4>Product List</h4>
        <h6>Simple & Measurement Based Products</h6>
    </div>
    <div class="page-btn">
        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addProductModal">
            + Add Product
        </button>
    </div>
</div>

{{-- PRODUCT TABLE --}}
<div class="card">
<div class="card-body">
<table class="table table-bordered">
<thead>
<tr>
    <th>#</th>
    <th>Item Name</th>
    <th>Mode</th>
    <th>Measurement</th>
    <th>Purchase</th>
    <th>Sale</th>
</tr>
</thead>
<tbody>
@foreach($products as $k => $p)
<tr>
    <td>{{ $k+1 }}</td>
    <td>{{ $p->item_name }}</td>
    <td>{{ ucfirst($p->product_mode) }}</td>

    <td>
        @if($p->product_mode == 'measurements')
            {{ $p->height }} × {{ $p->width }} = {{ $p->area }} Sq.ft
        @else
            —
        @endif
    </td>

    <td>
        @if($p->product_mode == 'measurements')
            {{ $p->wholesale_price }} × {{ $p->area }}
            = <b>{{ $p->wholesale_price * $p->area }}</b>
        @else
            {{ $p->wholesale_price }}
        @endif
    </td>

    <td>
        @if($p->product_mode == 'measurements')
            {{ $p->retail_price }} × {{ $p->area }}
            = <b>{{ $p->retail_price * $p->area }}</b>
        @else
            {{ $p->retail_price }}
        @endif
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

{{-- ADD PRODUCT MODAL --}}
<div class="modal fade" id="addProductModal">
<div class="modal-dialog modal-lg">
<div class="modal-content">

<form method="POST" action="{{ route('store-product') }}">
@csrf

<div class="modal-header">
    <h5 class="modal-title">Add Product</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

{{-- ITEM NAME --}}
<div class="mb-3">
    <label class="form-label">Item Name</label>
    <input type="text" class="form-control" name="item_name" required>
</div>

{{-- PRODUCT MODE --}}
<div class="mb-3">
    <label class="form-label">Product Mode</label>
    <select class="form-control" name="product_mode" id="productMode">
        <option value="simple">Simple (Per Unit)</option>
        <option value="measurements">Measurements (Height × Width)</option>
    </select>
</div>

{{-- SIMPLE MODE --}}
<div id="simpleFields">
<div class="row">
    <div class="col-md-6 mb-3">
        <label>Purchase Price</label>
        <input type="number" step="0.01" class="form-control" name="wholesale_price">
    </div>
    <div class="col-md-6 mb-3">
        <label>Sale Price</label>
        <input type="number" step="0.01" class="form-control" name="retail_price">
    </div>
</div>
</div>

{{-- MEASUREMENTS MODE --}}
<div id="measurementFields" style="display:none">
<hr>

<div class="row">
    <div class="col-md-3 mb-3">
        <label>Height (ft)</label>
        <input type="number" step="0.01" id="height" name="height" class="form-control">
    </div>

    <div class="col-md-3 mb-3">
        <label>Width (ft)</label>
        <input type="number" step="0.01" id="width" name="width" class="form-control">
    </div>

    <div class="col-md-3 mb-3">
        <label>Area (Sq.ft)</label>
        <input type="number" id="area" name="area" readonly class="form-control">
    </div>
</div>

<div class="row">
    <div class="col-md-3 mb-3">
        <label>Purchase / Sq.ft</label>
        <input type="number" step="0.01" id="wholesale_price" name="wholesale_price" class="form-control">
    </div>

    <div class="col-md-3 mb-3">
        <label>Sale / Sq.ft</label>
        <input type="number" step="0.01" id="retail_price" name="retail_price" class="form-control">
    </div>

    <div class="col-md-3 mb-3">
        <label>Total Purchase</label>
        <input type="number" id="purchase_total" readonly class="form-control">
    </div>

    <div class="col-md-3 mb-3">
        <label>Total Sale</label>
        <input type="number" id="sale_total" readonly class="form-control">
    </div>
</div>
</div>

</div>

<div class="modal-footer">
    <button type="submit" class="btn btn-primary">Save Product</button>
</div>

</form>
</div>
</div>
</div>

@include('admin_panel.include.footer_include')

{{-- JS --}}
<script>
function resetMeasurementFields() {
    $('#height, #width, #area, #wholesale_price, #retail_price, #purchase_total, #sale_total')
        .val('');
}

function calculateMeasurement() {
    let h = parseFloat($('#height').val()) || 0;
    let w = parseFloat($('#width').val()) || 0;
    let area = h * w;

    if (area > 0) {
        $('#area').val(area.toFixed(2));
    } else {
        $('#area').val('');
    }

    let purchaseRate = parseFloat($('#wholesale_price').val()) || 0;
    let saleRate = parseFloat($('#retail_price').val()) || 0;

    $('#purchase_total').val(
        area > 0 && purchaseRate > 0 ? (area * purchaseRate).toFixed(2) : ''
    );

    $('#sale_total').val(
        area > 0 && saleRate > 0 ? (area * saleRate).toFixed(2) : ''
    );
}

$('#productMode').on('change', function () {
    if (this.value === 'measurements') {
        $('#measurementFields').show();
        $('#simpleFields').hide();
        resetMeasurementFields(); // 👈 IMPORTANT (blank fields)
    } else {
        $('#measurementFields').hide();
        $('#simpleFields').show();
    }
});

$('#height, #width, #wholesale_price, #retail_price').on('input', calculateMeasurement);
</script>
