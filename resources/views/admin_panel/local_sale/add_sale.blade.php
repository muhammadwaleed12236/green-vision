@include('admin_panel.include.header_include')

<style>
    /* General Table Styling */
    .sale-table th {
        /* vertical-alig/n: middle; */
        font-weight: 600;
        background-color: #f8f9fa;
        color: #333;
        border-bottom: 2px solid #dee2e6;
        padding: 10px 5px !important;
        font-size: 13px;
        text-align: center;
    }

    .sale-table td {
        vertical-align: middle;
        padding: 8px 5px !important;
    }

    /* Column Widths */
    .sale-table th:nth-child(1) { width: 5%; }  /* # */
    .sale-table th:nth-child(2) { width: 45%; } /* Product Name */
    .sale-table th:nth-child(3) { width: 15%; } /* Quantity */
    .sale-table th:nth-child(4) { width: 10%; } /* Unit */
    .sale-table th:nth-child(5) { width: 12%; } /* Price/unit */
    .sale-table th:nth-child(6) { width: 12%; } /* amount */
    .sale-table th:nth-child(7) { width: 5%; }  /* Action */

    /* Input & Select Styling */
    .sale-table .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        font-size: 13px;
        padding: 6px 8px;
        height: 34px; /* Consistent height */
    }

    .sale-table .form-control:focus {
        border-color: #637381;
        box-shadow: none;
    }

    /* Readonly inputs styling */
    .readonly-box {
        background-color: #f8f9fa !important;
        color: #6c757d;
        cursor: default;
    }

    /* Qty Box Styling */
    .qty-box {
        display: flex;
        gap: 0;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .qty-box .btn {
        padding: 0 8px;
        height: 32px;
        border-radius: 0;
        font-weight: bold;
        background: #f1f3f5;
        border: none;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-box .btn:hover {
        background: #e2e6ea;
    }

    .qty-box .qty {
        border: none;
        border-right: 1px solid #ced4da;
        border-left: 1px solid #ced4da;
        border-radius: 0;
        height: 32px;
        padding: 0;
        width: 100%;
        text-align: center;
    }

    .btn-action {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1;
    }

    /* Type Select styling specifically */
    .row-type {
        font-weight: 500;
        color: #212529;
    }

    /* Autocomplete */
    .autocomplete-list {
        position: absolute;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        max-height: 220px;
        overflow-y: auto;
        width: 100%;
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
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <h4 class="mb-3">🧾 Job Order</h4>

            <form method="POST" action="{{ route('store-local-sale') }}">
                @csrf

                <div class="container-fluid">
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
                                <div class="col-md-3">
                                    <label>Sale Date & Time</label>
                                    <input type="datetime-local" name="sale_date" class="form-control" value="{{ date('Y-m-d\TH:i') }}">
                                </div>

                                <div class="col-md-3 party-box" id="customerBox">
                                    <label>Customer</label>
                                    <select class="form-control search" name="customer_id" id="customer">
                                        <option value="">Select</option>
                                        @foreach ($Customers as $c)
                                            <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                                data-address="{{ $c->address }}">
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
                                                data-address="{{ $v->Party_address }}">
                                                {{ $v->Party_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3 readonly-wrap">
                                    <label>Phone</label>
                                    <input id="phone" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-3 readonly-wrap">
                                    <label>Address</label>
                                    <input id="address" class="form-control readonly-box" readonly>
                                </div>

                                <div class="col-md-3 d-none" id="walkinName">
                                    <label>Name</label>
                                    <input name="walkin_name" class="form-control">
                                </div>

                                <div class="col-md-3 d-none" id="walkinPhone">
                                    <label>Phone</label>
                                    <input name="walkin_phone" class="form-control">
                                </div>

                                <div class="col-md-3 d-none" id="walkinAddress">
                                    <label>Address</label>
                                    <input name="walkin_address" class="form-control">
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-borderless mb-0 sale-table">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="text-center">#</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Quantity</th>
                                        <th>Unit</th>
                                        <th class="text-end">Price/unit</th>
                                        <th class="text-end">amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">
                                @for($i=0; $i<5; $i++)
                                    <tr class="sale-row">
                                        <td class="text-center"><span class="row-index">{{ $i + 1 }}</span></td>
                                        <td style="position:relative;">
                                            <input type="hidden" name="item_id[]" class="item-id">
                                            <input type="text" name="item_name[]" class="form-control item-input" autocomplete="off" placeholder="Product Name">
                                            <div class="autocomplete-list d-none"></div>
                                        </td>
                                        <td>
                                            <div class="qty-box">
                                                <button type="button" class="btn qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty" value="1" placeholder="0">
                                                <button type="button" class="btn qty-plus">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <input type="text" name="unit[]" class="form-control unit p-1 text-center" placeholder="Unit">
                                        </td>
                                        <td><input name="rate[]" class="form-control rate text-end" placeholder="0.00"></td>
                                        <td><input name="amount[]" class="form-control item-total readonly-box text-end" readonly tabindex="-1" value="0.00"></td>
                                        <td>
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button type="button" class="btn btn-success btn-action add-row">+</button>
                                                <button type="button" class="btn btn-danger btn-action remove-row">×</button>
                                            </div>
                                        </td>
                                    </tr>
                                @endfor
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card mb-3">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold text-primary">Delivery & Payment Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Notify Before (Days)</label>
                                <input type="number" name="notify_days_before" class="form-control" value="2" min="1" max="30">
                                <small class="text-muted">System will notify you X days before delivery</small>
                            </div>
                        </div>
                    </div>
                </div>

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
                <button class="btn btn-primary">Save Job Order</button>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}"
        });
    </script>
@endif

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `{!! implode('<br>', $errors->all()) !!}`
        });
    </script>
@endif

@include('admin_panel.include.footer_include')

<script>
    $('#partyType').on('change', function () {
        let t = this.value;

        $('#customerBox,#vendorBox').addClass('d-none');
        $('#walkinName,#walkinPhone,#walkinAddress').addClass('d-none');
        $('.readonly-wrap').addClass('d-none');

        $('#advance').prop('readonly', false);
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

    function calcRow(r) {
        let rate = parseFloat(r.find('.rate').val()) || 0;
        let qty = parseFloat(r.find('.qty').val());

        if (isNaN(qty) || qty < 0) qty = 1;

        let total = rate * qty;
        r.find('.item-total').val(total.toFixed(2));

        calcGrand();
    }

    $(document).on('input change', '.rate,.qty', e => {
        calcRow($(e.target).closest('tr'));
    });

    // Auto-Append Logic: Detect input in last row
    $(document).on('input', '.sale-row:last input', function() {
        let lastRow = $('.sale-row:last');
        let hasValue = false;
        lastRow.find('input').each(function() {
            if($(this).val()) hasValue = true;
        });

        if(hasValue) {
            addNewRow();
        }
    });

    function updateRowNumbers() {
        $('.sale-row').each(function(index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function addNewRow() {
        let r = $('.sale-row:first').clone();
        r.find('input').val('');
        r.find('.qty').val(1);
        r.find('.rate').val('');
        r.find('.item-total').val('0.00');
        r.find('.unit').val(it.unit || '');
        r.find('.autocomplete-list').addClass('d-none').empty();
        
        $('#saleTableBody').append(r);
        updateRowNumbers();
    }

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

    $('.add-row').click(() => {
        addNewRow();
    });

    $(document).on('click', '.remove-row', e => {
        if ($('.sale-row').length > 1) {
            $(e.target).closest('tr').remove();
            calcGrand();
            updateRowNumbers();
        }
    });

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

    $('form').on('submit', function () {
        calcGrand();
        
        let validItems = 0;
        $('.sale-row').each(function() {
             if($(this).find('[name="item_name[]"]').val()) validItems++;
        });

        if (validItems === 0) {
            Swal.fire('Error', 'Please add at least one item', 'error');
            return false;
        }
    });

    $(document).ready(function() {
        updateRowNumbers();

        // Autocomplete Logic
        $(document).on('input', '.item-input', function () {
            let input = $(this);
            let row = input.closest('tr');
            let list = row.find('.autocomplete-list');
            let q = input.val().trim();

            if (!q) {
                list.addClass('d-none');
                return;
            }

            $.ajax({
                url: "{{ route('get.items') }}",
                type: "GET",
                data: { q: q },
                success: function (res) {
                    if (!Array.isArray(res) || res.length === 0) {
                        list.addClass('d-none');
                        return;
                    }

                    list.empty().removeClass('d-none');
                    res.forEach(it => {
                        let el = $(`<div class="autocomplete-item">${it.item_name}</div>`);
                        el.data('item', it);
                        list.append(el);
                    });
                }
            });
        });

        // Hide autocomplete when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.item-input, .autocomplete-list').length) {
                $('.autocomplete-list').addClass('d-none');
            }
        });

        // Select item from autocomplete
        $(document).on('click', '.autocomplete-item', function () {
            let it = $(this).data('item');
            let row = $(this).closest('tr');

            row.find('.item-input').val(it.item_name);
            row.find('.item-id').val(it.id);

            // Rate logic
            let price = parseFloat(it.retail_price) || parseFloat(it.wholesale_price) || 0;
            row.find('.rate').val(price);
            row.find('.unit').val(it.unit || 'pcs');

            row.find('.autocomplete-list').addClass('d-none');

            calcRow(row);
        });
    });
</script>
