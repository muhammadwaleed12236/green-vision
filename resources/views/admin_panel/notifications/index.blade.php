@extends('admin_panel.include.admin_dash_layout')

@section('title', 'All Notifications')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <h4 class="mb-0">
                                <i class="fa fa-bell me-2 text-primary"></i>
                                All Notifications
                            </h4>
                        </div>
                        <div class="col-md-6 text-end">
                            @if(isset($notifications) && $notifications->where('is_read', 0)->count() > 0)
                            <button class="btn btn-sm btn-primary" id="markAllReadBtn">
                                <i class="fa fa-check-double me-1"></i>
                                Mark All as Read
                            </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <!-- Filter Tabs -->
                    <ul class="nav nav-tabs px-3 pt-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#all-notifications" role="tab">
                                All <span class="badge bg-secondary ms-1">{{ $notifications->total() ?? 0 }}</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#unread-notifications" role="tab">
                                Unread <span class="badge bg-danger ms-1">{{ $notifications->where('is_read', 0)->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <!-- Tab Content -->
                    <div class="tab-content p-3">
                        <!-- All Notifications Tab -->
                        <div class="tab-pane fade show active" id="all-notifications" role="tabpanel">
                            @if(isset($notifications) && $notifications->count() > 0)
                                <div class="list-group">
                                    @foreach($notifications as $notification)
                                    <div class="list-group-item notification-item {{ $notification->is_read ? '' : 'unread' }}" data-id="{{ $notification->id }}">
                                        <div class="d-flex align-items-start">
                                            <!-- Icon -->
                                            <div class="notification-icon me-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-{{ $notification->color ?? 'primary' }}"
                                                     style="width: 45px; height: 45px;">
                                                    <i class="fa {{ $notification->icon ?? 'fa-bell' }} text-white"></i>
                                                </div>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">
                                                            @if(!$notification->is_read)
                                                            <span class="badge badge-dot bg-primary me-2"></span>
                                                            @endif
                                                            {{ $notification->title }}
                                                        </h6>
                                                        <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                                                        <span class="text-muted" style="font-size: 11px;">
                                                            <i class="fa fa-clock me-1"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            @if(!$notification->is_read)
                                                            <li>
                                                                <a class="dropdown-item mark-read-btn" href="javascript:void(0);" data-id="{{ $notification->id }}">
                                                                    <i class="fa fa-check me-2"></i>Mark as Read
                                                                </a>
                                                            </li>
                                                            @endif
                                                            @if($notification->link)
                                                            <li>
                                                                <a class="dropdown-item" href="{{ $notification->link }}">
                                                                    <i class="fa fa-external-link-alt me-2"></i>View Details
                                                                </a>
                                                            </li>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger delete-notification-btn" href="javascript:void(0);" data-id="{{ $notification->id }}">
                                                                    <i class="fa fa-trash me-2"></i>Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                @if($notification->link)
                                                <a href="{{ $notification->link }}" class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="fa fa-arrow-right me-1"></i>View Details
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <!-- Pagination -->
                                <div class="mt-4 d-flex justify-content-center">
                                    {{ $notifications->links() }}
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fa fa-bell-slash fa-4x text-muted mb-3"></i>
                                    <h5 class="text-muted">No Notifications</h5>
                                    <p class="text-muted">You're all caught up!</p>
                                </div>
                            @endif
                        </div>

                        <!-- Unread Notifications Tab -->
                        <div class="tab-pane fade" id="unread-notifications" role="tabpanel">
                            @php
                                $unreadNotifications = $notifications->where('is_read', 0);
                            @endphp

                            @if($unreadNotifications->count() > 0)
                                <div class="list-group">
                                    @foreach($unreadNotifications as $notification)
                                    <div class="list-group-item notification-item unread" data-id="{{ $notification->id }}">
                                        <div class="d-flex align-items-start">
                                            <!-- Icon -->
                                            <div class="notification-icon me-3">
                                                <div class="rounded-circle d-flex align-items-center justify-content-center bg-{{ $notification->color ?? 'primary' }}"
                                                     style="width: 45px; height: 45px;">
                                                    <i class="fa {{ $notification->icon ?? 'fa-bell' }} text-white"></i>
                                                </div>
                                            </div>

                                            <!-- Content -->
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <h6 class="mb-1 fw-bold">
                                                            <span class="badge badge-dot bg-primary me-2"></span>
                                                            {{ $notification->title }}
                                                        </h6>
                                                        <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                                                        <span class="text-muted" style="font-size: 11px;">
                                                            <i class="fa fa-clock me-1"></i>
                                                            {{ $notification->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>

                                                    <!-- Actions -->
                                                    <div class="dropdown">
                                                        <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end">
                                                            <li>
                                                                <a class="dropdown-item mark-read-btn" href="javascript:void(0);" data-id="{{ $notification->id }}">
                                                                    <i class="fa fa-check me-2"></i>Mark as Read
                                                                </a>
                                                            </li>
                                                            @if($notification->link)
                                                            <li>
                                                                <a class="dropdown-item" href="{{ $notification->link }}">
                                                                    <i class="fa fa-external-link-alt me-2"></i>View Details
                                                                </a>
                                                            </li>
                                                            @endif
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li>
                                                                <a class="dropdown-item text-danger delete-notification-btn" href="javascript:void(0);" data-id="{{ $notification->id }}">
                                                                    <i class="fa fa-trash me-2"></i>Delete
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </div>

                                                @if($notification->link)
                                                <a href="{{ $notification->link }}" class="btn btn-sm btn-outline-primary mt-2">
                                                    <i class="fa fa-arrow-right me-1"></i>View Details
                                                </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5">
                                    <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                                    <h5 class="text-muted">All Clear!</h5>
                                    <p class="text-muted">No unread notifications</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.notification-item {
    border-left: 3px solid transparent;
    transition: all 0.2s;
}

.notification-item.unread {
    background-color: #f8f9fc;
    border-left-color: #4e73df;
}

.notification-item:hover {
    background-color: #f1f3f5;
}

.badge-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    padding: 0;
}

.notification-icon {
    flex-shrink: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mark all as read
    const markAllReadBtn = document.getElementById('markAllReadBtn');
    if (markAllReadBtn) {
        markAllReadBtn.addEventListener('click', function() {
            if (confirm('Mark all notifications as read?')) {
                fetch('/notifications/mark-all-read', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    }

    // Mark single as read
    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const notificationId = this.dataset.id;
            fetch(`/notifications/mark-read/${notificationId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => console.error('Error:', error));
        });
    });

    // Delete notification
    document.querySelectorAll('.delete-notification-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (confirm('Delete this notification?')) {
                const notificationId = this.dataset.id;
                fetch(`/notifications/delete/${notificationId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
            }
        });
    });
});
</script>
@endsection
