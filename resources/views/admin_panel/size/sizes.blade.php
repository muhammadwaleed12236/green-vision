@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Size List</h4>
                    <h6>Manage Sizes</h6>
                </div>
                <div class="page-btn">
                    @if(Auth::user()->usertype === 'admin')
                        <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addSizeModal">
                            <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Size
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
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif
                    <div class="table-responsive">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Size Name</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Sizes as $key => $size)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $size->size_name }}</td>
                                        <td>
                                            @if(Auth::user()->usertype === 'admin')
                                                <button class="btn btn-sm btn-primary editSizeBtn" data-id="{{ $size->id }}"
                                                    data-name="{{ $size->size_name }}" data-bs-toggle="modal"
                                                    data-bs-target="#editSizeModal">Edit</button>

                                                <button class="btn btn-sm btn-danger deleteSizeBtn"
                                                    data-id="{{ $size->id }}">Delete</button>
                                            @else
                                                <button class="btn btn-sm btn-danger " disabled>No Action</button>
                                            @endif
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

<!-- Add Size Modal -->
<div class="modal fade" id="addSizeModal" tabindex="-1" aria-labelledby="addSizeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Size</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('store-size') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="">
                        <label class="form-label">Size Name</label>
                        <input type="text" class="form-control" id="add_size_name" name="size_name" value="{{ old('size_name') }}">
                        <div class="text-danger d-none" id="add_size_error">
                            Size name is required
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

<!-- Edit Size Modal -->
<div class="modal fade" id="editSizeModal" tabindex="-1" aria-labelledby="editSizeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Size</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('size.update') }}" method="POST">
                @csrf
                <input type="hidden" name="size_id" id="edit_size_id">
                <div class="modal-body">
                    <div class="">
                        <label class="form-label">Size Name</label>
                        <input type="text" class="form-control" name="size_name" id="edit_size_name" >
                        <div class="text-danger d-none" id="edit_size_error">
                            Size name is required
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

    $(document).on('submit', '#addSizeModal form', function(e){
        let size = $('#add_size_name').val().trim();

        if (size === '') {
            e.preventDefault();
            $('#add_size_error').removeClass('d-none');
            $('#add_size_name').addClass('is-invalid');
        }else{
            $('#add_size_error').addClass('d-none');
            $('#add_size_name').removeClass('is-invalid')
        }
    });

    $(document).on('submit', '#editSizeModal form', function(e){
        let size = $('#edit_size_name').val().trim();

        if (size === '') {
            e.preventDefault();
            $('#edit_size_error').removeClass('d-none');
            $('#edit_size_name').addClass('is-invalid');
        }else{
            $('#edit_size_error').addClass('d-none');
            $('#edit_size_name').removeClass('is-invalid')
        }
    });

    $(document).on("click", ".editSizeBtn", function () {
        let id = $(this).data("id");
        let name = $(this).data("name");
        $("#edit_size_id").val(id);
        $("#edit_size_name").val(name);
    });

    $(document).on("click", ".deleteSizeBtn", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        let deleteUrl = "/size/delete/" + id;

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
                                response.msg ?? "Staff deleted successfully.",
                                "success"
                            ).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire(
                                "Error!",
                                response.msg ?? "Delete failed",
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
</script>
