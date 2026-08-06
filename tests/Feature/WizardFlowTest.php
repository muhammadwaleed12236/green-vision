<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\LocalSale;
use App\Models\Purchase;
use App\Models\CustomerLedger;
use App\Models\VendorLedger;
use App\Models\JournalVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WizardFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_wizard_quick_sale_and_payment_flow()
    {
        // 1. Setup User and Customer
        $superAdminRole = \App\Models\Role::updateOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin']
        );

        $user = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'usertype' => 'admin',
        ]);

        $customer = Customer::create([
            'customer_name' => 'Test Customer',
            'shop_name' => 'Test Shop',
            'phone_number' => '12345678',
            'address' => 'Test Address',
            'admin_or_user_id' => $user->id,
        ]);

        // 2. Quick Sale (starts as estimate)
        $response1 = $this->actingAs($user)
            ->postJson(route('wizard.api.sale.quick'), [
                'party_type' => 'customer',
                'customer_id' => $customer->id,
                'products' => [
                    [
                        'item_name' => 'Product A',
                        'rate' => 200,
                        'qty' => 1,
                    ]
                ],
            ]);

        $response1->assertStatus(200);
        $saleId = $response1->json('sale.id');
        $this->assertNotNull($saleId);

        $sale = LocalSale::find($saleId);
        $this->assertEquals('estimate', $sale->sale_type);

        // 3. Make Payment on the Sale via Wizard
        $response2 = $this->actingAs($user)
            ->postJson(route('wizard.api.payment.create'), [
                'sale_id' => $saleId,
                'payment_amount' => 200,
                'discount' => 0,
            ]);

        $response2->assertStatus(200);

        // Assert Sale conversion to completed sale
        $saleFresh = $sale->fresh();
        $this->assertEquals('sale', $saleFresh->sale_type);
        $this->assertEquals(200, $saleFresh->advance_amount);
        $this->assertEquals(0, $saleFresh->remaining_amount);

        // Assert Journal Voucher receipt is created for the payment amount
        $voucher = JournalVoucher::where('reference_type', 'local_sale')
            ->where('reference_id', $saleId)
            ->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(200, $voucher->credit_amount);

        // Assert customer ledger has correct balance (0)
        $ledger = CustomerLedger::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(0, $ledger->closing_balance);

        // Assert no recovery record was created
        $this->assertNull(\App\Models\CustomerRecovery::first());
    }

    public function test_wizard_purchase_creation_creates_payment_journal_voucher()
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
            'opening_balance' => 0,
        ]);

        // 2. Create Wizard Purchase with payment
        $response = $this->actingAs($user)
            ->postJson(route('wizard.api.purchase.create'), [
                'vendor_id' => $vendor->id,
                'payment_amount' => 300,
                'products' => [
                    [
                        'item_name' => 'Product A',
                        'rate' => 1000,
                        'pcs' => 1,
                    ]
                ],
            ]);

        $response->assertStatus(200);
        $purchaseId = $response->json('purchase_id');
        $this->assertNotNull($purchaseId);

        // Assert Purchase is created
        $purchase = Purchase::find($purchaseId);
        $this->assertNotNull($purchase);
        $this->assertEquals(1000, $purchase->grand_total);

        // Assert Vendor Ledger closing balance = grand_total (1000) - payment (300) = 700
        $ledger = VendorLedger::where('vendor_id', $vendor->id)->latest()->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(700, $ledger->closing_balance);

        // Assert Journal Voucher payment records the correct debit_amount (300)
        $voucher = JournalVoucher::where('reference_type', 'purchase')
            ->where('reference_id', $purchaseId)
            ->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(300, $voucher->debit_amount);

        // 3. Query the Vendor Ledger Report API to check that only 1 payment (no double-counting) is loaded, and balance is correct
        $apiResponse = $this->actingAs($user)
            ->getJson(route('fetch-vendor-ledger', [
                'Vendor_id' => $vendor->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $apiResponse->assertStatus(200);
        $closingBalance = (float) $apiResponse->json('closing_balance');
        $this->assertEquals(700, $closingBalance); // 1000 grand total - 300 paid = 700 balance

        // Verify only 1 recovery row is returned in the API response
        $recoveries = $apiResponse->json('recoveries');
        $this->assertCount(1, $recoveries);
        $this->assertEquals(300, (float) $recoveries[0]['amount']);
    }
}
