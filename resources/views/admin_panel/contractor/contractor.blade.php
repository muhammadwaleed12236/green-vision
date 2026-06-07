@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Contractor List</h4>
                    <h6>Manage Contractors</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addContractorModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Contractor
                    </button>
                </div>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success">
                    <strong>Success!</strong> {{ session('success') }}.
                </div>
            @endif

            <div class="container">
                <div class="table-responsive">
                    <table class="table datanew">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>admin_or_user_id</th>
                                <th>contractor_name</th>
                                <th>phone_number</th>
                                <th>address</th>
                                <th>opening_balance</th>
                                <th>created_at</th>
                                <th>updated_at</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($contractors as $key => $contractor)
                                <tr>
                                    <td>{{ $contractor->id }}</td>
                                    <td>{{ $contractor->admin_or_user_id }}</td>
                                    <td>{{ $contractor->contractor_name }}</td>
                                    <td>{{ $contractor->phone_number }}</td>
                                    <td>{{ $contractor->address }}</td>
                                    <td>{{ number_format($contractor->opening_balance) }}</td>
                                    <td>{{ $contractor->created_at }}</td>
                                    <td>{{ $contractor->updated_at }}</td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <button class="btn btn-sm btn-primary editContractorBtn"
                                                data-id="{{ $contractor->id }}">Edit</button>
                                            <button class="btn btn-sm btn-secondary copyContractorBtn" 
                                                data-name="{{ $contractor->contractor_name }}">Copy</button>
                                            <button class="btn btn-sm btn-danger deleteContractorBtn"
                                                data-id="{{ $contractor->id }}">Delete</button>
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

<!-- Add Contractor Modal -->
<div class="modal fade" id="addContractorModal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('contractor.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Add Contractor</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contractor Name</label>
                                <input type="text" name="contractor_name" class="form-control"
                                    value="{{ old('contractor_name') }}" placeholder="Enter Name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" name="address" class="form-control" value="{{ old('address') }}"
                                    placeholder="Enter Address">
                            </div>
                        </div>
                        <div class="col-md-6">
                    <div class="">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control"
                            value="{{ old('phone_number') }}" placeholder="Enter Phone Number">
                    </div>
                        </div>
                        <div class="col-md-6">
                    <div class="">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" name="opening_balance" class="form-control"
                            value="{{ old('opening_balance') }}" placeholder="Enter Opening Balance">
                    </div>
                        </div>
                    </div>


                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Contractor Modal -->
<div class="modal fade" id="editContractorModal" tabindex="-1" aria-labelledby="editContractorModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Contractor</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('contractor.update') }}" method="POST">
                @csrf
                <input type="hidden" name="contractor_id" id="edit_contractor_id">
                <div class="modal-body">
                    <div class="row">

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Contractor Name</label>
                                <input type="text" class="form-control" name="contractor_name" id="edit_contractor_name">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <input type="text" class="form-control" name="address" id="edit_address">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="">
                                <label class="form-label">Phone Number</label>
                                <input type="text" name="phone_number" class="form-control" id="edit_phone_number"
                                placeholder="Enter Phone Number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="">
                                <label class="form-label">Opening Balance</label>
                                <input type="number" name="opening_balance" class="form-control"
                                id="edit_opening_balance" placeholder="Enter Opening Balance">
                            </div>

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
    $(document).on("click", ".editContractorBtn", function () {
        let id = $(this).data("id");

        $.ajax({
            url: "{{ route('contractor.edit', ':id') }}".replace(':id', id),
            type: "GET",
            success: function (response) {
                $("#edit_contractor_id").val(response.id);
                $("#edit_contractor_name").val(response.contractor_name);
                $("#edit_phone_number").val(response.phone_number);
                $("#edit_address").val(response.address);
                $("#edit_opening_balance").val(parseFloat(response.ledger.opening_balance));


                $("#editContractorModal").modal("show");
            }
        });
    });


    $(document).on("click", ".copyContractorBtn", function() {
        let name = $(this).data('name');
        navigator.clipboard.writeText(name).then(() => {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Name copied: ' + name,
                showConfirmButton: false,
                timer: 1500
            });
        });
    });

    $(document).on("click", ".deleteContractorBtn", function (e) {
        e.preventDefault();
        let contractorId = $(this).data("id");

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
                    url: "{{ route('delete-contractor', '') }}/" + contractorId,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire("Deleted!", response.message, "success");
                            location.reload();
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
</script>
