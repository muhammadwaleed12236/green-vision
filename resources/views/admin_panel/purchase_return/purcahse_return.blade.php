@include('admin_panel.include.header_include')
<style>
    .info-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .info-card h5 {
        margin: 0;
        font-weight: 600;
    }
    .info-card p {
        margin: 5px 0 0 0;
        font-size: 14px;
        opacity: 0.9;
    }
    table input {
        font-size: 14px;
        padding: 8px 10px;
    }
    .table thead th {
        background-color: #f1f4f6;
        font-weight: 600;
        color: #2c3e50;
        border-bottom: 2px solid #dee2e6;
    }
    .return-qty {
        background-color: #fff3cd;
        font-weight: 600;
    }
    .return-amount {
        background-color: #d1ecf1;
        font-weight: 600;
        color: #0c5460;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Purchase Return</h4>
                    <h6>Return items from purchase invoice</h6>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <form action="{{ route('purchase.return.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                        <!-- Info Card -->
                        <div class="row">
                            <div class="col-md-12">
                                <div class="info-card">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <h5>{{ $purchase->invoice_number }}</h5>
                                            <p>Invoice Number</p>
                                        </div>
                                        <div class="col-md-3">
                                            <h5>{{ $purchase->purchase_date }}</h5>
                                            <p>Purchase Date</p>
                                        </div>
                                        <div class="col-md-4">
                                            <h5>{{ $purchase->vendor->Party_name ?? 'N/A' }}</h5>
                                            <p>Vendor Name</p>
                                        </div>
                                        <div class="col-md-2">
                                            <h5>Rs. {{ number_format($purchase->grand_total, 0) }}</h5>
                                            <p>Grand Total</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="party_name" value="{{ $purchase->party_name }}">

                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Return Date <span class="text-danger">*</span></label>
                                <input type="date" name="return_date" value="{{ date('Y-m-d') }}" class="form-control" required>
                            </div>
                        </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead>
                                <tr>
                                    <th style="width: 35%">Item Name</th>
                                    <th>Rate</th>
                                    <th>Discount</th>
                                    <th>Purchased Qty</th>
                                    <th>Return Qty</th>
                                    <th>Return Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(is_array($purchase->item))
                                    @foreach($purchase->item as $index => $item)
                                    <tr>
                                        <td>
                                            <input type="text" name="item[]" class="form-control-plaintext fw-bold" value="{{ $item }}" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="rate[]" class="form-control-plaintext text-center rate" value="{{ $purchase->rate[$index] ?? 0 }}" readonly>
                                        </td>
                                        <td>
                                            <input type="number" name="discount[]" class="form-control-plaintext text-center discount" value="{{ $purchase->discount[$index] ?? 0 }}" readonly>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info purchased-qty" data-max="{{ $purchase->pcs[$index] ?? 0 }}">{{ $purchase->pcs[$index] ?? 0 }}</span>
                                        </td>
                                        <td>
                                            <input type="number"
                                                name="return_qty[]"
                                                class="form-control return-qty text-center"
                                                data-index="{{ $index }}"
                                                data-max="{{ $purchase->pcs[$index] ?? 0 }}"
                                                step="0.01"
                                                min="0"
                                                max="{{ $purchase->pcs[$index] ?? 0 }}"
                                                placeholder="0">
                                            <small class="text-danger error-msg d-none" id="error-{{ $index }}">Max: {{ $purchase->pcs[$index] ?? 0 }}</small>
                                        </td>
                                        <td>
                                            <input type="text" name="return_amount[]"
                                                class="form-control return-amount text-center"
                                                id="return-amount-{{ $index }}"
                                                readonly>
                                        </td>
                                    </tr>
                                    @endforeach
                                @endif
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end">Total Return Amount:</th>
                                    <th class="text-center">
                                        <input type="text" id="grand_total" class="form-control fw-bold text-danger text-center" readonly>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('all-Purchases') }}" class="btn btn-secondary px-4">
                            <i data-feather="arrow-left" class="me-1"></i> Back to Purchases
                        </a>
                        <button type="submit" class="btn btn-danger px-5 shadow">
                            <i data-feather="rotate-ccw" class="me-1"></i> Submit Return
                        </button>
                    </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</div>

@include('admin_panel.include.footer_include')

<script>
    document.addEventListener('input', function (e) {
        if (e.target.classList.contains('return-qty')) {
            let input = e.target;
            let index = input.dataset.index;
            let maxQty = parseFloat(input.dataset.max) || 0;
            let row = input.closest('tr');
            let errorMsg = document.getElementById(`error-${index}`);

            let rate = parseFloat(row.querySelector('.rate').value) || 0;
            let discount = parseFloat(row.querySelector('.discount').value) || 0;
            let purchasedQty = parseFloat(row.querySelector('.purchased-qty').dataset.max) || 1;
            let returnQty = parseFloat(input.value) || 0;

            // Validation: Check if return qty exceeds purchased qty
            if (returnQty > maxQty) {
                input.classList.add('is-invalid');
                errorMsg.classList.remove('d-none');
                returnQty = maxQty;
                input.value = maxQty;
            } else {
                input.classList.remove('is-invalid');
                errorMsg.classList.add('d-none');
            }

            // Calculate: (rate × purchased_qty - discount) / purchased_qty × return_qty
            let grossTotal = rate * purchasedQty;
            let netTotal = grossTotal - discount;
            let effectiveRate = purchasedQty > 0 ? netTotal / purchasedQty : rate;
            let returnAmount = returnQty * effectiveRate;

            document.getElementById(`return-amount-${index}`).value = returnAmount.toFixed(2);

            calculateGrandTotal();
        }
    });

    function calculateGrandTotal() {
        let total = 0;
        document.querySelectorAll('.return-amount').forEach(el => {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('grand_total').value = 'Rs. ' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
    }
</script>
