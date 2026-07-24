@include('admin_panel.include.header_include')

<style>
    .readonly-box {
        background: #f1f3f5;
        font-weight: 600
    }

    .table td,
    .table th {
        vertical-align: middle
    }

    .qty-box {
        display: flex;
        gap: 4px
    }

    .autocomplete-list {
        position: fixed;
        z-index: 99999;
        background: #fff;
        border: 1px solid #ddd;
        max-height: 220px;
        overflow-y: auto;
        border-radius: 4px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }
    .autocomplete-item {
        padding: 8px 12px;
        cursor: pointer;
        border-bottom: 1px solid #eee;
    }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover { background: #e9ecef; }

    /* Row Action Buttons */
    .sale-row .add-row,
    .sale-row .remove-row {
        width: 32px;
        height: 32px;
        padding: 0;
        font-size: 14px;
        line-height: 1;
        border-radius: 4px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .sale-row .add-row {
        margin-right: 4px;
    }
    .sale-row .remove-row:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <form method="POST" action="{{ route('local.sale.update', $original->id) }}">
                @csrf
                @method('PUT')

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0">✏️ Edit Job Order</h4>
                    <div class="d-flex gap-3 align-items-center">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_estimate" value="estimate" {{ old('sale_type', $original->sale_type) == 'estimate' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_estimate">Estimate</label>

                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_sale" value="sale" {{ old('sale_type', $original->sale_type) == 'sale' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_sale">Sale</label>

                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_booking" value="booking" {{ old('sale_type', $original->sale_type) == 'booking' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_booking">Booking</label>
                        </div>
                        <div style="max-width: 260px;">
                            <label class="small text-muted d-block mb-0">Sale Date & Time</label>
                            <input type="datetime-local" name="sale_date" class="form-control form-control-sm" value="{{ old('sale_date', \Carbon\Carbon::parse($original->sale_date)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </div>

                {{-- ================= PARTY ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">

                        @php
                            $cloneEstimate = $original; // Reuse add_sale logic
                        @endphp
                        <div class="col-md-3">
                            <label>Party Type</label>
                            <select id="partyType" name="party_type" class="form-control">
                                <option value="customer" {{ (old('party_type') ?? ($cloneEstimate?->party_type ?? '')) == 'customer' ? 'selected' : '' }}>Customer</option>
                                <option value="vendor" {{ (old('party_type') ?? ($cloneEstimate?->party_type ?? '')) == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                <option value="walkin" {{ (old('party_type') ?? ($cloneEstimate?->party_type ?? '')) == 'walkin' ? 'selected' : '' }}>Walk-In</option>
                            </select>
                        </div>

                        <div class="col-md-3 party-box" id="customerBox">
                            <label>Customer</label>
                            <select class="form-control search" name="customer_id" id="customer">
                                <option value="">Select</option>
                                @foreach ($Customers as $c)
                                    <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                        data-address="{{ $c->address }}"
                                        {{ (old('customer_id') ?? ($cloneEstimate?->customer_id ?? '')) == $c->id ? 'selected' : '' }}>
                                        {{ $c->customer_name ?? $c->shop_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 party-box d-none" id="vendorBox">
                            <label>Vendor</label>
                            <select class="form-control search" name="vendor_id" id="vendor">
                                <option value="">Select</option>
                                @foreach ($Vendors as $v)
                                    <option value="{{ $v->id }}" data-phone="{{ $v->Party_phone }}"
                                        data-address="{{ $v->Party_address }}"
                                        {{ (old('vendor_id') ?? ($cloneEstimate?->vendor_id ?? '')) == $v->id ? 'selected' : '' }}>
                                        {{ $v->Party_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3 readonly-wrap">
                            <label>Phone</label>
                            <input id="phone" class="form-control readonly-box" readonly>
                        </div>

                        <div class="col-md-3 readonly-wrap mt-3">
                            <label>Address</label>
                            <input id="address" class="form-control readonly-box" readonly>
                        </div>

                        <div class="col-md-3 d-none mt-3" id="walkinName">
                            <label>Name</label>
                            <input name="walkin_name" class="form-control" value="{{ old('walkin_name') ?? ($cloneEstimate?->party_type === 'walkin' ? $cloneEstimate?->customer_shopname : '') }}">
                        </div>

                        <div class="col-md-3 d-none mt-3" id="walkinPhone">
                            <label>Phone</label>
                            <input name="walkin_phone" class="form-control" value="{{ old('walkin_phone') ?? ($cloneEstimate?->party_type === 'walkin' ? $cloneEstimate?->customer_phone : '') }}">
                        </div>

                        <div class="col-md-3 d-none mt-3" id="walkinAddress">
                            <label>Address</label>
                            <input name="walkin_address" class="form-control" value="{{ old('walkin_address') ?? ($cloneEstimate?->party_type === 'walkin' ? $cloneEstimate?->customer_address : '') }}">
                        </div>

                        </div>
                    </div>
                </div>

                {{-- ================= ITEMS ================= --}}
                @php
                    $items = json_decode($original->item, true) ?? [];
                    $heights = json_decode($original->height, true) ?? [];
                    $widths = json_decode($original->width, true) ?? [];
                    $units = json_decode($original->unit, true) ?? [];
                    $rates = json_decode($original->rate, true) ?? [];
                    $qtys = json_decode($original->qty, true) ?? [];
                    $amounts = json_decode($original->amount, true) ?? [];
                @endphp

                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">

                            <table class="table table-bordered text-center mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%;">#</th>
                                        <th style="width: 30%;">Product Name</th>
                                        <th style="width: 10%;">Avail. Stock</th>
                                        <th style="width: 15%;">Quantity</th>
                                        <th style="width: 10%;">Unit</th>
                                        <th style="width: 12%;">Price/unit</th>
                                        <th style="width: 10%;">amount</th>
                                        <th style="width: 8%;">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">

                                    @foreach($items as $i => $item)
                                        <tr class="sale-row">
                                            <td>
                                                <span class="row-index">{{ $i + 1 }}</span>
                                            </td>
                                            <td style="position:relative;">
                                                <div class="input-group input-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary mode-toggle px-2" title="Toggle Search/Manual" tabindex="-1">
                                                        <i class="fas fa-search mode-icon"></i>
                                                    </button>
                                                    <input name="item_name[]" class="form-control item-input" value="{{ $item }}" autocomplete="off" placeholder="Search Product" data-mode="search">
                                                </div>
                                                <div class="autocomplete-list d-none"></div>
                                            </td>
                                            <td>
                                                @php $prod = \App\Models\Product::where('item_name', $item)->first(); $avail = $prod ? $prod->initial_stock : 0; @endphp
                                                <input type="text" name="avail_stock[]" class="form-control avail-stock p-1 text-center readonly-box" value="{{ $avail }}" readonly tabindex="-1" placeholder="-">
                                            </td>
                                            <td>
                                                <div class="qty-box">
                                                    <button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>
                                                    <input name="qty[]" class="form-control qty text-center" value="{{ $qtys[$i] ?? 1 }}">
                                                    <button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <input name="unit[]" class="form-control unit text-center" value="{{ $units[$i] ?? '' }}" readonly>
                                            </td>
                                            <td>
                                                <input name="rate[]" class="form-control rate" value="{{ $rates[$i] ?? 0 }}">
                                            </td>
                                            <td>
                                                <input name="amount[]" class="form-control item-total" value="{{ $amounts[$i] ?? 0 }}" readonly>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-success btn-sm add-row" title="Add row">
                                                    <i class="fas fa-plus"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm remove-row" title="Delete row">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- template for JS-added rows -->
                                    <tr class="sale-row d-none" id="rowTemplate">
                                        <td><span class="row-index"></span></td>
                                        <td style="position:relative;">
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-outline-secondary mode-toggle px-2" title="Toggle Search/Manual" tabindex="-1">
                                                    <i class="fas fa-search mode-icon"></i>
                                                </button>
                                                <input name="item_name[]" class="form-control item-input" autocomplete="off" placeholder="Search Product" data-mode="search">
                                            </div>
                                            <div class="autocomplete-list d-none"></div>
                                        </td>
                                        <td>
                                            <input type="text" name="avail_stock[]" class="form-control avail-stock p-1 text-center readonly-box" value="" readonly tabindex="-1" placeholder="-">
                                        </td>
                                        <td>
                                            <div class="qty-box">
                                                <button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty text-center" value="1">
                                                <button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="unit[]" class="form-control unit text-center" value="" readonly>
                                        </td>
                                        <td>
                                            <input name="rate[]" class="form-control rate" value="0">
                                        </td>
                                        <td>
                                            <input name="amount[]" class="form-control item-total" value="0" readonly>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm add-row" title="Add row">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-sm remove-row" title="Delete row">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                {{-- ================= DELIVERY & PAYMENT ================= --}}
                <div class="card mb-3" id="deliveryPaymentPanel">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold text-primary">Delivery & Payment Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $original->delivery_date) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Notify Before (Days)</label>
                                <input type="number" name="notify_days_before" class="form-control" value="{{ old('notify_days_before', $original->notify_days_before ?? 2) }}" min="1" max="30">
                                <small class="text-muted">System will notify you X days before delivery</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= TOTAL ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <label>Grand Total</label>
                                <input id="grandTotal" name="grand_total" class="form-control readonly-box"
                                    value="{{ $original->grand_total }}" readonly>
                            </div>

                            <div class="col-md-3" id="discountContainer">
                                <label>Discount</label>
                                <input name="gross_discount" class="form-control"
                                    value="{{ $original->discount_value }}">
                            </div>

                            <div class="col-md-3" id="advanceContainer">
                                <label id="advanceLabel">Advance</label>
                                <input id="advance" name="advance_amount" class="form-control"
                                    value="{{ $original->advance_amount }}">
                            </div>

                            <div class="col-md-3" id="remainingContainer">
                                <label>Remaining</label>
                                <input id="remaining" class="form-control readonly-box"
                                    value="{{ $original->remaining_amount }}" readonly>
                            </div>

                        </div>
                    </div>
                </div>

                <input type="hidden" name="net_amount" id="netAmount" value="{{ $original->net_amount }}">

                <button class="btn btn-primary"><i class="fa fa-save me-1"></i> Update Invoice</button>

            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Add these two libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    function calcRow(row) {
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let qty = parseFloat(row.find('.qty').val());
        let avail = parseFloat(row.find('.avail-stock').val());

        if (isNaN(qty) || qty < 0) qty = 0;

        let saleType = $('input[name="sale_type"]:checked').val();
        if (saleType === 'sale' && !isNaN(avail)) {
            if (qty > avail) {
                row.find('.qty').addClass('border-danger text-danger');
            } else {
                row.find('.qty').removeClass('border-danger text-danger');
            }
        } else {
            row.find('.qty').removeClass('border-danger text-danger');
        }

        let total = rate * qty;
        row.find('.item-total').val(total.toFixed(2));

        calcGrand();
    }

    function calcGrand() {
        let total = 0;
        $('.item-total').each(function () {
            total += parseFloat(this.value) || 0;
        });
        let discount = parseFloat($('[name="gross_discount"]').val()) || 0;
        let net = total - discount;
        $('#grandTotal').val(total.toFixed(2));
        $('#netAmount').val(net.toFixed(2));
        let adv = parseFloat($('#advance').val()) || 0;
        $('#remaining').val((net - adv).toFixed(2));
    }

    $(document).on('input change', '.rate,.qty', function () {
        calcRow($(this).closest('tr'));
    });

    $(document).on('input change', '.item-total, [name="gross_discount"], #advance', function () {
        calcGrand();
    });

    $(document).on('click', '.qty-plus', function () {
        let r = $(this).closest('tr');
        r.find('.qty').val(+r.find('.qty').val() + 1);
        calcRow(r);
    });

    $(document).on('click', '.qty-minus', function () {
        let r = $(this).closest('tr');
        r.find('.qty').val(Math.max(0, +r.find('.qty').val() - 1));
        calcRow(r);
    });

    // Row Input Mode Toggle
    $(document).on('click', '.mode-toggle', function() {
        let btn = $(this);
        let icon = btn.find('.mode-icon');
        let input = btn.siblings('.item-input');

        if (input.attr('data-mode') === 'search') {
            input.attr('data-mode', 'manual');
            icon.removeClass('fa-search').addClass('fa-keyboard');
            btn.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
            input.attr('placeholder', 'Manual Entry');
            input.closest('td').find('.autocomplete-list').addClass('d-none');
        } else {
            input.attr('data-mode', 'search');
            icon.removeClass('fa-keyboard').addClass('fa-search');
            btn.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
            input.attr('placeholder', 'Search Product');
        }
        input.focus();
    });

    // Single global autocomplete dropdown
    let $acList = $('<div class="autocomplete-list d-none"></div>').appendTo('body');

    function fetchProducts(input, q) {
        let row = input.closest('tr');
        if (input.attr('data-mode') === 'manual') { $acList.addClass('d-none'); return; }
        $acList.data('row', row);
        $.ajax({
            url: "{{ route('get.items') }}",
            type: "GET",
            data: { q: q },
            success: function (res) {
                if (!Array.isArray(res) || res.length === 0) { $acList.addClass('d-none'); return; }
                let rect = input[0].getBoundingClientRect();
                $acList.css({ left: rect.left + 'px', top: rect.bottom + 'px', width: input.outerWidth() + 'px' });
                $acList.empty().removeClass('d-none');
                res.forEach(it => { $('<div class="autocomplete-item"></div>').text(it.item_name).data('item', it).appendTo($acList); });
            },
            error: function () { $acList.addClass('d-none'); }
        });
    }

    // On focus: show all products; on input: filter by typed query
    $(document).on('focus', '.item-input', function () { fetchProducts($(this), ''); });
    $(document).on('input', '.item-input', function () { fetchProducts($(this), $(this).val().trim()); });

    // Hide autocomplete when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.item-input, .autocomplete-list').length) {
            $acList.addClass('d-none');
        }
    });

    // Select item from autocomplete
    $(document).on('click', '.autocomplete-item', function () {
        let it = $(this).data('item');
        let row = $acList.data('row');

        if (!row || !row.length) return;

        row.find('.item-input').val(it.item_name);

        let price = parseFloat(it.retail_price) || parseFloat(it.wholesale_price) || 0;
        row.find('.rate').val(price);
        row.find('.avail-stock').val(it.initial_stock !== null && it.initial_stock !== undefined ? it.initial_stock : 0);

        $acList.addClass('d-none');

        calcRow(row);
    });

    // Reposition autocomplete on scroll/resize
    $(window).on('scroll resize', function () {
        if ($acList.hasClass('d-none')) return;
        let row = $acList.data('row');
        if (row && row.length) {
            let input = row.find('.item-input');
            let rect = input[0].getBoundingClientRect();
            $acList.css({
                left: rect.left + 'px',
                top: rect.bottom + 'px'
            });
        }
    });

    // calculate all rows on load
    $(document).ready(function () {
        calcGrand();
    });

    // ========== ROW ACTIONS ==========

    function updateRowNumbers() {
        $('#saleTableBody .sale-row').each(function (index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function createRowHtml() {
        let r = $('#rowTemplate').clone().removeClass('d-none').removeAttr('id');
        r.find('.qty').removeClass('border-danger text-danger');
        return r;
    }

    // Add row (insert after current)
    $(document).on('click', '.add-row', function () {
        let currentRow = $(this).closest('tr.sale-row');
        let newRow = createRowHtml();
        currentRow.after(newRow);
        updateRowNumbers();
        calcGrand();
        newRow.find('.item-input').focus();
    });

    // Remove row
    $(document).on('click', '.remove-row', function () {
        let rowCount = $('#saleTableBody .sale-row').length;
        if (rowCount > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
            calcGrand();
        }
    });

    // Enter key: auto-add new row when pressing Enter in last row
    $(document).on('keydown', '.sale-row input, .sale-row select', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let currentRow = $(this).closest('tr.sale-row');
            if (currentRow.length && currentRow.is('#saleTableBody .sale-row:last')) {
                let newRow = createRowHtml();
                currentRow.after(newRow);
                updateRowNumbers();
                calcGrand();
                newRow.find('.item-input').focus();
            }
            return false;
        }
    });

    function handleSaleTypeToggle() {
        let saleType = $('input[name="sale_type"]:checked').val();
        let advanceLabel = $('#advanceLabel');

        if (saleType === 'sale') {
            $('#deliveryPaymentPanel').addClass('d-none');
            $('#discountContainer').removeClass('d-none');
            $('#advanceContainer').removeClass('d-none');
            $('#remainingContainer').removeClass('d-none');
            $('#remainingContainer label').text('Remaining');
            $('[name="delivery_date"]').prop('required', false).val('');
            advanceLabel.text('Received Amount');
        } else if (saleType === 'estimate') {
            $('#deliveryPaymentPanel').addClass('d-none');
            $('#discountContainer').removeClass('d-none');
            $('#advanceContainer').addClass('d-none');
            $('#remainingContainer').removeClass('d-none');
            $('#remainingContainer label').text('Net Total');
            $('[name="delivery_date"]').prop('required', false).val('');
            advanceLabel.text('Advance Amount');
        } else {
            $('#deliveryPaymentPanel').removeClass('d-none');
            $('#discountContainer').removeClass('d-none');
            $('#advanceContainer').removeClass('d-none');
            $('#remainingContainer').removeClass('d-none');
            $('#remainingContainer label').text('Remaining');
            $('[name="delivery_date"]').prop('required', true);
            if (saleType === 'booking') {
                advanceLabel.text('Advance Amount');
            } else {
                advanceLabel.text('Advance/Received');
            }
        }

        $('.sale-row:not(#rowTemplate)').each(function() {
            calcRow($(this));
        });
    }

    $('form').on('submit', function () {
        calcGrand();
        
        let validItems = 0;
        let errors = [];
        $('.sale-row:not(#rowTemplate)').each(function() {
            let row = $(this);
            let input = row.find('.item-input');
            let itemName = input.val().trim();
            let qty = parseFloat(row.find('.qty').val()) || 0;

            if(itemName) {
                validItems++;
                if (qty <= 0) {
                    errors.push(`Row ${row.find('.row-index').text()}: Quantity for product "${itemName}" must be greater than 0.`);
                }
                
                let saleType = $('input[name="sale_type"]:checked').val();
                let avail = parseFloat(row.find('.avail-stock').val());
                if (saleType === 'sale' && !isNaN(avail) && qty > avail) {
                    errors.push(`Row ${row.find('.row-index').text()}: Quantity (${qty}) for product "${itemName}" exceeds Available Stock (${avail}).`);
                }
            }
        });

        if (validItems === 0) {
            Swal.fire('Error', 'Please add at least one item', 'error');
            return false;
        }

        if (errors.length > 0) {
            let errorHtml = '<ul style="text-align:left; margin:0; padding-left:20px;">';
            errors.forEach(function(msg) {
                errorHtml += '<li>' + msg + '</li>';
            });
            errorHtml += '</ul>';

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errorHtml
            });
            return false;
        }
    });

    $(document).ready(function() {
        handleSaleTypeToggle();
        $('input[name="sale_type"]').on('change', handleSaleTypeToggle);

        $('#partyType').on('change', function () {
            let t = this.value;
            $('#customerBox,#vendorBox').addClass('d-none');
            $('#walkinName,#walkinPhone,#walkinAddress').addClass('d-none');
            $('.readonly-wrap').addClass('d-none');

            $('#advance').prop('readonly', false);
            $('#advanceLabel').text('Advance');
            $('#remaining').closest('.col-md-3').removeClass('d-none');

            if (t === 'customer') {
                $('#customerBox').removeClass('d-none');
                $('.readonly-wrap').removeClass('d-none');
            }

            if (t === 'vendor') {
                $('#vendorBox').removeClass('d-none');
                $('.readonly-wrap').removeClass('d-none');
            }

            if (t === 'walkin') {
                $('#walkinName,#walkinPhone,#walkinAddress').removeClass('d-none');
                $('#advance').val($('#grandTotal').val()).prop('readonly', true);
                $('#advanceLabel').text('Paid Amount');
                $('#remaining').val('0');
                $('#remaining').closest('.col-md-3').addClass('d-none');
            }
            calcGrand();
        });

        $('#partyType').trigger('change');

        $('#customer').on('change', function () {
            let o = $('option:selected', this);
            $('#phone').val(o.data('phone') || '');
            $('#address').val(o.data('address') || '');
        });

        $('#vendor').on('change', function () {
            let o = $('option:selected', this);
            $('#phone').val(o.data('phone') || '');
            $('#address').val(o.data('address') || '');
        });

        // Populate phone/address from old selection on load
        let selCust = $('#customer').find('option:selected');
        if (selCust.val()) { $('#phone').val(selCust.data('phone') || ''); $('#address').val(selCust.data('address') || ''); }
        let selVend = $('#vendor').find('option:selected');
        if (selVend.val()) { $('#phone').val(selVend.data('phone') || ''); $('#address').val(selVend.data('address') || ''); }
    });
</script>
