<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\Purchase;
use App\Models\VendorLedger;
use App\Models\JournalVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_store_saves_paid_amount_and_updates_ledger_and_voucher()
    {
        // 1. Setup User and Vendor
        $superAdminRole = \App\Models\Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        $user = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'usertype' => 'admin',
        ]);

        $vendor = Vendor::create([
            'admin_or_user_id' => $user->id,
            'Party_name' => 'Test Vendor',
            'Party_code' => 'V-TEST-001',
            'opening_balance' => 5000,
        ]);

        // 2. Mock Request Data
        $data = [
            'purchase_date' => now()->format('Y-m-d'),
            'party_code' => 'V-TEST-001',
            'party_name' => $vendor->id, // Vendor ID
            'item_name' => ['Item A'],
            'rate' => [10000],
            'unit' => ['pcs'],
            'pcs' => [1],
            'discount' => [0],
            'amount' => [10000],
            'grand_total' => 10000,
            'paid_amount' => 3000, // <--- Our new field!
        ];

        // 3. Act - Store Purchase
        $response = $this->actingAs($user)
            ->post(route('store-Purchase'), $data);

        // 4. Assert Purchase is stored with paid_amount
        $purchase = Purchase::first();
        $this->assertNotNull($purchase);
        $this->assertEquals(10000, $purchase->grand_total);
        $this->assertEquals(3000, $purchase->paid_amount);

        // 5. Assert Vendor Ledger is updated correctly:
        // opening_balance (5000) + grand_total (10000) - paid_amount (3000) = 12000
        $ledger = VendorLedger::where('vendor_id', $vendor->id)->latest()->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(12000, $ledger->closing_balance);

        // 6. Assert Journal Voucher payment records the correct debit_amount (3000)
        $voucher = JournalVoucher::where('reference_type', 'purchase')
            ->where('reference_id', $purchase->id)
            ->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(3000, $voucher->debit_amount);
    }
}
