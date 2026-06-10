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
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <h4 class="mb-3">✏️ Edit Job Order</h4>

            <form method="POST" action="{{ route('local.sale.update', $original->id) }}">
                @csrf
                @method('PUT')

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
                                        <th style="width: 45%;">Product Name</th>
                                        <th style="width: 15%;">Quantity</th>
                                        <th style="width: 10%;">Unit</th>
                                        <th style="width: 12%;">Price/unit</th>
                                        <th style="width: 12%;">amount</th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">

                                    @foreach($items as $i => $item)
                                        <tr class="sale-row">
                                            <td>
                                                <span class="row-index">{{ $i + 1 }}</span>
                                            </td>
                                            <td>
                                                <input name="item[]" class="form-control readonly-box" value="{{ $item }}" readonly>
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
                                <input id="grandTotal" name="grand_total" class="form-control readonly-box"
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
                                <input id="netAmount" name="net_amount" class="form-control readonly-box"
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
        $('#grandTotal').val(total.toFixed(2));
        $('#netAmount').val(total.toFixed(2));
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

    // calculate all rows on load
    $(document).ready(function () {
        calcGrand();
    });
</script>
