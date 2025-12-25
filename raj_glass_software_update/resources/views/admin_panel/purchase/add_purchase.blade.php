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

            <div class="card p-4">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif
                    <form action="{{ route('store-Purchase') }}" method="POST">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Purchase Date</label>
                                <input type="date" class="form-control" name="purchase_date" id="purchase_date"
                                    value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Party Name</label>
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
                                <label class="form-label">Party Code</label>
                                <input type="text" class="form-control party_code" name="party_code" readonly>
                            </div>

                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle text-center" id="purchaseTable">
                                <thead>
                                    <tr>
                                        <th style="width:100px">Item</th>
                                        <th>Pcs/Carton</th>
                                        <th>Rate (Per Carton)</th>
                                        <th>Carton Qty</th>
                                        <th>Pcs</th>
                                        <th>Gross Total</th>
                                        <th>Discount</th>
                                        <th>Amount</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- rows injected by JS -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Grand Total:</td>
                                        <td colspan="3"><input type="number"
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
        position: absolute;
        z-index: 9999;
        background: #fff;
        border: 1px solid #ddd;
        max-height: 220px;
        overflow-y: auto;
        width: 100%;
    }

    .autocomplete-item {
        padding: 6px 8px;
        cursor: pointer;
    }

    .autocomplete-item.active {
        background: #f0f0f0;
    }

    .row-relative {
        position: relative;
    }
</style>

<script>
    $(document).ready(function () {
        // Row HTML generator (direct item input). IMPORTANT: includes hidden item_id[] input.
        function createRowHtml() {
            return `
            <tr class="purchase-row row-relative">
                <td style="position:relative; min-width:200px;">
                    <input type="hidden" name="item_id[]" class="item-id">
                    <input type="text" class="form-control form-control-lg item-input" name="item_name[]" autocomplete="off" placeholder="Type item name..." style="width:100%;">
                    <div class="autocomplete-list d-none"></div>
                </td>
                <td><input type="number" class="form-control form-control-lg pcs-carton" name="pcs_carton[]" style="width:120px;" readonly></td>
                <td><input type="number" class="form-control form-control-lg rate" name="rate[]" style="width:140px;"></td>
                <td><input type="number" class="form-control form-control-lg carton-qty" name="carton_qty[]" style="width:120px;"></td>
                <td><input type="number" class="form-control form-control-lg pcx" name="pcs[]" style="width:120px;"></td>
                <td><input type="number" class="form-control form-control-lg gross-total" name="gross_total[]" style="width:140px;" readonly></td>
                <td><input type="number" class="form-control form-control-lg discount" name="discount[]" style="width:120px;"></td>
                <td><input type="number" class="form-control form-control-lg amount" name="amount[]" style="width:140px;" readonly></td>
                <td><button type="button" class="btn btn-danger remove-row">Delete</button></td>
            </tr>`;
        }

        // Initialize 5 rows
        for (let i = 0; i < 5; i++) {
            $('#purchaseTable tbody').append(createRowHtml());
        }

        function appendNewRow() {
            $('#purchaseTable tbody').append(createRowHtml());
        }

        // Remove row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        // AUTOCOMPLETE: fetch matching items from server on input
        // Expected server response: [{ id, item_name, size, pcs_in_carton, retail_price }]
        $(document).on('input', '.item-input', function () {
            let input = $(this);
            let q = input.val().trim();
            let row = input.closest('tr');
            let list = row.find('.autocomplete-list');

            if (!q) {
                list.addClass('d-none');
                return;
            }

            // AJAX to search items globally
            $.ajax({
                url: "{{ route('get.items') }}", // ensure this route accepts ?q=...
                type: 'GET',
                data: { q: q },
                success: function (response) {
                    if (!Array.isArray(response) || response.length === 0) {
                        list.addClass('d-none');
                        return;
                    }

                    // sort client-side: startsWith first
                    let qLower = q.toLowerCase();
                    response.sort(function (a, b) {
                        let an = (a.item_name || '').toLowerCase();
                        let bn = (b.item_name || '').toLowerCase();
                        let aStarts = an.startsWith(qLower) ? 1 : 0;
                        let bStarts = bn.startsWith(qLower) ? 1 : 0;
                        if (aStarts !== bStarts) return bStarts - aStarts;
                        return an.localeCompare(bn);
                    });

                    list.empty().removeClass('d-none');
                    response.forEach(function (it, idx) {
                        let el = $(`<div class="autocomplete-item" data-idx="${idx}">${it.item_name}</div>`);
                        el.data('item', it);
                        list.append(el);
                    });
                    list.data('active', -1);
                },
                error: function () {
                    list.addClass('d-none');
                }
            });
        });

        // When user presses Enter while input empty -> fetch limited all products
        $(document).on('keydown', '.item-input', function (e) {
            let input = $(this);
            let row = input.closest('tr');
            let list = row.find('.autocomplete-list');

            // navigation / selection
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp' || e.key === 'Escape' || e.key === 'Enter') {
                // reuse logic: if dropdown visible handle nav/select
                let items = list.find('.autocomplete-item');
                let active = parseInt(list.data('active')) || -1;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (items.length === 0) return;
                    active = Math.min(active + 1, items.length - 1);
                    items.removeClass('active');
                    $(items[active]).addClass('active');
                    list.data('active', active);
                    return;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (items.length === 0) return;
                    active = Math.max(active - 1, 0);
                    items.removeClass('active');
                    $(items[active]).addClass('active');
                    list.data('active', active);
                    return;
                } else if (e.key === 'Escape') {
                    list.addClass('d-none');
                    return;
                } else if (e.key === 'Enter') {
                    // if dropdown visible -> select active or first
                    if (!list.hasClass('d-none')) {
                        e.preventDefault();
                        let act = parseInt(list.data('active')) || -1;
                        if (act >= 0) {
                            $(list.find('.autocomplete-item')[act]).trigger('click');
                            return;
                        } else {
                            if (items.length >= 1) {
                                $(items[0]).trigger('click');
                                return;
                            }
                        }
                    }

                    // if input EMPTY -> fetch limited "all products"
                    if (input.val().trim() === '') {
                        e.preventDefault();
                        $.ajax({
                            url: "{{ route('get.items') }}",
                            type: 'GET',
                            data: { q: '' }, // server returns limited list
                            success: function (response) {
                                if (!Array.isArray(response) || response.length === 0) {
                                    row.find('.autocomplete-list').addClass('d-none');
                                    return;
                                }
                                let listEl = row.find('.autocomplete-list');
                                listEl.empty().removeClass('d-none');
                                response.forEach(function (it, idx) {
                                    let el = $(`<div class="autocomplete-item" data-idx="${idx}">${it.item_name}</div>`);
                                    el.data('item', it);
                                    listEl.append(el);
                                });
                                listEl.data('active', -1);
                            },
                            error: function () {
                                row.find('.autocomplete-list').addClass('d-none');
                            }
                        });
                    }
                    return;
                }
            }
        });

        // click on autocomplete item: fill row fields and set item_id
        $(document).on('click', '.autocomplete-item', function () {
            let it = $(this).data('item');
            let row = $(this).closest('tr');
            row.find('.item-input').val(it.item_name);
            row.find('.item-id').val(it.id);
            row.find('.size').val(it.size || '');
            row.find('.pcs-carton').val(it.pcs_in_carton || '');
            // optional: set default rate if returned
            if (it.retail_price) row.find('.rate').val(it.retail_price);
            row.find('.autocomplete-list').addClass('d-none');

            // trigger calculation in case cartons/pcs/rate already present
            row.find('.rate, .carton-qty, .pcx, .discount').trigger('input');
            autoAddIfNeeded();
        });

        // hide autocomplete on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.item-input').length && !$(e.target).closest('.autocomplete-list').length) {
                $('.autocomplete-list').addClass('d-none');
            }
        });

        // vendor select to fill party code
        $(document).on('change', '.vendor-select', function () {
            let partycode = $(this).find(":selected").data('code') || '';
            $(".party_code").val(partycode);
        });

        // Calculations: compute gross-total and amount (no liters)
        $(document).on('input', '.carton-qty, .pcs-carton, .pcx, .rate, .discount', function () {
            let row = $(this).closest('tr');

            let cartonQty = parseFloat(row.find('.carton-qty').val()) || 0;
            let packing = parseFloat(row.find('.pcs-carton').val()) || 0;
            let pcsQty = parseFloat(row.find('.pcx').val()) || 0;
            let rate = parseFloat(row.find('.rate').val()) || 0;
            let discount = parseFloat(row.find('.discount').val()) || 0;

            // Carton amount
            let cartonAmount = rate * cartonQty;

            // Per-piece rate and pcs amount
            let perPieceRate = (packing > 0) ? (rate / packing) : 0;
            let pcsAmount = perPieceRate * pcsQty;

            // Gross total (before discount)
            let gross = cartonAmount + pcsAmount;
            row.find('.gross-total').val(parseFloat(gross.toFixed(2)).toString());

            // Final amount after discount
            let finalAmount = gross - discount;
            row.find('.amount').val(parseFloat(finalAmount.toFixed(2)).toString());

            calculateGrandTotal();
            autoAddIfNeeded();
        });

        // Calculate Grand Total
        function calculateGrandTotal() {
            let grandTotal = 0;
            $(".amount").each(function () {
                grandTotal += parseFloat($(this).val()) || 0;
            });

            $("#grandTotal").val(parseFloat(grandTotal.toFixed(2)).toString());
        }

        // Auto-add new row when last row has some data (item/rate/qty)
        function autoAddIfNeeded() {
            let lastRow = $('#purchaseTable tbody tr').last();
            if (!lastRow.length) return;

            let itemVal = lastRow.find('.item-input').val().trim();
            let rateVal = parseFloat(lastRow.find('.rate').val()) || 0;
            let cartonVal = parseFloat(lastRow.find('.carton-qty').val()) || 0;
            let pcsVal = parseFloat(lastRow.find('.pcx').val()) || 0;

            let hasData = (itemVal || rateVal > 0 || cartonVal > 0 || pcsVal > 0);

            if (hasData) {
                // check if there is already an empty final row
                let emptyRowsAfter = 0;
                $('#purchaseTable tbody tr').each(function (index, tr) {
                    let r = $(tr);
                    let it = r.find('.item-input').val().trim();
                    let rt = parseFloat(r.find('.rate').val()) || 0;
                    let cq = parseFloat(r.find('.carton-qty').val()) || 0;
                    let p = parseFloat(r.find('.pcx').val()) || 0;
                    if (!(it || rt > 0 || cq > 0 || p > 0)) emptyRowsAfter++;
                });
                if (emptyRowsAfter === 0) {
                    appendNewRow();
                }
            }
        }

        // manual addRow (hidden button)
        $(document).on('click', '#addRow', function () {
            appendNewRow();
        });

        // Set today's date if not set
        if (!$('#purchase_date').val()) {
            let today = new Date();
            let m = (today.getMonth() + 1).toString().padStart(2, '0');
            let d = today.getDate().toString().padStart(2, '0');
            $('#purchase_date').val(`${today.getFullYear()}-${m}-${d}`);
        }
    });
</script>
{{-- Place just before existing script tag --}}

<script>
    // repopulate rows from old input if validation failed
    const oldItems = @json(old('item_name', []));
    const oldRates = @json(old('rate', []));
    const oldCartons = @json(old('carton_qty', []));
    const oldPcs = @json(old('pcs', []));
    const oldGross = @json(old('gross_total', []));
    const oldDiscount = @json(old('discount', []));
    const oldAmount = @json(old('amount', []));
    const oldPcsCarton = @json(old('pcs_carton', []));

    $(document).ready(function () {
        // if old inputs exist, clear tbody and populate from old arrays
        if (oldItems && oldItems.length > 0) {
            $('#purchaseTable tbody').empty();
            for (let i = 0; i < oldItems.length; i++) {
                let html = createRowHtml();
                $('#purchaseTable tbody').append(html);
                let last = $('#purchaseTable tbody tr').last();
                last.find('.item-input').val(oldItems[i] || '');
                last.find('.rate').val(oldRates[i] ?? '');
                last.find('.carton-qty').val(oldCartons[i] ?? '');
                last.find('.pcx').val(oldPcs[i] ?? '');
                last.find('.gross-total').val(oldGross[i] ?? '');
                last.find('.discount').val(oldDiscount[i] ?? '');
                last.find('.amount').val(oldAmount[i] ?? '');
                last.find('.pcs-carton').val(oldPcsCarton[i] ?? '');
            }
            // after restoring old rows ensure an empty row exists at the end
            autoAddIfNeeded();
        }
    });

    // SweetAlert for errors
    @if ($errors->any())
        let errorList = [];
        @foreach ($errors->all() as $err)
            errorList.push(@json($err));
        @endforeach

        $(document).ready(function () {
            let html = '<ul style="text-align:left;">';
            errorList.forEach(function (e) { html += '<li>' + e + '</li>'; });
            html += '</ul>';
            // require SweetAlert2 library in layout, or use native alert as fallback
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Validation error',
                    html: html,
                });
            } else {
                alert(errorList.join("\n"));
            }
        });
    @endif
</script>