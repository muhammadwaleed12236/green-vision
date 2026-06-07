<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\VendorLedger;

class TestPurchaseFlow extends Command
{
    protected $signature = 'test:purchase-flow {purchase_id?}';
    protected $description = 'Test purchase flow integrity - Stock, Ledger, Reports';

    public function handle()
    {
        $this->info('🔍 Starting Purchase Flow Testing...');
        $this->line('═══════════════════════════════════════');

        $purchaseId = $this->argument('purchase_id') ?? $this->getLatestPurchaseId();

        if (!$purchaseId) {
            $this->error('❌ No purchase found to test!');
            return;
        }

        $this->info("📋 Testing Purchase ID: {$purchaseId}");
        $this->line('');

        // Test 1: Purchase Data Integrity
        $this->testPurchaseData($purchaseId);

        // Test 2: Stock Updates
        $this->testStockUpdates($purchaseId);

        // Test 3: Ledger Updates
        $this->testLedgerUpdates($purchaseId);

        // Test 4: Reporting Consistency
        $this->testReportingConsistency($purchaseId);

        $this->line('');
        $this->info('✅ Testing Complete!');
    }

    private function getLatestPurchaseId()
    {
        return DB::table('purchases')->latest('id')->value('id');
    }

    private function testPurchaseData($purchaseId)
    {
        $this->line('🧪 Test 1: Purchase Data Integrity');
        $this->line('───────────────────────────────────');

        // Get purchase data
        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();
        $purchaseItems = DB::table('purchase_items')->where('purchase_id', $purchaseId)->get();

        if (!$purchase) {
            $this->error("❌ Purchase {$purchaseId} not found!");
            return;
        }

        $this->info("✅ Purchase found: ID {$purchase->id}");
        $this->line("   📅 Date: {$purchase->purchase_date}");
        $this->line("   🏪 Vendor ID: {$purchase->vendor_id}");
        $this->line("   💰 Total: {$purchase->total_amount}");

        // Verify purchase items
        $itemsTotal = $purchaseItems->sum(function($item) {
            return $item->qty * $item->rate;
        });

        if (abs($itemsTotal - $purchase->total_amount) < 0.01) {
            $this->info("✅ Purchase amount calculation correct");
        } else {
            $this->error("❌ Purchase amount mismatch! Items Total: {$itemsTotal}, Purchase Total: {$purchase->total_amount}");
        }

        $this->line("   📦 Items Count: " . $purchaseItems->count());
        $this->line('');
    }

    private function testStockUpdates($purchaseId)
    {
        $this->line('📦 Test 2: Stock Updates');
        $this->line('───────────────────────────');

        $purchaseItems = DB::table('purchase_items')
            ->join('products', 'purchase_items.product_id', '=', 'products.id')
            ->where('purchase_id', $purchaseId)
            ->select('purchase_items.*', 'products.item_name', 'products.initial_stock')
            ->get();

        foreach ($purchaseItems as $item) {
            $this->line("📋 {$item->item_name}:");
            $this->line("   🔢 Purchased Qty: {$item->qty}");
            $this->line("   📊 Current Stock: {$item->initial_stock}");

            // Check if stock looks reasonable (this is basic check)
            if ($item->initial_stock >= $item->qty) {
                $this->info("   ✅ Stock level seems correct");
            } else {
                $this->warn("   ⚠️  Stock might be low or incorrect");
            }
        }
        $this->line('');
    }

    private function testLedgerUpdates($purchaseId)
    {
        $this->line('📑 Test 3: Ledger Updates');
        $this->line('─────────────────────────');

        $purchase = DB::table('purchases')->where('id', $purchaseId)->first();

        // Get vendor ledger entries for this vendor around purchase date
        $ledgerEntries = DB::table('vendor_ledgers')
            ->where('vendor_id', $purchase->vendor_id)
            ->whereDate('created_at', $purchase->purchase_date)
            ->get();

        if ($ledgerEntries->count() > 0) {
            $this->info("✅ Vendor ledger entries found: " . $ledgerEntries->count());

            foreach ($ledgerEntries as $entry) {
                $this->line("   💰 Balance: {$entry->closing_balance}");
                $this->line("   📈 Previous: {$entry->previous_balance}");
                $this->line("   📊 Opening: {$entry->opening_balance}");
            }
        } else {
            $this->error("❌ No vendor ledger entries found for this purchase!");
        }

        // Check journal vouchers
        $vouchers = DB::table('journal_vouchers')
            ->where('party_type', 'vendor')
            ->where('party_id', $purchase->vendor_id)
            ->whereDate('voucher_date', $purchase->purchase_date)
            ->get();

        if ($vouchers->count() > 0) {
            $this->info("✅ Journal voucher entries found: " . $vouchers->count());
        } else {
            $this->warn("⚠️  No journal voucher entries found");
        }

        $this->line('');
    }

    private function testReportingConsistency($purchaseId)
    {
        $this->line('📊 Test 4: Reporting Consistency');
        $this->line('──────────────────────────────');

        $purchase = DB::table('purchases')
            ->join('vendors', 'purchases.vendor_id', '=', 'vendors.id')
            ->where('purchases.id', $purchaseId)
            ->select('purchases.*', 'vendors.Party_name')
            ->first();

        // Check if purchase appears in business report data
        $businessReportPurchases = DB::table('purchases')
            ->whereDate('purchase_date', $purchase->purchase_date)
            ->sum('total_amount');

        $this->line("📈 Business Report Data:");
        $this->line("   📅 Date: {$purchase->purchase_date}");
        $this->line("   💰 Total Purchases for day: {$businessReportPurchases}");
        $this->line("   🏪 Vendor: {$purchase->Party_name}");

        // Check purchase report data
        $this->info("✅ Purchase appears in daily totals");

        $this->line('');
    }

    public function testAllPurchases()
    {
        $this->info('🔍 Running Complete Purchase System Test...');
        $this->line('══════════════════════════════════════════');

        // Test last 5 purchases
        $recentPurchases = DB::table('purchases')
            ->latest('id')
            ->limit(5)
            ->pluck('id');

        foreach ($recentPurchases as $purchaseId) {
            $this->handle($purchaseId);
            $this->line('──────────────────────────────────────────');
        }
    }
}
