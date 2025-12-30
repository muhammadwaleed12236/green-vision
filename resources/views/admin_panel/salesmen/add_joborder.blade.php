@include('admin_panel.include.header_include')

<style>
    .work-type-card {
        border-left: 4px solid #0d6efd;
        background: #ffffff;
    }

    .work-type-card.contract {
        border-left-color: #fd7e14;
        background: #fffdf8;
    }

    .work-type-card.labour {
        border-left-color: #198754;
        background: #f8fffb;
    }

    .item-row {
        background: #f8f9fa;
        border-radius: 6px;
        padding: 8px;
        margin-bottom: 6px;
    }

    .summary-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
    }

    /* Sticky footer bar */
    .job-footer-bar {
        position: sticky;
        bottom: 0;
        background: #ffffff;
        border-top: 1px solid #dee2e6;
        padding: 10px 15px;
        z-index: 10;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <!-- ================= PAGE HEADER ================= -->
            <div class="page-header">
                <div class="page-title">
                    <h4>Job Orders</h4>
                    <h6>Prepare Job by Work Type</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJobModal">
                        + New Job
                    </button>
                </div>
            </div>

            <!-- ================= JOB LIST ================= -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Job No</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Paid</th>
                                <th>Remaining</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jobOrders as $k => $job)
                                <tr>
                                    <td>{{ $k + 1 }}</td>
                                    <td class="fw-semibold">{{ $job->job_order_no }}</td>
                                    <td>{{ $job->job_date }}</td>
                                    <td>{{ number_format($job->total_amount) }}</td>
                                    <td class="text-success">{{ number_format($job->paid_amount) }}</td>
                                    <td class="text-danger">{{ number_format($job->remaining_amount) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ================= ADD JOB MODAL ================= -->
<div class="modal fade" id="addJobModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Prepare Job (Work Type Based)</h5>
                <button class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- ================= JOB INFO ================= -->
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="fw-semibold">Customer Order</label>
                                <select class="form-select" id="jobSelect">
                                    <option value="">Select Order</option>
                                    @foreach($localSales as $sale)
                                        <option value="{{ $sale->id }}">
                                            {{ $sale->invoice_number }} - {{ $sale->customer->shop_name ?? '' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold">Job Date</label>
                                <input type="date" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="fw-semibold">Job Note</label>
                                <input type="text" class="form-control" placeholder="Optional">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ================= TOP ADD WORK TYPE ================= -->
                <div class="text-end mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addWorkType">
                        + Add Work Type
                    </button>
                </div>

                <!-- ================= WORK TYPE CONTAINER ================= -->
                <div id="workTypeContainer"></div>

                <!-- ================= SUMMARY ================= -->
                <div class="card summary-box mt-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label>Total Job Cost</label>
                                <input id="jobTotal" class="form-control fw-bold text-end" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Paid</label>
                                <input id="jobPaid" class="form-control text-end" value="0">
                            </div>
                            <div class="col-md-4">
                                <label>Remaining</label>
                                <input id="jobRemaining" class="form-control fw-bold text-end" readonly>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ================= FOOTER BAR ================= -->
            <div class="job-footer-bar d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-primary" id="addWorkTypeFooter">
                    + Add Work Type
                </button>
                <button class="btn btn-primary px-4">Save Job</button>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<!-- ================= SCRIPT ================= -->
<script>
    $(document).ready(function () {

        function addWorkType() {
            let html = `
<div class="card shadow-sm mb-3 work-type-card">
<div class="card-header d-flex justify-content-between align-items-center py-2">
<span class="fw-semibold">Work Type</span>
<button type="button" class="btn btn-sm btn-outline-danger removeWorkType">✕</button>
</div>

<div class="card-body">

<div class="row g-3 mb-2">
<div class="col-md-4">
<label class="small">Work Type</label>
<input type="text" class="form-control form-control-sm work-type-name" placeholder="Glass / Aluminium">
</div>

<div class="col-md-4">
<label class="small">Assign To</label>
<select class="form-select form-select-sm assignType">
<option value="">Select</option>
<option value="labour">In-House Labour</option>
<option value="contract">Contractor</option>
</select>
</div>

<div class="col-md-4 contractorBox d-none">
<label class="small">Contractor</label>
<select class="form-select form-select-sm">
<option>Ali Contractor</option>
<option>Rehman Aluminium</option>
</select>
</div>
</div>

<div class="itemsContainer"></div>

<button type="button" class="btn btn-sm btn-outline-secondary addItem">
+ Add Item
</button>

</div>
</div>`;
            $("#workTypeContainer").append(html);
        }

        /* Top + Footer button */
        $("#addWorkType, #addWorkTypeFooter").click(addWorkType);

        /* Remove Work Type */
        $(document).on("click", ".removeWorkType", function () {
            $(this).closest(".work-type-card").remove();
            recalc();
        });

        /* Assign Type */
        $(document).on("change", ".assignType", function () {
            let card = $(this).closest(".work-type-card");
            card.removeClass("labour contract");
            if (this.value === "labour") {
                card.addClass("labour");
                card.find(".contractorBox").addClass("d-none");
            }
            if (this.value === "contract") {
                card.addClass("contract");
                card.find(".contractorBox").removeClass("d-none");
            }
        });

        /* Add Item */
        $(document).on("click", ".addItem", function () {
            let html = `
<div class="item-row row g-2 align-items-center">
<div class="col-md-4">
<input class="form-control form-control-sm item-name" placeholder="Item">
</div>
<div class="col-md-2">
<input class="form-control form-control-sm text-center item-qty" placeholder="Qty">
</div>
<div class="col-md-2">
<input class="form-control form-control-sm text-end item-rate" value="0">
</div>
<div class="col-md-2">
<input class="form-control form-control-sm text-end bg-light item-total" readonly>
</div>
<div class="col-md-2 text-end">
<button type="button" class="btn btn-sm btn-outline-danger removeItem">✕</button>
</div>
</div>`;
            $(this).siblings(".itemsContainer").append(html);
        });

        /* Remove Item */
        $(document).on("click", ".removeItem", function () {
            $(this).closest(".item-row").remove();
            recalc();
        });

        /* Calculation */
        function recalc() {
            let total = 0;
            $(".item-row").each(function () {
                let qty = +$(this).find(".item-qty").val() || 0;
                let rate = +$(this).find(".item-rate").val() || 0;
                let t = qty * rate;
                $(this).find(".item-total").val(t);
                total += t;
            });
            $("#jobTotal").val(total);
            $("#jobRemaining").val(total - (+$("#jobPaid").val() || 0));
        }

        $(document).on("keyup change", ".item-qty,.item-rate,#jobPaid", recalc);

    });
</script>