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
            <div class="page-header">
                <div class="page-title">
                    <h4>Job Order List</h4>
                    <h6>Manage Job Orders</h6>
                </div>
                <div class="page-btn">
                    @if(Auth::user()->usertype === 'admin')
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addJobModal">
                            <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Job Order
                        </button>
                    @else
                        <button class="btn btn-sm btn-danger d-none" disabled>No Action</button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Job No</th>
                                    <th>Date</th>
                                    <th>Total Amount</th>
                                    <th>Paid Amount</th>
                                    <th>Remaining</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jobOrders as $key => $job)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $job->job_order_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($job->job_date)->format('d-m-Y') }}</td>
                                        <td>{{ number_format($job->total_amount) }}</td>
                                        <td class="text-success">{{ number_format($job->paid_amount) }}</td>
                                        <td class="text-danger">{{ number_format($job->remaining_amount) }}</td>
                                        <td>
                                            <span
                                                class="badge job-status-badge bg-{{ $job->status === 'completed' ? 'success' : 'warning' }}"
                                                data-id="{{ $job->id }}">
                                                {{ ucfirst($job->status) }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-1 flex-wrap">

                                                <!-- STATUS DROPDOWN -->
                                                <select class="form-select form-select-sm job-status"
                                                        style="width:110px"
                                                        data-id="{{ $job->id }}">
                                                    <option value="pending" {{ $job->status === 'pending' ? 'selected' : '' }}>
                                                        Pending
                                                    </option>
                                                    <option value="completed" {{ $job->status === 'completed' ? 'selected' : '' }}>
                                                        Completed
                                                    </option>
                                                </select>

                                                @if(Auth::user()->usertype === 'admin')
                                                    <a href="{{ route('job-orders.show', $job->id) }}"
                                                    class="btn btn-sm btn-info">
                                                        View
                                                    </a>

                                                    <button class="btn btn-sm btn-primary editJobBtn"
                                                        data-id="{{ $job->id }}"
                                                        data-job-no="{{ $job->job_order_no }}"
                                                        data-date="{{ $job->job_date }}"
                                                        data-total="{{ $job->total_amount }}"
                                                        data-paid="{{ $job->paid_amount }}"
                                                        data-status="{{ $job->status }}"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editJobModal">
                                                        Edit
                                                    </button>

                                                    <button class="btn btn-sm btn-danger deleteJobBtn"
                                                        data-id="{{ $job->id }}">
                                                        Delete
                                                    </button>
                                                @endif

                                            </div>
                                        </td>

                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Job Order Modal -->
<div class="modal fade" id="addJobModal" tabindex="-1" aria-labelledby="addJobModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Job Order</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>

            <div class="modal-body">
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
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

                            <div class="col-md-6">
                                <label class="fw-semibold">Job Date</label>
                                <input type="date" id="jobDate" value="{{ date('Y-m-d') }}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-end mb-2">
                    <button type="button" class="btn btn-outline-primary btn-sm" id="addWorkType">
                        + Add Work Type
                    </button>
                </div>

                <div id="workTypeContainer"></div>

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
                                <small id="paidError" class="text-danger d-none">
                                    Paid amount cannot exceed total job cost
                                </small>
                            </div>
                            <div class="col-md-4">
                                <label>Remaining</label>
                                <input id="jobRemaining" class="form-control fw-bold text-end" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="saveJobBtn">Save Job Order</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Job Order Modal -->
<div class="modal fade" id="editJobModal" tabindex="-1" aria-labelledby="editJobModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Job Order</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('job-orders.update') }}" method="POST">
                @csrf
                <input type="hidden" name="job_id" id="edit_job_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Job Order No</label>
                        <input type="text" class="form-control" id="edit_job_no" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Job Date</label>
                        <input type="date" class="form-control" name="job_date" id="edit_job_date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Total Amount</label>
                        <input type="number" class="form-control" name="total_amount" id="edit_total_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Paid Amount</label>
                        <input type="number" class="form-control" name="paid_amount" id="edit_paid_amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_status">
                            <option value="pending">Pending</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    $(document).ready(function () {

        let saleItems = [];

        // Load sale items
        $("#jobSelect").change(function () {
            let saleId = $(this).val();

            if (!saleId) {
                saleItems = [];
                return;
            }

            $(this).prop('disabled', true);

            $.ajax({
                url: `/job-orders/get-sale-details/${saleId}`,
                method: 'GET',
                success: function (response) {
                    if (response.status && response.items) {
                        saleItems = response.items;
                        console.log('Items loaded:', saleItems);
                    }
                },
                error: function (xhr) {
                    alert('Failed to load order items');
                    saleItems = [];
                },
                complete: function () {
                    $("#jobSelect").prop('disabled', false);
                }
            });
        });

        // Add work type card
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
                            <select Selected class="form-select form-select-sm assignType">
                                <option value="">Select</option>
                                <option value="labour">In-House Labour</option>
                                <option value="contract">Contractor</option>
                            </select>
                        </div>
                            <div class="col-md-4 contractorBox d-none">
                                <label class="small">Contractor</label>
                                <select class="form-select form-select-sm contractor-select">
                                    <option value="">Select Contractor</option>
                                    @foreach($contractors as $contractor)
                                        <option value="{{ $contractor->id }}">
                                            {{ $contractor->contractor_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                    </div>
                    <div class="itemsContainer"></div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary addItem">+ Add Item Manually</button>
                        <button type="button" class="btn btn-sm btn-outline-primary addFromSale ms-2">+ Add From Order</button>
                    </div>
                </div>
            </div>`;
            $("#workTypeContainer").append(html);
        }

        $("#addWorkType").click(addWorkType);

        $(document).on("click", ".removeWorkType", function () {
            $(this).closest(".work-type-card").remove();
            recalc();
        });

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

        $(document).on("click", ".addItem", function () {
            let html = `
                <div class="item-row row g-2 align-items-center" data-item-id="">
                    <div class="col-md-4">
                        <input class="form-control form-control-sm item-name" placeholder="Item Name">
                    </div>
                    <div class="col-md-2">
                        <input class="form-control form-control-sm text-center item-qty" placeholder="Qty" value="1">
                    </div>
                    <div class="col-md-2">
                        <input class="form-control form-control-sm text-end item-rate" placeholder="Rate" value="0">
                    </div>
                    <div class="col-md-2">
                        <input class="form-control form-control-sm text-end bg-light item-total" readonly>
                    </div>
                    <div class="col-md-2 text-end">
                        <button type="button" class="btn btn-sm btn-outline-danger removeItem">✕</button>
                    </div>
                </div>`;
            $(this).closest(".card-body").find(".itemsContainer").append(html);
            recalc();
        });

        $(document).on("click", ".addFromSale", function () {
            if (!saleItems || saleItems.length === 0) {
                alert('Please select a customer order first');
                return;
            }

            let container = $(this).closest(".card-body").find(".itemsContainer");
            let itemsHtml = '<div class="mb-3"><label class="small fw-semibold">Select items to add:</label>';

            saleItems.forEach((item, index) => {
                let displayText = item.item || 'Unknown Product';
                if (item.size) displayText += ` (${item.size})`;
                if (item.height && item.width) displayText += ` - ${item.height}x${item.width}`;

                itemsHtml += `
                <div class="form-check">
                    <input class="form-check-input sale-item-check" type="checkbox" value="${index}" id="saleItem${index}">
                    <label class="form-check-label" for="saleItem${index}">
                        ${displayText} - Qty: ${item.qty}
                    </label>
                </div>`;
            });

            itemsHtml += '</div><button type="button" class="btn btn-sm btn-primary confirmAddItems">Add Selected Items</button>';

            let tempDiv = $('<div class="border p-3 mb-3 bg-light"></div>').html(itemsHtml);
            container.before(tempDiv);

            tempDiv.find('.confirmAddItems').click(function () {
                tempDiv.find('.sale-item-check:checked').each(function () {
                    let index = parseInt($(this).val());
                    let item = saleItems[index];

                    let itemName = item.item || 'Unknown Product';
                    let itemQty = parseFloat(item.qty) || 0;

                    let itemHtml = `
                        <div class="item-row row g-2 align-items-center">
                            <div class="col-md-4">
                                <input class="form-control form-control-sm item-name" value="${itemName}">
                            </div>
                            <div class="col-md-2">
                                <input class="form-control form-control-sm text-center item-qty" value="${itemQty}">
                            </div>
                            <div class="col-md-2">
                                <input class="form-control form-control-sm text-end item-rate" placeholder="Rate" value="0">
                            </div>
                            <div class="col-md-2">
                                <input class="form-control form-control-sm text-end bg-light item-total" readonly>
                            </div>
                            <div class="col-md-2 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger removeItem">✕</button>
                            </div>
                        </div>`;
                    container.append(itemHtml);
                });

                tempDiv.remove();
                recalc();
            });
        });

        $(document).on("click", ".removeItem", function () {
            $(this).closest(".item-row").remove();
            recalc();
        });

        $(document).on("keyup change", ".item-qty,.item-rate,#jobPaid", recalc);

        // Save job order
        $("#saveJobBtn").click(function () {
            let saleId = $("#jobSelect").val();
            if (!saleId) {
                alert('Please select a customer order');
                return;
            }

            let workTypes = [];

            $(".work-type-card").each(function () {
                let card = $(this);
                let workTypeName = card.find(".work-type-name").val();
                let assignType = card.find(".assignType").val();
                let contractor = card.find(".contractor-select").val();

                if (!workTypeName || !assignType) {
                    return true;
                }

                let items = [];
                card.find(".item-row").each(function () {
                    let itemId = $(this).data('item-id') || null;
                    let itemName = $(this).find(".item-name").val();
                    let qty = parseFloat($(this).find(".item-qty").val()) || 0;
                    let rate = parseFloat($(this).find(".item-rate").val()) || 0;

                    if (itemName && qty > 0) {
                        items.push({
                            id: itemId,
                            name: itemName,
                            qty: qty,
                            rate: rate
                        });
                    }
                });

                if (items.length > 0) {
                    workTypes.push({
                        name: workTypeName,
                        assign_type: assignType,
                        contractor: contractor,
                        items: items
                    });
                }
            });

            if (workTypes.length === 0) {
                alert('Please add at least one work type with items');
                return;
            }

            let data = {
                sale_id: saleId,
                job_date: $("#jobDate").val(),
                job_note: $("#jobNote").val(),
                total_amount: parseFloat($("#jobTotal").val()) || 0,
                paid_amount: parseFloat($("#jobPaid").val()) || 0,
                work_types: workTypes,
                _token: "{{ csrf_token() }}"
            };

            $.ajax({
                url: '/job-orders/store',
                method: 'POST',
                data: data,
                success: function () {
                    window.location.reload();
                },
                error: function (xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMsg = Object.values(errors).map(e => e[0]).join('\n');
                        alert(errorMsg);
                    } else {
                        alert('Failed to save job order');
                    }
                }
            });
        });

        // Edit button click
        $(document).on("click", ".editJobBtn", function () {
            let id = $(this).data("id");
            let jobNo = $(this).data("job-no");
            let date = $(this).data("date");
            let total = $(this).data("total");
            let paid = $(this).data("paid");
            let status = $(this).data("status");

            $("#edit_job_id").val(id);
            $("#edit_job_no").val(jobNo);
            $("#edit_job_date").val(date);
            $("#edit_total_amount").val(total);
            $("#edit_paid_amount").val(paid);
            $("#edit_status").val(status);
        });

        // Delete button click
        $(document).on("click", ".deleteJobBtn", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            let deleteUrl = "/job-orders/delete/" + id;

            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: deleteUrl,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            if (response.status) {
                                Swal.fire(
                                    "Deleted!",
                                    "Job Order deleted successfully.",
                                    "success"
                                ).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire(
                                    "Error!",
                                    "Delete failed",
                                    "error"
                                );
                            }
                        },
                        error: function () {
                            Swal.fire(
                                "Error!",
                                "Something went wrong",
                                "error"
                            );
                        }
                    });
                }
            });
        });

        // Recalculate totals
        function recalc() {
            let total = 0;

            $(".item-row").each(function () {
                let qty = parseFloat($(this).find(".item-qty").val()) || 0;
                let rate = parseFloat($(this).find(".item-rate").val()) || 0;
                let itemTotal = qty * rate;

                $(this).find(".item-total").val(itemTotal.toFixed());
                total += itemTotal;
            });

            $("#jobTotal").val(total.toFixed());

            let paidInput = $("#jobPaid");
            let paid = parseFloat(paidInput.val()) || 0;
            let remaining = total - paid;

            if (paid > total) {
                $("#paidError").removeClass("d-none");
                $("#jobRemaining").val("0");
                paidInput.addClass("is-invalid");
                $("#saveJobBtn").prop("disabled", true);
            } else {
                $("#paidError").addClass("d-none");
                paidInput.removeClass("is-invalid");
                $("#jobRemaining").val(remaining.toFixed());
                $("#saveJobBtn").prop("disabled", false);
            }
        }

    });
</script>

<script>
$(document).on('change', '.job-status', function () {

    let jobId  = $(this).data('id');
    let status = $(this).val();
    let badge  = $('.job-status-badge[data-id="' + jobId + '"]');

    $.ajax({
        url: "{{ route('job-orders.toggle-status') }}",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            job_id: jobId,
            status: status
        },
        success: function (res) {
            if (res.success) {

                // 🔁 Badge text update
                badge.text(status.charAt(0).toUpperCase() + status.slice(1));

                // 🔁 Badge color update
                badge
                    .removeClass('bg-warning bg-success')
                    .addClass(status === 'completed' ? 'bg-success' : 'bg-warning');

                // ✨ Small visual feedback
                badge.addClass('px-2');
                setTimeout(() => badge.removeClass('px-2'), 500);
            }
        }
    });
});
</script>
