<?php
require_once 'includes/header.php';

// Capture search & filter inputs (using GET so filters stay in URL)
$check_in  = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests    = isset($_GET['guests']) && $_GET['guests'] !== '' ? (int)$_GET['guests'] : 1;
$room_type = $_GET['room_type'] ?? '';
$max_price = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float)$_GET['max_price'] : '';

// Base SQL query: only active ('available') rooms
$sql = "SELECT * FROM rooms WHERE room_status = 'available' AND max_guests >= ?";
$params = [$guests];

// Bonus Filter 1: Room Type
if (!empty($room_type)) {
    $sql .= " AND room_type = ?";
    $params[] = $room_type;
}

// Bonus Filter 2: Max Price per night
if (!empty($max_price)) {
    $sql .= " AND price_per_night <= ?";
    $params[] = $max_price;
}

// Availability Check against Confirmed bookings
if (!empty($check_in) && !empty($check_out)) {
    $sql .= " AND room_id NOT IN (
        SELECT room_id FROM bookings 
        WHERE booking_status = 'Confirmed' 
        AND check_in < ? 
        AND check_out > ?
    )";
    $params[] = $check_out;
    $params[] = $check_in;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();
?>

<h2 class="mb-4">Rooms & Availability Search</h2>

<!-- Search & Filter Form -->
<div class="card shadow-sm mb-4 bg-light">
    <div class="card-body">
        <form method="GET" action="rooms.php" class="row g-3" onsubmit="return validateSearchDates()">
            <div class="col-md-2">
                <label class="form-label fw-semibold">Check-In</label>
                <input type="date" name="check_in" id="search_check_in" class="form-control" value="<?= htmlspecialchars($check_in) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Check-Out</label>
                <input type="date" name="check_out" id="search_check_out" class="form-control" value="<?= htmlspecialchars($check_out) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Guests</label>
                <input type="number" name="guests" class="form-control" min="1" max="10" value="<?= $guests ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Room Type</label>
                <select name="room_type" class="form-select">
                    <option value="">All Types</option>
                    <option value="Standard" <?= $room_type === 'Standard' ? 'selected' : '' ?>>Standard</option>
                    <option value="Deluxe" <?= $room_type === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                    <option value="Suite" <?= $room_type === 'Suite' ? 'selected' : '' ?>>Suite</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Max Price (LKR)</label>
                <input type="number" name="max_price" class="form-control" placeholder="e.g. 20000" value="<?= htmlspecialchars((string)$max_price) ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">Search</button>
                <a href="rooms.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<!-- Display Available Rooms -->
<?php if (empty($rooms)): ?>
    <div class="alert alert-warning">No rooms match your search criteria or selected dates. Please try different dates or filters.</div>
<?php else: ?>
    <div class="row g-4">
        <?php foreach ($rooms as $room): ?>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm room-card">
                    <img src="<?= htmlspecialchars($room['image_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($room['room_name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-info text-dark"><?= htmlspecialchars($room['room_type']) ?></span>
                            <span class="text-muted small">Max Guests: <?= (int)$room['max_guests'] ?></span>
                        </div>
                        <h5 class="card-title"><?= htmlspecialchars($room['room_name']) ?></h5>
                        <p class="card-text text-muted flex-grow-1"><?= htmlspecialchars($room['description']) ?></p>
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-primary fs-5">LKR <?= number_format($room['price_per_night'], 2) ?> <small class="text-muted fs-6">/ night</small></span>
                            <?php
                                // Pass selected dates forward to book.php
                                $bookUrl = "book.php?room_id=" . $room['room_id'];
                                if ($check_in && $check_out) {
                                    $bookUrl .= "&check_in=" . urlencode($check_in) . "&check_out=" . urlencode($check_out);
                                }
                            ?>
                            <a href="<?= $bookUrl ?>" class="btn btn-success">Book Room</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<script>
function validateSearchDates() {
    const checkIn = document.getElementById('search_check_in').value;
    const checkOut = document.getElementById('search_check_out').value;
    const today = new Date().toISOString().split('T')[0];

    if (checkIn && checkIn < today) {
        alert("Check-in date cannot be in the past.");
        return false;
    }
    if ((checkIn && !checkOut) || (!checkIn && checkOut)) {
        alert("Please select both Check-In and Check-Out dates.");
        return false;
    }
    if (checkIn && checkOut && checkOut <= checkIn) {
        alert("Check-out date must be after the Check-in date.");
        return false;
    }
    return true;
}
</script>

<?php require_once 'includes/footer.php'; ?>