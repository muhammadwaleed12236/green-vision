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
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <!-- Welcome Header -->
            <div class="welcome-header d-flex align-items-center justify-content-between">
                <div>
                    <h3>Welcome Back, {{ Auth::user()->name }}! 👋</h3>
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
                    <div class="dash-widget bg-rose">
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
                    <div class="dash-widget bg-amber">
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
                    <div class="dash-widget bg-emerald">
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
                    <div class="dash-widget bg-indigo">
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
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card">
                        <div class="stat-mini-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                            <i data-feather="tool"></i>
                        </div>
                        <div>
                            <p class="text-muted small mb-0">Contractor Costs</p>
                            <h4 class="mb-0 fw-bold"><span class="amount-text" data-amount="{{ $stats['totalContractorCosts'] }}">{{ number_format($stats['totalContractorCosts'], 0) }}</span></h4>

                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="stat-mini-card">
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
                    <div class="stat-mini-card shadow-sm border-0">
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
                    <div class="stat-mini-card">
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
                    <div class="stat-mini-card">
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
                    <div class="stat-mini-card">
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
                    <div class="stat-mini-card">
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
                    <div class="stat-mini-card">
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
                            <h5 class="card-title mb-0">Sales & Purchase Trend</h5>
                            <div class="graph-sets">
                                <ul>
                                    <li><span>Sales</span></li>
                                    <li><span>Purchase</span></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <canvas id="salesPurchaseChart" height="300"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Payment Status Donut Chart -->
                <div class="col-lg-5 col-sm-12 col-12 d-flex">
                    <div class="card flex-fill">
                        <div class="card-header pb-0">
                            <h5 class="card-title mb-0">Payment Status</h5>
                        </div>
                        <div class="card-body">
                            <canvas id="paymentStatusChart" height="300"></canvas>
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
            label: 'Sales',
            data: salesData,
            borderColor: '#1c9262',
            backgroundColor: 'rgba(28, 146, 98, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#1c9262',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
        }, {
            label: 'Purchase',
            data: purchasesData,
            borderColor: '#f59e0b',
            backgroundColor: 'rgba(245, 158, 11, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#f59e0b',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
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

// Payment Status Donut Chart
const paymentStatusCtx = document.getElementById('paymentStatusChart').getContext('2d');
const paymentStatus = @json($stats['paymentStatus']);

new Chart(paymentStatusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Paid', 'Unpaid', 'Pending'],
        datasets: [{
            data: [
                paymentStatus.paid,
                paymentStatus.unpaid,
                paymentStatus.pending
            ],
            backgroundColor: [
                '#10b981',  // Emerald for Paid
                '#ef4444',  // Rose for Unpaid
                '#64748b'   // Slate for Pending
            ],
            borderWidth: 0,
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.parsed || 0;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = ((value / total) * 100).toFixed(1);
                        return label + ': ' + value + ' (' + percentage + '%)';
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

// Top Products Bar Chart
const topProductsCtx = document.getElementById('topProductsChart').getContext('2d');
const topProducts = @json($stats['topProducts']);

new Chart(topProductsCtx, {
    type: 'bar',
    data: {
        labels: topProducts.map(item => item.item_name),
        datasets: [{
            label: 'Revenue',
            data: topProducts.map(item => item.total_revenue),
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
