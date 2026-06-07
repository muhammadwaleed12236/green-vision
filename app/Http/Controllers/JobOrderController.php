<?php

namespace App\Http\Controllers;

use App\Models\Contractor;
use App\Models\ContractorLedger;
use App\Models\JobOrder;
use App\Models\LocalSale;
use App\Models\Salesman;
use App\Models\Vendor;
use App\Traits\AutoJournalVoucher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JobOrderController extends Controller
{
    use AutoJournalVoucher;
    public function index(Request $request)
    {
        $userId = Auth::id();
        $searchQuery = $request->input('q');

        $jobOrders = JobOrder::with(['contractor', 'vendor', 'sale', 'sale.customer', 'sale.vendor'])
            ->where('admin_or_user_id', $userId);
        
        // Apply search filter if provided
        if ($searchQuery) {
            $jobOrders->where(function ($q) use ($searchQuery) {
                $q->where('job_order_number', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('sale', function ($saleQuery) use ($searchQuery) {
                      $saleQuery->where('invoice_number', 'like', '%' . $searchQuery . '%')
                                ->orWhere('customer_shopname', 'like', '%' . $searchQuery . '%')
                                ->orWhereHas('customer', function ($custQuery) use ($searchQuery) {
                                    $custQuery->where('customer_name', 'like', '%' . $searchQuery . '%')
                                              ->orWhere('shop_name', 'like', '%' . $searchQuery . '%');
                                })
                                ->orWhereHas('vendor', function ($vendQuery) use ($searchQuery) {
                                    $vendQuery->where('Party_name', 'like', '%' . $searchQuery . '%');
                                });
                  });
            });
        }

        $jobOrders = $jobOrders->latest()->get();

        // Build grouped-by-assignee data for the grouped cards view
        $groupedByAssignee = [];
        foreach ($jobOrders as $job) {
            // Determine key and label
            if ($job->assignee_type === 'contractor' && $job->contractor) {
                $key   = 'contractor_' . $job->staff_id;
                $name  = $job->contractor->contractor_name;
                $type  = 'Contractor';
                $color = 'warning';
                $icon  = 'fa-briefcase';
            } elseif ($job->assignee_type === 'vendor' && $job->vendor) {
                $key   = 'vendor_' . $job->vendor_id;
                $name  = $job->vendor->Party_name;
                $type  = 'Vendor';
                $color = 'info';
                $icon  = 'fa-truck';
            } else {
                $key   = 'inhouse';
                $name  = 'In-House Staff';
                $type  = 'In-House';
                $color = 'success';
                $icon  = 'fa-users';
            }

            if (!isset($groupedByAssignee[$key])) {
                $groupedByAssignee[$key] = [
                    'name'      => $name,
                    'type'      => $type,
                    'color'     => $color,
                    'icon'      => $icon,
                    'jobs'      => [],
                    'total_amount'     => 0,
                    'paid_amount'      => 0,
                    'remaining_amount' => 0,
                    'pending_count'    => 0,
                    'completed_count'  => 0,
                ];
            }

            $groupedByAssignee[$key]['jobs'][]          = $job;
            $groupedByAssignee[$key]['total_amount']    += $job->total_amount;
            $groupedByAssignee[$key]['paid_amount']     += $job->paid_amount;
            $groupedByAssignee[$key]['remaining_amount']+= $job->remaining_amount;
            if ($job->status === 'completed') {
                $groupedByAssignee[$key]['completed_count']++;
            } else {
                $groupedByAssignee[$key]['pending_count']++;
            }
        }

        // Build grouped-by-sale (Job-wise) data
        // Each sale gets a card listing all its assignments
        $groupedBySale = [];
        foreach ($jobOrders as $job) {
            $saleKey = $job->sale_id ?? 'no_sale';

            // Label for the sale
            $invoiceNo  = $job->sale->invoice_number ?? 'N/A';
            $partyLabel = '';
            if ($job->sale) {
                if ($job->sale->party_type === 'vendor' && $job->sale->vendor) {
                    $partyLabel = '[Vendor] ' . $job->sale->vendor->Party_name;
                } elseif ($job->sale->party_type === 'customer' && $job->sale->customer) {
                    $partyLabel = '[Customer] ' . ($job->sale->customer->customer_name ?? $job->sale->customer->shop_name);
                } else {
                    $partyLabel = '[Walk-in] ' . ($job->sale->customer_shopname ?? 'Walk-in');
                }
            }

            if (!isset($groupedBySale[$saleKey])) {
                $groupedBySale[$saleKey] = [
                    'sale_id'       => $saleKey,
                    'invoice_no'    => $invoiceNo,
                    'party_label'   => $partyLabel,
                    'assignments'   => [],
                    'total_amount'  => 0,
                    'paid_amount'   => 0,
                    'remaining_amount' => 0,
                    'pending_count' => 0,
                    'completed_count' => 0,
                ];
            }

            // Build assignee label for this job row
            $assigneeName = '';
            $assigneeType = '';
            if ($job->assignee_type === 'contractor' && $job->contractor) {
                $assigneeName = $job->contractor->contractor_name;
                $assigneeType = 'contractor';
            } elseif ($job->assignee_type === 'vendor' && $job->vendor) {
                $assigneeName = $job->vendor->Party_name;
                $assigneeType = 'vendor';
            } else {
                $assigneeName = 'In-House Staff';
                $assigneeType = 'inhouse';
            }

            $groupedBySale[$saleKey]['assignments'][] = [
                'job'           => $job,
                'assignee_name' => $assigneeName,
                'assignee_type' => $assigneeType,
            ];
            $groupedBySale[$saleKey]['total_amount']     += $job->total_amount;
            $groupedBySale[$saleKey]['paid_amount']      += $job->paid_amount;
            $groupedBySale[$saleKey]['remaining_amount'] += $job->remaining_amount;
            if ($job->status === 'completed') {
                $groupedBySale[$saleKey]['completed_count']++;
            } else {
                $groupedBySale[$saleKey]['pending_count']++;
            }
        }


        // Fetch all recent local sales
        $allLocalSales = LocalSale::with(['customer', 'vendor'])
            ->select('id', 'invoice_number', 'customer_id', 'vendor_id', 'party_type', 'customer_shopname', 'item', 'qty')
            ->latest()
            ->get();

        $localSales = $allLocalSales->filter(function ($sale) {
            return $this->hasRemainingItems($sale);
        });

        $contractors = Contractor::all();
        $vendors = Vendor::all();

        return view(
            'admin_panel.salesmen.add_joborder',
            compact('jobOrders', 'localSales', 'contractors', 'vendors', 'groupedByAssignee', 'groupedBySale')
        );
    }

    private function hasRemainingItems($sale)
    {
        // Decode Sale Items
        $items = json_decode($sale->item, true) ?? [];
        $qtys = json_decode($sale->qty, true) ?? [];

        if (empty($items)) return false;

        // Fetch All Job Orders for this Sale (Regardless of user who created the job)
        $relatedJobs = JobOrder::where('sale_id', $sale->id)->get();

        // Calculate Assigned Totals
        $assignedMap = [];  // Item Name -> Assigned Qty
        foreach ($relatedJobs as $job) {
            // Try new items_json first, fallback to old work_type
            $jobItems = json_decode($job->items_json, true);

            if ($jobItems && is_array($jobItems)) {
                // New format: direct items array
                foreach ($jobItems as $item) {
                    $name = $item['name'];
                    $assignedQty = $item['qty'];
                    if (!isset($assignedMap[$name])) $assignedMap[$name] = 0;
                    $assignedMap[$name] += $assignedQty;
                }
            } else {
                // Old format: work_type structure
                $workTypes = json_decode($job->work_type, true) ?? [];
                foreach ($workTypes as $wt) {
                    if(isset($wt['items']) && is_array($wt['items'])){
                        foreach ($wt['items'] as $item) {
                            $name = $item['name'];
                            $assignedQty = $item['qty'];
                            if (!isset($assignedMap[$name])) $assignedMap[$name] = 0;
                            $assignedMap[$name] += $assignedQty;
                        }
                    }
                }
            }
        }

        // Check if any item has remaining qty
        foreach ($items as $index => $name) {
             $totalQty = $qtys[$index] ?? 0;
             $alreadyAssigned = $assignedMap[$name] ?? 0;
             if ($totalQty > $alreadyAssigned) return true; // Found remaining item
        }

        return false; // All fully assigned
    }

    public function getSaleDetails($saleId)
    {
        $sale = LocalSale::with(['customer', 'vendor'])->where('id', $saleId)->first();

        if (! $sale) {
            return response()->json([
                'status' => false,
                'message' => 'Sale not found',
            ], 404);
        }

        // Get party information
        $partyType = $sale->party_type ?: 'walkin';
        $partyName = '';
        
        if ($partyType === 'vendor' && $sale->vendor) {
            $partyName = $sale->vendor->Party_name;
        } elseif ($partyType === 'customer' && $sale->customer) {
            $partyName = $sale->customer->customer_name ?? $sale->customer->shop_name;
        } else {
            $partyName = $sale->customer_shopname ?? 'Walk-in';
        }

        $items = json_decode($sale->item, true) ?? [];
        $qtys = json_decode($sale->qty, true) ?? [];
        $units = json_decode($sale->unit, true) ?? [];
        $amounts = json_decode($sale->amount, true) ?? [];

        // Fetch All Job Orders for this Sale to calculate remaining
        $relatedJobs = JobOrder::where('sale_id', $sale->id)->get();

        $assignedMap = [];
        foreach ($relatedJobs as $job) {
            // Try new items_json first, fallback to old work_type
            $jobItems = json_decode($job->items_json, true);

            if ($jobItems && is_array($jobItems)) {
                // New format
                foreach ($jobItems as $item) {
                    $name = $item['name'];
                    $aQty = $item['qty'];
                    if (!isset($assignedMap[$name])) $assignedMap[$name] = 0;
                    $assignedMap[$name] += $aQty;
                }
            } else {
                // Old format
                $workTypes = json_decode($job->work_type, true) ?? [];
                foreach ($workTypes as $wt) {
                     if(isset($wt['items']) && is_array($wt['items'])){
                        foreach ($wt['items'] as $item) {
                            $name = $item['name'];
                            $aQty = $item['qty'];
                            if (!isset($assignedMap[$name])) $assignedMap[$name] = 0;
                            $assignedMap[$name] += $aQty;
                        }
                     }
                }
            }
        }

        $formattedItems = [];

        foreach ($items as $index => $name) {
            $totalQty = $qtys[$index] ?? 0;
            $alreadyAssigned = $assignedMap[$name] ?? 0;
            $remaining = max(0, $totalQty - $alreadyAssigned);

            if ($remaining > 0) {
                 $formattedItems[] = [
                    'id' => $sale->id,
                    'item' => $name,
                    'total_qty' => $totalQty,
                    'qty' => $remaining, // remaining becomes the recommended qty
                    'unit' => $units[$index] ?? null,
                    'rate' => $amounts[$index] ?? 0,
                ];
            }
        }

        return response()->json([
            'status' => true,
            'party_type' => $partyType,
            'party_name' => $partyName,
            'items' => $formattedItems,
        ]);
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {

            // Expected Request:
            // sale_id, job_date
            // assignments: [
            //    { assign_type: 'contract'|'labour', contractor_id: 123, items: [{name, qty, rate}], total_amount: 500, paid_amount: 0 }
            // ]

            $assignments = $request->assignments;
            if (!$assignments || !is_array($assignments)) {
                 throw new \Exception("Invalid data");
            }

            $userId = Auth::id();

            // Server-Side Validation: Check Quantities
            // 1. Calculate assigned qty per item in this request
            $requestedQtys = [];
            foreach ($assignments as $a) {
                if(!isset($a['items'])) continue;
                foreach($a['items'] as $item) {
                     $name = $item['name'];
                     $qty = (float) $item['qty'];
                     if (!isset($requestedQtys[$name])) $requestedQtys[$name] = 0;
                     $requestedQtys[$name] += $qty;
                }
            }

            // 2. Fetch limit
            // 2. Fetch limit
            $sale = LocalSale::find($request->sale_id);
            if (!$sale) throw new \Exception("Sale not found");

            // Re-calculate remaining on server
            $relatedJobs = JobOrder::where('sale_id', $sale->id)->get();
            $assignedMap = [];
            foreach ($relatedJobs as $job) {
                $workTypes = json_decode($job->work_type, true) ?? [];
                foreach ($workTypes as $wt) {
                    if(isset($wt['items']) && is_array($wt['items'])){
                        foreach ($wt['items'] as $i) {
                            $n = $i['name'];
                            $q = (float) $i['qty'];
                            if (!isset($assignedMap[$n])) $assignedMap[$n] = 0;
                            $assignedMap[$n] += $q;
                        }
                    }
                }
            }

            // Compare
            foreach($request->assignments as $a) {
                 if(!isset($a['items'])) continue;
                 foreach($a['items'] as $item) {
                      // Skip manual items (assume manual items are not tracked by sale qty or have specific marker?)
                      // User requirement: "Manual item ... add custom rows".
                      // If name matches sale item, check allowed. If not, maybe allow infinite?
                      // Assuming manual items definitely have different names or we need a flag.
                      // For now, if name matches a sale item, we block over-assignment.

                      $name = $item['name'];
                      $requestedItemQty = $requestedQtys[$name] ?? 0;

                      // Find original qty
                      $originalQty = 0;
                      $foundInSale = false;
                      // Sale items stored in json or related table?
                      // LocalSale model usually has items in json or table.
                      // Based on getSaleDetails code:
                      $itemNames = json_decode($sale->item_name, true) ?? [];
                      $itemQtys = json_decode($sale->qty, true) ?? [];

                      foreach($itemNames as $k => $in) {
                          if ($in == $name) {
                              $originalQty = (float) ($itemQtys[$k] ?? 0);
                              $foundInSale = true;
                              break;
                          }
                      }

                      if ($foundInSale) {
                          $alreadyAssigned = $assignedMap[$name] ?? 0;
                          $remaining = max(0, $originalQty - $alreadyAssigned);

                          if ($requestedItemQty > $remaining) {
                              // Fail
                              return response()->json(['status'=>false, 'message' => "Quantity limit exceeded for item '$name'. Available: $remaining, Requested: $requestedItemQty."], 422);
                          }
                      }
                 }
            }

            // Validated. Proceed.

            foreach ($assignments as $assign) {

                $assignType = $assign['assign_type'];
                $contractorId = $assignType === 'contractor' ? ($assign['contractor_id'] ?? null) : null;
                $vendorId = $assignType === 'vendor' ? ($assign['vendor_id'] ?? null) : null;
                $items = $assign['items'] ?? [];
                $expectedReturnDate = $assign['expected_return_date'] ?? null;

                if (empty($items)) continue;

                $totalAmount = $assign['total_amount'] ?? 0;
                $paidAmount = $assign['paid_amount'] ?? 0;

                // Validate Amount for Contractor/Vendor
                if (($assignType === 'contractor' || $assignType === 'vendor') && $totalAmount <= 0) {
                     return response()->json(['status'=>false, 'message' => "Total Bill amount cannot be zero for Contractor/Vendor assignment."], 422);
                }

                // Generate Job No (Global Search to avoid duplicate entry error)
                $lastJob = JobOrder::withTrashed()->orderBy('id', 'desc')->first();
                $newNumber = 1;
                if ($lastJob && $lastJob->job_order_number) {
                    $newNumber = ((int) substr($lastJob->job_order_number, 4)) + 1;
                }
                $jobOrderNo = 'JOB-'.str_pad($newNumber, 4, '0', STR_PAD_LEFT);

                // Store items as JSON (simplified structure without work_type)
                $itemsJson = json_encode($items);

                $jobOrder = JobOrder::create([
                    'admin_or_user_id' => $userId,
                    'job_order_number' => $jobOrderNo,
                    'sale_id' => $request->sale_id,
                    'staff_id' => $contractorId,
                    'vendor_id' => $vendorId,
                    'staff_type' => $assignType, // Keep for backward compatibility
                    'assignee_type' => $assignType,
                    'items_json' => $itemsJson, // New field to store items
                    'order_date' => $request->job_date,
                    'expected_return_date' => $expectedReturnDate,
                    'notify_days_before' => 2, // Default
                    'total_amount' => $totalAmount,
                    'paid_amount' => $paidAmount,
                    'remaining_amount' => $totalAmount - $paidAmount,
                    'status' => 'pending',
                ]);

                // Ledger Update - Contractor
                if ($assignType === 'contractor' && $contractorId) {
                    $ledger = \App\Models\ContractorLedger::where('contractor_id', $contractorId)->first();
                    if ($ledger) {
                        $ledger->closing_balance += $totalAmount;
                        if ($paidAmount > 0) {
                            $ledger->closing_balance -= $paidAmount;

                            // 🔥 Create Journal Voucher Entry for Contractor Payment
                            $contractor = Contractor::find($contractorId);
                            $this->createContractorPaymentJournal(
                                $contractorId,
                                $contractor->name ?? "Contractor ID: {$contractorId}",
                                $paidAmount,
                                $request->job_date,
                                "Advance payment for job assignment {$jobOrderNo}",
                                $jobOrder->id
                            );
                        }
                        $ledger->save();
                    }

                    // 🔥 NEW: Create expense entry for contractor assignment
                    if ($totalAmount > 0) {
                        // Get or create "Job Assignment" expense category
                        $expenseCategory = \App\Models\Expense::firstOrCreate(
                            ['admin_or_user_id' => $userId, 'expense_name' => 'Job Assignment - Contractor'],
                            ['created_at' => now(), 'updated_at' => now()]
                        );

                        // Create expense entry
                        \App\Models\AddExpense::create([
                            'admin_or_user_id' => $userId,
                            'expense_id' => $expenseCategory->id,
                            'amount' => $totalAmount,
                            'expense_date' => $request->job_date,
                            'description' => "Job Assignment #{$jobOrderNo} to Contractor",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                // Ledger Update - Vendor (INCREASE balance when job assigned)
                if ($assignType === 'vendor' && $vendorId) {
                    $ledger = \App\Models\VendorLedger::firstOrCreate(
                        ['vendor_id' => $vendorId, 'admin_or_user_id' => $userId],
                        ['opening_balance' => 0, 'previous_balance' => 0, 'closing_balance' => 0]
                    );

                    // Job assign karne par vendor ka balance increase hoga (we owe them)
                    $ledger->closing_balance += $totalAmount;

                    // Agar advance payment di hai toh balance reduce
                    if ($paidAmount > 0) {
                        $ledger->closing_balance -= $paidAmount;

                        // 🔥 Create Journal Voucher Entry for Vendor Payment
                        $vendor = Vendor::find($vendorId);
                        $this->createVendorPaymentJournal(
                            $vendorId,
                            $vendor->Party_name ?? "Vendor ID: {$vendorId}",
                            $paidAmount,
                            $request->job_date,
                            "Advance payment for job assignment {$jobOrderNo}",
                            $jobOrder->id
                        );
                    }
                    $ledger->save();

                    // 🔥 NEW: Create expense entry for vendor assignment
                    if ($totalAmount > 0) {
                        // Get or create "Job Assignment" expense category
                        $expenseCategory = \App\Models\Expense::firstOrCreate(
                            ['admin_or_user_id' => $userId, 'expense_name' => 'Job Assignment - Vendor'],
                            ['created_at' => now(), 'updated_at' => now()]
                        );

                        // Create expense entry
                        \App\Models\AddExpense::create([
                            'admin_or_user_id' => $userId,
                            'expense_id' => $expenseCategory->id,
                            'amount' => $totalAmount,
                            'expense_date' => $request->job_date,
                            'description' => "Job Assignment #{$jobOrderNo} to Vendor",
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }
            }
        });

        return response()->json(['status' => true]);
    }

    public function show($id)
    {
        $job = JobOrder::where('id', $id)
            ->where('admin_or_user_id', Auth::id())
            ->firstOrFail();

        // Decode work_type JSON
        $workTypeData = json_decode($job->work_type, true) ?? [];
        $itemsCollection = collect();

        foreach($workTypeData as $wt) {
            $wtName = $wt['name'] ?? 'Main';
            $items = $wt['items'] ?? [];
            foreach($items as $it) {
                $itemsCollection->push((object)[
                    'work_type' => $wtName,
                    'item_name' => $it['name'] ?? 'N/A',
                    'qty' => $it['qty'] ?? 0,
                    'rate' => $it['rate'] ?? 0,
                    'total' => ($it['qty'] ?? 0) * ($it['rate'] ?? 0)
                ]);
            }
        }

        $jobItems = $itemsCollection->groupBy('work_type');

        return view(
            'admin_panel.salesmen.joborder_detail',
            compact('job', 'jobItems')
        );
    }

    public function update(Request $request)
    {
        $job = JobOrder::find($request->job_id);

        if (! $job) {
            return back()->with('error', 'Job Order not found');
        }

        $oldTotal = $job->total_amount;
        $oldPaid = $job->paid_amount;
        $contractorId = ($job->staff_type === 'contract') ? $job->staff_id : null;

        $job->update([
            'order_date' => $request->job_date,
            'total_amount' => $request->total_amount,
            'paid_amount' => $request->paid_amount,
            'remaining_amount' => $request->total_amount - $request->paid_amount,
            'status' => ($request->total_amount - $request->paid_amount) > 0 ? 'pending' : 'completed',
        ]);

        // ✅ Update Ledger
        if ($contractorId) {
            $ledger = \App\Models\ContractorLedger::where('contractor_id', $contractorId)->first();
            if ($ledger) {
                // Reverse Old
                $ledger->closing_balance -= $oldTotal;
                $ledger->closing_balance += $oldPaid;

                // Apply New
                $ledger->closing_balance += $request->total_amount;
                $ledger->closing_balance -= $request->paid_amount;
                $ledger->save();
            }

            // 🔥 NEW: Update expense entry for contractor assignment
            $description = "Job Assignment #{$job->job_order_number} to Contractor";
            $expenseEntry = \App\Models\AddExpense::where('admin_or_user_id', Auth::id())
                ->where('description', 'LIKE', "%{$job->job_order_number}%")
                ->where('description', 'LIKE', '%Contractor%')
                ->first();

            if ($expenseEntry) {
                $expenseEntry->update([
                    'amount' => $request->total_amount,
                    'expense_date' => $request->job_date,
                ]);
            }
        }

        // Handle vendor updates similarly
        if ($job->assignee_type === 'vendor' && $job->vendor_id) {
            $ledger = \App\Models\VendorLedger::where('vendor_id', $job->vendor_id)->first();
            if ($ledger) {
                // Reverse Old
                $ledger->closing_balance -= $oldTotal;
                $ledger->closing_balance += $oldPaid;

                // Apply New
                $ledger->closing_balance += $request->total_amount;
                $ledger->closing_balance -= $request->paid_amount;
                $ledger->save();
            }

            // 🔥 NEW: Update expense entry for vendor assignment
            $expenseEntry = \App\Models\AddExpense::where('admin_or_user_id', Auth::id())
                ->where('description', 'LIKE', "%{$job->job_order_number}%")
                ->where('description', 'LIKE', '%Vendor%')
                ->first();

            if ($expenseEntry) {
                $expenseEntry->update([
                    'amount' => $request->total_amount,
                    'expense_date' => $request->job_date,
                ]);
            }
        }

        return back()->with('success', 'Job Order updated successfully');
    }

    public function delete($id)
    {
        $job = JobOrder::find($id);

        if (! $job) {
            return response()->json(['status' => false]);
        }

        // ✅ Reverse Contractor Ledger if applicable
        if ($job->staff_type === 'contract' && $job->staff_id) {
            $ledger = \App\Models\ContractorLedger::where('contractor_id', $job->staff_id)->first();
            if ($ledger) {
                // Reverse Work DONE (Subtract from what we owe)
                $ledger->closing_balance -= $job->total_amount;
                // Reverse payment (Add back to what we owe)
                $ledger->closing_balance += $job->paid_amount;
                $ledger->save();
            }

            // 🔥 NEW: Delete related expense entry for contractor
            \App\Models\AddExpense::where('admin_or_user_id', Auth::id())
                ->where('description', 'LIKE', "%{$job->job_order_number}%")
                ->where('description', 'LIKE', '%Contractor%')
                ->delete();
        }

        // Handle vendor ledger reversal
        if ($job->assignee_type === 'vendor' && $job->vendor_id) {
            $ledger = \App\Models\VendorLedger::where('vendor_id', $job->vendor_id)->first();
            if ($ledger) {
                // Reverse Work DONE (Subtract from what we owe)
                $ledger->closing_balance -= $job->total_amount;
                // Reverse payment (Add back to what we owe)
                $ledger->closing_balance += $job->paid_amount;
                $ledger->save();
            }

            // 🔥 NEW: Delete related expense entry for vendor
            \App\Models\AddExpense::where('admin_or_user_id', Auth::id())
                ->where('description', 'LIKE', "%{$job->job_order_number}%")
                ->where('description', 'LIKE', '%Vendor%')
                ->delete();
        }

        DB::table('job_items')->where('job_order_id', $id)->delete();
        $job->delete();

        return response()->json(['status' => true]);
    }

    public function toggleStatus(Request $request)
    {
        $job = JobOrder::find($request->job_id);

        if (! $job) {
            return response()->json(['error' => 'Not found'], 404);
        }

        $job->status = $request->status;
        $job->save();

        return response()->json(['success' => true]);
    }

    public function getContractorBalance($id)
    {
        $ledger = \App\Models\ContractorLedger::where('contractor_id', $id)->first();
        $balance = $ledger ? $ledger->closing_balance : 0;
        return response()->json(['status' => true, 'balance' => $balance]);
    }

    // Job Assignments Dashboard - All assigned jobs in one place
    public function jobAssignments(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->back();
        }

        $userId = Auth::id();
        $searchQuery = $request->input('q');

        // Get all job orders with relationships
        $query = JobOrder::with(['sale.customer', 'sale.vendor', 'contractor', 'vendor']);
        
        // Apply search filter if provided
        if ($searchQuery) {
            $query->where(function ($q) use ($searchQuery) {
                $q->where('job_order_number', 'like', '%' . $searchQuery . '%')
                  ->orWhereHas('sale', function ($saleQuery) use ($searchQuery) {
                      $saleQuery->where('invoice_number', 'like', '%' . $searchQuery . '%')
                                ->orWhere('customer_shopname', 'like', '%' . $searchQuery . '%')
                                ->orWhereHas('customer', function ($custQuery) use ($searchQuery) {
                                    $custQuery->where('customer_name', 'like', '%' . $searchQuery . '%')
                                              ->orWhere('shop_name', 'like', '%' . $searchQuery . '%');
                                })
                                ->orWhereHas('vendor', function ($vendQuery) use ($searchQuery) {
                                    $vendQuery->where('Party_name', 'like', '%' . $searchQuery . '%');
                                });
                  });
            });
        }

        $allJobOrders = $query->orderBy('job_order_number', 'desc')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($job) {
                // Determine assigned to name based on type
                $assignedTo = '';
                $assignedType = '';

                if ($job->assignee_type === 'contractor' && $job->contractor) {
                    $assignedTo = $job->contractor->contractor_name;
                    $assignedType = 'Contractor';
                } elseif ($job->assignee_type === 'vendor' && $job->vendor) {
                    $assignedTo = $job->vendor->Party_name;
                    $assignedType = 'Vendor';
                } elseif ($job->assignee_type === 'inhouse') {
                    $staff = Salesman::find($job->staff_id);
                    $assignedTo = $staff ? $staff->salesman_name : 'In-House Staff';
                    $assignedType = 'In-House';
                }

                $job->assigned_to_name = $assignedTo;
                $job->assigned_type_label = $assignedType;

                return $job;
            });

        // Group jobs by job_order_number (same order can have multiple assignments)
        $groupedJobs = $allJobOrders->groupBy('job_order_number');

        return view('admin_panel.salesmen.job_assignments', compact('groupedJobs'));
    }

    // Update job assignment status
    public function updateJobStatus(Request $request, $id)
    {
        $job = JobOrder::findOrFail($id);

        $job->status = $request->status;

        if ($request->status === 'completed') {
            $job->completed_at = now();
        }

        $job->save();

        // Check if all jobs for this sale are completed
        $this->checkAndUpdateSaleStatus($job->sale_id);

        return response()->json([
            'success' => true,
            'message' => 'Job status updated successfully'
        ]);
    }

    // Check if all jobs for a sale are completed and update sale status
    private function checkAndUpdateSaleStatus($saleId)
    {
        $sale = LocalSale::find($saleId);

        if (!$sale) {
            return;
        }

        // Get all jobs for this sale
        $allJobs = JobOrder::where('sale_id', $saleId)->get();

        // Check if all jobs are completed
        $allCompleted = $allJobs->every(function ($job) {
            return $job->status === 'completed';
        });

        // If all jobs completed, update sale status to 'ready'
        if ($allCompleted && $allJobs->count() > 0) {
            $sale->job_status = 'ready';
            $sale->save();
        }
    }

    // Mark sale as completed (delivery done)
    public function markSaleCompleted(Request $request, $saleId)
    {
        $sale = LocalSale::findOrFail($saleId);

        // Only allow if status is 'ready'
        if ($sale->job_status !== 'ready') {
            return response()->json([
                'success' => false,
                'message' => 'Sale status must be Ready before marking as Completed'
            ], 400);
        }

        $sale->job_status = 'completed';
        $sale->completed_at = now();
        $sale->save();

        return response()->json([
            'success' => true,
            'message' => 'Order marked as completed successfully'
        ]);
    }
}
