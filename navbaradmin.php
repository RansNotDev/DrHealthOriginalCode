<?php
ob_start(); // Starts output buffering
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7+cWRp+aULq+oq4q7T+ceZ5rPUP8YcQ+j56QQpx+pL/m4GX0q0w=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand navbar-dark fixed-top" id="mainNav" style="background-color: #0D409E;">
        <div class="container mx-auto flex items-center justify-between p-1">
            <a href="#" class="flex items-center ml-[-65px] mt-2">
                <img src="./images/logo.png" alt="Logo" class="h-16 w-auto mr-3">
                <span class="text-xl font-semibold text-white">D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</span>
            </a>
            <button class="lg:hidden text-white focus:outline-none" id="navbar-toggler">
                <i class="fas fa-bars w-6 h-6"></i>
            </button>
            <div class="hidden lg:flex items-center space-x-4">
                <a href="admin.php" onclick="confirmLogout()" class="text-white hover:text-gray-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-1"></i>
                    Logout
                </a>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div class="lg:hidden bg-blue-800 fixed inset-0 z-50 hidden" id="navbar-menu">
            <div class="absolute top-4 right-4 text-white text-2xl cursor-pointer" onclick="closeMenu()">
                <i class="fas fa-times"></i> <!-- Close button -->
            </div>
            <ul class="flex flex-col items-center py-4 h-full justify-center space-y-4">
                <!-- Navigation Items -->
                <li><a href="javascript:void(0)" onclick="showDiv('dashboard'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fas fa-tachometer-alt w-5 h-5 mr-2"></i> Dashboard</a></li>
                <li><a href="javascript:void(0)" onclick="showDiv('list-doc'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fa fa-user-md w-5 h-5 mr-2"></i> Doctor List</a></li>
                <li><a href="javascript:void(0)" onclick="showDiv('list-pat'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i class="fa fa-user w-5 h-5 mr-2"></i>
                        Patient List</a></li>
                <li><a href="javascript:void(0)" onclick="showDiv('list-app'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fa fa-calendar-alt w-5 h-5 mr-2"></i> Appointment Details</a></li>
                <li><a href="javascript:void(0)" onclick="showDiv('add-doctor'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fa fa-user-plus w-5 h-5 mr-2"></i> Add Doctor</a></li>
                <li><a href="javascript:void(0)" onclick="showDiv('list-archived'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fa fa-archive w-5 h-5 mr-2"></i> Archived Doctor</a></li>
                <li><a href="javascript:void(0)" onclick="showDiv('monthly-reports'); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fa fa-file-alt w-5 h-5 mr-2"></i> Monthly Reports</a></li>
                <li><a href="admin.php" onclick="confirmLogout(); closeMenu()"
                        class="text-white hover:text-gray-200 flex items-center"><i
                            class="fas fa-sign-out-alt w-5 h-5 mr-2"></i> Logout</a></li>
            </ul>
        </div>
    </nav>

    <script>
        // Toggle mobile menu visibility
        document.getElementById('navbar-toggler').addEventListener('click', () => {
            const menu = document.getElementById('navbar-menu');
            menu.classList.toggle('hidden');
        });

        // Close mobile menu
        function closeMenu() {
            const menu = document.getElementById('navbar-menu');
            menu.classList.add('hidden');
        }

        // Confirm logout
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "logout2.php"; // Replace with your login page URL
            }
        }

        // Show specific content section
        function showDiv(divId) {
            // Hide all divs
            const divs = document.querySelectorAll('.content-wrapper > div');
            divs.forEach(div => div.classList.add('hidden'));

            // Show selected div
            document.getElementById(divId).classList.remove('hidden');
        }
    </script>

</body>

</html>