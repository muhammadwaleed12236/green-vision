@include('admin_panel.include.header_include')

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1c9262 0%, #157a52 100%);
        --secondary-gradient: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        --info-gradient: linear-gradient(135deg, #22a06b 0%, #1c9262 100%);
        --purple-gradient: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .page-wrapper {
        background-color: #ffffff;
    }

    .dash-widget {
        border: none !important;
        border-radius: 20px !important;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        padding: 24px !important;
        margin-bottom: 24px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }

    .dash-widget:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04) !important;
    }

    .dash-widget-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 16px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
    }

    .dash-widget-icon i {
        font-size: 24px;
        color: white;
    }

    .dash-widget-info h5 {
        font-size: 0.875rem;
        font-weight: 600;
        color: rgba(255, 255, 255, 0.9);
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.025em;
    }

    .dash-widget-info h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: white;
        margin: 0;
    }

    .bg-indigo { background: var(--primary-gradient); }
    .bg-emerald { background: var(--secondary-gradient); }
    .bg-amber { background: var(--warning-gradient); }
    .bg-rose { background: var(--danger-gradient); }
    .bg-sky { background: var(--info-gradient); }
    .bg-violet { background: var(--purple-gradient); }

    .card {
        border: none !important;
        border-radius: 24px !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05) !important;
        background: var(--glass-bg);
        backdrop-filter: blur(12px);
    }

    .card-header {
        background: transparent !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 20px 24px !important;
    }

    .card-title {
        font-weight: 700;
        color: #1a1a2e;
        font-size: 1.125rem !important;
    }

    .table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        border-bottom: 1px solid #f1f5f9;
        padding: 16px 24px;
    }

    .table tbody td {
        padding: 16px 24px;
        color: #334155;
        vertical-align: middle;
    }

    .badges {
        padding: 6px 12px !important;
        border-radius: 9999px !important;
        font-weight: 600 !important;
        font-size: 0.75rem !important;
    }

    .welcome-header {
        margin-bottom: 32px;
    }

    .welcome-header h3 {
        font-weight: 800;
        color: #1a1a2e;
        margin-bottom: 8px;
    }

    .welcome-header p {
        color: #64748b;
        font-size: 1rem;
    }

    .stat-mini-card {
        padding: 20px;
        border-radius: 20px;
        background: white;
        border: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 16px;
        margin-bottom: 24px;
        transition: all 0.2s;
    }

    .stat-mini-card:hover {
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }

    .stat-mini-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .text-amount {
        font-family: 'Inter', sans-serif;
    }

    .dash-widget[onclick], .stat-mini-card[onclick], canvas {
        cursor: pointer;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <!-- Welcome Header -->
            <div class="welcome-header d-flex align-items-center justify-content-between">
                <div>
                    <h3>Welcome Back!</h3>
                    <p>Here's what's happening with your business today.</p>
                </div>
                <div class="d-none d-md-block">
                    <span class="badge bg-white shadow-sm text-dark p-3 rounded-pill">
                        <i data-feather="calendar" class="me-2"></i>
                        {{ date('l, d M Y') }}
                    </span>
                </div>
            </div>

            <!-- Stats Cards Row 1 -->
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget bg-rose" onclick="window.location='{{ route('vendors-ledger') }}'" title="View Vendor Ledger">
                        <div class="dash-widget-icon">
                            <i data-feather="shopping-bag"></i>
                        </div>
                        <div class="dash-widget-info">
                            <h5>Total Purchase Due</h5>
                            <h2><span class="amount-text" data-amount="{{ $stats['totalPurchaseDue'] }}">{{ number_format($stats['totalPurchaseDue'], 0) }}</span></h2>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget bg-amber" onclick="window.location='{{ route('customer-ledger') }}'" title="View Customer Ledger">
                        <div class="dash-widget-icon">
                            <i data-feather="arrow-down-circle"></i>
                        </div>
                        <div class="dash-widget-info">
                            <h5>Total Sales Due</h5>
                            <h2><span class="amount-text" data-amount="{{ $stats['totalSalesDue'] }}">{{ number_format($stats['totalSalesDue'], 0) }}</span></h2>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget bg-emerald" onclick="window.location='{{ route('all-local-sale') }}'" title="View All Sales">
                        <div class="dash-widget-icon">
                            <i data-feather="trending-up"></i>
                        </div>
                        <div class="dash-widget-info">
                            <h5>Gross Sales (Revenue)</h5>
                            <h2><span class="amount-text" data-amount="{{ $stats['totalSaleAmount'] }}">{{ number_format($stats['totalSaleAmount'], 0) }}</span></h2>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget bg-indigo" onclick="window.location='{{ route('all-Purchases') }}'" title="View All Purchases">
                        <div class="dash-widget-icon">
                            <i data-feather="database"></i>
                        </div>
                        <div class="dash-widget-info">
                            <h5>Stock Investment</h5>
                            <h2><span class="amount-text" data-amount="{{ $stats['totalStockInvestment'] }}">{{ number_format($stats['totalStockInvestment'], 0) }}</span></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Mini Cards Row -->
            <div class="row">
                {{--
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('job-orders.index') }}'" title="View Job Orders">
                        <div class="stat-mini-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i data-feather="tool"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Contractor Costs</p>
                            <h4 class="mb-0 fw-bold"><span class="amount-text" data-amount="{{ $stats['totalContractorCosts'] }}">{{ number_format($stats['totalContractorCosts'], 0) }}</span></h4>

                        </div>
                    </div>
                </div>
                --}}

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('add-expenses') }}'" title="View Expenses">
                        <div class="stat-mini-icon" style="background: rgba(14, 165, 233, 0.1); color: #0ea5e9;">
                            <i data-feather="dollar-sign"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Other Expenses</p>
                            <h4 class="mb-0 fw-bold"><span class="amount-text" data-amount="{{ $stats['totalExpenses'] }}">{{ number_format($stats['totalExpenses'], 0) }}</span></h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card shadow-sm border-0" onclick="window.location='{{ route('all-local-sale') }}'" title="View Sales Report">
                        <div class="stat-mini-icon" style="background: rgba(168, 85, 247, 0.1); color: #a855f7;">
                            <i data-feather="activity"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Est. Operating Profit</p>
                            <h4 class="mb-0 fw-bold @if($stats['netProfit'] < 0) text-danger @else text-success @endif">
                                <span class="amount-text" data-amount="{{ $stats['netProfit'] }}">{{ number_format($stats['netProfit'], 0) }}</span>
                            </h4>
                            <div class="text-muted" style="font-size: 10px; line-height: 1.2;">
                                Formula: Sales - Work Costs - Exp
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('customer') }}'" title="View Customers">
                        <div class="stat-mini-icon" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i data-feather="users"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Total Customers</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['customersCount'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Mini Cards Row 2 (Counts) -->
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('vendors') }}'" title="View Suppliers">
                        <div class="stat-mini-icon" style="background: rgba(244, 63, 94, 0.1); color: #f43f5e;">
                            <i data-feather="truck"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Total Suppliers</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['vendorsCount'] }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('salesmen') }}'" title="View Staff">
                        <div class="stat-mini-icon" style="background: rgba(99, 102, 241, 0.1); color: #6366f1;">
                            <i data-feather="users"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Staff members</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['staffCount'] }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('product') }}'" title="View Products">
                        <div class="stat-mini-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                            <i data-feather="package"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Products</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['productsCount'] }}</h4>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card" onclick="window.location='{{ route('all-local-sale') }}'" title="View Sales Invoices">
                        <div class="stat-mini-icon" style="background: rgba(6, 182, 212, 0.1); color: #06b6d4;">
                            <i data-feather="file-text"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Sales Invoices</p>
                            <h4 class="mb-0 fw-bold">{{ $stats['local_salesInvoiceCount'] }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <!-- Sales & Purchase Chart -->
                <div class="col-lg-7 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0" style="border-left: 4px solid #0ea5e9; padding-left: 8px;">Revenue vs Expenses by Month</h5>
                            <div class="graph-sets">
                                <ul>
                                    <li><span>Revenue ($K)</span></li>
                                    <li><span>Expenses ($K)</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="salesPurchaseChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- For Every Dollar Made Donut Chart -->
                <div class="col-lg-5 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 text-center" style="background-color: #4a6b82 !important; border-top-left-radius: 24px; border-top-right-radius: 24px; padding: 16px !important; margin: 0; border-bottom: none;">
                            <h5 class="card-title mb-0 text-white" style="font-size: 1.1rem; font-weight: 600;">For Every Dollar Made</h5>
                        </div>
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <canvas id="everyDollarChart" height="250" style="max-height: 250px;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profit Breakdown & Chart Row -->
            <div class="row">
                <!-- Net Profit Breakdown -->
                <div class="col-lg-5 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 border-0 pt-4">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-feather="briefcase" class="text-success me-2"></i> Net Profit Breakdown
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4 mt-2">
                                <p class="text-muted text-uppercase fw-bold mb-1" style="letter-spacing: 1px; font-size: 12px;">NET PROFIT</p>
                                <h1 class="display-5 fw-bold {{ $stats['netProfit'] < 0 ? 'text-danger' : 'text-success' }} mb-2" style="font-size: 2.5rem;">
                                    Rs <span class="amount-text" data-amount="{{ $stats['netProfit'] }}">{{ number_format($stats['netProfit'], 0) }}</span>
                                </h1>
                                <p class="text-muted small">Net Revenue minus Cost of Goods Sold</p>
                            </div>
                            
                            @php
                                $totalCost = $stats['totalStockInvestment'] + $stats['totalJobCosts'] + $stats['totalExpenses'];
                                $revenue = $stats['totalSaleAmount'];
                                $maxVal = max($revenue, $totalCost);
                                $revenuePercent = $maxVal > 0 ? ($revenue / $maxVal) * 100 : 0;
                                $costPercent = $maxVal > 0 ? ($totalCost / $maxVal) * 100 : 0;
                            @endphp

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted fw-bold" style="font-size: 13px;">Net Revenue</span>
                                    <span class="fw-bold" style="font-size: 13px;">Rs <span class="amount-text" data-amount="{{ $revenue }}">{{ number_format($revenue, 0) }}</span></span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: {{ $revenuePercent }}%;" aria-valuenow="{{ $revenuePercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted fw-bold" style="font-size: 13px;">Cost of Goods Sold (COGS)</span>
                                    <span class="fw-bold" style="font-size: 13px;">Rs <span class="amount-text" data-amount="{{ $totalCost }}">{{ number_format($totalCost, 0) }}</span></span>
                                </div>
                                <div class="progress" style="height: 6px; border-radius: 10px;">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $costPercent }}%;" aria-valuenow="{{ $costPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Profit Chart -->
                <div class="col-lg-7 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0 border-0 pt-4">
                            <h5 class="card-title mb-0 d-flex align-items-center">
                                <i data-feather="bar-chart-2" class="text-primary me-2"></i> Revenue vs Cost vs Net Profit
                            </h5>
                            <p class="text-muted small mb-0 mt-1">Financial performance overview</p>
                        </div>
                        <div class="card-body">
                            <canvas id="profitBarChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Second Chart Row -->
            <div class="row">
                <!-- Category Sales Pie Chart -->
                <div class="col-lg-5 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">Sales by Category</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="categorySalesChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Top Products Bar Chart -->
                <div class="col-lg-7 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">Top 5 Selling Products</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="topProductsChart" height="300"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Sales Table -->
            <div class="row">
                <div class="col-lg-12 col-sm-12 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title">Recent Sales</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table datanew">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['recentlocal_sales'] as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_number }}</td>
                                            <td>{{ $sale->customer_name }}</td>
                                            <td>{{ date('d M Y', strtotime($sale->created_at)) }}</td>
                                            <td class="amount-text" data-amount="{{ $sale->grand_total }}">{{ number_format($sale->grand_total, 0) }}</td>
                                            <td>
                                                 @if($sale->job_status == 'paid')
                                                    <span class="badges bg-lightgreen">Paid</span>
                                                @elseif($sale->job_status == 'pending')
                                                    <span class="badges bg-secondary">Pending</span>
                                                @else
                                                    <span class="badges bg-lightred">Unpaid</span>
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
    </div>
</div>

@include('admin_panel.include.footer_include')

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Format numbers (1000 = 1k, 1M, 1B)
function formatAmount(amount) {
    if (Math.abs(amount) >= 1000000000) {
        return (amount / 1000000000).toFixed(1) + 'B';
    } else if (Math.abs(amount) >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'M';
    } else if (Math.abs(amount) >= 1000) {
        return (amount / 1000).toFixed(1) + 'k';
    }
    return amount.toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 0});
}

// Format all amounts on page load
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.amount-text').forEach(function(element) {
        const amount = parseFloat(element.getAttribute('data-amount'));
        if (!isNaN(amount)) {
            element.textContent = formatAmount(amount);
        }
    });
});

// Sales & Purchase Line Chart
const salesPurchaseCtx = document.getElementById('salesPurchaseChart').getContext('2d');
const monthlySalesData = @json($stats['monthlylocal_sales']);
const monthlyPurchasesData = @json($stats['monthlyPurchases']);

const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const labels = monthlySalesData.map(item => months[item.month - 1] + ' ' + item.year);
const salesData = monthlySalesData.map(item => item.total);
const purchasesData = monthlyPurchasesData.map(item => item.total);

new Chart(salesPurchaseCtx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Revenue',
            data: salesData,
            borderColor: '#0ea5e9', // Blue
            backgroundColor: 'rgba(14, 165, 233, 0.15)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#0ea5e9',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
        }, {
            label: 'Expenses',
            data: purchasesData,
            borderColor: '#ef4444', // Red
            backgroundColor: 'rgba(239, 68, 68, 0.15)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#ef4444',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        onClick: function(e, activeEls) {
            if (activeEls.length > 0) {
                const datasetIndex = activeEls[0].datasetIndex;
                if (datasetIndex === 0) {
                    window.location.href = "{{ route('Date-wise-Sales-Report') }}";
                } else {
                    window.location.href = "{{ route('date-wise-purcahse-report') }}";
                }
            } else {
                window.location.href = "{{ route('Date-wise-Sales-Report') }}";
            }
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': ' + formatAmount(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return formatAmount(value);
                    }
                }
            }
        }
    }
});

// For Every Dollar Made Donut Chart
const everyDollarCtx = document.getElementById('everyDollarChart').getContext('2d');

let donutRev = {{ $stats['totalSaleAmount'] }};
let donutCost = {{ $stats['totalStockInvestment'] + $stats['totalJobCosts'] + $stats['totalExpenses'] }};
let donutNp = {{ $stats['netProfit'] }};

// Normalize to "For Every Dollar" (per 100 or actual)
// If NP is negative, it breaks the pie chart logic, so clamp to 0 for visual purposes.
let displayNp = donutNp > 0 ? donutNp : 0;
let displayCost = donutCost;

new Chart(everyDollarCtx, {
    type: 'doughnut',
    data: {
        labels: ['Cost', 'Net Profit (NP)'],
        datasets: [{
            data: [displayCost, displayNp],
            backgroundColor: [
                '#20b2aa',  // Teal for Cost
                '#d9534f'   // Red for NP
            ],
            borderWidth: 2,
            borderColor: '#ffffff',
            hoverOffset: 4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '60%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: {
                    usePointStyle: true,
                    padding: 20,
                    font: {
                        family: 'Inter, sans-serif'
                    }
                }
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        return label + ': Rs ' + formatAmount(value);
                    }
                }
            }
        }
    }
});

// Top Selling Items Pie Chart (Simple)
const topSellingItemsCtx = document.getElementById('categorySalesChart').getContext('2d');
const topSellingItems = @json($stats['topSellingItems']);

console.log('Top Items:', topSellingItems); // ✅ Debug

if (topSellingItems && topSellingItems.length > 0) {
    new Chart(topSellingItemsCtx, {
        type: 'pie',
        data: {
            labels: topSellingItems.map(item => item.item_name),
            datasets: [{
                data: topSellingItems.map(item => parseFloat(item.total_sales)),
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#FF9F40'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: function() {
                window.location.href = "{{ route('Product-wise-Sales-Report') }}";
            },
            plugins: {
                legend: {
                    position: 'bottom'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ' + formatAmount(context.parsed);
                        }
                    }
                }
            }
        }
    });
} else {
    document.getElementById('categorySalesChart').parentElement.innerHTML =
        '<p class="text-center text-muted mt-5">No sales data yet</p>';
}

// Profit Bar Chart
const profitBarCtx = document.getElementById('profitBarChart').getContext('2d');

const rev = {{ $stats['totalSaleAmount'] }};
const cost = {{ $stats['totalStockInvestment'] + $stats['totalJobCosts'] + $stats['totalExpenses'] }};
const netProfit = {{ $stats['netProfit'] }};

const values = [rev, cost, netProfit];
const maxVal = Math.max(...values, 0);
const minVal = Math.min(...values, 0);
const yMax = maxVal > 0 ? maxVal + (maxVal * 0.15) : 1000;
const yMin = minVal < 0 ? minVal - (Math.abs(minVal) * 0.15) : 0;

const profitDataLabelsPlugin = {
    id: 'profitDataLabels',
    afterDatasetsDraw(chart, args, options) {
        const { ctx } = chart;
        ctx.save();
        chart.data.datasets.forEach((dataset, i) => {
            const meta = chart.getDatasetMeta(i);
            meta.data.forEach((bar, index) => {
                const data = dataset.data[index];
                const valueText = 'Rs ' + formatAmount(data);
                
                ctx.fillStyle = '#334155';
                ctx.font = 'bold 13px Inter, sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                
                // Position above positive bars, below negative bars
                const yPos = data >= 0 ? bar.y - 12 : bar.y + 12;
                ctx.fillText(valueText, bar.x, yPos);
            });
        });
        ctx.restore();
    }
};

new Chart(profitBarCtx, {
    type: 'bar',
    data: {
        labels: ['Revenue', 'Cost', 'Net Profit'],
        datasets: [{
            label: 'Amount (Rs)',
            data: [rev, cost, netProfit],
            backgroundColor: [
                '#10b981', // green for Revenue
                '#ef4444', // red/orange for Cost
                netProfit < 0 ? '#ef4444' : '#10b981' // red if negative, green if positive
            ],
            borderRadius: 6,
            barPercentage: 0.6,
            categoryPercentage: 0.7
        }]
    },
    plugins: [profitDataLabelsPlugin],
    options: {
        responsive: true,
        maintainAspectRatio: false,
        layout: {
            padding: {
                top: 20,
                bottom: 20
            }
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return 'Rs ' + formatAmount(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                suggestedMax: yMax,
                suggestedMin: yMin,
                beginAtZero: true,
                border: {
                    display: false
                },
                grid: {
                    color: function(context) {
                        if (context.tick.value === 0) {
                            return '#94a3b8'; // stronger zero line
                        }
                        return 'rgba(226, 232, 240, 0.4)'; // very subtle gray
                    },
                    lineWidth: function(context) {
                        if (context.tick.value === 0) {
                            return 2;
                        }
                        return 1;
                    }
                },
                ticks: {
                    color: '#64748b',
                    font: {
                        family: 'Inter, sans-serif'
                    },
                    callback: function(value) {
                        return formatAmount(value);
                    }
                }
            },
            x: {
                border: {
                    display: false
                },
                grid: {
                    display: false
                },
                ticks: {
                    color: '#334155',
                    font: {
                        family: 'Inter, sans-serif',
                        weight: '600'
                    }
                }
            }
        }
    }
});

// Top Products Bar Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
const topProducts = @json($stats['topProducts']);

new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: topProducts.map(item => item.item_name),
        datasets: [{
            label: 'Revenue',
            data: topProducts.map(item => item.total_sales),
            backgroundColor: 'rgba(28, 146, 98, 0.8)',
            borderColor: '#1c9262',
            borderRadius: 8,
            borderWidth: 0
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        indexAxis: 'y',
        onClick: function() {
            window.location.href = "{{ route('Product-wise-Sales-Report') }}";
        },
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Revenue: ' + formatAmount(context.parsed.x);
                    }
                }
            }
        },
        scales: {
            x: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return formatAmount(value);
                    }
                }
            }
        }
    }
});
</script>