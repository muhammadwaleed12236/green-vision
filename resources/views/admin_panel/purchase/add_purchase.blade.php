@include('admin_panel.include.header_include')

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       ============================================ */

    /* Never allow accidental horizontal scrolling */
    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
    }

    /* Keep media flexible so nothing overflows */
    img, canvas, table {
        max-width: 100%;
    }

    /* Purchase form table never scrolls horizontally */
    .pch-wrap {
        overflow-x: hidden;
    }
    #purchaseTable th {
        white-space: normal;
        word-break: break-word;
    }
    #purchaseTable td {
        white-space: normal;
        word-break: break-word;
    }

    /* ---------- Phone & below (576px) ---------- */
    @media (max-width: 575.98px) {
        .page-header .page-btn {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        .page-header .page-btn .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .page-title h4 { font-size: 1.05rem; }
        .page-title h6 { font-size: 0.85rem; }
    }

    /* ---------- Purchase table -> stacked cards (phone & small tablet) ---------- */
    @media (max-width: 767.98px) {
        .pch-wrap #purchaseTable {
            display: block !important;
        }
        .pch-wrap #purchaseTable thead {
            display: none !important;
        }
        .pch-wrap #purchaseTable tbody {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
        }
        .pch-wrap #purchaseTable tbody tr {
            background: #fff;
            border: 1px solid #eef2f7 !important;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 4px 14px;
            margin: 0 !important;
        }
        .pch-wrap #purchaseTable tbody tr:hover {
            background: #fff;
        }
        .pch-wrap #purchaseTable tbody td {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0 !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            background: transparent !important;
            font-size: 0.85rem !important;
            color: #1e293b;
            text-align: right;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .pch-wrap #purchaseTable tbody td:last-child {
            border-bottom: 0 !important;
        }
        .pch-wrap #purchaseTable tbody td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .pch-wrap #purchaseTable tbody td .form-control {
            max-width: 55%;
            flex: 1 1 auto;
        }
        .pch-wrap #purchaseTable tbody td .input-group {
            max-width: 70%;
            flex: 1 1 auto;
        }
        .pch-wrap #purchaseTable tbody td .add-row,
        .pch-wrap #purchaseTable tbody td .remove-row {
            width: 34px;
            height: 34px;
        }
        /* Grand Total / Paid / Due summary rows */
        .pch-wrap #purchaseTable tfoot {
            display: block;
        }
        .pch-wrap #purchaseTable tfoot tr {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            border: 1px solid #eef2f7 !important;
            border-radius: 12px;
            padding: 10px 14px;
            margin-bottom: 8px;
            background: #f8fafc;
        }
        .pch-wrap #purchaseTable tfoot td {
            border: 0 !important;
            background: transparent !important;
            padding: 4px 0 !important;
            text-align: left;
        }
        .pch-wrap #purchaseTable tfoot td:first-child {
            flex: 1 1 auto;
        }
        .pch-wrap #purchaseTable tfoot td input {
            max-width: 140px;
        }
        .pch-wrap #purchaseTable tfoot td:last-child {
            display: none !important;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Purchase Management</h4>
                    <h6>Manage Purchases Efficiently</h6>
                </div>
            </div>

            <!-- ITEMS TABLE -->
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif
                    <form action="{{ route('store-Purchase') }}" method="POST" id="purchaseForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" id="purchase_date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vendor Name</label>
                                <select name="party_name" id="party_name" class="form-control vendor-select">
                                    <option value="" selected disabled>Choose One</option>
                                    @foreach($Vendors as $Vendor)
                                        <option value="{{ $Vendor->id }}" data-code="{{ $Vendor->Party_code }}">
                                            {{ $Vendor->Party_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Vendor Code</label>
                                <input type="text" class="form-control party_code" name="party_code" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label" id="paymentAccountLabel">Payment Account</label>
                                <select name="account_id" class="form-control">
                                    <option value="">Select Account (Optional)</option>
                                    @foreach($Accounts as $account)
                                        <option value="{{ $account->id }}">{{ $account->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                        </div>

                        <div class="table-responsive pch-wrap">
                            <table class="table table-bordered align-middle text-center" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th style="width: 6%">#</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price/unit</th>
                                        <th>Amount</th>
                                        <th style="width: 13%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- rows injected by JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                        <td><input type="number"
                                                class="form-control form-control-lg fw-bold text-center" id="grandTotal"
                                                name="grand_total" readonly></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Amount Paid to Vendor:</td>
                                        <td><input type="number"
                                                class="form-control form-control-lg fw-bold text-center text-success" id="paidAmount"
                                                name="paid_amount" min="0" value="0"></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Remaining Due:</td>
                                        <td><input type="number"
                                                class="form-control form-control-lg fw-bold text-center text-danger" id="remainingDue"
                                                name="remaining_due" value="0" readonly></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <button type="button" class="btn btn-success mt-3 d-none" id="addRow">Add More</button>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">Submit</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<style>
    /* ── Product Autocomplete ── */
    .autocomplete-list {
        position: fixed;
        z-index: 99999;
        background: #fff;
        border: 1px solid #dee2e6;
        max-height: 260px;
        overflow-y: auto;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.13);
    }
    .autocomplete-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 7px 10px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
        transition: background 0.13s;
        min-height: 46px;
    }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.active { background: #f0f4ff; }
    .ac-thumb {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #f8f9fa;
    }
    .ac-thumb-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
    }
    .ac-info { display: flex; flex-direction: column; min-width: 0; }
    .ac-name { font-size: 13px; font-weight: 500; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ac-sku  { font-size: 11px; color: #868e96; margin-top: 1px; }

    /* ── Selected product display ── */
    .selected-product-display {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 3px 8px;
        background: #fff;
        border: 1px solid #ced4da;
        border-radius: 4px;
        cursor: pointer;
        min-height: 34px;
        width: 100%;
    }
    .selected-product-display:hover { border-color: #80bdff; }
    .sel-thumb {
        width: 26px;
        height: 26px;
        border-radius: 4px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
    }
    .sel-thumb-placeholder {
        width: 26px;
        height: 26px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        flex-shrink: 0;
    }
    .sel-info { display: flex; flex-direction: column; min-width: 0; flex: 1; }
    .sel-name { font-size: 13px; font-weight: 500; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .sel-sku  { font-size: 10px; color: #868e96; }
    .sel-clear {
        margin-left: auto;
        color: #adb5bd;
        font-size: 14px;
        cursor: pointer;
        padding: 0 2px;
        flex-shrink: 0;
    }
    .sel-clear:hover { color: #e03131; }
    .item-input.ac-hidden { display: none; }

    .row-relative {
        position: relative;
    }

    /* Purchase Table Styling */
    #purchaseTable {
        width: 100%;
        table-layout: fixed;
    }

    #purchaseTable thead th {
        background: #f8f9fa;
        font-weight: 600;
        padding: 12px 8px;
        white-space: nowrap;
        vertical-align: middle;
        font-size: 13px;
    }

    #purchaseTable tbody td {
        padding: 8px 6px;
        vertical-align: middle;
    }

    /* Column Widths */
    #purchaseTable th:nth-child(1),
    #purchaseTable td:nth-child(1) { width: 6%; } /* # */

    #purchaseTable th:nth-child(2),
    #purchaseTable td:nth-child(2) { width: 27%; } /* Product Name */

    #purchaseTable th:nth-child(3),
    #purchaseTable td:nth-child(3) { width: 13%; } /* Quantity */

    #purchaseTable th:nth-child(4),
    #purchaseTable td:nth-child(4) { width: 13%; } /* Unit */

    #purchaseTable th:nth-child(5),
    #purchaseTable td:nth-child(5) { width: 14%; } /* Price/unit */

    #purchaseTable th:nth-child(6),
    #purchaseTable td:nth-child(6) { width: 14%; } /* amount */

    #purchaseTable th:nth-child(7),
    #purchaseTable td:nth-child(7) { width: 13%; } /* Action */

    /* Input Styling in Table */
    #purchaseTable .form-control {
        width: 100% !important;
        padding: 6px 8px;
        font-size: 13px;
        border-radius: 4px;
    }

    #purchaseTable .form-control:focus {
        border-color: #80bdff;
        box-shadow: 0 0 0 0.15rem rgba(0,123,255,.25);
    }

    #purchaseTable .form-control[readonly] {
        background-color: #f8f9fa;
    }

    /* Row Action Buttons */
    #purchaseTable .add-row,
    #purchaseTable .remove-row {
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
    #purchaseTable .add-row {
        margin-right: 4px;
    }
    #purchaseTable .remove-row:disabled {
        opacity: 0.35;
        cursor: not-allowed;
    }

    /* Grand Total Row */
    #purchaseTable tfoot td {
        padding: 15px 8px;
        background: #f8f9fa;
    }

    #purchaseTable tfoot #grandTotal {
        font-size: 16px;
        font-weight: 700;
        text-align: center;
    }

    /* Hover effect on rows */
    #purchaseTable tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>

<script>
    $(document).ready(function () {

        $('#paidAmount').on('input change', function() {
            let paid = parseFloat($(this).val()) || 0;
            if (paid > 0) {
                $('#paymentAccountLabel').html('Payment Account <span class="text-danger">*</span>');
            } else {
                $('#paymentAccountLabel').html('Payment Account');
            }
        });

        // ========== PREVENT ENTER KEY FROM SUBMITTING FORM ==========
        $('#purchaseForm').on('keydown', 'input, select', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                let currentRow = $(this).closest('tr.purchase-row');
                let isLastRow = currentRow.length && currentRow.is('#purchaseTable tbody tr:last');
                if (isLastRow) {
                    appendNewRow();
                    $('#purchaseTable tbody tr:last').find('.item-input').focus();
                } else {
                    let inputs = $('#purchaseForm').find('input:visible, select:visible');
                    let currentIndex = inputs.index(this);
                    if (currentIndex < inputs.length - 1) {
                        inputs.eq(currentIndex + 1).focus();
                    }
                }
                return false;
            }
        });

        // ========== AJAX FORM SUBMISSION ==========
        $('#purchaseForm').on('submit', function (e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]');
            let originalText = submitBtn.html();

            // ========== CLIENT-SIDE VALIDATION ==========
            let hasValidItem = false;
            let errors = [];

            $('#purchaseTable tbody tr').each(function () {
                let row = $(this);
                let input = row.find('.item-input');
                let itemName = input.val().trim();
                let itemId = row.find('.item-id').val();
                let pcs = parseInt(row.find('.pcx').val()) || 0;
                let rate = parseInt(row.find('.rate').val()) || 0;
                let isManualMode = input.attr('data-mode') === 'manual';

                // Check if at least one valid item exists
                if (itemName) {
                    hasValidItem = true;
                    
                    if (!isManualMode && !itemId) {
                        errors.push(`Row ${row.find('.row-index').text()}: Product "${itemName}" not found. Please select a valid product from the dropdown.`);
                    }
                    
                    if (pcs <= 0) {
                        errors.push(`Row ${row.find('.row-index').text()}: Quantity for product "${itemName}" must be greater than 0.`);
                    }
                }
            });

            // Check if party is selected
            if (!$('#party_name').val()) {
                errors.push('Please select a Vendor');
            }

            // Check if payment account is selected when paid_amount > 0
            let paidAmount = parseFloat($('#paidAmount').val()) || 0;
            if (paidAmount > 0 && !$('select[name="account_id"]').val()) {
                errors.push('Please select a Payment Account for the paid amount.');
            }

            // Check if at least one item exists
            if (!hasValidItem) {
                errors.push('At least one complete item is required (with Item Name)');
            }

            // Show client-side errors
            if (errors.length > 0) {
                let errorHtml = '<ul style="text-align:left; margin:0; padding-left:20px;">';
                errors.forEach(function(msg) {
                    errorHtml += '<li>' + msg + '</li>';
                });
                errorHtml += '</ul>';

                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        html: errorHtml
                    });
                } else {
                    alert('Validation Errors:\n' + errors.join('\n'));
                }
                return false;
            }

            // Disable button and show loading
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Submitting...');

            $.ajax({
                url: form.attr('action'),
                method: 'POST',
                data: form.serialize(),
                success: function (response) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (response.success) {
                        // Show success message and redirect
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: response.message || 'Purchase saved successfully!',
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                // Redirect to invoice
                                if (response.redirect) {
                                    window.location.href = response.redirect;
                                }
                            });
                        } else {
                            alert(response.message || 'Purchase saved successfully!');
                            if (response.redirect) {
                                window.location.href = response.redirect;
                            }
                        }
                    }
                },
                error: function (xhr) {
                    submitBtn.prop('disabled', false).html(originalText);

                    if (xhr.status === 422) {
                        // Validation errors
                        let errors = xhr.responseJSON.errors;
                        let errorHtml = '<ul style="text-align:left; margin:0; padding-left:20px;">';

                        for (let field in errors) {
                            errors[field].forEach(function(msg) {
                                errorHtml += '<li>' + msg + '</li>';
                            });
                        }
                        errorHtml += '</ul>';

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Validation Error',
                                html: errorHtml
                            });
                        } else {
                            let errorText = '';
                            for (let field in errors) {
                                errorText += errors[field].join('\n') + '\n';
                            }
                            alert('Validation Errors:\n' + errorText);
                        }
                    } else {
                        // Server error
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Something went wrong. Please try again.'
                            });
                        } else {
                            alert('Something went wrong. Please try again.');
                        }
                    }
                }
            });
        });

        // ========== RESET FORM FUNCTION ==========
        function resetForm() {
            // Reset header fields
            $('#purchase_date').val(new Date().toISOString().split('T')[0]);
            $('#party_name').val('').trigger('change');
            $('.party_code').val('');
            $('#grandTotal').val('');
            $('#paidAmount').val(0);
            $('#remainingDue').val(0);

            // Clear all rows and add fresh 5 rows
            $('#purchaseTable tbody').empty();
            for (let i = 0; i < 5; i++) {
                $('#purchaseTable tbody').append(createRowHtml());
            }
        }

        // Make reset function globally accessible
        window.resetForm = resetForm;

        function updateRowNumbers() {
            $('#purchaseTable tbody tr').each(function (index) {
                $(this).find('.row-index').text(index + 1);
            });
        }

        // ========== ROW CREATION ==========
        function createRowHtml() {
            return `
    <tr class="purchase-row">
        <td class="row-index text-center fw-semibold" data-label="No." style="vertical-align: middle;"></td>
        <td data-label="Product" style="position:relative;">
            <input type="hidden" name="item_id[]" class="item-id">
            <input type="hidden" name="item_image_val[]" class="item-image-val" value="">
            <input type="hidden" name="item_sku_val[]" class="item-sku-val" value="">
            <div class="input-group input-group-sm">
                <button type="button" class="btn btn-outline-secondary mode-toggle px-2" title="Toggle Search/Manual" tabindex="-1">
                    <i class="fas fa-search mode-icon"></i>
                </button>
                <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Search Product" data-mode="search">
            </div>
            <div class="selected-display d-none"></div>
            <div class="autocomplete-list d-none"></div>
        </td>

        <td data-label="Qty">
            <input type="number" class="form-control pcx" name="pcs[]" min="0" value="0">
        </td>

        <td data-label="Unit">
            <input type="text" class="form-control unit" name="unit[]" placeholder="e.g. pcs, box" readonly>
        </td>

        <td data-label="Price">
            <input type="number" class="form-control rate" name="rate[]" min="0">
        </td>

        <td data-label="Amount">
            <input type="number" class="form-control amount" name="amount[]" readonly>
            <!-- Hidden backward-compatible inputs -->
            <input type="hidden" name="measurement[]" class="measurement" value="">
            <input type="hidden" name="gross_total[]" class="gross-total" value="0">
            <input type="hidden" name="discount[]" class="discount" value="0">
            <input type="hidden" name="pcs_carton[]" class="pcs-carton" value="0">
        </td>

        <td data-label="Action">
            <button type="button" class="btn btn-success btn-sm add-row" title="Add row">
                <i class="fas fa-plus"></i>
            </button>
            <button type="button" class="btn btn-danger btn-sm remove-row" title="Delete row">
                <i class="fas fa-times"></i>
            </button>
        </td>
    </tr>`;
        }

        // Initial 5 rows
        for (let i = 0; i < 5; i++) {
            $('#purchaseTable tbody').append(createRowHtml());
        }
        updateRowNumbers();

        function appendNewRow() {
            $('#purchaseTable tbody').append(createRowHtml());
            updateRowNumbers();
            // Scroll to new row
            let newRow = $('#purchaseTable tbody tr').last();
            newRow[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        // Add row (insert after current)
        $(document).on('click', '.add-row', function () {
            let currentRow = $(this).closest('tr.purchase-row');
            let newRow = $(createRowHtml());
            currentRow.after(newRow);
            updateRowNumbers();
            calculateGrandTotal();
            newRow.find('.item-input').focus();
        });

        // Remove row
        $(document).on('click', '.remove-row', function () {
            let rowCount = $('#purchaseTable tbody tr').length;
            if (rowCount > 1) {
                $(this).closest('tr').remove();
                updateRowNumbers();
                calculateGrandTotal();
            }
        });

        // Row Input Mode Toggle
        $(document).on('click', '.mode-toggle', function() {
            let btn = $(this);
            let icon = btn.find('.mode-icon');
            let input = btn.siblings('.item-input');
            let td = btn.closest('td');
            
            if (input.attr('data-mode') === 'search') {
                input.attr('data-mode', 'manual');
                icon.removeClass('fa-search').addClass('fa-keyboard');
                btn.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                input.attr('placeholder', 'Manual Entry');
                td.find('.autocomplete-list').addClass('d-none');
                // Clear selected display so user can type freely
                td.find('.selected-display').addClass('d-none').empty();
                input.removeClass('ac-hidden').val('');
                td.closest('tr').find('.item-id').val('');
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

        // ── Storage base URL ──
        const STORAGE_URL = "{{ asset('storage') }}";

        function acThumb(image) {
            if (image) {
                return `<img src="${STORAGE_URL}/${image}" class="ac-thumb" onerror="this.outerHTML='<div class=ac-thumb-placeholder><svg width=16 height=16 fill=none viewBox=\'0 0 24 24\'><rect width=24 height=24 rx=4 fill=\'#e9ecef\'/><path d=\'M5 19l4-5 3 4 4-6 5 7H5z\' fill=\'#adb5bd\'/></svg></div>'">`;
            }
            return `<div class="ac-thumb-placeholder"><svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="4" fill="#e9ecef"/><path d="M5 19l4-5 3 4 4-6 5 7H5z" fill="#adb5bd"/></svg></div>`;
        }

        function selThumb(image) {
            if (image) {
                return `<img src="${STORAGE_URL}/${image}" class="sel-thumb" onerror="this.outerHTML='<div class=sel-thumb-placeholder><svg width=12 height=12 fill=none viewBox=\'0 0 24 24\'><rect width=24 height=24 rx=4 fill=\'#e9ecef\'/><path d=\'M5 19l4-5 3 4 4-6 5 7H5z\' fill=\'#adb5bd\'/></svg></div>'">`;
            }
            return `<div class="sel-thumb-placeholder"><svg width="12" height="12" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="4" fill="#e9ecef"/><path d="M5 19l4-5 3 4 4-6 5 7H5z" fill="#adb5bd"/></svg></div>`;
        }

        function showSelectedProduct(row, it) {
            let skuHtml = it.item_code ? `<span class="sel-sku">${it.item_code}</span>` : '';
            let html = `<div class="selected-product-display">
                ${selThumb(it.image)}
                <div class="sel-info">
                    <span class="sel-name">${it.item_name}</span>
                    ${skuHtml}
                </div>
                <span class="sel-clear" title="Clear">✕</span>
            </div>`;
            row.find('.selected-display').html(html).removeClass('d-none');
            row.find('.item-input').addClass('ac-hidden').val(it.item_name);
            row.find('.item-id').val(it.id);
            row.find('.item-image-val').val(it.image || '');
            row.find('.item-sku-val').val(it.item_code || '');
        }

        function fetchProducts(input, q) {
            let row = input.closest('tr');
            if (input.attr('data-mode') === 'manual') { $acList.addClass('d-none'); return; }
            $acList.data('row', row);
            $.ajax({
                url: "{{ route('get.items') }}",
                type: "GET",
                data: { q: q },
                success: function (res) {
                    let rect = input[0].getBoundingClientRect();
                    $acList.css({ left: rect.left + 'px', top: rect.bottom + 'px', width: input.outerWidth() + 'px' });
                    if (!Array.isArray(res) || res.length === 0) {
                        $acList.empty().removeClass('d-none');
                        $('<div class="autocomplete-item text-danger not-found-item" style="cursor:default; font-weight:500; justify-content:center;"></div>').text('Not found').appendTo($acList);
                        return;
                    }
                    $acList.empty().removeClass('d-none');
                    res.forEach(it => {
                        let skuHtml = it.item_code ? `<span class="ac-sku">${it.item_code}</span>` : '';
                        $(`<div class="autocomplete-item"></div>`)
                            .append(acThumb(it.image))
                            .append(`<div class="ac-info"><span class="ac-name">${it.item_name}</span>${skuHtml}</div>`)
                            .data('item', it)
                            .appendTo($acList);
                    });
                },
                error: function () { $acList.addClass('d-none'); }
            });
        }

        // ========== AUTOCOMPLETE LOGIC ==========
        $(document).on('focus', '.item-input', function () { fetchProducts($(this), ''); });
        $(document).on('input', '.item-input', function () { 
            let input = $(this);
            input.closest('tr').find('.item-id').val(''); 
            fetchProducts(input, input.val().trim()); 
        });

        // Hide autocomplete when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.item-input, .autocomplete-list, .selected-product-display').length) {
                $acList.addClass('d-none');
            }
        });

        // Select item from autocomplete
        $(document).on('click', '.autocomplete-item:not(.not-found-item)', function () {
            let it = $(this).data('item');
            let row = $acList.data('row');

            if (!row || !row.length) return;

            // TYPE
            row.find('.unit').val(it.unit || 'pcs');

            // MEASUREMENT
            if (it.height && it.width && it.area) {
                row.find('.measurement').val(`${it.height} × ${it.width} = ${it.area} Sq.ft`);
            } else if (it.area) {
                row.find('.measurement').val(`${it.area} Sq.ft`);
            } else {
                row.find('.measurement').val('');
            }

            // Rate
            row.find('.rate').val(parseInt(it.wholesale_price) || 0);

            $acList.addClass('d-none');

            showSelectedProduct(row, it);
            calculateRow(row);
        });

        // Clear selected product
        $(document).on('click', '.sel-clear', function () {
            let row = $(this).closest('tr');
            row.find('.selected-display').addClass('d-none').empty();
            row.find('.item-input').removeClass('ac-hidden').val('').focus();
            row.find('.item-id').val('');
            row.find('.item-image-val').val('');
            row.find('.item-sku-val').val('');
            row.find('.rate').val(0);
            calculateRow(row);
        });

        // ========== CALCULATIONS ==========
        $(document).on('input', '.rate, .pcx, .discount', function () {
            let row = $(this).closest('tr');
            calculateRow(row);
        });

        $(document).on('input', '.amount', function () {
            calculateGrandTotal();
        });

        function calculateRow(row) {
            let rate = parseFloat(row.find('.rate').val()) || 0;
            let pcs = parseFloat(row.find('.pcx').val()) || 0;

            let gross = rate * pcs;
            row.find('.gross-total').val(gross);
            row.find('.amount').val(gross);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            $('.amount').each(function () {
                total += parseInt($(this).val()) || 0;
            });
            $('#grandTotal').val(total);
            calculateRemainingDue();
        }

        function calculateRemainingDue() {
            let grandTotal = parseFloat($('#grandTotal').val()) || 0;
            let paidAmount = parseFloat($('#paidAmount').val()) || 0;
            let remaining = grandTotal - paidAmount;
            $('#remainingDue').val(remaining);
        }

        $(document).on('input', '#paidAmount', function () {
            calculateRemainingDue();
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

        // Make functions globally accessible
        window.createRowHtml = createRowHtml;

    });

    // ========== VENDOR SELECT CHANGE ==========
    $(document).on('change', '.vendor-select', function () {
        let partyCode = $(this).find(':selected').data('code') || '';
        $('.party_code').val(partyCode);
    });

    // ========== SELECT2 FOR VENDOR SEARCH ==========
    $('.vendor-select').select2({
        placeholder: 'Search and select vendor',
        allowClear: true,
        width: '100%'
    });
</script>

