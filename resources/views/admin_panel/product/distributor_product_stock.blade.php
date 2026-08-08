@include('admin_panel.include.header_include')

<style>
    /* ============================================
       RESPONSIVE LAYOUT (mirror of admin dashboard)
       ============================================ */

    /* Never allow accidental horizontal scrolling */
    html, body {
        overflow-x: hidden;
        width: 100%;
        margin: 0;
    }

    /* Keep media flexible so nothing overflows */
    img, canvas, table {
        max-width: 100%;
    }

    /* Stock table never scrolls horizontally - cells wrap to fit */
    .pp-wrap {
        overflow-x: hidden;
    }
    .pp-wrap .datanew {
        table-layout: fixed !important;
        width: 100% !important;
        max-width: 100% !important;
        border-spacing: 0 !important;
    }
    .pp-wrap .datanew td {
        white-space: normal !important;
        word-break: break-word !important;
        overflow-wrap: break-word !important;
    }
    .pp-wrap .datanew th {
        white-space: normal !important;
        word-break: break-word !important;
    }
    .pp-wrap .datanew th:nth-child(1) { width: 4% !important; }
    .pp-wrap .datanew th:nth-child(2) { width: 10% !important; }
    .pp-wrap .datanew th:nth-child(3) { width: 12% !important; }
    .pp-wrap .datanew th:nth-child(4) { width: 8% !important; }
    .pp-wrap .datanew th:nth-child(5) { width: 14% !important; }
    .pp-wrap .datanew th:nth-child(6) { width: 6% !important; }
    .pp-wrap .datanew th:nth-child(7) { width: 8% !important; }
    .pp-wrap .datanew th:nth-child(8) { width: 10% !important; }
    .pp-wrap .datanew th:nth-child(9) { width: 12% !important; }
    .pp-wrap .datanew th:nth-child(10) { width: 6% !important; }
    .pp-wrap .datanew th:nth-child(11) { width: 10% !important; }
    .pp-wrap .dataTables_wrapper {
        max-width: 100%;
    }

    /* ---------- Phone & below (576px) ---------- */
    @media (max-width: 575.98px) {
        .page-header .page-btn {
            width: 100%;
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-top: 12px;
        }
        .page-header .page-btn .btn {
            width: 100%;
            margin-right: 0 !important;
        }
        .page-title h4 { font-size: 1.05rem; }
        .pp-wrap .dataTables_filter { margin-bottom: 8px; }
        .pp-wrap .dataTables_filter input { max-width: 130px; }
        .pp-wrap .dataTables_length select { max-width: 70px; }
    }

    /* ---------- Stock table -> stacked cards (phone & small tablet) ---------- */
    @media (max-width: 767.98px) {
        .table-responsive .datanew thead {
            display: none !important;
        }
        .table-responsive .datanew,
        .table-responsive .datanew tbody,
        .table-responsive .datanew tr,
        .table-responsive .datanew td {
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .table-responsive .datanew {
            border: 0 !important;
        }
        .table-responsive .datanew tbody {
            display: flex !important;
            flex-direction: column;
            gap: 12px;
        }
        .table-responsive .datanew tbody tr {
            background: #fff;
            border: 1px solid #eef2f7 !important;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 4px 14px;
            margin: 0 !important;
        }
        .table-responsive .datanew tbody tr:hover {
            background: #fff;
        }
        .table-responsive .datanew td {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 9px 0 !important;
            border: 0 !important;
            border-bottom: 1px dashed #eef2f7 !important;
            background: transparent !important;
            font-size: 0.85rem !important;
            color: #1e293b;
            text-align: right;
            white-space: normal !important;
            word-break: break-word;
        }
        .table-responsive .datanew td:last-child {
            border-bottom: 0 !important;
        }
        .table-responsive .datanew td::before {
            content: attr(data-label);
            flex-shrink: 0;
            color: #94a3b8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            text-align: left;
        }
        .pp-wrap .dataTables_filter,
        .pp-wrap .dataTables_length,
        .pp-wrap .dataTables_info,
        .pp-wrap .dataTables_paginate {
            max-width: 100%;
        }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header">
                <div class="page-title">
                    <h4>Product Stock</h4>
                </div>
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive pp-wrap">
                        <table class="table datanew">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Item Code</th>
                                    <th>Item Name</th>
                                    <th>Size</th>
                                    <th>Prcie</th>
                                    <th>Pcs/Carton</th>
                                    <th>Carton Quantity</th>
                                    <th>Pcs</th>
                                    <th>initial Stock</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $key => $product)
                                <tr>
                                    <td data-label="#">#{{ $key + 1 }}</td>
                                    <td data-label="Category">{{ $product->category }}</td>
                                    <td data-label="Sub-Category">{{ $product->subcategory }}</td>
                                    <td data-label="Item Code">{{ $product->code }}</td>
                                    <td data-label="Item Name">{{ $product->item }}</td>
                                    <td data-label="Size">{{ $product->size }}</td>
                                    <td data-label="Price">{{ $product->price }}</td>
                                    <td data-label="Pcs/Carton">{{ $product->pcs_carton }}</td>
                                    <td data-label="Carton Qty">{{ $product->carton_quantity }}</td>
                                    <td data-label="Pcs">{{ $product->pcs }}</td>
                                    <td data-label="Stock">{{ $product->initial_stock }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('admin_panel.include.footer_include')