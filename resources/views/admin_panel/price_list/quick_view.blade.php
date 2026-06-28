<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate List - Green Vision</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Fira+Sans:wght@300;400;500;600;700&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Fira Sans', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .rate-container {
            max-width: 900px;
            margin: 0 auto;
        }
        .company-header {
            text-align: center;
            color: #fff;
            margin-bottom: 30px;
            padding: 30px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
        }
        .company-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        .company-header p {
            opacity: 0.9;
            font-size: 1.1rem;
        }
        .category-card {
            background: #fff;
            border-radius: 20px;
            margin-bottom: 25px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }
        .category-header {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: #fff;
            padding: 15px 25px;
            font-size: 1.2rem;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .category-header i {
            margin-right: 12px;
            font-size: 1.3rem;
        }
        .price-row {
            padding: 18px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s;
        }
        .price-row:hover {
            background: #f8fafc;
            padding-left: 35px;
        }
        .price-row:last-child {
            border-bottom: none;
        }
        .product-info h5 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 4px;
        }
        .product-info p {
            color: #6b7280;
            font-size: 0.9rem;
            margin: 0;
        }
        .price-box {
            text-align: right;
        }
        .price-amount {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            color: #fff;
            padding: 8px 20px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 1.1rem;
            display: inline-block;
        }
        .price-unit {
            display: block;
            color: #9ca3af;
            font-size: 0.8rem;
            margin-top: 5px;
        }
        .footer-note {
            text-align: center;
            color: rgba(255,255,255,0.8);
            margin-top: 30px;
            padding: 20px;
        }
        .print-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #fff;
            color: #667eea;
            border: none;
            font-size: 24px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            cursor: pointer;
            transition: all 0.3s;
        }
        .print-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .company-header {
                background: none;
                color: #000;
            }
            .print-btn {
                display: none;
            }
            .category-card {
                box-shadow: none;
                border: 1px solid #ddd;
            }
        }
        @media (max-width: 600px) {
            .company-header h1 { font-size: 1.8rem; }
            .price-row {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            .price-box { text-align: center; }
        }
    </style>
</head>
<body>

<div class="rate-container">
    {{-- Company Header --}}
    <div class="company-header">
        @if($appSettings['company_logo'])
            <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" style="max-height: 60px; margin-bottom: 10px;">
        @endif
        <p><i class="fa fa-map-marker-alt me-1"></i> {{ $appSettings['company_address'] }}</p>
        <p><i class="fa fa-phone me-1"></i> {{ $appSettings['company_phone'] }}</p>
    </div>

    {{-- Rate Cards --}}
    @forelse($priceLists as $header => $items)
        <div class="category-card">
            <div class="category-header">
                <i class="fa fa-layer-group"></i>
                {{ $header ?: 'General Items' }}
            </div>
            @foreach($items as $item)
                <div class="price-row">
                    <div class="product-info">
                        <h5>{{ $item->product_name }}</h5>
                        @if($item->description)
                            <p>{{ $item->description }}</p>
                        @endif
                    </div>
                    <div class="price-box">
                        <span class="price-amount">PKR {{ number_format($item->rate, 0) }}</span>
                        <span class="price-unit">{{ $item->unit }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="category-card">
            <div class="p-5 text-center">
                <i class="fa fa-clipboard-list fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No rate items available</h4>
            </div>
        </div>
    @endforelse

    {{-- Footer --}}
    <div class="footer-note">
        <p><i class="fa fa-info-circle me-1"></i> Prices are subject to change without prior notice</p>
        <small>Last Updated: {{ date('d M Y') }}</small>
    </div>
</div>

{{-- Print Button --}}
<button class="print-btn" onclick="window.print()" title="Print Rate List">
    <i class="fa fa-print"></i>
</button>

</body>
</html>
