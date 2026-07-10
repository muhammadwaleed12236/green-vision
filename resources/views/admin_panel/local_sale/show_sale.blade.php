@include('admin_panel.include.header_include')

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        :root {
            --primary: #000000; 
            --primary-light: #333333;
            --text-main: #1a1a1a;
            --text-muted: #555555;
            --bg-light: #ffffff; 
            --bg-gray: #f7f7f7;
            --border-color: #dddddd;
            --accent: #000000;
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

        .no-print {
            max-width: 900px; 
            margin: 0 auto 15px auto;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            padding: 8px 16px;
            background-color: #fff;
            color: var(--text-main);
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .back-btn:hover {
            background-color: var(--bg-gray);
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        /* ----- HEADER ----- */
        .invoice-header {
            padding: 40px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--border-color);
            background-color: #ffffff;
        }

        .inv-header-left {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 50%;
        }

        .main-logo {
            max-width: 170px;
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
            gap: 6px;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .contact-item svg {
            width: 14px;
            height: 14px;
            fill: var(--text-muted);
        }

        .contact-item a {
            color: var(--text-muted);
            text-decoration: none;
            transition: color 0.2s;
        }

        .contact-item a:hover {
            color: var(--primary);
        }

        .inv-header-right {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 15px;
            max-width: 50%;
        }

        .document-title {
            font-size: 32px;
            font-weight: 800;
            margin: 0;
            letter-spacing: 2px;
            color: var(--primary);
            text-transform: uppercase;
        }

        .meta-container {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 20px;
        }

        .secondary-logo {
            max-height: 85px;
            object-fit: contain;
        }

        .meta-details {
            text-align: right;
            font-size: 13px;
            color: var(--text-muted);
            line-height: 1.6;
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
            padding: 30px 40px;
            background-color: var(--bg-light);
            border-bottom: 1px solid var(--border-color);
        }

        .info-block {
            background: var(--bg-gray);
            padding: 20px 25px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .info-block-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 12px;
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .info-block p {
            font-size: 13px;
            margin: 6px 0;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .info-block strong {
            color: var(--text-main);
            font-weight: 700;
        }

        /* ----- TABLE SECTION ----- */
        .invoice-table-container {
            padding: 30px 40px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            overflow: hidden;
        }

        .invoice-table th {
            background-color: var(--primary);
            color: white;
            padding: 12px 15px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: none;
            text-align: left;
        }

        .invoice-table td {
            padding: 12px 15px;
            font-size: 13px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-main);
        }

        .invoice-table tbody tr:last-child td {
            border-bottom: none;
        }

        .invoice-table tbody tr:nth-child(even) {
            background-color: var(--bg-gray);
        }

        /* ----- SUMMARY SECTION ----- */
        .summary-section {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 30px;
            padding: 0 40px 30px 40px;
            align-items: start;
        }

        .summary-section.estimate-summary {
            display: flex;
            justify-content: flex-end;
        }

        .ledger-section {
            background: var(--bg-light);
            padding: 20px 25px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .ledger-title {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 12px;
            color: var(--primary);
            letter-spacing: 1px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 8px;
        }

        .ledger-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: var(--text-muted);
            border-bottom: 1px dashed var(--border-color);
        }

        .ledger-row span:last-child {
            font-weight: 600;
            color: var(--text-main);
        }

        .ledger-row:last-of-type {
            border-bottom: none;
            font-weight: 800;
            color: var(--primary);
            margin-top: 5px;
            padding-top: 10px;
            border-top: 1px solid var(--border-color);
            font-size: 13px;
        }

        .terms-note {
            font-size: 11px;
            color: var(--text-muted);
            margin-top: 12px;
            font-style: italic;
        }

        .totals-box {
            background: var(--bg-gray);
            padding: 20px 25px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .summary-section.estimate-summary .totals-box {
            width: 380px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 13px;
            color: var(--text-muted);
        }

        .total-row span:last-child {
            font-weight: 600;
            color: var(--text-main);
        }

        .total-row.grand-total {
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
            margin: 10px 0;
            padding: 12px 0;
            font-size: 15px;
            font-weight: 800;
            color: var(--text-main);
        }

        .total-row.balance-due {
            font-weight: 800;
            font-size: 15px;
            color: var(--primary);
            margin-top: 5px;
        }

        /* ----- TERMS & CONDITIONS ----- */
        .terms-conditions-block {
            margin: 0 40px 30px 40px;
            padding: 20px 25px;
            border-radius: 4px;
            background-color: var(--bg-gray);
            border: 1px solid var(--border-color);
            border-left: 4px solid var(--text-muted);
        }

        .terms-conditions-block h4 {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: var(--primary);
            letter-spacing: 1px;
        }

        .terms-conditions-block ul {
            font-size: 12px;
            color: var(--text-muted);
            padding-left: 20px;
            line-height: 1.6;
        }
        
        .terms-conditions-block ul li {
            margin-bottom: 4px;
        }

        .terms-conditions-block ul li:last-child {
            margin-bottom: 0;
        }

        /* ----- ADDRESSES ----- */
        .addresses-block {
            margin: 0 40px 20px 40px;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .address-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .address-item svg {
            width: 16px;
            height: 16px;
            fill: var(--text-muted);
            margin-top: 2px;
        }

        .address-item span {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-main);
            line-height: 1.5;
        }

        /* ----- SIGNATURES ----- */
        .signature-section {
            display: flex;
            justify-content: space-between;
            padding: 20px 60px 40px 60px;
        }

        .signature-box {
            text-align: center;
            width: 220px;
        }

        .signature-line {
            border-top: 1px solid var(--text-muted);
            margin-bottom: 10px;
        }

        .signature-label {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-main);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ----- FOOTER ----- */
        .invoice-footer {
            text-align: center;
            padding: 20px;
            background-color: var(--primary);
            color: white;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }

        .print-button {
            position: fixed;
            bottom: 30px;
            right: 30px;
            padding: 14px 28px;
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
            z-index: 100;
        }

        .print-button:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }

        /* ----- PRINT STYLES ----- */
        @media print {
            @page {
                size: auto;
                margin: 5mm;
            }
            body { 
                padding: 0; 
                background-color: white;
            }
            .invoice-container { 
                border: none;
                box-shadow: none;
                max-width: 100%; 
                border-radius: 0;
            }
            
            .invoice-header { padding: 20px 30px; }
            .document-title { font-size: 26px; }
            .main-logo { max-width: 140px; }
            .invoice-info-section { padding: 15px 30px; gap: 15px; }
            .info-block { padding: 12px 15px; }
            .info-block p, .info-block-title { font-size: 11px; margin: 4px 0; }
            .invoice-table-container { padding: 15px 30px; }
            .invoice-table th, .invoice-table td { padding: 8px 10px; font-size: 11px; }
            .summary-section { padding: 0 30px 15px 30px; gap: 15px; grid-template-columns: 1fr 300px; }
            .ledger-section, .totals-box { padding: 15px; }
            .ledger-row, .total-row { padding: 6px 0; font-size: 11px; }
            .total-row.grand-total { font-size: 13px; padding: 10px 0; margin: 8px 0; }
            .total-row.balance-due { font-size: 13px; margin-top: 4px; }
            .terms-conditions-block { margin: 0 30px 15px 30px; padding: 12px 15px; }
            .terms-conditions-block h4 { font-size: 11px; margin-bottom: 8px; }
            .terms-conditions-block ul { font-size: 10px; }
            .addresses-block { margin: 0 30px 15px 30px; gap: 8px; }
            .address-item span { font-size: 10px; }
            .signature-section { padding: 15px 40px 20px 40px; }
            .invoice-footer { padding: 10px; font-size: 9px; }

            .print-button, .no-print {
                display: none !important;
            }
            .invoice-info-section, .invoice-table tbody tr:nth-child(even), .totals-box, .terms-conditions-block, .invoice-footer, .invoice-table th, .back-btn, .info-block, .ledger-section {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
<div class="no-print" style="max-width: 900px; margin: 30px auto 0 auto;">
    <a href="{{ route('local-sale') }}" style="display: inline-block; padding: 10px 20px; background-color: #16a34a; color: #fff; text-decoration: none; border-radius: 4px; font-size: 14px; font-weight: 500;">
        &larr; Create New Sale
    </a>
</div>

<div class="invoice-container">
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
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M504 256C504 119 393 8 256 8S8 119 8 256c0 123.78 90.69 226.38 209.25 245V327.69h-63V256h63v-54.64c0-62.15 37-96.48 93.67-96.48 27.14 0 55.52 4.84 55.52 4.84v61h-31.28c-30.8 0-40.41 19.12-40.41 38.73V256h68.78l-11 71.69h-57.78V501C413.31 482.38 504 379.78 504 256z"/></svg>
                    <a href="{{ strpos($appSettings['company_social'], 'http') === 0 ? $appSettings['company_social'] : 'https://'.$appSettings['company_social'] }}" target="_blank">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_social'], '/')) }}</a>
                </div>
                @endif
                @if(!empty($appSettings['company_phone']))
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M164.9 24.6c-7.7-18.6-28-28.5-47.4-23.2l-88 24C12.1 30.2 0 46 0 64C0 311.4 200.6 512 448 512c18 0 33.8-12.1 38.6-29.5l24-88c5.3-19.4-4.6-39.7-23.2-47.4l-96-40c-16.3-6.8-35.2-2.1-46.3 11.6L304.7 368C234.3 334.7 177.3 277.7 144 207.3L193.3 167c13.7-11.2 18.4-30 11.6-46.3l-40-96z"/></svg>
                    <span>{{ $appSettings['company_phone'] }}</span>
                </div>
                @endif
                @if(!empty($appSettings['company_website']))
                <div class="contact-item">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M352 256c0 22.2-1.2 43.6-3.3 64H163.3c-2.2-20.4-3.3-41.8-3.3-64s1.2-43.6 3.3-64h185.4c2.2 20.4 3.3 41.8 3.3 64zm28.8-64h123.1c5.3 20.5 8.1 41.9 8.1 64s-2.8 43.5-8.1 64H380.8c2.1-20.6 3.2-42 3.2-64s-1.1-43.4-3.2-64zm112.6-32H376.7c-10-63.9-29.8-117.4-55.3-151.6c78.3 20.7 142 77.5 171.9 151.6zm-149.1 0H167.7c6.1-36.4 15.5-68.6 27-94.7c10.5-23.6 22.2-40.7 33.5-51.5C239.4 3.2 248.7 0 256 0s16.6 3.2 27.8 13.8c11.3 10.8 23 27.9 33.5 51.5c11.6 26 20.9 58.2 27 94.7zm-209 0H18.6C48.6 85.9 112.2 29.1 190.6 8.4C165.1 42.6 145.3 96.1 135.3 160zM8.1 192H131.2c-2.1 20.6-3.2 42-3.2 64s1.1 43.4 3.2 64H8.1C2.8 299.5 0 278.1 0 256s2.8-43.5 8.1-64zM194.7 446.6c-11.6-26-20.9-58.2-27-94.6H344.3c-6.1 36.4-15.5 68.6-27 94.6c-10.5 23.6-22.2 40.7-33.5 51.5C272.6 508.8 263.3 512 256 512s-16.6-3.2-27.8-13.8c-11.3-10.8-23-27.9-33.5-51.5zM135.3 352c10 63.9 29.8 117.4 55.3 151.6C112.2 482.9 48.6 426.1 18.6 352H135.3zm358.1 0c-30 74.1-93.6 130.9-171.9 151.6c25.5-34.2 45.2-87.7 55.3-151.6H493.4z"/></svg>
                    <a href="{{ strpos($appSettings['company_website'], 'http') === 0 ? $appSettings['company_website'] : 'https://'.$appSettings['company_website'] }}" target="_blank">{{ preg_replace('#^https?://#', '', rtrim($appSettings['company_website'], '/')) }}</a>
                </div>
                @endif
            </div>
        </div>
        
        <div class="inv-header-right">
            <h2 class="document-title">
                @if(strtolower($sale->sale_type) === 'estimate')
                    ESTIMATE
                @elseif(strtolower($sale->sale_type) === 'booking')
                    BOOKING
                @else
                    SALE
                @endif
            </h2>
            <div class="meta-container">
                <div class="meta-details">
                    <p>{{ \Carbon\Carbon::parse($sale->sale_date)->format('F j, Y') }}</p>
                    <p>REF #: <span>{{ $sale->invoice_number }}</span></p>
                </div>
            </div>
            @if(!empty($appSettings['secondary_logo']))
                <div style="display: flex; flex-direction: column; align-items: center; margin-top: 10px;">
                    <img src="{{ asset('storage/' . $appSettings['secondary_logo']) }}" alt="Secondary Logo" class="secondary-logo">
                    <span style="font-size: 10px; font-weight: 700; margin-top: 4px; color: var(--text-main); letter-spacing: 0.5px;">PEC CERTIFIED</span>
                </div>
            @endif
        </div>
    </div>

    <div class="invoice-info-section">
        <div class="info-block">
            <div class="info-block-title">Bill To</div>
            <p><strong>{{ $party->business_name ?? $party->name }}</strong></p>
            <p>{{ !empty($party->address) ? $party->address : 'Address Not Provided' }}</p>
            <p>Phone: {{ $party->phone ?: 'N/A' }}</p>
        </div>
        
        <div class="info-block">
            <div class="info-block-title">Invoice Details</div>
            <p><strong>Invoice No:</strong> #{{ $sale->invoice_number }}</p>
            <p><strong>Invoice Date:</strong> {{ date('d-M-Y', strtotime($sale->sale_date)) }}</p>
            @if(strtolower($sale->sale_type) !== 'estimate')
            <p><strong>Delivery Date:</strong> {{ !empty($sale->delivery_date) ? date('d-M-Y', strtotime($sale->delivery_date)) : 'Not Scheduled' }}</p>
            @endif
        </div>
    </div>

    <div class="invoice-table-container">
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="text-align: center; width: 5%;">#</th>
                    <th style="width: 45%;">Product Name</th>
                    <th style="text-align: center; width: 10%;">Qty</th>
                    <th style="text-align: center; width: 10%;">Unit</th>
                    <th style="text-align: right; width: 15%;">Price/Unit</th>
                    <th style="text-align: right; width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $items = json_decode($sale->item) ?? [];
                    $qtys = json_decode($sale->qty) ?? [];
                    $units = json_decode($sale->unit) ?? [];
                    $rates = json_decode($sale->rate) ?? [];
                    $amounts = json_decode($sale->amount) ?? [];
                @endphp
                @foreach($items as $i => $item)
                    @if(!empty($item))
                    <tr>
                        <td style="text-align: center; font-weight: 500;">{{ $loop->iteration }}</td>
                        <td><strong>{{ $item }}</strong></td>
                        <td style="text-align: center;">{{ ($qtys[$i] ?? 0) == 0 ? '-' : $qtys[$i] }}</td>
                        <td style="text-align: center;">{{ empty($units[$i]) ? '-' : strtoupper($units[$i]) }}</td>
                        <td style="text-align: right;">{{ number_format((float)($rates[$i] ?? 0), 2) }}</td>
                        <td style="text-align: right;"><strong>{{ number_format((float)($amounts[$i] ?? 0), 2) }}</strong></td>
                    </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-section {{ strtolower($sale->sale_type) === 'estimate' ? 'estimate-summary' : '' }}">
        @if(strtolower($sale->sale_type) !== 'estimate')
        <div class="ledger-section">
            <div class="ledger-title">Account Statement</div>
            <div class="ledger-row">
                <span>{{ $ledger_info->label_prev }}</span>
                <span>RS {{ number_format($ledger_info->previous_balance, 2) }}</span>
            </div>
            <div class="ledger-row">
                <span>Current Invoice Amount</span>
                <span>{{ $ledger_info->operator }} {{ number_format($sale->remaining_amount, 2) }}</span>
            </div>
            <div class="ledger-row">
                <span>{{ $ledger_info->label_curr }} (CLOSING)</span>
                <span>RS {{ number_format($ledger_info->current_balance, 2) }}</span>
            </div>
            <p class="terms-note">* Goods once sold will not be returned. Payment terms apply.</p>
        </div>
        @else
        <div style="display: none;"></div>
        @endif

        <div class="totals-box">
            <div class="total-row">
                <span>Sub Total</span>
                <span>{{ number_format($sale->grand_total, 2) }}</span>
            </div>
            @if($sale->discount_value > 0)
            <div class="total-row">
                <span>Discount</span>
                <span>- {{ number_format($sale->discount_value, 2) }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span>Net Total</span>
                <span>RS {{ number_format($sale->net_amount, 2) }}</span>
            </div>
            @if(strtolower($sale->sale_type) !== 'estimate')
            <div class="total-row">
                <span>{{ $sale->party_type === 'walkin' ? 'Amount Paid' : 'Advance Paid' }}</span>
                <span>{{ number_format($sale->advance_amount, 2) }}</span>
            </div>
            <div class="total-row balance-due">
                <span>Balance Due</span>
                <span>RS {{ number_format($sale->remaining_amount, 2) }}</span>
            </div>
            @endif
        </div>
    </div>

    @if(!empty($appSettings['invoice_terms']))
    <div class="terms-conditions-block">
        <h4>Terms &amp; Conditions</h4>
        <ul>
            @foreach(explode("\n", str_replace("\r", "", $appSettings['invoice_terms'])) as $term)
                @if(trim($term) !== '')
                    <li>{{ $term }}</li>
                @endif
            @endforeach
        </ul>
    </div>
    @endif

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

    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Customer Signature</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="signature-label">Authorized Signature</div>
        </div>
    </div>

    <div class="invoice-footer">
        Powered by ProWave Software Solutions | Contact: 0317-3836223
    </div>
    <!-- Action Footer container with single DynamicActionButton -->
    <div class="card-footer border-top p-3 bg-light text-end no-print">
        @if($sale->sale_type === 'estimate')
            <button id="dynamicActionBtn" class="btn btn-warning" data-id="{{ $sale->id }}" data-status="booking">
                <i class="fa fa-arrow-circle-right me-1"></i>Update to Booking
            </button>
            <a href="{{ route('local.sale.edit', $sale->id) }}" class="btn btn-primary ms-2">
                <i class="fa fa-edit me-1"></i>Edit Estimate
            </a>
        @elseif($sale->sale_type === 'booking')
            <button id="dynamicActionBtn" class="btn btn-success" data-id="{{ $sale->id }}" data-status="sale">
                <i class="fa fa-check-circle me-1"></i>Finalize Sale
            </button>
        @else
            <span class="badge bg-success p-2"><i class="fa fa-check-double me-1"></i>Completed Sale</span>
        @endif
        <a href="{{ route('local-sale', ['clone_from_estimate' => $sale->id]) }}" class="btn btn-outline-info ms-2">
            <i class="fa fa-copy me-1"></i>Clone Estimate
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const btn = document.getElementById('dynamicActionBtn');
    if (btn) {
        btn.addEventListener('click', function() {
            const transactionId = this.dataset.id;
            const targetStatus = this.dataset.status;

            Swal.fire({
                title: 'Confirm Transition',
                text: `Do you want to change this order status to ${targetStatus}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, proceed',
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return axios.put("{{ route('api.transactions.status-update') }}", {
                        transaction_id: transactionId,
                        status: targetStatus,
                        _token: "{{ csrf_token() }}"
                    })
                    .then(response => {
                        if (!response.data.success) {
                            throw new Error(response.data.message || 'Error updating status');
                        }
                        return response.data;
                    })
                    .catch(error => {
                        Swal.showValidationMessage(
                            `Request failed: ${error.response ? error.response.data.message : error.message}`
                        );
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: result.value.message,
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        if (result.value.sale_type === 'booking') {
                            window.location.reload();
                        } else {
                            window.location.reload();
                        }
                    });
                }
            });
        });
    }
});
</script>

@include('admin_panel.include.footer_include')
