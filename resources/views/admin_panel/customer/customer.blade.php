@include('admin_panel.include.header_include')

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       ============================================ */

    /* Never allow accidental horizontal scrolling */
    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
    }

    /* Keep media flexible so nothing overflows */
    img, canvas, table {
        max-width: 100%;
    }

    /* Customer table never scrolls horizontally - cells wrap to fit */
    .cust-wrap {
        overflow-x: hidden;
    }
    .cust-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .cust-wrap .datanew td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .cust-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .cust-wrap .datanew th:nth-child(1) { width: 5% !important; }
    .cust-wrap .datanew th:nth-child(2) { width: 18% !important; }
    .cust-wrap .datanew th:nth-child(3) { width: 22% !important; }
    .cust-wrap .datanew th:nth-child(4) { width: 12% !important; }
    .cust-wrap .datanew th:nth-child(5) { width: 15% !important; }
    .cust-wrap .datanew th:nth-child(6) { width: 28% !important; }
    .cust-wrap .dataTables_wrapper {
        max-width: 100%;
    }

    /* Empty-state message: keep it a full-width centered table cell */
    .cust-wrap .datanew td.dataTables_empty {
        display: table-cell !important;
        width: 100% !important;
        text-align: center !important;
        padding: 28px !important;
        color: #94a3b8;
        font-weight: 500;
        border: 0 !important;
    }

    /* Actions cell: keep buttons wrapping inside the cell on desktop */
    @media (min-width: 768px) {
        .cust-wrap .datanew td:last-child:not(.dataTables_empty) {
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
            justify-content: flex-start;
        }
    }

    /* ---------- Phone & below (576px) ---------- */
    @media (max-width: 575.98px) {
        .page-header .page-btn {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        .page-header .page-btn .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .page-title h4 { font-size: 1.05rem; }
        .page-title h6 { font-size: 0.85rem; }
        .cust-wrap .dataTables_filter { margin-bottom: 8px; }
        .cust-wrap .dataTables_filter input { max-width: 130px; }
        .cust-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- Customer table -> stacked cards (phone & small tablet) ---------- */
    @media (max-width: 767.98px) {
        .table-responsive .datanew thead {
            display: none !important;
        }
        .table-responsive .datanew,
        .table-responsive .datanew tbody,
        .table-responsive .datanew tr,
        .table-responsive .datanew td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .table-responsive .datanew {
            border: 0 !important;
        }
        .table-responsive .datanew tbody {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
        }
        .table-responsive .datanew tbody tr {
            background: #fff;
            border: 1px solid #eef2f7 !important;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 4px 14px;
            margin: 0 !important;
        }
        .table-responsive .datanew tbody tr:hover {
            background: #fff;
        }
        .table-responsive .datanew td {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0 !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            background: transparent !important;
            font-size: 0.85rem !important;
            color: #1e293b;
            text-align: right;
            white-space: normal !important;
            word-break: break-word;
        }
        .table-responsive .datanew td:last-child {
            border-bottom: 0 !important;
        }
        .table-responsive .datanew td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .table-responsive .datanew td .btn {
            padding: 0.35rem 0.6rem;
        }
        .cust-wrap .dataTables_filter,
        .cust-wrap .dataTables_length,
        .cust-wrap .dataTables_info,
        .cust-wrap .dataTables_paginate {
            max-width: 100%;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Customer List</h4>
                    <h6>Manage Customers</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Customer
                    </button>
                </div>
            </div>

            @if (session()->has('success'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: '{{ session('success') }}'
                        });
                    });
                </script>
            @endif

            <div class="container">
                <div class="table-responsive cust-wrap">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Address</th>
                                <th>Phone</th>
                                <th>Opening Balance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($customers as $key => $customer)
                                <tr>
                                    <td data-label="#">#{{ $key + 1 }}</td>
                                    <td data-label="Name">{{ $customer->customer_name }}</td>
                                    <td data-label="Address">{{ $customer->address }}</td>
                                    <td data-label="Phone">{{ $customer->phone_number }}</td>
                                    <td data-label="Opening Balance">{{ $customer->opening_balance }}</td>
                                    <td data-label="Actions">
                                        @if(Auth::check() && Auth::user()->usertype == 'admin')
                                            <button class="btn btn-sm btn-primary editCustomerBtn"
                                                data-id="{{ $customer->id }}">Edit</button>
                                            <button class="btn btn-sm btn-danger deleteCustomerBtn"
                                                data-id="{{ $customer->id }}">Delete</button>
                                        @endif
                                        @if(Auth::check() && Auth::user()->usertype == 'distributor')
                                            <button class="btn btn-sm btn-primary editCustomerBtn"
                                                data-id="{{ $customer->id }}">Edit</button>
                                        @endif
                                        @if(Auth::check() && Auth::user()->usertype == 'salesman')
                                            <span class="btn btn-danger btn-sm">No Action Given</span>
                                        @endif
                                        <a href="{{ route('customer.transaction.history', $customer->id) }}"
                                           class="btn btn-sm btn-info text-white" title="Transaction History">
                                            <i class="fa fa-history"></i> History
                                        </a>
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

<!-- Add Customer Modal -->
<div class="modal fade" id="addCustomerModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('customer.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Customer</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Customer Name</label>
                        <input type="text" name="customer_name" class="form-control mt-2" value="{{ old('customer_name') }}" placeholder="Enter Name">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control mt-2" value="{{ old('address') }}" placeholder="Enter Address">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control mt-2" value="{{ old('phone_number') }}" placeholder="Enter Phone Number">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" name="opening_balance" class="form-control mt-2" value="{{ old('opening_balance', '0') }}" placeholder="Enter Opening Balance">
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Customer Modal -->
<div class="modal fade" id="editCustomerModal" tabindex="-1" aria-labelledby="editCustomerModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg"> <!-- Changed to modal-lg for larger size -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Customer</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('customers.update') }}" method="POST">
                @csrf
                <input type="hidden" name="customer_id" id="edit_customer_id">
                <div class="modal-body">
                    <div class="row mb-3">

                        <div class="col-md-6">
                            <label class="form-label">Customer Name</label>
                            <input type="text" class="form-control" name="customer_name" id="edit_customer_name" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" id="edit_address">
                        </div>

                        <div class="col-md-6 mt-2">
                            <label class="form-label">Phone Number</label>
                            <input type="text" name="phone_number" class="form-control" id="edit_phone_number" placeholder="Enter Phone Number">
                        </div>

                        <div class="col-md-6 mt-2">
                           <label class="form-label">Opening Balance</label>
                            <input type="number" name="opening_balance" class="form-control" id="edit_opening_balance" value="0" placeholder="Enter Opening Balance">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Recape Type</label>
                            <select class="form-control" name="recape_type">
                                <option value="">Select Recape Type</option>
                                <option value="plus">Plus</option>
                                <option value="minus">Minus</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Recape Amount</label>
                            <input type="number" class="form-control" name="recape_opening" min="0">
                        </div>
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

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).on("click", ".editCustomerBtn", function () {
        let id = $(this).data("id");

        $.ajax({
            url: "{{ route('customer.edit', ':id') }}".replace(':id', id),
            type: "GET",
            success: function (response) {
                $("#edit_customer_id").val(response.id);
                $("#edit_customer_name").val(response.customer_name);
                $("#edit_phone_number").val(response.phone_number);
                $("#edit_address").val(response.address);
                $("#edit_shop_name").val(response.shop_name);
                if (response.ledger) {
                    $("#edit_opening_balance").val(response.ledger.opening_balance);
                } else {
                    $("#edit_opening_balance").val(0);
                }
                $("#edit_business_type").html('<option value="">Select Business Type</option>');

                $.each(response.business_types, function (index, type) {
                    let selected = (type.business_type_name === response.business_type_name) ? 'selected' : '';
                    $("#edit_business_type").append(`<option value="${type.business_type_name}" ${selected}>${type.business_type_name}</option>`);
                });

                $("#edit_citySelect").val(response.city).trigger("change");

                // Load Areas
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    type: "GET",
                    data: {
                        city_id: response.city
                    },
                    success: function (data) {
                        $("#edit_areasSelect").html('<option value="">Select Area</option>');
                        $.each(data, function (key, area) {
                            let selected = (area.area_name === response.area) ? 'selected' : '';
                            $("#edit_areasSelect").append(`<option value="${area.area_name}" ${selected}>${area.area_name}</option>`);
                        });
                    }
                });

                $("#editCustomerModal").modal("show");
            }
        });
    });


    $(document).on("click", ".deleteCustomerBtn", function (e) {
        e.preventDefault();
        let customerId = $(this).data("id");

        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('delete-customer', '') }}/" + customerId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire("Deleted!", response.message, "success");
                            location.reload(); // Refresh page
                        } else {
                            Swal.fire("Error!", response.message, "error");
                        }
                    },
                    error: function () {
                        Swal.fire("Error!", "Something went wrong.", "error");
                    }
                });
            }
        });
    });

    $(document).ready(function () {
        // Fetch Business Types
        $.get("{{ route('fetch-business-types') }}", function (data) {
            $('#businessTypeDropdown').html('<option value="">Select Business Type</option>');
            $.each(data, function (index, type) {
                $('#businessTypeDropdown').append('<option value="' + type.business_type_name + '">' + type.business_type_name + '</option>');
            });
        });

        // Delete Customer
        $('.deleteCustomerBtn').click(function () {
            let customerId = $(this).data('id');
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#3085d6",
                confirmButtonText: "Yes, delete it!"
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/customer/delete/" + customerId,
                        type: "DELETE",
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function (response) {
                            Swal.fire("Deleted!", response.success, "success").then(() => location.reload());
                        }
                    });
                }
            });
        });
    });
    $(document).ready(function () {
        // Add Product Modal: Fetch areas on Category Change
        $('#citySelect').change(function () {
            var cityId = $(this).val();
            $('#areasSelect').html('<option value="">Loading...</option>');

            if (cityId) {
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    type: "GET",
                    data: {
                        city_id: cityId
                    },
                    success: function (data) {
                        $('#areasSelect').html('<option value="">Select Area</option>');
                        $.each(data, function (key, area) {
                            $('#areasSelect').append('<option value="' + area.area_name + '">' + area.area_name + '</option>');
                        });
                    },
                    error: function () {
                        alert('Error fetching areas.');
                    }
                });
            } else {
                $('#areasSelect').html('<option value=""> Area Not Found...</option>');
            }
        });
    });
</script>
