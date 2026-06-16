<?php

use App\Http\Controllers\Business_tpyeController;
use App\Http\Controllers\CategoryAndSubCategoryController;
use App\Http\Controllers\CityAndAreaController;
use App\Http\Controllers\ContractorController;
use App\Http\Controllers\CreateBillController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DistributorController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GeneralReportController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\JobOrderController;
use App\Http\Controllers\LocalSaleController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseReturnController;
use App\Http\Controllers\QATestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\SalesmanController;
use App\Http\Controllers\SizeController;
use App\Http\Controllers\StaffAttendenceController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\VendorController;
use App\Models\Product;
use App\Models\Size;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// ========================= HOME ROUTES =========================
Route::get('/home', [HomeController::class, 'index'])->middleware('auth')->name('home');

// ========================= CITY ROUTES =========================
Route::get('/city', [CityAndAreaController::class, 'city'])->name('city');
Route::post('/store-city', [CityAndAreaController::class, 'store_city'])->name('store-city');
Route::post('/city/update', [CityAndAreaController::class, 'update'])->name('city.update');

Route::get('/Area', [CityAndAreaController::class, 'Area'])->name('Area');
Route::post('/store-Area', [CityAndAreaController::class, 'store_Area'])->name('store-Area');
Route::post('/Area/update', [CityAndAreaController::class, 'update_area'])->name('Area.update');

// ========================= BILL ROUTES =========================
Route::get('/create-bill', [CreateBillController::class, 'create_bill'])->name('create-bill');

// ========================= DISTRIBUTOR ROUTES =========================
Route::get('/Distributor', [DistributorController::class, 'Distributor'])->name('Distributor');
Route::post('/store-Distributor', [DistributorController::class, 'store_Distributor'])->name('store-Distributor');
Route::put('/Distributor/update/{id}', [DistributorController::class, 'update_Distributor'])->name('Distributor.update');
Route::get('/distributor/ledger/{id}', [DistributorController::class, 'getDistributorLedger'])->name('distributor.ledger');
Route::get('/get-areas', [DistributorController::class, 'get_areas'])->name('get-areas');
Route::get('/Distributor-ledger', [DistributorController::class, 'Distributor_ledger'])->name('Distributor-ledger');
Route::post('/recovery-store', [DistributorController::class, 'recovery_store'])->name('recovery-store');
Route::get('/Distributor-recovery', [DistributorController::class, 'Distributor_recovery'])->name('Distributor-recovery');
Route::put('/Distributor-recovery-update/{id}', [DistributorController::class, 'updateDistributorRecovery'])->name('Distributor-recovery-update');
Route::get('/distributor/balance/ledger/{id}', [DistributorController::class, 'getDistributorLedgerbalance'])->name('distributor.balance.ledger');
Route::get('/Distributor-Balance-Transfer', [DistributorController::class, 'Distributor_Balance_Transfer'])->name('Distributor-Balance-Transfer');
Route::post('/distributor/transfer', [DistributorController::class, 'storeTransfer'])->name('distributor.transfer.store');
Route::delete('/transfers/{id}', [DistributorController::class, 'destroyTransfer'])->name('transfers.destroy');
Route::put('/transfers/{id}', [DistributorController::class, 'update'])->name('transfers.update');

// ========================= CATEGORY ROUTES =========================
Route::get('/category', [CategoryAndSubCategoryController::class, 'category'])->name('category');
Route::post('/store-category', [CategoryAndSubCategoryController::class, 'store_category'])->name('store-category');
Route::post('/category/update', [CategoryAndSubCategoryController::class, 'update_category'])->name('category.update');
Route::delete('/category/delete/{id}', [CategoryAndSubCategoryController::class, 'category_delete'])->name('category.delete');

Route::get('/sub-category', [CategoryAndSubCategoryController::class, 'sub_category'])->name('sub-category');
Route::post('/store-sub-category', [CategoryAndSubCategoryController::class, 'store_sub_category'])->name('store-sub-category');
Route::post('/sub-category/update', [CategoryAndSubCategoryController::class, 'update_sub_category'])->name('sub-category.update');
Route::delete('/sub-category/delete/{id}', [CategoryAndSubCategoryController::class, 'sub_category_delete'])->name('sub-category.delete');

// ========================= SIZE ROUTES =========================
Route::get('/size', [SizeController::class, 'size'])->name('size');
Route::post('/store-size', [SizeController::class, 'store_size'])->name('store-size');
Route::post('/size/update', [SizeController::class, 'update'])->name('size.update');
Route::delete('/size/delete/{id}', [SizeController::class, 'delete'])->name('size.delete');

// ========================= BUSINESS TYPE ROUTES =========================
Route::get('/business-type', [Business_tpyeController::class, 'index'])->name('business_type');
Route::post('/business-type/store', [Business_tpyeController::class, 'store'])->name('business_type.store');
Route::post('/business-type/update', [Business_tpyeController::class, 'update'])->name('business_type.update');
Route::delete('/business-type/delete/{id}', [Business_tpyeController::class, 'delete'])->name('business-type.delete');

// ========================= EXPENSE ROUTES =========================
Route::get('/expense', [ExpenseController::class, 'expense'])->name('expense');
Route::post('/store-expense-category', [ExpenseController::class, 'store_expense_category'])->name('store-expense-category');
Route::post('/expense/update', [ExpenseController::class, 'update'])->name('expense.update');
Route::delete('/delete-expense-category/{id}', [ExpenseController::class, 'delete_Add_ExpenseBtn'])->name('delete-expense-category');

Route::get('/expenses', [ExpenseController::class, 'expense'])->name('expenses.index');
Route::get('/add-expenses', [ExpenseController::class, 'addExpenseScreen'])->name('add-expenses');
Route::post('/store-expense', [ExpenseController::class, 'store_addexpense'])->name('store-expense');
Route::post('/update-expense', [ExpenseController::class, 'update_addexpense'])->name('update-expense');
Route::delete('/delete-expense/{id}', [ExpenseController::class, 'delete_add_expense'])->name('delete-expense');

// ========================= STOCKOUT ROUTES =========================
Route::get('/stockout', [StockOutController::class, 'stockout'])->name('stockout.index');
Route::post('/store-stockout', [StockOutController::class, 'store_stockout'])->name('store-stockout');
Route::post('/update-stockout', [StockOutController::class, 'update_stockout'])->name('update-stockout');
Route::delete('/delete-stockout', [StockOutController::class, 'delete_stockout'])->name('delete-stockout');
Route::delete('/delete-job-stockout', [StockOutController::class, 'delete_job_stockout'])->name('delete-job-stockout');
Route::get('/stockout-details/{jobId}', [StockOutController::class, 'stockout_details'])->name('stockout-details');
Route::get('/get-invoices-by-date', [StockOutController::class, 'getInvoicesByDate'])->name('get-invoices-by-date');
Route::get('/get-products', [StockOutController::class, 'getProducts'])->name('get-products');

// ========================= CASH BOOK / LEDGER ROUTES =========================
Route::get('/cash-book', [App\Http\Controllers\CashBookController::class, 'index'])->name('cash-book');
Route::get('/cash-book/history', [App\Http\Controllers\CashBookController::class, 'history'])->name('cash-book.history');
Route::post('/cash-book/store', [App\Http\Controllers\CashBookController::class, 'store'])->name('cash-book.store');
Route::post('/cash-book/update', [App\Http\Controllers\CashBookController::class, 'update'])->name('cash-book.update');
Route::delete('/cash-book/delete/{id}', [App\Http\Controllers\CashBookController::class, 'delete'])->name('cash-book.delete');

// ========================= PRODUCT ROUTES =========================
Route::get('/product', [ProductController::class, 'product'])->name('product');
Route::post('/store-product', [ProductController::class, 'store_product'])->name('store-product');
Route::post('/store-unit', [ProductController::class, 'store_unit'])->name('store-unit');
Route::put('/product/update/{id}', [ProductController::class, 'update'])->name('product.update');
Route::get('/fetch-subcategories', [ProductController::class, 'fetchSubCategories'])->name('fetch-subcategories');
Route::get('/products/{id}/edit', [ProductController::class, 'edit'])->name('products.edit');
Route::post('/product/update', [ProductController::class, 'update_product'])->name('product_update');
Route::delete('/product/delete/{id}', [ProductController::class, 'delete'])->name('product.delete');

// ========================= PURCHASE ROUTES =========================
Route::get('/Purchase', [PurchaseController::class, 'Purchase'])->name('Purchase');
Route::get('/get-subcategories/{categoryname}', [PurchaseController::class, 'getSubcategories'])->name('get.subcategories');
Route::get('/get-items', [PurchaseController::class, 'getItems'])->name('get.items');
Route::post('/store-Purchase', [PurchaseController::class, 'store_Purchase'])->name('store-Purchase');
Route::get('/all-Purchases', [PurchaseController::class, 'all_Purchases'])->name('all-Purchases');
Route::get('/purchase/invoice/{id}', [PurchaseController::class, 'purchaseInvoice'])->name('purchase.invoice');
Route::get('/purchase/edit/{id}', [PurchaseController::class, 'purchaseedit'])->name('purchase.edit');
Route::put('/purchase/update/{id}', [PurchaseController::class, 'update_purchase'])->name('update-Purchase');
Route::delete('/purchase/delete/{id}', [PurchaseController::class, 'delete_purchase'])->name('purchase.delete');

Route::get('/purchase-return/{id}', [PurchaseReturnController::class, 'showReturnForm'])->name('purchase.return.form');
Route::post('/purchase-return/store', [PurchaseReturnController::class, 'store'])->name('purchase.return.store');
Route::get('/all-purchase-return', [PurchaseReturnController::class, 'all_purchase_return'])->name('all-purchase-return');

// ========================= CONTRACTOR ROUTES =========================
Route::get('/contractor', [ContractorController::class, 'index'])->name('contractor');
Route::post('/contractor/store', [ContractorController::class, 'store'])->name('contractor.store');
Route::get('/contractor/edit/{id}', [ContractorController::class, 'getContractorData'])->name('contractor.edit');
Route::post('/contractor/update', [ContractorController::class, 'update'])->name('contractor.update');
Route::delete('/contractor/delete/{id}', [ContractorController::class, 'destroy'])->name('delete-contractor');

Route::get('/contractor-ledger', [ContractorController::class, 'contractor_ledger'])->name('contractor-ledger');
Route::post('/contractor-recovery-store', [ContractorController::class, 'contractor_recovery_store'])->name('contractor-recovery-store');
Route::get('/contractor-recovery', [ContractorController::class, 'contractor_recovery'])->name('contractor-recovery');
Route::put('/contractor-recovery/{id}', [ContractorController::class, 'updateRecovery'])->name('contractor_recovery.update');

// ========================= SALE ROUTES =========================
Route::get('/add-sale', [SaleController::class, 'add_sale'])->name('add-sale');
Route::post('/store-sale', [SaleController::class, 'store_sale'])->name('store-sale');
Route::get('/all-sale', [SaleController::class, 'all_sale'])->name('all-sale');
Route::get('/sale/{id}', [SaleController::class, 'show_sale'])->name('show_sale');
Route::get('/sale/invoice/{id}', [SaleController::class, 'saleInvoice'])->name('sale.invoice');
Route::get('/sale/edit/{id}', [SaleController::class, 'saleEdit'])->name('sale.edit');
Route::get('/sale/delete/{id}', [SaleController::class, 'delete'])->name('sale.delete');
Route::put('/sale/update/{id}', [SaleController::class, 'saleupdate'])->name('sale.update');
Route::post('/sale/assign/{id}', [SaleController::class, 'assignSalesman'])->name('sale.assign');
Route::get('/sale/cancel/{id}', [SaleController::class, 'cancelSale'])->name('sale.cancel');

// ========================= SALE RETURN ROUTES =========================
Route::get('/add-sale-return', [SaleReturnController::class, 'add_sale_return'])->name('add-sale-return');
Route::get('/get-sale-invoices', [SaleReturnController::class, 'getSaleInvoices'])->name('get-sale-invoices');
Route::get('/fetch-sale-details', [SaleReturnController::class, 'fetchSaleDetails'])->name('fetch-sale-details');
Route::post('/sale-return', [SaleReturnController::class, 'store'])->name('sale-return.store');
Route::get('/all-sale-return', [SaleReturnController::class, 'all_sale_return'])->name('all-sale-return');

// ========================= SALESMAN ROUTES =========================
Route::get('/salesmen', [SalesmanController::class, 'salesmen'])->name('salesmen');
Route::post('/store-salesman', [SalesmanController::class, 'store_salesman'])->name('store-salesman');
Route::post('/salesman/update', [SalesmanController::class, 'update_salesman'])->name('update-salesman');
Route::get('/fetch-cities', [SalesmanController::class, 'fetchCities'])->name('fetch-cities');
Route::delete('/salesman/delete/{id}', [SalesmanController::class, 'delete'])->name('delete-salesman');

Route::get('/staff-ledger', [SalesmanController::class, 'staff_ledger'])->name('staff-ledger');
Route::post('/staff-recovery-store', [SalesmanController::class, 'staff_recovery_store'])->name('staff-recovery-store');
Route::get('/staff-recovery', [SalesmanController::class, 'staff_recovery'])->name('staff-recovery');
Route::put('/staff-recovery/{id}', [SalesmanController::class, 'updateStaffRecovery'])->name('staff-recovery.update');
Route::post('/salesman/toggle-status', [SalesmanController::class, 'toggleStatus'])->name('toggle-salesman-status');

// ========================= DESIGNATION ROUTES =========================
Route::get('/designation', [SalesmanController::class, 'designation'])->name('designation');
Route::post('/store-designation', [SalesmanController::class, 'store_designation'])->name('designation.store');
Route::post('/designation/update', [SalesmanController::class, 'update_designation'])->name('designation.update');
Route::delete('/designation/delete/{id}', [SalesmanController::class, 'destroy'])->name('designation.delete');

// ========================= VENDOR ROUTES =========================
Route::get('/vendors', [VendorController::class, 'vendors'])->name('vendors');
Route::post('/store-vendors', [VendorController::class, 'store_vendors'])->name('store-vendors');
Route::put('/vendors/update/{id}', [VendorController::class, 'update_vendors'])->name('vendors.update');
Route::get('/vendors-ledger', [VendorController::class, 'vendors_ledger'])->name('vendors-ledger');
Route::post('/vendors-payment', [VendorController::class, 'vendors_payment'])->name('vendors-payment');
Route::get('/amount-paid-vendors', [VendorController::class, 'amount_paid_vendors'])->name('amount-paid-vendors');
Route::get('/vendor/ledger/{id}', [VendorController::class, 'getLedger'])->name('vendor.ledger');
Route::post('/update-vendor-payment', [VendorController::class, 'update_vendor_payment'])->name('update-vendor-payment');
Route::delete('/delete-vendor-payment/{id}', [VendorController::class, 'delete_vendor_payment'])->name('delete-vendor-payment');
Route::get('/vendor/transaction-history/{id}', [VendorController::class, 'transactionHistory'])->name('vendor.transaction.history');

Route::get('/vendors-builty', [VendorController::class, 'vendors_builty'])->name('vendors-builty');
Route::post('/store-vendors-builty', [VendorController::class, 'store_vendors_builty'])->name('store-vendors-builty');
Route::put('/vendor-builty/update/{id}', [VendorController::class, 'update'])->name('update-vendors-builty');
Route::delete('/vendor-builty/delete/{id}', [VendorController::class, 'delete'])->name('delete-vendors-builty');

// ========================= CUSTOMER ROUTES =========================
Route::get('/customer', [CustomerController::class, 'index'])->name('customer');
Route::post('/customer/store', [CustomerController::class, 'store'])->name('customer.store');
Route::post('/customer/update', [CustomerController::class, 'update'])->name('customers.update');
Route::delete('/customer/delete/{id}', [CustomerController::class, 'destroy'])->name('delete-customer');
Route::get('/fetch-business-types', [CustomerController::class, 'fetchBusinessTypes'])->name('fetch-business-types');
Route::get('/fetch-areas', [CustomerController::class, 'fetchAreas'])->name('fetch-areas');
Route::get('/fetch-designation', [CustomerController::class, 'fetchdesignation'])->name('fetch-designation');
Route::get('/fetch-areas-report', [CustomerController::class, 'fetch_areas_report'])->name('fetch-areas-report');
Route::get('/customer-ledger', [CustomerController::class, 'customer_ledger'])->name('customer-ledger');
Route::post('/customer-recovery-store', [CustomerController::class, 'customer_recovery_store'])->name('customer-recovery-store');
Route::get('/customer-recovery', [CustomerController::class, 'customer_recovery'])->name('customer-recovery');
Route::get('/customer-payment-history', [CustomerController::class, 'getCustomerPaymentHistory'])->name('customer-payment-history');
Route::get('/customer/transaction-history/{id}', [CustomerController::class, 'transactionHistory'])->name('customer.transaction.history');
Route::get('/customer/edit/{id}', [CustomerController::class, 'getCustomerData'])->name('customer.edit');
Route::put('/customer-recovery/{id}', [CustomerController::class, 'updateRecovery'])->name('customer_recovery.update');

// ========================= LOCAL SALE ROUTES =========================
Route::get('/local-sale', [LocalSaleController::class, 'local_sale'])->name('local-sale');
Route::post('/store-local-sale', [LocalSaleController::class, 'store_local_sale'])->name('store-local-sale');
Route::get('/all-local-sale', [LocalSaleController::class, 'all_local_sale'])->name('all-local-sale');
Route::get('/delivery-notifications', [LocalSaleController::class, 'deliveryNotifications'])->name('delivery-notifications');
Route::get('/show-local-sale/{id}', [LocalSaleController::class, 'show_local_sale'])->name('show-local-sale');
Route::get('/local/sale/invoice/{id}', [LocalSaleController::class, 'localsaleInvoice'])->name('local.sale.invoice');
Route::get('/local/sale/delete/{id}', [LocalSaleController::class, 'delete_localsale'])->name('local.sale.delete');
Route::get('/local/sale/edit/{id}', [LocalSaleController::class, 'localsaleEdit'])->name('local.sale.edit');
Route::put('/local/sale/update/{id}', [LocalSaleController::class, 'localsaleupdate'])->name('local.sale.update');

// ========================= NOTIFICATIONS ROUTES =========================
Route::prefix('notifications')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/unread-count', [NotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
    Route::get('/recent', [NotificationController::class, 'getRecent'])->name('notifications.recent');
    Route::post('/mark-read/{id}', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/delete/{id}', [NotificationController::class, 'delete'])->name('notifications.delete');
});

// ========================= JOB ORDER ROUTES =========================
Route::get('/job-orders', [JobOrderController::class, 'index'])->name('job-orders.index');
Route::get('/job-orders/get-sale-details/{id}', [JobOrderController::class, 'getSaleDetails'])->name('job-orders.sale-details');
Route::post('/job-orders/store', [JobOrderController::class, 'store'])->name('job-orders.store');
Route::post('/job-orders/update', [JobOrderController::class, 'update'])->name('job-orders.update');
Route::get('/job-orders/{id}', [JobOrderController::class, 'show'])->name('job-orders.show');
Route::delete('/job-orders/delete/{id}', [JobOrderController::class, 'delete'])->name('job-orders.delete');
Route::post('/job-orders/status-update', [JobOrderController::class, 'toggleStatus'])->name('job-orders.toggle-status');
Route::get('/job-orders/contractor-balance/{id}', [JobOrderController::class, 'getContractorBalance'])->name('job-orders.contractor-balance');

Route::get('/job-assignments', [JobOrderController::class, 'jobAssignments'])->name('job-assignments');
Route::post('/job-assignments/update-status/{id}', [JobOrderController::class, 'updateJobStatus'])->name('job-assignments.update-status');
Route::post('/sales/mark-completed/{id}', [JobOrderController::class, 'markSaleCompleted'])->name('sales.mark-completed');

// ========================= REPORTING ROUTES =========================
Route::get('/Distributor-Ledger-Record', [ReportController::class, 'Distributor_Ledger_Record'])->name('Distributor-Ledger-Record');
Route::get('/fetch-distributor-ledger', [ReportController::class, 'fetchDistributorLedger'])->name('fetch-distributor-ledger');

Route::get('/vendor-Ledger-Record', [ReportController::class, 'vendor_Ledger_Record'])->name('vendor-Ledger-Record');
Route::get('/fetch-vendor-ledger', [ReportController::class, 'fetchvendorLedger'])->name('fetch-vendor-ledger');

Route::get('/job-profit-report', [GeneralReportController::class, 'index'])->name('job.profit.report');
Route::get('/job-profit-report/fetch', [GeneralReportController::class, 'fetch'])->name('job.profit.report.fetch');

Route::get('/Customer-Ledger-Record', [ReportController::class, 'Customer_Ledger_Record'])->name('Customer-Ledger-Record');
Route::get('/fetch-Customer-ledger', [ReportController::class, 'fetchCustomerLedger'])->name('fetch-Customer-ledger');

Route::get('/stock-Record', [ReportController::class, 'stock_Record'])->name('stock-Record');
Route::get('/get-items-report/{subcategory}', [ReportController::class, 'getItems'])->name('get.items.report');
Route::get('/get-item-details', [ReportController::class, 'getItemDetails'])->name('get.item.details');

Route::get('/date-wise-recovery-report', [ReportController::class, 'date_wise_recovery_report'])->name('date-wise-recovery-report');
Route::post('/get-recovery-report', [ReportController::class, 'getRecoveryReport'])->name('get-recovery-report');

Route::get('/date-wise-purcahse-report', [ReportController::class, 'date_wise_purcahse_report'])->name('date-wise-purcahse-report');
Route::post('/fetch-purchase-report', [ReportController::class, 'fetch_purchase_report'])->name('fetch-purchase-report');

Route::get('/vendor-wise-purcahse-report', [ReportController::class, 'vendor_wise_purcahse_report'])->name('vendor-wise-purcahse-report');
Route::post('/fetch-vendor-purchase-report', [ReportController::class, 'fetchVendorPurchaseReport'])->name('fetch.vendor.purchase.report');

Route::get('/contractor-wise-report', [ReportController::class, 'contractor_wise_report'])->name('contractor-wise-report');
Route::post('/fetch-contractor-report', [ReportController::class, 'fetchContractorReport'])->name('fetch.contractor.report');

Route::get('/staff-wise-report', [ReportController::class, 'staff_wise_report'])->name('staff-wise-report');
Route::post('/staff-weekly-history', [ReportController::class, 'staffWeeklyHistory'])->name('staff.weekly.history');
Route::post('/staff-weekly-attendance', [ReportController::class, 'getStaffWeeklyAttendance'])->name('staff.weekly.attendance');
Route::post('staff/weekly/save', [ReportController::class, 'saveStaffWeekly'])->name('staff.weekly.save');
Route::get('/staff-all-summary', [ReportController::class, 'getAllStaffSummary'])->name('staff.all.summary');

Route::get('/Area-wise-Customer-payments', [ReportController::class, 'Area_wise_Customer_payments'])->name('Area-wise-Customer-payments');
Route::get('/receivable-report', [ReportController::class, 'fetchReceivableReport'])->name('fetch.receivable.report');

Route::get('/Area-wise-salesman-market-payments', [ReportController::class, 'Area_wise_salesman_market_payments'])->name('Area-wise-salesman-market-payments');
Route::get('/receivable-salesman-marketreport', [ReportController::class, 'receivablesalesmanmarketreport'])->name('receivable.salesman.marketreport');

Route::get('/Date-wise-Sales-Report', [ReportController::class, 'Date_wise_Sales_Report'])->name('Date-wise-Sales-Report');
Route::post('/get-sales-report', [ReportController::class, 'getsalesreport'])->name('get-sales-report');

Route::get('/Product-wise-Sales-Report', [ReportController::class, 'Product_wise_Sales_Report'])->name('Product-wise-Sales-Report');
Route::post('/get-Product-sales-report', [ReportController::class, 'getProductsalesreport'])->name('get-Product-sales-report');

// ========================= STAFF ATTENDANCE ROUTES =========================
Route::get('/staff-attendance', [StaffAttendenceController::class, 'index'])->name('staff-attendance.index');
Route::post('/staff-attendance/save', [StaffAttendenceController::class, 'store'])->name('staff-attendance.store');
Route::get('/staff-attendance/edit/{id}', [StaffAttendenceController::class, 'edit'])->name('staff-attendance.edit');
Route::post('/staff-attendance/update', [StaffAttendenceController::class, 'update'])->name('staff-attendance.update');
Route::delete('/staff-attendance/delete/{id}', [StaffAttendenceController::class, 'destroy'])->name('staff-attendance.delete');
Route::get('/staff-attendance/history/{staffId}', [StaffAttendenceController::class, 'history'])->name('staff-attendance.history');
Route::get('/staff-attendance/export-pdf', [StaffAttendenceController::class, 'exportPDF'])->name('staff-attendance.export-pdf');

// ========================= STAFF ADVANCE ROUTES =========================
Route::get('/staff-advance', [App\Http\Controllers\StaffAdvanceController::class, 'index'])->name('staff-advance.index');
Route::post('/staff-advance/store', [App\Http\Controllers\StaffAdvanceController::class, 'store'])->name('staff-advance.store');
Route::get('/staff-advance/balance/{staffId}', [App\Http\Controllers\StaffAdvanceController::class, 'getBalance'])->name('staff-advance.balance');
Route::post('/staff-advance/recover', [App\Http\Controllers\StaffAdvanceController::class, 'recover'])->name('staff-advance.recover');
Route::delete('/staff-advance/delete/{id}', [App\Http\Controllers\StaffAdvanceController::class, 'destroy'])->name('staff-advance.delete');

Route::get('/staff-ledger-view', [App\Http\Controllers\StaffAdvanceController::class, 'ledger'])->name('staff-ledger-view');

// ========================= STAFF SALARY ROUTES =========================
Route::get('/staff-salary', [App\Http\Controllers\StaffSalaryController::class, 'index'])->name('staff-salary.index');
Route::get('/staff-salary/info/{staffId}', [App\Http\Controllers\StaffSalaryController::class, 'getInfo'])->name('staff-salary.info');
Route::post('/staff-salary/store', [App\Http\Controllers\StaffSalaryController::class, 'store'])->name('staff-salary.store');
Route::get('/staff-salary/receipt/{id}', [App\Http\Controllers\StaffSalaryController::class, 'receipt'])->name('staff-salary.receipt');
Route::get('/staff-salary/{id}', [App\Http\Controllers\StaffSalaryController::class, 'show'])->name('staff-salary.show');
Route::put('/staff-salary/{id}', [App\Http\Controllers\StaffSalaryController::class, 'update'])->name('staff-salary.update');
Route::delete('/staff-salary/{id}', [App\Http\Controllers\StaffSalaryController::class, 'destroy'])->name('staff-salary.destroy');

// ========================= PAYMENT ROUTES =========================
Route::get('/vendors-payments', [PaymentController::class, 'vendors_payments'])->name('vendors-payments');
Route::post('/vendor-payment-store', [PaymentController::class, 'storeVendorPayment'])->name('vendor-payment-store');
Route::get('/get-vendor-balance/{id}', [PaymentController::class, 'getVendorBalance'])->name('get-Vendor-balance');
Route::get('/vendor-payment/receipt', [PaymentController::class, 'showVendorPaymentReceipt'])->name('Vendor.payment.receipt');

Route::get('/customer-payments', [PaymentController::class, 'customer_payments'])->name('customer-payments');
Route::get('/get-customer-balance/{id}', [PaymentController::class, 'getCustomerBalance'])->name('get.customer.balance');
Route::post('/customer-payment/store', [PaymentController::class, 'storeCustomerPayment'])->name('customer.payment.store');
Route::get('customer/payment/receipt/{customer_id}/{amount}', [PaymentController::class, 'showCustomerPaymentReceipt'])->name('Customer.payment.receipt');

Route::get('/staff-payments', [PaymentController::class, 'staff_payments'])->name('staff-payments');
Route::get('/get-staff-balance/{id}', [PaymentController::class, 'getStaffBalance'])->name('get.staff.balance');
Route::post('/staff-payment/store', [PaymentController::class, 'storeStaffPayment'])->name('staff.payment.store');
Route::get('staff/payment/receipt/{staff_id}/{amount}', [PaymentController::class, 'showStaffPaymentReceipt'])->name('Staff.payment.receipt');

Route::get('/Distributor-payments', [PaymentController::class, 'Distributor_payments'])->name('Distributor-payments');
Route::get('/get-Distributor-balance/{id}', [PaymentController::class, 'getDistributorBalance'])->name('get.Distributor.balance');
Route::post('/Distributor-payment/store', [PaymentController::class, 'storeDistributorPayment'])->name('Distributor.payment.store');
Route::get('/distributor/payment/receipt/{distributor_id}/{amount}', [PaymentController::class, 'showPaymentReceipt'])->name('Distributor.payment.receipt');

// ========================= PRICE LIST ROUTES =========================
Route::middleware('auth')->group(function () {
    Route::get('/price-list', [App\Http\Controllers\PriceListController::class, 'index'])->name('price-list.index');
    Route::get('/price-list/get-all', [App\Http\Controllers\PriceListController::class, 'getAll'])->name('price-list.get-all');
    Route::get('/price-list/headers', [App\Http\Controllers\PriceListController::class, 'getHeaders'])->name('price-list.headers');
    Route::get('/price-list/quick-view', [App\Http\Controllers\PriceListController::class, 'quickView'])->name('price-list.quick-view');
    Route::post('/price-list', [App\Http\Controllers\PriceListController::class, 'store'])->name('price-list.store');
    Route::get('/price-list/{id}', [App\Http\Controllers\PriceListController::class, 'show'])->name('price-list.show');
    Route::put('/price-list/{id}', [App\Http\Controllers\PriceListController::class, 'update'])->name('price-list.update');
    Route::delete('/price-list/{id}', [App\Http\Controllers\PriceListController::class, 'destroy'])->name('price-list.destroy');
});

// ========================= JOURNAL VOUCHER ROUTES =========================
Route::get('/journal-voucher', [App\Http\Controllers\JournalVoucherController::class, 'index'])->name('journal-voucher.index');
Route::get('/journal-voucher/parties/{type}', [App\Http\Controllers\JournalVoucherController::class, 'getParties'])->name('journal-voucher.parties');
Route::post('/journal-voucher/payment', [App\Http\Controllers\JournalVoucherController::class, 'storePayment'])->name('journal-voucher.payment');
Route::post('/journal-voucher/receipt', [App\Http\Controllers\JournalVoucherController::class, 'storeReceipt'])->name('journal-voucher.receipt');
Route::get('/journal-voucher/print/{id}', [App\Http\Controllers\JournalVoucherController::class, 'print'])->name('journal-voucher.print');
Route::get('/journal-voucher/day-book', [App\Http\Controllers\JournalVoucherController::class, 'dayBook'])->name('journal-voucher.daybook');
Route::get('/journal-voucher/daily-closing', [App\Http\Controllers\JournalVoucherController::class, 'dailyClosing'])->name('journal-voucher.daily-closing');
Route::post('/journal-voucher/close-day', [App\Http\Controllers\JournalVoucherController::class, 'closeDayFinally'])->name('journal-voucher.close-day');
Route::get('/journal-voucher/closing-history', [App\Http\Controllers\JournalVoucherController::class, 'closingHistory'])->name('journal-voucher.closing-history');
Route::post('/journal-voucher/reopen-day', [App\Http\Controllers\JournalVoucherController::class, 'reopenDay'])->name('journal-voucher.reopen-day');
Route::get('/journal-voucher/{id}', [App\Http\Controllers\JournalVoucherController::class, 'show'])->name('journal-voucher.show');
Route::put('/journal-voucher/{id}', [App\Http\Controllers\JournalVoucherController::class, 'update'])->name('journal-voucher.update');
Route::delete('/journal-voucher/{id}', [App\Http\Controllers\JournalVoucherController::class, 'destroy'])->name('journal-voucher.destroy');

// ========================= BUSINESS REPORT ROUTES =========================
Route::get('/business-report', [App\Http\Controllers\BusinessReportController::class, 'index'])->name('business-report.index');

// ========================= QA TESTING ROUTES =========================
Route::get('/qa-dashboard', [QATestController::class, 'dashboard'])->name('qa.dashboard');
Route::get('/qa/test-purchase/{id}', [QATestController::class, 'testPurchaseFlow'])->name('qa.test.purchase');
Route::get('/qa/health-check', [QATestController::class, 'quickHealthCheck'])->name('qa.health.check');

// ========================= PROFILE ROUTES =========================
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ========================= WELCOME ROUTE =========================
Route::get('/', function () {
    return view('welcome');
});

// ========================= AUTH ROUTES =========================
require __DIR__.'/auth.php';