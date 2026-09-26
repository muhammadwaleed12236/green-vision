<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_number }} - {{ $appSettings['company_name'] ?? 'Receipt' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            padding: 24px 12px;
            font-size: 12px;
            line-height: 1.4;
            -webkit-font-smoothing: antialiased;
        }

        /* ----- ACTION TOOLBAR (SCREEN ONLY) ----- */
        .toolbar-container {
            max-width: 440px;
            margin: 0 auto 18px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 9px 15px;
            font-size: 12.5px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-print {
            background-color: #0f172a;
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.25);
        }
        .btn-print:hover {
            background-color: #1e293b;
            transform: translateY(-1px);
        }

        .btn-print kbd {
            background: rgba(255, 255, 255, 0.2);
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 10px;
            font-family: inherit;
        }

        .btn-invoice {
            background-color: #ffffff;
            color: #334155;
            border-color: #cbd5e1;
        }
        .btn-invoice:hover {
            background-color: #f8fafc;
            color: #0f172a;
            border-color: #94a3b8;
        }

        .btn-back {
            background-color: #64748b;
            color: #ffffff;
        }
        .btn-back:hover {
            background-color: #475569;
        }

        .btn-new {
            background-color: #16a34a;
            color: #ffffff;
        }
        .btn-new:hover {
            background-color: #15803d;
        }

        /* ----- THERMAL RECEIPT CONTAINER ----- */
        .receipt-wrapper {
            max-width: 80mm;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 16px 14px 18px 14px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.04);
            border-radius: 8px;
            color: #000000;
        }

        /* Number font formatting */
        .num-font {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* Header */
        .receipt-header {
            text-align: center;
            margin-bottom: 10px;
        }

        .receipt-logo {
            max-width: 110px;
            max-height: 50px;
            object-fit: contain;
            margin: 0 auto 6px auto;
            display: block;
            filter: grayscale(100%) contrast(150%);
        }

        .company-title {
            font-size: 15px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            margin-bottom: 3px;
            color: #000;
        }

        .company-meta {
            font-size: 10.5px;
            font-weight: 500;
            color: #222;
            line-height: 1.35;
        }

        .company-meta.phone {
            font-weight: 700;
            margin-top: 1px;
            font-size: 11px;
        }

        /* Receipt Badge */
        .receipt-type-badge {
            margin: 10px 0 8px 0;
            text-align: center;
            font-size: 11.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 4px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
        }

        /* Info meta rows */
        .info-grid {
            margin: 8px 0;
            font-size: 11.5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3.5px;
            gap: 6px;
        }

        .info-row .label {
            font-weight: 600;
            color: #222;
            white-space: nowrap;
        }

        .info-row .value {
            font-weight: 700;
            text-align: right;
            color: #000;
            word-break: break-word;
        }

        /* Dividers */
        .divider-dashed {
            border-top: 1px dashed #000;
            margin: 8px 0;
        }

        .divider-dotted {
            border-top: 1px dotted #555;
            margin: 6px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 8px 0;
        }

        .divider-double {
            border-top: 2.5px double #000;
            margin: 8px 0;
        }

        /* Items Section */
        .items-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 4px 0;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            margin-bottom: 6px;
        }

        .items-list {
            display: flex;
            flex-direction: column;
            gap: 7px;
        }

        .item-card {
            display: flex;
            flex-direction: column;
            gap: 2px;
            padding-bottom: 5px;
            border-bottom: 1px dotted #888;
        }

        .item-card:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .item-main-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 6px;
        }

        .item-name {
            font-weight: 700;
            font-size: 11.5px;
            color: #000;
            line-height: 1.3;
        }

        .item-amount {
            font-weight: 800;
            font-size: 12px;
            text-align: right;
            white-space: nowrap;
        }

        .item-sub-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 10.5px;
            color: #333;
            padding-left: 12px;
        }

        .item-calc {
            font-weight: 600;
        }

        .item-dim {
            font-size: 10px;
            color: #444;
            font-weight: 500;
            padding-left: 12px;
            margin-top: -1px;
        }

        /* Totals Block */
        .totals-block {
            margin: 8px 0;
            display: flex;
            flex-direction: column;
            gap: 3.5px;
            font-size: 11.5px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .total-row .label {
            font-weight: 600;
            color: #222;
        }

        .total-row .value {
            font-weight: 700;
            text-align: right;
        }

        .total-row.net-amount-row {
            font-size: 13.5px;
            font-weight: 900;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
            padding: 5px 0;
            margin: 4px 0;
        }

        .total-row.net-amount-row .label {
            font-weight: 900;
            letter-spacing: 0.5px;
        }

        .total-row.net-amount-row .value {
            font-weight: 900;
            font-size: 14px;
        }

        .total-row.balance-due-row {
            font-weight: 800;
            font-size: 12.5px;
            padding-top: 2px;
        }

        .split-item {
            font-size: 10.5px;
            padding-left: 10px;
            color: #333;
        }

        /* Ledger / Account Summary Box */
        .ledger-box {
            margin-top: 8px;
            padding: 6px 8px;
            border: 1px dashed #000;
            border-radius: 4px;
            background: #fafafa;
            font-size: 11px;
        }

        .ledger-title {
            text-align: center;
            font-weight: 800;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 5px;
            padding-bottom: 3px;
            border-bottom: 1px dotted #999;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 12px;
            font-size: 10.5px;
            line-height: 1.4;
        }

        .receipt-footer .thank-you {
            font-weight: 800;
            font-size: 11.5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .receipt-footer .policy {
            color: #333;
            font-size: 10px;
        }

        .barcode-container {
            margin: 8px auto 4px auto;
            text-align: center;
        }

        .barcode-svg {
            max-width: 100%;
            height: 38px;
            margin: 0 auto;
            display: block;
        }

        .barcode-text {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }

        .software-branding {
            margin-top: 8px;
            font-size: 9px;
            font-weight: 600;
            color: #555;
            letter-spacing: 0.3px;
            border-top: 1px dotted #ccc;
            padding-top: 5px;
        }

        /* ----- PRINT SPECIFIC STYLES ----- */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0mm;
            }

            *, *:before, *:after {
                color: #000000 !important;
                text-shadow: none !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            html, body {
                width: 80mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 11.5px !important;
            }

            .no-print {
                display: none !important;
            }

            .receipt-wrapper {
                max-width: 80mm !important;
                width: 80mm !important;
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 2mm 3mm 4mm 3mm !important;
                margin: 0 !important;
            }

            .ledger-box {
                background: transparent !important;
                border: 1px dashed #000 !important;
            }

            .receipt-logo {
                filter: grayscale(100%) contrast(250%) !important;
            }

            .software-branding {
                border-top: 1px dotted #000 !important;
                color: #000 !important;
            }
        }
    </style>
</head>
<body>

<!-- SCREEN ONLY ACTIONS -->
<div class="toolbar-container no-print">
    <button onclick="window.print()" class="btn-action btn-print" title="Print Receipt (Ctrl+P)">
        <i class="fa fa-print"></i> Print Receipt <kbd>Ctrl+P</kbd>
    </button>
    <a href="{{ route('local.sale.invoice', $sale->id) }}" class="btn-action btn-invoice" title="View A4 Invoice">
        <i class="fa fa-file-invoice"></i> A4 Invoice
    </a>
    <a href="{{ route('show-local-sale', $sale->id) }}" class="btn-action btn-invoice" title="View Sale Details">
        <i class="fa fa-eye"></i> Details
    </a>
    <a href="{{ route('all-local-sale') }}" class="btn-action btn-back" title="Back to All Sales">
        <i class="fa fa-list"></i> Sales List
    </a>
    <a href="{{ route('local-sale') }}" class="btn-action btn-new" title="Create New Sale">
        <i class="fa fa-plus"></i> New Sale
    </a>
</div>

<!-- RECEIPT PAPER -->
<div class="receipt-wrapper" id="thermalReceipt">

    <!-- HEADER -->
    <div class="receipt-header">
        @if(!empty($appSettings['company_logo']))
            <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" 
                 alt="Logo" 
                 class="receipt-logo"
                 onerror="this.style.display='none'">
        @endif
        <div class="company-title">{{ $appSettings['company_name'] ?? 'Company Name' }}</div>
        @if(!empty($appSettings['company_address']))
            <div class="company-meta">{!! nl2br(e($appSettings['company_address'])) !!}</div>
        @endif
        @if(!empty($appSettings['company_phone']))
            <div class="company-meta phone">Tel: {{ $appSettings['company_phone'] }}</div>
        @endif
    </div>

    <!-- TYPE BADGE -->
    <div class="receipt-type-badge">
        @if(strtolower($sale->sale_type) === 'estimate')
            ESTIMATE
        @elseif(strtolower($sale->sale_type) === 'booking')
            BOOKING RECEIPT
        @else
            SALE RECEIPT
        @endif
    </div>

    <!-- INVOICE & CUSTOMER INFO -->
    <div class="info-grid">
        <div class="info-row">
            <span class="label">Receipt #:</span>
            <span class="value num-font">{{ $sale->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date:</span>
            <span class="value num-font">{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('d-M-Y') : date('d-M-Y') }}</span>
        </div>
        <div class="info-row">
            <span class="label">Time:</span>
            <span class="value num-font">{{ $sale->created_at ? \Carbon\Carbon::parse($sale->created_at)->format('h:i A') : date('h:i A') }}</span>
        </div>

        @if(!empty($sale->delivery_date) && strtolower($sale->sale_type) !== 'estimate')
        <div class="info-row">
            <span class="label">Delivery:</span>
            <span class="value num-font">{{ \Carbon\Carbon::parse($sale->delivery_date)->format('d-M-Y') }}</span>
        </div>
        @endif

        <div class="divider-dashed"></div>

        <div class="info-row">
            <span class="label">Customer:</span>
            <span class="value">{{ $party->business_name ?? $party->name ?? 'Walk-in Customer' }}</span>
        </div>
        @if(!empty($party->phone) && $party->phone !== 'N/A')
        <div class="info-row">
            <span class="label">Phone:</span>
            <span class="value num-font">{{ $party->phone }}</span>
        </div>
        @endif
        @if(!empty($party->address) && $party->address !== 'N/A' && $party->address !== 'Address Not Provided')
        <div class="info-row">
            <span class="label">Address:</span>
            <span class="value">{{ Str::limit($party->address, 36) }}</span>
        </div>
        @endif
    </div>

    <!-- ITEMS SECTION -->
    <div class="items-header">
        <span>Item Description</span>
        <span>Amount (RS)</span>
    </div>

    @php
        $items = json_decode($sale->item) ?? [];
        $heights = json_decode($sale->height) ?? [];
        $widths = json_decode($sale->width) ?? [];
        $qtys = json_decode($sale->qty) ?? [];
        $units = json_decode($sale->unit) ?? [];
        $rates = json_decode($sale->rate) ?? [];
        $amounts = json_decode($sale->amount) ?? [];
        $totalQtyCount = 0;
        $itemCount = 0;
    @endphp

    <div class="items-list">
        @foreach($items as $i => $item)
            @if(!empty($item))
                @php
                    $itemCount++;
                    $qtyVal = (float)($qtys[$i] ?? 0);
                    $totalQtyCount += $qtyVal;
                    $rateVal = (float)($rates[$i] ?? 0);
                    $amountVal = (float)($amounts[$i] ?? 0);
                    $hasDim = (!empty($heights[$i]) || !empty($widths[$i]));
                @endphp
                <div class="item-card">
                    <div class="item-main-row">
                        <span class="item-name">{{ $itemCount }}. {{ $item }}</span>
                        <span class="item-amount num-font">{{ number_format($amountVal, 2) }}</span>
                    </div>
                    @if($hasDim)
                        <div class="item-dim">
                            Size: {{ $heights[$i] ?? '-' }} &times; {{ $widths[$i] ?? '-' }}{{ !empty($units[$i]) ? ' ' . $units[$i] : '' }}
                        </div>
                    @endif
                    <div class="item-sub-row">
                        <span class="item-calc num-font">{{ $qtyVal == 0 ? 1 : $qtyVal }} &times; {{ number_format($rateVal, 2) }}</span>
                    </div>
                </div>
            @endif
        @endforeach
    </div>

    <div class="divider-solid"></div>

    <!-- TOTALS SECTION -->
    <div class="totals-block">
        <div class="total-row">
            <span class="label">Total Items / Qty:</span>
            <span class="value num-font">{{ $itemCount }} items ({{ $totalQtyCount }} pcs)</span>
        </div>
        <div class="total-row">
            <span class="label">Sub Total:</span>
            <span class="value num-font">RS {{ number_format($sale->grand_total, 2) }}</span>
        </div>

        @if($sale->discount_value > 0)
        <div class="total-row">
            <span class="label">Discount:</span>
            <span class="value num-font">- RS {{ number_format($sale->discount_value, 2) }}</span>
        </div>
        @endif

        <div class="total-row net-amount-row">
            <span class="label">NET AMOUNT:</span>
            <span class="value num-font">RS {{ number_format($sale->net_amount, 2) }}</span>
        </div>

        @if(strtolower($sale->sale_type) !== 'estimate')
        <div class="total-row">
            <span class="label">{{ $sale->party_type === 'walkin' ? 'Paid Amount:' : 'Advance Paid:' }}</span>
            <span class="value num-font">RS {{ number_format($sale->advance_amount, 2) }}</span>
        </div>

        @if(!empty($sale->split_payments) && is_array($sale->split_payments))
            @foreach($sale->split_payments as $sp)
            <div class="total-row split-item">
                <span class="label">&bull; {{ $sp['account_name'] ?? 'Account' }}:</span>
                <span class="value num-font">RS {{ number_format($sp['amount'] ?? 0, 2) }}</span>
            </div>
            @endforeach
        @endif

        <div class="total-row balance-due-row">
            <span class="label">Balance Due:</span>
            <span class="value num-font">RS {{ number_format($sale->remaining_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <!-- ACCOUNT STATEMENT (IF NOT ESTIMATE & HAS BALANCE) -->
    @if(strtolower($sale->sale_type) !== 'estimate' && isset($ledger_info) && $sale->party_type !== 'walkin')
    <div class="ledger-box">
        <div class="ledger-title">Account Summary</div>
        <div class="total-row">
            <span class="label">{{ $ledger_info->label_prev }}:</span>
            <span class="value num-font">RS {{ number_format($ledger_info->previous_balance, 2) }}</span>
        </div>
        <div class="total-row">
            <span class="label">Current Invoice:</span>
            <span class="value num-font">{{ $ledger_info->operator }} RS {{ number_format($sale->remaining_amount, 2) }}</span>
        </div>
        <div class="total-row" style="font-weight: 800; border-top: 1px dotted #888; margin-top: 3px; padding-top: 3px;">
            <span class="label">{{ $ledger_info->label_curr }}:</span>
            <span class="value num-font">RS {{ number_format($ledger_info->current_balance, 2) }}</span>
        </div>
    </div>
    @endif

    <div class="divider-double"></div>

    <!-- FOOTER -->
    <div class="receipt-footer">
        <div class="thank-you">THANK YOU!</div>
        <div class="policy">Goods once sold will not be returned.</div>
        <div class="policy">Please keep this receipt for future reference.</div>
        
        {{-- <div class="barcode-container">
            <svg id="barcode" class="barcode-svg"></svg>
            <div class="barcode-text" id="barcodeFallback" style="display:none;">*{{ $sale->invoice_number }}*</div>
        </div> --}}

        <div class="software-branding">
            ProWave Software Solutions &bull; 0317-3836223
        </div>
    </div>

</div>

<!-- BARCODE & PRINT SCRIPTS -->
<script>
    document.addEventListener("DOMContentLoaded", function () {
        try {
            if (typeof JsBarcode === "function") {
                JsBarcode("#barcode", "{{ $sale->invoice_number }}", {
                    format: "CODE128",
                    lineColor: "#000",
                    width: 1.5,
                    height: 32,
                    displayValue: true,
                    fontSize: 11,
                    fontOptions: "bold",
                    font: "monospace",
                    margin: 0
                });
            } else {
                document.getElementById('barcodeFallback').style.display = 'block';
                document.getElementById('barcode').style.display = 'none';
            }
        } catch (e) {
            document.getElementById('barcodeFallback').style.display = 'block';
            document.getElementById('barcode').style.display = 'none';
        }
    });

    // Auto print if requested in query or session
    window.addEventListener('load', function() {
        @if(request()->has('autoprint') || session('autoprint'))
            setTimeout(function() {
                window.print();
            }, 450);
        @endif
    });

    // Support keyboard shortcut Ctrl+P or Command+P
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            window.print();
        }
    });
</script>

</body>
</html>
