<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #{{ $sale->invoice_number }} - {{ $appSettings['company_name'] ?? 'Receipt' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #e2e8f0;
            color: #000;
            padding: 20px 10px;
            font-size: 13px;
            line-height: 1.35;
            -webkit-font-smoothing: antialiased;
        }

        /* ----- ACTION TOOLBAR (SCREEN ONLY) ----- */
        .toolbar-container {
            max-width: 440px;
            margin: 0 auto 15px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            border-radius: 6px;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
        }

        .btn-print {
            background-color: #000;
            color: #fff;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.35);
        }
        .btn-print:hover {
            background-color: #1e293b;
        }

        .btn-invoice {
            background-color: #fff;
            color: #000;
            border-color: #94a3b8;
        }
        .btn-invoice:hover {
            background-color: #f1f5f9;
        }

        .btn-back {
            background-color: #475569;
            color: #fff;
        }
        .btn-back:hover {
            background-color: #334155;
        }

        .btn-new {
            background-color: #15803d;
            color: #fff;
        }
        .btn-new:hover {
            background-color: #166534;
        }

        /* ----- THERMAL RECEIPT CONTAINER ----- */
        .receipt-wrapper {
            max-width: 80mm;
            width: 100%;
            margin: 0 auto;
            background: #fff;
            padding: 14px 12px;
            box-shadow: 0 6px 25px rgba(0, 0, 0, 0.15);
            border-radius: 4px;
            border: 2px solid #000;
            color: #000 !important;
            font-family: 'Courier New', Courier, monospace;
            font-weight: 700;
        }

        .receipt-wrapper * {
            color: #000 !important;
        }

        /* Header */
        .receipt-header {
            text-align: center;
            margin-bottom: 8px;
        }

        .receipt-logo {
            max-width: 130px;
            max-height: 55px;
            object-fit: contain;
            margin: 0 auto 6px auto;
            display: block;
            filter: grayscale(100%) contrast(200%);
        }

        .company-title {
            font-size: 16px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }

        .company-meta {
            font-size: 11.5px;
            font-weight: 700;
            line-height: 1.3;
        }

        .receipt-type-badge {
            margin: 8px 0;
            text-align: center;
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-top: 2px dashed #000;
            border-bottom: 2px dashed #000;
            padding: 4px 0;
        }

        /* Info meta rows */
        .info-grid {
            margin: 8px 0;
            font-size: 12px;
            font-weight: 700;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .info-row span:first-child {
            font-weight: 800;
        }

        .info-row span:last-child {
            font-weight: 900;
            text-align: right;
            word-break: break-all;
        }

        /* Divider */
        .dashed-line {
            border-top: 1.5px dashed #000;
            margin: 8px 0;
        }

        .double-dashed-line {
            border-top: 2.5px dashed #000;
            margin: 8px 0;
        }

        /* Items Table */
        .receipt-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            font-weight: 700;
            margin: 8px 0;
        }

        .receipt-table th {
            text-align: left;
            padding: 4px 0;
            border-bottom: 2px dashed #000;
            font-weight: 900;
            font-size: 12px;
        }

        .receipt-table th.text-center { text-align: center; }
        .receipt-table th.text-end { text-align: right; }

        .receipt-table td {
            padding: 3px 0;
            vertical-align: top;
            font-weight: 700;
        }

        .receipt-table td.text-center { text-align: center; }
        .receipt-table td.text-end { text-align: right; }

        .item-row-title {
            padding-top: 4px;
            font-weight: 900;
            font-size: 12.5px;
        }

        .item-specs {
            font-size: 11px;
            font-weight: 700;
            display: block;
        }

        /* Totals */
        .totals-block {
            margin: 8px 0;
            font-size: 12px;
            font-weight: 700;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 2.5px 0;
        }

        .total-row span:first-child {
            font-weight: 800;
        }

        .total-row span:last-child {
            font-weight: 900;
        }

        .total-row.grand-net {
            font-size: 14.5px;
            font-weight: 900;
            border-top: 2px dashed #000;
            border-bottom: 2px dashed #000;
            padding: 5px 0;
            margin: 5px 0;
        }

        .total-row.balance-due {
            font-weight: 900;
            font-size: 13.5px;
        }

        .split-item {
            font-size: 11px;
            padding-left: 8px;
        }

        /* Ledger Statement */
        .ledger-box {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1.5px dashed #000;
            font-size: 12px;
            font-weight: 700;
        }

        .ledger-title {
            text-align: center;
            font-weight: 900;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 12px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.35;
        }

        .receipt-footer .thank-you {
            font-weight: 900;
            font-size: 12.5px;
            text-transform: uppercase;
            margin-bottom: 4px;
        }

        .barcode-area {
            text-align: center;
            margin-top: 8px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 2px;
        }

        /* ----- PRINT SPECIFIC STYLES ----- */
        @media print {
            @page {
                size: 80mm auto;
                margin: 0mm;
            }

            html, body {
                width: 80mm;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
                color: #000 !important;
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
                padding: 3mm 3mm !important;
                margin: 0 !important;
                font-weight: 800 !important;
            }

            .receipt-wrapper * {
                color: #000 !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            .receipt-logo {
                filter: grayscale(100%) contrast(250%) !important;
            }
        }
    </style>
</head>
<body>

<!-- SCREEN ONLY ACTIONS -->
<div class="toolbar-container no-print">
    <button onclick="window.print()" class="btn-action btn-print" title="Print Thermal Receipt">
        <i class="fa fa-print"></i> Print Receipt
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
            <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="Logo" class="receipt-logo">
        @endif
        <div class="company-title">{{ $appSettings['company_name'] ?? 'Company Name' }}</div>
        @if(!empty($appSettings['company_address']))
            <div class="company-meta">{!! nl2br(e($appSettings['company_address'])) !!}</div>
        @endif
        @if(!empty($appSettings['company_phone']))
            <div class="company-meta">Tel: {{ $appSettings['company_phone'] }}</div>
        @endif
    </div>

    <!-- TYPE BADGE -->
    <div class="receipt-type-badge">
        @if(strtolower($sale->sale_type) === 'estimate')
            *** ESTIMATE ***
        @elseif(strtolower($sale->sale_type) === 'booking')
            *** BOOKING RECEIPT ***
        @else
            *** SALE RECEIPT ***
        @endif
    </div>

    <!-- INVOICE & CUSTOMER INFO -->
    <div class="info-grid">
        <div class="info-row">
            <span>Receipt #:</span>
            <span>{{ $sale->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span>Date:</span>
            <span id="receiptDate"></span>
        </div>
        <div class="info-row">
            <span>Time:</span>
            <span id="receiptTime"></span>
        </div>

        @if(!empty($sale->delivery_date) && strtolower($sale->sale_type) !== 'estimate')
        <div class="info-row">
            <span>Delivery:</span>
            <span>{{ \Carbon\Carbon::parse($sale->delivery_date)->format('d-M-Y') }}</span>
        </div>
        @endif

        <div class="dashed-line"></div>

        <div class="info-row">
            <span>Party:</span>
            <span>{{ $party->business_name ?? $party->name }}</span>
        </div>
        @if(!empty($party->phone) && $party->phone !== 'N/A')
        <div class="info-row">
            <span>Phone:</span>
            <span>{{ $party->phone }}</span>
        </div>
        @endif
        @if(!empty($party->address) && $party->address !== 'N/A' && $party->address !== 'Address Not Provided')
        <div class="info-row">
            <span>Address:</span>
            <span>{{ Str::limit($party->address, 32) }}</span>
        </div>
        @endif
    </div>

    <!-- ITEMS TABLE -->
    <table class="receipt-table">
        <thead>
            <tr>
                <th style="width: 46%;">Item</th>
                <th class="text-center" style="width: 14%;">Qty</th>
                <th class="text-end" style="width: 20%;">Price</th>
                <th class="text-end" style="width: 20%;">Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $items = json_decode($sale->item) ?? [];
                $heights = json_decode($sale->height) ?? [];
                $widths = json_decode($sale->width) ?? [];
                $qtys = json_decode($sale->qty) ?? [];
                $units = json_decode($sale->unit) ?? [];
                $rates = json_decode($sale->rate) ?? [];
                $amounts = json_decode($sale->amount) ?? [];
                $totalQtyCount = 0;
            @endphp
            @foreach($items as $i => $item)
                @if(!empty($item))
                    @php
                        $qtyVal = (float)($qtys[$i] ?? 0);
                        $totalQtyCount += $qtyVal;
                        $hasDim = (!empty($heights[$i]) || !empty($widths[$i]));
                    @endphp
                    <tr>
                        <td colspan="4" class="item-row-title">
                            {{ $loop->iteration }}. {{ $item }}
                            @if($hasDim)
                                <span class="item-specs">({{ $heights[$i] ?? '-' }} &times; {{ $widths[$i] ?? '-' }}{{ !empty($units[$i]) ? ' ' . $units[$i] : '' }})</span>
                            @endif
                        </td>
                    </tr>
                    <tr style="border-bottom: 1.5px dotted #000;">
                        <td></td>
                        <td class="text-center" style="font-weight: 800;">{{ $qtyVal == 0 ? '-' : $qtyVal }}</td>
                        <td class="text-end" style="font-weight: 800;">{{ number_format((float)($rates[$i] ?? 0), 2) }}</td>
                        <td class="text-end" style="font-weight: 900;">{{ number_format((float)($amounts[$i] ?? 0), 2) }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    <!-- TOTALS SECTION -->
    <div class="totals-block">
        <div class="total-row">
            <span>Total Items:</span>
            <span>{{ count(array_filter($items)) }} (Qty: {{ $totalQtyCount }})</span>
        </div>
        <div class="total-row">
            <span>Sub Total:</span>
            <span>RS {{ number_format($sale->grand_total, 2) }}</span>
        </div>

        @if($sale->discount_value > 0)
        <div class="total-row">
            <span>Discount:</span>
            <span>- RS {{ number_format($sale->discount_value, 2) }}</span>
        </div>
        @endif

        <div class="total-row grand-net">
            <span>NET AMOUNT:</span>
            <span>RS {{ number_format($sale->net_amount, 2) }}</span>
        </div>

        @if(strtolower($sale->sale_type) !== 'estimate')
        <div class="total-row">
            <span>{{ $sale->party_type === 'walkin' ? 'Paid Amount:' : 'Advance Paid:' }}</span>
            <span>RS {{ number_format($sale->advance_amount, 2) }}</span>
        </div>

        @if(!empty($sale->split_payments) && is_array($sale->split_payments))
            @foreach($sale->split_payments as $sp)
            <div class="total-row split-item">
                <span>&bull; {{ $sp['account_name'] ?? 'Account' }}:</span>
                <span>RS {{ number_format($sp['amount'] ?? 0, 2) }}</span>
            </div>
            @endforeach
        @endif

        <div class="total-row balance-due">
            <span>Balance Due:</span>
            <span>RS {{ number_format($sale->remaining_amount, 2) }}</span>
        </div>
        @endif
    </div>

    <!-- ACCOUNT STATEMENT (IF NOT ESTIMATE & HAS BALANCE) -->
    @if(strtolower($sale->sale_type) !== 'estimate' && isset($ledger_info) && $sale->party_type !== 'walkin')
    <div class="ledger-box">
        <div class="ledger-title">--- Account Summary ---</div>
        <div class="total-row">
            <span>{{ $ledger_info->label_prev }}:</span>
            <span>RS {{ number_format($ledger_info->previous_balance, 2) }}</span>
        </div>
        <div class="total-row">
            <span>Current Invoice:</span>
            <span>{{ $ledger_info->operator }} RS {{ number_format($sale->remaining_amount, 2) }}</span>
        </div>
        <div class="total-row" style="font-weight: 900; border-top: 1.5px dotted #000; padding-top: 3px;">
            <span>{{ $ledger_info->label_curr }}:</span>
            <span>RS {{ number_format($ledger_info->current_balance, 2) }}</span>
        </div>
    </div>
    @endif

    <div class="double-dashed-line"></div>

    <!-- FOOTER -->
    <div class="receipt-footer">
        <div class="thank-you">THANK YOU FOR YOUR BUSINESS!</div>
        <p>Goods once sold will not be returned.</p>
        <p>Please keep this receipt for future reference.</p>
        <div class="barcode-area">
            *{{ $sale->invoice_number }}*
        </div>
        <p style="margin-top: 5px; font-size: 10px; font-weight: 800;">
            ProWave Software Solutions | 0317-3836223
        </p>
    </div>

</div>

<!-- SET CURRENT DATE & TIME -->
<script>
    (function() {
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        const now = new Date();
        const day = String(now.getDate()).padStart(2, '0');
        const mon = months[now.getMonth()];
        const year = now.getFullYear();
        let hours = now.getHours();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        const minutes = String(now.getMinutes()).padStart(2, '0');
        document.getElementById('receiptDate').textContent = day + '-' + mon + '-' + year;
        document.getElementById('receiptTime').textContent = String(hours).padStart(2, '0') + ':' + minutes + ' ' + ampm;
    })();
</script>

<!-- AUTO-PRINT SCRIPT -->
<script>
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
