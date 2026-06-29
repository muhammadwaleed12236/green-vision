<div class="header">
	<div class="header-left active">
		<a href="#" class="logo">
			<h6>{{ $appSettings['company_name'] }}</h6>
		</a>
		<a href="#" class="logo-small">
			@if($appSettings['company_logo'])
				<img src="{{ asset('storage/' . $appSettings['company_logo']) }}" alt="{{ $appSettings['company_name'] }}" style="max-height: 30px;">
			@endif
		</a>
		<a id="toggle_btn" href="javascript:void(0);">
		</a>
	</div>
	<a id="mobile_btn" class="mobile_btn" href="#sidebar">
		<span class="bar-icon">
			<span></span>
			<span></span>
			<span></span>
		</span>
	</a>

	<ul class="nav user-menu">
		<!-- Delivery Notifications -->
		<li class="nav-item dropdown">
			<a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
				<i class="fa fa-bell" style="font-size: 24px; color: #1c9262;"></i>
				@php
					$upcomingDeliveries = \App\Models\LocalSale::whereNotNull('delivery_date')
						->whereNotIn('job_status', ['completed', 'ready'])
						->where('admin_or_user_id', Auth::id())
						->get()
						->filter(function($sale) {
							$deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
							$notifyDate = $deliveryDate->subDays($sale->notify_days_before ?? 2);
							return \Carbon\Carbon::now()->greaterThanOrEqualTo($notifyDate);
						});
					$notificationCount = $upcomingDeliveries->count();
				@endphp
				@if($notificationCount > 0)
					<span class="badge bg-danger" style="position: absolute; top: 5px; right: 5px; font-size: 10px; padding: 2px 6px; border-radius: 10px;">
						{{ $notificationCount }}
					</span>
				@endif
			</a>
			<div class="dropdown-menu dropdown-menu-end" style="min-width: 350px; max-height: 400px; overflow-y: auto;">
				<div class="dropdown-header bg-primary text-white">
					<strong><i class="fa fa-truck me-2"></i>Upcoming Deliveries</strong>
				</div>
				@if($notificationCount > 0)
					@foreach($upcomingDeliveries as $sale)
						@php
							$deliveryDate = \Carbon\Carbon::parse($sale->delivery_date);
							$daysLeft = \Carbon\Carbon::now()->diffInDays($deliveryDate, false);
							$isOverdue = $daysLeft < 0;
							$customerName = $sale->customer ? $sale->customer->customer_name : ($sale->customer_shopname ?? 'Walk-in');
						@endphp
						<a href="{{ route('all-local-sale') }}" class="dropdown-item {{ $isOverdue ? 'bg-danger text-white' : '' }}">
							<div class="d-flex justify-content-between align-items-start">
								<div>
									<strong>{{ $sale->invoice_number }}</strong><br>
									<small>{{ $customerName }}</small><br>
									<small class="text-muted">
										<i class="fa fa-calendar"></i> {{ $deliveryDate->format('d M Y') }}
									</small>
								</div>
								<div class="text-end">
									@if($isOverdue)
										<span class="badge bg-white text-danger">
											<i class="fa fa-exclamation-triangle"></i> Overdue
										</span>
									@elseif($daysLeft == 0)
										<span class="badge bg-warning text-dark">
											<i class="fa fa-clock"></i> Today
										</span>
									@else
										<span class="badge bg-info">
											{{ abs($daysLeft) }} days left
										</span>
									@endif
								</div>
							</div>
						</a>
						<hr class="dropdown-divider my-1">
					@endforeach
					<a href="{{ route('delivery-notifications') }}" class="dropdown-item text-center text-primary">
						<strong>View All Notifications</strong>
					</a>
				@else
					<div class="dropdown-item text-center text-muted">
						<i class="fa fa-check-circle me-2"></i>No pending deliveries
					</div>
				@endif
			</div>
		</li>

		<li class="nav-item">
			<h5 class="mt-3">
				@if(auth()->check())
				@if(auth()->user()->usertype === 'admin')
				Admin Dashboard
				@elseif(auth()->user()->usertype === 'distributor')
				Distributor Dashboard
				@elseif(auth()->user()->usertype === 'salesman')
				Salesman Dashboard
				@else
				User
				@endif
				@endif
			</h5>
		</li>
		<li class="nav-item dropdown has-arrow main-drop">
			<a href="javascript:void(0);" class="dropdown-toggle nav-link userset" data-bs-toggle="dropdown">
				<span class="user-img"><img src="{{ url('assets/img/profiles/manager.png') }}" alt="">
					<span class="status online"></span></span>
			</a>
			<div class="dropdown-menu menu-drop-user">
				<div class="profilename">
					<div class="profileset">
						<span class="user-img"><img src="{{ url('assets/img/profiles/manager.png') }}" alt="">
							<span class="status online"></span></span>
						<div class="profilesets">
							<h6>{{ Auth::user()->name }}</h6>
							<h5>Admin</h5>
						</div>
					</div>
					<hr class="m-0">
					<hr class="m-0">
					<form method="POST" action="{{ route('logout') }}">
						@csrf
						<a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item ai-icon"> Logout </a>
					</form>
				</div>
			</div>
		</li>
	</ul>

	<div class="dropdown mobile-user-menu">
		<a href="javascript:void(0);" class="nav-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><i class="fa fa-ellipsis-v"></i></a>
		<div class="dropdown-menu dropdown-menu-right">
			<form method="POST" action="{{ route('logout') }}">
				@csrf
				<a href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();" class="dropdown-item ai-icon"> Logout </a>
			</form>
		</div>
	</div>
</div>

<script>
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.altKey && (e.key === 's' || e.key === 'S')) {
        e.preventDefault();
        window.location.href = "{{ route('local-sale') }}";
    }
});
</script>
