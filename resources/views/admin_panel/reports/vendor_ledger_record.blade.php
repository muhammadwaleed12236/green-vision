@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">Vendor Ledger</h3>

                    <form id="ledgerSearchForm">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="Vendor">Select Vendor</label>
                                <select id="Vendor" class="form-control">
                                    <option value="">-- Select Vendor --</option>
                                    @foreach($Vendors as $Vendor)
                                        <option value="{{ $Vendor->id }}" data-contact="{{ $Vendor->Party_phone }}"
                                            data-city="{{ $Vendor->City }}" data-area="{{ $Vendor->Area }}">
                                            {{ $Vendor->Party_name }}
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

                            <button type="button" id="downloadPdf" class="btn btn-danger btn-lg">
                                Download PDF
                            </button>
                        </div>


                    </form>

                    <div id="ledgerResult" style="display: none;">
                        <div class="ledger-container mt-4">
                            <div class="ledger-header">VENDOR LEDGER</div>
                            <div class="ledger-info">
                                <span><strong>Vendor:</strong> <span id="vendorName"></span></span>
                                <span><strong>Duration:</strong> From <span id="startDate"></span> To <span
                                        id="endDate"></span></span>
                            </div>
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>INV-No</th>
                                        <th>Description</th>
                                        <th>Items</th>
                                        <th>Bill & Receipts (Add)</th>
                                        <th>Paid & Sales (Less)</th>
                                        <th>Balance</th>
                                    </tr>
                                    <tr>
                                        <td colspan="6" class="text-end fw-bold">Opening Balance:</td>
                                        <td id="openingBalance" class="fw-bold">PKR 0</td>
                                    </tr>
                                </thead>
                                <tbody id="ledgerData"></tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Totals:</td>
                                        <td id="totalDebit" class="fw-bold">0</td>
                                        <td id="totalCredit" class="fw-bold">0</td>
                                        <td id="closingBalance" class="fw-bold">0</td>
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

<!-- Items Modal -->
<div class="modal fade" id="itemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemsModalLabel">Items</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="itemsContent">
                <!-- Items will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    function formatDate(dateString) {
        const date = new Date(dateString);
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0'); // Months are zero-based
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }
    $(document).ready(function () {
        $('#Vendor').change(function () {
            var selected = $(this).find(':selected');
            $('#contact').val(selected.data('contact'));
            $('#city').val(selected.data('city'));
            $('#area').val(selected.data('area'));
        });

        $('#searchLedger').click(function () {
            var vendorID = $('#Vendor').val();
            var vendorName = $('#Vendor option:selected').text();
            let startDate = $('#start_date').val();
            let endDate = $('#end_date').val();
            if (!vendorID) {
                alert('Please select a Vendor.');
                return;
            }

            $.ajax({
                url: "{{ route('fetch-vendor-ledger') }}",
                type: "GET",
                data: {
                    Vendor_id: vendorID,
                    start_date: startDate,
                    end_date: endDate
                },
                success: function (response) {

                    const startDateObj = new Date(response.startDate);
                    const endDateObj = new Date(response.endDate);
                    // Format dates to 'dd/mm/yyyy'
                    const formattedStartDate = formatDate(response.startDate);
                    const formattedEndDate = formatDate(response.endDate);

                    $('#ledgerResult').show();
                    $('#vendorName').text(vendorName);
                    $('#startDate').text(formattedStartDate || "N/A");
                    $('#endDate').text(formattedEndDate || "N/A");


                    let openingBalance = parseFloat(response.opening_balance);
                    let balance = openingBalance;
                    let totalDebit = 0,
                        totalCredit = 0;
                    let ledgerHTML = "";

                    let allEntries = [];

                    // ✅ Opening Balance Entry
                    ledgerHTML += `
                <tr>
                    <td>${response.start_date}</td>
                    <td>-</td>
                    <td class="fw-bold">Opening Balance</td>
                    <td>-</td>
                    <td>-</td>
                    <td class="fw-bold text-primary">PKR ${balance.toFixed(2)}</td>
                </tr>
            `;

                    // ✅ Sales Entries
                    response.purchases.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'purchase',
                            invoice_number: entry.invoice_number,
                            amount: parseFloat(entry.net_amount),
                            items: entry.items || null
                        });
                    });

                    // ✅ Payment Entries (Journal Vouchers + Vendor Payments)
                    response.recoveries.forEach(entry => {
                        allEntries.push({
                            date: entry.payment_date,
                            type: 'payment',
                            salesman: entry.remarks,
                            amount: parseFloat(entry.amount) || 0
                        });
                    });

                    // ✅ Receipt Entries (From Journal Vouchers when Vendor pays us)
                    if (response.receipts) {
                        response.receipts.forEach(entry => {
                            allEntries.push({
                                date: entry.receipt_date,
                                type: 'receipt',
                                remarks: entry.remarks || 'Receipt Voucher',
                                amount: parseFloat(entry.amount) || 0
                            });
                        });
                    }


                    // ✅ Recovery Entries
                    response.returns.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'return',
                            invoice_number: entry.invoice_number,
                            amount: parseFloat(entry.net_amount)
                        });
                    });

                    // ✅ Builty Entries
                    response.builties.forEach(entry => {
                        allEntries.push({
                            date: entry.date,
                            type: 'builty',
                            description: entry.description,
                            amount: parseFloat(entry.amount)
                        });
                    });

                    // ✅ Job Orders assigned to Vendor (Debit = total_amount assigned, Credit = paid_amount)
                    if (response.job_orders) {
                        response.job_orders.forEach(entry => {
                            // Job Assign Entry (Debit - we owe vendor)
                            if (parseFloat(entry.total_amount) > 0) {
                                allEntries.push({
                                    date: entry.order_date,
                                    type: 'job_assigned',
                                    job_number: entry.job_order_number,
                                    description: entry.description || 'Job Assigned to Vendor',
                                    status: entry.assignment_status,
                                    amount: parseFloat(entry.total_amount)
                                });
                            }
                            // Job Payment Entry (Credit - we paid vendor)
                            if (parseFloat(entry.paid_amount) > 0) {
                                allEntries.push({
                                    date: entry.order_date,
                                    type: 'job_payment',
                                    job_number: entry.job_order_number,
                                    description: 'Job Payment - ' + (entry.description || entry.job_order_number),
                                    amount: parseFloat(entry.paid_amount)
                                });
                            }
                        });
                    }

                    // ✅ Local Sales Entries
                    if (response.local_sales) {
                        response.local_sales.forEach(entry => {
                            allEntries.push({
                                date: entry.Date,
                                type: 'sale',
                                invoice_number: entry.invoice_number,
                                amount: parseFloat(entry.net_amount)
                            });

                            if (parseFloat(entry.advance_amount) > 0) {
                                allEntries.push({
                                    date: entry.Date,
                                    type: 'sale_advance',
                                    invoice_number: entry.invoice_number,
                                    amount: parseFloat(entry.advance_amount)
                                });
                            }
                        });
                    }

                    // ✅ Sort Entries by Date (Sales pehle, Recovery baad me agar date same ho)
                    allEntries.sort((a, b) => {
                        let dateA = new Date(a.date);
                        let dateB = new Date(b.date);
                        if (dateA - dateB === 0) {
                            return a.type === 'sale' ? -1 : 1; // Sale pehle ayegi, Recovery baad me
                        }
                        return dateA - dateB;
                    });

                    // ✅ Maintain Correct Ledger Balance
                    allEntries.forEach(entry => {
                        if (entry.type === 'purchase') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit;
                            let itemsHTML = entry.items ? '<button class="btn btn-sm btn-info show-items-btn" data-inv="' + entry.invoice_number + '" data-type="purchase" style="cursor:pointer;">View Items</button>' : '-';
                            ledgerHTML += `
        <tr>
            <td>${formatDate(entry.date)}</td>
            <td>${entry.invoice_number}</td>
            <td>To Purchase A/c</td>
            <td>${itemsHTML}</td>
            <td>PKR ${debit.toFixed(2)}</td>
            <td>-</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        } else if (entry.type === 'payment') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            ledgerHTML += `
<tr>
    <td>${formatDate(entry.date)}</td>
    <td>-</td>
    <td>Vendor Payment<br><small class="text-muted">${entry.salesman || ''}</small></td>
    <td>-</td>
    <td>-</td>
    <td>PKR ${credit.toFixed(2)}</td>
    <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
</tr>
`;
                        } else if (entry.type === 'receipt') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit; // Receipt means Vendor gives us money, decreases their Dr balance (adds to it)
                            ledgerHTML += `
        <tr style="background:#e3f2fd;">
            <td>${formatDate(entry.date)}</td>
            <td>-</td>
            <td>Vendor Receipt (Received from Vendor)<br><small class="text-muted">${entry.remarks || ''}</small></td>
            <td>-</td>
            <td class="text-success fw-bold">PKR ${debit.toFixed(2)}</td>
            <td>-</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        } else if (entry.type === 'builty') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit;
                            ledgerHTML += `
    <tr>
        <td>${formatDate(entry.date)}</td>
        <td>-</td>
        <td>${entry.description ?? 'Vendor Builty Entry'}</td>
        <td>-</td>
        <td>PKR ${debit.toFixed(2)}</td>
        <td>-</td>
        <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
    </tr>`;
                        } else if (entry.type === 'return') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            ledgerHTML += `
        <tr>
            <td>${formatDate(entry.date)}</td>
            <td>${entry.invoice_number}</td>
            <td>Purchase Return</td>
            <td>-</td>
            <td>-</td>
            <td>PKR ${credit.toFixed(2)}</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        } else if (entry.type === 'sale') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            ledgerHTML += `
        <tr>
            <td>${formatDate(entry.date)}</td>
            <td>${entry.invoice_number}</td>
            <td>Local Sale</td>
            <td><button class="btn btn-sm btn-info show-items-btn" data-inv="${entry.invoice_number}" data-type="sale" style="cursor:pointer;">View Items</button></td>
            <td>-</td>
            <td>PKR ${credit.toFixed(2)}</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        } else if (entry.type === 'sale_advance') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit; // We got paid by vendor, decreases their Dr balance (adds to it)
                            ledgerHTML += `
        <tr style="background:#e3f2fd;">
            <td>${formatDate(entry.date)}</td>
            <td>${entry.invoice_number}</td>
            <td>Advance Received via Local Sale</td>
            <td>-</td>
            <td>PKR ${debit.toFixed(2)}</td>
            <td>-</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        } else if (entry.type === 'job_assigned') {
                            let debit = entry.amount;
                            totalDebit += debit;
                            balance += debit;
                            let statusBadge = '';
                            if (entry.status === 'completed') statusBadge = '<span class="badge bg-success ms-1">Completed</span>';
                            else if (entry.status === 'in_progress') statusBadge = '<span class="badge bg-warning ms-1">In Progress</span>';
                            else statusBadge = '<span class="badge bg-secondary ms-1">Pending</span>';
                            ledgerHTML += `
        <tr style="background:#fff8e1;">
            <td>${formatDate(entry.date)}</td>
            <td>${entry.job_number || '-'}</td>
            <td>Job Assigned to Vendor ${statusBadge}<br><small class="text-muted">${entry.description || ''}</small></td>
            <td>-</td>
            <td>PKR ${debit.toFixed(2)}</td>
            <td>-</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        } else if (entry.type === 'job_payment') {
                            let credit = entry.amount;
                            totalCredit += credit;
                            balance -= credit;
                            ledgerHTML += `
        <tr style="background:#e8f5e9;">
            <td>${formatDate(entry.date)}</td>
            <td>${entry.job_number || '-'}</td>
            <td>Job Payment Paid<br><small class="text-muted">${entry.description || ''}</small></td>
            <td>-</td>
            <td>-</td>
            <td>PKR ${credit.toFixed(2)}</td>
            <td class="fw-bold ${balance < 0 ? 'text-danger' : 'text-success'}">PKR ${Math.abs(balance).toFixed(2)} ${balance > 0 ? 'Cr' : (balance < 0 ? 'Dr' : '')}</td>
        </tr>`;
                        }

                    });


                    // ✅ Update Totals
                    $('#ledgerData').html(ledgerHTML);
                    
                    let ob = parseFloat(openingBalance);
                    let cb = parseFloat(response.closing_balance);
                    
                    $('#openingBalance').text(`PKR ${Math.abs(ob).toFixed(2)} ${ob > 0 ? 'Cr' : (ob < 0 ? 'Dr' : '')}`);
                    $('#totalDebit').text(`PKR ${totalDebit.toFixed(2)}`);
                    $('#totalCredit').text(`PKR ${totalCredit.toFixed(2)}`);

                    // Closing balance directly from API response
                    $('#closingBalance').text(`PKR ${Math.abs(cb).toFixed(2)} ${cb > 0 ? 'Cr' : (cb < 0 ? 'Dr' : '')}`);

                    // Store data for items modal
                    window.allItemsData = {};
                    
                    // Store local sales items
                    response.local_sales.forEach(sale => {
                        window.allItemsData[sale.invoice_number] = { type: 'sale', items: JSON.parse(sale.item || '[]') };
                    });
                    
                    // Store purchase items
                    response.purchases.forEach(purchase => {
                        if (purchase.items) {
                            window.allItemsData[purchase.invoice_number] = { type: 'purchase', items: JSON.parse(purchase.items || '[]') };
                        }
                    });

                    // Add click handler for items button
                    $(document).on('click', '.show-items-btn', function() {
                        let invNum = $(this).data('inv');
                        let data = window.allItemsData[invNum];
                        if (data && data.items) {
                            let itemsArray = data.items;
                            
                            // Ensure it's an array
                            if (typeof itemsArray === 'string') {
                                try { itemsArray = JSON.parse(itemsArray); } catch (e) { itemsArray = [itemsArray]; }
                            }
                            if (!Array.isArray(itemsArray)) {
                                if (typeof itemsArray === 'object' && itemsArray !== null) {
                                    itemsArray = Object.values(itemsArray);
                                } else if (itemsArray) {
                                    itemsArray = [itemsArray];
                                } else {
                                    itemsArray = [];
                                }
                            }

                            let itemsHTML = '<ul class="list-group">';
                            if (itemsArray.length > 0) {
                                itemsArray.forEach(item => {
                                    let displayItem = typeof item === 'object' && item !== null ? JSON.stringify(item) : item;
                                    itemsHTML += '<li class="list-group-item"><strong>' + displayItem + '</strong></li>';
                                });
                            } else {
                                itemsHTML += '<li class="list-group-item"><strong>No items found</strong></li>';
                            }
                            itemsHTML += '</ul>';
                            $('#itemsModalLabel').text('Items for Invoice: ' + invNum);
                            $('#itemsContent').html(itemsHTML);
                            $('#itemsModal').modal('show');
                        }
                    });
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
    });
</script>
<script>
    document.getElementById("downloadPdf").addEventListener("click", function () {
        const element = document.querySelector(".ledger-container");

        html2canvas(element).then(canvas => {
            const imgData = canvas.toDataURL("image/png");
            const pdf = new jspdf.jsPDF("p", "mm", "a4");

            const imgProps = pdf.getImageProperties(imgData);
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            pdf.addImage(imgData, "PNG", 0, 0, pdfWidth, pdfHeight);
            pdf.save("Vendor_ledger .pdf");
        });
    });

    // Show PDF button only when result appears
    $('#searchLedger').click(function () {
        setTimeout(() => {
            $('#downloadPdf').removeClass('d-none');
        }, 500);
    });
</script>
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
