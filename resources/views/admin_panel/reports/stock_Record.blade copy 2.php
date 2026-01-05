@include('admin_panel.include.header_include')

<style>
    table {
        font-size: 13px;
        width: 100%;
        border-collapse: collapse;
    }

    th,
    td {
        text-align: center;
        vertical-align: middle;
        border: 1px solid #dee2e6;
    }

    th.sub-group-heading {
        background-color: #0088fb;
        color: #fff !important;
        font-weight: bold;
    }

    th.sub-heading {
        background-color: #f8f9fa;
        font-weight: bold;
    }

    .table tbody tr td {
        padding: 8px;
        white-space: nowrap;
    }

    tfoot td {
        font-weight: bold;
    }

    .badge {
        font-size: 11px;
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow">
                <div class="card-body">

                    <h3 class="text-center text-primary fw-bold mb-4">Item Stock Report </h3>

                    {{-- FILTERS --}}
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Category</label>
                            <select class="form-control category-select">
                                <option value="all">All</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->category_name }}">{{ $c->category_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Sub Category</label>
                            <select class="form-control subcategory-select">
                                <option value="all">All</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label>Item</label>
                            <select class="form-control item-select">
                                <option value="all">All</option>
                            </select>
                        </div>

                        <div class="col-md-3 d-flex align-items-end">
                            <button class="btn btn-primary w-100 search-item">Search</button>
                        </div>
                    </div>

                    {{-- TABLE --}}
                    <div class="table-responsive">
                        <table class="table table-bordered" id="stockReport">
                            <thead>
                                <tr>
                                    <th rowspan="2">Code / Name</th>
                                    {{-- <th colspan="2" class="sub-group-heading">Purchase</th>

                                    <!-- ✅ FIXED -->
                                    <th colspan="2" class="sub-group-heading">Local Sale</th>

                                    <th colspan="2" class="sub-group-heading">Balance</th>
                                    <th colspan="2" class="sub-group-heading">Stock Value</th> --}}
                                    <th class="sub-heading">Size</th>
                                    <th class="sub-heading">Measurment</th>
                                    <th class="sub-heading">Initial Stock</th>
                                    <th class="sub-heading">Purchase Price</th>
                                    <th class="sub-heading">Unit</th>
                                    <th class="sub-heading">Value</th>
                                </tr>

                                {{-- <tr>

                                    <th class="sub-heading">Packing</th>
                                    <th class="sub-heading">Qty</th>

                                    <th class="sub-heading">Qty</th>
                                    <th class="sub-heading">Return</th>

                                    <th class="sub-heading">measurment</th>
                                    <th class="sub-heading">Stock Out</th>

                                    <th class="sub-heading">Intial Stock</th>
                                    <th class="sub-heading">W.Price</th>

                                    <th class="sub-heading">Unit</th>
                                    <th class="sub-heading">Value</th>
                                </tr> --}}
                            </thead>


                            <tbody id="item-details"></tbody>

                            <tfoot>
                                <tr>
                                    <td colspan="6" class="text-end">Total Stock Value</td>
                                    <td id="subtotalStockValue">0.00</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- <button class="btn btn-danger mt-3" id="exportPdf">Export PDF</button> --}}

                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
<script>

    $(document).on('change', '.category-select', function () {
        let categoryName = $(this).val();
        let subCategoryDropdown = $('.subcategory-select');

        if (categoryName !== "all") {
            $.ajax({
                url: "{{ route('get.subcategories', ':categoryname') }}".replace(':categoryname', categoryName),
                type: 'GET',
                success: function (response) {
                    subCategoryDropdown.html('<option value="all">All</option>');
                    $.each(response, function (index, name) {
                        subCategoryDropdown.append(`<option value="${name}">${name}</option>`);
                    });
                }
            });
        } else {
            subCategoryDropdown.html('<option value="all">All</option>');
        }
    });

    // Fetch Items on Subcategory Change
    $(document).on('change', '.subcategory-select', function () {
        let subCategoryName = $(this).val();
        let itemDropdown = $('.item-select');

        if (subCategoryName !== "all") {
            $.ajax({
                url: "{{ route('get.items.report', ':subcategory') }}".replace(':subcategory', subCategoryName),
                type: 'GET',
                success: function (response) {
                    itemDropdown.html('<option value="all">All</option>');
                    $.each(response, function (index, item) {
                        itemDropdown.append(`<option value="${item.item_code}">${item.item_name}</option>`);
                    });
                }
            });
        } else {
            itemDropdown.html('<option value="all">All</option>');
        }
    });

    $('.search-item').on('click', function () {

        let filters = {
            category: $('.category-select').val(),
            subcategory: $('.subcategory-select').val(),
            itemCode: $('.item-select').val()
        };

        console.log('Sending filters:', filters);

        $.ajax({
            url: "{{ route('get.item.details') }}",
            type: "GET",
            data: {
                category: $('.category-select').val(),
                subcategory: $('.subcategory-select').val(),
                itemCode: $('.item-select').val(),
            },
            success: function (items) {

                let html = '';
                let total = 0;

                items.forEach(item => {

                    total += Number(item.stock_value ?? 0); // ✅ TOTAL FIX

                    html += `
    <tr>
        <td>${item.item_code}<br>${item.item_name}</td>

 <td>${item.area ?? 'N/A'}</td>



        <td>
       ${item.height ?? 0} Height /
        ${item.width ?? 0} Width
    </td>

        <td>
            <strong>
            ${item.balance_stock ?? 0}
            </strong>
        </td>

        <td>${item.balance_wholesale_price ?? 0}</td>


        <td><span class="badge bg-success">PCS</span></td>
        <td><strong>${item.stock_value ?? 0}</strong></td>
    </tr>`;
                });

                $('#item-details').html(html);
                $('#subtotalStockValue').text(total.toFixed(2));
            }
        });
    });
</script>
