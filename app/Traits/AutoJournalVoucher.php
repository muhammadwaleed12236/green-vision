<?php

namespace App\Traits;

use App\Models\JournalVoucher;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

trait AutoJournalVoucher
{
    /**
     * Automatically create journal voucher entry for any payment
     */
    public function createJournalVoucherEntry($data)
    {
        // Generate unique voucher number
        $voucherNo = $this->generateVoucherNumber($data['voucher_type']);

        return JournalVoucher::create([
            'admin_or_user_id' => Auth::id(),
            'voucher_no' => $voucherNo,
            'voucher_type' => $data['voucher_type'], // 'payment' or 'receipt'
            'voucher_date' => $data['date'],
            'party_type' => $data['party_type'], // 'customer', 'vendor', 'staff', 'expense'
            'party_id' => $data['party_id'] ?? null,
            'party_name' => $data['party_name'],
            'account_head' => $data['account_head'] ?? 'Cash Account',
            'debit_amount' => $data['voucher_type'] === 'payment' ? $data['amount'] : 0,
            'credit_amount' => $data['voucher_type'] === 'receipt' ? $data['amount'] : 0,
            'narration' => $data['description'] ?? '',
            'reference_type' => $data['reference_type'] ?? null, // 'customer_payment', 'vendor_payment', etc.
            'reference_id' => $data['reference_id'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Generate unique voucher number
     */
    private function generateVoucherNumber($type)
    {
        $prefix = $type === 'payment' ? 'PAY' : 'REC';
        $dateCode = date('ymd');

        // Get last voucher of this type created today
        $lastVoucher = JournalVoucher::where('admin_or_user_id', Auth::id())
            ->where('voucher_type', $type)
            ->whereDate('created_at', today())
            ->orderBy('id', 'desc')
            ->first();

        if ($lastVoucher) {
            // Expected format: PREFIX + ymd + sequence, e.g. PAY260808001
            if (preg_match('/^' . preg_quote($prefix) . preg_quote($dateCode) . '(\d+)$/', $lastVoucher->voucher_no, $matches)
                && substr_count($lastVoucher->voucher_no, $dateCode) === 1) {
                $lastNumber = (int) $matches[1];
            } else {
                // Legacy/corrupted voucher number — fall back to counting today's vouchers
                $lastNumber = JournalVoucher::where('admin_or_user_id', Auth::id())
                    ->where('voucher_type', $type)
                    ->whereDate('created_at', today())
                    ->count();
            }
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }

        $voucherNo = $prefix . $dateCode . $newNumber;

        // Safety net: never reuse an existing voucher number
        $attempts = 0;
        while (JournalVoucher::withTrashed()->where('voucher_no', $voucherNo)->exists() && $attempts < 1000) {
            $newNumber = str_pad((int) $newNumber + 1, 3, '0', STR_PAD_LEFT);
            $voucherNo = $prefix . $dateCode . $newNumber;
            $attempts++;
        }

        return $voucherNo;
    }

    /**
     * Create Customer Payment Journal Entry
     */
    public function createCustomerPaymentJournal($customerId, $customerName, $amount, $date, $description = '', $referenceId = null)
    {
        return $this->createJournalVoucherEntry([
            'voucher_type' => 'receipt',
            'date' => $date,
            'party_type' => 'customer',
            'party_id' => $customerId,
            'party_name' => $customerName,
            'amount' => $amount,
            'description' => $description ?: "Customer payment received from {$customerName}",
            'reference_type' => 'customer_payment',
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create Vendor Payment Journal Entry
     */
    public function createVendorPaymentJournal($vendorId, $vendorName, $amount, $date, $description = '', $referenceId = null)
    {
        return $this->createJournalVoucherEntry([
            'voucher_type' => 'payment',
            'date' => $date,
            'party_type' => 'vendor',
            'party_id' => $vendorId,
            'party_name' => $vendorName,
            'amount' => $amount,
            'description' => $description ?: "Payment made to vendor {$vendorName}",
            'reference_type' => 'vendor_payment',
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create Staff Payment Journal Entry
     */
    public function createStaffPaymentJournal($staffId, $staffName, $amount, $date, $paymentType, $description = '', $referenceId = null)
    {
        return $this->createJournalVoucherEntry([
            'voucher_type' => 'payment',
            'date' => $date,
            'party_type' => 'staff',
            'party_id' => $staffId,
            'party_name' => $staffName,
            'amount' => $amount,
            'description' => $description ?: "{$paymentType} payment to {$staffName}",
            'reference_type' => $paymentType, // 'salary_payment', 'advance_payment'
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create Expense Payment Journal Entry
     */
    public function createExpensePaymentJournal($expenseId, $expenseName, $amount, $date, $description = '', $referenceId = null)
    {
        return $this->createJournalVoucherEntry([
            'voucher_type' => 'payment',
            'date' => $date,
            'party_type' => 'expense',
            'party_id' => $expenseId,
            'party_name' => $expenseName,
            'amount' => $amount,
            'description' => $description ?: "Expense payment for {$expenseName}",
            'reference_type' => 'expense_payment',
            'reference_id' => $referenceId,
        ]);
    }

    /**
     * Create Contractor Payment Journal Entry
     */
    public function createContractorPaymentJournal($contractorId, $contractorName, $amount, $date, $description = '', $referenceId = null)
    {
        return $this->createJournalVoucherEntry([
            'voucher_type' => 'payment',
            'date' => $date,
            'party_type' => 'contractor',
            'party_id' => $contractorId,
            'party_name' => $contractorName,
            'amount' => $amount,
            'description' => $description ?: "Payment made to contractor {$contractorName}",
            'reference_type' => 'contractor_payment',
            'reference_id' => $referenceId,
        ]);
    }
}
