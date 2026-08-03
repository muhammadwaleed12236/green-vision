@include('admin_panel.include.header_include')

<style>
    .wizard-container {
        background: #fff;
        padding: 30px;
        border-radius: 8px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    
    .wizard-steps {
        display: flex;
        justify-content: space-between;
        position: relative;
        margin-bottom: 40px;
    }
    
    .wizard-steps::before {
        content: "";
        position: absolute;
        top: 20px;
        left: 0;
        width: 100%;
        height: 2px;
        background: #e0e0e0;
        z-index: 1;
    }
    
    .step {
        text-align: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #fff;
        border: 2px solid #e0e0e0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        color: #999;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }
    
    .step.active .step-icon {
        background: #1c9262;
        border-color: #1c9262;
        color: #fff;
    }
    
    .step.completed .step-icon {
        background: #28a745;
        border-color: #28a745;
        color: #fff;
    }
    
    .step-title {
        font-size: 14px;
        font-weight: 600;
        color: #666;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .step.active .step-title {
        color: #1c9262;
    }
    
    .wizard-content {
        display: none;
    }
    
    .wizard-content.active {
        display: block;
        animation: fadeIn 0.5s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h4 class="mb-0">Sale to Purchase Wizard</h4>
                    <small class="text-muted">Streamlined process from Sale to Purchase to Payment</small>
                </div>
            </div>

            <div class="wizard-container">
                <!-- Stepper UI -->
                <div class="wizard-steps">
                    <div class="step active" id="step1-indicator">
                        <div class="step-icon">1</div>
                        <div class="step-title">Sale Details</div>
                    </div>
                    <div class="step" id="step2-indicator">
                        <div class="step-icon">2</div>
                        <div class="step-title">Purchase</div>
                    </div>
                    <div class="step" id="step3-indicator">
                        <div class="step-icon">3</div>
                        <div class="step-title">Payment</div>
                    </div>
                </div>

                <!-- Alert Messages -->
                <div id="wizard-alert" class="alert d-none"></div>

                <!-- Step 1: Sale Details -->
                <div class="wizard-content active" id="step1">
                    <h5 class="mb-4">Select Sale (Booking/Estimate/Pending)</h5>
                    
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-6">
                            <label>Select Sale</label>
                            <select id="sale_selector" class="form-control select2">
                                <option value="">-- Loading Sales --</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <span class="mx-3 text-muted">or</span>
                            <button type="button" class="btn btn-outline-primary" id="btn-toggle-new-sale">
                                <i class="fas fa-plus"></i> Create New Sale
                            </button>
                        </div>
                    </div>

                    <!-- Inline New Sale Section -->
                    <div id="newSaleSection" class="mt-4 border rounded p-3 bg-light" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h5 class="m-0 text-primary">Create New Sale</h5>
                            <button type="button" class="btn btn-sm btn-secondary" onclick="closeNewSaleAndRefresh()">
                                <i class="fas fa-sync"></i> Close & Refresh Sale List
                            </button>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Party Type <span class="text-danger">*</span></label>
                                <select id="qs_party_type" class="form-control select2">
                                    <option value="customer" selected>Customer</option>
                                    <option value="walkin">Walk-in</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3" id="qs_customer_div">
                                <label>Customer <span class="text-danger">*</span></label>
                                <select id="qs_customer" class="form-control select2">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $cust)
                                        <option value="{{ $cust->id }}">{{ $cust->customer_name ?? $cust->shop_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3 d-none walkin-field">
                                <label>Name <span class="text-danger">*</span></label>
                                <input type="text" id="qs_walkin_name" class="form-control" placeholder="Name">
                            </div>
                            <div class="col-md-2 mb-3 d-none walkin-field">
                                <label>Phone</label>
                                <input type="text" id="qs_walkin_phone" class="form-control" placeholder="Phone">
                            </div>
                            <div class="col-md-2 mb-3 d-none walkin-field">
                                <label>Address</label>
                                <input type="text" id="qs_walkin_address" class="form-control" placeholder="Address">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm" id="qs_table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">Product</th>
                                        <th style="width: 20%">Rate</th>
                                        <th style="width: 20%">Qty</th>
                                        <th style="width: 15%">Amount</th>
                                        <th style="width: 5%">Act</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <select class="form-control select2 qs-product">
                                                <option value="">Select Product</option>
                                                @foreach($products as $prod)
                                                    <option value="{{ $prod->item_name }}" data-rate="{{ $prod->retail_price ?? 0 }}">{{ $prod->item_name }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td><input type="number" class="form-control qs-rate" value="0"></td>
                                        <td><input type="number" class="form-control qs-qty" value="1"></td>
                                        <td class="qs-amt fw-bold text-end">0.00</td>
                                        <td><button type="button" class="btn btn-sm btn-danger qs-remove"><i class="fas fa-times"></i></button></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2">
                            <button type="button" class="btn btn-sm btn-info text-white" id="qs_add_row"><i class="fas fa-plus"></i> Add Row</button>
                            <h5 class="mb-0">Total: <span id="qs_grand_total">0.00</span></h5>
                        </div>
                        <div class="text-end mt-3 border-top pt-3">
                            <button type="button" class="btn btn-primary" id="btn_qs_save"><i class="fas fa-save"></i> Save Quick Sale</button>
                        </div>
                    </div>

                    <div id="sale_details_container" class="d-none">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Customer: </strong> <span id="display_customer"></span>
                            </div>
                            <div class="col-md-6 text-end">
                                <strong>Previous Balance: </strong> <span id="display_prev_balance" class="text-danger"></span>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item Name</th>
                                        <th>Rate</th>
                                        <th>Qty</th>
                                        <th>Unit</th>
                                    </tr>
                                </thead>
                                <tbody id="sale_products_table">
                                    <!-- Populated via JS -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="button" class="btn btn-primary" id="btn-next-1" disabled>Next Step <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>

                <!-- Step 2: Purchase -->
                <div class="wizard-content" id="step2">
                    <h5 class="mb-4">Create Purchase for Items</h5>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label>Select Vendor <span class="text-danger">*</span></label>
                            <select id="vendor_selector" class="form-control select2">
                                <option value="">-- Select Vendor --</option>
                                @foreach($vendors as $vendor)
                                    <option value="{{ $vendor->id }}">{{ $vendor->Party_name }} ({{ $vendor->Party_code }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Item Name</th>
                                    <th>Required Qty (from Sale)</th>
                                    <th>Purchase Rate <span class="text-danger">*</span></th>
                                    <th>Total Amount</th>
                                </tr>
                            </thead>
                            <tbody id="purchase_products_table">
                                <!-- Populated via JS -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Grand Total:</th>
                                    <th id="purchase_grand_total">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="row mb-4 mt-3">
                        <div class="col-md-4">
                            <label>Purchase Price</label>
                            <input type="text" id="purchase_price_readonly" class="form-control" readonly value="0.00">
                        </div>
                        <div class="col-md-4">
                            <label>Amount Being Paid Now</label>
                            <input type="number" id="purchase_payment_amount" class="form-control" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label>Account</label>
                            <select id="purchase_account_id" class="form-control select2">
                                <option value="">-- Select Account --</option>
                                @if(isset($accounts))
                                    @foreach($accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->Account_name ?? $account->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)"><i class="fas fa-arrow-left me-1"></i> Previous</button>
                        <button type="button" class="btn btn-success" id="btn-create-purchase">Create Purchase & Next <i class="fas fa-arrow-right ms-1"></i></button>
                    </div>
                </div>

                <!-- Step 3: Payment -->
                <div class="wizard-content" id="step3">
                    <h5 class="mb-4">Receive Customer Payment</h5>
                    
                    <div class="row">
                        <div class="col-md-6 mx-auto">
                            <div class="card shadow-sm border-0 bg-light">
                                <div class="card-body p-4">
                                    <h6 class="border-bottom pb-2 mb-3 text-primary">Payment Summary</h6>
                                    
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Customer Name:</span>
                                        <strong id="pay_customer_name"></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Previous Balance:</span>
                                        <strong id="pay_prev_balance" class="text-danger"></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-3 pb-3 border-bottom">
                                        <span>Sale Net Amount:</span>
                                        <strong id="pay_sale_amount"></strong>
                                    </div>
                                    
                                    <div class="form-group mb-3">
                                        <label class="fw-bold">Discount Applied</label>
                                        <input type="number" id="pay_discount" class="form-control" value="0" min="0">
                                    </div>
                                    
                                    <div class="form-group mb-4">
                                        <label class="fw-bold">Payment Received Amount <span class="text-danger">*</span></label>
                                        <input type="number" id="pay_amount" class="form-control form-control-lg text-success fw-bold" placeholder="0.00">
                                    </div>

                                    <div class="form-group mb-4">
                                        <label class="fw-bold">Account</label>
                                        <select id="pay_account_id" class="form-control select2">
                                            <option value="">-- Select Account --</option>
                                            @if(isset($accounts))
                                                @foreach($accounts as $account)
                                                    <option value="{{ $account->id }}">{{ $account->Account_name ?? $account->name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(2)"><i class="fas fa-arrow-left me-1"></i> Previous</button>
                        <button type="button" class="btn btn-primary" id="btn-complete">Complete Process <i class="fas fa-check ms-1"></i></button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    let currentSaleId = null;
    let saleProducts = [];
    let saleDetails = null;
    
    $(document).ready(function() {
        // Toggle new sale inline
        $('#btn-toggle-new-sale').click(function() {
            $('#newSaleSection').slideToggle();
        });
        
        // Quick Sale Logic
        $('#qs_add_row').click(function() {
            const row = `<tr>
                <td>
                    <select class="form-control qs-product">
                        <option value="">Select Product</option>
                        @foreach($products as $prod)
                            <option value="{{ $prod->item_name }}" data-rate="{{ $prod->retail_price ?? 0 }}">{{ $prod->item_name }}</option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" class="form-control qs-rate" value="0"></td>
                <td><input type="number" class="form-control qs-qty" value="1"></td>
                <td class="qs-amt fw-bold text-end">0.00</td>
                <td><button type="button" class="btn btn-sm btn-danger qs-remove"><i class="fas fa-times"></i></button></td>
            </tr>`;
            $('#qs_table tbody').append(row);
            // Reinitialize select2 for dynamic rows if you use it globally
            $('.qs-product').select2();
        });

        $(document).on('change', '.qs-product', function() {
            const rate = $(this).find(':selected').data('rate') || 0;
            $(this).closest('tr').find('.qs-rate').val(rate);
            calcQs();
        });

        $(document).on('input', '.qs-rate, .qs-qty', function() {
            calcQs();
        });

        $(document).on('click', '.qs-remove', function() {
            if($('#qs_table tbody tr').length > 1) {
                $(this).closest('tr').remove();
                calcQs();
            }
        });

        function calcQs() {
            let total = 0;
            $('#qs_table tbody tr').each(function() {
                const rate = parseFloat($(this).find('.qs-rate').val()) || 0;
                const qty = parseFloat($(this).find('.qs-qty').val()) || 0;
                const amt = rate * qty;
                $(this).find('.qs-amt').text(amt.toFixed(2));
                total += amt;
            });
            $('#qs_grand_total').text(total.toFixed(2));
        }

        $('#qs_party_type').on('change', function() {
            if ($(this).val() === 'walkin') {
                $('#qs_customer_div').addClass('d-none');
                $('.walkin-field').removeClass('d-none');
            } else {
                $('#qs_customer_div').removeClass('d-none');
                $('.walkin-field').addClass('d-none');
            }
        });

        $('#btn_qs_save').click(function() {
            const party_type = $('#qs_party_type').val();
            let customer_id = '';
            let walkin_name = '';
            let walkin_phone = '';
            let walkin_address = '';

            if (party_type === 'customer') {
                customer_id = $('#qs_customer').val();
                if(!customer_id) {
                    showAlert('Please select a customer for the new sale', 'danger');
                    return;
                }
            } else {
                walkin_name = $('#qs_walkin_name').val();
                if(!walkin_name) {
                    showAlert('Please enter walk-in name', 'danger');
                    return;
                }
                walkin_phone = $('#qs_walkin_phone').val();
                walkin_address = $('#qs_walkin_address').val();
            }

            let products = [];
            let isValid = true;
            $('#qs_table tbody tr').each(function() {
                const pname = $(this).find('.qs-product').val();
                const rate = $(this).find('.qs-rate').val();
                const qty = $(this).find('.qs-qty').val();

                if(pname && qty > 0) {
                    products.push({
                        item_name: pname,
                        rate: rate,
                        qty: qty,
                        unit: 'pcs'
                    });
                }
            });

            if(products.length === 0) {
                showAlert('Please add at least one valid product', 'danger');
                return;
            }

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: '{{ route("wizard.api.sale.quick") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    party_type: party_type,
                    customer_id: customer_id,
                    walkin_name: walkin_name,
                    walkin_phone: walkin_phone,
                    walkin_address: walkin_address,
                    products: products
                },
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Quick Sale');
                    if(res.success) {
                        showAlert('Quick Sale created successfully!', 'success');
                        $('#newSaleSection').slideUp();
                        
                        // Clear form
                        $('#qs_customer').val('').trigger('change');
                        $('#qs_table tbody tr:not(:first)').remove();
                        $('#qs_table tbody tr:first .qs-product').val('').trigger('change');
                        $('#qs_table tbody tr:first .qs-rate').val('0');
                        $('#qs_table tbody tr:first .qs-qty').val('1');
                        calcQs();
                        
                        // Fetch new sales list and auto-select
                        fetchSales(res.sale.id);
                    } else {
                        showAlert(res.message, 'danger');
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Quick Sale');
                    showAlert('Error saving quick sale', 'danger');
                }
            });
        });

        // Initialize fetch sales
        fetchSales();
        
        // Step 1: Sale Selection
        $('#sale_selector').change(function() {
            const saleId = $(this).val();
            if(saleId) {
                fetchSaleDetails(saleId);
            } else {
                $('#sale_details_container').addClass('d-none');
                $('#btn-next-1').prop('disabled', true);
            }
        });
        
        $('#btn-next-1').click(function() {
            populatePurchaseTable();
            nextStep(2);
        });
        
        // Step 2: Purchase Creation
        $(document).on('input', '.purchase-rate', function() {
            calculatePurchaseTotal();
        });
        
        $('#btn-create-purchase').click(function() {
            const vendorId = $('#vendor_selector').val();
            if(!vendorId) {
                showAlert('Please select a vendor', 'danger');
                return;
            }
            
            let purchaseItems = [];
            let isValid = true;
            
            $('.purchase-row').each(function(index) {
                const rate = $(this).find('.purchase-rate').val();
                if(!rate || rate <= 0) {
                    isValid = false;
                    $(this).find('.purchase-rate').addClass('is-invalid');
                } else {
                    $(this).find('.purchase-rate').removeClass('is-invalid');
                }
                
                purchaseItems.push({
                    item_name: saleProducts[index].item_name,
                    pcs: saleProducts[index].qty, // map qty to pcs for purchase
                    unit: saleProducts[index].unit,
                    rate: rate
                });
            });
            
            if(!isValid) {
                showAlert('Please enter a valid purchase rate for all items', 'danger');
                return;
            }
            
            // Disable button during AJAX
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creating...');
            
            $.ajax({
                url: '{{ route("wizard.api.purchase.create") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    vendor_id: vendorId,
                    products: purchaseItems,
                    payment_amount: $('#purchase_payment_amount').val(),
                    account_id: $('#purchase_account_id').val()
                },
                success: function(res) {
                    btn.prop('disabled', false).html('Create Purchase & Next <i class="fas fa-arrow-right ms-1"></i>');
                    if(res.success) {
                        showAlert('Purchase created successfully!', 'success');
                        populatePaymentStep();
                        nextStep(3);
                    } else {
                        showAlert(res.message, 'danger');
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html('Create Purchase & Next <i class="fas fa-arrow-right ms-1"></i>');
                    showAlert('Error creating purchase', 'danger');
                }
            });
        });
        
        // Step 3: Payment
        $('#btn-complete').click(function() {
            const amount = $('#pay_amount').val();
            const discount = $('#pay_discount').val();
            
            if(!amount || amount < 0) {
                showAlert('Please enter a valid payment amount', 'danger');
                $('#pay_amount').addClass('is-invalid');
                return;
            }
            
            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing...');
            
            $.ajax({
                url: '{{ route("wizard.api.payment.create") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    sale_id: currentSaleId,
                    payment_amount: amount,
                    discount: discount,
                    account_id: $('#pay_account_id').val()
                },
                success: function(res) {
                    btn.prop('disabled', false).html('Complete Process <i class="fas fa-check ms-1"></i>');
                    if(res.success) {
                        showAlert('Wizard completed successfully! Payment recorded.', 'success');
                        // Reset wizard after 2 seconds
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    } else {
                        showAlert(res.message, 'danger');
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html('Complete Process <i class="fas fa-check ms-1"></i>');
                    showAlert('Error recording payment', 'danger');
                }
            });
        });
    });
    
    function fetchSales(autoSelectId = null) {
        $.ajax({
            url: '{{ route("wizard.api.sales") }}',
            type: 'GET',
            success: function(res) {
                let options = '<option value="">-- Select Sale --</option>';
                res.forEach(sale => {
                    let customerName = sale.customer_shopname ? sale.customer_shopname : 'Customer ID: ' + sale.customer_id;
                    options += `<option value="${sale.id}">Inv: ${sale.invoice_number} - ${customerName} (${sale.sale_type.toUpperCase()})</option>`;
                });
                $('#sale_selector').html(options);
                
                if(autoSelectId) {
                    $('#sale_selector').val(autoSelectId).trigger('change');
                }
            }
        });
    }
    
    function fetchSaleDetails(saleId) {
        currentSaleId = saleId;
        $.ajax({
            url: `/wizard/api/sale/${saleId}`,
            type: 'GET',
            success: function(res) {
                saleDetails = res.sale;
                saleProducts = res.products;
                
                // Populate Customer Details
                let customerName = saleDetails.customer ? (saleDetails.customer.customer_name || saleDetails.customer.shop_name) : saleDetails.customer_shopname;
                $('#display_customer').text(customerName);
                $('#display_prev_balance').text(parseFloat(res.previous_balance).toFixed(2));
                
                // Keep for payment step
                $('#pay_customer_name').text(customerName);
                $('#pay_prev_balance').text(parseFloat(res.previous_balance).toFixed(2));
                $('#pay_sale_amount').text(parseFloat(saleDetails.net_amount || 0).toFixed(2));
                
                // Populate Products Table
                let tbody = '';
                saleProducts.forEach(prod => {
                    tbody += `<tr>
                        <td>${prod.item_name}</td>
                        <td>${prod.rate}</td>
                        <td>${prod.qty}</td>
                        <td>${prod.unit}</td>
                    </tr>`;
                });
                $('#sale_products_table').html(tbody);
                
                $('#sale_details_container').removeClass('d-none');
                $('#btn-next-1').prop('disabled', false);
            }
        });
    }
    
    function populatePurchaseTable() {
        let tbody = '';
        saleProducts.forEach((prod, index) => {
            tbody += `<tr class="purchase-row">
                <td>${prod.item_name}</td>
                <td>${prod.qty} ${prod.unit}</td>
                <td><input type="number" class="form-control form-control-sm purchase-rate" value="${prod.purchase_rate}" readonly></td>
                <td class="purchase-amount fw-bold">0.00</td>
            </tr>`;
        });
        $('#purchase_products_table').html(tbody);
        calculatePurchaseTotal();
    }
    
    function calculatePurchaseTotal() {
        let grandTotal = 0;
        $('.purchase-row').each(function(index) {
            const rate = parseFloat($(this).find('.purchase-rate').val()) || 0;
            const qty = parseFloat(saleProducts[index].qty) || 0;
            const amount = rate * qty;
            $(this).find('.purchase-amount').text(amount.toFixed(2));
            grandTotal += amount;
        });
        $('#purchase_grand_total').text(grandTotal.toFixed(2));
        $('#purchase_price_readonly').val(grandTotal.toFixed(2));
    }
    
    function closeNewSaleAndRefresh() {
        $('#newSaleSection').slideUp();
        fetchSales();
    }
    
    function populatePaymentStep() {
        // Pre-fill amount if advance is there, or default to 0
        // Currently we just let the user type the amount they want to receive
        $('#pay_amount').val('');
        $('#pay_discount').val('0');
    }
    
    function nextStep(stepNum) {
        $('.wizard-content').removeClass('active');
        $(`#step${stepNum}`).addClass('active');
        
        $(`.step`).removeClass('active');
        for(let i = 1; i < stepNum; i++) {
            $(`#step${i}-indicator`).addClass('completed');
        }
        $(`#step${stepNum}-indicator`).addClass('active').removeClass('completed');
    }
    
    function prevStep(stepNum) {
        $('.wizard-content').removeClass('active');
        $(`#step${stepNum}`).addClass('active');
        
        $(`.step`).removeClass('active completed');
        for(let i = 1; i < stepNum; i++) {
            $(`#step${i}-indicator`).addClass('completed');
        }
        $(`#step${stepNum}-indicator`).addClass('active');
    }
    
    function showAlert(msg, type) {
        const alertBox = $('#wizard-alert');
        alertBox.removeClass('alert-success alert-danger d-none').addClass(`alert-${type}`).text(msg);
        $('html, body').animate({ scrollTop: 0 }, 'slow');
        setTimeout(() => {
            alertBox.addClass('d-none');
        }, 5000);
    }
</script>

@include('admin_panel.include.footer_include')
