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

                    <!-- User Management -->
                    @if(auth()->user()->hasPermission('user-management-users.view') || auth()->user()->hasPermission('user-management-roles.view') || auth()->user()->hasPermission('user-management-permissions.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-users-cog"></i><span> User Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @if(auth()->user()->hasPermission('user-management-users.view'))
                            <li><a href="{{ route('rbac.users.index') }}"><i class="fas fa-user"></i> Users</a></li>
                            @endif
                            @if(auth()->user()->hasPermission('user-management-roles.view'))
                            <li><a href="{{ route('rbac.roles.index') }}"><i class="fas fa-user-tag"></i> Roles</a></li>
                            @endif
                            @if(auth()->user()->hasPermission('user-management-permissions.view'))
                            <li><a href="{{ route('rbac.permissions.index') }}"><i class="fas fa-shield-alt"></i> Permissions</a></li>
                            @endif
                        </ul>
                    </li>
                    @endif

                    <!-- Business Report -->
                    @haspermission('business-report.view')
                    <li>
                        <a href="{{ route('business-report.index') }}"><i class="fas fa-chart-pie"></i><span> Business Report</span></a>
                    </li>
                    @endhaspermission
                    @haspermission('journal-voucher.view')
                    <li>
                        <a href="{{ route('journal-voucher.index') }}"><i class="fas fa-receipt"></i><span> All Vouchers</span></a>
                    </li>
                    @endhaspermission

                    <!-- Price List -->
                    @haspermission('price-list.view')
                    <li>
                        <a href="{{ route('price-list.index') }}"><i class="fas fa-tags"></i><span>Price List</span></a>
                    </li>
                    @endhaspermission

                    <!-- Product Management -->
                    @haspermission('product.view')
                    <li>
                        <a href="{{ route('product') }}"><i class="fas fa-box-open"></i> <span>Product </span> </a>
                    </li>
                    @endhaspermission

                    <!-- Vendors -->
                    @haspermission('vendor.view')
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-friends"></i><span> Vendors</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('vendor.view')
                            <li><a href="{{ route('vendors') }}">All Vendors</a></li>
                            @endhaspermission
                            @haspermission('vendor.view')
                            <li><a href="{{ route('vendors-ledger') }}">Vendors Ledger </a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endhaspermission

                    <!-- Purchase -->
                    @if(auth()->user()->hasPermission('purchase.create') || auth()->user()->hasPermission('purchase.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-basket"></i><span> Purchase</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('purchase.create')
                            <li><a href="{{ route('Purchase') }}">Add Purchase</a></li>
                            @endhaspermission
                            @haspermission('purchase.view')
                            <li><a href="{{ route('all-Purchases') }}">All Purchase</a></li>
                            @endhaspermission
                            @haspermission('purchase.view')
                            <li><a href="{{ route('all-purchase-return') }}"> Purchase Returns</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endif

                    <!-- Customers -->
                    @haspermission('customer.view')
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-users"></i><span> Customer</span>
                            <span class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('customer.view')
                            <li><a href="{{ route('customer') }}">All Customers</a></li>
                            @endhaspermission
                            @haspermission('customer.view')
                            <li><a href="{{ route('customer-ledger') }}">Customer Ledger</a></li>
                            @endhaspermission
                            @haspermission('customer.view')
                            <li><a href="{{ route('customer-recovery') }}">Customer Recoveries</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endhaspermission

                    <!-- Sale -->
                    @if(auth()->user()->hasPermission('local-sale.view') || auth()->user()->hasPermission('sale.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-shopping-bag"></i><span>Sale</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('local-sale.view')
                            <li><a href="{{ route('local-sale') }}">Add Sale</a></li>
                            @endhaspermission
                            @haspermission('local-sale.view')
                            <li><a href="{{ route('all-local-sale') }}">All Sales</a></li>
                            @endhaspermission
                            @haspermission('sale.view')
                            <li><a href="{{ route('all-sale-return') }}">Return Sales</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endif

                    <!-- Stock Out -->
                    @haspermission('stock-out.view')
                    <li>
                        <a href="{{ route('stockout.index') }}"><i class="fas fa-level-down-alt"></i><span> Stock Out</span></a>
                    </li>
                    @endhaspermission

                    <!-- Job Assignment -->
                    @if(auth()->user()->hasPermission('job-order.view') || auth()->user()->hasPermission('job-assignment.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-clipboard-list"></i><span> Job Management</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('job-order.view')<li><a href="{{ route('job-orders.index') }}">Assign Job</a></li>@endhaspermission
                            @haspermission('job-assignment.view')<li><a href="{{ route('job-assignments') }}">Job Assignments</a></li>@endhaspermission
                        </ul>
                    </li>
                    @endif

                    <!-- Contractor -->
                    @haspermission('contractor.view')
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fa fa-wrench"></i><span> Contractor</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('contractor.view')
                            <li><a href="{{ route('contractor') }}">All Contractor</a></li>
                            @endhaspermission
                            @haspermission('contractor.view')
                            <li><a href="{{ route('contractor-ledger') }}">Contractor Payments</a></li>
                            @endhaspermission
                            @haspermission('contractor.view')
                            <li><a href="{{ route('contractor-recovery') }}">Contractor Given Payments</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endhaspermission

                    <!-- Staff -->
                    @if(auth()->user()->hasPermission('designation.view') || auth()->user()->hasPermission('salesman.view') || auth()->user()->hasPermission('staff-report.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Staff</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('designation.view')
                            <li><a href="{{ route('designation') }}">Add Designation</a></li>
                            @endhaspermission
                            @haspermission('salesman.view')
                            <li><a href="{{ route('salesmen') }}">Add Staff</a></li>
                            @endhaspermission
                            @haspermission('staff-report.view')
                            <li><a href="{{ route('staff-wise-report') }}"><i class="fas fa-money-check-alt"></i> Weekly Staff Payment</a></li>
                            @endhaspermission
                            @haspermission('salesman.view')
                            <li><a href="{{ route('staff-recovery') }}">Staff Given Payments</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endif

                    <!-- Staff Attendance -->
                    @if(auth()->user()->hasPermission('staff-attendance.view') || auth()->user()->hasPermission('staff-advance.view') || auth()->user()->hasPermission('staff-salary.view') || auth()->user()->hasPermission('staff-ledger.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-check"></i><span> Staff Management</span> <span class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('staff-attendance.view')
                            <li><a href="{{ route('staff-attendance.index') }}">Attendance</a></li>
                            @endhaspermission
                            @haspermission('staff-advance.view')
                            <li><a href="{{ route('staff-advance.index') }}">Advance / Loan</a></li>
                            @endhaspermission
                            @haspermission('staff-salary.view')
                            <li><a href="{{ route('staff-salary.index') }}">Pay Salary</a></li>
                            @endhaspermission
                            @haspermission('staff-ledger.view')
                            <li><a href="{{ route('staff-ledger-view') }}">Staff Ledger</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endif

                    <!-- Cash Book / Ledger -->
                    @haspermission('cash-book.view')
                    <li>
                        <a href="{{ route('cash-book') }}"><i class="fas fa-book"></i><span> Cash Book</span></a>
                    </li>
                    @endhaspermission

                    <!-- Expenses -->
                    @haspermission('expense.view')
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-money-bill-wave"></i><span> Expense Categories</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('expense.view')
                            <li><a href="{{ route('expense') }}">Manage Categories</a></li>
                            @endhaspermission
                            @haspermission('expense.view')
                            <li><a href="{{ route('add-expenses') }}">All Expenses</a></li>
                            @endhaspermission
                        </ul>
                    </li>
                    @endhaspermission

                    <!-- Reports -->
                    @if(auth()->user()->hasPermission('stock-report.view') || auth()->user()->hasPermission('purchase-report.view') || auth()->user()->hasPermission('sales-report.view') || auth()->user()->hasPermission('staff-report.view'))
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-chart-line"></i><span> Reports</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            @haspermission('stock-report.view')<li><a href="{{ route('stock-Record') }}">Stock Report</a></li>@endhaspermission
                            @haspermission('purchase-report.view')<li><a href="{{ route('date-wise-purcahse-report') }}">Purchase Report</a></li>@endhaspermission
                            @haspermission('sales-report.view')<li><a href="{{ route('Date-wise-Sales-Report') }}">Sales Report</a></li>@endhaspermission
                            @haspermission('staff-report.view')<li><a href="{{ route('staff-wise-report') }}">Staff Report</a></li>@endhaspermission
                        </ul>
                    </li>
                    @endif

                    <!-- Company Settings -->
                    @haspermission('settings.edit')
                    <li>
                        <a href="{{ route('settings.company.edit') }}"><i class="fas fa-cog"></i><span> Company Settings</span></a>
                    </li>
                    @endhaspermission

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
                            <li><a href="{{ route('all-sale-return') }}">Return Sales</a></li>
                        </ul>
                    </li>
                    <li class="submenu">
                        <a href="javascript:void(0);"><i class="fas fa-user-tie"></i><span> Staff Management</span> <span
                                class="menu-arrow"></span></a>
                        <ul>
                            <li><a href="{{ route('designation') }}">Add Designation</a></li>
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
                            <li><a href="{{ route('all-sale-return') }}">Return Sales</a></li>
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
