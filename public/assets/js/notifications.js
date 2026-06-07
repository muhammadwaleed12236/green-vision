// Facebook-Style Notification System
(function() {
    'use strict';

    // Notification Manager
    const NotificationManager = {
        badge: null,
        dropdown: null,
        listContainer: null,
        unreadCount: 0,

        init: function() {
            this.badge = document.getElementById('notificationBadge');
            this.dropdown = document.getElementById('notificationDropdown');
            this.listContainer = document.getElementById('notificationsList');

            if (!this.badge || !this.dropdown || !this.listContainer) {
                console.log('Notification elements not found');
                return;
            }

            this.loadNotifications();
            this.setupEventListeners();
            this.startAutoRefresh();
        },

        loadNotifications: function() {
            console.log('Loading notifications from:', '/notifications/recent');
            fetch('/notifications/recent', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('Notifications loaded:', data);
                    this.unreadCount = data.unreadCount || 0;
                    this.updateBadge();
                    this.renderNotifications(data.notifications || []);
                })
                .catch(error => {
                    console.error('Error loading notifications:', error);
                    this.listContainer.innerHTML = `
                        <div class="text-center py-4 text-danger">
                            <i class="fa fa-exclamation-triangle fa-2x mb-2"></i>
                            <p class="mb-0 small">Failed to load notifications</p>
                            <p class="mb-0 small text-muted">${error.message}</p>
                        </div>
                    `;
                });
        },

        updateBadge: function() {
            if (this.unreadCount > 0) {
                this.badge.textContent = this.unreadCount > 99 ? '99+' : this.unreadCount;
                this.badge.style.display = 'inline-block';
            } else {
                this.badge.style.display = 'none';
            }
        },

        renderNotifications: function(notifications) {
            if (notifications.length === 0) {
                this.listContainer.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="fa fa-bell-slash fa-3x mb-3" style="opacity: 0.3;"></i>
                        <p class="mb-0">No notifications yet</p>
                        <small>You're all caught up!</small>
                    </div>
                `;
                return;
            }

            let html = '';
            notifications.forEach(notification => {
                html += this.renderNotificationItem(notification);
            });

            this.listContainer.innerHTML = html;
        },

        renderNotificationItem: function(notification) {
            const isRead = notification.is_read;
            const bgClass = isRead ? '' : 'bg-light';
            const iconColor = this.getIconColor(notification.color);
            const timeAgo = this.getTimeAgo(notification.created_at);

            return `
                <div class="dropdown-item notification-item ${bgClass}"
                     data-id="${notification.id}"
                     data-link="${notification.link || '#'}"
                     style="cursor: pointer; padding: 12px 16px; border-bottom: 1px solid #f0f0f0; position: relative;">

                    ${!isRead ? '<span class="unread-dot" style="position: absolute; left: 5px; top: 50%; transform: translateY(-50%); width: 8px; height: 8px; background: #0d6efd; border-radius: 50%;"></span>' : ''}

                    <div class="d-flex align-items-start" style="padding-left: ${!isRead ? '12px' : '0'};">
                        <div class="notification-icon me-3 d-flex align-items-center justify-content-center"
                             style="width: 40px; height: 40px; background: ${iconColor}15; border-radius: 50%; flex-shrink: 0;">
                            <i class="fa ${notification.icon}" style="color: ${iconColor}; font-size: 18px;"></i>
                        </div>
                        <div class="flex-grow-1" style="min-width: 0;">
                            <h6 class="mb-1 fw-bold" style="font-size: 13px; line-height: 1.4;">${notification.title}</h6>
                            <p class="mb-1 text-muted" style="font-size: 12px; line-height: 1.4;">${notification.message}</p>
                            <small class="text-muted" style="font-size: 11px;">
                                <i class="fa fa-clock me-1"></i>${timeAgo}
                            </small>
                        </div>
                        <button class="btn btn-sm btn-link text-muted delete-notification p-0 ms-2"
                                data-id="${notification.id}"
                                onclick="event.stopPropagation();"
                                style="opacity: 0.6; font-size: 12px;">
                            <i class="fa fa-times"></i>
                        </button>
                    </div>
                </div>
            `;
        },

        getIconColor: function(color) {
            const colors = {
                'primary': '#0d6efd',
                'success': '#28a745',
                'warning': '#ffc107',
                'danger': '#dc3545',
                'info': '#17a2b8'
            };
            return colors[color] || colors['info'];
        },

        getTimeAgo: function(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);

            if (seconds < 60) return 'Just now';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
            if (seconds < 604800) return Math.floor(seconds / 86400) + 'd ago';
            return date.toLocaleDateString();
        },

        setupEventListeners: function() {
            // Click on notification item
            document.addEventListener('click', (e) => {
                const item = e.target.closest('.notification-item');
                if (item && !e.target.closest('.delete-notification')) {
                    const id = item.dataset.id;
                    const link = item.dataset.link;
                    this.markAsRead(id, link);
                }
            });

            // Delete notification
            document.addEventListener('click', (e) => {
                if (e.target.closest('.delete-notification')) {
                    const btn = e.target.closest('.delete-notification');
                    const id = btn.dataset.id;
                    this.deleteNotification(id);
                }
            });

            // Mark all as read
            const markAllBtn = document.getElementById('markAllRead');
            if (markAllBtn) {
                markAllBtn.addEventListener('click', () => {
                    this.markAllAsRead();
                });
            }

            // Reload on dropdown open
            if (this.dropdown) {
                this.dropdown.addEventListener('click', () => {
                    this.loadNotifications();
                });
            }
        },

        markAsRead: function(id, link) {
            fetch(`/notifications/mark-read/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (link && link !== '#') {
                        window.location.href = link;
                    } else {
                        this.loadNotifications();
                    }
                }
            })
            .catch(error => console.error('Error marking as read:', error));
        },

        markAllAsRead: function() {
            fetch('/notifications/mark-all-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.loadNotifications();
                }
            })
            .catch(error => console.error('Error marking all as read:', error));
        },

        deleteNotification: function(id) {
            if (!confirm('Delete this notification?')) return;

            fetch(`/notifications/delete/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.loadNotifications();
                }
            })
            .catch(error => console.error('Error deleting notification:', error));
        },

        startAutoRefresh: function() {
            // Refresh every 30 seconds
            setInterval(() => {
                if (!document.querySelector('.notifications-dropdown.show')) {
                    this.loadNotifications();
                }
            }, 30000);
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => NotificationManager.init());
    } else {
        NotificationManager.init();
    }
})();
