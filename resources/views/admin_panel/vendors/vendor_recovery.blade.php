@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Vendor Payments</h4>
                    <h6>Track all Payments from salesmen</h6>
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
                                    <th>Date</th>
                                    <th>Party Code</th>
                                    <th>Party Name</th>
                                    <th>Paid Amount</th>
                                    <th>Description</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($VendorPayments as $key => $VendorPayment)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $VendorPayment->payment_date }}</td>
                                        <td>{{ $VendorPayment->vendor->Party_code ?? 'N/A' }}</td>
                                        <td>{{ $VendorPayment->vendor->Party_name ?? 'N/A' }}</td>
                                        <td>{{ number_format($VendorPayment->amount_paid) }}</td>
                                        <td>{{ $VendorPayment->description }}</td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editPaymentBtn"
                                                data-id="{{ $VendorPayment->id }}"
                                                data-amount="{{ $VendorPayment->amount_paid }}"
                                                data-date="{{ $VendorPayment->payment_date }}"
                                                data-description="{{ $VendorPayment->description }}"
                                                data-vendor="{{ $VendorPayment->vendor->Party_name ?? 'N/A' }}"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#editPaymentModal">Edit</button>

                                            <button class="btn btn-sm btn-danger deletePaymentBtn"
                                                data-id="{{ $VendorPayment->id }}">Delete</button>
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

<!-- Edit Payment Modal -->
<div class="modal fade" id="editPaymentModal" tabindex="-1" aria-labelledby="editPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Vendor Payment</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('update-vendor-payment') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_id" id="modal_payment_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Vendor</label>
                        <input type="text" class="form-control" id="modal_vendor_name" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Original Amount</label>
                        <input type="text" class="form-control" id="modal_amount_paid" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Update Type</label>
                        <select class="form-control" name="adjust_type" id="modal_adjust_type">
                            <option value="">-- Select --</option>
                            <option value="plus">+ plus</option>
                            <option value="minus">– minus</option>
                        </select>
                        <div class="text-danger d-none" id="edit_adjust_type_error">
                            Update type is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount to Adjust</label>
                        <input type="number" step="0.01" class="form-control" name="adjust_amount" id="modal_adjust_amount">
                        <div class="text-danger d-none" id="edit_adjust_amount_error">
                            Adjust amount is required
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" id="modal_description">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" id="modal_date">
                        <div class="text-danger d-none" id="edit_date_error">
                            Date is required
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

<script>
    // Edit Payment Validation
    $(document).on('submit', '#editPaymentModal form', function (e) {
        let adjustType = $('#modal_adjust_type').val();
        let adjustAmount = $('#modal_adjust_amount').val();
        let date = $('#modal_date').val();
        let isValid = true;

        // Reset errors
        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');

        if (!adjustType) {
            $('#edit_adjust_type_error').removeClass('d-none');
            $('#modal_adjust_type').addClass('is-invalid');
            isValid = false;
        }

        if (!adjustAmount || adjustAmount <= 0) {
            $('#edit_adjust_amount_error').removeClass('d-none');
            $('#modal_adjust_amount').addClass('is-invalid');
            isValid = false;
        }

        if (!date) {
            $('#edit_date_error').removeClass('d-none');
            $('#modal_date').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
        }
    });

    // Edit Button Click
    $(document).on('click', '.editPaymentBtn', function () {
        let id = $(this).data('id');
        let amount = $(this).data('amount');
        let vendor = $(this).data('vendor');
        let date = $(this).data('date');
        let description = $(this).data('description');

        $('#modal_payment_id').val(id);
        $('#modal_amount_paid').val(amount);
        $('#modal_vendor_name').val(vendor);
        $('#modal_date').val(date);
        $('#modal_description').val(description);

        // Reset validation errors
        $('.text-danger').addClass('d-none');
        $('.form-control').removeClass('is-invalid');
    });

    // Delete Button Click
    $(document).on('click', '.deletePaymentBtn', function (e) {
        e.preventDefault();

        let id = $(this).data('id');
        let deleteUrl = "{{ route('delete-vendor-payment', ':id') }}".replace(':id', id);

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