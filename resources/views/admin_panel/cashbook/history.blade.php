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
                    <h6>View all daily closing balances</h6>
                </div>
                <div class="page-btn">
                    <a href="{{ route('cash-book') }}" class="btn btn-primary">
                        <i class="fa fa-arrow-left"></i> Back to Daily View
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
                                    <th width="20%">Date</th>
                                    <th width="20%">Total Debit (IN)</th>
                                    <th width="20%">Total Credit (OUT)</th>
                                    <th width="20%">Closing Balance</th>
                                    <th width="15%">Action</th>
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
                                        <td class="text-success fw-bold">{{ number_format($day->total_debit, 2) }}</td>
                                        <td class="text-danger fw-bold">{{ number_format($day->total_credit, 2) }}</td>
                                        <td class="fw-bold {{ $day->closing_balance >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ number_format($day->closing_balance, 2) }}
                                        </td>
                                        <td>
                                            <a href="{{ route('cash-book', ['date' => $day->entry_date]) }}" 
                                               class="btn btn-sm btn-primary">
                                                <i class="fa fa-eye"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            No cash book history found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($dailyHistory->count() > 0)
                                <tfoot>
                                    <tr class="table-secondary fw-bold">
                                        <th colspan="2" class="text-end">Grand Total:</th>
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

@include('admin_panel.include.footer_include')
