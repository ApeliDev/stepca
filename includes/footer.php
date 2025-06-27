            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/script.js"></script>
    <script>
        // Toggle profile dropdown
        $('#user-menu-button').click(function() {
            $(this).next().toggleClass('hidden');
        });

        // Close dropdown when clicking outside
        $(document).click(function(event) {
            if (!$(event.target).closest('#user-menu-button, #user-menu-button + div').length) {
                $('#user-menu-button').next().addClass('hidden');
            }
        });

        // AJAX for notifications
        function markNotificationAsRead(notificationId) {
            $.ajax({
                url: 'mark-notification-read.php',
                method: 'POST',
                data: { id: notificationId },
                success: function(response) {
                    if (response.success) {
                        $('#notification-' + notificationId).removeClass('bg-gray-50').addClass('bg-white');
                        $('#notification-badge').text(parseInt($('#notification-badge').text()) - 1);
                        if (parseInt($('#notification-badge').text()) === 0) {
                            $('#notification-badge').hide();
                        }
                    }
                }
            });
        }
    </script>
</body>
</html>