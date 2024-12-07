<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>D.R. HEALTH MEDICAL AND DIAGNOSTIC CENTER</title>
    <link rel="shortcut icon" type="image/x-icon" href="images/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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
            <a href="index.php" onclick="confirmLogout()" class="text-white hover:text-gray-200 flex items-center">
                <i class="fas fa-sign-out-alt w-5 h-5 mr-1"></i>
                Logout
            </a>
        </div>
    </div>
    <div class="lg:hidden bg-teal-700" id="navbar-menu">
        <ul class="flex flex-col items-center py-4">
            <li class="py-2">
                <a href="index.php" onclick="confirmLogout()" class="text-white hover:text-gray-200 flex items-center">
                    <i class="fas fa-sign-out-alt w-5 h-5 mr-1"></i>
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

    // Confirm logout with a popup and redirect to the login page if confirmed
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "login.php"; // Replace with your login page URL
        }
    }
</script>

</body>
</html>