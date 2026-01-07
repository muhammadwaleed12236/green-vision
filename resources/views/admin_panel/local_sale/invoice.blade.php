@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <!-- ACTION BUTTONS -->
            <div class="d-flex justify-content-end gap-2 mb-2 no-print" style="max-width:1100px; margin:auto;">
                <a href="{{ route('local-sale') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>

                <button onclick="printInvoice()" class="btn btn-danger">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            <!-- INVOICE CARD -->
            <div class="card p-3" id="invoiceCard" style="max-width:1100px; margin:auto;">
                <div class="card-body">

                    <!-- HEADER -->
                    <div class="d-flex justify-content-between align-items-center mb-2"
                        style="border-bottom:3px solid #000; padding-bottom:8px;">

                        <div class="d-flex align-items-center">
                            <img src="{{ url('small-logo.png') }}" alt="Logo" style="max-width:100px;">
                            <div class="ms-3">
                                <h5 class="mb-0 fw-bold">Raj Glass</h5>
                                <small class="text-muted">Glass & Aluminum Works</small>
                            </div>
                        </div>

                        <div class="text-center">
                            <h6 class="fw-bold mb-1">Raj Glass</h6>
                            <small>6-B Block-E, Latifabad No-08, Hyderabad</small><br>
                            <small>0314-4021603 | 0334-2611233</small>
                        </div>

                        <div class="text-end">
                            <h6 class="fw-bold mb-1">Job Order Invoice</h6>
                            <small>Job No: <strong>{{ $sale->invoice_number }}</strong></small><br>
                            <small>Date: <strong>{{ $sale->Date }}</strong></small>
                        </div>
                    </div>

                    <!-- CUSTOMER INFO -->
                    <div class="row my-3 mx-1" style="border:2px solid #000; padding:12px;">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Client Name:</strong> {{ $sale->customer->customer_name ?? 'N/A' }}</p>
                            {{-- <p class="mb-1"><strong>Area:</strong> {{ $sale->customer_area ?? 'N/A' }}</p> --}}
                            <p class="mb-0"><strong>Mobile:</strong> {{ $sale->customer_phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- ITEMS TABLE -->
                    @php
                        $items = json_decode($sale->item) ?? [];
                        $amounts = json_decode($sale->amount) ?? [];
                    @endphp

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light text-center">
                                <tr>
                                    <th width="8%">#</th>
                                    <th>Description</th>
                                    <th width="20%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $i => $item)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>{{ $item }}</td>
                                        <td class="text-end">{{ number_format($amounts[$i] ?? 0, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>

                            <!-- TOTALS -->
                            <tfoot>
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Gross Total</td>
                                    <td class="text-end fw-bold">{{ number_format($sale->grand_total,2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Discount</td>
                                    <td class="text-end">{{ number_format($sale->discount_value ?? 0,2) }}</td>
                                </tr>
                                <tr class="table-light">
                                    <td colspan="2" class="text-end fw-bold">Net Amount</td>
                                    <td class="text-end fw-bold">{{ number_format($sale->net_amount,2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Advance</td>
                                    <td class="text-end">{{ number_format($sale->advance_amount ?? 0,2) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-end fw-bold">Remaining</td>
                                    <td class="text-end fw-bold text-danger">
                                        {{ number_format($sale->remaining_amount ?? 0,2) }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- SIGNATURE -->
                    <div class="row mt-4">
                        <div class="col-6">
                            <p class="fw-bold mb-0">For Raj Glass</p>
                            <small>Authorized Signature</small>
                        </div>
                        <div class="col-6 text-end">
                            <div style="border-bottom:1px solid #000; width:220px; height:60px; display:inline-block;"></div>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="text-center mt-4" style="border-top:2px solid #000; padding-top:6px;">
                        <small class="fw-bold">
                            Developed by ProWave Software Solutions<br>
                            📞 0317-3836223 | 0317-3859647
                        </small>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<!-- PRINT STYLE -->
<style>
    #invoiceCard { font-family: Arial, sans-serif; font-size:13px; }

    @media print {
        body * { visibility: hidden; }
        #invoiceCard, #invoiceCard * { visibility: visible; }
        #invoiceCard { position:absolute; left:0; top:0; width:100%; }
        .no-print { display:none !important; }
        table { border-collapse: collapse; }
        th, td { border:1px solid #000 !important; }
    }
</style>

<script>
    function printInvoice(){
        window.print();
    }
</script>
