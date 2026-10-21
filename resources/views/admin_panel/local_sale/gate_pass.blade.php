<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gate Pass #{{ $sale->invoice_number }} - {{ $appSettings['company_name'] ?? 'Gate Pass' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #000000; 
            --primary-light: #333333;
            --text-main: #1a1a1a;
            --text-muted: #555555;
            --bg-light: #ffffff; 
            --bg-gray: #f8fafc;
            --border-color: #000000;
            --table-border: #222222;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #e2e8f0;
            padding: 20px;
            color: var(--text-main);
            -webkit-font-smoothing: antialiased;
        }

        /* Screen toolbar */
        .no-print {
            max-width: 900px; 
            margin: 0 auto 15px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }

        .toolbar-left, .toolbar-right {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .btn-link-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            background-color: #ffffff;
            color: var(--text-main);
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            border: 1px solid #cbd5e1;
            transition: all 0.2s;
            cursor: pointer;
        }

        .btn-link-action:hover {
            background-color: #f1f5f9;
            color: #000000;
            border-color: #94a3b8;
        }

        .btn-link-action.btn-primary-print {
            background-color: #0f172a;
            color: #ffffff;
            border-color: #0f172a;
        }

        .btn-link-action.btn-primary-print:hover {
            background-color: #1e293b;
        }

        .btn-link-action kbd {
            background: rgba(255, 255, 255, 0.25);
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 10px;
        }

        /* Gate Pass document sheet */
        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            border: 1px solid #d1d5db;
        }

        /* ----- HEADER ----- */
        .invoice-header {
            padding: 35px 40px 25px 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #000000;
            background-color: #ffffff;
        }

        .inv-header-left {
            display: flex;
            flex-direction: column;
            gap: 12px;
            max-width: 50%;
        }

        .main-logo {
            max-width: 170px;
            max-height: 70px;
            object-fit: contain;
            display: block;
        }

        .company-name {
            font-size: 22px;
            font-weight: 800;
            color: var(--primary);
            margin: 0;
            letter-spacing: 0.5px;
        }

        .company-contact {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12.5px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .contact-item a {
            color: var(--text-muted);
            text-decoration: none;
        }

        .contact-item a:hover {
            color: var(--primary);
        }

        .inv-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
            max-width: 50%;
        }

        .document-title {
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            letter-spacing: 2.5px;
            color: var(--primary);
            text-transform: uppercase;
            line-height: 1.1;
        }

        .document-subtitle {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
            padding-bottom: 3px;
        }

        .meta-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 4px;
        }

        .secondary-logo {
            max-height: 70px;
            object-fit: contain;
        }

        .meta-details {
            text-align: right;
            font-size: 12.5px;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .meta-details span {
            font-weight: 700;
            color: var(--primary);
        }

        /* ----- INFO SECTION ----- */
        .invoice-info-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            padding: 24px 40px;
            background-color: var(--bg-light);
            border-bottom: 1px solid #e2e8f0;
        }

        .info-block {
            background: #f8fafc;
            padding: 16px 20px;
            border-radius: 4px;
            border: 1px solid #cbd5e1;
        }

        .info-block-title {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 10px;
            letter-spacing: 1px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 6px;
        }

        .info-block p {
            font-size: 12.5px;
            margin: 4px 0;
            color: var(--text-muted);
            line-height: 1.45;
        }

        .info-block strong {
            color: var(--text-main);
            font-weight: 700;
        }

        /* ----- GATE PASS TABLE SECTION ----- */
        .gatepass-table-container {
            padding: 25px 40px 20px 40px;
        }

        .gatepass-table {
            width: 100%;
            border-collapse: collapse;
            border: 1.5px solid #000000;
        }

        .gatepass-table th {
            background-color: #f1f5f9;
            color: #000000;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1.5px solid #000000;
        }

        .gatepass-table td {
            padding: 10px 14px;
            font-size: 13px;
            color: #000000;
            border: 1px solid #000000;
            height: 36px;
            vertical-align: middle;
        }

        .gatepass-table tr.blank-row td {
            height: 34px;
        }

        .gatepass-table tfoot th {
            background-color: #f8fafc;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 800;
            border: 1.5px solid #000000;
        }

        .item-title {
            font-weight: 600;
            color: #000000;
        }

        .item-sub {
            font-size: 11px;
            color: #555555;
            margin-top: 2px;
        }

        /* ----- TERMS & CONDITIONS ----- */
        .terms-conditions-block {
            margin: 0 40px 20px 40px;
            padding: 16px 20px;
            border-radius: 4px;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0f172a;
        }

        .terms-conditions-block h4 {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            margin-bottom: 8px;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .terms-conditions-block ul {
            font-size: 11.5px;
            color: var(--text-muted);
            padding-left: 20px;
            line-height: 1.5;
        }
        
        .terms-conditions-block ul li {
            margin-bottom: 3px;
        }

        /* ----- ADDRESSES ----- */
        .addresses-block {
            margin: 0 40px 20px 40px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .address-item {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-size: 11.5px;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.45;
        }

        .address-item svg {
            width: 14px;
            height: 14px;
            fill: #475569;
            margin-top: 2px;
            flex-shrink: 0;
        }

        /* ----- FOOTER ----- */
        .invoice-footer {
            text-align: center;
            padding: 14px 20px;
            background-color: var(--primary);
            color: #ffffff;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        /* Floating print button */
        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 12px 24px;
            background-color: #0f172a;
            color: #ffffff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13.5px;
            font-weight: 600;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.25);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        .print-button:hover {
            background-color: #1e293b;
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.35);
        }

        /* ----- PRINT STYLES ----- */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 6mm;
            }
            body { 
                padding: 0; 
                background-color: #ffffff !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            .invoice-container { 
                border: none !important;
                box-shadow: none !important;
                max-width: 100% !important; 
                width: 100% !important;
                border-radius: 0 !important;
            }
            
            .invoice-header { padding: 15px 25px 12px 25px; }
            .document-title { font-size: 26px; }
            .main-logo { max-width: 140px; max-height: 55px; }
            .invoice-info-section { padding: 12px 25px; gap: 12px; }
            .info-block { padding: 10px 14px; }
            .info-block p, .info-block-title { font-size: 10.5px; margin: 3px 0; }
            .gatepass-table-container { padding: 12px 25px; }
            .gatepass-table th, .gatepass-table td { padding: 6px 10px; font-size: 11px; }
            .gatepass-table tr.blank-row td { height: 26px; }
            .terms-conditions-block { margin: 0 25px 12px 25px; padding: 10px 14px; }
            .terms-conditions-block h4 { font-size: 10px; margin-bottom: 5px; }
            .terms-conditions-block ul { font-size: 9.5px; }
            .addresses-block { margin: 0 25px 12px 25px; gap: 5px; }
            .address-item { font-size: 9.5px; }
            .invoice-footer { padding: 8px; font-size: 9px; }

            .print-button, .no-print {
                display: none !important;
            }

            .invoice-header, .invoice-info-section, .info-block, .gatepass-table th, .gatepass-table td, 
            .terms-conditions-block, .invoice-footer {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
        }
    </style>
</head>
<body>

<!-- SCREEN ACTION TOOLBAR -->
<div class="no-print">
    <div class="toolbar-left">
        <a href="{{ route('local.sale.receipt', $sale->id) }}" class="btn-link-action" title="View Thermal Receipt">
            <i class="fa fa-receipt"></i> Thermal Receipt
        </a>
        <a href="{{ route('local.sale.invoice', $sale->id) }}" class="btn-link-action" title="View A4 Invoice">
            <i class="fa fa-file-invoice"></i> A4 Invoice
        </a>
        <a href="{{ route('show-local-sale', $sale->id) }}" class="btn-link-action" title="View Details">
            <i class="fa fa-eye"></i> Details
        </a>
        <a href="{{ route('all-local-sale') }}" class="btn-link-action" title="Sales List">
            <i class="fa fa-list"></i> Sales List
        </a>
    </div>
    <div class="toolbar-right">
        <button onclick="window.print()" class="btn-link-action btn-primary-print" title="Print Gate Pass (Ctrl+P)">
            <i class="fa fa-print"></i> Print Gate Pass <kbd>Ctrl+P</kbd>
        </button>
    </div>
</div>

<div class="invoice-container">
    <!-- HEADER -->
    <div class="invoice-header">
        <div class="inv-header-left">
            @if(!empty($appSettings['company_logo']))
                <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" class="main-logo">
            @else
                <h1 class="company-name">{{ $appSettings['company_name'] ?? 'Company Name' }}</h1>
            @endif
            
            <div class="company-contact">
                @if(!empty($appSettings['company_social']))
                <div class="contact-item">
                    <i class="fa-brands fa-facebook" style="width: 14px; color: #475569;"></i>
                    <a href="{{ strpos($appSettings['company_social'], 'http') === 0 ? $appSettings['company_social'] : 'https://'.$appSettings['company_social'] }}" target="_blank">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_social'], '/')) }}</a>
                </div>
                @endif
                @if(!empty($appSettings['company_phone']))
                <div class="contact-item">
                    <i class="fa fa-phone" style="width: 14px; color: #475569;"></i>
                    <span>{{ $appSettings['company_phone'] }}</span>
                </div>
                @endif
                @if(!empty($appSettings['company_website']))
                <div class="contact-item">
                    <i class="fa fa-globe" style="width: 14px; color: #475569;"></i>
                    <a href="{{ strpos($appSettings['company_website'], 'http') === 0 ? $appSettings['company_website'] : 'https://'.$appSettings['company_website'] }}" target="_blank">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_website'], '/')) }}</a>
                </div>
                @endif
            </div>
        </div>
        
        <div class="inv-header-right">
            <h2 class="document-title">GATE PASS</h2>
            <div class="document-subtitle">Goods Delivery Challan</div>
            <div class="meta-container">
                <div class="meta-details">
                    <p>{{ \Carbon\Carbon::parse($sale->sale_date)->format('F j, Y') }}</p>
                    <p>GATE PASS #: <span>GP-{{ $sale->invoice_number }}</span></p>
                    <p>INVOICE REF #: <span>#{{ $sale->invoice_number }}</span></p>
                </div>
            </div>
            @if(!empty($appSettings['secondary_logo']))
                <div style="display: flex; flex-direction: column; align-items: center; margin-top: 6px;">
                    <img src="{{ asset('storage/' . $appSettings['secondary_logo']) }}" alt="Secondary Logo" class="secondary-logo">
                    <span style="font-size: 9.5px; font-weight: 700; margin-top: 3px; color: var(--text-main); letter-spacing: 0.5px;">PEC CERTIFIED</span>
                </div>
            @endif
        </div>
    </div>

    <!-- INFO SECTION -->
    <div class="invoice-info-section">
        <div class="info-block">
            <div class="info-block-title">Delivered To / Consignee</div>
            <p><strong>{{ $party->business_name ?? $party->name }}</strong></p>
            <p>{{ !empty($party->address) ? $party->address : 'Address Not Provided' }}</p>
            <p>Phone: {{ $party->phone ?: 'N/A' }}</p>
        </div>
        
        <div class="info-block">
            <div class="info-block-title">Dispatch Information</div>
            <p><strong>Gate Pass No:</strong> GP-{{ $sale->invoice_number }}</p>
            <p><strong>Ref Invoice:</strong> #{{ $sale->invoice_number }}</p>
            <p><strong>Issue Date:</strong> {{ date('d-M-Y', strtotime($sale->sale_date)) }}</p>
            @if(!empty($sale->delivery_date))
                <p><strong>Delivery Date:</strong> {{ date('d-M-Y', strtotime($sale->delivery_date)) }}</p>
            @endif
        </div>
    </div>

    <!-- PRODUCTS TABLE (EXACT GATE PASS LAYOUT) -->
    <div class="gatepass-table-container">
        <table class="gatepass-table">
            <thead>
                <tr>
                    <th style="width: 10%; text-align: center;">S.No.</th>
                    <th style="width: 65%; text-align: left;">Description</th>
                    <th style="width: 25%; text-align: center;">Quantity</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = json_decode($sale->item) ?? [];
                    $heights = json_decode($sale->height) ?? [];
                    $widths = json_decode($sale->width) ?? [];
                    $qtys = json_decode($sale->qty) ?? [];
                    $units = json_decode($sale->unit) ?? [];
                    $rowCount = 0;
                    $totalQty = 0;
                @endphp

                @foreach($items as $i => $item)
                    @if(!empty($item))
                        @php
                            $rowCount++;
                            $qtyVal = (float)($qtys[$i] ?? 0);
                            $totalQty += $qtyVal;
                            $unitVal = !empty($units[$i]) ? strtoupper($units[$i]) : '';
                            $hasDims = (!empty($heights[$i]) || !empty($widths[$i]));
                        @endphp
                        <tr>
                            <td style="text-align: center; font-weight: 600;">{{ $rowCount }}</td>
                            <td style="text-align: left;">
                                <div class="item-title">{{ $item }}</div>
                                @if($hasDims)
                                    <div class="item-sub">Dim: {{ $heights[$i] ?? '-' }} &times; {{ $widths[$i] ?? '-' }} {{ $unitVal }}</div>
                                @endif
                            </td>
                            <td style="text-align: center; font-weight: 700;">
                                {{ $qtyVal == (int)$qtyVal ? (int)$qtyVal : $qtyVal }}
                                @if(!empty($unitVal))
                                    <span style="font-weight: 500; font-size: 11px; margin-left: 3px;">{{ $unitVal }}</span>
                                @endif
                            </td>
                        </tr>
                    @endif
                @endforeach

                {{-- Blank rows padding to reproduce gate pass voucher look --}}
                @php
                    $minRows = 8;
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
                    <th style="text-align: center;">{{ $totalQty == (int)$totalQty ? (int)$totalQty : $totalQty }}</th>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- TERMS & CONDITIONS FROM SETTINGS -->
    @if(!empty($appSettings['invoice_terms']))
    <div class="terms-conditions-block">
        <h4>Gate Instructions &amp; Terms</h4>
        <ul>
            @foreach(explode("\n", str_replace("\r", "", $appSettings['invoice_terms'])) as $term)
                @if(trim($term) !== '')
                    <li>{{ $term }}</li>
                @endif
            @endforeach
            <li>Please inspect goods and quantity carefully at the gate before accepting delivery.</li>
        </ul>
    </div>
    @endif

    <!-- COMPANY ADDRESSES FROM SETTINGS -->
    <div class="addresses-block">
        @if(!empty($appSettings['company_address']))
        <div class="address-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0c-88.37 0-160 71.63-160 160 0 102.34 140.09 335.79 150.93 346.52 4.79 5.37 13.35 5.37 18.14 0C275.91 495.79 416 262.34 416 160c0-88.37-71.63-160-160-160zm0 240c-44.18 0-80-35.82-80-80s35.82-80 80-80 80 35.82 80 80-35.82 80-80 80z"/></svg>
            <span>{!! nl2br(e($appSettings['company_address'])) !!}</span>
        </div>
        @endif
        @if(!empty($appSettings['company_address_2']))
        <div class="address-item">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M256 0c-88.37 0-160 71.63-160 160 0 102.34 140.09 335.79 150.93 346.52 4.79 5.37 13.35 5.37 18.14 0C275.91 495.79 416 262.34 416 160c0-88.37-71.63-160-160-160zm0 240c-44.18 0-80-35.82-80-80s35.82-80 80-80 80 35.82 80 80-35.82 80-80 80z"/></svg>
            <span>{!! nl2br(e($appSettings['company_address_2'])) !!}</span>
        </div>
        @endif
    </div>

    <!-- FOOTER -->
    <div class="invoice-footer">
        Powered by ProWave Software Solutions | Contact: 0317-3836223
    </div>
</div>

<!-- FLOATING PRINT BUTTON -->
<button class="print-button" onclick="window.print()">
    <i class="fa fa-print"></i>
    Print Gate Pass
</button>

</body>
</html>