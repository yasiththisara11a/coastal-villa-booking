<?php
require_once __DIR__ . '/db.php';
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mirissa Bay Coastal Villa</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Hybrid CSS -->
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark navbar-coastal shadow-sm sticky-top">
    <div class="container-fluid px-4 px-lg-5">
        <a class="navbar-brand" href="index.php">🌊 Mirissa Bay Villa</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'index.php' ? 'active fw-bold' : '' ?>" href="index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $currentPage === 'rooms.php' ? 'active fw-bold' : '' ?>" href="rooms.php">Rooms & Accommodation</a>
                </li>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'guest'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $currentPage === 'my_bookings.php' ? 'active fw-bold' : '' ?>" href="my_bookings.php">My Bookings</a>
                    </li>
                <?php endif; ?>
                <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link fw-bold text-warning" href="admin.php">Admin Dashboard</a>
                    </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
                <a href="rooms.php" class="btn btn-sand btn-pill btn-sm">Book Now</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <span class="text-white small mx-2">Hi, <?= htmlspecialchars($_SESSION['name']) ?></span>
                    <a class="btn btn-outline-light btn-pill btn-sm" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-pill btn-sm" href="login.php">Sign In</a>
                    <a class="btn btn-teal btn-pill btn-sm" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($currentPage !== 'index.php'): ?>
<div class="container py-4 mb-4">
<?php endif; ?>