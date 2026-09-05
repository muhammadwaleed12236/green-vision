@include('admin_panel.include.header_include')

<style>
    /* General Table Styling */
    .sale-table th {
        /* vertical-alig/n: middle; */
        font-weight: 600;
        background-color: #f8f9fa;
        color: #333;
        border-bottom: 2px solid #dee2e6;
        padding: 10px 5px !important;
        font-size: 13px;
        text-align: center;
    }

    .sale-table td {
        vertical-align: middle;
        padding: 8px 5px !important;
    }

    /* Column Widths - unified (10 cols; H/W reserved in both modes) */
    .sale-table th:nth-child(1) { width: 4%; }  /* # */
    .sale-table th:nth-child(2) { width: 25%; } /* Product Name */
    .sale-table th:nth-child(3) { width: 7%; }  /* Avail Stock */
    .sale-table th:nth-child(4) { width: 8%; }  /* H */
    .sale-table th:nth-child(5) { width: 8%; }  /* W */
    .sale-table th:nth-child(6) { width: 10%; } /* Qty/Feet */
    .sale-table th:nth-child(7) { width: 8%; }  /* Unit */
    .sale-table th:nth-child(8) { width: 11%; } /* Price/unit */
    .sale-table th:nth-child(9) { width: 11%; } /* amount */
    .sale-table th:nth-child(10) { width: 8%; } /* Action */

    /* Input & Select Styling */
    .sale-table .form-control {
        border-radius: 4px;
        border: 1px solid #ced4da;
        font-size: 13px;
        padding: 6px 8px;
        height: 34px; /* Consistent height */
    }

    .sale-table .form-control:focus {
        border-color: #637381;
        box-shadow: none;
    }

    /* Readonly inputs styling */
    .readonly-box {
        background-color: #f8f9fa !important;
        color: #6c757d;
        cursor: default;
    }

    /* Qty Box Styling */
    .qty-box {
        display: flex;
        gap: 0;
        align-items: center;
        border: 1px solid #ced4da;
        border-radius: 4px;
        overflow: hidden;
    }
    
    .qty-box .btn {
        padding: 0 8px;
        height: 32px;
        border-radius: 0;
        font-weight: bold;
        background: #f1f3f5;
        border: none;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .qty-box .btn:hover {
        background: #e2e6ea;
    }

    .qty-box .qty {
        border: none;
        border-right: 1px solid #ced4da;
        border-left: 1px solid #ced4da;
        border-radius: 0;
        height: 32px;
        padding: 0;
        width: 100%;
        text-align: center;
    }

    .btn-action {
        width: 28px;
        height: 28px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 4px;
        font-size: 16px;
        line-height: 1;
    }

    /* Type Select styling specifically */
    .row-type {
        font-weight: 500;
        color: #212529;
    }

    /* ── Product Autocomplete ── */
    .autocomplete-list {
        position: fixed;
        z-index: 99999;
        background: #fff;
        border: 1px solid #dee2e6;
        max-height: 260px;
        overflow-y: auto;
        border-radius: 6px;
        box-shadow: 0 4px 16px rgba(0,0,0,0.13);
    }
    .autocomplete-item {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 7px 10px;
        cursor: pointer;
        border-bottom: 1px solid #f1f3f5;
        transition: background 0.13s;
        min-height: 46px;
    }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.ac-active { background: #f0f4ff; }
    .ac-thumb {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        object-fit: cover;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #f8f9fa;
    }
    .ac-thumb-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        flex-shrink: 0;
        border: 1px solid #e2e8f0;
        background: #f1f3f5;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
    }
    .ac-info { display: flex; flex-direction: column; min-width: 0; }
    .ac-name { font-size: 13px; font-weight: 500; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ac-sku  { font-size: 11px; color: #868e96; margin-top: 1px; }

    /* ── Selected product display ── */
    .selected-product-display {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 5px 10px;
        background: #f8f9fa;
        border: 1px solid #ced4da;
        border-radius: 6px;
        cursor: pointer;
        min-height: 44px;
        width: 100%;
        transition: all 0.2s ease;
    }
    .selected-product-display:hover { 
        border-color: #adb5bd;
        background: #fff;
        box-shadow: 0 2px 5px rgba(0,0,0,0.04);
    }
    .sel-thumb {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        flex-shrink: 0;
        background: #fff;
    }
    .sel-thumb-placeholder {
        width: 34px;
        height: 34px;
        border-radius: 5px;
        border: 1px solid #e2e8f0;
        background: #e9ecef;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #adb5bd;
        flex-shrink: 0;
    }
    .sel-info { display: flex; flex-direction: column; min-width: 0; flex: 1; justify-content: center; }
    .sel-name { font-size: 13.5px; font-weight: 600; color: #212529; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; line-height: 1.2; }
    .sel-sku  { font-size: 11px; color: #6c757d; margin-top: 2px; }
    .sel-clear {
        margin-left: auto;
        color: #adb5bd;
        font-size: 15px;
        cursor: pointer;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border-radius: 4px;
        transition: all 0.15s;
    }
    .sel-clear:hover { color: #e03131; background: #ffe3e3; }
    .item-input.ac-hidden { display: none; }

    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       ============================================ */

    /* Sale items table never scrolls horizontally - cells wrap to fit */
    .ls-wrap {
        overflow-x: hidden;
    }
    .ls-wrap .sale-table {
        table-layout: fixed;
        width: 100%;
        min-width: 0;
    }
    .ls-wrap .sale-table th,
    .ls-wrap .sale-table td {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .ls-wrap .sale-table th:nth-child(1), .ls-wrap .sale-table td:nth-child(1) { width: 4%; }
    .ls-wrap .sale-table th:nth-child(2), .ls-wrap .sale-table td:nth-child(2) { width: 25%; }
    .ls-wrap .sale-table th:nth-child(3), .ls-wrap .sale-table td:nth-child(3) { width: 7%; }
    .ls-wrap .sale-table th:nth-child(4), .ls-wrap .sale-table td:nth-child(4) { width: 8%; }
    .ls-wrap .sale-table th:nth-child(5), .ls-wrap .sale-table td:nth-child(5) { width: 8%; }
    .ls-wrap .sale-table th:nth-child(6), .ls-wrap .sale-table td:nth-child(6) { width: 10%; }
    .ls-wrap .sale-table th:nth-child(7), .ls-wrap .sale-table td:nth-child(7) { width: 8%; }
    .ls-wrap .sale-table th:nth-child(8), .ls-wrap .sale-table td:nth-child(8) { width: 11%; }
    .ls-wrap .sale-table th:nth-child(9), .ls-wrap .sale-table td:nth-child(9) { width: 11%; }
    .ls-wrap .sale-table th:nth-child(10), .ls-wrap .sale-table td:nth-child(10) { width: 8%; }

    /* H/W reserved space in both modes; only visible on manual rows */
    .sale-table .hw-cell { visibility: hidden; }
    .sale-table tr.manual-mode .hw-cell { visibility: visible; }

    /* ---------- Page header (Estimate/Sale/Booking + date) ---------- */
    @media (max-width: 575.98px) {
        .sale-head {
            flex-direction: column;
            align-items: flex-start !important;
            gap: 10px;
        }
        .sale-head-actions {
            width: 100%;
            flex-direction: column;
            align-items: stretch !important;
            gap: 10px;
        }
        .sale-head-actions .btn-group {
            width: 100%;
            flex-wrap: wrap;
        }
        .sale-head-actions .btn-group .sale-type-label {
            flex: 1;
        }
        .sale-head-actions .date-box {
            width: 100%;
            max-width: 100% !important;
        }
        .page-title h4 { font-size: 1.05rem; }
        .page-title h6 { font-size: 0.85rem; }
    }

    /* ---------- Sale items table -> stacked cards (phone & small tablet) ---------- */
    @media (max-width: 767.98px) {
        .ls-wrap .sale-table thead {
            display: none;
        }
        .ls-wrap .sale-table,
        .ls-wrap .sale-table tbody,
        .ls-wrap .sale-table tr,
        .ls-wrap .sale-table td {
            display: block;
            width: 100% !important;
            box-sizing: border-box;
        }
        .ls-wrap .sale-table {
            border: 0 !important;
        }
        .ls-wrap .sale-table tbody {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ls-wrap .sale-table tbody tr {
            background: #fff;
            border: 1px solid #eef2f7;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 6px 12px;
            margin: 0 !important;
        }
        .ls-wrap .sale-table tbody tr:hover {
            background: #fff;
        }
        .ls-wrap .sale-table td {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 7px 0 !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            background: transparent !important;
            font-size: 0.85rem;
            color: #1e293b;
            text-align: right;
            white-space: normal;
            word-break: break-word;
        }
        .ls-wrap .sale-table td:last-child {
            border-bottom: 0 !important;
        }
        .ls-wrap .sale-table td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .ls-wrap .sale-table td .form-control,
        .ls-wrap .sale-table td .input-group,
        .ls-wrap .sale-table td .qty-box {
            width: 100%;
            max-width: 100%;
        }
        .ls-wrap .sale-table td .qty-box .qty {
            flex: 1;
        }
        .ls-wrap .sale-table td.hw-cell { display: none !important; }
        .ls-wrap .sale-table tr.manual-mode td.hw-cell { display: flex !important; }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <form method="POST" action="{{ route('store-local-sale') }}">
                @csrf
                @if(isset($cloneEstimate))
                    <input type="hidden" name="estimate_id" value="{{ $cloneEstimate->id }}">
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3 sale-head">
                    <h4 class="mb-0">🧾 Job Order / Sale</h4>
                    <div class="d-flex gap-3 align-items-center sale-head-actions">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_estimate" value="estimate" {{ old('sale_type') == 'estimate' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_estimate">Estimate</label>

                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_sale" value="sale" {{ old('sale_type') == 'sale' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_sale">Sale</label>

                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_booking" value="booking" {{ old('sale_type', 'booking') == 'booking' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_booking">Booking</label>
                        </div>
                        <div class="date-box" style="max-width: 260px;">
                            <label class="small text-muted d-block mb-0">Sale Date & Time</label>
                            <input type="datetime-local" name="sale_date" class="form-control form-control-sm" value="{{ old('sale_date', date('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="row g-3">
                                 <div class="col-md-3">
                                     <label>Party Type</label>
                                     <select id="partyType" name="party_type" class="form-control">
                                         <option value="customer" {{ (old('party_type') ?? ($cloneEstimate?->party_type ?? '')) == 'customer' ? 'selected' : '' }}>Customer</option>
                                         <option value="vendor" {{ (old('party_type') ?? ($cloneEstimate?->party_type ?? '')) == 'vendor' ? 'selected' : '' }}>Vendor</option>
                                         <option value="walkin" {{ (old('party_type') ?? ($cloneEstimate?->party_type ?? '')) == 'walkin' ? 'selected' : '' }}>Walk-In</option>
                                     </select>
                                 </div>

                                <div class="col-md-3 party-box" id="customerBox">
                                    <label>Customer</label>
                                    <div class="d-flex">
                                        <div style="flex: 1;">
                                            <select class="form-control search" name="customer_id" id="customer">
                                                <option value="">Select</option>
                                                @foreach ($Customers as $c)
                                                    <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                                        data-address="{{ $c->address }}"
                                                        {{ old('customer_id') == $c->id ? 'selected' : '' }}>
                                                        {{ $c->customer_name ?? $c->shop_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-sm ms-2" data-bs-toggle="modal" data-bs-target="#quickAddCustomerModal" style="height: 38px;">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                </div>

                                 <div class="col-md-3 party-box d-none" id="vendorBox">
                                     <label>Vendor</label>
                                     <select class="form-control search" name="vendor_id" id="vendor">
                                         <option value="">Select</option>
                                         @foreach ($Vendors as $v)
                                             <option value="{{ $v->id }}" data-phone="{{ $v->Party_phone }}"
                                                 data-address="{{ $v->Party_address }}"
                                                 {{ (old('vendor_id') ?? ($cloneEstimate?->vendor_id ?? '')) == $v->id ? 'selected' : '' }}>
                                                 {{ $v->Party_name }}
                                             </option>
                                         @endforeach
                                     </select>
                                 </div>

                                 <div class="col-md-3 readonly-wrap">
                                     <label>Phone</label>
                                     <input id="phone" class="form-control readonly-box" readonly>
                                 </div>

                                 <div class="col-md-3 readonly-wrap">
                                     <label>Address</label>
                                     <input id="address" class="form-control readonly-box" readonly>
                                 </div>

                                 <div class="col-md-3 d-none" id="walkinName">
                                     <label>Name</label>
                                     <input name="walkin_name" class="form-control" value="{{ old('walkin_name') ?? ($cloneEstimate?->party_type === 'walkin' ? $cloneEstimate?->customer_shopname : '') }}">
                                 </div>

                                 <div class="col-md-3 d-none" id="walkinPhone">
                                     <label>Phone</label>
                                     <input name="walkin_phone" class="form-control" value="{{ old('walkin_phone') ?? ($cloneEstimate?->party_type === 'walkin' ? $cloneEstimate?->customer_phone : '') }}">
                                 </div>

                                 <div class="col-md-3 d-none" id="walkinAddress">
                                     <label>Address</label>
                                     <input name="walkin_address" class="form-control" value="{{ old('walkin_address') ?? ($cloneEstimate?->party_type === 'walkin' ? $cloneEstimate?->customer_address : '') }}">
                                 </div>
                            </div>
                        </div>
                    </div>
                </div>



                <div class="card mb-3">
                    <div class="card-body p-0">
                        <div class="table-responsive ls-wrap">
                            <table class="table table-borderless mb-0 sale-table">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="text-center">#</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Avail. Stock</th>
                                        <th class="text-center">H</th>
                                        <th class="text-center">W</th>
                                        <th class="text-center">Qty/Feet</th>
                                        <th>Unit</th>
                                        <th class="text-end">Price/unit</th>
                                        <th class="text-end">amount</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>

                                <tbody id="saleTableBody">
                                 @php
                                     $oldItemNames = old('item_name', []);
                                     $cloneItems = isset($cloneEstimate) ? json_decode($cloneEstimate?->item, true) : [];
                                     $cloneHeights = isset($cloneEstimate) ? json_decode($cloneEstimate?->height, true) : [];
                                     $cloneWidths = isset($cloneEstimate) ? json_decode($cloneEstimate?->width, true) : [];
                                     $cloneQtys = isset($cloneEstimate) ? json_decode($cloneEstimate?->qty, true) : [];
                                     $cloneUnits = isset($cloneEstimate) ? json_decode($cloneEstimate?->unit, true) : [];
                                     $cloneRates = isset($cloneEstimate) ? json_decode($cloneEstimate?->rate, true) : [];
                                     $cloneAmounts = isset($cloneEstimate) ? json_decode($cloneEstimate?->amount, true) : [];
                                     
                                     $rowCount = max(5, count($oldItemNames), count($cloneItems));
                                 @endphp
                                 @for($i=0; $i < $rowCount; $i++)
                                     <tr class="sale-row">
                                         <td class="text-center" data-label="#"> <span class="row-index">{{ $i + 1 }}</span></td>
                                         <td style="position:relative;" data-label="Product">
                                             <input type="hidden" name="item_id[]" class="item-id" value="{{ old('item_id.' . $i) }}">
                                             <input type="hidden" name="item_image_val[]" class="item-image-val" value="">
                                             <input type="hidden" name="item_sku_val[]" class="item-sku-val" value="">
                                             <div class="input-group input-group-sm">
                                                 <button type="button" class="btn btn-outline-secondary mode-toggle px-2" title="Toggle Search/Manual" tabindex="-1">
                                                     <i class="fas fa-search mode-icon"></i>
                                                 </button>
                                                 <input type="text" name="item_name[]" class="form-control item-input" autocomplete="off" placeholder="Search Product" data-mode="search" value="{{ old('item_name.' . $i) ?? ($cloneItems[$i] ?? '') }}">
                                             </div>
                                             <div class="selected-display d-none"></div>
                                             <div class="autocomplete-list d-none"></div>
                                         </td>
                                         <td data-label="Avail Stock">
                                             <input type="text" name="avail_stock[]" class="form-control avail-stock p-1 text-center readonly-box" value="" readonly tabindex="-1" placeholder="-">
                                         </td>
<td class="hw-cell" data-label="H">
                                             <input type="text" name="height[]" class="form-control h-input p-1 text-center" placeholder="H" value="{{ old('height.' . $i) ?? ($cloneHeights[$i] ?? '') }}">
                                         </td>
                                         <td class="hw-cell" data-label="W">
                                             <input type="text" name="width[]" class="form-control w-input p-1 text-center" placeholder="W" value="{{ old('width.' . $i) ?? ($cloneWidths[$i] ?? '') }}">
                                         </td>
                                         <td>
                                             <div class="qty-box">
                                                 <button type="button" class="btn qty-minus">−</button>
                                                 <input name="qty[]" class="form-control qty text-center" value="{{ old('qty.' . $i) ?? ($cloneQtys[$i] ?? 0) }}" placeholder="0">
                                                 <button type="button" class="btn qty-plus">+</button>
                                             </div>
                                         </td>
                                         <td data-label="Unit">
                                             <input type="text" name="unit[]" class="form-control unit p-1 text-center" placeholder="Unit" value="{{ old('unit.' . $i) ?? ($cloneUnits[$i] ?? '') }}" readonly>
                                         </td>
                                         <td data-label="Price"><input name="rate[]" class="form-control rate text-end" placeholder="0.00" value="{{ old('rate.' . $i) ?? ($cloneRates[$i] ?? '') }}"></td>
                                         <td data-label="Amount"><input name="amount[]" class="form-control item-total text-end" value="{{ old('amount.' . $i) ?? ($cloneAmounts[$i] ?? '0.00') }}" readonly></td>
                                         <td data-label="Action">
                                             <div class="d-flex gap-1 justify-content-center">
                                                 <button type="button" class="btn btn-success btn-action add-row">+</button>
                                                 <button type="button" class="btn btn-danger btn-action remove-row">×</button>
                                             </div>
                                         </td>
                                     </tr>
                                 @endfor
                                 </tbody>
                             </table>
                         </div>
                     </div>
                 </div>

                 <div class="card mb-3" id="deliveryPaymentPanel">
                     <div class="card-body">
                         <h6 class="mb-3 fw-bold text-primary">Delivery & Payment Details</h6>
                         <div class="row g-3 mb-3">
                             <div class="col-md-4">
                                 <label class="fw-bold">Delivery Date <span class="text-danger">*</span></label>
                                 <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') ?? ($cloneEstimate?->delivery_date ?? '') }}" required>
                             </div>
                             <div class="col-md-4">
                                 <label class="fw-bold">Notify Before (Days)</label>
                                 <input type="number" name="notify_days_before" class="form-control" value="{{ old('notify_days_before') ?? ($cloneEstimate?->notify_days_before ?? '2') }}" min="1" max="30">
                                 <small class="text-muted">System will notify you X days before delivery</small>
                             </div>
                         </div>
                     </div>
                 </div>

                 <div class="card mb-3">
                     <div class="card-body py-3 px-3">

                         {{-- ===== SALE / BOOKING LAYOUT (two cards) ===== --}}
                         <div id="saleBookingLayout">
                             <div class="row g-3">
                                 {{-- LEFT: Payment Account --}}
                                 <div class="col-md-6">
                                     <div class="card h-100 border">
                                         <div class="card-body">
                                             <h6 class="fw-bold text-primary mb-2" id="paymentAccountLabel">Payment Account</h6>
                                             <div id="accountContainer">
                                                 <div id="splitRows" class="d-flex flex-column gap-1"></div>
                                                 <div class="d-flex align-items-center justify-content-end gap-2 pt-1">
                                                     <span class="small text-muted">Total</span>
                                                     <strong id="splitTotal" class="text-primary small">0</strong>
                                                 </div>
                                                 <input type="hidden" name="split_payments_json" id="splitPaymentsJson" value="{{ $cloneEstimate?->split_payments ? json_encode($cloneEstimate->split_payments) : '' }}">
                                             </div>
                                         </div>
                                     </div>
                                 </div>

                                 {{-- RIGHT: Financial Summary --}}
                                 <div class="col-md-6">
                                     <div class="card h-100 border">
                                         <div class="card-body">
                                             <h6 class="fw-bold text-primary mb-2">Financial Summary</h6>
                                             <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                                 <span class="small text-muted">Gross Total</span>
                                                 <input id="grandTotal" class="form-control form-control-sm readonly-box w-50 text-end" value="{{ $cloneEstimate?->grand_total ?? '' }}" readonly>
                                             </div>
                                             <div class="d-flex align-items-center justify-content-between gap-2 mb-2" id="discountContainer">
                                                 <span class="small text-muted">Discount</span>
                                                 <input name="gross_discount" class="form-control form-control-sm w-50 text-end" value="{{ old('gross_discount') ?? ($cloneEstimate?->discount_value ?? '0') }}">
                                             </div>
                                             <div class="d-flex align-items-center justify-content-between gap-2 mb-2" id="advanceContainer">
                                                 <span class="small text-muted" id="advanceLabel">Advance</span>
                                                 <input id="advance" name="advance_amount" class="form-control form-control-sm w-50 text-end" value="{{ old('advance_amount') }}">
                                             </div>
                                             <div class="d-flex align-items-center justify-content-between gap-2" id="remainingContainer">
                                                 <span class="small text-muted">Remaining Balance</span>
                                                 <input id="remaining" class="form-control form-control-sm readonly-box w-50 text-end" value="{{ $cloneEstimate?->remaining_amount ?? '' }}" readonly>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         {{-- ===== ESTIMATE LAYOUT (vertical, no payment) ===== --}}
                         <div id="estimateLayout" class="d-none">
                             <div class="card border">
                                 <div class="card-body">
                                     <h6 class="fw-bold text-primary mb-3">Financial Summary</h6>
                                     <div class="row g-3">
                                         <div class="col-md-4">
                                             <label class="small text-muted d-block mb-1">Gross Total</label>
                                             <input id="grandTotalE" class="form-control form-control-sm readonly-box text-end" value="{{ $cloneEstimate?->grand_total ?? '' }}" readonly>
                                         </div>
                                         <div class="col-md-4">
                                             <label class="small text-muted d-block mb-1">Discount</label>
                                             <input name="gross_discount" id="discountE" class="form-control form-control-sm text-end" value="{{ old('gross_discount') ?? ($cloneEstimate?->discount_value ?? '0') }}">
                                         </div>
                                         <div class="col-md-4">
                                             <label class="small text-muted d-block mb-1">Net Total</label>
                                             <input id="netE" class="form-control form-control-sm readonly-box text-end" value="{{ $cloneEstimate?->net_amount ?? '' }}" readonly>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>

                         {{-- SAVE BUTTON (right aligned) --}}
                         <div class="d-flex justify-content-end mt-3">
                             <input type="hidden" name="net_amount" id="netAmount">
                             <button type="submit" class="btn btn-primary btn-save-order">Save Job Order</button>
                         </div>
                     </div>
                 </div>
             </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success',
            text: "{{ session('success') }}",
            timer: 2000,
            showConfirmButton: false
        });
    </script>
@endif

@if(session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: "{{ session('error') }}"
        });
    </script>
@endif

@if ($errors->any())
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Validation Error',
            html: `{!! implode('<br>', $errors->all()) !!}`
        });
    </script>
@endif

@include('admin_panel.include.footer_include')

<script>
    $('#partyType').on('change', function () {
        let t = this.value;

        $('#customerBox,#vendorBox').addClass('d-none');
        $('#walkinName,#walkinPhone,#walkinAddress').addClass('d-none');
        $('.readonly-wrap').addClass('d-none');

        $('#advance').prop('readonly', false);
        $('#advanceLabel').text('Advance');
        $('#remainingContainer').removeClass('d-none');

        if (t === 'customer') {
            $('#customerBox').removeClass('d-none');
            $('.readonly-wrap').removeClass('d-none');
        }

        if (t === 'vendor') {
            $('#vendorBox').removeClass('d-none');
            $('.readonly-wrap').removeClass('d-none');
        }

        if (t === 'walkin') {
            $('#walkinName,#walkinPhone,#walkinAddress').removeClass('d-none');
            $('#advance').val($('#grandTotal').val()).prop('readonly', true);
            $('#advanceLabel').text('Paid Amount');
            let saleType = $('input[name="sale_type"]:checked').val();
            if (saleType === 'estimate') {
                $('#advance').val('');
                $('#advanceContainer').show();
                $('#remainingContainer').show();
                $('#remainingContainer span').text('Net Total');
            } else {
                $('#remaining').val('0');
                $('#remainingContainer').addClass('d-none');
            }
        }

        calcGrand();
    });

    $('#partyType').trigger('change');

    $('#customer').on('change', function () {
        let o = $('option:selected', this);
        $('#phone').val(o.data('phone') || '');
        $('#address').val(o.data('address') || '');
    });

    $('#vendor').on('change', function () {
        let o = $('option:selected', this);
        $('#phone').val(o.data('phone') || '');
        $('#address').val(o.data('address') || '');
    });

    function calcRow(r) {
        let rate = parseFloat(r.find('.rate').val()) || 0;
        let qty = parseFloat(r.find('.qty').val());
        let avail = parseFloat(r.find('.avail-stock').val());

        if (isNaN(qty) || qty < 0) qty = 0;

        let saleType = $('input[name="sale_type"]:checked').val();
        if (saleType === 'sale' && !isNaN(avail) && !r.hasClass('manual-mode')) {
            if (qty > avail) {
                r.find('.qty').addClass('border-danger text-danger');
            } else {
                r.find('.qty').removeClass('border-danger text-danger');
            }
        } else {
            r.find('.qty').removeClass('border-danger text-danger');
        }

        let total = rate * qty;
        r.find('.item-total').val(total.toFixed(2));

        calcGrand();
    }

    $(document).on('input change', '.rate,.qty', e => {
        calcRow($(e.target).closest('tr'));
    });

    // Auto-calc Qty from Height (H) x Width (W) - only in manual mode
    $(document).on('input change', '.h-input,.w-input', function () {
        let r = $(this).closest('tr');
        if (r.find('.item-input').attr('data-mode') !== 'manual') return;
        let h = parseFloat(r.find('.h-input').val()) || 0;
        let w = parseFloat(r.find('.w-input').val()) || 0;
        r.find('.qty').val((h * w).toFixed(2));
        calcRow(r);
    });

    $(document).on('input change', '.item-total', e => {
        calcGrand();
    });

    // Auto-Append Logic: Detect input in last row
    $(document).on('input', '.sale-row:last input', function() {
        let lastRow = $('.sale-row:last');
        let hasValue = false;
        lastRow.find('input').each(function() {
            if($(this).val()) hasValue = true;
        });

        if(hasValue) {
            addNewRow();
        }
    });

    function updateRowNumbers() {
        $('.sale-row').each(function(index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function addNewRow() {
        let r = $('.sale-row:first').clone();
        r.find('input').val('');
        r.find('.qty').val(0).removeClass('border-danger text-danger');
        r.find('.avail-stock').val('');
        r.find('.h-input').val('');
        r.find('.w-input').val('');
        r.find('.rate').val('');
        r.find('.item-total').val('0.00');
        r.find('.unit').val('');
        r.find('.autocomplete-list').addClass('d-none').empty();
        r.find('.selected-display').addClass('d-none').empty();
        r.find('.input-group').removeClass('d-none');
        r.find('.item-input').removeClass('ac-hidden').val('');
        r.removeClass('manual-mode');
        
        // Reset toggle to search mode
        let input = r.find('.item-input');
        input.attr('data-mode', 'search');
        input.attr('placeholder', 'Search Product');
        let btn = r.find('.mode-toggle');
        btn.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
        btn.find('.mode-icon').removeClass('fa-keyboard').addClass('fa-search');
        
        $('#saleTableBody').append(r);
        updateRowNumbers();
    }

    $(document).on('click', '.qty-plus', e => {
        let r = $(e.target).closest('tr');
        r.find('.qty').val(+r.find('.qty').val() + 1);
        calcRow(r);
    });

    $(document).on('click', '.qty-minus', e => {
        let r = $(e.target).closest('tr');
        r.find('.qty').val(Math.max(0, +r.find('.qty').val() - 1));
        calcRow(r);
    });

    $('.add-row').click(() => {
        addNewRow();
    });

    $(document).on('click', '.remove-row', e => {
        if ($('.sale-row').length > 1) {
            $(e.target).closest('tr').remove();
            calcGrand();
            updateRowNumbers();
        }
    });

    function calcGrand() {
        let g = 0;
        $('.item-total').each((_, e) => g += +e.value || 0);
        let d = +($('#estimateLayout').hasClass('d-none')
            ? $('#saleBookingLayout [name="gross_discount"]').val()
            : $('#estimateLayout [name="gross_discount"]').val()) || 0;
        let net = g - d;
        $('#grandTotal').val(g.toFixed(2));
        $('#grandTotalE').val(g.toFixed(2));
        $('#netAmount').val(net.toFixed(2));
        $('#netE').val(net.toFixed(2));
        
        if ($('#partyType').val() === 'walkin') {
            let saleType = $('input[name="sale_type"]:checked').val();
            if (saleType === 'estimate') {
                $('#remaining').val(net.toFixed(2));
            } else {
                $('#advance').val(net.toFixed(2));
                $('#remaining').val('0');
            }
        } else {
            let adv = +$('#advance').val() || 0;
            $('#remaining').val((net - adv).toFixed(2));
        }
    }

    $('#advance,[name="gross_discount"]').on('input', calcGrand);

    $('form').on('submit', function () {
        calcGrand();
        
        let validItems = 0;
        let errors = [];
        let saleType = $('input[name="sale_type"]:checked').val();
        $('.sale-row').each(function() {
            let row = $(this);
            let input = row.find('.item-input');
            let itemName = input.val().trim();
            let itemId = row.find('.item-id').val();
            let qty = parseFloat(row.find('.qty').val()) || 0;
            let isManualMode = input.attr('data-mode') === 'manual';

            if(itemName) {
                validItems++;
                
                if (!isManualMode && !itemId) {
                    errors.push(`Row ${row.find('.row-index').text()}: Product "${itemName}" not found. Please select a valid product from the dropdown.`);
                }
                
                if (qty <= 0) {
                    errors.push(`Row ${row.find('.row-index').text()}: Quantity for product "${itemName}" must be greater than 0.`);
                }
                
                let avail = parseFloat(row.find('.avail-stock').val());
                if (saleType === 'sale' && !isManualMode && !isNaN(avail) && qty > avail) {
                    errors.push(`Row ${row.find('.row-index').text()}: Quantity (${qty}) for product "${itemName}" exceeds Available Stock (${avail}).`);
                }
            }
        });

        let advance = parseFloat($('#advance').val()) || 0;
        let partyType = $('#partyType').val();
        let hasSplit = (($('#splitPaymentsJson').val() || '') !== '');
        if (saleType !== 'estimate' && (advance > 0 || partyType === 'walkin') && !hasSplit && !$('select[name="account_id"]').val()) {
            errors.push('Please select a Payment Account or add a Split Payment.');
        }

        // Walk-in: payment must equal the Paid Amount (full amount)
        if (partyType === 'walkin' && saleType !== 'estimate') {
            let paidAmount = parseFloat($('#advance').val()) || 0;
            let splitSum = 0;
            try {
                splitSum = (JSON.parse($('#splitPaymentsJson').val() || '[]') || []).reduce((s, d) => s + parseFloat(d.amount || 0), 0);
            } catch (e) { splitSum = 0; }
            let payment = splitSum > 0 ? splitSum : paidAmount;
            if (Math.abs(payment - paidAmount) > 0.01) {
                errors.push('Walk-in must pay the full amount (RS ' + paidAmount.toFixed(2) + ').');
            }
        }

        if (validItems === 0) {
            Swal.fire('Error', 'Please add at least one item', 'error');
            return false;
        }

        if (errors.length > 0) {
            let errorHtml = '<ul style="text-align:left; margin:0; padding-left:20px;">';
            errors.forEach(function(msg) {
                errorHtml += '<li>' + msg + '</li>';
            });
            errorHtml += '</ul>';

            Swal.fire({
                icon: 'error',
                title: 'Validation Error',
                html: errorHtml
            });
            return false;
        }
    });

    $(document).ready(function() {
        function updateAccountLabel() {
            let saleType = $('input[name="sale_type"]:checked').val();
            let adv = parseFloat($('#advance').val()) || 0;
            let partyType = $('#partyType').val();
            let hasSplit = (($('#splitPaymentsJson').val() || '') !== '');
            if (saleType !== 'estimate' && (adv > 0 || partyType === 'walkin') && !hasSplit) {
                $('#paymentAccountLabel').html('Payment Account <span class="text-danger">*</span>');
            } else {
                $('#paymentAccountLabel').html('Payment Account');
            }
        }

        $('#advance').on('input change', updateAccountLabel);
        $('#partyType').on('change', updateAccountLabel);
        updateAccountLabel();

        updateRowNumbers();
        calcGrand();

        // Sale/Estimate toggle logic
        function handleSaleTypeToggle() {
            let saleType = $('input[name="sale_type"]:checked').val();
            
            // Update Active State Styles
            $('.sale-type-label').removeClass('btn-primary text-white shadow-sm border-primary').addClass('btn-outline-secondary');
            let checkedId = $('input[name="sale_type"]:checked').attr('id');
            $('label[for="' + checkedId + '"]').removeClass('btn-outline-secondary').addClass('btn-primary text-white shadow-sm border-primary');

            if (saleType === 'sale') {
                $('#deliveryPaymentPanel').hide();
                $('#saleBookingLayout').removeClass('d-none');
                $('#estimateLayout').addClass('d-none');
$('#saleBookingLayout :input').prop('disabled', false);
                $('#estimateLayout :input').prop('disabled', true);
                $('#discountContainer').show();
                $('#advanceContainer').show();
                $('#accountContainer').show();
                $('#remainingContainer').show();
                $('#remainingContainer span').text('Remaining');
                $('[name="delivery_date"], [name="notify_days_before"]').prop('required', false).val('').prop('disabled', true);
                $('.btn-save-order').text('Save Sale');
            } else if (saleType === 'estimate') {
                $('#deliveryPaymentPanel').hide();
                $('#saleBookingLayout').addClass('d-none');
                $('#estimateLayout').removeClass('d-none');
                $('#estimateLayout :input').prop('disabled', false);
                $('#saleBookingLayout :input').prop('disabled', true);
                $('[name="delivery_date"], [name="notify_days_before"]').prop('required', false).val('').prop('disabled', true);
                $('.btn-save-order').text('Save Estimate');
            } else { // booking
                $('#deliveryPaymentPanel').show();
                $('#saleBookingLayout').removeClass('d-none');
                $('#estimateLayout').addClass('d-none');
                $('#saleBookingLayout :input').prop('disabled', false);
                $('#estimateLayout :input').prop('disabled', true);
                $('#discountContainer').show();
                $('#advanceContainer').show();
                $('#remainingContainer').show();
                $('#remainingContainer span').text('Remaining');
                $('[name="delivery_date"], [name="notify_days_before"]').prop('required', true).prop('disabled', false);
                if (!$('[name="notify_days_before"]').val()) {
                    $('[name="notify_days_before"]').val('2');
                }
                $('.btn-save-order').text('Save Job Order');
            }

            $('.sale-row').each(function() {
                calcRow($(this));
            });

            let discountVal = $('#estimateLayout').hasClass('d-none')
                ? $('#saleBookingLayout [name="gross_discount"]').val()
                : $('#estimateLayout [name="gross_discount"]').val();
            $('#saleBookingLayout [name="gross_discount"]').val(discountVal);
            $('#estimateLayout [name="gross_discount"]').val(discountVal);
        }

        $('input[name="sale_type"]').on('change', handleSaleTypeToggle);
        handleSaleTypeToggle(); // Run on load

        // Populate phone/address from old selection
        let selCust = $('#customer').find('option:selected');
        if (selCust.val()) { $('#phone').val(selCust.data('phone') || ''); $('#address').val(selCust.data('address') || ''); }
        let selVend = $('#vendor').find('option:selected');
        if (selVend.val()) { $('#phone').val(selVend.data('phone') || ''); $('#address').val(selVend.data('address') || ''); }

        // Row Input Mode Toggle
        $(document).on('click', '.mode-toggle', function() {
            let btn = $(this);
            let icon = btn.find('.mode-icon');
            let input = btn.siblings('.item-input');
            let td = btn.closest('td');
            
            if (input.attr('data-mode') === 'search') {
                input.attr('data-mode', 'manual');
                icon.removeClass('fa-search').addClass('fa-keyboard');
                btn.removeClass('btn-outline-secondary').addClass('btn-outline-primary');
                input.attr('placeholder', 'Manual Entry');
                input.closest('td').find('.autocomplete-list').addClass('d-none');
                input.closest('tr').addClass('manual-mode');
            } else {
                input.attr('data-mode', 'search');
                icon.removeClass('fa-keyboard').addClass('fa-search');
                btn.removeClass('btn-outline-primary').addClass('btn-outline-secondary');
                input.attr('placeholder', 'Search Product');
                input.closest('tr').removeClass('manual-mode');
            }
            input.focus();
        });

        // Single global autocomplete dropdown
        let $acList = $('<div class="autocomplete-list d-none"></div>').appendTo('body');

        // ── Storage base URL ──
        const STORAGE_URL = "{{ asset('storage') }}";

        function acThumb(image) {
            if (image) {
                return `<img src="${STORAGE_URL}/${image}" class="ac-thumb" onerror="this.outerHTML='<div class=ac-thumb-placeholder><svg width=16 height=16 fill=none viewBox=\'0 0 24 24\'><rect width=24 height=24 rx=4 fill=\'#e9ecef\'/><path d=\'M5 19l4-5 3 4 4-6 5 7H5z\' fill=\'#adb5bd\'/></svg></div>'">`;
            }
            return `<div class="ac-thumb-placeholder"><svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="4" fill="#e9ecef"/><path d="M5 19l4-5 3 4 4-6 5 7H5z" fill="#adb5bd"/></svg></div>`;
        }

        function selThumb(image) {
            if (image) {
                return `<img src="${STORAGE_URL}/${image}" class="sel-thumb" onerror="this.outerHTML='<div class=sel-thumb-placeholder><svg width=16 height=16 fill=none viewBox=\'0 0 24 24\'><rect width=24 height=24 rx=4 fill=\'#e9ecef\'/><path d=\'M5 19l4-5 3 4 4-6 5 7H5z\' fill=\'#adb5bd\'/></svg></div>'">`;
            }
            return `<div class="sel-thumb-placeholder"><svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect width="24" height="24" rx="4" fill="#e9ecef"/><path d="M5 19l4-5 3 4 4-6 5 7H5z" fill="#adb5bd"/></svg></div>`;
        }

        function showSelectedProduct(row, it) {
            let skuHtml = it.item_code ? `<span class="sel-sku">${it.item_code}</span>` : '';
            let html = `<div class="selected-product-display">
                ${selThumb(it.image)}
                <div class="sel-info">
                    <span class="sel-name">${it.item_name}</span>
                    ${skuHtml}
                </div>
                <span class="sel-clear" title="Clear"><i class="fas fa-times"></i></span>
            </div>`;
            row.find('.selected-display').html(html).removeClass('d-none');
            row.find('.input-group').addClass('d-none');
            row.find('.item-input').addClass('ac-hidden').val(it.item_name);
            row.find('.item-id').val(it.id);
            row.find('.item-image-val').val(it.image || '');
            row.find('.item-sku-val').val(it.item_code || '');
        }

        function fetchProducts(input, q) {
            let row = input.closest('tr');
            if (input.attr('data-mode') === 'manual') { $acList.addClass('d-none'); return; }
            $acList.data('row', row);
            $.ajax({
                url: "{{ route('get.items') }}",
                type: "GET",
                data: { q: q },
                success: function (res) {
                    let rect = input[0].getBoundingClientRect();
                    $acList.css({ left: rect.left + 'px', top: rect.bottom + 'px', width: input.outerWidth() + 'px' });
                    if (!Array.isArray(res) || res.length === 0) {
                        $acList.empty().removeClass('d-none');
                        $('<div class="autocomplete-item text-danger not-found-item" style="cursor:default; font-weight:500; justify-content:center;"></div>').text('Not found').appendTo($acList);
                        return;
                    }
                    $acList.empty().removeClass('d-none');
                    res.forEach(it => {
                        let skuHtml = it.item_code ? `<span class="ac-sku">${it.item_code}</span>` : '';
                        $(`<div class="autocomplete-item"></div>`)
                            .append(acThumb(it.image))
                            .append(`<div class="ac-info"><span class="ac-name">${it.item_name}</span>${skuHtml}</div>`)
                            .data('item', it)
                            .appendTo($acList);
                    });
                },
                error: function () { $acList.addClass('d-none'); }
            });
        }

        // On focus: show all products; on input: filter by typed query
        $(document).on('focus', '.item-input', function () { fetchProducts($(this), ''); });
        $(document).on('input', '.item-input', function () { 
            let input = $(this);
            input.closest('tr').find('.item-id').val('');
            fetchProducts(input, input.val().trim()); 
        });

        // Hide autocomplete when clicking outside
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.item-input, .autocomplete-list, .selected-product-display').length) {
                $acList.addClass('d-none');
            }
        });

        // Select item from autocomplete
        $(document).on('click', '.autocomplete-item:not(.not-found-item)', function () {
            let it = $(this).data('item');
            let row = $acList.data('row');

            if (!row || !row.length) return;

            // Rate & stock
            let price = parseFloat(it.retail_price) || parseFloat(it.wholesale_price) || 0;
            row.find('.rate').val(price);
            row.find('.unit').val(it.unit || 'pcs');
            row.find('.avail-stock').val(it.initial_stock !== null && it.initial_stock !== undefined ? it.initial_stock : 0);

            $acList.addClass('d-none');

            showSelectedProduct(row, it);
            calcRow(row);
        });

        // Clear selected product
        $(document).on('click', '.sel-clear', function () {
            let row = $(this).closest('tr');
            row.find('.selected-display').addClass('d-none').empty();
            row.find('.input-group').removeClass('d-none');
            row.find('.item-input').removeClass('ac-hidden').val('').focus();
            row.find('.item-id').val('');
            row.find('.item-image-val').val('');
            row.find('.item-sku-val').val('');
            row.find('.rate').val('');
            row.find('.avail-stock').val('');
            calcRow(row);
        });

        // Reposition autocomplete on scroll/resize
        $(window).on('scroll resize', function () {
            if ($acList.hasClass('d-none')) return;
            let row = $acList.data('row');
            if (row && row.length) {
                let input = row.find('.item-input');
                let rect = input[0].getBoundingClientRect();
                $acList.css({
                    left: rect.left + 'px',
                    top: rect.bottom + 'px'
                });
            }
        });
        // Quick Add Customer AJAX
        $('#quickAddCustomerForm').on('submit', function(e) {
            e.preventDefault();
            let btn = $('#saveQuickCustomerBtn');
            btn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: '{{ route("customer.store") }}',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if(response.success) {
                        // Add new option to dropdown
                        let newOption = new Option(response.customer.name, response.customer.id, true, true);
                        $(newOption).attr('data-phone', response.customer.phone_number || '');
                        $(newOption).attr('data-address', response.customer.address || '');
                        $('#customer').append(newOption).trigger('change');
                        
                        // Close modal and reset form
                        $('#quickAddCustomerModal').modal('hide');
                        $('#quickAddCustomerForm')[0].reset();
                        
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Customer added successfully!'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Error adding customer.'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error adding customer. Please check the inputs.'
                    });
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save');
                }
            });
        });

    });

    // ================= SPLIT PAYMENT (multiple accounts) =================
    const splitAccountOptions = function () {
        let html = '<select class="form-control form-control-sm split-account">';
        html += '<option value="">Select Account</option>';
        @foreach($Accounts as $account)
            html += '<option value="{{ $account->id }}" data-name="{{ $account->name }}">{{ $account->name }}</option>';
        @endforeach
        html += '</select>';
        return html;
    };

    function renderSplitRow(accountId, amount, isFirst) {
        let row = $('<div class="split-row d-flex gap-2 align-items-center w-100"></div>');

        let $sel = $(splitAccountOptions());
        $sel.css({ 'flex': '1.25 1 0', 'min-width': '0' });
        if (accountId) {
            $sel.val(String(accountId));
        }

        let $amt = $('<input type="text" inputmode="decimal" class="form-control form-control-sm split-amount" placeholder="Amt">');
        $amt.css({ 'flex': '1 1 0', 'min-width': '0' });
        if (amount) $amt.val(amount);

        let $btn;
        if (isFirst) {
            $btn = $('<button type="button" class="btn btn-outline-primary btn-sm split-add" title="Add another account" style="width:31px;height:31px;flex:0 0 31px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-size:16px;line-height:1;">+</button>');
        } else {
            $btn = $('<button type="button" class="btn btn-outline-danger btn-sm split-remove" title="Remove this account" style="width:31px;height:31px;flex:0 0 31px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-size:15px;line-height:1;">&#128465;</button>');
        }

        row.append($sel, $amt, $btn);
        return row;
    }

    function splitRowCount() {
        return $('#splitRows .split-row').length;
    }

    function refreshFirstRowButton() {
        // First row always shows "+ Add", subsequent rows show trash
        $('#splitRows .split-row').each(function (i) {
            let $row = $(this);
            let $btn = $row.find('.split-add, .split-remove');
            if (i === 0) {
                if (!$btn.hasClass('split-add')) {
                    $row.find('.split-remove').replaceWith(
                        $('<button type="button" class="btn btn-outline-primary btn-sm split-add" title="Add another account" style="width:31px;height:31px;flex:0 0 31px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-size:16px;line-height:1;">+</button>')
                    );
                }
            } else {
                if (!$btn.hasClass('split-remove')) {
                    $row.find('.split-add').replaceWith(
                        $('<button type="button" class="btn btn-outline-danger btn-sm split-remove" title="Remove this account" style="width:31px;height:31px;flex:0 0 31px;padding:0;display:inline-flex;align-items:center;justify-content:center;border-radius:6px;font-size:15px;line-height:1;">&#128465;</button>')
                    );
                }
            }
        });
    }

    function ensurePrimaryRow() {
        if (splitRowCount() === 0) {
            $('#splitRows').append(renderSplitRow(null, null, true));
        }
        refreshFirstRowButton();
        syncSplitJson();
    }

    function syncSplitJson() {
        let data = [];
        $('#splitRows .split-row').each(function () {
            let $sel = $(this).find('.split-account');
            let accountId = $sel.val();
            let amount = parseFloat($(this).find('.split-amount').val()) || 0;
            let name = $sel.find('option:selected').data('name') || '';
            if (accountId && amount > 0) {
                data.push({ account_id: accountId, account_name: name, amount: amount });
            }
        });
        $('#splitPaymentsJson').val(JSON.stringify(data));
        let total = data.reduce((s, d) => s + parseFloat(d.amount), 0);
        $('#splitTotal').text(total.toFixed(2));
        if (data.length) {
            // Recalculate Remaining from the split-paid amount
            let net = parseFloat($('#netAmount').val()) || 0;
            if ($('#partyType').val() === 'walkin') {
                // Walk-in always pays the full amount, so Paid Amount stays = net total
                let saleType = $('input[name="sale_type"]:checked').val();
                $('#advance').val(net.toFixed(2));
                if (saleType === 'estimate') {
                    $('#remaining').val(net.toFixed(2));
                } else {
                    $('#remaining').val('0');
                }
            } else {
                $('#advance').val(total);
                $('#remaining').val((net - total).toFixed(2));
            }
        }
        refreshFirstRowButton();
    }

    // "+ Add" on primary row -> append a new dynamic row
    $('#splitRows').on('click', '.split-add', function () {
        if (splitRowCount() >= 10) return;
        $('#splitRows').append(renderSplitRow(null, null, false));
        refreshFirstRowButton();
    });

    $('#splitRows').on('input', '.split-amount', function () {
        syncSplitJson();
    });

    $('#splitRows').on('change', '.split-account', function () {
        syncSplitJson();
    });

    // Trash button on dynamic rows -> delete that row only (primary row never removable)
    $('#splitRows').on('click', '.split-remove', function () {
        $(this).closest('.split-row').remove();
        ensurePrimaryRow();
    });

    // Preload existing split payments (e.g. from a cloned estimate)
    let existingSplit = $('#splitPaymentsJson').val();
    if (existingSplit) {
        try {
            let rows = JSON.parse(existingSplit);
            if (rows.length) {
                $('#splitRows').append(renderSplitRow(rows[0].account_id, rows[0].amount, true));
                for (let i = 1; i < rows.length; i++) {
                    $('#splitRows').append(renderSplitRow(rows[i].account_id, rows[i].amount, false));
                }
            } else {
                ensurePrimaryRow();
            }
            syncSplitJson();
        } catch (e) {
            ensurePrimaryRow();
        }
    } else {
        ensurePrimaryRow();
    }
</script>

<!-- Quick Add Customer Modal -->
<div class="modal fade" id="quickAddCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="quickAddCustomerForm">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Quick Add Customer</h5>
                    <button type="button" class="btn-close text-black" data-bs-dismiss="modal">X</button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Customer Name *</label>
                        <input type="text" name="customer_name" class="form-control mt-2" required placeholder="Enter Name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address</label>
                        <input type="text" name="address" class="form-control mt-2" placeholder="Enter Address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone_number" class="form-control mt-2" placeholder="Enter Phone Number">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Opening Balance</label>
                        <input type="number" name="opening_balance" class="form-control mt-2" value="0" placeholder="Enter Opening Balance">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary" id="saveQuickCustomerBtn">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
