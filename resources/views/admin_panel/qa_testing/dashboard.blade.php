@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <!-- Page Header -->
            <div class="page-header">
                <div class="page-title">
                    <h4><i class="fas fa-bug me-2"></i>Quality Assurance Dashboard</h4>
                    <h6>System Testing & Monitoring</h6>
                </div>
                <div class="page-btn">
                    <button class="btn btn-success" onclick="runQuickHealthCheck()">
                        <i class="fas fa-heartbeat me-1"></i>Health Check
                    </button>
                </div>
            </div>

            <!-- Health Status Cards -->
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Stock Health</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @if($stockIssues['status'] == 'OK')
                                            <span class="text-success">✅ Good</span>
                                        @elseif($stockIssues['status'] == 'WARNING')
                                            <span class="text-warning">⚠️ Warning</span>
                                        @else
                                            <span class="text-danger">❌ Critical</span>
                                        @endif
                                    </div>
                                    <small>Negative: {{ $stockIssues['negative_stocks'] }} | Low: {{ $stockIssues['low_stocks'] }}</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Ledger Health</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @if($ledgerIssues['status'] == 'OK')
                                            <span class="text-success">✅ Good</span>
                                        @else
                                            <span class="text-danger">❌ Issues</span>
                                        @endif
                                    </div>
                                    <small>Unbalanced: {{ $ledgerIssues['unbalanced_ledgers'] }}</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Data Consistency</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">
                                        @if($dataConsistency['status'] == 'OK')
                                            <span class="text-success">✅ Good</span>
                                        @else
                                            <span class="text-warning">⚠️ Issues</span>
                                        @endif
                                    </div>
                                    <small>Inconsistencies: {{ $dataConsistency['inconsistencies'] }}</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-sync fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Recent Tests</div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800" id="recent-tests">
                                        <span class="text-info">{{ $recentPurchases->count() }}</span>
                                    </div>
                                    <small>Purchases to test</small>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-vial fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-rocket me-2"></i>Quick Test Actions</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <button class="btn btn-outline-primary btn-block mb-2" onclick="testAllRecentPurchases()">
                                <i class="fas fa-shopping-cart me-1"></i>Test All Recent Purchases
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-info btn-block mb-2" onclick="checkStockConsistency()">
                                <i class="fas fa-boxes me-1"></i>Check Stock Consistency
                            </button>
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-outline-success btn-block mb-2" onclick="validateLedgerBalances()">
                                <i class="fas fa-balance-scale me-1"></i>Validate Ledger Balances
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Purchases Testing -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between">
                    <h5><i class="fas fa-list me-2"></i>Recent Purchases - Testing</h5>
                    <span class="badge bg-info">{{ $recentPurchases->count() }} Items</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Purchase ID</th>
                                    <th>Date</th>
                                    <th>Vendor</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPurchases as $purchase)
                                <tr id="purchase-row-{{ $purchase->id }}">
                                    <td><strong>#{{ $purchase->id }}</strong></td>
                                    <td>{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d/m/Y') }}</td>
                                    <td>{{ $purchase->vendor->Party_name ?? 'N/A' }}</td>
                                    <td>{{ number_format($purchase->total_amount) }}</td>
                                    <td>
                                        <span class="badge bg-secondary" id="status-{{ $purchase->id }}">Not Tested</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" onclick="testPurchase({{ $purchase->id }})">
                                            <i class="fas fa-play me-1"></i>Test
                                        </button>
                                        <button class="btn btn-sm btn-info" onclick="viewDetails({{ $purchase->id }})">
                                            <i class="fas fa-eye me-1"></i>Details
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Test Results -->
            <div class="card" id="test-results-card" style="display: none;">
                <div class="card-header">
                    <h5><i class="fas fa-clipboard-check me-2"></i>Test Results</h5>
                </div>
                <div class="card-body" id="test-results-content">
                    <!-- Results will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
function runQuickHealthCheck() {
    $.ajax({
        url: '/qa/health-check',
        method: 'GET',
        beforeSend: function() {
            Swal.fire({
                title: 'Running Health Check...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading()
                },
            });
        },
        success: function(response) {
            Swal.close();

            let status = 'success';
            let message = 'System Health: Good ✅';

            if (response.stock_health.status === 'CRITICAL' || response.ledger_health.status === 'CRITICAL') {
                status = 'error';
                message = 'Critical Issues Found! ❌';
            } else if (response.stock_health.status === 'WARNING' || response.data_consistency.status === 'WARNING') {
                status = 'warning';
                message = 'Some Issues Found ⚠️';
            }

            Swal.fire({
                icon: status,
                title: 'Health Check Complete',
                html: `
                    <div class="text-left">
                        <p><strong>${message}</strong></p>
                        <hr>
                        <p>📦 Stock Issues: ${response.stock_health.negative_stocks} negative, ${response.stock_health.low_stocks} low</p>
                        <p>📑 Ledger Issues: ${response.ledger_health.unbalanced_ledgers} unbalanced</p>
                        <p>🔄 Data Inconsistencies: ${response.data_consistency.inconsistencies}</p>
                        <p>📊 Today's Purchases: ${response.recent_purchases}</p>
                    </div>
                `,
                confirmButtonText: 'OK'
            });
        },
        error: function() {
            Swal.fire('Error!', 'Failed to run health check', 'error');
        }
    });
}

function testPurchase(purchaseId) {
    $.ajax({
        url: `/qa/test-purchase/${purchaseId}`,
        method: 'GET',
        beforeSend: function() {
            $(`#status-${purchaseId}`).removeClass().addClass('badge bg-warning').text('Testing...');
        },
        success: function(response) {
            // Update status badge
            let statusClass = 'bg-success';
            let statusText = '✅ PASS';

            if (response.overall_status === 'FAIL') {
                statusClass = 'bg-danger';
                statusText = '❌ FAIL';
            } else if (response.overall_status === 'WARNING') {
                statusClass = 'bg-warning';
                statusText = '⚠️ WARNING';
            }

            $(`#status-${purchaseId}`).removeClass().addClass(`badge ${statusClass}`).text(statusText);

            // Show detailed results
            showTestResults(response);
        },
        error: function() {
            $(`#status-${purchaseId}`).removeClass().addClass('badge bg-danger').text('❌ ERROR');
            Swal.fire('Error!', 'Failed to test purchase', 'error');
        }
    });
}

function showTestResults(response) {
    let html = `
        <div class="row">
            <div class="col-md-12">
                <h6>Purchase ID: ${response.purchase_id} - Overall Status:
                    <span class="badge bg-${response.overall_status === 'PASS' ? 'success' : (response.overall_status === 'WARNING' ? 'warning' : 'danger')}">
                        ${response.overall_status}
                    </span>
                </h6>
                <hr>
            </div>
        </div>
        <div class="row">
    `;

    Object.keys(response.test_results).forEach(testName => {
        const result = response.test_results[testName];
        const statusIcon = result.status === 'PASS' ? '✅' : (result.status === 'WARNING' ? '⚠️' : '❌');
        const cardClass = result.status === 'PASS' ? 'border-success' : (result.status === 'WARNING' ? 'border-warning' : 'border-danger');

        html += `
            <div class="col-md-6 mb-3">
                <div class="card ${cardClass}">
                    <div class="card-header">
                        <h6>${statusIcon} ${testName.replace(/_/g, ' ').toUpperCase()}</h6>
                    </div>
                    <div class="card-body">
                        <p><strong>Status:</strong> ${result.status}</p>
                        <p><strong>Message:</strong> ${result.message}</p>
                        <details>
                            <summary>Details</summary>
                            <pre class="mt-2 small">${JSON.stringify(result.details, null, 2)}</pre>
                        </details>
                    </div>
                </div>
            </div>
        `;
    });

    html += '</div>';

    $('#test-results-content').html(html);
    $('#test-results-card').show();
}

function testAllRecentPurchases() {
    Swal.fire({
        title: 'Test All Recent Purchases?',
        text: 'This will test all purchases shown in the table.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Test All!'
    }).then((result) => {
        if (result.isConfirmed) {
            const purchaseIds = [];
            $('[onclick^="testPurchase"]').each(function() {
                const onclick = $(this).attr('onclick');
                const id = onclick.match(/testPurchase\((\d+)\)/)[1];
                purchaseIds.push(id);
            });

            // Test each purchase sequentially
            let index = 0;
            function testNext() {
                if (index < purchaseIds.length) {
                    testPurchase(purchaseIds[index]);
                    index++;
                    setTimeout(testNext, 1000); // 1 second delay between tests
                }
            }
            testNext();
        }
    });
}

function checkStockConsistency() {
    Swal.fire('Feature Coming Soon!', 'Stock consistency check will be implemented', 'info');
}

function validateLedgerBalances() {
    Swal.fire('Feature Coming Soon!', 'Ledger balance validation will be implemented', 'info');
}

function viewDetails(purchaseId) {
    window.open(`/purchase-details/${purchaseId}`, '_blank');
}
</script>

<style>
.border-left-info {
    border-left: .25rem solid #36b9cc!important;
}
.border-left-success {
    border-left: .25rem solid #1cc88a!important;
}
.border-left-warning {
    border-left: .25rem solid #f6c23e!important;
}
.border-left-primary {
    border-left: .25rem solid #4e73df!important;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15)!important;
}

.border-success {
    border-color: #1cc88a!important;
}
.border-warning {
    border-color: #f6c23e!important;
}
.border-danger {
    border-color: #e74a3b!important;
}

details summary {
    cursor: pointer;
    color: #007bff;
}

details pre {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 4px;
    max-height: 200px;
    overflow-y: auto;
}
</style>
