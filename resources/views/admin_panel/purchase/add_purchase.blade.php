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
                                        <th>Type</th>
                                        <th>Measurement</th>
                                        <th>Rate</th>
                                        {{-- <th>Carton Qty</th> --}}
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

        function createRowHtml() {
            return `
    <tr class="purchase-row">
        <td style="position:relative; min-width:185px;">
            <input type="hidden" name="item_id[]" class="item-id">
            <input type="text" class="form-control item-input" name="item_name[]" autocomplete="off" placeholder="Type item name">
            <div class="autocomplete-list d-none"></div>
        </td>

        <!-- TYPE - MUST HAVE name attribute -->
        <td>
            <input type="text"
                   class="form-control product_mode"
                   name="product_mode[]"
                   style="width:130px"
                   readonly>
        </td>

        <!-- MEASUREMENT - MUST HAVE name attribute -->
        <td>
            <input type="text"
                   class="form-control measurement"
                   name="measurement[]"
                   style="width:140px"
                   readonly>
        </td>

        <td>
            <input type="number" class="form-control rate" name="rate[]" min="0" style="width: 80px;">
        </td>

        <td>
            <input type="number" class="form-control pcx" name="pcs[]" min="0" style="width: 80px;">
        </td>

        <td>
            <input type="number" class="form-control gross-total" name="gross_total[]" readonly>
        </td>

        <td>
            <input type="number" class="form-control discount" name="discount[]" min="0" style="width: 80px;">
        </td>

        <td>
            <input type="number" class="form-control amount" name="amount[]" readonly style="width: 80px;">
        </td>

        <td>
            <button type="button" class="btn btn-danger remove-row">Delete</button>
        </td>
    </tr>`;
        }

        // initial rows
        for (let i = 0; i < 5; i++) {
            $('#purchaseTable tbody').append(createRowHtml());
        }

        function appendNewRow() {
            $('#purchaseTable tbody').append(createRowHtml());
        }

        // remove row
        $(document).on('click', '.remove-row', function () {
            $(this).closest('tr').remove();
            calculateGrandTotal();
        });

        // autocomplete search
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

        // select item
        $(document).on('click', '.autocomplete-item', function () {
            let it = $(this).data('item');
            let row = $(this).closest('tr');

            row.find('.item-input').val(it.item_name);
            row.find('.item-id').val(it.id);

            // TYPE - will now be submitted
            row.find('.product_mode').val(it.product_mode || '');

            // MEASUREMENT FULL TEXT - will now be submitted
            if (it.height && it.width && it.area) {
                row.find('.measurement')
                    .val(`${it.height} × ${it.width} = ${it.area} Sq.ft`);
            } else if (it.area) {
                row.find('.measurement').val(`${it.area} Sq.ft`);
            } else {
                row.find('.measurement').val('');
            }

            // rate
            row.find('.rate').val(parseInt(it.retail_price) || 0);

            row.find('.autocomplete-list').addClass('d-none');

            calculateRow(row);
            autoAddIfNeeded();
        });

        // calculations (NO DECIMALS)
        $(document).on('input', '.rate, .pcx, .discount', function () {
            let row = $(this).closest('tr');
            calculateRow(row);
            autoAddIfNeeded();
        });

        function calculateRow(row) {
            let rate = parseInt(row.find('.rate').val()) || 0;
            let pcs = parseInt(row.find('.pcx').val()) || 0;
            let discount = parseInt(row.find('.discount').val()) || 0;

            let gross = rate * pcs;
            row.find('.gross-total').val(gross);

            let finalAmount = gross - discount;
            row.find('.amount').val(finalAmount);

            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            $('.amount').each(function () {
                total += parseInt($(this).val()) || 0;
            });
            $('#grandTotal').val(total);
        }

        function autoAddIfNeeded() {
            let lastRow = $('#purchaseTable tbody tr').last();
            let hasData =
                lastRow.find('.item-input').val().trim() ||
                parseInt(lastRow.find('.rate').val()) > 0 ||
                parseInt(lastRow.find('.pcx').val()) > 0;

            if (hasData) {
                let emptyExists = false;
                $('#purchaseTable tbody tr').each(function () {
                    let r = $(this);
                    if (
                        !r.find('.item-input').val().trim() &&
                        !parseInt(r.find('.rate').val()) &&
                        !parseInt(r.find('.pcx').val())
                    ) {
                        emptyExists = true;
                    }
                });

                if (!emptyExists) {
                    appendNewRow();
                }
            }
        }

    });
</script>


{{-- Place just before existing script tag --}}

<script>
    // repopulate rows from old input if validation failed
    const oldItems = @json(old('item_name', []));
    const oldRates = @json(old('rate', []));
    const oldCartons = @json(old('product_mode', []));
    const measurement = @json(old('measurement', []));
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
                last.find('.measurement').val(oldCartons[i] ?? '');
                last.find('.pcx').val(oldPcs[i] ?? '');
                last.find('.gross-total').val(oldGross[i] ?? '');
                last.find('.discount').val(oldDiscount[i] ?? '');
                last.find('.amount').val(oldAmount[i] ?? '');
                last.find('.product_mode').val(oldPcsCarton[i] ?? '');
            }
            // after restoring old rows ensure an empty row exists at the end
            autoAddIfNeeded();
        }
    });

    $(document).on('change', '.vendor-select', function () {
        let partyCode = $(this).find(':selected').data('code') || '';
        $('.party_code').val(partyCode);
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