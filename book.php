<?php
require_once 'includes/header.php';

// 1. Only logged-in guests can make a booking
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Fetch logged-in user details to pre-fill the form
$userStmt = $pdo->prepare("SELECT name, email, contact_number FROM users WHERE user_id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$currentUser = $userStmt->fetch();

// Fetch all available rooms for the dropdown
$roomsStmt = $pdo->query("SELECT room_id, room_name, price_per_night, max_guests FROM rooms WHERE room_status = 'available'");
$allRooms = $roomsStmt->fetchAll();

// Get pre-selected values from URL (if coming from rooms.php)
$selected_room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : ($allRooms[0]['room_id'] ?? 0);
$check_in  = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';

$error = '';
$success = '';

// 2. Handle Form Submission (Server-Side Processing)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_room_id = (int)$_POST['room_id'];
    $check_in  = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $today     = date('Y-m-d');

    // Fetch chosen room's real price from database
    $roomCheck = $pdo->prepare("SELECT price_per_night FROM rooms WHERE room_id = ? AND room_status = 'available'");
    $roomCheck->execute([$selected_room_id]);
    $roomData = $roomCheck->fetch();

    if (!$roomData) {
        $error = "Selected room is not available.";
    } elseif (empty($check_in) || empty($check_out)) {
        $error = "Please select both check-in and check-out dates.";
    } elseif ($check_in < $today) {
        $error = "Check-in date cannot be in the past.";
    } elseif ($check_out <= $check_in) {
        $error = "Check-out date must be at least 1 day after check-in.";
    } else {
        // Check for overlapping CONFIRMED bookings
        $overlapStmt = $pdo->prepare("
            SELECT booking_id FROM bookings 
            WHERE room_id = ? 
            AND booking_status = 'Confirmed' 
            AND check_in < ? 
            AND check_out > ?
        ");
        $overlapStmt->execute([$selected_room_id, $check_out, $check_in]);

        if ($overlapStmt->fetch()) {
            $error = "Sorry, this room is already confirmed for the selected dates. Please choose different dates or another room.";
        } else {
            // Calculate nights and total amount securely on the server
            $date1 = new DateTime($check_in);
            $date2 = new DateTime($check_out);
            $nights = $date1->diff($date2)->days;
            $total_amount = $nights * $roomData['price_per_night'];

            // Insert booking with 'Pending' status
            $insert = $pdo->prepare("
                INSERT INTO bookings (user_id, room_id, check_in, check_out, total_amount, booking_status) 
                VALUES (?, ?, ?, ?, ?, 'Pending')
            ");

            if ($insert->execute([$_SESSION['user_id'], $selected_room_id, $check_in, $check_out, $total_amount])) {
                $success = "Booking request submitted! Total for $nights night(s): LKR " . number_format($total_amount, 2) . ".";
            } else {
                $error = "Failed to submit booking. Please try again.";
            }
        }
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h3 class="card-title mb-4">Book Your Coastal Stay</h3>

                <?php if ($error): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= htmlspecialchars($success) ?> 
                        <a href="my_bookings.php" class="fw-bold">View My Bookings</a>
                    </div>
                <?php endif; ?>

                <form method="POST" onsubmit="return validateBookingForm()">
                    <!-- Guest Details (Pre-filled from account) -->
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Guest Name</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($currentUser['name']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control bg-light" value="<?= htmlspecialchars($currentUser['email']) ?>" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contact Number</label>
                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($currentUser['contact_number']) ?>" readonly>
                        </div>
                    </div>

                    <!-- Room Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Room</label>
                        <select name="room_id" id="room_select" class="form-select" onchange="calculateCost()" required>
                            <?php foreach ($allRooms as $r): ?>
                                <option value="<?= $r['room_id'] ?>" 
                                        data-price="<?= $r['price_per_night'] ?>"
                                        <?= $r['room_id'] == $selected_room_id ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($r['room_name']) ?> — LKR <?= number_format($r['price_per_night'], 2) ?> / night (Max <?= $r['max_guests'] ?> guests)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Date Selection -->
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Check-In Date</label>
                            <input type="date" name="check_in" id="check_in" class="form-control" value="<?= htmlspecialchars($check_in) ?>" onchange="calculateCost()" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Check-Out Date</label>
                            <input type="date" name="check_out" id="check_out" class="form-control" value="<?= htmlspecialchars($check_out) ?>" onchange="calculateCost()" required>
                        </div>
                    </div>

                    <!-- Dynamic Price Display Box (Updated by JavaScript) -->
                    <div class="alert alert-info d-flex justify-content-between align-items-center" id="priceBox">
                        <div>
                            <strong>Duration:</strong> <span id="nightsDisplay">0</span> night(s)
                        </div>
                        <div class="fs-5">
                            <strong>Estimated Total:</strong> LKR <span id="totalDisplay">0.00</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 btn-lg">Confirm Booking Request</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Calculate nights and total cost dynamically
function calculateCost() {
    const roomSelect = document.getElementById('room_select');
    const selectedOption = roomSelect.options[roomSelect.selectedIndex];
    const pricePerNight = parseFloat(selectedOption.getAttribute('data-price')) || 0;

    const checkInVal = document.getElementById('check_in').value;
    const checkOutVal = document.getElementById('check_out').value;

    const nightsDisplay = document.getElementById('nightsDisplay');
    const totalDisplay = document.getElementById('totalDisplay');

    if (checkInVal && checkOutVal) {
        const d1 = new Date(checkInVal);
        const d2 = new Date(checkOutVal);
        const diffTime = d2 - d1;
        const nights = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        if (nights > 0) {
            const total = nights * pricePerNight;
            nightsDisplay.textContent = nights;
            totalDisplay.textContent = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            return;
        }
    }

    nightsDisplay.textContent = '0';
    totalDisplay.textContent = '0.00';
}

// Client-side validation before submitting
function validateBookingForm() {
    const checkInVal = document.getElementById('check_in').value;
    const checkOutVal = document.getElementById('check_out').value;
    const today = new Date().toISOString().split('T')[0];

    if (checkInVal < today) {
        alert("Check-in date cannot be in the past.");
        return false;
    }
    if (checkOutVal <= checkInVal) {
        alert("Check-out date must be after the check-in date.");
        return false;
    }
    return true;
}

// Run calculation immediately on page load in case dates were passed in URL
window.onload = calculateCost;
</script>

<?php require_once 'includes/footer.php'; ?>