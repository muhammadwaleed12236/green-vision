@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')
    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Purchase Returns</h4>
                    <h6>View all purchase return records</h6>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success alert-dismissible fade show">
                            <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover datanew">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Return Date</th>
                                    <th>Vendor Name</th>
                                    <th>Items</th>
                                    <th class="text-end">Total Return Amount</th>
                                    <th>Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($Purchases as $purchase)
                                    @php
                                        $items = json_decode($purchase->item ?? '[]', true) ?: [];
                                        $returnQtys = json_decode($purchase->return_qty ?? '[]', true) ?: [];
                                        $itemCount = is_array($items) ? count($items) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $purchase->purchase->invoice_number ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($purchase->return_date)->format('d M Y') }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-danger text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                    {{ strtoupper(substr($purchase->purchase->vendor?->Party_name ?? 'N', 0, 1)) }}
                                                </div>
                                                <span class="fw-semibold">{{ $purchase->purchase->vendor?->Party_name ?? 'N/A' }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="collapse" data-bs-target="#items-{{ $purchase->id }}">
                                                <i class="fa fa-list me-1"></i>{{ $itemCount }} Items
                                            </button>
                                            <div class="collapse mt-2" id="items-{{ $purchase->id }}">
                                                <div class="card card-body p-2">
                                                    <table class="table table-sm mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th>Item</th>
                                                                <th>Rate</th>
                                                                <th>Discount</th>
                                                                <th>Return Qty</th>
                                                                <th>Amount</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if(is_array($items))
                                                                @foreach($items as $index => $item)
                                                                    @php
                                                                        $rates = json_decode($purchase->rate ?? '[]', true) ?: [];
                                                                        $discounts = json_decode($purchase->discount ?? '[]', true) ?: [];
                                                                        $amounts = json_decode($purchase->return_amount ?? '[]', true) ?: [];
                                                                    @endphp
                                                                    <tr>
                                                                        <td>{{ $item }}</td>
                                                                        <td>Rs. {{ $rates[$index] ?? 0 }}</td>
                                                                        <td>Rs. {{ $discounts[$index] ?? 0 }}</td>
                                                                        <td>{{ $returnQtys[$index] ?? 0 }}</td>
                                                                        <td>Rs. {{ number_format($amounts[$index] ?? 0, 2) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-danger fs-6">Rs. {{ number_format($purchase->total_return_amount ?? 0) }}</span>
                                        </td>
                                        <td>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($purchase->created_at)->format('d M Y h:i A') }}</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">No purchase returns found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')

<style>
    .avatar {
        font-weight: 600;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(220, 53, 69, 0.05);
    }
    .card {
        border: none;
        border-radius: 10px;
    }
</style>
