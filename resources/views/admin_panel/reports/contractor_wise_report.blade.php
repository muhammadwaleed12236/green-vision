@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        .balance-card {
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            color: #fff;
        }
        .balance-card.opening { background: linear-gradient(135deg, #667eea, #764ba2); }
        .balance-card.closing { background: linear-gradient(135deg, #f093fb, #f5576c); }
        .balance-card.work { background: linear-gradient(135deg, #4facfe, #00f2fe); }
        .balance-card.paid { background: linear-gradient(135deg, #43e97b, #38f9d7); }
        .balance-card h6 { font-size: 12px; opacity: 0.9; margin-bottom: 5px; }
        .balance-card h4 { font-size: 20px; font-weight: bold; margin: 0; }

        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }
        table.report-table th,
        table.report-table td {
            border: 1px solid #000 !important;
            padding: 8px;
            text-align: center;
        }
        table.report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        table.report-table tfoot td {
            font-weight: bold;
            background-color: #e9e9e9;
        }
        .section-header {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
            padding: 10px 15px;
            border-radius: 8px;
            margin: 20px 0 10px 0;
            font-weight: bold;
        }
        .job-row { background-color: #fff3cd; }
        .payment-row { background-color: #d4edda; }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">
                        <i class="fas fa-user-tie me-2"></i>CONTRACTOR LEDGER REPORT
                    </h3>

                    <form id="contractorSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-bold" for="Contractor">Select Contractor</label>
                                <select id="Contractor" class="form-control">
                                    <option value="">-- Select Contractor --</option>
                                    @foreach($Contractors as $contractor)
                                        <option value="{{ $contractor->id }}">
                                            {{ $contractor->contractor_name ?? $contractor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control" value="{{ date('Y-m-01') }}">
                            </div>
                            <div class="col-md-3">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control" value="{{ date('Y-m-d') }}">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="button" id="searchLedger" class="btn btn-primary btn-lg w-100">
                                    <i class="fas fa-search me-1"></i> Search
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Balance Summary Cards -->
                    <div class="row mt-4" id="balance-cards" style="display: none;">
                        <div class="col-md-3">
                            <div class="balance-card opening">
                                <h6>OPENING BALANCE</h6>
                                <h4 id="opening-balance">PKR 0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="balance-card work">
                                <h6>TOTAL WORK</h6>
                                <h4 id="total-work">PKR 0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="balance-card paid">
                                <h6>TOTAL PAID</h6>
                                <h4 id="total-paid">PKR 0</h4>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="balance-card closing">
                                <h6>CLOSING BALANCE</h6>
                                <h4 id="closing-balance">PKR 0</h4>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-4 contractor_report" id="report-preview" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h4 class="text-center fw-bold mb-0">CONTRACTOR LEDGER REPORT</h4>
                                <div class="date-range-text text-center text-muted"></div>
                            </div>
                            <button id="downloadPdf" class="btn btn-danger">
                                <i class="fas fa-file-pdf me-1"></i> Download PDF
                            </button>
                        </div>
                        <div class="report-party-name fw-bold text-primary mb-2 fs-5"></div>

                        <!-- Job Orders Section -->
                        <div class="section-header">
                            <i class="fas fa-hammer me-2"></i>JOB ORDERS (Work Done)
                        </div>
                        <table class="report-table" id="jobs-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job No</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>

                        <!-- Payments Given Section -->
                        <div class="section-header" style="background: linear-gradient(135deg, #43e97b, #38f9d7);">
                            <i class="fas fa-hand-holding-usd me-2"></i>PAYMENTS GIVEN (To Contractor)
                        </div>
                        <table class="report-table" id="recoveries-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Amount Given</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
                    </div>

                    <div id="no-data" class="text-center py-5" style="display: none;">
                        <i class="fas fa-search fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted">No data found for the selected criteria</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    function formatDate(dateStr) {
        if (!dateStr) return 'N/A';
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const d = new Date(dateStr);
        return `${String(d.getDate()).padStart(2, '0')}-${months[d.getMonth()]}-${d.getFullYear()}`;
    }

    function formatCurrency(amount) {
        return 'PKR ' + Number(amount || 0).toLocaleString();
    }

    $('#searchLedger').click(function () {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        const contractorId = $('#Contractor').val();

        if (!contractorId) {
            Swal.fire('Error', 'Please select a Contractor', 'error');
            return;
        }

        // Show loading
        Swal.fire({
            title: 'Loading...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "{{ route('fetch.contractor.report') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                start_date: start,
                end_date: end,
                contractor_id: contractorId
            },
            success: function (response) {
                Swal.close();

                // Update Balance Cards
                $('#balance-cards').show();
                $('#opening-balance').text(formatCurrency(response.opening_balance));
                // Use ledger_closing_balance which is the actual database value
                $('#closing-balance').text(formatCurrency(response.ledger_closing_balance));
                $('#total-work').text(formatCurrency(response.totals.total_work));
                $('#total-paid').text(formatCurrency(response.totals.total_paid));


                // Set Party Name
                $('.report-party-name').html(`<i class="fas fa-user-tie me-2"></i>${$('#Contractor option:selected').text()}`);

                // Set Date Range
                $('.date-range-text').html(`From <strong>${formatDate(start)}</strong> To <strong>${formatDate(end)}</strong>`);

                // Job Orders Table
                let jobRows = '';
                let jobTotal = 0, jobPaid = 0, jobRemaining = 0;
                if (response.job_orders && response.job_orders.length > 0) {
                    response.job_orders.forEach((job, i) => {
                        jobTotal += parseFloat(job.total_amount || 0);
                        jobPaid += parseFloat(job.paid_amount || 0);
                        jobRemaining += parseFloat(job.remaining_amount || 0);
                        
                        let statusBadge = job.status === 'completed' 
                            ? '<span class="badge bg-success">Completed</span>'
                            : '<span class="badge bg-warning">' + (job.status || 'Pending') + '</span>';

                        jobRows += `
                            <tr class="job-row">
                                <td>${i + 1}</td>
                                <td>${job.job_order_number || 'N/A'}</td>
                                <td>${formatDate(job.order_date)}</td>
                                <td class="text-start">${job.description || '-'}</td>
                                <td>${formatCurrency(job.total_amount)}</td>
                                <td class="text-success">${formatCurrency(job.paid_amount)}</td>
                                <td class="text-danger">${formatCurrency(job.remaining_amount)}</td>
                                <td>${statusBadge}</td>
                            </tr>`;
                    });
                } else {
                    jobRows = '<tr><td colspan="8" class="text-muted">No job orders found</td></tr>';
                }
                $('#jobs-table tbody').html(jobRows);
                $('#jobs-table tfoot').html(`
                    <tr>
                        <td colspan="4"><strong>Total</strong></td>
                        <td>${formatCurrency(jobTotal)}</td>
                        <td>${formatCurrency(jobPaid)}</td>
                        <td>${formatCurrency(jobRemaining)}</td>
                        <td></td>
                    </tr>
                `);

                // Payments Given Table
                let recRows = '';
                let recTotal = 0;
                if (response.payments && response.payments.length > 0) {
                    response.payments.forEach((rec, i) => {
                        recTotal += parseFloat(rec.amount || 0);
                        recRows += `
                            <tr class="payment-row">
                                <td>${i + 1}</td>
                                <td>${formatDate(rec.voucher_date)}</td>
                                <td class="text-success fw-bold">${formatCurrency(rec.amount)}</td>
                                <td>${rec.narration || rec.payment_method || '-'}</td>
                            </tr>`;
                    });
                } else {
                    recRows = '<tr><td colspan="4" class="text-muted">No payments found</td></tr>';
                }
                $('#recoveries-table tbody').html(recRows);
                $('#recoveries-table tfoot').html(`
                    <tr>
                        <td colspan="2"><strong>Total Payments Given</strong></td>
                        <td colspan="2">${formatCurrency(recTotal)}</td>
                    </tr>

                `);

                // Show report
                $('#report-preview').show();
                $('#no-data').hide();

                if ((!response.job_orders || response.job_orders.length === 0) && 
                    (!response.payments || response.payments.length === 0)) {
                    $('#no-data').show();
                }
            },
            error: function (xhr) {
                Swal.close();
                console.error('Error:', xhr);
                let errorMsg = 'Error loading report';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
            }
        });
    });

    document.getElementById("downloadPdf").addEventListener("click", function () {
        const element = document.querySelector(".contractor_report");
        const opt = {
            margin: 0.3,
            filename: 'Contractor-Ledger-Report.pdf',
            image: { type: 'jpeg', quality: 1 },
            html2canvas: { scale: 2, useCORS: true },
            jsPDF: { unit: 'in', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    });

    // Auto-trigger search when contractor is selected
    $('#Contractor').on('change', function() {
        if ($(this).val()) {
            $('#searchLedger').click();
        }
    });

    // Auto-load first contractor on page load if exists
    $(document).ready(function() {
        @if($Contractors->count() == 1)
            $('#Contractor').val('{{ $Contractors->first()->id }}').trigger('change');
        @endif
    });
</script>

