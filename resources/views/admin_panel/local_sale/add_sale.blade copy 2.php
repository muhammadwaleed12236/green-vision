@include('admin_panel.include.header_include')

<style>
    .readonly-box {
        background: #f1f3f5;
        font-weight: 600;
    }

    .table td,
    .table th {
        vertical-align: middle
    }

    .qty-box {
        display: flex;
        gap: 4px
    }

    /* responsive safety */
    .page-wrapper,
    .content {
        width: 100%;
        overflow-x: hidden
    }

    .table-responsive {
        overflow-x: auto
    }

    @media(max-width:992px) {
        .qty-box {
            flex-direction: column
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <h4 class="mb-3">🧾 Job Order</h4>

            <form method="POST" action="{{ route('store-local-sale') }}">
                @csrf

                {{-- ================= PARTY ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label>Party Type</label>
                                <select id="partyType" name="party_type" class="form-control">
                                    <option value="customer">Customer</option>
                                    <option value="vendor">Vendor</option>
                                    <option value="walkin">Walk-In</option>
                                </select>
                            </div>

                            <div class="col-md-3" id="customerBox">
                                <label>Customer</label>
                                <select class="form-control" name="customer_id" id="customer">
                                    <option value="">Select</option>
                                    @foreach ($Customers as $c)
                                        <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                            data-address="{{ $c->address }}">
                                            {{ $c->shop_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 d-none" id="vendorBox">
                                <label>Vendor</label>
                                <select class="form-control" name="vendor_id" id="vendor">
                                    <option value="">Select</option>
                                    @foreach ($Vendors as $v)
                                        <option value="{{ $v->id }}" data-phone="{{ $v->Party_phone }}"
                                            data-address="{{ $v->Party_address }}">
                                            {{ $v->Party_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- READONLY CONTACT --}}
                            <div class="col-md-3 readonly-wrap">
                                <label>Phone</label>
                                <input id="phone" class="form-control readonly-box" readonly>
                            </div>

                            <div class="col-md-3 readonly-wrap">
                                <label>Address</label>
                                <input id="address" class="form-control readonly-box" readonly>
                            </div>

                            {{-- WALK IN --}}
                            <div class="col-12 d-none" id="walkinBox">
                                <div class="row g-3">
                                    <div class="col-md-4"><input name="walkin_name" class="form-control"
                                            placeholder="Customer Name"></div>
                                    <div class="col-md-4"><input name="walkin_phone" class="form-control"
                                            placeholder="Phone"></div>
                                    <div class="col-md-4"><input name="walkin_address" class="form-control"
                                            placeholder="Address"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- ================= ITEMS ================= --}}
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
                                        <th>Area (ft²)</th>
                                        <th>Rate</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th></th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">
                                    <tr class="sale-row">

                                        <td><input name="item_name[]" class="form-control"></td>

                                        <td><input name="height[]" class="form-control height"></td>

                                        <td><input name="width[]" class="form-control width"></td>

                                        <td>
                                            <select name="unit[]" class="form-control unit">
                                                <option value="ft" selected>Feet</option>
                                                <option value="inch">Inch</option>
                                            </select>
                                        </td>

                                        <td><input class="form-control area readonly-box" readonly></td>

                                        <td><input name="rate[]" class="form-control rate"></td>

                                        <td>
                                            <div class="qty-box">
                                                <button type="button"
                                                    class="btn btn-sm btn-secondary qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty text-center"
                                                    value="1">
                                                <button type="button"
                                                    class="btn btn-sm btn-secondary qty-plus">+</button>
                                            </div>
                                        </td>

                                        <td><input name="amount[]" class="form-control item-total readonly-box"
                                                readonly></td>

                                        <td>
                                            <button type="button" class="btn btn-success btn-sm add-row">+</button>
                                            <button type="button" class="btn btn-danger btn-sm remove-row">×</button>
                                        </td>

                                    </tr>
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
                                <label>Gross Total</label>
                                <input id="grandTotal" class="form-control readonly-box" readonly>
                            </div>

                            <div class="col-md-3">
                                <label>Discount</label>
                                <input name="gross_discount" class="form-control" value="0">
                            </div>

                            <div class="col-md-3">
                                <label>Advance</label>
                                <input id="advance" name="advance_amount" class="form-control">
                            </div>

                            <div class="col-md-3">
                                <label>Remaining</label>
                                <input id="remaining" class="form-control readonly-box" readonly>
                            </div>

                        </div>
                    </div>
                </div>

                <input type="hidden" name="net_amount" id="netAmount">
                <button class="btn btn-success">Save Job Order</button>

            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    /* PARTY SWITCH */
    $('#partyType').on('change', function() {
        let t = this.value;

        // hide all
        $('#customerBox,#vendorBox,#walkinBox').addClass('d-none');
        $('.readonly-wrap').addClass('d-none');
        $('#phone,#address').val('');

        if (t === 'customer') {
            $('#customerBox').removeClass('d-none');
            $('.readonly-wrap').removeClass('d-none');
        }

        if (t === 'vendor') {
            $('#vendorBox').removeClass('d-none');
            $('.readonly-wrap').removeClass('d-none');
        }

        if (t === 'walkin') {
            $('#walkinBox').removeClass('d-none');
            // phone & address remain hidden ✔
        }
    });

    $('#partyType').trigger('change');

    /* AUTO FILL */
    $('#customer').on('change', function() {
        let o = $('option:selected', this);
        $('#phone').val(o.data('phone') || '');
        $('#address').val(o.data('address') || '');
    });
    $('#vendor').on('change', function() {
        let o = $('option:selected', this);
        $('#phone').val(o.data('phone') || '');
        $('#address').val(o.data('address') || '');
    });

    /* SIZE PARSER */
   function toFeet(value, unit) {
    if (!value) return 0;

    value = value.toString().trim();
    let parts = value.split('.');

    if (unit === 'ft') {
        // Feet and inches ko convert karte hue
        let feet = parseInt(parts[0]) || 0;
        let inches = parseFloat(parts[1]) || 0;  // fractional inches ke liye parseFloat

        return feet + (inches / 12);
    } else {
        // Inches ko feet mein convert karte hue
        let inches = parseFloat(parts[0]) || 0;  // fractional inches ke liye parseFloat
        return inches / 12;
    }
}




    /* CALC */
   function calcRow(r) {

    let unit = r.find('.unit').val(); // ft | inch

    let h = toFeet(r.find('.height').val(), unit);
    let w = toFeet(r.find('.width').val(), unit);

    let rate = parseFloat(r.find('.rate').val()) || 0;
    let qty  = parseFloat(r.find('.qty').val())  || 1;

    let area = h * w;

    // FINAL AREA (AS REQUIRED)
    r.find('.area').val(area ? area.toFixed(2) : '');

    r.find('.item-total').val((area * rate * qty).toFixed(2));

    calcGrand();
}



    $(document).on('input change', '.height,.width,.unit,.rate,.qty', e => {
        calcRow($(e.target).closest('tr'));
    });

    $(document).on('click', '.qty-plus', e => {
        let r = $(e.target).closest('tr');
        r.find('.qty').val(+r.find('.qty').val() + 1);
        calcRow(r);
    });
    $(document).on('click', '.qty-minus', e => {
        let r = $(e.target).closest('tr');
        r.find('.qty').val(Math.max(1, +r.find('.qty').val() - 1));
        calcRow(r);
    });

    /* ROWS */
    $('.add-row').click(() => {
        let r = $('.sale-row:first').clone();
        r.find('input').val('');
        r.find('.qty').val(1);
        $('#saleTableBody').append(r);
    });
    $(document).on('click', '.remove-row', e => {
        if ($('.sale-row').length > 1) {
            $(e.target).closest('tr').remove();
            calcGrand();
        }
    });

    /* GRAND */
    function calcGrand() {
        let g = 0;
        $('.item-total').each((_, e) => g += +e.value || 0);
        let d = +$('[name="gross_discount"]').val() || 0;
        let net = g - d;
        $('#grandTotal').val(g.toFixed(2));
        $('#netAmount').val(net.toFixed(2));
        let adv = +$('#advance').val() || 0;
        $('#remaining').val((net - adv).toFixed(2));
    }
    $('#advance,[name="gross_discount"]').on('input', calcGrand);
</script>












<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Area Calculator</title>
<style>
  body { font-family: Arial; padding: 20px }
  input, select, button { padding: 6px; margin: 5px }
</style>
</head>
<body>

<h3>Area Calculator (sq.ft)</h3>

<select id="unit">
  <option value="inches">Inches (decimal = mm)</option>
  <option value="feet">Feet (decimal = inches)</option>
</select>
<br>

<input type="number" step="0.1" id="v1" placeholder="Value 1">
<input type="number" step="0.1" id="v2" placeholder="Value 2">
<br>

<button onclick="calc()">Calculate</button>

<h4 id="result"></h4>

<script>
function splitValue(val, unit) {
  let parts = val.toString().split('.');
  let whole = parseInt(parts[0]);
  let decimal = parts[1] ? parseInt(parts[1]) : 0;

  if (unit === 'inches') {
    // decimal = mm
    return whole + (decimal / 25.4);
  } else {
    // decimal = inches
    return whole + (decimal / 12);
  }
}

function calc() {
  let unit = document.getElementById('unit').value;
  let a = document.getElementById('v1').value;
  let b = document.getElementById('v2').value;

  if (!a || !b) return alert('Enter values');

  let v1 = splitValue(a, unit);
  let v2 = splitValue(b, unit);

  let area = unit === 'inches'
    ? (v1 * v2) / 144
    : v1 * v2;

  document.getElementById('result').innerText =
    "Area = " + area.toFixed(2) + " sq.ft";
}
</script>


</body>
</html>
