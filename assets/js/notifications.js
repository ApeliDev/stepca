 // Modal functionality using vanilla JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('notification-modal');
            const modalContent = modal.querySelector('.modal-content');
            
            // Handle notification item clicks
            document.querySelectorAll('.notification-item').forEach(item => {
                item.addEventListener('click', function(e) {
                    // Don't trigger if clicking on form elements
                    if (e.target.closest('form')) return;
                    
                    const notificationId = this.dataset.notificationId;
                    const title = this.querySelector('.font-semibold').textContent.trim();
                    const message = this.querySelector('.text-lightGray').textContent.trim();
                    const datetime = this.querySelector('time').getAttribute('datetime');
                    const formattedDate = this.querySelector('time').textContent.trim();
                    const isUnread = this.classList.contains('bg-primary/5');
                    const icon = this.querySelector('i').className;
                    const isPending = this.querySelector('.bg-yellow-500\\/20') !== null;
                    
                    showNotificationModal({
                        id: notificationId,
                        title: title.replace(/^.*?\s/, ''), // Remove icon from title
                        message: message,
                        datetime: datetime,
                        formattedDate: formattedDate,
                        isUnread: isUnread,
                        icon: icon,
                        isPending: isPending
                    });
                    
                    // Mark as read if unread
                    if (isUnread) {
                        markNotificationAsRead(notificationId);
                    }
                });
            });
            
            // Close modal functionality
            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                modalContent.classList.remove('scale-100', 'opacity-100');
                modalContent.classList.add('scale-90', 'opacity-0');
                document.body.classList.remove('overflow-hidden');
            }
            
            // Close modal event listeners
            document.querySelectorAll('.close-modal-btn').forEach(btn => {
                btn.addEventListener('click', closeModal);
            });
            
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeModal();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    closeModal();
                }
            });
            
            // Auto-hide flash messages
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.style.opacity = '0';
                    setTimeout(() => {
                        flashMessage.style.display = 'none';
                    }, 300);
                }, 5000);
            }
        });
        
        function showNotificationModal(notification) {
            const modal = document.getElementById('notification-modal');
            const modalContent = modal.querySelector('.modal-content');
            
            // Update modal content
            document.getElementById('modal-icon').className = notification.icon + ' text-primary text-lg';
            document.getElementById('modal-title').innerHTML = `
                <i class="${notification.icon} text-primary mr-2 text-sm"></i>
                ${notification.title}
            `;
            document.getElementById('modal-date').innerHTML = `
                <i class="fas fa-clock text-primary mr-2"></i>
                ${notification.formattedDate}
            `;
            document.getElementById('modal-message').textContent = notification.message;
            
            // Show/hide new badge
            const newBadge = document.getElementById('modal-new-badge');
            if (notification.isUnread) {
                newBadge.classList.remove('hidden');
            } else {
                newBadge.classList.add('hidden');
            }
            
            // Show/hide action buttons for pending notifications
            const actionsDiv = document.getElementById('modal-actions');
            if (notification.isPending) {
                actionsDiv.innerHTML = `
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-green-500/20 text-green-400 text-sm font-medium rounded-lg hover:bg-green-500/30 transition-colors">
                        <i class="fas fa-eye mr-2"></i>
                        View Details
                    </button>
                    <button type="button" class="inline-flex items-center px-4 py-2 bg-blue-500/20 text-blue-400 text-sm font-medium rounded-lg hover:bg-blue-500/30 transition-colors">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Track Status
                    </button>
                `;
                actionsDiv.classList.remove('hidden');
            } else {
                actionsDiv.classList.add('hidden');
            }
            
            // Show modal
            document.body.classList.add('overflow-hidden');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            
            // Trigger animation
            requestAnimationFrame(() => {
                modalContent.classList.remove('scale-90', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            });
        }
        
        function markNotificationAsRead(notificationId) {
            // Create a form and submit it
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const actionInput = document.createElement('input');
            actionInput.type = 'hidden';
            actionInput.name = 'action';
            actionInput.value = 'mark_as_read';
            
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'notification_id';
            idInput.value = notificationId;
            
            form.appendChild(actionInput);
            form.appendChild(idInput);
            document.body.appendChild(form);
            form.submit();
        }
        
        // Add stagger animation to notification items
        document.querySelectorAll('.notification-item').forEach((item, index) => {
            item.style.animationDelay = (index * 0.1) + 's';
        });