<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Attendance Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 5px 0;
            color: #333;
        }
        .staff-info {
            margin-bottom: 20px;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
        }
        .staff-info p {
            margin: 5px 0;
        }
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .summary-item {
            display: table-cell;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
            background: #f8f9fa;
        }
        .summary-item strong {
            display: block;
            font-size: 18px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #333;
            color: white;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #333;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 3px;
            font-weight: bold;
            font-size: 10px;
            display: inline-block;
        }
        .badge-present { background: #d1fae5; color: #065f46; }
        .badge-absent { background: #fee2e2; color: #991b1b; }
        .badge-leave { background: #fef3c7; color: #92400e; }
        .badge-half { background: #dbeafe; color: #1e40af; }
        .badge-off { background: #e5e7eb; color: #4b5563; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .off-row {
            background-color: #f3f4f6 !important;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Staff Attendance Report</h2>
        <p><strong>{{ $staff->name }}</strong> - {{ $staff->designation }}</p>
        @if($from && $to)
            <p>Period: {{ \Carbon\Carbon::parse($from)->format('d M Y') }} to {{ \Carbon\Carbon::parse($to)->format('d M Y') }}</p>
        @else
            <p>All Records</p>
        @endif
    </div>

    <div class="staff-info">
        <p><strong>Staff Name:</strong> {{ $staff->name }}</p>
        <p><strong>Designation:</strong> {{ $staff->designation }}</p>
        <p><strong>Phone:</strong> {{ $staff->phone ?? 'N/A' }}</p>
        <p><strong>Report Generated:</strong> {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="summary">
        <div class="summary-item">
            <strong style="color: #065f46;">{{ $presentCount }}</strong>
            <small>Present</small>
        </div>
        <div class="summary-item">
            <strong style="color: #991b1b;">{{ $absentCount }}</strong>
            <small>Absent</small>
        </div>
        <div class="summary-item">
            <strong style="color: #92400e;">{{ $leaveCount }}</strong>
            <small>Leave</small>
        </div>
        <div class="summary-item">
            <strong style="color: #1e40af;">{{ $halfCount }}</strong>
            <small>Half Day</small>
        </div>
        <div class="summary-item">
            <strong style="color: #4b5563;">{{ $offCount }}</strong>
            <small>OFF</small>
        </div>
        <div class="summary-item">
            <strong style="color: #333;">{{ count($allDates) }}</strong>
            <small>Total Days</small>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="15%">Date</th>
                <th width="10%">Day</th>
                <th width="12%">Status</th>
                <th width="12%">Check In</th>
                <th width="12%">Check Out</th>
                <th width="12%">Hours</th>
                <th width="22%">Remarks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($allDates as $index => $item)
                @php
                    $record = $item['record'];
                    $date = $item['date'];
                    $dateObj = \Carbon\Carbon::parse($date);
                @endphp
                <tr class="{{ !$record ? 'off-row' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $dateObj->format('d M Y') }}</td>
                    <td>{{ $dateObj->format('D') }}</td>
                    <td>
                        @if($record)
                            @php
                                $statusClass = '';
                                if($record->status == 'present') $statusClass = 'badge-present';
                                elseif($record->status == 'absent') $statusClass = 'badge-absent';
                                elseif($record->status == 'leave') $statusClass = 'badge-leave';
                                else $statusClass = 'badge-half';
                            @endphp
                            <span class="status-badge {{ $statusClass }}">
                                {{ strtoupper(str_replace('_', ' ', $record->status)) }}
                            </span>
                        @else
                            <span class="status-badge badge-off">OFF</span>
                        @endif
                    </td>
                    <td>{{ $record->check_in ?? '-' }}</td>
                    <td>{{ $record->check_out ?? '-' }}</td>
                    <td>
                        @if($record && $record->check_in && $record->check_out)
                            @php
                                try {
                                    $checkIn = \Carbon\Carbon::parse('2000-01-01 ' . $record->check_in);
                                    $checkOut = \Carbon\Carbon::parse('2000-01-01 ' . $record->check_out);
                                    
                                    // If check_out is before check_in, it means next day
                                    if($checkOut < $checkIn) {
                                        $checkOut->addDay();
                                    }
                                    
                                    $diffMinutes = $checkOut->diffInMinutes($checkIn);
                                    $hours = $diffMinutes / 60;
                                } catch(\Exception $e) {
                                    $hours = 0;
                                }
                            @endphp
                            {{ number_format($hours, 1) }} hrs
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $record->remarks ?? ($record ? '-' : 'No attendance marked') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature required.</p>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>
</body>
</html>
