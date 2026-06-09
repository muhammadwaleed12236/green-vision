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
                                @forelse($monthlyEntries as $k => $row)
                                    <tr>
                                        <td>{{ $k + 1 }}</td>
                                        <td>{{ $entry->description }}</td>
                                        <td class="text-success fw-bold">
                                            {{ $row->total_debit > 0 ? number_format($row->total_debit, 2) : '—' }}
                                        </td>
                                        <td class="text-danger fw-bold">
                                            {{ $row->total_credit > 0 ? number_format($row->total_credit, 2) : '—' }}
                                        </td>
                                        <td class="fw-bold {{ $row->running_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($row->running_balance, 2) }}
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
                                    <th colspan="3" class="text-end">Closing Balance:</th>
                                    <th colspan="2" class="{{ $closingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($closingBalance, 2) }}
                                    </th>
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
                        <input type="date" class="form-control" name="date" value="{{ $selectedMonth . '-' . date('d') }}" required>
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

@include('admin_panel.include.footer_include')