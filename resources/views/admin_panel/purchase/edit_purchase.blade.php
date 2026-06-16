@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4><i class="fa fa-edit me-2"></i>Edit Purchase</h4>
                    <h6>Update Purchase #{{ $purchase->invoice_number }}</h6>
                </div>
                <a href="{{ route('all-Purchases') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-1"></i>Back to List
                </a>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('update-Purchase', $purchase->id) }}" method="POST" id="editPurchaseForm">
                        @csrf
                        @method('PUT')
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" id="purchase_date"
                                    value="{{ $purchase->purchase_date }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Vendor Name</label>
                                <select name="party_name" id="party_name" class="form-control vendor-select">
                                    <option value="" disabled>Choose One</option>
                                    @foreach($Vendors as $Vendor)
                                        <option value="{{ $Vendor->id }}"
                                            data-code="{{ $Vendor->Party_code }}"
                                            {{ $purchase->party_name == $Vendor->id ? 'selected' : '' }}>
                                            {{ $Vendor->Party_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Vendor Code</label>
                                <input type="text" class="form-control party_code" name="party_code"
                                    value="{{ $purchase->party_code }}" readonly>
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
                                        <th>amount</th>
                                        <th style="width: 100px">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Rows will be populated by JavaScript -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Grand Total:</td>
                                        <td>
                                            <input type="number" class="form-control form-control-lg fw-bold text-center"
                                                id="grandTotal" name="grand_total"
                                                value="{{ $purchase->grand_total }}" readonly>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="d-flex justify-content-between mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fa fa-save me-1"></i>Update Purchase
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<style>
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

    .autocomplete-item:last-child {
        border-bottom: none;
    }

    .autocomplete-item:hover,
    .autocomplete-item.active {
        background: #e9ecef;
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
    #purchaseTable th:nth-child(1), #purchaseTable td:nth-child(1) { width: 50px; } /* # */
    #purchaseTable th:nth-child(2), #purchaseTable td:nth-child(2) { width: 220px; } /* Product Name */
    #purchaseTable th:nth-child(3), #purchaseTable td:nth-child(3) { width: 100px; } /* Quantity */
    #purchaseTable th:nth-child(4), #purchaseTable td:nth-child(4) { width: 100px; } /* Unit */
    #purchaseTable th:nth-child(5), #purchaseTable td:nth-child(5) { width: 110px; } /* Price/unit */
    #purchaseTable th:nth-child(6), #purchaseTable td:nth-child(6) { width: 120px; } /* amount */
    #purchaseTable th:nth-child(7), #purchaseTable td:nth-child(7) { width: 80px; } /* Action */

    #purchaseTable .form-control {
        width: 100% !important;
        padding: 6px 8px;
        font-size: 13px;
        border-radius: 4px;
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

    #purchaseTable tbody tr:hover {
        background-color: #f5f5f5;
    }
</style>

<script>
$(document).ready(function () {

    // Existing purchase data
    @php
        $_items = is_array($purchase->item) ? $purchase->item : (json_decode($purchase->item, true) ?? []);
        $_rates = is_array($purchase->rate) ? $purchase->rate : (json_decode($purchase->rate, true) ?? []);
        $_pcs = is_array($purchase->pcs) ? $purchase->pcs : (json_decode($purchase->pcs, true) ?? []);
        $_discounts = is_array($purchase->discount) ? $purchase->discount : (json_decode($purchase->discount, true) ?? []);
        $_amounts = is_array($purchase->amount) ? $purchase->amount : (json_decode($purchase->amount, true) ?? []);
        $_units = is_array($purchase->product_mode) ? $purchase->product_mode : (json_decode($purchase->product_mode, true) ?? []);
        $_grossTotals = $purchase->gross_total ? (is_array($purchase->gross_total) ? $purchase->gross_total : (json_decode($purchase->gross_total, true) ?? [])) : [];
    @endphp
    const existingItems = @json($_items);
    const existingRates = @json($_rates);
    const existingPcs = @json($_pcs);
    const existingDiscounts = @json($_discounts);
    const existingAmounts = @json($_amounts);
    const existingUnits = @json($_units);
    const existingGrossTotals = @json($_grossTotals);

    // Prevent Enter key from submitting form
    $('#editPurchaseForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let inputs = $('#editPurchaseForm').find('input:visible, select:visible');
            let currentIndex = inputs.index(this);
            if (currentIndex < inputs.length - 1) {
                inputs.eq(currentIndex + 1).focus();
            }
            return false;
        }
    });

    function updateRowNumbers() {
        $('#purchaseTable tbody tr').each(function (index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function createRowHtml(itemName = '', unit = '', measurement = '', rate = '', pcs = '', grossTotal = '', discount = '', amount = '') {
        return `
        <tr class="purchase-row">
            <td class="row-index text-center fw-semibold" style="vertical-align: middle;"></td>
            <td style="position:relative;">
                <input type="hidden" name="item_id[]" class="item-id">
                <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off"
                    placeholder="Type item name" value="${itemName}">
                <div class="autocomplete-list d-none"></div>
            </td>
            <td>
                <input type="number" class="form-control pcx" name="pcs[]" min="1" value="${pcs || 1}">
            </td>
            <td>
                <input type="text" class="form-control unit" name="unit[]"
                    value="${unit}" placeholder="e.g. pcs, box">
            </td>
            <td>
                <input type="number" class="form-control rate" name="rate[]" min="0" value="${rate}">
            </td>
            <td>
                <input type="number" class="form-control amount" name="amount[]"
                    value="${amount}">
                <!-- Hidden backward-compatible inputs -->
                <input type="hidden" name="measurement[]" class="measurement" value="${measurement}">
                <input type="hidden" name="gross_total[]" class="gross-total" value="${grossTotal || 0}">
                <input type="hidden" name="discount[]" class="discount" value="${discount || 0}">
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

    // Populate existing data
    if (existingItems.length > 0) {
        existingItems.forEach((item, index) => {
            let html = createRowHtml(
                item || '',
                existingUnits[index] || '',
                '', // measurement will be fetched if needed
                existingRates[index] || '',
                existingPcs[index] || '',
                existingGrossTotals[index] || '',
                existingDiscounts[index] || '',
                existingAmounts[index] || ''
            );
            $('#purchaseTable tbody').append(html);
        });
    } else {
        // Add empty rows if no existing data
        for (let i = 0; i < 3; i++) {
            $('#purchaseTable tbody').append(createRowHtml());
        }
    }
    updateRowNumbers();

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

    // Enter key: auto-add new row when pressing Enter in last row
    $('#editPurchaseForm').on('keydown', 'input, select', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let currentRow = $(this).closest('tr.purchase-row');
            if (currentRow.length && currentRow.is('#purchaseTable tbody tr:last')) {
                let newRow = $(createRowHtml());
                currentRow.after(newRow);
                updateRowNumbers();
                calculateGrandTotal();
                newRow.find('.item-input').focus();
            }
            return false;
        }
    });

    // Autocomplete search
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
        row.find('.unit').val(it.unit || '');

        if (it.height && it.width && it.area) {
            row.find('.measurement').val(`${it.height} × ${it.width} = ${it.area} Sq.ft`);
        } else if (it.area) {
            row.find('.measurement').val(`${it.area} Sq.ft`);
        } else {
            row.find('.measurement').val('');
        }

        row.find('.rate').val(parseInt(it.retail_price) || 0);
        row.find('.autocomplete-list').addClass('d-none');

        calculateRow(row);
    });

    // Calculations
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

    // Vendor select change
    $(document).on('change', '.vendor-select', function () {
        let partyCode = $(this).find(':selected').data('code') || '';
        $('.party_code').val(partyCode);
    });

    // Initialize Select2 for vendor search
    $('.vendor-select').select2({
        placeholder: 'Search and select vendor',
        allowClear: true,
        width: '100%'
    });

    // Calculate totals for existing data
    calculateGrandTotal();
});
</script>
