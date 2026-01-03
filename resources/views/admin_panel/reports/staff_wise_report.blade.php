@include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <div class="page-wrapper">
        <div class="content">

            <div class="container mt-4">

                <!-- STAFF INFO -->
                <div class="card mb-3">
                    <div class="card-header fw-bold">Staff Weekly Account</div>
                    <div class="card-body row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Select Staff</label>
                            <select class="form-select" id="staff_id">
                                <option value="">-- Select Staff --</option>
                                @foreach($staffs as $staff)
                                    <option value="{{ $staff->id }}"
                                        data-joining="{{ $staff->created_at->format('Y-m-d') }}"
                                        data-weekly="{{ $staff->salary }}">
                                        {{ $staff->name }} ({{ $staff->designation }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Joining Date</label>
                            <input type="text" id="joining_date" class="form-control" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Weekly Amount</label>
                            <input type="text" id="weekly_amount" class="form-control" readonly>
                        </div>

                    </div>
                </div>

                <!-- WEEK SELECTION -->
                <div class="card mb-3">
                    <div class="card-header fw-bold">Week Details</div>
                    <div class="card-body row g-3">

                        <div class="col-md-3">
                            <label class="form-label">Week Start</label>
                            <input type="date" id="week_start" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Week End</label>
                            <input type="date" id="week_end" class="form-control">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Balance</label>
                            <input type="number" id="balance" class="form-control" readonly>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Paid</label>
                            <input type="number" id="paid" class="form-control calc">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Advance / Udhar</label>
                            <input type="number" id="advance" class="form-control calc">
                        </div>

                    </div>

                    <div class="card-footer text-end">
                        <button class="btn btn-success" id="saveEntry">
                            Save Weekly Entry
                        </button>
                    </div>
                </div>

                <!-- WEEKLY HISTORY -->
                <div class="card">
                    <div class="card-header fw-bold">Weekly History</div>
                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Week</th>
                                    <th>Weekly Amt</th>
                                    <th>Paid</th>
                                    <th>Advance</th>
                                    <th>Balance</th>
                                </tr>
                            </thead>
                            <tbody id="historyRows">
                                <tr>
                                    <td colspan="5" class="text-center">Select staff to view history</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

@include('admin_panel.include.footer_include')

<script>
    const csrf = "{{ csrf_token() }}";
    let lastWeekEnd = null;
    let previousBalance = 0;

    document.getElementById('staff_id').addEventListener('change', function () {
        const opt = this.options[this.selectedIndex];

        $('#joining_date').val(opt.dataset.joining || '');
        $('#weekly_amount').val(opt.dataset.weekly || '');

        // Reset fields
        $('#paid').val('');
        $('#advance').val('');

        loadHistory(this.value);
    });

    $('.calc').on('input', calculateBalance);

    function calculateBalance() {
        const weekly = Number($('#weekly_amount').val() || 0);
        const paid = Number($('#paid').val() || 0);
        const advance = Number($('#advance').val() || 0);

        // ✅ Previous Balance + Weekly Amount - Paid - Advance
        const balance = previousBalance + weekly - paid - advance;

        $('#balance').val(balance);

        // Color coding
        if (balance > 0) {
            $('#balance').css('color', 'green').css('font-weight', 'bold');
        } else if (balance < 0) {
            $('#balance').css('color', 'red').css('font-weight', 'bold');
        } else {
            $('#balance').css('color', 'black').css('font-weight', 'normal');
        }
    }

    // ================= LOAD HISTORY =================
    function loadHistory(staffId) {
        if (!staffId) return;

        $.post("{{ route('staff.weekly.history') }}", {
            _token: csrf,
            staff_id: staffId
        }, function (res) {

            let rows = '';
            let validEntries = res.filter(r => r.week_start && r.week_end);

            if (validEntries.length === 0) {
                rows = `<tr><td colspan="5" class="text-center">No record found</td></tr>`;
                lastWeekEnd = $('#joining_date').val();
                previousBalance = 0;
            } else {
                lastWeekEnd = validEntries[0].week_end;

                // ✅ Latest entry ka balance = next week ka previous balance
                previousBalance = Number(validEntries[0].balance || 0);

                validEntries.forEach(r => {
                    let balanceColor = '';

                    if (r.balance > 0) {
                        balanceColor = 'text-success fw-bold';
                    } else if (r.balance < 0) {
                        balanceColor = 'text-danger fw-bold';
                    }

                    rows += `
                    <tr>
                        <td>${r.week_start} – ${r.week_end}</td>
                        <td>${r.weekly_amount}</td>
                        <td>${r.paid}</td>
                        <td>${r.advance || 0}</td>
                        <td class="${balanceColor}">
                            ${r.balance}
                        </td>
                    </tr>`;
                });
            }
            $('#historyRows').html(rows);

            setMinimumWeekStart();
            calculateBalance();
        });
    }

    // ================= SET MINIMUM WEEK START =================
    function setMinimumWeekStart() {
        if (lastWeekEnd) {
            let nextDate = new Date(lastWeekEnd);
            nextDate.setDate(nextDate.getDate() + 1);

            let minDate = nextDate.toISOString().split('T')[0];
            $('#week_start').attr('min', minDate);
            $('#week_start').val(minDate);

            let weekEndDate = new Date(nextDate);
            weekEndDate.setDate(weekEndDate.getDate() + 6);
            $('#week_end').val(weekEndDate.toISOString().split('T')[0]);
        }
    }

    // ================= SAVE =================
    $('#saveEntry').click(function () {


        $.post("{{ route('staff.weekly.save') }}", {
            _token: csrf,
            staff_id: $('#staff_id').val(),
            week_start: $('#week_start').val(),
            week_end: $('#week_end').val(),
            weekly_amount: $('#weekly_amount').val(),
            paid: $('#paid').val() || 0,
            advance: $('#advance').val() || 0,
            balance: $('#balance').val()
        }, function () {
            // Reset input fields
            $('#paid').val('');
            $('#advance').val('');

            // Reload history
            loadHistory($('#staff_id').val());
            alert('Saved successfully');
        });
    });

</script>
