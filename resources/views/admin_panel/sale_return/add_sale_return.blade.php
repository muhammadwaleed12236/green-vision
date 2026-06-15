{{-- resources/views/admin_panel/sale_return/sale_return_form.blade.php --}}
@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Sale Return Management</h4>
                    <h6>Manage Sale Return Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">
                    <form action="{{ route('sale-return.store') }}" method="POST" id="sale-return-form">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label for="sale_type" class="form-label">Select Sale Type</label>
                                @if(Auth::check() && Auth::user()->usertype === 'admin')
                                    <select class="form-select" id="sale_type" name="sale_type" required>
                                        <option value="">-- Select Sale Type --</option>
                                        <option value="customer" selected>Local Customer Sale</option>
                                    </select>
                                @elseif(Auth::check() && Auth::user()->usertype === 'distributor')
                                    <select class="form-select" id="sale_type" name="sale_type" required>
                                        <option value="">-- Select Sale Type --</option>
                                        <option value="customer" selected>Local Customer Sale</option>
                                    </select>
                                @endif
                            </div>

                            <div class="col-md-4">
                                <label for="invoice_date" class="form-label">Filter by Date</label>
                                <input type="date" id="invoice_date" class="form-control" name="invoice_date" />
                                <small class="text-muted">Leave empty to show all invoices</small>
                            </div>

                            <div class="col-md-4">
                                <label for="invoice_number" class="form-label">Invoice (Number — Shop — Item)</label>
                                <select class="form-select" id="invoice_number" name="invoice_number" required>
                                    <option value="">-- Select Invoice Number --</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="party_id" class="form-label">Party ID</label>
                                <input type="text" class="form-control" id="party_id" name="party_id" readonly>
                            </div>

                            <div class="col-md-6 d-flex align-items-end justify-content-end">
                                <div>
                                    <button type="button" class="btn btn-success me-2" id="searchSale">
                                        <i class="fa fa-search"></i> Search
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="refreshInvoices">
                                        <i class="fa fa-sync"></i> Refresh Invoices
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Static Table for Displaying Sale Data -->
                        <div id="sale-details-section" class="mt-4">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead id="return-table-thead">
                                        <tr class="text-center">
                                            <th>Product Name</th>
                                            <th>Original Qty</th>
                                            <th>Unit</th>
                                            <th>Price/unit</th>
                                            <th>Total Amount</th>
                                            <th>Return Qty</th>
                                            <th>Return Amount</th>
                                        </tr>
                                    </thead>

                                    <tbody id="return-table-body" class="text-center align-middle">
                                        {{-- Rows will be generated dynamically via JS --}}
                                    </tbody>

                                    <tfoot class="text-end fw-bold">
                                        <tr>
                                            <td colspan="5">Gross Amount:</td>
                                            <td id="grossAmount">0.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">Discount Amount:</td>
                                            <td id="discountAmount">0.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">Scheme Amount:</td>
                                            <td id="schemeAmount">0.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">Net Amount:</td>
                                            <td id="netAmount">0.00</td>
                                        </tr>
                                        <tr>
                                            <td colspan="5">Total Return Amount:</td>
                                            <td id="totalReturnAmount">0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div class="d-flex justify-content-end mt-3">
                                    <button type="submit" class="btn btn-primary" id="submitReturn">Submit
                                        Return</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<!-- Styles -->
<style>
    .table th,
    .table td {
        vertical-align: middle !important;
    }

    .form-control-plaintext {
        background-color: #f1f1f1;
        text-align: center;
        font-weight: bold;
    }

    tfoot td {
        background-color: #f9f9f9;
    }

    .table tfoot tr:last-child td {
        background-color: #ffe5e5;
        color: #b30000;
        font-size: 1rem;
    }
</style>

<!-- Scripts: jQuery + page logic -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Add these two libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function () {
        // Load invoices for the initial sale_type (if set)
        function loadInvoices() {
            let saleType = $('#sale_type').val() || 'customer';
            let date = $('#invoice_date').val() || '';

            $('#invoice_number').html('<option value="">Loading...</option>');

            $.ajax({
                url: '{{ route("get-sale-invoices") }}',
                type: 'GET',
                data: { sale_type: saleType, date: date },
                success: function (data) {
                    let options = '<option value="">-- Select Invoice Number --</option>';
                    if (Array.isArray(data) && data.length) {
                        $.each(data, function (i, inv) {
                            // store party_id & first_item in data attributes
                            options += `<option value="${inv.invoice_number}" data-party-id="${inv.party_id ?? ''}" data-first-item="${inv.first_item ?? ''}">${inv.label}</option>`;
                        });
                    } else {
                        options = '<option value="">No invoices found</option>';
                    }
                    $('#invoice_number').html(options).trigger('change');
                },
                error: function () {
                    $('#invoice_number').html('<option value="">Failed to load</option>');
                }
            });
        }

        // Initial load
        loadInvoices();

        // When sale type or date changes -> reload invoices
        $('#sale_type, #invoice_date').on('change', loadInvoices);
        $('#refreshInvoices').on('click', loadInvoices);

        // When invoice selected, auto-fill party id if present
        $('#invoice_number').on('change', function () {
            let sel = $(this).find('option:selected');
            $('#party_id').val(sel.attr('data-party-id') || '');
        });

        // Search and show sale details
        $('#searchSale').on('click', function () {
            let saleType = $('#sale_type').val();
            let invoiceNumber = $('#invoice_number').val();

            if (!saleType) {
                alert('Please select Sale Type.');
                return;
            }

            if (!invoiceNumber) {
                alert('Please select an Invoice Number.');
                return;
            }

            $.ajax({
                url: '{{ route("fetch-sale-details") }}',
                type: 'GET',
                data: { sale_type: saleType, invoice_number: invoiceNumber },
                success: function (response) {
                    if (!response.success) {
                        alert(response.message || 'No sale details found.');
                        return;
                    }

                    let theadHTML = '';
                    let tableHTML = '';
                    let grossAmount = 0;

                    if (saleType === 'customer') {
                        theadHTML = `
                            <tr class="text-center">
                                <th>Product Name</th>
                                <th>Original Qty</th>
                                <th>Unit</th>
                                <th>Price/unit</th>
                                <th>Total Amount</th>
                                <th style="width: 15%;">Return Qty</th>
                                <th style="width: 15%;">Return Amount</th>
                            </tr>
                        `;

                        $.each(response.sales, function (index, sale) {
                            let rate = parseFloat(sale.rate) || 0;
                            let itemAmount = parseFloat(sale.item_total) || 0;
                            grossAmount += itemAmount;

                            let itemID = sale.item_id ? sale.item_id : '';

                            tableHTML += `<tr data-item-id="${itemID}">
                                <td>${sale.item}</td>
                                <td class="text-center">${sale.qty}</td>
                                <td class="text-center">${sale.unit}</td>
                                <td class="text-center">${parseFloat(rate).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(itemAmount).toFixed(2)}</td>
                                <td><input type="number" min="0" max="${sale.qty}" class="form-control return-pcs-qty" data-index="${index}" data-rate="${rate}" value="0"></td>
                                <td><input type="text" class="form-control-plaintext return-amount" readonly value="0"></td>
                            </tr>`;
                        });
                    } else {
                        theadHTML = `
                            <tr class="text-center">
                                <th>Product Name</th>
                                <th>Pcs/Carton</th>
                                <th>Carton Qty</th>
                                <th>Pcs Qty</th>
                                <th>Liter</th>
                                <th>Rate</th>
                                <th>Discount</th>
                                <th>Total</th>
                                <th style="width: 12%;">Return Carton Qty</th>
                                <th style="width: 12%;">Return Pcs Qty</th>
                                <th style="width: 15%;">Return Amount</th>
                            </tr>
                        `;

                        $.each(response.sales, function (index, sale) {
                            let rate = parseFloat(sale.rate) || 0;
                            let itemAmount = parseFloat(sale.item_total) || 0;
                            grossAmount += itemAmount;

                            let itemID = sale.item_id ? sale.item_id : '';

                            tableHTML += `<tr data-item-id="${itemID}">
                                <td>${sale.item}</td>
                                <td class="text-center">${sale.packing}</td>
                                <td class="text-center">${sale.carton_quantity}</td>
                                <td class="text-center">${sale.pcs_quantity}</td>
                                <td class="text-center">${sale.liter ?? ''}</td>
                                <td class="text-center">${parseFloat(rate).toFixed(2)}</td>
                                <td class="text-center">${parseFloat(sale.discount_amount || 0).toFixed(2)}</td>
                                <td class="text-end">${parseFloat(itemAmount).toFixed(2)}</td>
                                <td><input type="number" min="0" max="${sale.carton_quantity}" class="form-control return-carton-qty" data-index="${index}" data-rate="${rate}" value="0"></td>
                                <td><input type="number" min="0" max="${sale.pcs_quantity}" class="form-control return-pcs-qty" data-index="${index}" data-rate="${rate}" value="0"></td>
                                <td><input type="text" class="form-control-plaintext return-amount" readonly value="0"></td>
                            </tr>`;
                        });
                    }

                    $('#return-table-thead').html(theadHTML);
                    $('#return-table-body').html(tableHTML);

                    let colSpanVal = saleType === 'customer' ? 5 : 8;
                    $('#grossAmount').closest('tr').find('td:first').attr('colspan', colSpanVal);
                    $('#discountAmount').closest('tr').find('td:first').attr('colspan', colSpanVal);
                    $('#schemeAmount').closest('tr').find('td:first').attr('colspan', colSpanVal);
                    $('#netAmount').closest('tr').find('td:first').attr('colspan', colSpanVal);
                    $('#totalReturnAmount').closest('tr').find('td:first').attr('colspan', colSpanVal);

                    $('#grossAmount').text(grossAmount.toFixed(2));
                    $('#discountAmount').text(parseFloat(response.summary.discount_value || 0).toFixed(2));
                    $('#schemeAmount').text(parseFloat(response.summary.scheme_value || 0).toFixed(2));
                    $('#netAmount').text(parseFloat(response.summary.net_amount || 0).toFixed(2));
                    $('#totalReturnAmount').text('0.00');
                    $('#party_id').val(response.party_id || '');
                },
                error: function () {
                    alert('Sale details not found.');
                }
            });
        });

        // Recalculate return amount when user inputs quantities
        $(document).on('input', '.return-carton-qty, .return-pcs-qty', function () {
            let saleType = $('#sale_type').val() || 'customer';
            let $row = $(this).closest('tr');
            let returnAmount = 0;

            if (saleType === 'customer') {
                let returnPcsQty = parseFloat($row.find('.return-pcs-qty').val()) || 0;
                let rate = parseFloat($row.find('.return-pcs-qty').data('rate')) || 0;
                returnAmount = rate * returnPcsQty;
            } else {
                let returnCartonQty = parseFloat($row.find('.return-carton-qty').val()) || 0;
                let returnPcsQty = parseFloat($row.find('.return-pcs-qty').val()) || 0;
                let rate = parseFloat($row.find('.return-carton-qty').data('rate')) || 0;
                let pcsPerCarton = parseFloat($row.find('td:nth-child(2)').text()) || 1;
                returnAmount = (rate * returnCartonQty) + ((rate / pcsPerCarton) * returnPcsQty);
            }
            $row.find('.return-amount').val(returnAmount.toFixed(2));

            // update total
            let totalReturnAmount = 0;
            $('.return-amount').each(function () {
                totalReturnAmount += parseFloat($(this).val()) || 0;
            });
            $('#totalReturnAmount').text(totalReturnAmount.toFixed(2));
        });

        // Submit return (AJAX)
        $('#sale-return-form').on('submit', function (e) {
            e.preventDefault();

            let saleType = $('#sale_type').val();
            let invoiceNumber = $('#invoice_number').val();
            let partyId = $('#party_id').val();

            let returnItems = [];
            $('#return-table-body tr').each(function () {
                let $tr = $(this);
                let item = {};

                if (saleType === 'customer') {
                    item = {
                        item_id: $tr.data('item-id') || null,
                        item_name: $tr.find('td:eq(0)').text(),
                        pcs_per_carton: null,
                        carton_qty: 0,
                        pcs_qty: parseInt($tr.find('.return-pcs-qty').val()) || 0,
                        rate: parseFloat($tr.find('td:eq(3)').text()) || 0,
                        discount: 0,
                        total: parseFloat($tr.find('.return-amount').val()) || 0
                    };
                } else {
                    item = {
                        item_id: $tr.data('item-id') || null,
                        item_name: $tr.find('td:eq(0)').text(),
                        pcs_per_carton: parseInt($tr.find('td:eq(1)').text()) || 0,
                        carton_qty: parseInt($tr.find('.return-carton-qty').val()) || 0,
                        pcs_qty: parseInt($tr.find('.return-pcs-qty').val()) || 0,
                        rate: parseFloat($tr.find('td:eq(5)').text()) || 0,
                        discount: parseFloat($tr.find('td:eq(6)').text()) || 0,
                        total: parseFloat($tr.find('.return-amount').val()) || 0
                    };
                }

                if (item.carton_qty > 0 || item.pcs_qty > 0) {
                    returnItems.push(item);
                }
            });

            if (returnItems.length === 0) {
                Swal.fire({ icon: 'warning', title: 'No items selected', text: 'Please enter return quantity for at least one item.' });
                return;
            }

            // Compose payload
            let payload = {
                sale_type: saleType,
                invoice_number: invoiceNumber,
                party_id: partyId,
                invoice_date: $('#invoice_date').val() || null,
                return_items: returnItems,
                _token: '{{ csrf_token() }}'
            };

            $.ajax({
                url: '{{ route("sale-return.store") }}',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(payload),
                success: function (res) {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Saved', text: res.message || 'Sale return recorded.' }).then(() => {
                            // reset form and reload invoices
                            $('#sale-return-form')[0].reset();
                            $('#return-table-body').html('');
                            $('#grossAmount, #discountAmount, #schemeAmount, #netAmount, #totalReturnAmount').text('0.00');
                            loadInvoices();
                        });
                    } else {
                        Swal.fire({ icon: 'error', title: 'Failed', text: res.message || 'Could not save return.' });
                    }
                },
                error: function (xhr) {
                    let msg = 'Failed to submit sale return. Please try again.';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    Swal.fire({ icon: 'error', title: 'Error', text: msg });
                }
            });
        });
    });
</script>
