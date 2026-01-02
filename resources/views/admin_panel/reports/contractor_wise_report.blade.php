@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000 !important;
            padding: 6px;
            text-align: center;
        }

        table.report-table th {
            background-color: #f2f2f2;
        }

        table.report-table tfoot td {
            font-weight: bold;
            background-color: #e9e9e9;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">CONTRACTOR JOB REPORT</h3>

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
                            <div class="col-md-4">
                                <label for="start_date" class="form-label">Start Date</label>
                                <input type="date" id="start_date" name="start_date" class="form-control">
                            </div>
                            <div class="col-md-4">
                                <label for="end_date" class="form-label">End Date</label>
                                <input type="date" id="end_date" name="end_date" class="form-control">
                            </div>
                        </div>
                        <div class="d-flex justify-content-between align-items-center gap-4 mt-4">
                            <button type="button" id="searchLedger" class="btn btn-primary btn-lg px-5">Search</button>

                            <button id="downloadPdf" class="btn btn-danger btn-lg">Download PDF</button>
                        </div>
                    </form>

                    <div class="table-responsive mt-4 Contractor_report" id="report-preview">
                        <h4 class="text-center fw-bold">CONTRACTOR JOB REPORT HYDERABAD</h4>
                        <div class="date-range-text text-center mb-2"></div>
                        <div class="report-party-name fw-bold text-secondary mb-2"></div>

                        <table class="report-table">
                            <thead>
                                <tr>
                                    <th>Job No</th>
                                    <th>Date</th>
                                    <th>Work Type</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                            <tfoot></tfoot>
                        </table>
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
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const d = new Date(dateStr);
        return `${String(d.getDate()).padStart(2, '0')}-${months[d.getMonth()]}-${String(d.getFullYear()).slice(-2)}`;
    }

    $('#searchLedger').click(function () {
        const start = $('#start_date').val();
        const end = $('#end_date').val();
        const contractorId = $('#Contractor').val();

        if (!start || !end || !contractorId) {
            alert('Please select Contractor, Start Date, and End Date.');
            return;
        }

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
                console.log(response); // Debug

                let rows = '';

                // ✅ Correct way to access report data
                response.report.forEach(row => {
                    rows += `
                    <tr>
                        <td>${row.job_no}</td>
                        <td>${row.date}</td>
                        <td>${row.work_type}</td>
                        <td>${Number(row.total_amount).toLocaleString()}</td>
                        <td class="text-success">${Number(row.paid_amount).toLocaleString()}</td>
                        <td class="text-danger">${Number(row.remaining_amount).toLocaleString()}</td>
                        <td><span class="badge bg-${row.status === 'Completed' ? 'success' : 'warning'}">${row.status}</span></td>
                    </tr>`;
                });

                // Set Contractor Name
                $('.report-party-name').html(`<p>${$('#Contractor option:selected').text()}</p>`);

                // Set Date Range
                const dateRangeHTML = `
                <p>From <strong>${formatDate(start)}</strong> To <strong>${formatDate(end)}</strong></p>
            `;
                $('.date-range-text').html(dateRangeHTML);

                // Set Table
                $('#report-preview tbody').html(rows);
                $('#report-preview tfoot').html(`
                <tr>
                    <td colspan="3">Total</td>
                    <td>${Number(response.totals.total_amount).toLocaleString()}</td>
                    <td>${Number(response.totals.paid_amount).toLocaleString()}</td>
                    <td>${Number(response.totals.remaining_amount).toLocaleString()}</td>
                    <td></td>
                </tr>
            `);
            },
            error: function (xhr) {
                alert('Error loading report');
                console.error(xhr);
            }
        });
    });

    document.getElementById("downloadPdf").addEventListener("click", function () {
        const element = document.querySelector(".Contractor_report");
        const opt = {
            margin: 0.3,
            filename: 'Contractor-Job-Report.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'landscape' // ✅ Landscape for more columns
            }
        };
        html2pdf().set(opt).from(element).save();
    });
</script>
