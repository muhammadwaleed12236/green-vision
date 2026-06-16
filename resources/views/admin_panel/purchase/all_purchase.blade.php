@include('admin_panel.include.header_include')
<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <!-- Quick View Modal -->
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="quickViewModalLabel">
                        <i class="fa fa-eye me-2"></i>Purchase Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <!-- Header Info -->
                    <div class="bg-light p-3 border-bottom">
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Invoice Number</small>
                                <h6 class="mb-0 fw-bold" id="modal-invoice"></h6>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Purchase Date</small>
                                <h6 class="mb-0" id="modal-date"></h6>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Party Name</small>
                                <h6 class="mb-0" id="modal-party"></h6>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Table -->
                    <div class="p-3">
                        <h6 class="fw-bold mb-3"><i class="fa fa-list me-2"></i>Items</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Item Name</th>
                                        <th class="text-end">Rate</th>
                                        <th class="text-end">Pcs/Feet</th>
                                        <th class="text-end">Amount</th>
                                    </tr>
                                </thead>
                                <tbody id="modal-items-body">
                                    <!-- Items will be injected here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Footer Summary -->
                    <div class="bg-success text-white p-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <span class="badge bg-light text-success" id="modal-status"></span>
                            </div>
                            <div class="col-md-6 text-end">
                                <h5 class="mb-0">Grand Total: <span class="fw-bold" id="modal-total"></span></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="#" class="btn btn-primary" id="modal-invoice-btn">
                        <i class="fa fa-file-invoice me-1"></i>View Invoice
                    </a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Modal -->
    <div class="modal fade" id="payModal" tabindex="-1" aria-labelledby="payModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalLabel">Make Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('vendors-payment') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="purchase_id" id="purchase_id">
                        <input type="hidden" name="vendor_id" id="vendor_id">
                        <div class="mb-3">
                            <label class="form-label">Invoice Number</label>
                            <input type="text" class="form-control" id="Invoice_Number" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Invoice Grand Total</label>
                            <input type="text" class="form-control" id="pay_grand_total" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Remaining Amount</label>
                            <input type="text" class="form-control" id="remaining_amount" disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Amount Paid</label>
                            <input type="number" class="form-control" name="amount_paid" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date</label>
                            <input type="date" class="form-control" name="payment_date" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Make Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center mb-4">
                <div class="page-title">
                    <h4><i class="fa fa-shopping-cart me-2"></i>All Purchases</h4>
                    <h6 class="text-muted">View and manage all purchase records</h6>
                </div>
                <a href="{{ route('Purchase') }}" class="btn btn-primary">
                    <i class="fa fa-plus me-1"></i>Add New Purchase
                </a>
            </div>

            @if (session()->has('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fa fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fa fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover datanew" id="purchaseTable">
                            <thead>
                                <tr>
                                    <th style="width: 120px;">Invoice #</th>
                                    <th style="width: 110px;">Date</th>
                                    <th>Party</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-end" style="width: 120px;">Total</th>
                                    <th style="width: 100px;">Status</th>
                                    <th style="width: 180px;" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($Purchases as $purchase)
                                    @php
                                        $items = $purchase->item ?? [];
                                        $rates = $purchase->rate ?? [];
                                        $pcsArr = $purchase->pcs ?? [];
                                        $amounts = $purchase->amount ?? [];
                                        $itemCount = is_array($items) ? count($items) : 0;
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-bold text-primary">{{ $purchase->invoice_number }}</span>
                                        </td>
                                        <td>
                                            <span class="text-dark">{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm bg-primary text-white rounded-circle me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; font-size: 12px;">
                                                    {{ strtoupper(substr($purchase->vendor?->Party_name ?? 'N', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <span class="fw-semibold">{{ $purchase->vendor?->Party_name ?? 'N/A' }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info text-white">{{ $itemCount }} Items</span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-success fs-6">Rs. {{ number_format($purchase->grand_total) }}</span>
                                        </td>
                                        <td>
                                            @if($purchase->return_status == 1)
                                                <span class="badge bg-danger">Returned</span>
                                            @else
                                                <span class="badge bg-success">Completed</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <button type="button" 
                                                    class="btn btn-info quick-view-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#quickViewModal"
                                                    data-invoice="{{ $purchase->invoice_number }}"
                                                    data-date="{{ \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') }}"
                                                    data-party="{{ $purchase->vendor?->Party_name ?? 'N/A' }}"
                                                    data-items='@json($items)'
                                                    data-rates='@json($rates)'
                                                    data-pcs='@json($pcsArr)'
                                                    data-amounts='@json($amounts)'
                                                    data-total="{{ number_format($purchase->grand_total) }}"
                                                    data-status="{{ $purchase->return_status == 1 ? 'Returned' : 'Completed' }}"
                                                    data-invoice-url="{{ route('purchase.invoice', $purchase->id) }}"
                                                    title="Quick View">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                <a href="{{ route('purchase.invoice', $purchase->id) }}" class="btn btn-primary" title="Invoice">
                                                    <i class="fa fa-file-invoice"></i>
                                                </a>
                                                <a href="{{ route('purchase.edit', $purchase->id) }}" class="btn btn-warning" title="Edit">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                @if($purchase->return_status != 1)
                                                    <a href="{{ route('purchase.return.form', $purchase->id) }}" class="btn btn-danger" title="Return">
                                                        <i class="fa fa-undo"></i>
                                                    </a>
                                                @endif
                                                <form action="{{ route('purchase.delete', $purchase->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this purchase? This will reverse stock and ledger entries.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger" title="Delete">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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
@include('admin_panel.include.footer_include')

<style>
    .avatar {
        font-weight: 600;
    }
    .table-hover tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }
    .btn-group-sm .btn {
        padding: 0.25rem 0.5rem;
    }
    .card {
        border: none;
        border-radius: 10px;
    }
    .badge {
        font-weight: 500;
        padding: 0.4em 0.8em;
    }
    #quickViewModal .modal-content {
        border: none;
        border-radius: 15px;
        overflow: hidden;
    }
    #quickViewModal .table th {
        font-size: 12px;
    }
    #quickViewModal .table td {
        font-size: 13px;
    }
</style>

<script>
    $(document).ready(function () {
        // Quick View Modal
        $(document).on('click', '.quick-view-btn', function () {
            let invoice = $(this).data('invoice');
            let date = $(this).data('date');
            let party = $(this).data('party');
            let items = $(this).data('items') || [];
            let rates = $(this).data('rates') || [];
            let pcs = $(this).data('pcs') || [];
            let amounts = $(this).data('amounts') || [];
            let total = $(this).data('total');
            let status = $(this).data('status');
            let invoiceUrl = $(this).data('invoice-url');

            // Set header info
            $('#modal-invoice').text(invoice);
            $('#modal-date').text(date);
            $('#modal-party').text(party);
            $('#modal-total').text('Rs. ' + total);
            $('#modal-status').text(status);
            $('#modal-invoice-btn').attr('href', invoiceUrl);

            // Build items table
            let tbody = $('#modal-items-body');
            tbody.empty();

            if (Array.isArray(items) && items.length > 0) {
                items.forEach((item, index) => {
                    let rate = rates[index] || 0;
                    let pc = pcs[index] || 0;
                    let amount = amounts[index] || 0;
                    
                    tbody.append(`
                        <tr>
                            <td>${index + 1}</td>
                            <td>${item}</td>
                            <td class="text-end">Rs. ${Number(rate).toLocaleString()}</td>
                            <td class="text-end">${pc}</td>
                            <td class="text-end fw-bold">Rs. ${Number(amount).toLocaleString()}</td>
                        </tr>
                    `);
                });
            } else {
                tbody.append('<tr><td colspan="5" class="text-center text-muted">No items found</td></tr>');
            }
        });

        // Payment Modal
        $(document).on("click", ".pay-btn", function () {
            let purchaseId = $(this).data("id");
            let invoiceno = $(this).data("invoice-no");
            let vendorId = $(this).data("vendor-id");
            let grandTotal = $(this).data("grand-total");
            let remainingAmount = $(this).data("remaining-amount");

            $("#purchase_id").val(purchaseId);
            $("#Invoice_Number").val(invoiceno);
            $("#vendor_id").val(vendorId);
            $("#pay_grand_total").val(grandTotal);
            $("#remaining_amount").val(remainingAmount);
        });
    });
</script>