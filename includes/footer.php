            </main>
            </div>
            </div>

            <script>
                function toggleUserMenu() {
                    const menu = document.getElementById('userMenu');
                    menu.classList.toggle('hidden');
                }

                // Close menu when clicking outside
                document.addEventListener('click', function(event) {
                    const menu = document.getElementById('userMenu');
                    const button = event.target.closest('button');

                    if (!button || !button.hasAttribute('onclick')) {
                        menu.classList.add('hidden');
                    }
                });
            </script>
            </body>

            </html>