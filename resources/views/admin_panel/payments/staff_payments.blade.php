@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">
            <div class="page-header d-flex justify-content-between align-items-center">
                <div class="page-title">
                    <h4>Staff Payments Management</h4>
                    <h6>Manage Staff / Contractor Payments Efficiently</h6>
                </div>
            </div>

            <div class="card p-4">
                <div class="card-body">

                    {{-- SUCCESS / ERROR --}}
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('staff.payment.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Paid To (Staff / Contractor) <span class="text-danger">*</span>
                                </label>

                                <select id="staff" name="staff_id"
                                        class="form-select select2-basic" required>
                                    <option value="" selected disabled>Select Staff</option>
                                    @foreach($staffs as $staff)
                                        <option value="{{ $staff->id }}">
                                            {{ $staff->contractor_name }}
                                            ({{ $staff->contact_number }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Payment Amount (PKR) <span class="text-danger">*</span>
                                </label>
                                <input type="number" name="amount" class="form-control"
                                       placeholder="Enter payment amount" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Payment Date <span class="text-danger">*</span>
                                </label>
                                <input type="date" name="date" class="form-control"
                                       value="{{ date('Y-m-d') }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Remarks
                                </label>
                                <input type="text" name="detail" class="form-control"
                                       placeholder="Any remarks (optional)">
                            </div>
                        </div>

                        {{-- BALANCE --}}
                        <div class="text-end fw-bold text-danger fs-5 mb-4">
                            Staff Balance:
                            <span id="staff_balance">PKR 0</span>
                        </div>

                        <div class="d-flex justify-content-center gap-3">
                            <button type="submit" class="btn btn-success">
                                Save & Close
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
const balanceRoute = "{{ route('get.staff.balance', ['id' => 'STAFF_ID']) }}";


    function fetchStaffBalance(staffId) {
        let url = balanceRoute.replace('STAFF_ID', staffId);

        fetch(url)
            .then(res => res.json())
            .then(data => {
                document.getElementById('staff_balance').innerText =
                    'PKR ' + (data.balance ?? 0);
            });
    }

    $(document).ready(function () {
        $('#staff').on('change', function () {
            let staffId = $(this).val();
            if (staffId) {
                fetchStaffBalance(staffId);
            } else {
                $('#staff_balance').text('PKR 0');
            }
        });
    });
</script>
