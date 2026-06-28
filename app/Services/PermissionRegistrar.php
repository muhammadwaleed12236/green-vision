<?php

namespace App\Services;

use App\Models\Permission;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class PermissionRegistrar
{
    protected array $modules = [];

    public function analyzeAndGenerate(): array
    {
        $this->modules = [];
        $routes = Route::getRoutes();

        foreach ($routes as $route) {
            $name = $route->getName();
            if (!$name || $name === 'login' || $name === 'register' || str_starts_with($name, 'verification.')
                || str_starts_with($name, 'password.') || str_starts_with($name, 'profile.')
                || $name === 'logout' || str_starts_with($name, 'debugbar')) {
                continue;
            }

            $module = $this->resolveModule($name);
            $action = $this->resolveAction($name, $route);

            if (!isset($this->modules[$module])) {
                $this->modules[$module] = [];
            }
            if (!in_array($action, $this->modules[$module])) {
                $this->modules[$module][] = $action;
            }
        }

        $modules = $this->modules;
        uksort($modules, function ($a, $b) {
            $order = ['user', 'role', 'permission', 'dashboard', 'settings'];
            $ia = array_search(Str::lower($a), $order);
            $ib = array_search(Str::lower($b), $order);
            if ($ia !== false && $ib !== false) return $ia - $ib;
            if ($ia !== false) return -1;
            if ($ib !== false) return 1;
            return strcmp($a, $b);
        });

        return $modules;
    }

    public function syncPermissions(): array
    {
        $modules = $this->analyzeAndGenerate();

        $allSlugs = [];
        foreach ($modules as $module => $actions) {
            $parentSlug = Str::slug($module);
            $allSlugs[] = $parentSlug;
            Permission::updateOrCreate(
                ['slug' => $parentSlug],
                ['name' => $module, 'module' => $module, 'action' => 'All']
            );

            foreach ($actions as $action) {
                $slug = $parentSlug . '.' . Str::slug($action);
                $allSlugs[] = $slug;
                Permission::updateOrCreate(
                    ['slug' => $slug],
                    ['name' => $module . ' ' . $action, 'module' => $module, 'action' => $action]
                );
            }
        }

        Permission::whereNotIn('slug', $allSlugs)->delete();

        return $modules;
    }

    protected function cleanRouteName(string $name): string
    {
        $lower = Str::lower($name);

        if (str_starts_with($lower, 'ignition') || str_starts_with($lower, 'debugbar')) {
            return '';
        }

        $strip = [
            'store-', 'update-', 'delete-', 'destroy-', 'add-', 'all-', 'fetch-', 'get-', 
            'toggle-', 'create-', 'show-', 'edit-', 'view-', 'post-', 'print-', 'export-', 
            'import-', 'approve-', 'reject-', 'cancel-', 'duplicate-', 'copy-', 'restore-', 
            'recover-', 'recovery-', 'ledger-', 'history-', 'transaction-', 'status-', 
            'assign-', 'close-', 'reopen-', 'mark-', 'convert-', 'search-', 'filter-',
            '-store', '-update', '-delete', '-destroy', '-add', '-all', '-fetch', '-get', 
            '-toggle', '-create', '-show', '-edit', '-view', '-post', '-print', '-export', 
            '-import', '-approve', '-reject', '-cancel', '-duplicate', '-copy', '-restore', 
            '-recover', '-recovery', '-ledger', '-history', '-transaction', '-status', 
            '-assign', '-close', '-reopen', '-mark', '-convert', '-search', '-filter'
        ];

        $cleaned = $lower;
        foreach ($strip as $s) {
            if (str_starts_with($cleaned, $s)) {
                $cleaned = substr($cleaned, strlen($s));
            }
            if (str_ends_with($cleaned, $s)) {
                $cleaned = substr($cleaned, 0, -strlen($s));
            }
        }

        return $cleaned;
    }

    public function resolveModule(string $routeName): string
    {
        $lowerName = Str::lower($routeName);

        $explicit = [
            'get.subcategories' => 'Purchase',
            'get.items' => 'Purchase',
            'get-areas' => 'Distributor',
            'get-products' => 'Stock Out',
            'store-unit' => 'Product',
            'fetch-subcategories' => 'Product',
            'delivery-notifications' => 'Local Sale',
            'create-bill' => 'Create Bill',
            'receivable' => 'Recovery Report',
            'recovery-store' => 'Distributor',
            'get-recovery-report' => 'Recovery Report',
            'date-wise-recovery' => 'Recovery Report',
            'get-invoices-by-date' => 'Stock Out',
            'fetch-distributor-ledger' => 'Distributor Ledger Record',
            'fetch-vendor-ledger' => 'Vendor Ledger Record',
            'fetch-customer-ledger' => 'Customer Ledger Record',
            'fetch-purchase-report' => 'Purchase Report',
            'fetch-vendor-purchase-report' => 'Vendor Purchase Report',
            'fetch-sale-details' => 'Sale Return',
            'get-sale-invoices' => 'Sale Return',
            'get-vendor-balance' => 'Vendor',
            'contractor-wise' => 'Contractor Report',
            'staff-wise' => 'Staff Report',
            'Date-wise-Sales' => 'Sales Report',
            'Product-wise-Sales' => 'Product Sales Report',
            'Area-wise-Customer' => 'Area Customer Report',
            'Area-wise-salesman' => 'Area Salesman Report',
            'job.profit' => 'Job Profit Report',
            'transfers' => 'Distributor Balance Transfer',
            'sizing' => 'Size',
            'size' => 'Size',
            'reports' => 'Report',
        ];

        foreach ($explicit as $routePattern => $moduleName) {
            if (str_contains($lowerName, Str::lower($routePattern))) {
                return $moduleName;
            }
        }

        $cleaned = $this->cleanRouteName($routeName);

        if (!$cleaned) {
            return 'Other';
        }

        $mappings = [
            'home' => 'Dashboard',
            'dashboard' => 'Dashboard',
            'city' => 'City',
            'area' => 'Area',
            'sub-category' => 'Sub Category',
            'subcategory' => 'Sub Category',
            'subcategories' => 'Sub Category',
            'category' => 'Category',
            'business_type' => 'Business Type',
            'business-type' => 'Business Type',
            'product' => 'Product',
            'expense' => 'Expense',
            'distributor' => 'Distributor',
            'vendor' => 'Vendor',
            'customer' => 'Customer',
            'salesman' => 'Salesman',
            'salesmen' => 'Salesman',
            'designation' => 'Designation',
            'purchase' => 'Purchase',
            'local-sale' => 'Local Sale',
            'localsale' => 'Local Sale',
            'sale' => 'Sale',
            'stockout' => 'Stock Out',
            'stock-out' => 'Stock Out',
            'contractor' => 'Contractor',
            'job-order' => 'Job Order',
            'job-orders' => 'Job Order',
            'job-assignment' => 'Job Assignment',
            'job-assignments' => 'Job Assignment',
            'cash-book' => 'Cash Book',
            'cashbook' => 'Cash Book',
            'journal-voucher' => 'Journal Voucher',
            'journalvoucher' => 'Journal Voucher',
            'price-list' => 'Price List',
            'pricelist' => 'Price List',
            'attendance' => 'Staff Attendance',
            'advance' => 'Staff Advance',
            'salary' => 'Staff Salary',
            'ledger' => 'Staff Ledger',
            'notification' => 'Notification',
            'settings' => 'Settings',
            'profile' => 'Profile',
            'business-report' => 'Business Report',
            'qa' => 'QA Testing',
            'rbac.users' => 'User Management - Users',
            'rbac.roles' => 'User Management - Roles',
            'rbac.permissions' => 'User Management - Permissions',
        ];

        foreach ($mappings as $key => $module) {
            $lowerKey = Str::lower($key);
            if (str_contains($cleaned, $lowerKey)) {
                return $module;
            }
        }

        foreach ($mappings as $key => $module) {
            $lowerKey = Str::lower($key);
            if (str_contains($lowerName, $lowerKey)) {
                return $module;
            }
        }

        $parts = explode('.', $routeName);
        $firstPart = str_replace(['-', '_'], ' ', $parts[0]);
        return Str::title($firstPart);
    }

    public function resolveAction(string $routeName, $route): string
    {
        $lower = Str::lower($routeName);
        $methods = $route->methods();

        if (str_contains($lower, 'delete') || str_contains($lower, 'destroy')) return 'Delete';
        if (str_contains($lower, 'create') || str_contains($lower, 'store') || str_contains($lower, 'add')) {
            return 'Create';
        }
        if (str_contains($lower, 'edit') || str_contains($lower, 'update')) return 'Edit';
        if (str_contains($lower, 'print') || str_contains($lower, 'invoice') || str_contains($lower, 'receipt')) return 'Print';
        if (str_contains($lower, 'export') || str_contains($lower, 'pdf')) return 'Export';
        if (str_contains($lower, 'import')) return 'Import';
        if (str_contains($lower, 'approve')) return 'Approve';
        if (str_contains($lower, 'reject')) return 'Reject';
        if (str_contains($lower, 'cancel')) return 'Cancel';
        if (str_contains($lower, 'duplicate') || str_contains($lower, 'copy')) return 'Duplicate';
        if (str_contains($lower, 'restore')) return 'Restore';
        if (str_contains($lower, 'recover') || str_contains($lower, 'recovery')) return 'Recovery';
        if (str_contains($lower, 'ledger') || str_contains($lower, 'history') || str_contains($lower, 'transaction')) return 'View';
        if (str_contains($lower, 'toggle') || str_contains($lower, 'status') || str_contains($lower, 'assign')) return 'Update';
        if (str_contains($lower, 'show') || str_contains($lower, 'view') || str_contains($lower, 'detail')) return 'View';
        if (str_contains($lower, 'close') || str_contains($lower, 'reopen')) return 'Update';
        if (str_contains($lower, 'mark')) return 'Update';
        if (str_contains($lower, 'convert')) return 'Edit';
        if (str_contains($lower, 'fetch') || str_contains($lower, 'get-')) return 'View';
        if (str_contains($lower, 'search') || str_contains($lower, 'filter')) return 'View';

        if (in_array('POST', $methods) || in_array('PUT', $methods) || in_array('PATCH', $methods)) {
            if (str_contains($route->uri(), 'delete') || str_contains($route->uri(), 'destroy')) return 'Delete';
            return 'Create';
        }
        if (in_array('DELETE', $methods)) return 'Delete';

        return 'View';
    }
}
