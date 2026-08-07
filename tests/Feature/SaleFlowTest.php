<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Product;
use App\Models\LocalSale;
use App\Models\CustomerLedger;
use App\Models\JournalVoucher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SaleFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_store_saves_advance_and_updates_ledger_and_voucher()
    {
        // 1. Setup User, Customer, and Product
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

        $product = Product::create([
            'admin_or_user_id' => $user->id,
            'item_name' => 'Item X',
            'initial_stock' => 10,
            'item_code' => 'COD-001',
        ]);

        // 2. Mock Request Data
        // Total Sale is 200, Advance is 150, Remaining is 50
        $data = [
            'sale_date' => now()->format('Y-m-d'),
            'sale_type' => 'sale',
            'party_type' => 'customer',
            'customer_id' => $customer->id,
            'item_id' => [$product->id],
            'item_name' => ['Item X'],
            'rate' => [200],
            'unit' => ['pcs'],
            'qty' => [1],
            'amount' => [200],
            'net_amount' => 200,
            'advance_amount' => 150,
        ];

        // 3. Act - Store Local Sale
        $response = $this->actingAs($user)
            ->post(route('store-local-sale'), $data);

        // 4. Assert Sale is stored with correct amounts
        $sale = LocalSale::first();
        $this->assertNotNull($sale);
        $this->assertEquals(200, $sale->net_amount);
        $this->assertEquals(150, $sale->advance_amount);
        $this->assertEquals(50, $sale->remaining_amount);

        // Assert Product stock is correctly reduced by 1 (10 - 1 = 9)
        $this->assertEquals(9, $product->fresh()->initial_stock);

        // 5. Assert Customer Ledger is updated correctly with remaining amount (50)
        $ledger = CustomerLedger::where('customer_id', $customer->id)->latest()->first();
        $this->assertNotNull($ledger);
        $this->assertEquals(50, $ledger->closing_balance);

        // 6. Assert Journal Voucher payment records the correct credit_amount (150)
        $voucher = JournalVoucher::where('reference_type', 'local_sale')
            ->where('reference_id', $sale->id)
            ->first();
        $this->assertNotNull($voucher);
        $this->assertEquals(150, $voucher->credit_amount);

        // 7. Test fetch customer ledger record API
        $fetchResponse = $this->actingAs($user)
            ->get(route('fetch-Customer-ledger', [
                'Customer_id' => $customer->id,
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $fetchResponse->assertStatus(200);
        $fetchData = $fetchResponse->json();

        // The API output closing balance must be 50, and receipts must be empty
        // because the JV created for the sale (reference_type = local_sale) is filtered out to avoid double counting!
        $this->assertEquals(50, $fetchData['closing_balance']);
        $this->assertEmpty($fetchData['receipts']);

        // 8. Test Sale Deletion
        $deleteResponse = $this->actingAs($user)
            ->get(route('local.sale.delete', $sale->id));

        // After deletion, the ledger closing balance must be reversed to 0 (since remaining 50 was reversed)
        $ledgerFresh = $ledger->fresh();
        $this->assertEquals(0, $ledgerFresh->closing_balance);

        // Also Journal Voucher must be deleted
        $this->assertNull(JournalVoucher::where('reference_type', 'local_sale')->where('reference_id', $sale->id)->first());

        // Assert Product stock is correctly restored back to 10
        $this->assertEquals(10, $product->fresh()->initial_stock);
    }
}
