@include('admin_panel.include.header_include')
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
                            <div class="col-md-4">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" id="purchase_date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
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
                            <div class="col-md-4">
                                <label class="form-label">Vendor Code</label>
                                <input type="text" class="form-control party_code" name="party_code" readonly>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th style="width: 50px">#</th>
                                        <th>Product Name</th>
                                        <th>Quantity</th>
                                        <th>Unit</th>
                                        <th>Price/unit</th>
                                        <th>Amount</th>
                                        <th style="width: 100px">Action</th>
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
    /* Simple styles for custom autocomplete dropdown */
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

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background: #e9ecef;
    }

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
    #purchaseTable td:nth-child(1) { width: 50px; } /* # */

    #purchaseTable th:nth-child(2),
    #purchaseTable td:nth-child(2) { width: 220px; } /* Product Name */

    #purchaseTable th:nth-child(3),
    #purchaseTable td:nth-child(3) { width: 100px; } /* Quantity */

    #purchaseTable th:nth-child(4),
    #purchaseTable td:nth-child(4) { width: 100px; } /* Unit */

    #purchaseTable th:nth-child(5),
    #purchaseTable td:nth-child(5) { width: 110px; } /* Price/unit */

    #purchaseTable th:nth-child(6),
    #purchaseTable td:nth-child(6) { width: 120px; } /* amount */

    #purchaseTable th:nth-child(7),
    #purchaseTable td:nth-child(7) { width: 100px; } /* Action */

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
        <td class="row-index text-center fw-semibold" style="vertical-align: middle;"></td>
        <td style="position:relative;">
            <input type="hidden" name="item_id[]" class="item-id">
            <div class="input-group input-group-sm">
                <button type="button" class="btn btn-outline-secondary mode-toggle px-2" title="Toggle Search/Manual" tabindex="-1">
                    <i class="fas fa-search mode-icon"></i>
                </button>
                <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Search Product" data-mode="search">
            </div>
            <div class="autocomplete-list d-none"></div>
        </td>

        <td>
            <input type="number" class="form-control pcx" name="pcs[]" min="0" value="0">
        </td>

        <td>
            <input type="text" class="form-control unit" name="unit[]" placeholder="e.g. pcs, box" readonly>
        </td>

        <td>
            <input type="number" class="form-control rate" name="rate[]" min="0">
        </td>

        <td>
            <input type="number" class="form-control amount" name="amount[]" readonly>
            <!-- Hidden backward-compatible inputs -->
            <input type="hidden" name="measurement[]" class="measurement" value="">
            <input type="hidden" name="gross_total[]" class="gross-total" value="0">
            <input type="hidden" name="discount[]" class="discount" value="0">
            <input type="hidden" name="pcs_carton[]" class="pcs-carton" value="0">
        </td>

        <td>
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
                    if (!Array.isArray(res) || res.length === 0) { 
                        let rect = input[0].getBoundingClientRect();
                        $acList.css({ left: rect.left + 'px', top: rect.bottom + 'px', width: input.outerWidth() + 'px' });
                        $acList.empty().removeClass('d-none');
                        $('<div class="autocomplete-item text-danger not-found-item" style="cursor:default; font-weight: 500;"></div>').text('Not found').appendTo($acList);
                        return; 
                    }
                    let rect = input[0].getBoundingClientRect();
                    $acList.css({ left: rect.left + 'px', top: rect.bottom + 'px', width: input.outerWidth() + 'px' });
                    $acList.empty().removeClass('d-none');
                    res.forEach(it => { $('<div class="autocomplete-item"></div>').text(it.item_name).data('item', it).appendTo($acList); });
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
            if (!$(e.target).closest('.item-input, .autocomplete-list').length) {
                $acList.addClass('d-none');
            }
        });

        // Select item from autocomplete
        $(document).on('click', '.autocomplete-item:not(.not-found-item)', function () {
            let it = $(this).data('item');
            let row = $acList.data('row');

            if (!row || !row.length) return;

            row.find('.item-input').val(it.item_name);
            row.find('.item-id').val(it.id);

            // TYPE
            row.find('.unit').val(it.unit || 'pcs');

            // MEASUREMENT
            if (it.height && it.width && it.area) {
                row.find('.measurement')
                    .val(`${it.height} × ${it.width} = ${it.area} Sq.ft`);
            } else if (it.area) {
                row.find('.measurement').val(`${it.area} Sq.ft`);
            } else {
                row.find('.measurement').val('');
            }

            // Rate
            row.find('.rate').val(parseInt(it.wholesale_price) || 0);

            $acList.addClass('d-none');

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
        }

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

