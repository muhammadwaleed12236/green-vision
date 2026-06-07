@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- PAGE HEADER --}}
            <div class="page-header">
                <div class="page-title">
                    <h4>Cash Book / Ledger - Daily</h4>
                    <h6>Each day starts from 0 balance</h6>
                </div>
                <div class="page-btn d-flex gap-2 align-items-center">
                    <a href="{{ route('cash-book.history') }}" class="btn btn-info">
                        <i class="fa fa-history"></i> View History
                    </a>
                    <form method="GET" action="{{ route('cash-book') }}" class="d-flex gap-2">
                        <input type="date" name="date" class="form-control" value="{{ $selectedDate }}" onchange="this.form.submit()">
                    </form>
                    <button class="btn btn-added" data-bs-toggle="modal" data-bs-target="#addEntryModal">
                        + Add Entry
                    </button>
                </div>
            </div>

            {{-- CASH BOOK TABLE --}}
            <div class="card">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            <strong>Success!</strong> {{ session('success') }}.
                        </div>
                    @endif

                    <div class="mb-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Date: {{ \Carbon\Carbon::parse($selectedDate)->format('d M Y, l') }}</h5>
                        <div class="badge bg-success fs-6">Opening Balance: 0.00</div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="20%">Description</th>
                                    <th width="15%">Debit (IN)</th>
                                    <th width="15%">Credit (OUT)</th>
                                    <th width="15%">Balance</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($entries as $k => $entry)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td class="text-success fw-bold">
                                            {{ $entry->debit > 0 ? number_format($entry->debit, 2) : '—' }}
                                        </td>
                                        <td class="text-danger fw-bold">
                                            {{ $entry->credit > 0 ? number_format($entry->credit, 2) : '—' }}
                                        </td>
                                        <td class="fw-bold {{ $entry->running_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($entry->running_balance, 2) }}
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary editEntryBtn"
                                                    data-id="{{ $entry->id }}"
                                                    data-date="{{ $entry->date }}"
                                                    data-description="{{ $entry->description }}"
                                                    data-debit="{{ $entry->debit }}"
                                                    data-credit="{{ $entry->credit }}"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editEntryModal">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger deleteEntryBtn"
                                                    data-id="{{ $entry->id }}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No entries for this date</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot>
                                <tr class="table-secondary fw-bold">
                                    <th colspan="2" class="text-end">Total for Day:</th>
                                    <th class="text-success">{{ number_format($entries->sum('debit'), 2) }}</th>
                                    <th class="text-danger">{{ number_format($entries->sum('credit'), 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                                <tr class="table-success fw-bold fs-5">
                                    <th colspan="4" class="text-end">Closing Balance:</th>
                                    <th class="{{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($closingBalance, 2) }}
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ADD ENTRY MODAL --}}
<div class="modal fade" id="addEntryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('cash-book.store') }}">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Add New Entry</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" value="{{ $selectedDate }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" placeholder="e.g. Sale payment received" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Debit (Received)</label>
                            <input type="number" step="0.01" class="form-control" name="debit" placeholder="0.00" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Credit (Paid)</label>
                            <input type="number" step="0.01" class="form-control" name="credit" placeholder="0.00" min="0">
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <small><strong>Tip:</strong> Debit = Money IN (Received), Credit = Money OUT (Paid)</small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- EDIT ENTRY MODAL --}}
<div class="modal fade" id="editEntryModal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('cash-book.update') }}">
                @csrf
                <input type="hidden" name="entry_id" id="edit_entry_id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Entry</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" id="edit_date" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <input type="text" class="form-control" name="description" id="edit_description" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Debit (Received)</label>
                            <input type="number" step="0.01" class="form-control" name="debit" id="edit_debit" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Credit (Paid)</label>
                            <input type="number" step="0.01" class="form-control" name="credit" id="edit_credit" min="0">
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Entry</button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
$(document).on("click", ".editEntryBtn", function () {
    let id = $(this).data("id");
    let date = $(this).data("date");
    let description = $(this).data("description");
    let debit = $(this).data("debit");
    let credit = $(this).data("credit");

    $("#edit_entry_id").val(id);
    $("#edit_date").val(date);
    $("#edit_description").val(description);
    $("#edit_debit").val(debit);
    $("#edit_credit").val(credit);
});

$(document).on("click", ".deleteEntryBtn", function (e) {
    e.preventDefault();

    let entryId = $(this).data("id");

    Swal.fire({
        title: "Are you sure?",
        text: "This entry will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!"
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: "{{ route('cash-book.delete', ':id') }}".replace(':id', entryId),
                type: "DELETE",
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function (response) {
                    if (response.status === 'success') {
                        Swal.fire("Deleted!", response.message, "success")
                            .then(() => location.reload());
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
