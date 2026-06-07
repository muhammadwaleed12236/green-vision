<?php

namespace App\Http\Controllers;

use App\Models\StaffSalaryPayment;
use App\Models\StaffAdvance;
use App\Models\StaffAttendence;
use App\Models\Salesman;
use App\Traits\AutoJournalVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffSalaryController extends Controller
{
    use AutoJournalVoucher;
    // Salary Payment Page
    public function index()
    {
        $staffs = Salesman::where('admin_or_user_id', Auth::id())
            ->where('status', 1)
            ->get();

        $payments = StaffSalaryPayment::with('staff')
            ->where('admin_or_user_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('admin_panel.staff_attendance.salary', compact('staffs', 'payments'));
    }

    // Get Staff Info for Salary Calculation (Date Range Based)
    public function getInfo($staffId, Request $request)
    {
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        if (!$fromDate || !$toDate) {
            return response()->json(['error' => 'Please select date range'], 400);
        }

        $staff = Salesman::find($staffId);

        // Check for overlapping payments
        $overlapCheck = $this->checkOverlap($staffId, $fromDate, $toDate);

        // Get last payment info
        $lastPayment = StaffSalaryPayment::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->latest('to_date')
            ->first();

        // Get attendance records for date range (with details)
        $attendanceRecords = StaffAttendence::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->whereBetween('attendence_date', [$fromDate, $toDate])
            ->orderBy('attendence_date')
            ->get();

        // Prepare attendance history for display
        $attendanceHistory = $attendanceRecords->map(function($record) {
            return [
                'date' => Carbon::parse($record->attendence_date)->format('d M'),
                'day' => Carbon::parse($record->attendence_date)->format('D'),
                'status' => $record->status,
                'status_badge' => $this->getStatusBadge($record->status),
            ];
        });

        $daysPresent = $attendanceRecords->where('status', 'present')->count();
        $daysHalfDay = $attendanceRecords->where('status', 'half_day')->count();
        $daysAbsent = $attendanceRecords->where('status', 'absent')->count();
        $daysLeave = $attendanceRecords->where('status', 'leave')->count();

        // Calculate working days in range
        $start = Carbon::parse($fromDate);
        $end = Carbon::parse($toDate);
        $totalDays = $start->diffInDays($end) + 1;

        // Get pending advances (both salary and additional)
        $salaryAdvance = StaffAdvance::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->where('advance_type', 'salary')
            ->where('status', '!=', 'cleared')
            ->sum('remaining_amount');

        $additionalAdvance = StaffAdvance::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->where('advance_type', 'additional')
            ->where('status', '!=', 'cleared')
            ->sum('remaining_amount');

        // Staff's set salary (could be weekly 7000, monthly 30000, etc.)
        $setSalary = $staff->salary ?? 0;

        // Per day = Set Salary / Selected Days
        // Example: 5000 salary, 1-7 selected = 5000/7 = 714 per day
        // Example: 1000 salary, 1-30 selected = 1000/30 = 33 per day
        $perDaySalary = $totalDays > 0 ? ($setSalary / $totalDays) : 0;

        // Calculate worked days (present + half days count as 0.5)
        $workedDays = $daysPresent + ($daysHalfDay * 0.5);

        // Gross salary = Per Day × Worked Days
        $grossSalary = round($perDaySalary * $workedDays, 0);

        // Full salary for the period (the set salary without any deduction)
        $fullPeriodSalary = $setSalary;

        // Absent deduction = Per Day × Absent Days
        $absentDeduction = round($perDaySalary * $daysAbsent, 0);

        // Half day deduction (0.5 per half day)
        $halfDayDeduction = round($perDaySalary * $daysHalfDay * 0.5, 0);

        return response()->json([
            'staff_name' => $staff->name,
            'set_salary' => $setSalary,
            'per_day_salary' => round($perDaySalary, 0),
            'total_days' => $totalDays,
            'days_present' => $daysPresent,
            'days_half_day' => $daysHalfDay,
            'days_absent' => $daysAbsent,
            'days_leave' => $daysLeave,
            'worked_days' => $workedDays,
            'gross_salary' => $grossSalary,
            'full_period_salary' => $fullPeriodSalary,
            'absent_deduction' => $absentDeduction,
            'salary_advance' => $salaryAdvance,
            'additional_advance' => $additionalAdvance,
            'total_advance' => $salaryAdvance + $additionalAdvance,
            'last_paid_date' => $lastPayment ? $lastPayment->to_date : null,
            'last_paid_formatted' => $lastPayment ? Carbon::parse($lastPayment->to_date)->format('d M Y') : 'No previous payment',
            'overlap' => $overlapCheck,
            'attendance_history' => $attendanceHistory,
        ]);
    }

    // Get status badge HTML
    private function getStatusBadge($status)
    {
        switch ($status) {
            case 'present':
                return '<span class="badge bg-success">P</span>';
            case 'absent':
                return '<span class="badge bg-danger">A</span>';
            case 'half_day':
                return '<span class="badge bg-warning">H</span>';
            case 'leave':
                return '<span class="badge bg-info">L</span>';
            default:
                return '<span class="badge bg-secondary">-</span>';
        }
    }

    // Check for overlapping payment dates
    private function checkOverlap($staffId, $fromDate, $toDate)
    {
        $overlapping = StaffSalaryPayment::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->where(function($q) use ($fromDate, $toDate) {
                $q->whereBetween('from_date', [$fromDate, $toDate])
                  ->orWhereBetween('to_date', [$fromDate, $toDate])
                  ->orWhere(function($q2) use ($fromDate, $toDate) {
                      $q2->where('from_date', '<=', $fromDate)
                         ->where('to_date', '>=', $toDate);
                  });
            })
            ->first();

        if ($overlapping) {
            return [
                'has_overlap' => true,
                'message' => 'Payment from ' . Carbon::parse($overlapping->from_date)->format('d M') .
                            ' to ' . Carbon::parse($overlapping->to_date)->format('d M Y') .
                            ' is already cleared. Please select different dates.',
                'paid_from' => $overlapping->from_date,
                'paid_to' => $overlapping->to_date,
            ];
        }

        return ['has_overlap' => false];
    }

    // Store Salary Payment
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:sales_mens,id',
            'from_date' => 'required|date',
            'to_date' => 'required|date|after_or_equal:from_date',
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0',
        ]);

        $staffId = $request->staff_id;
        $fromDate = $request->from_date;
        $toDate = $request->to_date;

        // Check for overlap
        $overlapCheck = $this->checkOverlap($staffId, $fromDate, $toDate);
        if ($overlapCheck['has_overlap']) {
            return response()->json([
                'success' => false,
                'message' => $overlapCheck['message']
            ], 400);
        }

        $staff = Salesman::find($staffId);

        // Get attendance info
        $attendance = StaffAttendence::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->whereBetween('attendence_date', [$fromDate, $toDate])
            ->get();

        $daysPresent = $attendance->where('status', 'present')->count();
        $daysHalfDay = $attendance->where('status', 'half_day')->count();
        $daysAbsent = $attendance->where('status', 'absent')->count();

        // Deduct salary advances
        $salaryAdvanceDeducted = 0;
        if ($request->deduct_salary_advance) {
            $salaryAdvances = StaffAdvance::where('staff_id', $staffId)
                ->where('admin_or_user_id', Auth::id())
                ->where('advance_type', 'salary')
                ->where('status', '!=', 'cleared')
                ->get();

            foreach ($salaryAdvances as $advance) {
                $salaryAdvanceDeducted += $advance->remaining_amount;
                $advance->recovered_amount += $advance->remaining_amount;
                $advance->remaining_amount = 0;
                $advance->status = 'cleared';
                $advance->save();
            }
        }

        // Deduct additional advances (partial amount as entered by user)
        $additionalAdvanceDeducted = 0;
        if ($request->deduct_additional_advance && $request->additional_deduct_amount > 0) {
            $amountToDeduct = $request->additional_deduct_amount;

            $additionalAdvances = StaffAdvance::where('staff_id', $staffId)
                ->where('admin_or_user_id', Auth::id())
                ->where('advance_type', 'additional')
                ->where('status', '!=', 'cleared')
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($additionalAdvances as $advance) {
                if ($amountToDeduct <= 0) break;

                $deductFromThis = min($amountToDeduct, $advance->remaining_amount);
                $advance->recovered_amount += $deductFromThis;
                $advance->remaining_amount -= $deductFromThis;

                if ($advance->remaining_amount <= 0) {
                    $advance->status = 'cleared';
                }
                $advance->save();

                $additionalAdvanceDeducted += $deductFromThis;
                $amountToDeduct -= $deductFromThis;
            }
        }

        // Create salary payment record
        $paymentMonth = Carbon::parse($fromDate)->format('Y-m');

        $salaryPayment = StaffSalaryPayment::create([
            'admin_or_user_id' => Auth::id(),
            'staff_id' => $staffId,
            'payment_month' => $paymentMonth,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'basic_salary' => $staff->salary ?? 0,
            'advance_deducted' => $salaryAdvanceDeducted,
            'additional_advance_deducted' => $additionalAdvanceDeducted,
            'amount_paid' => $request->amount,
            'payment_date' => $request->payment_date,
            'days_present' => $daysPresent + $daysHalfDay,
            'days_absent' => $daysAbsent,
            'remarks' => $request->remarks,
        ]);

        // 🔥 Create Journal Voucher Entry for Staff Salary Payment
        $this->createStaffPaymentJournal(
            $staffId,
            $staff->name,
            $request->amount,
            $request->payment_date,
            'salary_payment',
            $request->remarks ?: "Salary payment to {$staff->name} for period {$fromDate} to {$toDate}",
            $salaryPayment->id
        );

        $totalDeducted = $salaryAdvanceDeducted + $additionalAdvanceDeducted;
        $message = 'Salary paid successfully!';
        if ($totalDeducted > 0) {
            $message .= ' PKR ' . number_format($totalDeducted, 0) . ' advance deducted.';
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    // Salary Receipt
    public function receipt($id)
    {
        $payment = StaffSalaryPayment::with('staff')
            ->where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        return view('admin_panel.staff_attendance.salary_receipt', compact('payment'));
    }

    // Get single payment for edit
    public function show($id)
    {
        $payment = StaffSalaryPayment::with('staff')
            ->where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }

    // Update salary payment
    public function update(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
        ]);

        $payment = StaffSalaryPayment::where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        $payment->update([
            'amount_paid' => $request->amount,
            'payment_date' => $request->payment_date,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Payment updated successfully!'
        ]);
    }

    // Delete salary payment
    public function destroy($id)
    {
        $payment = StaffSalaryPayment::where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        // Restore advances if they were deducted
        if ($payment->advance_deducted > 0) {
            $salaryAdvances = StaffAdvance::where('staff_id', $payment->staff_id)
                ->where('admin_or_user_id', Auth::id())
                ->where('advance_type', 'salary')
                ->where('status', 'cleared')
                ->get();

            foreach ($salaryAdvances as $advance) {
                $advance->remaining_amount = $advance->amount;
                $advance->recovered_amount = 0;
                $advance->status = 'pending';
                $advance->save();
            }
        }

        $payment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment deleted successfully!'
        ]);
    }
}
