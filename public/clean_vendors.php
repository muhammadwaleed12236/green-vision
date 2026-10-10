<?php

use Illuminate\Support\Facades\DB;
use App\Models\VendorLedger;

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "<h1>Clean Orphaned Vendor Ledgers</h1>";

try {
    $orphanedQuery = DB::table('vendor_ledgers')
        ->whereNotIn('vendor_id', function($query) {
            $query->select('id')->from('vendors')->whereNull('deleted_at');
        });

    if (isset($_POST['confirm_delete'])) {
        $deleted = $orphanedQuery->delete();
        echo "<h3 style='color: green;'>✅ Successfully deleted {$deleted} orphaned vendor ledger records!</h3>";
        echo "<p>Please delete this script file (clean_vendors.php) now for security purposes.</p>";
        echo "<a href='{$_SERVER['PHP_SELF']}'>Go Back</a>";
    } else {
        $orphanedRecords = $orphanedQuery->get();

        if ($orphanedRecords->isEmpty()) {
            echo "<h3 style='color: green;'>No orphaned ledgers found. Your database is clean!</h3>";
        } else {
            echo "<h3 style='color: orange;'>⚠️ Warning: Found " . $orphanedRecords->count() . " orphaned ledger records.</h3>";
            echo "<p>These ledgers belong to vendors that have been deleted. Preview below:</p>";

            echo "<table border='1' cellpadding='10' cellspacing='0' style='width:100%; text-align:left; border-collapse: collapse;'>";
            echo "<tr style='background-color:#f2f2f2;'>
                    <th>Ledger ID</th>
                    <th>Missing Vendor ID</th>
                    <th>Opening Balance</th>
                    <th>Previous Balance</th>
                    <th>Closing Balance</th>
                    <th>Created At</th>
                  </tr>";

            foreach ($orphanedRecords as $record) {
                echo "<tr>
                        <td>{$record->id}</td>
                        <td><strong style='color:red;'>{$record->vendor_id}</strong></td>
                        <td>{$record->opening_balance}</td>
                        <td>{$record->previous_balance}</td>
                        <td>{$record->closing_balance}</td>
                        <td>{$record->created_at}</td>
                      </tr>";
            }
            echo "</table><br>";

            echo "<form method='POST'>
                    <button type='submit' name='confirm_delete' value='1' style='background-color:red; color:white; padding:10px 20px; font-size:16px; border:none; cursor:pointer; font-weight:bold;'>
                        🗑️ Confirm & Delete These Records
                    </button>
                  </form>";
        }
    }
} catch (\Exception $e) {
    echo "<h3 style='color: red;'>Error: " . $e->getMessage() . "</h3>";
}
