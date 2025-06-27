            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Toggle admin dropdown
        $('#admin-menu-button').click(function() {
            $(this).next().toggleClass('hidden');
        });

        // Close dropdown when clicking outside
        $(document).click(function(event) {
            if (!$(event.target).closest('#admin-menu-button, #admin-menu-button + div').length) {
                $('#admin-menu-button').next().addClass('hidden');
            }
        });

        // Initialize DataTables
        $(document).ready(function() {
            $('.data-table').DataTable({
                responsive: true,
                dom: '<"flex justify-between items-center mb-4"<"flex"f><"flex"l>>rt<"flex justify-between items-center mt-4"<"flex"i><"flex"p>>',
                language: {
                    search: "",
                    searchPlaceholder: "Search...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    paginate: {
                        previous: "Previous",
                        next: "Next"
                    }
                }
            });
        });
    </script>
</body>
</html>