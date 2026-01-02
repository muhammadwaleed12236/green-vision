{{-- @include('admin_panel.include.header_include')

<div class="main-wrapper">
    @include('admin_panel.include.navbar_include')
    @include('admin_panel.include.admin_sidebar_include')

    <style>
        table.report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 14px;
        }

        table.report-table th,
        table.report-table td {
            border: 1px solid #000 !important;
            padding: 8px;
            text-align: left;
        }

        table.report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
    </style>

    <div class="page-wrapper">
        <div class="content">
            <div class="card p-4 shadow-lg">
                <div class="card-body">
                    <h3 class="card-title text-center fw-bold mb-4 text-primary">STAFF INFORMATION REPORT</h3>

                    <form id="staffSearchForm">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="fw-bold" for="Staff">Select Staff</label>
                                <select id="Staff" class="form-control">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($Staff as $staff)
                                        <option value="{{ $staff->id }}">
{{ $staff->name }} - {{ $staff->designation ?? 'Staff' }}
</option>
@endforeach
</select>
</div>
</div>
<div class="d-flex justify-content-between align-items-center gap-4 mt-4">
    <button type="button" id="searchStaff" class="btn btn-primary btn-lg px-5">Search</button>

    <button id="downloadPdf" class="btn btn-danger btn-lg">Download PDF</button>
</div>
</form>

<div class="table-responsive mt-4 Staff_report" id="report-preview" style="display:none;">
    <h4 class="text-center fw-bold">STAFF INFORMATION REPORT HYDERABAD</h4>
    <div class="report-party-name text-center fw-bold text-secondary mb-4"></div>

    <table class="report-table">
        <tbody>
            <tr>
                <th width="30%">Staff Name</th>
                <td id="staff-name"></td>
            </tr>
            <tr>
                <th>Phone Number</th>
                <td id="staff-phone"></td>
            </tr>
            <tr>
                <th>Designation</th>
                <td id="staff-designation"></td>
            </tr>
            <tr>
                <th>Monthly Salary</th>
                <td id="staff-salary" class="text-success fw-bold"></td>
            </tr>
            <tr>
                <th>City</th>
                <td id="staff-city"></td>
            </tr>
            <tr>
                <th>Address</th>
                <td id="staff-address"></td>
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
$('#searchStaff').click(function() {
    const staffId = $('#Staff').val();

    if (!staffId) {
        alert('Please select a staff member.');
        return;
    }

    $.ajax({
        url: "{{ route('fetch.staff.report') }}",
        method: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            staff_id: staffId
        },
        success: function(response) {
            console.log(response);

            // ✅ Populate staff details
            $('#staff-name').text(response.report.name);
            $('#staff-phone').text(response.report.phone);
            $('#staff-designation').text(response.report.designation);
            $('#staff-salary').text('Rs. ' + Number(response.report.salary).toLocaleString());
            $('#staff-city').text(response.report.city);
            $('#staff-address').text(response.report.address);

            // Set Staff Name in header
            $('.report-party-name').html(`<p>${response.staff_name}</p>`);

            // Show report
            $('#report-preview').show();
        },
        error: function(xhr) {
            alert('Error loading report');
            console.error(xhr);
        }
    });
});

document.getElementById("downloadPdf").addEventListener("click", function() {
    if ($('#report-preview').is(':visible')) {
        const element = document.querySelector(".Staff_report");
        const opt = {
            margin: 0.5,
            filename: 'Staff-Information-Report.pdf',
            image: {
                type: 'jpeg',
                quality: 1
            },
            html2canvas: {
                scale: 2,
                useCORS: true
            },
            jsPDF: {
                unit: 'in',
                format: 'a4',
                orientation: 'portrait'
            }
        };
        html2pdf().set(opt).from(element).save();
    } else {
        alert('Please search for a staff member first.');
    }
});
</script> --}}