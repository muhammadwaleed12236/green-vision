@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- PAGE HEADER --}}
            <div class="page-header">
                <div class="page-title">
                    <h4>📊 {{ $account->name }} - Account Ledger</h4>
                    <h6>View complete transaction history</h6>
                </div>
            </div>

            {{-- FILTERS --}}
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ route('chart-of-accounts.ledger', $account->id) }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-3 mb-3">
                                <label>From Date</label>
                                <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>To Date</label>
                                <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                            </div>
                            <div class="col-md-6 mb-3 d-flex gap-2">
                                <button type="submit" class="btn btn-primary flex-fill" style="background: #3b82f6; border: none;">Filter</button>
                                <a href="{{ route('chart-of-accounts.ledger', $account->id) }}" class="btn btn-outline-primary flex-fill">Reset</a>
                                <a href="{{ route('chart-of-accounts.index') }}" class="btn btn-secondary flex-fill" style="background: #4f46e5; border: none;">Back</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- LEDGER TABLE --}}
            <div class="card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="text-white text-center" style="background-color: #2a2e33;">
                                <tr>
                                    <th class="py-3">Date</th>
                                    <th class="py-3">Voucher No</th>
                                    <th class="py-3 text-start">Description</th>
                                    <th class="py-3">Party</th>
                                    <th class="py-3">Debit</th>
                                    <th class="py-3">Credit</th>
                                    <th class="py-3">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Opening Balance Row --}}
                                @php
                                    $runningBalance = $account->opening_balance;
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp
                                <tr style="background-color: #f8f9fa;">
                                    <td colspan="6" class="fw-bold py-3 px-4 text-start">Opening Balance</td>
                                    <td class="text-center fw-bold py-3 text-primary">{{ number_format($runningBalance, 2) }} {{ $account->balance_type == 'debit' ? 'Dr' : 'Cr' }}</td>
                                </tr>

                                {{-- Transactions --}}
                                @forelse($transactions as $txn)
                                    @php
                                        // Update running balance based on account type
                                        if ($account->balance_type == 'debit') {
                                            $runningBalance += $txn['debit'] - $txn['credit'];
                                        } else {
                                            $runningBalance += $txn['credit'] - $txn['debit'];
                                        }

                                        $totalDebit += $txn['debit'];
                                        $totalCredit += $txn['credit'];
                                    @endphp
                                    <tr class="text-center">
                                        <td class="py-3">{{ \Carbon\Carbon::parse($txn['date'])->format('d/m/Y') }}</td>
                                        <td class="py-3">
                                            <span class="badge bg-light text-dark border">{{ $txn['voucher_no'] }}</span>
                                        </td>
                                        <td class="py-3 text-start">{{ $txn['description'] }}</td>
                                        <td class="py-3 text-muted">{{ $txn['party'] }}</td>
                                        <td class="py-3 text-success">{{ $txn['debit'] > 0 ? number_format($txn['debit'], 2) : '-' }}</td>
                                        <td class="py-3 text-danger">{{ $txn['credit'] > 0 ? number_format($txn['credit'], 2) : '-' }}</td>
                                        <td class="py-3 fw-semibold">
                                            {{ number_format(abs($runningBalance), 2) }} 
                                            <small class="text-muted">{{ $runningBalance >= 0 ? ($account->balance_type == 'debit' ? 'Dr' : 'Cr') : ($account->balance_type == 'debit' ? 'Cr' : 'Dr') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No transactions found for the selected period.</td>
                                    </tr>
                                @endforelse

                                {{-- Total Row --}}
                                <tr class="text-white text-center fw-bold" style="background-color: #8c8c8c;">
                                    <td colspan="4" class="py-3 text-start px-4">Total Period</td>
                                    <td class="py-3">{{ number_format($totalDebit, 2) }}</td>
                                    <td class="py-3">{{ number_format($totalCredit, 2) }}</td>
                                    <td class="py-3">{{ number_format(abs($runningBalance), 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
