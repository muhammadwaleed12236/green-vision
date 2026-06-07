@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4><i class="fa fa-bell me-2"></i>Delivery Notifications</h4>
                    <h6>Track upcoming delivery dates and overdue orders</h6>
                </div>
            </div>

            <div class="row">
                <!-- Overdue Orders -->
                <div class="col-md-12 mb-4">
                    <div class="card border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0"><i class="fa fa-exclamation-triangle me-2"></i>Overdue Deliveries</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $overdueOrders = $orders->filter(function($sale) {
                                    $deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
                                    return \Carbon\Carbon::now()->greaterThan($deliveryDate);
                                });
                            @endphp
                            @if($overdueOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Customer</th>
                                                <th>Delivery Date</th>
                                                <th>Days Overdue</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($overdueOrders as $sale)
                                                @php
                                                    $deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
                                                    $daysOverdue = \Carbon\Carbon::now()->diffInDays($deliveryDate);
                                                    $customerName = $sale->customer ? $sale->customer->customer_name : ($sale->customer_shopname ?? 'Walk-in');
                                                @endphp
                                                <tr class="table-danger">
                                                    <td><strong>{{ $sale->invoice_number }}</strong></td>
                                                    <td>{{ $customerName }}</td>
                                                    <td>{{ $deliveryDate->format('d M Y') }}</td>
                                                    <td><span class="badge bg-danger">{{ $daysOverdue }} days</span></td>
                                                    <td>Rs. {{ number_format($sale->net_amount) }}</td>
                                                    <td><span class="badge bg-warning">{{ ucfirst($sale->job_status) }}</span></td>
                                                    <td>
                                                        <a href="{{ route('all-local-sale') }}" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fa fa-check-circle text-success" style="font-size: 48px;"></i>
                                    <p class="mt-3 text-muted">No overdue deliveries</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Upcoming Deliveries -->
                <div class="col-md-12">
                    <div class="card border-warning">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0"><i class="fa fa-clock me-2"></i>Upcoming Deliveries</h5>
                        </div>
                        <div class="card-body">
                            @php
                                $upcomingOrders = $orders->filter(function($sale) {
                                    $deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
                                    return \Carbon\Carbon::now()->lessThanOrEqualTo($deliveryDate);
                                })->sortBy('delivery_date');
                            @endphp
                            @if($upcomingOrders->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Invoice #</th>
                                                <th>Customer</th>
                                                <th>Delivery Date</th>
                                                <th>Days Left</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($upcomingOrders as $sale)
                                                @php
                                                    $deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
                                                    $daysLeft = \Carbon\Carbon::now()->diffInDays($deliveryDate);
                                                    $customerName = $sale->customer ? $sale->customer->customer_name : ($sale->customer_shopname ?? 'Walk-in');
                                                    $rowClass = $daysLeft == 0 ? 'table-warning' : ($daysLeft <= 2 ? 'table-info' : '');
                                                @endphp
                                                <tr class="{{ $rowClass }}">
                                                    <td><strong>{{ $sale->invoice_number }}</strong></td>
                                                    <td>{{ $customerName }}</td>
                                                    <td>{{ $deliveryDate->format('d M Y') }}</td>
                                                    <td>
                                                        @if($daysLeft == 0)
                                                            <span class="badge bg-warning text-dark"><i class="fa fa-exclamation"></i> Today</span>
                                                        @elseif($daysLeft == 1)
                                                            <span class="badge bg-info">Tomorrow</span>
                                                        @else
                                                            <span class="badge bg-primary">{{ $daysLeft }} days</span>
                                                        @endif
                                                    </td>
                                                    <td>Rs. {{ number_format($sale->net_amount) }}</td>
                                                    <td><span class="badge bg-secondary">{{ ucfirst($sale->job_status) }}</span></td>
                                                    <td>
                                                        <a href="{{ route('all-local-sale') }}" class="btn btn-sm btn-primary">
                                                            <i class="fa fa-eye"></i> View
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fa fa-calendar-check text-info" style="font-size: 48px;"></i>
                                    <p class="mt-3 text-muted">No upcoming deliveries scheduled</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<style>
    .card {
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0,123,255,0.05);
    }
</style>
