@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Staff Management List</h4>
                    <h6>Manage Staff Management</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addSalesmanModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Staff
                    </button>
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
                                    <th>Designation</th>
                                    <th>Name</th>
                                    <th>Phone</th>
                                    <th>Address</th>
                                    <th>Salary</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($salesmen as $key => $salesman)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>{{ $salesman->designation }}</td>
                                        <td>{{ $salesman->name }}</td>
                                        <td>{{ $salesman->phone }}</td>
                                        <td>{{ $salesman->address }}</td>
                                        <td>{{ number_format($salesman->salary) }}</td>
                                        <td>
                                            <button class="btn btn-sm toggle-status" data-id="{{ $salesman->id }}"
                                                data-status="{{ $salesman->status }}">
                                                {{ $salesman->status == 1 ? 'Active' : 'Inactive' }}
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editSalesmanBtn"
                                                data-id="{{ $salesman->id }}" data-name="{{ $salesman->name }}"
                                                data-phone="{{ $salesman->phone }}" data-city="{{ $salesman->city }}"
                                                data-area="{{ $salesman->area }}" data-address="{{ $salesman->address }}"
                                                data-salary="{{ $salesman->salary }}" data-status="{{ $salesman->status }}"
                                                data-designation="{{ $salesman->designation }}" data-bs-toggle="modal"
                                                data-bs-target="#editSalesmanModal">
                                                Edit
                                            </button>

                                            <button class="btn btn-sm btn-danger deleteSalesmanBtn"
                                                data-id="{{ $salesman->id }}">
                                                Delete
                                            </button>
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

<!-- Add Salesman Modal -->
<div class="modal fade" id="addSalesmanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Staff </h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
            </div>
            <form action="{{ route('store-salesman') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Address - Dynamic Width -->
                        <div class="mb-3" id="addressWrapper">
                            <label for="address" class="form-label">Address</label>
                            <input type="text" class="form-control" name="address" required>
                        </div>

                        <!-- Salary - Hidden by default -->
                        <div class="col-md-6 mb-3 d-none" id="salaryWrapper">
                            <label for="salary" class="form-label">Salary</label>
                            <input type="number" class="form-control" name="salary" id="salaryField">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Designation -->
                        <div class="col-md-6 mb-3">
                            <label for="designation" class="form-label">Designation</label>
                            <select class="form-control" name="designation" id="designationSelect" required>
                                <option value="">Select Type</option>
                                <option value="contract">Contract</option>
                                <option value="labour">Labour</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-control" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>

                        <!-- These fields will be shown only when designation is "Saleman" -->
                        <div class="row d-none" id="loginFields">
                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="emailField">
                            </div>

                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" name="password" id="passwordField">
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

<!-- Edit Staff Modal -->
<div class="modal fade" id="editSalesmanModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Edit Staff</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
            </div>

            <form action="{{ route('update-salesman') }}" method="POST">
                @csrf
                <input type="hidden" id="edit_salesman_id" name="salesman_id">

                <div class="modal-body">
                    <div class="row">
                        <!-- Name -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_name" class="form-label">Name</label>
                            <input type="text" class="form-control" id="edit_name" name="name" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="edit_phone" name="phone" required>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Address - Dynamic Width -->
                        <div class="mb-3" id="editAddressWrapper">
                            <label for="edit_address" class="form-label">Address</label>
                            <input type="text" class="form-control" id="edit_address" name="address" required>
                        </div>

                        <!-- Salary - Hidden by default -->
                        <div class="col-md-6 mb-3 d-none" id="editSalaryWrapper">
                            <label for="edit_salary" class="form-label">Opening Balance</label>
                            <input type="number" class="form-control" id="edit_salary" name="salary">
                        </div>
                    </div>

                    <div class="row">
                        <!-- Designation -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_designation" class="form-label">Designation</label>
                            <select class="form-control" name="designation" id="edit_designation" required>
                                <option value="contract">Contract</option>
                                <option value="labour">Labour</option>
                            </select>
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label for="edit_status" class="form-label">Status</label>
                            <select class="form-control" id="edit_status" name="status" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
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
    $(document).ready(function () {
        // Designation change handler for Add Modal
        const designationSelect = document.getElementById("designationSelect");
        const loginFields = document.getElementById("loginFields");
        const emailField = document.getElementById("emailField");
        const passwordField = document.getElementById("passwordField");
        const salaryWrapper = document.getElementById("salaryWrapper");
        const salaryField = document.getElementById("salaryField");
        const addressWrapper = document.getElementById("addressWrapper");

        function toggleLoginFields() {
            if (designationSelect.value === "Saleman") {
                loginFields.classList.remove("d-none");
                emailField.setAttribute("required", "required");
                passwordField.setAttribute("required", "required");
            } else {
                loginFields.classList.add("d-none");
                emailField.removeAttribute("required");
                passwordField.removeAttribute("required");
                emailField.value = "";
                passwordField.value = "";
            }
        }

        function toggleSalaryField() {
            if (designationSelect.value === "labour") {
                // Show salary field and make address 50% width
                salaryWrapper.classList.remove("d-none");
                salaryField.setAttribute("required", "required");
                addressWrapper.classList.remove("col-md-12");
                addressWrapper.classList.add("col-md-6");
            } else {
                // Hide salary field and make address full width
                salaryWrapper.classList.add("d-none");
                salaryField.removeAttribute("required");
                salaryField.value = "";
                addressWrapper.classList.remove("col-md-6");
                addressWrapper.classList.add("col-md-12");
            }
        }

        designationSelect.addEventListener("change", function () {
            toggleLoginFields();
            toggleSalaryField();
        });

        // Initial call
        toggleLoginFields();
        toggleSalaryField();

        // Fetch areas on City Change
        $('#citySelect').change(function () {
            var cityId = $(this).val();
            $('#areasSelect').html('<option value="">Loading...</option>');

            if (cityId) {
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    type: "GET",
                    data: { city_id: cityId },
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
                $('#areasSelect').html('<option value="">Area Not Found...</option>');
            }
        });

        // Edit Salesman Button Click
        $(document).on("click", ".editSalesmanBtn", function () {
            $("#edit_salesman_id").val($(this).data("id"));
            $("#edit_name").val($(this).data("name"));
            $("#edit_phone").val($(this).data("phone"));
            $("#edit_city").val($(this).data("city"));
            $("#edit_area").val($(this).data("area"));
            $("#edit_address").val($(this).data("address"));
            $("#edit_status").val($(this).data("status"));

            let designation = $(this).data("designation");
            $("#edit_designation").val(designation);

            $("#edit_salary").val($(this).data("salary"));

            // Trigger designation change to show/hide salary field
            toggleEditSalary();

            // Fetch areas for selected city
            let cityName = $(this).data("city");
            if (cityName) {
                fetchEditAreas(cityName, $(this).data("area"));
            }
        });

        // City change handler for Edit Modal
        $('#edit_city').change(function () {
            var cityName = $(this).val();
            $('#edit_area').html('<option value="">Loading...</option>');

            if (cityName) {
                $.ajax({
                    url: "{{ route('fetch-areas') }}",
                    type: "GET",
                    data: { city_id: cityName },
                    success: function (data) {
                        $('#edit_area').html('<option value="">Select Area</option>');
                        $.each(data, function (key, area) {
                            $('#edit_area').append('<option value="' + area.area_name + '">' + area.area_name + '</option>');
                        });
                    },
                    error: function () {
                        alert('Error fetching areas.');
                    }
                });
            } else {
                $('#edit_area').html('<option value="">Select City First</option>');
            }
        });

        // Designation change handler for Edit Modal
        const editDesignation = document.getElementById("edit_designation");
        const editSalaryWrapper = document.getElementById("editSalaryWrapper");
        const editSalaryField = document.getElementById("edit_salary");
        const editAddressWrapper = document.getElementById("editAddressWrapper");

        function toggleEditSalary() {
            if (editDesignation.value === "labour") {
                editSalaryWrapper.classList.remove("d-none");
                editSalaryField.setAttribute("required", "required");
                editAddressWrapper.classList.remove("col-md-12");
                editAddressWrapper.classList.add("col-md-6");
            } else {
                editSalaryWrapper.classList.add("d-none");
                editSalaryField.removeAttribute("required");
                editSalaryField.value = "";
                editAddressWrapper.classList.remove("col-md-6");
                editAddressWrapper.classList.add("col-md-12");
            }
        }

        editDesignation.addEventListener("change", toggleEditSalary);

        // Toggle Status Button
        $(".toggle-status").click(function () {
            var button = $(this);
            var salesmanId = button.data("id");
            var currentStatus = button.data("status");

            $.ajax({
                url: "{{ route('toggle-salesman-status') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    salesman_id: salesmanId,
                    status: currentStatus == 1 ? 0 : 1
                },
                success: function (response) {
                    if (response.success) {
                        let newStatus = currentStatus == 1 ? 0 : 1;
                        button.data("status", newStatus);
                        button.text(newStatus == 1 ? "Active" : "Inactive");

                        if (newStatus == 1) {
                            button.removeClass("btn-danger").addClass("btn-success");
                        } else {
                            button.removeClass("btn-success").addClass("btn-danger");
                        }
                    }
                }
            });
        });

        // Delete Salesman Button with SweetAlert
        $(document).on("click", ".deleteSalesmanBtn", function (e) {
            e.preventDefault();

            let id = $(this).data("id");
            let deleteUrl = "/salesman/delete/" + id;

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
    });

    // Fetch areas based on selected city (for edit modal)
    function fetchEditAreas(cityName, selectedArea) {
        if (cityName) {
            $.ajax({
                url: "{{ route('fetch-areas') }}",
                type: 'GET',
                data: { city_id: cityName },
                success: function (response) {
                    $('#edit_area').html('<option value="">Select Area</option>');
                    $.each(response, function (key, area) {
                        var selected = area.area_name == selectedArea ? 'selected' : '';
                        $('#edit_area').append('<option value="' + area.area_name + '" ' + selected + '>' + area.area_name + '</option>');
                    });
                }
            });
        }
    }
</script>