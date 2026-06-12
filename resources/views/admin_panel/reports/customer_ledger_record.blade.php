@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">Customer Ledger Report</h3>

                    <form id="ledgerSearchForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="Customer">Select Customer</label>
                                <select id="Customer" class="form-control">
                                    <option value="">-- Select Customer --</option>
                                    @foreach($Customers as $Customer)
                                        <option value="{{ $Customer->id }}" data-contact="{{ $Customer->phone_number }}"
                                            data-city="{{ $Customer->city }}" data-area="{{ $Customer->area }}">
                                            {{ $Customer->customer_name }}{{ $Customer->shop_name ? ' ('.$Customer->shop_name.')' : '' }}{{ $Customer->area ? ' ('.$Customer->area.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Contact</label>
                                <input type="text" id="contact" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold">City</label>
                                <input type="text" id="city" class="form-control bg-light" readonly>
                            </div>
                            <div class="col-md-2">
                                <label class="fw-bold">Area</label>
                                <input type="text" id="area" class="form-control bg-light" readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control bg-light" value="{{ date('Y-m-01') }}">
                            </div>

                            <div class="col-md-6">
                                <label class="fw-bold">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control bg-light" value="{{ date('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-4 mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">
                                Search
                            </button>

                            <button id="downloadPdf" class="btn btn-danger btn-lg d-none">
                                Download PDF
                            </button>
                        </div>
                    </form>
                    <div id="ledgerResult" style="display: none;">
                        <div class="ledger-container mt-4">
                            <div class="ledger-header">CUSTOMER LEDGER</div>
                            <div class="ledger-info">
                                <span><strong>Customer:</strong> <span id="CustomerName"></span></span>
                                <span><strong>Duration:</strong> From <span id="startDate"></span> To <span
                                        id="endDate"></span></span>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>INV-No</th>
                                        <th>Description</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                    </tr>
                                    <tr>
                                        <td colspan="5" class="opening-balance">Opening Balance:</td>
                                        <td id="openingBalance">Rs. 0</td>
                                    </tr>
                                </thead>
                                <tbody id="ledgerData"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3"><strong>Totals:</strong></td>
                                        <td id="totalDebit">0</td>
                                        <td id="totalCredit">0</td>
                                        <td id="closingBalance">0</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<style>
    .ledger-container {
        border: 2px solid black;
        padding: 10px;
        max-width: 900px;
        margin: 20px auto;
        background: #fff;
    }

    .ledger-header {
        text-align: center;
        font-size: 20px;
        font-weight: bold;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    .ledger-info {
        display: flex;
        justify-content: space-between;
        padding: 10px;
        border-bottom: 2px solid black;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        border: 1px solid black;
        padding: 8px;
        text-align: center;
    }

    thead th {
        background: #f2f2f2;
    }

    .opening-balance {
        text-align: right;
        font-weight: bold;
        padding: 8px;
        border: 1px solid black;
    }
</style>

<script>
    function formatDate(dateString) {
        if (!dateString) return "N/A";
        const date = new Date(dateString);
        if (isNaN(date.getTime())) return "Invalid Date";
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    $(document).ready(function() {
        // Customer Select Change
        $('#Customer').change(function() {
            var selected = $(this).find(':selected');
            $('#contact').val(selected.data('contact') || '');
            $('#city').val(selected.data('city') || '');
            $('#area').val(selected.data('area') || '');
        });

        // Search Ledger
        $('#searchLedger').click(function() {
            var CustomerId = $('#Customer').val();
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();

            if (!CustomerId) {
                alert('Please select a Customer.');
                return;
            }

            // Hide previous results
            $('#ledgerResult').hide();
            $('#downloadPdf').addClass('d-none');

            $.ajax({
                url: "{{ route('fetch-Customer-ledger') }}",
                type: "GET",
                data: {
                    Customer_id: CustomerId,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function(response) {
                    // Update Header Info
                    $('#CustomerName').text($('#Customer option:selected').text());
                    $('#startDate').text(formatDate(response.startDate));
                    $('#endDate').text(formatDate(response.endDate));

                    let openingBalance = parseFloat(response.opening_balance) || 0;
                    let balance = openingBalance;
                    let totalDebit = 0;
                    let totalCredit = 0;
                    let allEntries = [];

                    // Collect Entries
                    // 1. Sales & Advances
                    if (Array.isArray(response.local_sales)) {
                        response.local_sales.forEach(entry => {
                            allEntries.push({
                                date: entry.created_at,
                                type: 'sale',
                                invoice_number: entry.invoice_number,
                                amount: parseFloat(entry.net_amount) || 0
                            });

                            let advance = parseFloat(entry.advance_amount) || 0;
                            if (advance > 0) {
                                allEntries.push({
                                    date: entry.created_at,
                                    type: 'advance',
                                    invoice_number: entry.invoice_number,
                                    amount: advance
                                });
                            }
                        });
                    }

                    // 2. Recoveries
                    if (Array.isArray(response.recoveries)) {
                        response.recoveries.forEach(entry => {
                            allEntries.push({
                                date: entry.date,
                                type: 'recovery',
                                salesman: entry.salesman,
                                remarks: entry.remarks,
                                amount: parseFloat(entry.amount_paid) || 0
                            });
                        });
                    }

                    // 2.1 Journal Receipts (Vendor pays us)
                    if (Array.isArray(response.receipts)) {
                        response.receipts.forEach(entry => {
                            allEntries.push({
                                date: entry.voucher_date,
                                type: 'journal_receipt',
                                remarks: entry.remarks || 'Customer Receipt Voucher',
                                amount: parseFloat(entry.credit_amount) || 0
                            });
                        });
                    }

                    // 3. Returns
                    if (Array.isArray(response.sale_returns)) {
                        response.sale_returns.forEach(entry => {
                            allEntries.push({
                                date: entry.created_at,
                                type: 'sale_return',
                                reason: entry.reason || 'Sale Return',
                                amount: parseFloat(entry.return_amount) || 0
                            });
                        });
                    }

                    // Sort Entries
                    allEntries.sort((a, b) => {
                        let dateA = new Date(a.date);
                        let dateB = new Date(b.date);
                        if (dateA - dateB === 0) {
                            const typeOrder = { 'sale': 1, 'advance': 2, 'sale_return': 3, 'recovery': 4 };
                            return (typeOrder[a.type] || 5) - (typeOrder[b.type] || 5);
                        }
                        return dateA - dateB;
                    });

                    // Build HTML
                    let ledgerHTML = `
                        <tr>
                            <td>${formatDate(response.startDate)}</td>
                            <td>-</td>
                            <td class="fw-bold">Opening Balance</td>
                            <td>-</td>
                            <td>-</td>
                            <td class="fw-bold text-primary">Rs. ${openingBalance.toFixed(2)}</td>
                        </tr>
                    `;

                    allEntries.forEach(entry => {
                        let rowHtml = '';
                        if (entry.type === 'sale') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit;
                            rowHtml = `
                                <tr>
                                    <td>${formatDate(entry.date)}</td>
                                    <td>${entry.invoice_number || '-'}</td>
                                    <td>To Sale A/c</td>
                                    <td>Rs. ${debit.toFixed(2)}</td>
                                    <td>-</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                        } else if (entry.type === 'recovery') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            rowHtml = `
                                <tr>
                                    <td>${formatDate(entry.date)}</td>
                                    <td>-</td>
                                    <td>${entry.remarks || 'Recovery'}</td>
                                    <td>-</td>
                                    <td>Rs. ${credit.toFixed(2)}</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                        } else if (entry.type === 'journal_receipt') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            rowHtml = `
                                <tr style="background:#e3f2fd;">
                                    <td>${formatDate(entry.date)}</td>
                                    <td>-</td>
                                    <td class="text-success fw-bold">Receipt Voucher<br><small class="text-muted">${entry.remarks || ''}</small></td>
                                    <td>-</td>
                                    <td class="text-success fw-bold">Rs. ${credit.toFixed(2)}</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                        } else if (entry.type === 'sale_return') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            rowHtml = `
                                <tr>
                                    <td>${formatDate(entry.date)}</td>
                                    <td>-</td>
                                    <td class="text-danger fw-bold">Sale Return: ${entry.reason || 'N/A'}</td>
                                    <td>-</td>
                                    <td class="text-danger fw-bold">Rs. ${credit.toFixed(2)}</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                        } else if (entry.type === 'advance') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            rowHtml = `
                                <tr>
                                    <td>${formatDate(entry.date)}</td>
                                    <td>${entry.invoice_number || '-'}</td>
                                    <td class="text-success fw-bold">Advance Payment (Cash)</td>
                                    <td>-</td>
                                    <td class="text-success fw-bold">Rs. ${credit.toFixed(2)}</td>
                                    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">Rs. ${balance.toFixed(2)}</td>
                                </tr>`;
                        }
                        ledgerHTML += rowHtml;
                    });

                    // Render
                    $('#ledgerData').html(ledgerHTML);
                    $('#openingBalance').text(`Rs. ${openingBalance.toFixed(2)}`);
                    $('#totalDebit').text(`Rs. ${totalDebit.toFixed(2)}`);
                    $('#totalCredit').text(`Rs. ${totalCredit.toFixed(2)}`);
                    $('#closingBalance').text(`Rs. ${parseFloat(response.closing_balance).toFixed(2)}`);

                    $('#ledgerResult').show();
                    $('#downloadPdf').removeClass('d-none');
                },
                error: function(xhr) {
                    console.error(xhr);
                    let errorMsg = 'Error fetching ledger data.';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                        if (xhr.responseJSON.line) {
                            errorMsg += '\nLine: ' + xhr.responseJSON.line;
                        }
                        if (xhr.responseJSON.file) {
                            errorMsg += '\nFile: ' + xhr.responseJSON.file;
                        }
                    } else if (xhr.responseText) {
                        errorMsg += '\n' + xhr.responseText.substring(0, 200);
                    }
                    
                    alert(errorMsg);
                }
            });
        });

        // PDF Download
        $('#downloadPdf').click(function(e) {
            e.preventDefault();
            const element = document.querySelector(".ledger-container");
            html2canvas(element).then(canvas => {
                const imgData = canvas.toDataURL("image/png");
                const pdf = new jspdf.jsPDF("p", "mm", "a4");
                const imgProps = pdf.getImageProperties(imgData);
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;
                pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
                pdf.save("Customer-Ledger.pdf");
            });
        });
    });
</script>