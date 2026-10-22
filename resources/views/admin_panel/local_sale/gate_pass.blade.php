<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass #{{ $sale->invoice_number }} - {{ $appSettings['company_name'] ?? 'Gate Pass' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
            padding: 20px 10px;
            font-size: 11.5px;
            line-height: 1.35;
            -webkit-font-smoothing: antialiased;
        }

        /* ----- ACTION TOOLBAR (SCREEN ONLY) ----- */
        .toolbar-container {
            max-width: 580px;
            margin: 0 auto 16px auto;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            justify-content: center;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 7px;
            text-decoration: none;
            border: 1px solid transparent;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .btn-print {
            background-color: #0f172a;
            color: #ffffff;
            box-shadow: 0 3px 8px rgba(15, 23, 42, 0.25);
        }
        .btn-print:hover {
            background-color: #1e293b;
        }

        .btn-print kbd {
            background: rgba(255, 255, 255, 0.2);
            padding: 1px 4px;
            border-radius: 4px;
            font-size: 9.5px;
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
        /* Safe 70mm - 72mm width to prevent edge cutting on 80mm thermal printers */
        .receipt-wrapper {
            max-width: 72mm;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            padding: 14px 10px 16px 10px;
            box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08), 0 0 0 1px rgba(0, 0, 0, 0.05);
            border-radius: 6px;
            color: #000000;
            box-sizing: border-box;
            overflow: hidden;
        }

        /* Number font formatting */
        .num-font {
            font-family: 'JetBrains Mono', 'Consolas', monospace;
            font-variant-numeric: tabular-nums;
        }

        /* Header */
        .receipt-header {
            text-align: center;
            margin-bottom: 8px;
        }

        .receipt-logo {
            max-width: 100px;
            max-height: 45px;
            object-fit: contain;
            margin: 0 auto 5px auto;
            display: block;
            filter: grayscale(100%) contrast(150%);
        }

        .company-title {
            font-size: 14px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 2px;
            color: #000;
        }

        .company-meta {
            font-size: 10px;
            font-weight: 500;
            color: #222;
            line-height: 1.3;
        }

        .company-meta.phone {
            font-weight: 700;
            margin-top: 1px;
            font-size: 10.5px;
        }

        /* Receipt Badge */
        .receipt-type-badge {
            margin: 8px 0 6px 0;
            text-align: center;
            font-size: 12px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 4px 0;
            border-top: 1.5px solid #000;
            border-bottom: 1.5px solid #000;
        }

        /* Info meta rows */
        .info-grid {
            margin: 6px 0;
            font-size: 10.5px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 3px;
            gap: 4px;
            width: 100%;
        }

        .info-row .label {
            font-weight: 600;
            color: #222;
            flex-shrink: 0;
        }

        .info-row .value {
            font-weight: 700;
            text-align: right;
            color: #000;
            flex: 1;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        /* Dividers */
        .divider-dashed {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }

        .divider-dotted {
            border-top: 1px dotted #555;
            margin: 5px 0;
        }

        .divider-solid {
            border-top: 1px solid #000;
            margin: 6px 0;
        }

        .divider-double {
            border-top: 2.5px double #000;
            margin: 6px 0;
        }

        /* Gate Pass Products Table (Matching User's Layout) */
        .thermal-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #000;
            margin: 6px 0;
            font-size: 10.5px;
        }

        .thermal-table th {
            border: 1px solid #000;
            background-color: #f1f5f9;
            padding: 4px 3px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 10px;
            color: #000;
            letter-spacing: 0.3px;
        }

        .thermal-table td {
            border: 1px solid #000;
            padding: 4px 3px;
            color: #000;
            vertical-align: middle;
        }

        .thermal-table tr.blank-row td {
            height: 22px;
        }

        .thermal-table tfoot th {
            border: 1.5px solid #000;
            padding: 5px 4px;
            font-weight: 900;
            font-size: 11px;
            background-color: #f8fafc;
        }

        .item-name {
            font-weight: 700;
            font-size: 10.5px;
            color: #000;
            line-height: 1.25;
        }

        .item-dim {
            font-size: 9px;
            color: #444;
            margin-top: 1px;
            font-weight: 500;
        }

        /* Footer */
        .receipt-footer {
            text-align: center;
            margin-top: 10px;
            font-size: 10px;
            line-height: 1.35;
        }

        .receipt-footer .thank-you {
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }

        .receipt-footer .policy {
            color: #333;
            font-size: 9.5px;
        }

        .software-branding {
            margin-top: 6px;
            font-size: 8.5px;
            font-weight: 600;
            color: #555;
            letter-spacing: 0.2px;
            border-top: 1px dotted #ccc;
            padding-top: 4px;
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
                box-sizing: border-box !important;
            }

            html, body {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                font-size: 10px !important;
                line-height: 1.3 !important;
            }

            .no-print {
                display: none !important;
            }

            .receipt-wrapper {
                width: 100% !important;
                max-width: 68mm !important; /* Safe printable width on 80mm POS printers */
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                padding: 1mm 1.5mm 3mm 1.5mm !important;
                margin: 0 auto !important;
                box-sizing: border-box !important;
                overflow: hidden !important;
            }

            .receipt-logo {
                filter: grayscale(100%) contrast(250%) !important;
                max-width: 85px !important;
            }

            .company-title {
                font-size: 13px !important;
            }

            .company-meta {
                font-size: 9px !important;
            }

            .receipt-type-badge {
                font-size: 11px !important;
                margin: 5px 0 4px 0 !important;
                padding: 2px 0 !important;
            }

            .info-grid {
                font-size: 9.5px !important;
                margin: 4px 0 !important;
            }

            .thermal-table {
                font-size: 9.5px !important;
                margin: 4px 0 !important;
            }

            .thermal-table th {
                font-size: 9px !important;
                padding: 3px 2px !important;
                border-color: #000000 !important;
            }

            .thermal-table td {
                padding: 3px 2px !important;
                font-size: 9.5px !important;
                border-color: #000000 !important;
            }

            .thermal-table tr.blank-row td {
                height: 18px !important;
            }

            .thermal-table tfoot th {
                font-size: 10px !important;
                padding: 4px 2px !important;
                border-color: #000000 !important;
            }

            .receipt-type-badge, .divider-solid, .divider-double, .divider-dashed {
                border-color: #000000 !important;
            }

            .receipt-footer {
                font-size: 9px !important;
                margin-top: 6px !important;
            }

            .software-branding {
                border-top: 1px dotted #000 !important;
                color: #000 !important;
                font-size: 8px !important;
                padding-top: 3px !important;
            }
        }
    </style>
</head>
<body>

<!-- SCREEN ONLY ACTIONS -->
<div class="toolbar-container no-print">
    <button onclick="window.print()" class="btn-action btn-print" title="Print Gate Pass (Ctrl+P)">
        <i class="fa fa-print"></i> Print Gate Pass <kbd>Ctrl+P</kbd>
    </button>
    <a href="{{ route('local.sale.receipt', $sale->id) }}" class="btn-action btn-invoice" title="View Thermal Receipt">
        <i class="fa fa-receipt"></i> Thermal Receipt
    </a>
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

<!-- THERMAL GATE PASS PAPER -->
<div class="receipt-wrapper" id="thermalGatePass">

    <!-- HEADER -->
    <div class="receipt-header">
        @if(!empty($appSettings['company_logo']))
            <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" 
                 alt="Logo" 
                 class="receipt-logo"
                 onerror="this.style.display='none'">
        @endif

        <div class="company-title">{{ $appSettings['company_name'] ?? 'Company Name' }}</div>

        @if(!empty($appSettings['company_phone']))
            <div class="company-meta phone">Tel: {{ $appSettings['company_phone'] }}</div>
        @endif

        @if(!empty($appSettings['company_address']))
            <div class="company-meta">{{ Str::limit($appSettings['company_address'], 65) }}</div>
        @endif

        @if(!empty($appSettings['company_website']))
            <div class="company-meta">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_website'], '/')) }}</div>
        @endif
    </div>

    <!-- GATE PASS BADGE -->
    <div class="receipt-type-badge">
        GATE PASS
    </div>

    <!-- META & PARTY INFO -->
    <div class="info-grid">
        <div class="info-row">
            <span class="label">Gate Pass #:</span>
            <span class="value num-font">GP-{{ $sale->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Invoice Ref:</span>
            <span class="value num-font">#{{ $sale->invoice_number }}</span>
        </div>
        <div class="info-row">
            <span class="label">Date &amp; Time:</span>
            <span class="value num-font">{{ \Carbon\Carbon::parse($sale->sale_date)->format('d-M-Y') }} {{ date('h:i A') }}</span>
        </div>
        @if(!empty($sale->delivery_date))
        <div class="info-row">
            <span class="label">Delivery Date:</span>
            <span class="value num-font">{{ date('d-M-Y', strtotime($sale->delivery_date)) }}</span>
        </div>
        @endif

        <div class="divider-dotted"></div>

        <div class="info-row">
            <span class="label">Consignee:</span>
            <span class="value">{{ $party->business_name ?? $party->name }}</span>
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

    <!-- PRODUCTS TABLE (EXACT LAYOUT: S.No | Description | Quantity) -->
    @php
        $items = json_decode($sale->item) ?? [];
        $heights = json_decode($sale->height) ?? [];
        $widths = json_decode($sale->width) ?? [];
        $qtys = json_decode($sale->qty) ?? [];
        $units = json_decode($sale->unit) ?? [];
        $rowCount = 0;
        $totalQtyCount = 0;
    @endphp

    <table class="thermal-table">
        <thead>
            <tr>
                <th style="width: 14%; text-align: center;">S.No.</th>
                <th style="width: 58%; text-align: left;">Description</th>
                <th style="width: 28%; text-align: center;">Quantity</th>
            </tr>
        </thead>
        <tbody>
            @foreach($items as $i => $item)
                @if(!empty($item))
                    @php
                        $rowCount++;
                        $qtyVal = (float)($qtys[$i] ?? 0);
                        $totalQtyCount += $qtyVal;
                        $unitVal = !empty($units[$i]) ? strtoupper($units[$i]) : '';
                        $hasDim = (!empty($heights[$i]) || !empty($widths[$i]));
                    @endphp
                    <tr>
                        <td style="text-align: center; font-weight: 700;">{{ $rowCount }}</td>
                        <td style="text-align: left;">
                            <div class="item-name">{{ $item }}</div>
                            @if($hasDim)
                                <div class="item-dim">
                                    Size: {{ $heights[$i] ?? '-' }} &times; {{ $widths[$i] ?? '-' }}{{ !empty($unitVal) ? ' ' . $unitVal : '' }}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: center; font-weight: 800;" class="num-font">
                            {{ $qtyVal == (int)$qtyVal ? (int)$qtyVal : $qtyVal }}
                            @if(!empty($unitVal))
                                <span style="font-weight: 500; font-size: 9.5px;">{{ $unitVal }}</span>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach

            {{-- Minimum 5 rows to preserve classic gate pass voucher look --}}
            @php
                $minRows = 5;
                $blankRows = max(0, $minRows - $rowCount);
            @endphp
            @for($b = 0; $b < $blankRows; $b++)
                <tr class="blank-row">
                    <td style="text-align: center;">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" style="text-align: right; text-transform: uppercase;">Total Quantity:</th>
                <th style="text-align: center;" class="num-font">{{ $totalQtyCount == (int)$totalQtyCount ? (int)$totalQtyCount : $totalQtyCount }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="divider-dashed"></div>

    <!-- TERMS & NOTE FROM SETTINGS -->
    @if(!empty($appSettings['invoice_terms']))
    <div style="font-size: 9px; color: #222; margin: 4px 0 6px 0; line-height: 1.35; text-align: left;">
        <strong>Instructions:</strong>
        <div>{{ Str::limit(str_replace(["\r", "\n"], ' ', $appSettings['invoice_terms']), 120) }}</div>
    </div>
    @endif

    <div class="divider-double"></div>

    <!-- FOOTER -->
    <div class="receipt-footer">
        <div class="software-branding">
            ProWave Software technologies &bull; 0317-3836223
        </div>
    </div>

</div>

<!-- SCRIPTS FOR PRINT -->
<script>
    // Auto print if requested in query
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