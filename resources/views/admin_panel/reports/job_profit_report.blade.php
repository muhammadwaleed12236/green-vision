@include('admin_panel.include.header_include')

<style>
    table {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
        white-space: nowrap;
    }

    th {
        background: #f8f9fa;
        font-weight: bold;
    }

    tfoot td {
        font-weight: bold;
        background: #f1f1f1;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <div class="card shadow p-4">
                <div class="card-body">

                    <h3 class="text-center text-primary fw-bold mb-4">
                        General Job Profit Report
                    </h3>

                    {{-- FILTER --}}
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label>From Date</label>
                            <input type="date" id="fromDate" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label>To Date</label>
                            <input type="date" id="toDate" class="form-control">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="searchReport">
                                Search Report
                            </button>
                        </div>
                    </div>

                    {{-- SUMMARY --}}
                    <div class="row text-center mb-4">
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6>Total Jobs</h6>
                                    <h4 id="totalJobs">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6>Total Job Amount</h6>
                                    <h4 id="totalAmount">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6>Overall Expense</h6>
                                    <h4 id="overallExpense">0</h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card shadow-sm">
                                <div class="card-body">
                                    <h6>Net Profit</h6>
                                    <h4 id="netProfit">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- TABLE --}}
                    {{-- <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Job No</th>
                                    <th>Staff</th>
                                    <th>Job Amount</th>
                                    <th>Total Items</th>
                                    <th>Total Stock Out</th>
                                    <th>Job Profit</th>
                                </tr>
                            </thead>

                            <tbody id="jobTable"></tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end">Net Profit</td>
                                    <td id="footerNetProfit">0</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div> --}}

                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- SCRIPT --}}
<script>
    $('#searchReport').on('click', function () {

        let from = $('#fromDate').val();
        let to = $('#toDate').val();

        if (!from || !to) {
            alert('Please select both dates');
            return;
        }

        $.ajax({
            url: "{{ route('job.profit.report.fetch') }}",
            type: "GET",
            data: { from: from, to: to },
            success: function (res) {

                let html = '';

                res.jobs.forEach(job => {
                    html += `
    <tr>
        <td>${job.job}</td>
        <td>${job.staff}</td>
        <td>${job.job_amount}</td>
        <td>${job.total_items}</td>
        <td>${job.stock_cost}</td>
        <td class="${parseFloat(job.profit.replace(/,/g, '')) < 0 ? 'text-danger' : 'text-success'} fw-bold">
            ${job.profit}
        </td>
    </tr>`;
                });

                $('#jobTable').html(html);
                $('#totalJobs').text(res.totalJobs);
                $('#totalAmount').text(res.totalAmount);
                $('#overallExpense').text(res.overallExpense);
                $('#netProfit').text(res.netProfit);
                $('#footerNetProfit').text(res.netProfit);
            }
        });
    });
</script>
