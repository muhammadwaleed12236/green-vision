@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        /* ============================================
           RESPONSIVE LAYOUT (mirror of admin dashboard)
           ============================================ */

        html, body {
            overflow-x: hidden;
            width: 100%;
            margin: 0;
        }

        img, canvas, table {
            max-width: 100%;
        }

        /* Expenses table never scrolls horizontally - cells wrap to fit */
        .ex-wrap {
            overflow-x: hidden;
        }
        .ex-wrap .datanew {
            table-layout: fixed !important;
            width: 100% !important;
            max-width: 100% !important;
            border-spacing: 0 !important;
        }
        .ex-wrap .datanew td,
        .ex-wrap .datanew th {
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
        }
        .ex-wrap .datanew th:nth-child(1) { width: 6% !important; }
        .ex-wrap .datanew th:nth-child(2) { width: 14% !important; }
        .ex-wrap .datanew th:nth-child(3) { width: 18% !important; }
        .ex-wrap .datanew th:nth-child(4) { width: 34% !important; }
        .ex-wrap .datanew th:nth-child(5) { width: 13% !important; }
        .ex-wrap .datanew th:nth-child(6) { width: 15% !important; }
        .ex-wrap .dataTables_wrapper {
            max-width: 100%;
        }

        /* Empty-state message: keep it a full-width centered table cell */
        .ex-wrap .datanew td.dataTables_empty {
            display: table-cell !important;
            width: 100% !important;
            text-align: center !important;
            padding: 28px !important;
            color: #94a3b8;
            font-weight: 500;
            border: 0 !important;
        }

        /* Actions cell: keep button wrapping inside the cell on desktop */
        @media (min-width: 768px) {
            .ex-wrap .datanew td:last-child:not(.dataTables_empty) {
                display: flex;
                flex-wrap: wrap;
                gap: 6px;
                justify-content: flex-start;
            }
        }

        /* ---------- Phone & below (576px) ---------- */
        @media (max-width: 575.98px) {
            .page-title h4 { font-size: 1.05rem; }
            .page-title h6 { font-size: 0.85rem; }
            .ex-wrap .dataTables_filter { margin-bottom: 8px; }
            .ex-wrap .dataTables_filter input { max-width: 130px; }
            .ex-wrap .dataTables_length select { max-width: 70px; }
        }

        /* ---------- Expenses table -> stacked cards (phone & small tablet) ---------- */
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
            .table-responsive .datanew td:last-child {
                flex-wrap: wrap;
                row-gap: 4px;
            }
            .ex-wrap .dataTables_filter,
            .ex-wrap .dataTables_length,
            .ex-wrap .dataTables_info,
            .ex-wrap .dataTables_paginate {
                max-width: 100%;
            }
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Expense List</h4>
                    <h6>Manage Expenses</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
                        <img src="assets/img/icons/plus.svg" class="me-1" alt="img">Add Expense
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
                    <div class="table-responsive ex-wrap">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($expenses as $key => $expense)
                                    @php
                                        $isJobAssignment = strpos($expense->description, 'Job Assignment #') !== false;
                                    @endphp
                                    <tr class="{{ $isJobAssignment ? 'table-warning' : '' }}">
                                        <td data-label="#">{{ $key + 1 }}</td>
                                        <td data-label="Date">{{ \Carbon\Carbon::parse($expense->expense_date)->format('d/m/Y') }}</td>
                                        <td data-label="Category">
                                            {{ $expense->expenseCategory->expense_name ?? 'N/A' }}
                                            @if($isJobAssignment)
                                                <span class="badge bg-info ms-1">Auto-Generated</span>
                                            @endif
                                        </td>
                                        <td data-label="Description">{{ $expense->description }}</td>
                                        <td data-label="Amount">{{ number_format($expense->amount) }}</td>
                                        <td data-label="Action">
                                            @if($isJobAssignment)
                                                <span class="text-muted small">
                                                    <i class="fas fa-lock me-1"></i>Protected Entry
                                                </span>
                                            @else
                                                <button class="btn btn-sm btn-primary editExpenseBtn"
                                                    data-id="{{ $expense->id }}"
                                                    data-category="{{ $expense->expenseCategory->expense_name ?? '' }}"
                                                    data-amount="{{ $expense->amount }}"
                                                    data-date="{{ $expense->expense_date }}"
                                                    data-description="{{ $expense->description }}" data-bs-toggle="modal"
                                                    data-bs-target="#editExpenseModal">
                                                    Edit
                                                </button>

                                                <button class="btn btn-sm btn-danger deleteExpenseBtn"
                                                    data-id="{{ $expense->id }}">Delete</button>
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

<!-- Add Expense Modal -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Expense</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('store-expense') }}" method="POST">
                @csrf
                <meta name="csrf-token" content="{{ csrf_token() }}">

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Category</label>
                        <select class="form-control" name="expense_category" required>
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $category)
                                <option value="{{ $category->expense_name }}">{{ $category->expense_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title/Description</label>
                        <input type="text" class="form-control" name="title" placeholder="Enter description">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Edit Expense Modal -->
<div class="modal fade" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Expense</h5>
                <button type="button" class="btn-close text-black" data-bs-dismiss="modal" aria-label="Close">X</button>
            </div>
            <form action="{{ route('update-expense') }}" method="POST">
                @csrf
                <input type="hidden" id="edit_expense_id" name="expense_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Expense Category</label>
                        <select class="form-control" id="edit_expense_category" name="expense_category" required>
                            <option value="">Select Category</option>
                            @foreach($expenseCategories as $category)
                                <option value="{{ $category->expense_name }}">{{ $category->expense_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" class="form-control" id="edit_expense_amount" name="amount" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" id="edit_expense_date" name="date" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" id="edit_expense_description" name="description"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@include('admin_panel.include.footer_include')
<!-- SweetAlert2 Library -->


<script>
    // DELETE Expense with SweetAlert and AJAX
    $(document).on("click", ".deleteExpenseBtn", function (e) {
        e.preventDefault();

        let id = $(this).data("id");
        let deleteUrl = "{{ route('delete-expense', ':id') }}".replace(':id', id);

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
                    headers: {
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content") // Include CSRF token
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

    // EDIT Expense - Filling Modal Data
    $(document).on("click", ".editExpenseBtn", function () {
        let id = $(this).data("id");
        let category = $(this).data("category");
        let title = $(this).data("title");
        let amount = $(this).data("amount");
        let date = $(this).data("date");
        let description = $(this).data("description");

        $("#edit_expense_id").val(id);
        $("#edit_expense_category").val(category);

        // Properly selecting the category in dropdown
        $("#edit_expense_category option").each(function () {
            $(this).prop("selected", $(this).val() == category);
        });

        $("#edit_expense_title").val(title);
        $("#edit_expense_amount").val(amount);
        $("#edit_expense_date").val(date);
        $("#edit_expense_description").val(description);
    });
</script>
