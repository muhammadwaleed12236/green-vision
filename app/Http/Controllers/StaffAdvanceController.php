<?php

namespace App\Http\Controllers;

use App\Models\StaffAdvance;
use App\Models\Salesman;
use App\Models\StaffAttendence;
use App\Traits\AutoJournalVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class StaffAdvanceController extends Controller
{
    use AutoJournalVoucher;
    // Advance List Page
    public function index(Request $request)
    {
        $query = StaffAdvance::with('staff')
            ->where('admin_or_user_id', Auth::id());

        if ($request->staff_id) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->type) {
            $query->where('advance_type', $request->type);
        }

        $advances = $query->latest()->paginate(20);

        $staffs = Salesman::where('admin_or_user_id', Auth::id())
            ->where('status', 1)
            ->get();

        return view('admin_panel.staff_attendance.advance', compact('advances', 'staffs'));
    }

    // Store Advance
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|exists:sales_mens,id',
            'advance_type' => 'required|in:salary,additional',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);

        $staffAdvance = StaffAdvance::create([
            'admin_or_user_id' => Auth::id(),
            'staff_id' => $request->staff_id,
            'advance_type' => $request->advance_type,
            'amount' => $request->amount,
            'remaining_amount' => $request->amount,
            'date' => $request->date,
            'remarks' => $request->remarks,
            'status' => 'pending',
        ]);

        // 🔥 Create Journal Voucher Entry for Staff Advance
        $staff = Salesman::find($request->staff_id);
        $this->createStaffPaymentJournal(
            $request->staff_id,
            $staff->name,
            $request->amount,
            $request->date,
            'advance_payment',
            $request->remarks ?: "{$request->advance_type} advance given to {$staff->name}",
            $staffAdvance->id
        );

        return response()->json([
            'success' => true,
            'message' => 'Advance recorded successfully'
        ]);
    }

    // Get Staff Balance
    public function getBalance($staffId)
    {
        $salaryAdvance = StaffAdvance::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->where('advance_type', 'salary')
            ->where('status', '!=', 'cleared')
            ->sum('remaining_amount');

        $additionalLoan = StaffAdvance::where('staff_id', $staffId)
            ->where('admin_or_user_id', Auth::id())
            ->where('advance_type', 'additional')
            ->where('status', '!=', 'cleared')
            ->sum('remaining_amount');

        return response()->json([
            'salary_advance' => $salaryAdvance,
            'additional_loan' => $additionalLoan,
            'total' => $salaryAdvance + $additionalLoan
        ]);
    }

    // Recover Advance
    public function recover(Request $request)
    {
        $request->validate([
            'advance_id' => 'required|exists:staff_advances,id',
            'recovery_amount' => 'required|numeric|min:1',
        ]);

        $advance = StaffAdvance::where('admin_or_user_id', Auth::id())
            ->findOrFail($request->advance_id);

        $recoveryAmount = min($request->recovery_amount, $advance->remaining_amount);

        $advance->recovered_amount += $recoveryAmount;
        $advance->remaining_amount -= $recoveryAmount;

        if ($advance->remaining_amount <= 0) {
            $advance->status = 'cleared';
            $advance->remaining_amount = 0;
        } else {
            $advance->status = 'partially_paid';
        }

        $advance->save();

        return response()->json([
            'success' => true,
            'message' => 'Recovery recorded successfully'
        ]);
    }

    // Delete Advance
    public function destroy($id)
    {
        $advance = StaffAdvance::where('admin_or_user_id', Auth::id())
            ->findOrFail($id);

        $advance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Advance deleted successfully'
        ]);
    }

    // Staff Ledger View
    public function ledger(Request $request)
    {
        $staffs = Salesman::where('admin_or_user_id', Auth::id())
            ->where('status', 1)
            ->get();

        $data = compact('staffs');

        if ($request->staff_id) {
            $staffId = $request->staff_id;
            $from = $request->from ?? Carbon::now()->startOfMonth()->format('Y-m-d');
            $to = $request->to ?? Carbon::now()->format('Y-m-d');

            $selectedStaff = Salesman::find($staffId);

            // Attendance Summary
            $attendanceRecords = StaffAttendence::where('staff_id', $staffId)
                ->where('admin_or_user_id', Auth::id())
                ->whereBetween('attendence_date', [$from, $to])
                ->orderBy('attendence_date', 'desc')
                ->get();

            $attendanceSummary = [
                'present' => $attendanceRecords->where('status', 'present')->count(),
                'absent' => $attendanceRecords->where('status', 'absent')->count(),
                'leave' => $attendanceRecords->where('status', 'leave')->count(),
                'half_day' => $attendanceRecords->where('status', 'half_day')->count(),
            ];

            // Advance Summary
            $advanceRecords = StaffAdvance::where('staff_id', $staffId)
                ->where('admin_or_user_id', Auth::id())
                ->whereBetween('date', [$from, $to])
                ->orderBy('date', 'desc')
                ->get();

            $advanceSummary = [
                'salary' => $advanceRecords->where('advance_type', 'salary')->where('status', '!=', 'cleared')->sum('remaining_amount'),
                'additional' => $advanceRecords->where('advance_type', 'additional')->where('status', '!=', 'cleared')->sum('remaining_amount'),
            ];

            $totalPending = $advanceSummary['salary'] + $advanceSummary['additional'];

            // Payment History (Combined ledger entries)
            $paymentHistory = collect();

            // Add advances as debit entries
            foreach ($advanceRecords as $adv) {
                $paymentHistory->push((object)[
                    'date' => $adv->date,
                    'type' => $adv->advance_type == 'salary' ? 'Salary Advance' : 'Additional Loan',
                    'description' => $adv->remarks,
                    'debit' => $adv->amount,
                    'credit' => 0,
                    'balance' => 0,
                ]);
            }

            // Sort by date
            $paymentHistory = $paymentHistory->sortByDesc('date')->values();

            // Calculate running balance
            $runningBalance = 0;
            $paymentHistory = $paymentHistory->reverse()->map(function($item) use (&$runningBalance) {
                $runningBalance += $item->debit - $item->credit;
                $item->balance = $runningBalance;
                return $item;
            })->reverse()->values();

            $data = array_merge($data, compact(
                'selectedStaff',
                'attendanceRecords',
                'attendanceSummary',
                'advanceRecords',
                'advanceSummary',
                'totalPending',
                'paymentHistory'
            ));
        }

        return view('admin_panel.staff_attendance.ledger', $data);
    }
}
