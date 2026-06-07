<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\VendorLedger;
use Carbon\Carbon;

class QATestController extends Controller
{
    public function dashboard()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userId = Auth::id();

        // Get recent test data
        $recentPurchases = Purchase::with('vendor')
            ->where('admin_or_user_id', $userId)
            ->latest()
            ->limit(5)
            ->get();

        $stockIssues = $this->checkStockIssues();
        $ledgerIssues = $this->checkLedgerIssues($userId);
        $dataConsistency = $this->checkDataConsistency($userId);

        return view('admin_panel.qa_testing.dashboard', [
            'recentPurchases' => $recentPurchases,
            'stockIssues' => $stockIssues,
            'ledgerIssues' => $ledgerIssues,
            'dataConsistency' => $dataConsistency,
        ]);
    }

    public function testPurchaseFlow($purchaseId)
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $results = [];

        // Test 1: Purchase Data
        $results['purchase_data'] = $this->testPurchaseData($purchaseId);

        // Test 2: Stock Updates
        $results['stock_updates'] = $this->testStockUpdates($purchaseId);

        // Test 3: Ledger Updates
        $results['ledger_updates'] = $this->testLedgerUpdates($purchaseId);

        // Test 4: Report Consistency
        $results['report_consistency'] = $this->testReportConsistency($purchaseId);

        return response()->json([
            'success' => true,
            'purchase_id' => $purchaseId,
            'test_results' => $results,
            'overall_status' => $this->calculateOverallStatus($results)
        ]);
    }

    private function testPurchaseData($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            return [
                'status' => 'FAIL',
                'message' => 'Purchase not found',
                'details' => []
            ];
        }

        // Parse purchase data from longtext fields (handle JSON format)
        $items = [];
        $rates = [];
        $pcs = [];
        $amounts = [];

        if ($purchase->item) {
            $itemData = json_decode($purchase->item, true);
            $items = is_array($itemData) ? $itemData : explode(',', $purchase->item);
        }

        if ($purchase->rate) {
            $rateData = json_decode($purchase->rate, true);
            $rates = is_array($rateData) ? $rateData : explode(',', $purchase->rate);
        }

        if ($purchase->pcs) {
            $pcsData = json_decode($purchase->pcs, true);
            $pcs = is_array($pcsData) ? $pcsData : explode(',', $purchase->pcs);
        }

        if ($purchase->amount) {
            $amountData = json_decode($purchase->amount, true);
            $amounts = is_array($amountData) ? $amountData : explode(',', $purchase->amount);
        }

        // Calculate items total from amount field
        $itemsTotal = 0;
        if (!empty($amounts)) {
            $itemsTotal = array_sum(array_map('floatval', $amounts));
        }

        $isAmountCorrect = abs($itemsTotal - $purchase->grand_total) < 0.01;

        return [
            'status' => $isAmountCorrect ? 'PASS' : 'FAIL',
            'message' => $isAmountCorrect ? 'Purchase data is correct' : 'Amount calculation mismatch',
            'details' => [
                'purchase_total' => $purchase->grand_total,
                'items_total' => $itemsTotal,
                'items_count' => count($items),
                'vendor_id' => $purchase->vendor_id,
                'date' => $purchase->purchase_date,
                'items' => array_slice($items, 0, 3), // Show first 3 items
                'rates' => array_slice($rates, 0, 3)  // Show first 3 rates
            ]
        ];
    }

    private function testStockUpdates($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);

        if (!$purchase) {
            return [
                'status' => 'FAIL',
                'message' => 'Purchase not found',
                'details' => []
            ];
        }

        // Parse purchase data (handle JSON format)
        $items = [];
        $pcs = [];

        if ($purchase->item) {
            $itemData = json_decode($purchase->item, true);
            $items = is_array($itemData) ? $itemData : explode(',', $purchase->item);
        }

        if ($purchase->pcs) {
            $pcsData = json_decode($purchase->pcs, true);
            $pcs = is_array($pcsData) ? $pcsData : explode(',', $purchase->pcs);
        }

        $stockIssues = [];
        $itemsChecked = 0;

        // Check each item
        for ($i = 0; $i < count($items); $i++) {
            if (isset($items[$i]) && !empty(trim($items[$i]))) {
                $itemName = trim($items[$i]);
                $purchasedQty = isset($pcs[$i]) ? floatval($pcs[$i]) : 0;

                // Find product by name (approximate match)
                $product = DB::table('products')
                    ->where('item_name', 'LIKE', "%{$itemName}%")
                    ->first();

                if ($product) {
                    $itemsChecked++;
                    if ($product->initial_stock < $purchasedQty) {
                        $stockIssues[] = [
                            'product' => $product->item_name,
                            'issue' => 'Current stock less than purchased qty',
                            'purchased' => $purchasedQty,
                            'current' => $product->initial_stock
                        ];
                    }
                }
            }
        }

        return [
            'status' => empty($stockIssues) ? 'PASS' : 'WARNING',
            'message' => empty($stockIssues) ? 'Stock updates look correct' : 'Potential stock issues found',
            'details' => [
                'items_checked' => $itemsChecked,
                'issues_found' => $stockIssues
            ]
        ];
    }

    private function testLedgerUpdates($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);

        // Check for vendor ledger entry
        $ledgerEntry = VendorLedger::where('vendor_id', $purchase->vendor_id)
            ->whereDate('created_at', $purchase->purchase_date)
            ->first();

        // Check for journal voucher
        $voucher = DB::table('journal_vouchers')
            ->where('party_type', 'vendor')
            ->where('party_id', $purchase->vendor_id)
            ->whereDate('voucher_date', $purchase->purchase_date)
            ->first();

        $hasLedger = !is_null($ledgerEntry);
        $hasVoucher = !is_null($voucher);

        return [
            'status' => $hasLedger ? 'PASS' : 'WARNING',
            'message' => $hasLedger ? 'Ledger entries found' : 'Ledger entry might be missing',
            'details' => [
                'vendor_ledger_exists' => $hasLedger,
                'journal_voucher_exists' => $hasVoucher,
                'ledger_balance' => $ledgerEntry ? $ledgerEntry->closing_balance : null,
                'voucher_amount' => $voucher ? ($voucher->debit_amount ?? $voucher->credit_amount) : null,
                'purchase_amount' => $purchase->grand_total,
                'date' => $purchase->purchase_date
            ]
        ];
    }

    private function checkStockIssues()
    {
        // Check for negative stocks
        $negativeStocks = Product::where('initial_stock', '<', 0)->count();

        // Check for very low stocks
        $lowStocks = Product::where('initial_stock', '<=', 5)
            ->where('initial_stock', '>', 0)
            ->count();

        return [
            'negative_stocks' => $negativeStocks,
            'low_stocks' => $lowStocks,
            'status' => $negativeStocks > 0 ? 'CRITICAL' : ($lowStocks > 5 ? 'WARNING' : 'OK')
        ];
    }

    private function checkLedgerIssues($userId)
    {
        // Check for unbalanced ledgers (simplified check for vendor ledgers)
        $unbalancedLedgers = DB::table('vendor_ledgers')
            ->where('admin_or_user_id', $userId)
            ->whereRaw('ABS(opening_balance - closing_balance + previous_balance) > 0.01')
            ->count();

        return [
            'unbalanced_ledgers' => $unbalancedLedgers,
            'status' => $unbalancedLedgers > 0 ? 'CRITICAL' : 'OK'
        ];
    }

    private function checkDataConsistency($userId)
    {
        // Check purchase vs ledger consistency (simplified)
        $inconsistencies = DB::select("
            SELECT COUNT(*) as count FROM (
                SELECT p.id, p.grand_total, vl.closing_balance, vl.previous_balance
                FROM purchases p
                LEFT JOIN vendor_ledgers vl ON p.vendor_id = vl.vendor_id
                    AND DATE(p.purchase_date) = DATE(vl.created_at)
                WHERE p.admin_or_user_id = ?
                    AND vl.id IS NOT NULL
                    AND ABS((vl.closing_balance - vl.previous_balance) - p.grand_total) > 0.01
            ) as subquery
        ", [$userId]);

        return [
            'inconsistencies' => $inconsistencies[0]->count ?? 0,
            'status' => ($inconsistencies[0]->count ?? 0) > 0 ? 'WARNING' : 'OK'
        ];
    }

    private function testReportConsistency($purchaseId)
    {
        $purchase = Purchase::find($purchaseId);

        // Check if purchase is included in daily totals
        $dailyTotal = DB::table('purchases')
            ->whereDate('purchase_date', $purchase->purchase_date)
            ->where('admin_or_user_id', $purchase->admin_or_user_id)
            ->sum('grand_total');

        $isIncluded = $dailyTotal >= $purchase->grand_total;

        return [
            'status' => $isIncluded ? 'PASS' : 'FAIL',
            'message' => $isIncluded ? 'Purchase included in reports' : 'Purchase missing from reports',
            'details' => [
                'purchase_amount' => $purchase->grand_total,
                'daily_total' => $dailyTotal,
                'date' => $purchase->purchase_date
            ]
        ];
    }

    private function calculateOverallStatus($results)
    {
        $statuses = array_column($results, 'status');

        if (in_array('FAIL', $statuses)) {
            return 'FAIL';
        } elseif (in_array('WARNING', $statuses)) {
            return 'WARNING';
        } else {
            return 'PASS';
        }
    }

    public function quickHealthCheck()
    {
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userId = Auth::id();

        return response()->json([
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'stock_health' => $this->checkStockIssues(),
            'ledger_health' => $this->checkLedgerIssues($userId),
            'data_consistency' => $this->checkDataConsistency($userId),
            'recent_purchases' => Purchase::where('admin_or_user_id', $userId)
                ->whereDate('created_at', today())
                ->count()
        ]);
    }
}
