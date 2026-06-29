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
                            // determine party display values
                            $partyName = '';
                            $phone = '';
                            $address = '';

                            if ($original->party_type === 'customer' && $original->customer) {
                                $partyName = $original->customer->customer_name ?? $original->customer->shop_name;
                                $phone = $original->customer->phone_number;
                                $address = $original->customer->address;
                            } elseif ($original->party_type === 'vendor' && $original->vendor) {
                                $partyName = $original->vendor->Party_name;
                                $phone = $original->vendor->Party_phone;
                                $address = $original->vendor->Party_address;
                            } else {
                                // walkin or fallback
                                $partyName = $original->customer_shopname;
                                $phone = $original->customer_phone;
                                $address = $original->customer_address;
                            }
                        @endphp

                        <input type="hidden" name="party_type" value="{{ $original->party_type }}">
                        <input type="hidden" name="customer_id" value="{{ $original->customer_id }}">
                        <input type="hidden" name="vendor_id" value="{{ $original->vendor_id }}">
                        <input type="hidden" name="walkin_name" value="{{ $original->customer_shopname }}">

                        <div class="col-md-3">
                            <label>Party Type</label>
                            <input class="form-control readonly-box" value="{{ ucfirst($original->party_type) }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Party</label>
                            <input class="form-control readonly-box" value="{{ $partyName }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Phone</label>
                            <input class="form-control readonly-box" value="{{ $phone }}" readonly>
                        </div>

                        <div class="col-md-3">
                            <label>Address</label>
                            <input class="form-control readonly-box" value="{{ $address }}" readonly>
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
                                        <th style="width: 40%;">Product Name</th>
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
                                                    <input name="item[]" class="form-control item-input" value="{{ $item }}" autocomplete="off" placeholder="Search Product" data-mode="search">
                                                </div>
                                                <div class="autocomplete-list d-none"></div>
                                            </td>
                                            <td>
                                                <div class="qty-box">
                                                    <button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>
                                                    <input name="qty[]" class="form-control qty text-center" value="{{ $qtys[$i] ?? 1 }}">
                                                    <button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
                                                </div>
                                            </td>
                                            <td>
                                                <input name="unit[]" class="form-control unit text-center" value="{{ $units[$i] ?? '' }}">
                                            </td>
                                            <td>
                                                <input name="rate[]" class="form-control rate" value="{{ $rates[$i] ?? 0 }}">
                                            </td>
                                            <td>
                                                <input name="amount[]" class="form-control item-total" value="{{ $amounts[$i] ?? 0 }}">
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
                                                <input name="item[]" class="form-control item-input" autocomplete="off" placeholder="Search Product" data-mode="search">
                                            </div>
                                            <div class="autocomplete-list d-none"></div>
                                        </td>
                                        <td>
                                            <div class="qty-box">
                                                <button type="button" class="btn btn-sm btn-secondary qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty text-center" value="1">
                                                <button type="button" class="btn btn-sm btn-secondary qty-plus">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="unit[]" class="form-control unit text-center" value="">
                                        </td>
                                        <td>
                                            <input name="rate[]" class="form-control rate" value="0">
                                        </td>
                                        <td>
                                            <input name="amount[]" class="form-control item-total" value="0">
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

                            <div class="col-md-3">
                                <label>Discount</label>
                                <input name="discount_value" class="form-control"
                                    value="{{ $original->discount_value }}">
                            </div>

                            <div class="col-md-3">
                                <label id="advanceLabel">Advance</label>
                                <input id="advance" name="advance_amount" class="form-control"
                                    value="{{ $original->advance_amount }}">
                            </div>

                            <div class="col-md-3">
                                <label>Net Amount</label>
                                <input id="netAmount" name="net_amount" class="form-control readonly-box"
                                    value="{{ $original->net_amount }}" readonly>
                            </div>

                        </div>
                    </div>
                </div>

                <button class="btn btn-primary"><i class="fa fa-save me-1"></i> Update Invoice</button>

            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- ================= JS ================= --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Add these two libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    function calcRow(row) {
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let qty = parseFloat(row.find('.qty').val());
        if (isNaN(qty) || qty < 0) qty = 0;

        let total = rate * qty;
        row.find('.item-total').val(total.toFixed(2));

        calcGrand();
    }

    function calcGrand() {
        let total = 0;
        $('.item-total').each(function () {
            total += parseFloat(this.value) || 0;
        });
        let discount = parseFloat($('[name="discount_value"]').val()) || 0;
        let net = total - discount;
        $('#grandTotal').val(total.toFixed(2));
        $('#netAmount').val(net.toFixed(2));
    }

    $(document).on('input change', '.rate,.qty', function () {
        calcRow($(this).closest('tr'));
    });

    $(document).on('input change', '.item-total', function () {
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
        return $('#rowTemplate').clone().removeClass('d-none').removeAttr('id');
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
            $('[name="delivery_date"]').prop('required', false).val('');
            advanceLabel.text('Received Amount');
        } else {
            $('#deliveryPaymentPanel').removeClass('d-none');
            $('[name="delivery_date"]').prop('required', true);
            if (saleType === 'booking') {
                advanceLabel.text('Advance Amount');
            } else {
                advanceLabel.text('Advance/Received');
            }
        }
    }

    $(document).ready(function() {
        handleSaleTypeToggle();
        $('input[name="sale_type"]').on('change', handleSaleTypeToggle);
    });
</script>
