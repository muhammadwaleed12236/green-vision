@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- PAGE HEADER --}}
            <div class="page-header">
                <div class="page-title">
                    <h4>Cash Book History</h4>
                    <h6>
                        Showing ledger for:
                        <strong>{{ \Carbon\Carbon::parse($selectedMonth . '-01')->format('F Y') }}</strong>
                    </h6>
                </div>
                <div class="page-btn d-flex gap-2 align-items-center flex-wrap">

                    {{-- Month Filter --}}
                    <form method="GET" action="{{ route('cash-book.history') }}" class="d-flex align-items-center gap-2 mb-0">
                        <label class="mb-0 fw-semibold text-nowrap" for="monthFilter">
                            <i class="fa fa-filter"></i> Filter Month:
                        </label>
                        <select name="month" id="monthFilter" class="form-select form-select-sm" style="min-width:160px;" onchange="this.form.submit()">
                            @foreach($availableMonths as $ym)
                                <option value="{{ $ym }}" {{ $selectedMonth === $ym ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::parse($ym . '-01')->format('F Y') }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <a href="{{ route('cash-book') }}" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to Monthly View
                    </a>
                </div>
            </div>


            {{-- HISTORY TABLE --}}
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%">#</th>
                                    <th width="15%">Date</th>
                                    <th width="25%">Description</th>
                                    <th width="15%">Total Debit (IN)</th>
                                    <th width="15%">Total Credit (OUT)</th>
                                    <th width="15%">Closing Balance</th>
                                    <th width="10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyHistory as $k => $day)
                                    <tr>
                                        <td>{{ $dailyHistory->firstItem() + $k }}</td>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($day->entry_date)->format('d M Y') }}</strong><br>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($day->entry_date)->format('l') }}</small>
                                        </td>
                                        <td>{{ $day->description }}</td>
                                        <td class="text-success fw-bold">{{ number_format($day->total_debit, 2) }}</td>
                                        <td class="text-danger fw-bold">{{ number_format($day->total_credit, 2) }}</td>
                                        <td class="fw-bold {{ $day->closing_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($day->closing_balance, 2) }}
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editEntryModal"
                                                    onclick="editEntry({{ $day->id }}, '{{ $day->entry_date }}', '{{ addslashes($day->description) }}', '{{ $day->total_debit }}', '{{ $day->total_credit }}')">
                                                <i class="fa fa-edit"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            No cash book history found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($dailyHistory->count() > 0)
                                <tfoot>
                                    <tr class="table-secondary fw-bold">
                                        <th colspan="3" class="text-end">Grand Total:</th>
                                        <th class="text-success">{{ number_format($dailyHistory->sum('total_debit'), 2) }}</th>
                                        <th class="text-danger">{{ number_format($dailyHistory->sum('total_credit'), 2) }}</th>
                                        <th class="{{ ($dailyHistory->sum('total_debit') - $dailyHistory->sum('total_credit')) >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($dailyHistory->sum('total_debit') - $dailyHistory->sum('total_credit'), 2) }}
                                        </th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    @if($dailyHistory->hasPages())
                        <div class="d-flex justify-content-center mt-3">
                            {{ $dailyHistory->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>

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

<script>
function editEntry(id, date, description, debit, credit) {
    document.getElementById('edit_entry_id').value = id;
    document.getElementById('edit_date').value = date;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_debit').value = debit > 0 ? debit : '';
    document.getElementById('edit_credit').value = credit > 0 ? credit : '';
}
</script>

@include('admin_panel.include.footer_include')
