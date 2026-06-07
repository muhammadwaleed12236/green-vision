<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendence;
use App\Models\Salesman;
use App\Models\AttendanceNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;

class StaffAttendenceController extends Controller
{
public function index(Request $request)
{
    $query = StaffAttendence::with('staff')
        ->where('admin_or_user_id', Auth::id());

    if ($request->staff_id) {
        $query->where('staff_id', $request->staff_id);
    }

    if ($request->status) {
        $query->where('status', $request->status);
    }

    if ($request->from && $request->to) {
        $query->whereBetween('attendence_date', [
            $request->from,
            $request->to
        ]);
    }

    // AJAX Request for Staff-Wise History
    if ($request->ajax || $request->has('ajax')) {
        $records = $query->latest('attendence_date')->get();
        $staff = null;
        if ($request->staff_id) {
            $staff = Salesman::find($request->staff_id);
        }
        
        return response()->json([
            'success' => true,
            'records' => $records,
            'staff_name' => $staff ? $staff->name . ' - ' . $staff->designation : 'All Staff'
        ]);
    }

    $records = $query->latest('attendence_date')->paginate(30);

    $staffs = Salesman::where('admin_or_user_id', Auth::id())
        ->where('status', 1)
        ->get();

    // Today's attendance records
    $todayAttendance = StaffAttendence::where('admin_or_user_id', Auth::id())
        ->where('attendence_date', date('Y-m-d'))
        ->get();

    // Today's Summary
    $todaySummary = [
        'present' => $todayAttendance->where('status', 'present')->count(),
        'absent' => $todayAttendance->where('status', 'absent')->count(),
        'leave' => $todayAttendance->where('status', 'leave')->count(),
        'half_day' => $todayAttendance->where('status', 'half_day')->count(),
    ];

    return view('admin_panel.staff_attendance.attendance', compact('records', 'staffs', 'todayAttendance', 'todaySummary'));
}

    public function store(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        $userId = Auth::id();
        $attendanceDate = $request->attendance_date;
        $processedStaff = [];
        $alreadyMarked = [];

        // Process ONLY staff that have a status selected (not empty)
        foreach ($request->attendance as $staffId => $row) {
            // Skip if status is empty - this is crucial!
            if (empty($row['status'])) {
                continue;
            }

            // Check if attendance already exists for this staff on this date
            $existingAttendance = StaffAttendence::where([
                'staff_id' => $staffId,
                'attendence_date' => $attendanceDate,
                'admin_or_user_id' => $userId,
            ])->first();

            if ($existingAttendance) {
                $staff = Salesman::find($staffId);
                $alreadyMarked[] = $staff ? $staff->name : "Staff #$staffId";
                continue;
            }

            // Mark this staff as processed
            $processedStaff[] = $staffId;

            StaffAttendence::create([
                'staff_id' => $staffId,
                'attendence_date' => $attendanceDate,
                'admin_or_user_id' => $userId,
                'status' => $row['status'],
                'check_in' => $row['check_in'] ?? null,
                'check_out' => $row['check_out'] ?? null,
                'overtime_hours' => $row['overtime'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'marked_by_admin_id' => $userId,
            ]);

            // Remove notification for this staff
            AttendanceNotification::where([
                'admin_or_user_id' => $userId,
                'staff_id' => $staffId,
                'attendance_date' => $attendanceDate,
            ])->delete();
        }

        $message = 'Attendance saved successfully. ' . count($processedStaff) . ' staff marked.';

        if (count($alreadyMarked) > 0) {
            $message .= ' Already marked: ' . implode(', ', $alreadyMarked);
        }

        // Create notifications for staff NOT marked (pending)
        $allStaff = Salesman::where('admin_or_user_id', $userId)
            ->where('status', 1)
            ->pluck('id')
            ->toArray();

        $unmappedStaff = array_diff($allStaff, array_merge($processedStaff, array_keys($alreadyMarked)));

        foreach ($unmappedStaff as $staffId) {
            AttendanceNotification::updateOrCreate(
                [
                    'admin_or_user_id' => $userId,
                    'staff_id' => $staffId,
                    'attendance_date' => $attendanceDate,
                ],
                [
                    'status' => 'pending',
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'marked_count' => count($processedStaff),
            'already_marked_count' => count($alreadyMarked),
            'pending_count' => count($unmappedStaff),
        ]);
    }

    public function edit($id)
    {
        $attendance = StaffAttendence::with('staff')
            ->where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        return response()->json($attendance);
    }

public function history($staffId)
{
    $records = StaffAttendence::where('admin_or_user_id', Auth::id())
        ->where('staff_id', $staffId)
        ->orderBy('attendence_date','desc')
        ->get();

    $summary = [
        'present' => $records->where('status','present')->count(),
        'absent' => $records->where('status','absent')->count(),
        'leave' => $records->where('status','leave')->count(),
        'half_day' => $records->where('status','half_day')->count(),
    ];

    return response()->json([
        'records' => $records,
        'summary' => $summary
    ]);
}

public function update(Request $request)
{
    $request->validate([
        'attendance_id' => 'required|exists:staff_attendences,id',
        'status' => 'required|in:present,absent,leave,half_day',
    ]);

    $attendance = StaffAttendence::where('admin_or_user_id', Auth::id())
        ->findOrFail($request->attendance_id);

    $attendance->update([
        'status' => $request->status,
        'check_in' => in_array($request->status, ['present','half_day'])
                        ? $request->check_in : null,
        'check_out' => in_array($request->status, ['present','half_day'])
                        ? $request->check_out : null,
        'overtime_hours' => in_array($request->status, ['present','half_day'])
                        ? $request->overtime_hours : null,
        'remarks' => in_array($request->status, ['leave','half_day'])
                        ? $request->remarks : null,
        'marked_by_admin_id' => Auth::id(),
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Attendance updated successfully'
    ]);
}

    public function destroy($id)
    {
        $attendance = StaffAttendence::where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        $attendance->delete();

        return response()->json(['status' => 'success', 'message' => 'Attendance deleted successfully']);
    }

    // Dismiss notification temporarily (for 4 hours)
    public function dismissNotification(Request $request)
    {
        $notification = AttendanceNotification::where('admin_or_user_id', Auth::id())
            ->findOrFail($request->notification_id);

        $notification->update([
            'status' => 'dismissed',
            'dismissed_until' => now()->addHours(4), // Show again after 4 hours
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification dismissed for 4 hours'
        ]);
    }

    // Store advance payment
    public function storeAdvance(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:sales_mens,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        \App\Models\StaffRecovery::create([
            'admin_or_user_id' => Auth::id(),
            'saleman_id' => $request->staff_id,
            'date' => $request->date,
            'adjust_type' => 'plus', // Advance is added to staff balance
            'adjust_amount' => $request->amount,
            'remarks' => $request->remarks ?? 'Advance Payment',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Advance payment recorded successfully'
        ]);
    }

    // Get staff advances for current week
    public function getStaffAdvances($staffId)
    {
        $weekStart = now()->startOfWeek()->format('Y-m-d');
        $weekEnd = now()->endOfWeek()->format('Y-m-d');

        $advances = \App\Models\StaffRecovery::where('saleman_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->where('adjust_type', 'plus')
            ->get();

        $totalAdvance = $advances->sum('adjust_amount');

        return response()->json([
            'success' => true,
            'advances' => $advances,
            'total' => $totalAdvance
        ]);
    }

    public function exportPDF(Request $request)
    {
        $staffId = $request->staff_id;
        $from = $request->from;
        $to = $request->to;

        if (!$staffId) {
            return redirect()->back()->with('error', 'Please select a staff member');
        }

        $staff = Salesman::find($staffId);
        if (!$staff) {
            return redirect()->back()->with('error', 'Staff not found');
        }

        $query = StaffAttendence::where('admin_or_user_id', Auth::id())
            ->where('staff_id', $staffId);

        if ($from && $to) {
            $query->whereBetween('attendence_date', [$from, $to]);
        }

        $records = $query->orderBy('attendence_date')->get();

        // Generate date range including OFF days
        $startDate = $from ? new \DateTime($from) : ($records->first() ? new \DateTime($records->first()->attendence_date) : new \DateTime());
        $endDate = $to ? new \DateTime($to) : new \DateTime();

        $allDates = [];
        $attendanceMap = [];

        foreach ($records as $record) {
            $attendanceMap[$record->attendence_date] = $record;
        }

        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($startDate, $interval, $endDate->modify('+1 day'));

        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $allDates[] = [
                'date' => $dateStr,
                'record' => $attendanceMap[$dateStr] ?? null
            ];
        }

        // Count statistics
        $presentCount = $records->where('status', 'present')->count();
        $absentCount = $records->where('status', 'absent')->count();
        $leaveCount = $records->where('status', 'leave')->count();
        $halfCount = $records->where('status', 'half_day')->count();
        $offCount = count($allDates) - $records->count();

        $html = view('admin_panel.staff_attendance.pdf_export', compact(
            'staff', 
            'allDates', 
            'from', 
            'to',
            'presentCount',
            'absentCount',
            'leaveCount',
            'halfCount',
            'offCount'
        ))->render();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->stream('attendance_' . $staff->name . '_' . date('Y-m-d') . '.pdf');
    }


    // Get pending notifications for today
    public function getPendingNotifications()
    {
        $pendingNotifications = AttendanceNotification::where('admin_or_user_id', Auth::id())
            ->where('attendance_date', date('Y-m-d'))
            ->with('staff')
            ->get()
            ->filter(fn($n) => $n->shouldShow())
            ->values();

        return response()->json([
            'success' => true,
            'notifications' => $pendingNotifications,
            'count' => $pendingNotifications->count(),
        ]);
    }
}
