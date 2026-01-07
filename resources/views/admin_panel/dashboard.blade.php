@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <!-- Stats Cards Row 1 -->
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash1.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5><span class="amount-text" data-amount="{{ $stats['totalPurchaseDue'] }}">{{ number_format($stats['totalPurchaseDue'], 2) }}</span></h5>
                            <h6>Total Purchase Due</h6>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash1">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash2.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5><span class="amount-text" data-amount="{{ $stats['totallocal_salesDue'] }}">{{ number_format($stats['totallocal_salesDue'], 2) }}</span></h5>
                            <h6>Total Sales Due</h6>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash2">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash3.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5><span class="amount-text" data-amount="{{ $stats['totalSaleAmount'] }}">{{ number_format($stats['totalSaleAmount'], 2) }}</span></h5>
                            <h6>Total Sale Amount</h6>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget dash3">
                        <div class="dash-widgetimg">
                            <span><img src="{{ asset('assets/img/icons/dash4.svg') }}" alt="img"></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5><span class="amount-text" data-amount="{{ $stats['totalPurchaseAmount'] }}">{{ number_format($stats['totalPurchaseAmount'], 2) }}</span></h5>
                            <h6>Total Purchase Amount</h6>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row 2 -->
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="dash-widgetimg">
                            <span><i data-feather="dollar-sign" style="color: white;"></i></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5 style="color: white;"><span class="amount-text" data-amount="{{ $stats['totalExpenses'] }}">{{ number_format($stats['totalExpenses'], 2) }}</span></h5>
                            <h6 style="color: white;">Total Expenses</h6>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12">
                    <div class="dash-widget" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <div class="dash-widgetimg">
                            <span><i data-feather="trending-up" style="color: white;"></i></span>
                        </div>
                        <div class="dash-widgetcontent">
                            <h5 style="color: white;"><span class="amount-text" data-amount="{{ $stats['netProfit'] }}">{{ number_format($stats['netProfit'], 2) }}</span></h5>
                            <h6 style="color: white;">Net Profit</h6>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count">
                        <div class="dash-counts">
                            <h4>{{ $stats['productsCount'] }}</h4>
                            <h5>Products</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="package"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das1">
                        <div class="dash-counts">
                            <h4>{{ $stats['staffCount'] }}</h4>
                            <h5>Staff Members</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="users"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Count Cards Row -->
            <div class="row">
                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count">
                        <div class="dash-counts">
                            <h4>{{ $stats['customersCount'] }}</h4>
                            <h5>Customers</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="user"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das1">
                        <div class="dash-counts">
                            <h4>{{ $stats['vendorsCount'] }}</h4>
                            <h5>Suppliers</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="user-check"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das2">
                        <div class="dash-counts">
                            <h4>{{ $stats['purchaseInvoiceCount'] }}</h4>
                            <h5>Purchase Invoices</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="file-text"></i>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6 col-12 d-flex">
                    <div class="dash-count das3">
                        <div class="dash-counts">
                            <h4>{{ $stats['local_salesInvoiceCount'] }}</h4>
                            <h5>Sales Invoices</h5>
                        </div>
                        <div class="dash-imgs">
                            <i data-feather="file"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
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
                                            <th>Net Amount</th>
                                            <th>Grand Total</th>
                                            <th>Due Amount</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($stats['recentlocal_sales'] as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_number }}</td>
                                            <td>{{ $sale->customer_name }}</td>
                                            <td>{{ date('d M Y', strtotime($sale->created_at)) }}</td>
                                            <td class="amount-text" data-amount="{{ $sale->net_amount }}">{{ number_format($sale->net_amount, 2) }}</td>
                                            <td class="amount-text" data-amount="{{ $sale->grand_total }}">{{ number_format($sale->grand_total, 2) }}</td>
                                            <td class="amount-text" data-amount="{{ $sale->grand_total - $sale->net_amount }}">{{ number_format($sale->grand_total - $sale->net_amount, 2) }}</td>
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
// Format numbers (1000 = 1k, 1000000 = 1M)
function formatAmount(amount) {
    if (amount >= 1000000) {
        return (amount / 1000000).toFixed(1) + 'M';
    } else if (amount >= 1000) {
        return (amount / 1000).toFixed(1) + 'k';
    }
    return amount.toFixed(2);
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
            borderColor: '#28a745',
            backgroundColor: 'rgba(40, 167, 69, 0.1)',
            tension: 0.4,
            fill: true
        }, {
            label: 'Purchase',
            data: purchasesData,
            borderColor: '#ff9f43',
            backgroundColor: 'rgba(255, 159, 67, 0.1)',
            tension: 0.4,
            fill: true
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
                '#28a745',  // Green for Paid
                '#dc3545',  // Red for Unpaid
                '#6c757d'   // Gray for Pending
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
            backgroundColor: 'rgba(54, 162, 235, 0.8)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
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