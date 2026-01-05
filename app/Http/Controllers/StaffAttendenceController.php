<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendence;
use App\Models\Salesman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    $records = $query->latest('attendence_date')->paginate(30);

    $staffs = Salesman::where('admin_or_user_id', Auth::id())
        ->where('status', 1)
        ->get();

    return view('admin_panel.staff_attendance.index', compact('records','staffs'));
}

    public function store(Request $request)
    {
        $request->validate([
            'attendance_date' => 'required|date',
            'attendance' => 'required|array',
        ]);

        foreach ($request->attendance as $staffId => $row) {
            StaffAttendence::updateOrCreate(
                [
                    'staff_id' => $staffId,
                    'attendence_date' => $request->attendance_date, // ✅ YE CHANGE
                ],
                [
                    'admin_or_user_id' => Auth::id(),
                    'status' => $row['status'],
                    'check_in' => $row['check_in'] ?? null,
                    'check_out' => $row['check_out'] ?? null,
                    'overtime_hours' => $row['overtime'] ?? null,
                    'remarks' => $row['remarks'] ?? null,
                    'marked_by_admin_id' => Auth::id(),
                ]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance saved successfully'
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

        // present & half_day
        'check_in' => in_array($request->status, ['present','half_day'])
                        ? $request->check_in : null,

        'check_out' => in_array($request->status, ['present','half_day'])
                        ? $request->check_out : null,

        // VARCHAR – text / number dono allow
        'overtime_hours' => in_array($request->status, ['present','half_day'])
                        ? $request->overtime_hours : null,

        // leave & half_day
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
}