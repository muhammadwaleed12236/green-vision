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

            <form method="POST" action="{{ route('local.sale.update', $original->id) }}">
                @csrf
                @method('PUT')

                <div class="d-flex justify-content-between align-items-center mb-3 sale-head">
                    <h4 class="mb-0">✏️ Edit Job Order</h4>
                    <div class="d-flex gap-3 align-items-center sale-head-actions">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_estimate" value="estimate" {{ old('sale_type', $original->sale_type) == 'estimate' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_estimate">Estimate</label>

                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_sale" value="sale" {{ old('sale_type', $original->sale_type) == 'sale' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_sale">Sale</label>

                            <input type="radio" class="btn-check" name="sale_type" id="sale_type_booking" value="booking" {{ old('sale_type', $original->sale_type) == 'booking' ? 'checked' : '' }} autocomplete="off">
                            <label class="btn btn-outline-secondary px-3 sale-type-label" for="sale_type_booking">Booking</label>
                        </div>
                        <div class="date-box" style="max-width: 260px;">
                            <label class="small text-muted d-block mb-0">Sale Date & Time</label>
                            <input type="datetime-local" name="sale_date" class="form-control form-control-sm" value="{{ old('sale_date', \Carbon\Carbon::parse($original->sale_date)->format('Y-m-d\TH:i')) }}">
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                {{-- ================= PARTY ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-3">

                        @php
                            $cloneEstimate = $original; // Reuse add_sale logic
                        @endphp
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
                            <select class="form-control search" name="customer_id" id="customer">
                                <option value="">Select</option>
                                @foreach ($Customers as $c)
                                    <option value="{{ $c->id }}" data-phone="{{ $c->phone_number }}"
                                        data-address="{{ $c->address }}"
                                        {{ (old('customer_id') ?? ($cloneEstimate?->customer_id ?? '')) == $c->id ? 'selected' : '' }}>
                                        {{ $c->customer_name ?? $c->shop_name }}
                                    </option>
                                @endforeach
                            </select>
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

                {{-- ================= ITEMS ================= --}}
                @php
                    $items = json_decode($original->item, true) ?? [];
                    $heights = json_decode($original->height, true) ?? [];
                    $widths = json_decode($original->width, true) ?? [];
                    $units = json_decode($original->unit, true) ?? [];
                    $rates = json_decode($original->rate, true) ?? [];
                    $qtys = json_decode($original->qty, true) ?? [];
                    $amounts = json_decode($original->amount, true) ?? [];
                    $rowManual = [];
                    foreach ($items as $i => $it) {
                        $rowManual[$i] = !empty($heights[$i] ?? null) || !empty($widths[$i] ?? null);
                    }
                @endphp

                <div class="card mb-3">
                    <div class="card-body p-0">
<div class="table-responsive ls-wrap">

                            <table class="table table-borderless mb-0 sale-table" id="saleTable">
                                <thead>
                                    <tr>
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

                                    @foreach($items as $i => $item)
                                        <tr class="sale-row{{ !empty($rowManual[$i]) ? ' manual-mode' : '' }}">
                                            <td class="text-center" data-label="#">
                                                <span class="row-index">{{ $i + 1 }}</span>
                                            </td>
                                            <td style="position:relative;" data-label="Product">
                                                <div class="input-group input-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary mode-toggle px-2{{ !empty($rowManual[$i]) ? ' btn-outline-primary' : '' }}" title="Toggle Search/Manual" tabindex="-1">
                                                        <i class="fas {{ !empty($rowManual[$i]) ? 'fa-keyboard' : 'fa-search' }} mode-icon"></i>
                                                    </button>
                                                    <input name="item_name[]" class="form-control item-input" value="{{ $item }}" autocomplete="off" placeholder="{{ !empty($rowManual[$i]) ? 'Manual Entry' : 'Search Product' }}" data-mode="{{ !empty($rowManual[$i]) ? 'manual' : 'search' }}">
                                                </div>
                                                <div class="autocomplete-list d-none"></div>
                                            </td>
                                            <td data-label="Avail Stock">
                                                @php $prod = \App\Models\Product::where('item_name', $item)->first(); $avail = $prod ? $prod->initial_stock : 0; @endphp
                                                <input type="text" name="avail_stock[]" class="form-control avail-stock p-1 text-center readonly-box" value="{{ $avail }}" readonly tabindex="-1" placeholder="-">
                                            </td>
                                            <td class="hw-cell" data-label="H">
                                            <input type="text" name="height[]" class="form-control h-input p-1 text-center" placeholder="H" value="{{ $heights[$i] ?? '' }}">
                                        </td>
                                        <td class="hw-cell" data-label="W">
                                            <input type="text" name="width[]" class="form-control w-input p-1 text-center" placeholder="W" value="{{ $widths[$i] ?? '' }}">
                                        </td>
                                            <td data-label="Qty">
                                                <div class="qty-box">
                                                    <button type="button" class="btn qty-minus">−</button>
                                                    <input name="qty[]" class="form-control qty text-center" value="{{ $qtys[$i] ?? 1 }}">
                                                    <button type="button" class="btn qty-plus">+</button>
                                                </div>
                                            </td>
                                            <td data-label="Unit">
                                                <input name="unit[]" class="form-control unit p-1 text-center" value="{{ $units[$i] ?? '' }}" readonly>
                                            </td>
                                            <td data-label="Price">
                                                <input name="rate[]" class="form-control rate text-end" value="{{ $rates[$i] ?? 0 }}">
                                            </td>
                                            <td data-label="Amount">
                                                <input name="amount[]" class="form-control item-total text-end" value="{{ $amounts[$i] ?? 0 }}" readonly>
                                            </td>
                                            <td data-label="Action">
                                                <div class="d-flex gap-1 justify-content-center">
                                                    <button type="button" class="btn btn-success btn-action add-row">+</button>
                                                    <button type="button" class="btn btn-danger btn-action remove-row">×</button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <!-- template for JS-added rows -->
                                    <tr class="sale-row d-none" id="rowTemplate">
                                        <td class="text-center" data-label="#"><span class="row-index"></span></td>
                                        <td style="position:relative;" data-label="Product">
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-outline-secondary mode-toggle px-2" title="Toggle Search/Manual" tabindex="-1">
                                                    <i class="fas fa-search mode-icon"></i>
                                                </button>
                                                <input name="item_name[]" class="form-control item-input" autocomplete="off" placeholder="Search Product" data-mode="search">
                                            </div>
                                            <div class="autocomplete-list d-none"></div>
                                        </td>
                                        <td data-label="Avail Stock">
                                            <input type="text" name="avail_stock[]" class="form-control avail-stock p-1 text-center readonly-box" value="" readonly tabindex="-1" placeholder="-">
                                        </td>
                                        <td class="hw-cell" data-label="H">
                                            <input type="text" name="height[]" class="form-control h-input p-1 text-center" placeholder="H" value="">
                                        </td>
                                        <td class="hw-cell" data-label="W">
                                            <input type="text" name="width[]" class="form-control w-input p-1 text-center" placeholder="W" value="">
                                        </td>
                                        <td data-label="Qty">
                                            <div class="qty-box">
                                                <button type="button" class="btn qty-minus">−</button>
                                                <input name="qty[]" class="form-control qty text-center" value="1">
                                                <button type="button" class="btn qty-plus">+</button>
                                            </div>
                                        </td>
                                        <td>
                                            <input name="unit[]" class="form-control unit p-1 text-center" value="" readonly>
                                        </td>
                                        <td data-label="Price">
                                            <input name="rate[]" class="form-control rate text-end" value="0">
                                        </td>
                                        <td data-label="Amount">
                                            <input name="amount[]" class="form-control item-total text-end" value="0" readonly>
                                        </td>
                                        <td data-label="Action">
                                            <div class="d-flex gap-1 justify-content-center">
                                                <button type="button" class="btn btn-success btn-action add-row">+</button>
                                                <button type="button" class="btn btn-danger btn-action remove-row">×</button>
                                            </div>
                                        </td>
                                    </tr>

                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

                {{-- ================= DELIVERY & PAYMENT ================= --}}
                <div class="card mb-3" id="deliveryPaymentPanel">
                    <div class="card-body">
                        <h6 class="mb-3 fw-bold text-primary">Delivery & Payment Details</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="fw-bold">Delivery Date <span class="text-danger">*</span></label>
                                <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date', $original->delivery_date) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="fw-bold">Notify Before (Days)</label>
                                <input type="number" name="notify_days_before" class="form-control" value="{{ old('notify_days_before', $original->notify_days_before ?? 2) }}" min="1" max="30">
                                <small class="text-muted">System will notify you X days before delivery</small>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================= TOTAL ================= --}}
                <div id="saleBookingLayout">
                    <div class="row g-3 mb-2">
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
                                        <input type="hidden" name="split_payments_json" id="splitPaymentsJson"
                                            value="{{ $original->split_payments ? json_encode($original->split_payments) : '' }}">
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
                                        <input id="grandTotal" class="form-control form-control-sm readonly-box w-50 text-end"
                                            value="{{ $original->grand_total }}" readonly>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2" id="discountContainer">
                                        <span class="small text-muted">Discount</span>
                                        <input name="gross_discount" class="form-control form-control-sm w-50 text-end"
                                            value="{{ $original->discount_value }}">
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-2 mb-2" id="advanceContainer">
                                        <span class="small text-muted" id="advanceLabel">Advance</span>
                                        <input id="advance" name="advance_amount" class="form-control form-control-sm w-50 text-end"
                                            value="{{ $original->advance_amount }}">
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between gap-2" id="remainingContainer">
                                        <span class="small text-muted">Remaining Balance</span>
                                        <input id="remaining" class="form-control form-control-sm readonly-box w-50 text-end"
                                            value="{{ $original->remaining_amount }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ===== ESTIMATE LAYOUT (vertical, no payment) ===== --}}
                <div id="estimateLayout" class="d-none">
                    <div class="card border mb-2">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3">Financial Summary</h6>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="small text-muted d-block mb-1">Gross Total</label>
                                    <input id="grandTotalE" class="form-control form-control-sm readonly-box text-end"
                                        value="{{ $original->grand_total }}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted d-block mb-1">Discount</label>
                                    <input name="gross_discount" id="discountE" class="form-control form-control-sm text-end"
                                        value="{{ $original->discount_value }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="small text-muted d-block mb-1">Net Total</label>
                                    <input id="netE" class="form-control form-control-sm readonly-box text-end" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SAVE BUTTON (right aligned) --}}
                <div class="d-flex justify-content-end mt-3">
                    <input type="hidden" name="net_amount" id="netAmount" value="{{ $original->net_amount }}">

                    <button type="submit" id="btnSaveOrder" class="btn btn-primary btn-save-order"><i class="fa fa-save me-1"></i> Update Invoice</button>
                </div>
                </div>{{-- /container-fluid --}}

            </form>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- ================= JS ================= --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Add these two libraries -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jQuery-slimScroll/1.3.8/jquery.slimscroll.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
    function calcRow(row) {
        let rate = parseFloat(row.find('.rate').val()) || 0;
        let qty = parseFloat(row.find('.qty').val());
        let avail = parseFloat(row.find('.avail-stock').val());

        if (isNaN(qty) || qty < 0) qty = 0;

        let saleType = $('input[name="sale_type"]:checked').val();
        if (saleType === 'sale' && !isNaN(avail) && !row.hasClass('manual-mode')) {
            if (qty > avail) {
                row.find('.qty').addClass('border-danger text-danger');
            } else {
                row.find('.qty').removeClass('border-danger text-danger');
            }
        } else {
            row.find('.qty').removeClass('border-danger text-danger');
        }

        let total = rate * qty;
        row.find('.item-total').val(total.toFixed(2));

        calcGrand();
    }

    function calcGrand() {
        let total = 0;
        $('.item-total').each(function () {
            total += parseFloat(this.value) || 0;
        });
        let discount = parseFloat($('#estimateLayout').hasClass('d-none')
            ? $('#saleBookingLayout [name="gross_discount"]').val()
            : $('#estimateLayout [name="gross_discount"]').val()) || 0;
        let net = total - discount;
        $('#grandTotal').val(total.toFixed(2));
        $('#grandTotalE').val(total.toFixed(2));
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
            let adv = parseFloat($('#advance').val()) || 0;
            $('#remaining').val((net - adv).toFixed(2));
        }
    }

    $(document).on('input change', '.rate,.qty', function () {
        calcRow($(this).closest('tr'));
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

    $(document).on('input change', '.item-total, [name="gross_discount"], #advance', function () {
        calcGrand();
    });

    $(document).on('click', '.qty-plus', function () {
        let r = $(this).closest('tr');
        r.find('.qty').val(+r.find('.qty').val() + 1);
        calcRow(r);
    });

    $(document).on('click', '.qty-minus', function () {
        let r = $(this).closest('tr');
        r.find('.qty').val(Math.max(0, +r.find('.qty').val() - 1));
        calcRow(r);
    });

    // Row Input Mode Toggle
    $(document).on('click', '.mode-toggle', function() {
        let btn = $(this);
        let icon = btn.find('.mode-icon');
        let input = btn.siblings('.item-input');

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

    function fetchProducts(input, q) {
        let row = input.closest('tr');
        if (input.attr('data-mode') === 'manual') { $acList.addClass('d-none'); return; }
        $acList.data('row', row);
        $.ajax({
            url: "{{ route('get.items') }}",
            type: "GET",
            data: { q: q },
            success: function (res) {
                if (!Array.isArray(res) || res.length === 0) { $acList.addClass('d-none'); return; }
                let rect = input[0].getBoundingClientRect();
                $acList.css({ left: rect.left + 'px', top: rect.bottom + 'px', width: input.outerWidth() + 'px' });
                $acList.empty().removeClass('d-none');
                res.forEach(it => { $('<div class="autocomplete-item"></div>').text(it.item_name).data('item', it).appendTo($acList); });
            },
            error: function () { $acList.addClass('d-none'); }
        });
    }

    // On focus: show all products; on input: filter by typed query
    $(document).on('focus', '.item-input', function () { fetchProducts($(this), ''); });
    $(document).on('input', '.item-input', function () { fetchProducts($(this), $(this).val().trim()); });

    // Hide autocomplete when clicking outside
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.item-input, .autocomplete-list').length) {
            $acList.addClass('d-none');
        }
    });

    // Select item from autocomplete
    $(document).on('click', '.autocomplete-item', function () {
        let it = $(this).data('item');
        let row = $acList.data('row');

        if (!row || !row.length) return;

        row.find('.item-input').val(it.item_name);

        let price = parseFloat(it.retail_price) || parseFloat(it.wholesale_price) || 0;
        row.find('.rate').val(price);
        row.find('.unit').val(it.unit || 'pcs');
        row.find('.avail-stock').val(it.initial_stock !== null && it.initial_stock !== undefined ? it.initial_stock : 0);

        $acList.addClass('d-none');

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

    // calculate all rows on load
    $(document).ready(function () {
        calcGrand();
    });

    // ========== ROW ACTIONS ==========

    function updateRowNumbers() {
        $('#saleTableBody .sale-row').each(function (index) {
            $(this).find('.row-index').text(index + 1);
        });
    }

    function createRowHtml() {
        let r = $('#rowTemplate').clone().removeClass('d-none').removeAttr('id');
        r.find('.qty').removeClass('border-danger text-danger');
        return r;
    }

    // Add row (insert after current)
    $(document).on('click', '.add-row', function () {
        let currentRow = $(this).closest('tr.sale-row');
        let newRow = createRowHtml();
        currentRow.after(newRow);
        updateRowNumbers();
        calcGrand();
        newRow.find('.item-input').focus();
    });

    // Remove row
    $(document).on('click', '.remove-row', function () {
        let rowCount = $('#saleTableBody .sale-row').length;
        if (rowCount > 1) {
            $(this).closest('tr').remove();
            updateRowNumbers();
            calcGrand();
        }
    });

    // Enter key: auto-add new row when pressing Enter in last row
    $(document).on('keydown', '.sale-row input, .sale-row select', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            let currentRow = $(this).closest('tr.sale-row');
            if (currentRow.length && currentRow.is('#saleTableBody .sale-row:last')) {
                let newRow = createRowHtml();
                currentRow.after(newRow);
                updateRowNumbers();
                calcGrand();
                newRow.find('.item-input').focus();
            }
            return false;
        }
    });

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
            $('[name="delivery_date"], [name="notify_days_before"]').prop('required', false).val('').prop('disabled', true);
            $('#remainingContainer span').text('Remaining');
            $('#btnSaveOrder').text('Update Sale');
        } else if (saleType === 'estimate') {
            $('#deliveryPaymentPanel').hide();
            $('#saleBookingLayout').addClass('d-none');
            $('#estimateLayout').removeClass('d-none');
            $('#estimateLayout :input').prop('disabled', false);
            $('#saleBookingLayout :input').prop('disabled', true);
            $('[name="delivery_date"], [name="notify_days_before"]').prop('required', false).val('').prop('disabled', true);
            $('#btnSaveOrder').text('Update Estimate');
        } else { // booking
            $('#deliveryPaymentPanel').show();
            $('#saleBookingLayout').removeClass('d-none');
            $('#estimateLayout').addClass('d-none');
            $('#saleBookingLayout :input').prop('disabled', false);
            $('#estimateLayout :input').prop('disabled', true);
            $('[name="delivery_date"], [name="notify_days_before"]').prop('required', true).prop('disabled', false);
            if (!$('[name="notify_days_before"]').val()) {
                $('[name="notify_days_before"]').val('2');
            }
            $('#remainingContainer span').text('Remaining');
            $('#btnSaveOrder').text('Update Job Order');
        }

        let discountVal = $('#estimateLayout').hasClass('d-none')
            ? $('#saleBookingLayout [name="gross_discount"]').val()
            : $('#estimateLayout [name="gross_discount"]').val();
        $('#saleBookingLayout [name="gross_discount"]').val(discountVal);
        $('#estimateLayout [name="gross_discount"]').val(discountVal);

        $('.sale-row:not(#rowTemplate)').each(function() {
            calcRow($(this));
        });
        calcGrand();
    }

    $('form').on('submit', function () {
        calcGrand();
        
        let validItems = 0;
        let errors = [];
        $('.sale-row:not(#rowTemplate)').each(function() {
            let row = $(this);
            let input = row.find('.item-input');
            let itemName = input.val().trim();
            let qty = parseFloat(row.find('.qty').val()) || 0;

            if(itemName) {
                validItems++;
                if (qty <= 0) {
                    errors.push(`Row ${row.find('.row-index').text()}: Quantity for product "${itemName}" must be greater than 0.`);
                }
            }
        });

        if (validItems === 0) {
            Swal.fire('Error', 'Please add at least one item', 'error');
            return false;
        }

        let saleType = $('input[name="sale_type"]:checked').val();
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

        handleSaleTypeToggle();
        $('input[name="sale_type"]').on('change', handleSaleTypeToggle);

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
                    $('#advanceContainer').addClass('d-none');
                    $('#remainingContainer').removeClass('d-none');
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

        // Populate phone/address from old selection on load
        let selCust = $('#customer').find('option:selected');
        if (selCust.val()) { $('#phone').val(selCust.data('phone') || ''); $('#address').val(selCust.data('address') || ''); }
        let selVend = $('#vendor').find('option:selected');
        if (selVend.val()) { $('#phone').val(selVend.data('phone') || ''); $('#address').val(selVend.data('address') || ''); }
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

    $('#splitRows').on('click', '.split-add', function () {
        if (splitRowCount() >= 10) return;
        $('#splitRows').append(renderSplitRow(null, null, false));
        refreshFirstRowButton();
    });

    $('#splitRows').on('input', '.split-amount', syncSplitJson);
    $('#splitRows').on('change', '.split-account', syncSplitJson);
    $('#splitRows').on('click', '.split-remove', function () {
        $(this).closest('.split-row').remove();
        ensurePrimaryRow();
    });

    // Preload existing split payments on page load
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
