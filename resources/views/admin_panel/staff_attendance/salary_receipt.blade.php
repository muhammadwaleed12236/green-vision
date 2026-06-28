@include('admin_panel.include.header_include')

<style>
    @media print {
        body * { visibility: hidden; }
        #receiptCard, #receiptCard * { visibility: visible; }
        #receiptCard { position: absolute; left: 0; top: 0; width: 100%; }
        .no-print { display: none !important; }
    }
</style>

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            {{-- Buttons --}}
            <div class="d-flex justify-content-end gap-2 mb-3 no-print" style="max-width: 600px; margin: auto;">
                <a href="{{ route('staff-salary.index') }}" class="btn btn-secondary">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
                <button onclick="window.print()" class="btn btn-primary">
                    <i class="fa fa-print"></i> Print
                </button>
            </div>

            {{-- Receipt Card --}}
            <div class="card p-4" id="receiptCard" style="max-width: 600px; margin: auto;">
                <div class="text-center mb-4" style="border-bottom: 2px solid #000; padding-bottom: 15px;">
                    @if($appSettings['company_logo'])
                        <img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" style="max-width: 200px; margin-bottom: 10px;">
                    @endif
                    <p class="mb-1 fw-bold">{{ $appSettings['company_name'] }}</p>
                    <small>{{ $appSettings['company_address'] }}</small><br>
                    <small>{{ $appSettings['company_phone'] }}</small>
                </div>

                <h5 class="text-center mb-4" style="background: #f0f0f0; padding: 10px; border-radius: 5px;">
                    SALARY PAYMENT RECEIPT
                </h5>

                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Receipt No:</strong></td>
                        <td>SP-{{ str_pad($payment->id, 5, '0', STR_PAD_LEFT) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Date:</strong></td>
                        <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('d M Y') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Payment Period:</strong></td>
                        <td>
                            @if($payment->from_date && $payment->to_date)
                                {{ \Carbon\Carbon::parse($payment->from_date)->format('d M Y') }} -
                                {{ \Carbon\Carbon::parse($payment->to_date)->format('d M Y') }}
                            @else
                                {{ \Carbon\Carbon::parse($payment->payment_month . '-01')->format('F Y') }}
                            @endif
                        </td>
                    </tr>
                </table>

                <hr>

                <table class="table table-borderless">
                    <tr>
                        <td width="40%"><strong>Staff Name:</strong></td>
                        <td>{{ $payment->staff->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Designation:</strong></td>
                        <td>{{ $payment->staff->designation ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Phone:</strong></td>
                        <td>{{ $payment->staff->phone_number ?? 'N/A' }}</td>
                    </tr>
                </table>

                <hr>

                <table class="table table-bordered">
                    <tr>
                        <td><strong>Basic Salary (Monthly)</strong></td>
                        <td class="text-end">PKR {{ number_format($payment->basic_salary, 0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>Days Present</strong></td>
                        <td class="text-end">{{ $payment->days_present }} days</td>
                    </tr>
                    <tr>
                        <td><strong>Days Absent</strong></td>
                        <td class="text-end text-danger">{{ $payment->days_absent }} days</td>
                    </tr>
                    <tr>
                        <td><strong>Salary Advance Deducted</strong></td>
                        <td class="text-end text-danger">- PKR {{ number_format($payment->advance_deducted ?? 0, 0) }}</td>
                    </tr>
                    @if($payment->additional_advance_deducted > 0)
                    <tr>
                        <td><strong>Additional Loan Deducted</strong></td>
                        <td class="text-end text-danger">- PKR {{ number_format($payment->additional_advance_deducted, 0) }}</td>
                    </tr>
                    @endif
                    <tr class="table-success">
                        <td><strong>Net Amount Paid</strong></td>
                        <td class="text-end"><strong>PKR {{ number_format($payment->amount_paid, 0) }}</strong></td>
                    </tr>
                </table>

                @if($payment->remarks)
                    <p class="mb-3"><strong>Remarks:</strong> {{ $payment->remarks }}</p>
                @endif

                <div class="row mt-5">
                    <div class="col-6">
                        <div style="border-top: 1px solid #000; width: 150px; padding-top: 5px;">
                            Staff Signature
                        </div>
                    </div>
                    <div class="col-6 text-end">
                        <div style="border-top: 1px solid #000; width: 150px; padding-top: 5px; display: inline-block;">
                            Authorized Signature
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4" style="border-top: 1px solid #ccc; padding-top: 10px;">
                    <small class="text-muted">
                        Developed by ProWave Software Solutions | 0317-3836223
                    </small>
                </div>
            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')
