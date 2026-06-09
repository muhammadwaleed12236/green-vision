<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $voucher->voucher_no }} - {{ ucfirst($voucher->voucher_type) }} Voucher</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Fira Sans', sans-serif; background: #f5f5f5; padding: 20px; }
        .voucher-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #333;
        }
        .voucher-header {
            background: {{ $voucher->voucher_type == 'payment' ? '#dc2626' : ($voucher->voucher_type == 'receipt' ? '#16a34a' : '#2563eb') }};
            color: #fff;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .voucher-header h1 { font-size: 24px; }
        .voucher-no {
            font-family: 'Courier New', monospace;
            font-size: 18px;
            font-weight: bold;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 5px;
        }
        .company-info {
            padding: 15px 20px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
        }
        .company-name { font-size: 20px; font-weight: bold; color: #333; }
        .voucher-date { text-align: right; }
        .voucher-body { padding: 25px; }
        .party-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .party-info label { font-size: 12px; color: #666; text-transform: uppercase; }
        .party-info h3 { font-size: 18px; color: #333; margin-top: 5px; }
        .party-type {
            display: inline-block;
            background: #e5e7eb;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            text-transform: uppercase;
            margin-top: 5px;
        }
        .amount-box {
            background: {{ $voucher->voucher_type == 'payment' ? '#fef2f2' : '#f0fdf4' }};
            border: 2px solid {{ $voucher->voucher_type == 'payment' ? '#fca5a5' : '#86efac' }};
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            margin: 20px 0;
        }
        .amount-box label { font-size: 14px; color: #666; }
        .amount-box h2 {
            font-size: 36px;
            color: {{ $voucher->voucher_type == 'payment' ? '#dc2626' : '#16a34a' }};
            margin-top: 5px;
        }
        .amount-words {
            font-style: italic;
            color: #666;
            margin-top: 10px;
            font-size: 14px;
        }
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
        }
        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .detail-item label { font-size: 11px; color: #666; text-transform: uppercase; }
        .detail-item span { display: block; font-weight: 600; margin-top: 3px; }
        .narration-box {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .narration-box label { font-size: 12px; color: #666; text-transform: uppercase; }
        .narration-box p { margin-top: 5px; }
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            padding-top: 30px;
        }
        .signature-box {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-top: 1px solid #333;
            padding-top: 10px;
            font-size: 12px;
        }
        .voucher-footer {
            background: #f8f9fa;
            padding: 10px 20px;
            text-align: center;
            font-size: 11px;
            color: #666;
            border-top: 1px solid #ddd;
        }
        @media print {
            body { background: #fff; padding: 0; }
            .no-print { display: none; }
            .voucher-container { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="background: #3b82f6; color: #fff; border: none; padding: 10px 30px; border-radius: 5px; cursor: pointer; font-size: 16px;">
            <i class="fas fa-print"></i> Print Voucher
        </button>
        <button onclick="window.close()" style="background: #6b7280; color: #fff; border: none; padding: 10px 30px; border-radius: 5px; cursor: pointer; font-size: 16px; margin-left: 10px;">
            Close
        </button>
    </div>

    <div class="voucher-container">
        <div class="voucher-header">
            <h1>
                @if($voucher->voucher_type == 'payment')
                    ⬆ PAYMENT VOUCHER
                @elseif($voucher->voucher_type == 'receipt')
                    ⬇ RECEIPT VOUCHER
                @else
                    📋 JOURNAL VOUCHER
                @endif
            </h1>
            <span class="voucher-no">{{ $voucher->voucher_no }}</span>
        </div>

        <div class="company-info">
            <div>
                <img src="{{ asset('assets/img/logo.png') }}" alt="Green Vision Logo" style="height: 50px; max-width: 250px; object-fit: contain;">
            </div>
            <div class="voucher-date">
                <label>Date</label>
                <h3>{{ \Carbon\Carbon::parse($voucher->voucher_date)->format('d M, Y') }}</h3>
            </div>
        </div>

        <div class="voucher-body">
            <div class="party-info">
                <label>{{ $voucher->voucher_type == 'payment' ? 'Paid To' : 'Received From' }}</label>
                <h3>{{ $voucher->party_name ?? 'N/A' }}</h3>
                <span class="party-type">{{ $voucher->party_type }}</span>
            </div>

            <div class="amount-box">
                <label>{{ $voucher->voucher_type == 'payment' ? 'Amount Paid' : 'Amount Received' }}</label>
                <h2>PKR {{ number_format($voucher->voucher_type == 'payment' ? $voucher->debit_amount : $voucher->credit_amount, 0) }}</h2>
                <div class="amount-words">
                    ({{ ucwords(\App\Models\JournalVoucher::amountInWords($voucher->voucher_type == 'payment' ? $voucher->debit_amount : $voucher->credit_amount)) }} Rupees Only)
                </div>
            </div>

            <div class="details-grid">
                <div class="detail-item">
                    <label>Payment Method</label>
                    <span>{{ ucfirst($voucher->payment_method) }}</span>
                </div>
                <div class="detail-item">
                    <label>Account Head</label>
                    <span>{{ $voucher->account_head ?? '-' }}</span>
                </div>
                @if($voucher->bank_name)
                <div class="detail-item">
                    <label>Bank Name</label>
                    <span>{{ $voucher->bank_name }}</span>
                </div>
                @endif
                @if($voucher->cheque_no)
                <div class="detail-item">
                    <label>Cheque No / Date</label>
                    <span>{{ $voucher->cheque_no }} {{ $voucher->cheque_date ? '/ ' . \Carbon\Carbon::parse($voucher->cheque_date)->format('d M Y') : '' }}</span>
                </div>
                @endif
            </div>

            @if($voucher->narration)
            <div class="narration-box">
                <label>Narration / Description</label>
                <p>{{ $voucher->narration }}</p>
            </div>
            @endif

            <div class="signatures">
                <div class="signature-box">
                    <div class="signature-line">Prepared By</div>
                </div>
                <!-- <div class="signature-box">
                    <div class="signature-line">Checked By</div>
                </div> -->
                <div class="signature-box">
                    <div class="signature-line">Recieved By</div>
                </div>
            </div>
        </div>

        <div class="voucher-footer">
            Printed on {{ now()->format('d M Y, h:i A') }} | This is a computer generated voucher
        </div>
    </div>

    <script>
        // Auto print on load
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>