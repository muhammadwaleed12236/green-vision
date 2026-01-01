@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Header --}}
            <div class="row mb-4 align-items-center pt-2 pb-2 border-bottom border-3 border-dark">
                <div class="col-md-4 d-flex align-items-center">
                    <img src="{{ url('small-logo.png') }}" alt="Logo" style="max-width: 120px;">
                    <h4 class="fw-bold ms-3" style="font-size: 16px;">Raj Glass</h4>
                </div>
                <div class="col-md-4 text-center">
                    <h5 class="fw-bold mb-1">Raj Glass</h5>
                    <p class="mb-0">6-B Block-E, Latifabad No. 08, Hyderabad</p>
                    <p class="mb-0">Phone: 0314-4021603 / 0334-2611233</p>
                </div>
                <div class="col-md-4 text-end">
                    <h4 class="fw-bold">Glass Works</h4>
                </div>
            </div>

            {{-- Receipt Card --}}
            <div class="card shadow-lg mt-4 p-4 border-0 rounded-4" id="receiptSection">
                <div class="text-center mb-4">
                    <h5 class="text-success fw-bold">Staff Payment Receipt</h5>
                    <p class="text-muted mb-0">Payment details for staff / contractor</p>
                    <hr>
                </div>

                <table class="table table-striped align-middle">
                    <tbody>
                        <tr>
                            <th>Date</th>
                            <td>{{ $date }}</td>
                        </tr>

                        <tr>
                            <th>Staff / Contractor Name</th>
                            <td>{{ $staff->contractor_name }}</td>
                        </tr>

                        <tr>
                            <th>Contact Number</th>
                            <td>{{ $staff->contact_number ?? 'N/A' }}</td>
                        </tr>

                        <tr>
                            <th>Amount Paid</th>
                            <td>
                                <span class="badge bg-success fs-6">
                                    PKR {{ number_format($amount_paid) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Updated Closing Balance</th>
                            <td>
                                <span class="badge bg-danger fs-6">
                                    PKR {{ number_format($closing_balance) }}
                                </span>
                            </td>
                        </tr>

                        <tr>
                            <th>Remarks</th>
                            <td>{{ $remarks ?? 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>

                {{-- Actions --}}
                <div class="text-center mt-5">
                    <a href="{{ route('staff.payments') }}" class="btn btn-outline-primary px-4 me-2">
                        <i class="fas fa-arrow-left me-1"></i> Back to Staff Payments
                    </a>

                    <button onclick="window.print()" class="btn btn-outline-secondary px-4 me-2">
                        <i class="fas fa-print me-1"></i> Print Receipt
                    </button>

                    <button id="screenshotBtn" class="btn btn-outline-success px-4">
                        <i class="fas fa-camera me-1"></i> Capture Screenshot
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

{{-- Screenshot --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    document.getElementById('screenshotBtn').addEventListener('click', function () {
        let receipt = document.getElementById('receiptSection');

        html2canvas(receipt, {
            scale: 2,
            useCORS: true
        }).then(canvas => {
            let imageData = canvas.toDataURL("image/png");
            let link = document.createElement('a');
            link.download = 'staff_payment_receipt.png';
            link.href = imageData;
            link.click();
        });
    });
</script>
