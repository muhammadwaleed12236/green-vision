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
    .sale-table th:nth-child(1) { width: 12%; } /* Type */
    .sale-table th:nth-child(2) { width: 20%; } /* Item */
    .sale-table th:nth-child(3) { width: 7%; }  /* H */
    .sale-table th:nth-child(4) { width: 7%; }  /* W */
    .sale-table th:nth-child(5) { width: 8%; }  /* Unit */
    .sale-table th:nth-child(6) { width: 8%; }  /* Area */
    .sale-table th:nth-child(7) { width: 9%; } /* Manual */
    .sale-table th:nth-child(8) { width: 9%; }  /* Rate */
    .sale-table th:nth-child(9) { width: 12%; } /* Qty */
    .sale-table th:nth-child(10) { width: 10%; } /* Total */
    .sale-table th:nth-child(11) { width: 5%; }  /* Action */

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
                                        <th>Type</th>
                                        <th>Item Name</th>
                                        <th>H</th>
                                        <th>W</th>
                                        <th>Unit</th>
                                        <th>Area (ft²)</th>
                                        <th>Manual SqFt</th>
                                        <th>Rate</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">
                                @for($i=0; $i<5; $i++)
                                    <tr class="sale-row">
                                        <td>
                                            <select class="form-control row-type">
                                                <option value="" disabled selected>Select Type</option>
                                                <option value="glass">Measurements</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </td>
                                        <td><input name="item_name[]" class="form-control" placeholder="Item"></td>
                                        <td><input name="height[]" class="form-control height text-center"></td>
                                        <td><input name="width[]" class="form-control width text-center"></td>
                                        <td>
                                            <select name="unit[]" class="form-control unit p-1">
                                                <option value="ft" selected>Ft</option>
                                                <option value="inch">In</option>
                                            </select>
                                        </td>
                                        <td><input class="form-control area readonly-box text-center" readonly tabindex="-1"></td>
                                        <td><input name="manual_sqft[]" class="form-control manual-sqft text-center" placeholder="--"></td>
                                        <td><input name="rate[]" class="form-control rate text-end" placeholder="0.00"></td>
                                        <td>
                                            <div class="qty-box">
                                                <button type="button" class="btn qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty" value="1" placeholder="0">
                                                <button type="button" class="btn qty-plus">+</button>
                                            </div>
                                        </td>
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

    function toFeet(value, unit) {
        if (!value) return 0;

        value = value.toString().trim();
        let parts = value.split('.');

        let whole = parseInt(parts[0]) || 0;
        let decimal = parts[1] ? parseInt(parts[1]) : 0;

        if (unit === 'ft') {
            return whole + (decimal / 12);
        }

        let inches = whole + (decimal / 25.4);
        return inches / 12;
    }

    // Toggle Input State based on Type
    $(document).on('change', '.row-type', function() {
        let r = $(this).closest('tr');
        let type = $(this).val();

        // Reset Styles first
        r.find('.height, .width, .unit, .manual-sqft').prop('readonly', false).css('background-color', '');
        r.find('.unit').prop('disabled', false);

        if (!type) {
             // If Select Type (empty)
             // Initialize as disabled if empty
             r.find('.height, .width, .unit, .manual-sqft, .rate, .qty').prop('readonly', true).css('background-color', '#f8f9fa');
             r.find('.unit').prop('disabled', true);
             return;
        }

        // Re-enable common fields in case they were disabled by empty check
        r.find('.rate, .qty').prop('readonly', false).css('background-color', '');

        if (type === 'other') { // Was 'hardware'
            // Disable Height, Width, Manual Sqft, Unit
            r.find('.height, .width, .unit, .manual-sqft, .area').prop('readonly', true).val('').css('background-color', '#f0f0f0');
            r.find('.unit').prop('disabled', true);
             // Default Qty to 1 if empty
             if(!r.find('.qty').val()) r.find('.qty').val(1);
        } else if (type === 'glass') { // Means 'Measurements'
            // Enable Height, Width, Manual Sqft
             r.find('.area').prop('readonly', true); // Area always readonly
        }
        
        calcRow(r);
    });

    function calcRow(r) {
        let type = r.find('.row-type').val();
        let rate = parseFloat(r.find('.rate').val()) || 0;
        let qty = parseFloat(r.find('.qty').val());

        // Default Qty to 1 if empty/invalid
        if (isNaN(qty) || qty < 0) qty = 1;

        if (type === 'other') {
            // Simple Calculation: Rate * Qty
            let total = rate * qty;
            r.find('.item-total').val(total.toFixed(2));
            r.find('.area').val('-'); // clear area
        } else if(type === 'glass') { // Measurements
            // Glass Calculation
            let unit = r.find('.unit').val();
            let hInput = r.find('.height').val();
            let wInput = r.find('.width').val();
            let mInput = r.find('.manual-sqft').val();

            let h = toFeet(hInput, unit);
            let w = toFeet(wInput, unit);
            let area = h * w;
            r.find('.area').val(area ? area.toFixed(2) : '');

            let manualSqft = parseFloat(mInput) || 0;
            let finalArea = manualSqft > 0 ? manualSqft : area;
            
            let total = finalArea * rate * qty;
            r.find('.item-total').val(total.toFixed(2));
        } else {
            // No type selected
            r.find('.item-total').val('');
        }

        calcGrand();
    }

    $(document).on('input change', '.height,.width,.unit,.rate,.qty,.manual-sqft', e => {
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

    function addNewRow() {
        let r = $('.sale-row:first').clone();
        r.find('input').val('');
        
        // Item name should always be editable
        r.find('[name="item_name[]"]').prop('readonly', false).css('background-color', '');
        
        // Other fields start disabled until type is selected
        r.find('.height, .width, .manual-sqft, .rate').prop('readonly', true).css('background-color', '#f8f9fa');
        r.find('.qty').val(1).prop('readonly', true).css('background-color', '#f8f9fa');
        r.find('.manual-sqft').val('');
        r.find('.area').prop('readonly', true);
        
        // Reset type to empty default
        r.find('.row-type').val(''); 
        r.find('.unit').prop('disabled', true).val('ft');
        
        $('#saleTableBody').append(r);
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
        
        // Remove empty rows before submitting to avoid validation errors or cluttered DB
        // Check filtering logic if needed, but for now just submit all.
        // Actually, Controller should filter out empty items.
        // Let's ensure at least one row has data.
        let validItems = 0;
        $('.sale-row').each(function() {
             if($(this).find('[name="item_name[]"]').val()) validItems++;
        });

        if (validItems === 0) {
            Swal.fire('Error', 'Please add at least one item', 'error');
            return false;
        }
        
        // Enable disabled selects (like unit) just in case, so they submit
        $('.unit').prop('disabled', false);
    });
    // Initial State Check for first load
    $(document).ready(function() {
         $('.sale-row').each(function() {
             let type = $(this).find('.row-type').val();
             if(!type) {
                 // Initialize as disabled if empty
                 $(this).find('.height, .width, .unit, .manual-sqft, .rate, .qty').prop('readonly', true).css('background-color', '#f8f9fa');
                 $(this).find('.unit').prop('disabled', true);
             }
         });
    });
</script>
