@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Vendor Builty</h4>
                    <h6>Manage Vendors Builty</h6>
                </div>
                <div class="page-btn">
                    @if(Auth::user()->usertype === 'admin')
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addVendorModal">
                            <img src="{{ asset('assets/img/icons/plus.svg') }}" class="me-1" alt="img">Add Vendor Builty
                        </button>
                    @else
                        <button class="btn btn-sm btn-danger" disabled>No Action</button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Vendor</th>
                                    <th>Date</th>
                                    <th>Month</th>
                                    <th>Amount</th>
                                    <th>Description</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($builtyRecords as $index => $builty)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $builty->vendor->Party_name ?? 'N/A' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($builty->date)->format('d-m-Y') }}</td>
                                        <td>{{ \Carbon\Carbon::parse($builty->month)->format('F Y') }}</td>
                                        <td>{{ number_format($builty->amount) }}</td>
                                        <td>{{ $builty->description }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editBuiltyBtn"
                                                data-id="{{ $builty->id }}"
                                                data-vendor="{{ $builty->vendor_id }}"
                                                data-date="{{ $builty->date }}"
                                                data-month="{{ \Carbon\Carbon::parse($builty->month)->format('Y-m') }}"
                                                data-amount="{{ $builty->amount }}"
                                                data-description="{{ $builty->description }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editVendorModal">Edit</button>

                                            <button class="btn btn-sm btn-danger deleteBuiltyBtn"
                                                data-id="{{ $builty->id }}">Delete</button>
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

<!-- Add Vendor Builty Modal -->
<div class="modal fade" id="addVendorModal" tabindex="-1" aria-labelledby="addVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Vendor Builty</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('store-vendors-builty') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Vendor</label>
                        <select name="vendor_id" id="vendor_id" class="form-control">
                            <option value="">-- Select Vendor --</option>
                            @foreach($Vendors as $vendor)
                                <option value="{{ $vendor->id }}|{{ $vendor->Party_name }}">{{ $vendor->Party_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger d-none" id="add_vendor_error">
                            Vendor is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="builty_date" value="{{ date('Y-m-d') }}" class="form-control">
                        <div class="text-danger d-none" id="add_date_error">
                            Date is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" id="builty_month" class="form-control">
                        <div class="text-danger d-none" id="add_month_error">
                            Month is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" id="builty_amount" class="form-control">
                        <div class="text-danger d-none" id="add_amount_error">
                            Amount is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="builty_description" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Vendor Builty Modal -->
<div class="modal fade" id="editVendorModal" tabindex="-1" aria-labelledby="editVendorModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vendor Builty</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('update-vendors-builty', ':id') }}" method="POST" id="editBuiltyForm">
                @csrf
                @method('PUT')
                <input type="hidden" name="builty_id" id="edit_builty_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Select Vendor</label>
                        <select name="vendor_id" id="edit_vendor_id" class="form-control">
                            <option value="">-- Select Vendor --</option>
                            @foreach($Vendors as $vendor)
                                <option value="{{ $vendor->id }}">{{ $vendor->Party_name }}</option>
                            @endforeach
                        </select>
                        <div class="text-danger d-none" id="edit_vendor_error">
                            Vendor is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" name="date" id="edit_builty_date" class="form-control">
                        <div class="text-danger d-none" id="edit_date_error">
                            Date is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Month</label>
                        <input type="month" name="month" id="edit_builty_month" class="form-control">
                        <div class="text-danger d-none" id="edit_month_error">
                            Month is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" name="amount" id="edit_builty_amount" class="form-control">
                        <div class="text-danger d-none" id="edit_amount_error">
                            Amount is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="edit_builty_description" class="form-control" rows="2"></textarea>
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
    // Add Builty Validation
    $(document).on('submit', '#addVendorModal form', function (e) {
        let vendor = $('#vendor_id').val();
        let date = $('#builty_date').val();
        let month = $('#builty_month').val();
        let amount = $('#builty_amount').val();
        let isValid = true;

        // Reset errors
        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');

        if (!vendor) {
            $('#add_vendor_error').removeClass('d-none');
            $('#vendor_id').addClass('is-invalid');
            isValid = false;
        }

        if (!date) {
            $('#add_date_error').removeClass('d-none');
            $('#builty_date').addClass('is-invalid');
            isValid = false;
        }

        if (!month) {
            $('#add_month_error').removeClass('d-none');
            $('#builty_month').addClass('is-invalid');
            isValid = false;
        }

        if (!amount || amount <= 0) {
            $('#add_amount_error').removeClass('d-none');
            $('#builty_amount').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Edit Builty Validation
    $(document).on('submit', '#editVendorModal form', function (e) {
        let vendor = $('#edit_vendor_id').val();
        let date = $('#edit_builty_date').val();
        let month = $('#edit_builty_month').val();
        let amount = $('#edit_builty_amount').val();
        let isValid = true;

        // Reset errors
        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');

        if (!vendor) {
            $('#edit_vendor_error').removeClass('d-none');
            $('#edit_vendor_id').addClass('is-invalid');
            isValid = false;
        }

        if (!date) {
            $('#edit_date_error').removeClass('d-none');
            $('#edit_builty_date').addClass('is-invalid');
            isValid = false;
        }

        if (!month) {
            $('#edit_month_error').removeClass('d-none');
            $('#edit_builty_month').addClass('is-invalid');
            isValid = false;
        }

        if (!amount || amount <= 0) {
            $('#edit_amount_error').removeClass('d-none');
            $('#edit_builty_amount').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Edit Button Click
    $(document).on("click", ".editBuiltyBtn", function () {
        let id = $(this).data("id");
        let vendor = $(this).data("vendor");
        let date = $(this).data("date");
        let month = $(this).data("month");
        let amount = $(this).data("amount");
        let description = $(this).data("description");

        $("#edit_builty_id").val(id);
        $("#edit_vendor_id").val(vendor);
        $("#edit_builty_date").val(date);
        $("#edit_builty_month").val(month);
        $("#edit_builty_amount").val(amount);
        $("#edit_builty_description").val(description);

        // Update form action URL
        let formAction = "{{ route('update-vendors-builty', ':id') }}".replace(':id', id);
        $("#editBuiltyForm").attr('action', formAction);
    });

    // Delete Button Click
    $(document).on("click", ".deleteBuiltyBtn", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        let deleteUrl = "{{ route('delete-vendors-builty', ':id') }}".replace(':id', id);

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
                        Swal.fire("Deleted!", response.success, "success")
                            .then(() => location.reload());
                    },
                    error: function (xhr) {
                        console.error(xhr.responseText);
                        Swal.fire("Error!", "Something went wrong: " + xhr.responseText, "error");
                    }
                });
            }
        });
    });
</script>