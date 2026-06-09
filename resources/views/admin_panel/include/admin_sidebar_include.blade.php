<style>
    /* Active Link Styles */
    #sidebar-menu ul li.active > a {
        background-color: rgba(40, 167, 69, 0.15) !important; /* Transparent dark green background */
        color: #ffffff !important; /* White text */
        font-weight: 600;
        border-radius: 6px;
    }
    #sidebar-menu ul li.active > a i {
        color: #28a745 !important; /* Green icon */
    }
    
    /* Submenu Active Styles */
    #sidebar-menu ul li.submenu ul li.active > a {
        background-color: transparent !important;
        color: #28a745 !important;
    }
</style>

<div class="sidebar" id="sidebar">
    <div class="sidebar-inner slimscroll">
        <div id="sidebar-menu" class="sidebar-menu">
            @if(Auth::check() && Auth::user()->usertype == 'admin')
                <ul>
                    <!-- Dashboard -->
                    <li class="active">
                        <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span> </a>
                    </li>

                    <!-- Business Report -->
                    <li>
                        <a href="{{ route('business-report.index') }}"><i class="fas fa-chart-pie"></i><span> Business Report</span></a>
                    </li>
                    <li>
                        <a href="{{ route('journal-voucher.index') }}"><i class="fas fa-receipt"></i><span> All Vouchers</span></a>
                    </li>

                    <!-- 📒 Journal Voucher System -->
                    <li class="submenu">
                        <!-- <a href="javascript:void(0);"><i class="fas fa-book"></i><span> Accounting</span> <span class="menu-arrow"></span></a> -->
                        <ul>
                            <!-- <li><a href="{{ route('journal-voucher.index') }}"><i class="fas fa-receipt"></i> All Vouchers</a></li> -->
                            <!-- <li><a href="{{ route('journal-voucher.daybook') }}"><i class="fas fa-calendar-day"></i> Day Book</a></li> -->
                            <!-- <li><a href="{{ route('journal-voucher.daily-closing') }}"><i class="fas fa-calculator"></i> Daily Closing</a></li> -->
                            <!-- <li><a href="{{ route('journal-voucher.closing-history') }}"><i class="fas fa-history"></i> Closing History</a></li> -->
                        </ul>
                    </li>

                    <!-- Price List -->
                    <li>
                        <a href="{{ route('price-list.index') }}"><i class="fas fa-tags"></i><span>Price List</span></a>
                    </li>

                    <!-- Product Management -->
                    <li>
                        <a href="{{ route('product') }}"><i class="fas fa-box-open"></i> <span>Product </span> </a>
                    </li>

                    <!-- Vendors -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-friends"></i><span> Vendors</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('vendors') }}">All Vendors</a></li>
                            <li><a href="{{ route('vendors-ledger') }}">Vendors Ledger </a></li>
                            <!-- <li><a href="{{ route('amount-paid-vendors') }}">Vendors Given Payments </a></li> -->
                            <!-- <li><a href="{{ route('vendors-builty') }}">Vendors Builty </a></li> -->
                        </ul>
                    </li>

                    <!-- Purchase -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-basket"></i><span> Purchase</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('Purchase') }}">Add Purchase</a></li>
                            <li><a href="{{ route('all-Purchases') }}">All Purchase</a></li>
                            <li><a href="{{ route('all-purchase-return') }}"> Purchase Returns</a></li>
                        </ul>
                    </li>

                    <!-- Customers -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-users"></i><span> Customer</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('customer') }}">All Customers</a></li>
                            <li><a href="{{ route('customer-ledger') }}">Customer Ledger</a></li>
                            <li><a href="{{ route('customer-recovery') }}">Customer Recoveries</a></li>
                        </ul>
                    </li>

                    <!-- Sale -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span>Sale</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('local-sale') }}">Add Sale</a></li>
                            <li><a href="{{ route('all-local-sale') }}">All Sales</a></li>
                        </ul>
                    </li>

                    <!-- Sale Return -->
                    <!-- <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-undo"></i><span> Sale Return</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('add-sale-return') }}">Add Sale Return</a></li>
                            <li><a href="{{ route('all-sale-return') }}">All Sales Return</a></li>
                        </ul>
                    </li> -->

                    <!-- Stock Out -->
                    <li>
                        <a href="{{ route('stockout.index') }}"><i class="fas fa-level-down-alt"></i><span> Stock Out</span></a>
                    </li>

                    <!-- Job Assignment -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-clipboard-list"></i><span> Job Management</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('job-orders.index') }}">Assign Job</a></li>
                            <li><a href="{{ route('job-assignments') }}">Job Assignments</a></li>
                        </ul>
                    </li>

                    <!-- Contractor -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-wrench"></i><span> Contractor</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('contractor') }}">All Contractor</a></li>
                            <li><a href="{{ route('contractor-ledger') }}">Contractor Payments</a></li>
                            <li><a href="{{ route('contractor-recovery') }}">Contractor Given Payments</a></li>
                        </ul>
                    </li>

                    <!-- Staff -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Staff</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('salesmen') }}">Add Staff</a></li>
                            <li><a href="{{ route('staff-wise-report') }}"><i class="fas fa-money-check-alt"></i> Weekly Staff Payment</a></li>
                            <li><a href="{{ route('staff-recovery') }}">Staff Given Payments</a></li>
                        </ul>
                    </li>

                    <!-- Staff Attendance -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-check"></i><span> Staff Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('staff-attendance.index') }}">Attendance</a></li>
                            <li><a href="{{ route('staff-advance.index') }}">Advance / Loan</a></li>
                            <li><a href="{{ route('staff-salary.index') }}">Pay Salary</a></li>
                            <li><a href="{{ route('staff-ledger-view') }}">Staff Ledger</a></li>
                        </ul>
                    </li>

                    <!-- Cash Book / Ledger -->
                    <li>
                        <a href="{{ route('cash-book') }}"><i class="fas fa-book"></i><span> Cash Book</span></a>
                    </li>

                    <!-- Expenses -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-money-bill-wave"></i><span> Expense Categories</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('expense') }}">Manage Categories</a></li>
                            <li><a href="{{ route('add-expenses') }}">All Expenses</a></li>
                        </ul>
                    </li>

                    <!-- Reports -->
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-chart-line"></i><span> Reports</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('stock-Record') }}">Stock Report</a></li>
                            <!-- <li><a href="{{ route('job.profit.report') }}">General Report</a></li> -->
                            <li><a href="{{ route('vendor-Ledger-Record') }}">Vendor Ledger</a></li>
                            <li><a href="{{ route('Customer-Ledger-Record') }}">Customer Ledger</a></li>
                            <!-- <li><a href="{{ route('date-wise-recovery-report') }}">Recovery Report</a></li> -->
                            <li><a href="{{ route('date-wise-purcahse-report') }}">Purchase Report</a></li>
                            <li><a href="{{ route('vendor-wise-purcahse-report') }}">Vendor Purchase</a></li>
                            <li><a href="{{ route('Date-wise-Sales-Report') }}">Sales Report</a></li>
                            <li><a href="{{ route('Product-wise-Sales-Report') }}">Product Sales</a></li>
                            <li><a href="{{ route('contractor-wise-report') }}">Contractor Report</a></li>
                            <li><a href="{{ route('staff-wise-report') }}">Staff Report</a></li>
                            <!-- Payment Reports - Now handled via Journal Voucher -->
                            <!-- <li><a href="{{ route('vendors-payments') }}">Vendor Payments</a></li>
                            <li><a href="{{ route('customer-payments') }}">Customer Payments</a></li>
                            <li><a href="{{ route('staff-payments') }}">Staff Payments</a></li> -->
                        </ul>
                    </li>

                    <!-- Reporting -->
                    <!-- <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-chart-pie"></i><span>Reports</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('stock-Record') }}">Item Stock Report </a></li>
                            <li><a href="{{ route('job.profit.report') }}">General Report </a></li>
                            <li><a href="{{ route('vendor-Ledger-Record') }}">Vendor Ledger Record </a></li>
                            <li><a href="{{ route('Customer-Ledger-Record') }}">Customer Ledger Record </a></li>
                            <li><a href="{{ route('date-wise-recovery-report') }}">Date Wise Recovery Report </a></li>
                            <li><a href="{{ route('date-wise-purcahse-report') }}">Date wise Purchase Report </a></li>
                            <li><a href="{{ route('vendor-wise-purcahse-report') }}">Vendor wise Purchase Report </a></li>
                            <li><a href="{{ route('contractor-wise-report') }}">Contractor wise Report </a></li>
                            <li><a href="{{ route('staff-wise-report') }}">Staff wise Report </a></li>
                            <li><a href="{{ route('Date-wise-Sales-Report') }}">Date wise Sales Report </a></li>
                            <li><a href="{{ route('Product-wise-Sales-Report') }}">Product Wise Sales Report </a></li>
                        </ul>
                    </li> -->

                    <!-- QA Testing Section -->
                    <!-- <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-bug"></i><span> Quality Assurance</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('qa.dashboard') }}">QA Dashboard</a></li>
                        </ul>
                    </li> -->
                </ul>
            @endif

            @if(Auth::check() && Auth::user()->usertype == 'distributor')
                <ul>
                    <li class="active">
                        <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span> </a>
                    </li>

                    <li>
                        <a href="{{ route('city') }}"><i class="fas fa-city"></i><span> City</span> </a>
                    </li>
                    <li>
                        <a href="{{ route('Area') }}"><i class="fas fa-building"></i><span> Areas</span> </a>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-friends"></i><span> Vendors</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('vendors') }}">Vendors</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-users"></i><span> Ledger</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('Distributor-ledger') }}">Ledger </a></li>
                            <li><a href="{{ route('Distributor-recovery') }}"> Recoveries </a></li>
                        </ul>
                    </li>

                    <li>
                        <a href="{{ route('category') }}"><i class="fas fa-box"></i><span> Category</span> </a>
                    </li>

                    <li>
                        <a href="{{ route('sub-category') }}"><i class="fas fa-boxes"></i><span> Sub-Category</span> </a>
                    </li>

                    <li>
                        <a href="{{ route('size') }}"><i class="fas fa-wine-bottle"></i> <span> Size </span> </a>
                    </li>

                    <li>
                        <a href="{{ route('business_type') }}"><i class="fas fa-business-time"></i> <span> Business Type
                            </span> </a>
                    </li>

                    <li>
                        <a href="{{ route('product') }}"><i class="fas fa-box-open"></i> <span> Product </span> </a>
                    </li>

                    <li>
                        <a href="{{ route('all-sale') }}"><i class="fas fa-box-open"></i> <span> Purchase </span> </a>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-clipboard-list"></i><span> Assign Job</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                           <li><a href="{{ route('job-orders.index') }}">Add Job</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-wrench"></i><span> Contractor</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('contractor') }}">All Contractor</a></li>
                            <li><a href="{{ route('contractor-ledger') }}">Contractor Payments</a></li>
                            <li><a href="{{ route('contractor-recovery') }}">Contractor Given Payments</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span> Sale</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('local-sale') }}">Add Sale</a></li>
                            <li><a href="{{ route('all-local-sale') }}">Sales</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span> Sale Return</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('add-sale-return') }}">Add Sale Return</a></li>
                            <li><a href="{{ route('all-sale-return') }}">Sales Return</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Staff Management</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <!-- <li><a href="{{ route('designation') }}">Add Designation </a></li> -->
                            <li><a href="{{ route('salesmen') }}">Add Staff</a></li>

                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-money-bill-wave"></i><span> Expenses</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('expense') }}">Add Expense Categroy</a></li>
                            <li><a href="{{ route('add-expenses') }}">Add Expenss</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Customer Management</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('customer') }}">Add Cutomers </a></li>
                            <li><a href="{{ route('customer-ledger') }}">Cutomers Payments </a></li>
                            <li><a href="{{ route('customer-recovery') }}">Cutomers Recoveries </a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Payments Management</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('customer-payments') }}">Customer Payments </a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-chart-pie"></i><span>Reporting</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('stock-Record') }}">Item Stock Report </a></li>
                            <li><a href="{{ route('Customer-Ledger-Record') }}">Customer Ledger Record </a></li>
                            <li><a href="{{ route('date-wise-recovery-report') }}">Date Wise Recovery Report </a></li>
                        </ul>
                    </li>
                </ul>
            @endif
            @if(Auth::check() && Auth::user()->usertype == 'salesman')
                <ul>
                    <li class="active">
                        <a href="{{ route('home') }}"><i class="fas fa-home"></i><span> Dashboard</span> </a>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-clipboard-list"></i><span> Assign Job</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                           <li><a href="{{ route('job-orders.index') }}">Add Job</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-wrench"></i><span> Contractor</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('contractor') }}">All Contractor</a></li>
                            <li><a href="{{ route('contractor-ledger') }}">Contractor Payments</a></li>
                            <li><a href="{{ route('contractor-recovery') }}">Contractor Given Payments</a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span> Sale Invoice</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('local-sale') }}">Add Sale</a></li>
                            <li><a href="{{ route('all-local-sale') }}">Sales</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Customer Management</span>
                            <span class="menu-arrow "></span></a>
                        <ul>
                            <li><a href="{{ route('customer') }}">Add Cutomers </a></li>
                            <li><a href="{{ route('customer-ledger') }}">Cutomers Payments </a></li>
                            <li><a href="{{ route('customer-recovery') }}">Cutomers Recoveries </a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-address-book"></i><span>Payments Management</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('customer-payments') }}">Customer Payments </a></li>
                        </ul>
                    </li>

                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-chart-pie"></i><span>Reporting</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('Customer-Ledger-Record') }}">Customer Ledger Record </a></li>
                            <li><a href="{{ route('date-wise-recovery-report') }}">Date Wise Recovery Report </a></li>
                            <li><a href="{{ route('Area-wise-Customer-payments') }}">Area wise Customer Report </a></li>
                            <li><a href="{{ route('Product-wise-Sales-Report') }}">Product Wise Sales Report </a></li>
                        </ul>
                    </li>
                </ul>
            @endif
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    var currentUrl = window.location.href.split('#')[0].split('?')[0];

    // Remove static active classes
    var staticActives = document.querySelectorAll('#sidebar-menu li.active');
    staticActives.forEach(function(li) {
        li.classList.remove('active');
    });
    
    var staticActiveLinks = document.querySelectorAll('#sidebar-menu a.active');
    staticActiveLinks.forEach(function(a) {
        a.classList.remove('active');
    });

    // Find and set active link
    var links = document.querySelectorAll('#sidebar-menu a');
    var isMatched = false;

    links.forEach(function(link) {
        if (link.href === currentUrl && link.getAttribute('href') !== 'javascript:void(0);') {
            link.parentElement.classList.add('active');
            link.classList.add('active'); // Add to a tag too if needed
            
            // If it's a submenu child, highlight parent and open the submenu
            var parentUl = link.closest('ul');
            if (parentUl && parentUl.parentElement.tagName === 'LI' && parentUl.parentElement.classList.contains('submenu')) {
                parentUl.parentElement.classList.add('active');
                
                var parentA = parentUl.parentElement.querySelector('a');
                if (parentA) {
                    parentA.classList.add('active');
                    parentA.classList.add('subdrop');
                }
                
                parentUl.style.display = 'block'; // Make submenu visible
            }
            isMatched = true;
        }
    });

    // Fallback: if no match, highlight dashboard
    if (!isMatched) {
        var dashLink = document.querySelector('#sidebar-menu a[href="{{ route("home") }}"]');
        if (dashLink) {
            dashLink.parentElement.classList.add('active');
            dashLink.classList.add('active');
        }
    }
});
</script>
