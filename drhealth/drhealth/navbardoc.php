<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7+cWRp+aULq+oq4q7T+ceZ5rPUP8YcQ+j56QQpx+pL/m4GX0q0w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body>

    <nav class="navbar navbar-expand navbar-dark fixed-top" id="mainNav" style="background-color: #0D409E;">
        <div class="container mx-auto flex items-center justify-between p-1">
            <a href="#" class="flex items-center ml-[-65px] mt-2">
                <img src="./images/logo.png" alt="Logo" class="h-16 w-auto mr-3">
                <span class="text-xl font-semibold">D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</span>
            </a>
            <button class="lg:hidden text-white focus:outline-none" id="navbar-toggler">
                <i class="fas fa-bars w-6 h-6"></i>
            </button>
            <div class="hidden lg:flex items-center space-x-4">
                <a href="doctor.php" onclick="confirmLogout()" class="text-white hover:text-gray-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-1"></i>
                    Logout
                </a>
            </div>
        </div>
        <div class="lg:hidden bg-blue-800 fixed inset-0 z-50 hidden" id="navbar-menu">
            <div class="absolute top-4 right-4 text-white text-2xl cursor-pointer" onclick="closeMenu()">
                <i class="fas fa-times"></i> <!-- Close button (X) -->
            </div>
            <ul class="flex flex-col items-center py-4 h-full justify-center space-y-4"> <!-- Added spacing between items -->
                <!-- Dashboard Button -->
                <li class="py-2">
                    <a href="javascript:void(0)" onclick="showDiv('dashboard'); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-tachometer-alt w-5 h-5 mr-2"></i>
                        Dashboard
                    </a>
                </li>
                <!-- Appointments Button -->
                <li class="py-2">
                    <a href="javascript:void(0)" onclick="showDiv('appointments'); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-calendar-alt w-5 h-5 mr-2"></i>
                        Appointments
                    </a>
                </li>
                <!-- Prescriptions Button -->
                <li class="py-2">
                    <a href="javascript:void(0)" onclick="showDiv('prescriptions'); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-prescription-bottle-alt w-5 h-5 mr-2"></i>
                        Prescriptions
                    </a>
                </li>
                <!-- Patient List Button -->
                <li class="py-2">
                    <a href="javascript:void(0)" onclick="showDiv('patients'); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-users w-5 h-5 mr-2"></i>
                        Patient List
                    </a>
                </li>
                <!-- Availability Button -->
                <li class="py-2">
                    <a href="javascript:void(0)" onclick="showDiv('availability'); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-clock w-5 h-5 mr-2"></i>
                        Availability
                    </a>
                </li>
                <!-- Profile Button -->
                <li class="py-2">
                    <a href="javascript:void(0)" onclick="showDiv('profile'); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-user-circle w-5 h-5 mr-2"></i>
                        Profile
                    </a>
                </li>
                <!-- Logout Button -->
                <li class="py-2">
                    <a href="doctor.php" onclick="confirmLogout(); closeMenu()" class="text-white hover:text-gray-200 flex items-center">
                        <i class="fas fa-sign-out-alt w-5 h-5 mr-2"></i>
                        Logout
                    </a>
                </li>
            </ul>
        </div>

    </nav>

    <script>
        // Toggle mobile menu visibility
        document.getElementById('navbar-toggler').addEventListener('click', () => {
            const menu = document.getElementById('navbar-menu');
            menu.classList.toggle('hidden');
        });

        // Close mobile menu when an item is clicked
        function closeMenu() {
            const menu = document.getElementById('navbar-menu');
            menu.classList.add('hidden');
        }

        // Confirm logout with a popup and redirect to the login page if confirmed
        function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php"; // Replace with your login page URL
            }
        }

        // Show the correct content in the dashboard
        function showDiv(divId) {
            // Hide all divs
            const divs = document.querySelectorAll('.content-wrapper > div');
            divs.forEach(div => div.classList.add('hidden'));

            // Show the selected div
            document.getElementById(divId).classList.remove('hidden');
        }
    </script>

</body>

</html>