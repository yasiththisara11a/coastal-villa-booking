<?php
require_once 'includes/header.php';

// 1. Access Control: Allow only logged-in administrators
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// 2. Handle Admin Actions (POST requests)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // A. Add New Room
    if ($action === 'add_room') {
        $room_name   = trim($_POST['room_name']);
        $room_type   = trim($_POST['room_type']);
        $description = trim($_POST['description']);
        $price       = (float)$_POST['price_per_night'];
        $max_guests  = (int)$_POST['max_guests'];
        $image_url   = trim($_POST['image_url']) ?: 'images/room1.jpg';

        $stmt = $pdo->prepare("INSERT INTO rooms (room_name, room_type, description, price_per_night, max_guests, room_status, image_url) VALUES (?, ?, ?, ?, ?, 'available', ?)");
        if ($stmt->execute([$room_name, $room_type, $description, $price, $max_guests, $image_url])) {
            $success = "New room '$room_name' added successfully!";
        } else {
            $error = "Failed to add room.";
        }
    }

    // B. Update Existing Room
    elseif ($action === 'update_room') {
        $room_id     = (int)$_POST['room_id'];
        $room_name   = trim($_POST['room_name']);
        $room_type   = trim($_POST['room_type']);
        $description = trim($_POST['description']);
        $price       = (float)$_POST['price_per_night'];
        $max_guests  = (int)$_POST['max_guests'];
        $room_status = $_POST['room_status'];
        $image_url   = trim($_POST['image_url']) ?: 'images/room1.jpg';

        $stmt = $pdo->prepare("UPDATE rooms SET room_name=?, room_type=?, description=?, price_per_night=?, max_guests=?, room_status=?, image_url=? WHERE room_id=?");
        if ($stmt->execute([$room_name, $room_type, $description, $price, $max_guests, $room_status, $image_url, $room_id])) {
            $success = "Room #$room_id updated successfully!";
        } else {
            $error = "Failed to update room.";
        }
    }

    // C. Delete Room
    elseif ($action === 'delete_room') {
        $room_id = (int)$_POST['room_id'];
        $stmt = $pdo->prepare("DELETE FROM rooms WHERE room_id = ?");
        if ($stmt->execute([$room_id])) {
            $success = "Room #$room_id deleted.";
        } else {
            $error = "Could not delete room.";
        }
    }

    // D. Confirm or Cancel Booking (With Overlap Protection & Pending Check)
    elseif ($action === 'update_booking') {
        $booking_id = (int)$_POST['booking_id'];
        $new_status = $_POST['new_status'];

        if ($new_status === 'Confirmed') {
            // Only allow confirming bookings that are currently Pending
            $bStmt = $pdo->prepare("SELECT room_id, check_in, check_out FROM bookings WHERE booking_id = ? AND booking_status = 'Pending'");
            $bStmt->execute([$booking_id]);
            $target = $bStmt->fetch();

            if ($target) {
                // Check if another CONFIRMED booking already overlaps these dates for the same room
                $overlapStmt = $pdo->prepare("
                    SELECT booking_id FROM bookings 
                    WHERE room_id = ? 
                    AND booking_id != ?
                    AND booking_status = 'Confirmed' 
                    AND check_in < ? 
                    AND check_out > ?
                ");
                $overlapStmt->execute([$target['room_id'], $booking_id, $target['check_out'], $target['check_in']]);

                if ($overlapStmt->fetch()) {
                    $error = "Cannot confirm Booking #$booking_id! Another confirmed booking already overlaps these dates for this room.";
                } else {
                    $upd = $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ?");
                    $upd->execute([$booking_id]);
                    $success = "Booking #$booking_id has been Confirmed!";
                }
            } else {
                $error = "Only Pending bookings can be confirmed.";
            }
        } elseif ($new_status === 'Cancelled') {
            $upd = $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ? AND booking_status != 'Cancelled'");
            $upd->execute([$booking_id]);
            if ($upd->rowCount() > 0) {
                $success = "Booking #$booking_id has been Cancelled.";
            }
        }
    }
}

// 3. Fetch Dashboard Statistics (Bonus Feature)
$totalRooms      = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$pendingCount    = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Pending'")->fetchColumn();
$confirmedCount  = $pdo->query("SELECT COUNT(*) FROM bookings WHERE booking_status = 'Confirmed'")->fetchColumn();
$totalRevenue    = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM bookings WHERE booking_status = 'Confirmed'")->fetchColumn();

// 4. Fetch All Rooms & Check if Editing a Room
$rooms = $pdo->query("SELECT * FROM rooms ORDER BY room_id ASC")->fetchAll();
$editRoom = null;
if (isset($_GET['edit_room'])) {
    $eStmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ?");
    $eStmt->execute([(int)$_GET['edit_room']]);
    $editRoom = $eStmt->fetch();
}

// 5. Fetch All Bookings with Guest & Room Info
$bookings = $pdo->query("
    SELECT b.*, u.name AS guest_name, u.contact_number, r.room_name 
    FROM bookings b
    JOIN users u ON b.user_id = u.user_id
    JOIN rooms r ON b.room_id = r.room_id
    ORDER BY b.booking_date DESC
")->fetchAll();
?>

<h2 class="mb-4">Administrator Dashboard</h2>

<?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Bonus Feature: Booking Statistics Cards -->
<div class="row g-3 mb-5">
    <div class="col-md-3">
        <div class="card text-white bg-primary shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Total Rooms</h6>
                <h2 class="mb-0"><?= $totalRooms ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-dark bg-warning shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Pending Bookings</h6>
                <h2 class="mb-0"><?= $pendingCount ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Confirmed Bookings</h6>
                <h2 class="mb-0"><?= $confirmedCount ?></h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-dark shadow-sm">
            <div class="card-body">
                <h6 class="card-title text-uppercase">Confirmed Revenue</h6>
                <h4 class="mb-0 mt-2">LKR <?= number_format($totalRevenue, 2) ?></h4>
            </div>
        </div>
    </div>
</div>

<!-- Section 1: Manage Guest Bookings -->
<h4 class="mb-3">Manage Reservations</h4>
<div class="table-responsive shadow-sm bg-white rounded mb-5">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Guest</th>
                <th>Room</th>
                <th>Dates</th>
                <th>Total (LKR)</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($bookings)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">No bookings found.</td></tr>
            <?php else: ?>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b['booking_id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($b['guest_name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($b['contact_number']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['room_name']) ?></td>
                        <td><?= htmlspecialchars($b['check_in']) ?> to <?= htmlspecialchars($b['check_out']) ?></td>
                        <td class="fw-semibold"><?= number_format($b['total_amount'], 2) ?></td>
                        <td>
                            <?php
                                $badge = 'bg-warning text-dark';
                                if ($b['booking_status'] === 'Confirmed') $badge = 'bg-success';
                                if ($b['booking_status'] === 'Cancelled') $badge = 'bg-danger';
                            ?>
                            <span class="badge <?= $badge ?>"><?= htmlspecialchars($b['booking_status']) ?></span>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <?php if ($b['booking_status'] === 'Pending'): ?>
                                    <form method="POST">
                                        <input type="hidden" name="action" value="update_booking">
                                        <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                        <input type="hidden" name="new_status" value="Confirmed">
                                        <button type="submit" class="btn btn-success btn-sm">Confirm</button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($b['booking_status'] !== 'Cancelled'): ?>
                                    <form method="POST" onsubmit="return confirm('Cancel this booking?');">
                                        <input type="hidden" name="action" value="update_booking">
                                        <input type="hidden" name="booking_id" value="<?= $b['booking_id'] ?>">
                                        <input type="hidden" name="new_status" value="Cancelled">
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">No actions</span>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Section 2: Add or Edit Room Form -->
<h4 class="mb-3"><?= $editRoom ? "Edit Room #" . $editRoom['room_id'] : "Add New Room" ?></h4>
<div class="card shadow-sm mb-5 bg-light">
    <div class="card-body">
        <form method="POST" class="row g-3">
            <input type="hidden" name="action" value="<?= $editRoom ? 'update_room' : 'add_room' ?>">
            <?php if ($editRoom): ?>
                <input type="hidden" name="room_id" value="<?= $editRoom['room_id'] ?>">
            <?php endif; ?>

            <div class="col-md-4">
                <label class="form-label fw-semibold">Room Name</label>
                <input type="text" name="room_name" class="form-control" value="<?= htmlspecialchars($editRoom['room_name'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Room Type</label>
                <select name="room_type" class="form-select" required>
                    <?php $curType = $editRoom['room_type'] ?? 'Standard'; ?>
                    <option value="Standard" <?= $curType === 'Standard' ? 'selected' : '' ?>>Standard</option>
                    <option value="Deluxe" <?= $curType === 'Deluxe' ? 'selected' : '' ?>>Deluxe</option>
                    <option value="Suite" <?= $curType === 'Suite' ? 'selected' : '' ?>>Suite</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Price / Night (LKR)</label>
                <input type="number" step="0.01" name="price_per_night" class="form-control" value="<?= htmlspecialchars($editRoom['price_per_night'] ?? '') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Max Guests</label>
                <input type="number" name="max_guests" class="form-control" min="1" max="10" value="<?= htmlspecialchars($editRoom['max_guests'] ?? '2') ?>" required>
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold">Image Path</label>
                <input type="text" name="image_url" class="form-control" value="<?= htmlspecialchars($editRoom['image_url'] ?? 'images/room1.jpg') ?>" required>
            </div>

            <div class="col-md-<?= $editRoom ? '9' : '12' ?>">
                <label class="form-label fw-semibold">Description & Facilities</label>
                <input type="text" name="description" class="form-control" value="<?= htmlspecialchars($editRoom['description'] ?? '') ?>" required>
            </div>

            <?php if ($editRoom): ?>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Room Status</label>
                    <select name="room_status" class="form-select">
                        <option value="available" <?= $editRoom['room_status'] === 'available' ? 'selected' : '' ?>>Available (Active)</option>
                        <option value="unavailable" <?= $editRoom['room_status'] === 'unavailable' ? 'selected' : '' ?>>Unavailable (Hidden)</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <button type="submit" class="btn btn-primary"><?= $editRoom ? 'Update Room' : 'Add Room' ?></button>
                <?php if ($editRoom): ?>
                    <a href="admin.php" class="btn btn-secondary">Cancel Edit</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Section 3: View & Manage All Rooms -->
<h4 class="mb-3">All Villa Rooms</h4>
<div class="table-responsive shadow-sm bg-white rounded">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Room Name</th>
                <th>Type</th>
                <th>Price / Night</th>
                <th>Max Guests</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rooms as $r): ?>
                <tr>
                    <td>#<?= $r['room_id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['room_name']) ?></strong></td>
                    <td><?= htmlspecialchars($r['room_type']) ?></td>
                    <td>LKR <?= number_format($r['price_per_night'], 2) ?></td>
                    <td><?= (int)$r['max_guests'] ?></td>
                    <td>
                        <span class="badge <?= $r['room_status'] === 'available' ? 'bg-success' : 'bg-secondary' ?>">
                            <?= htmlspecialchars($r['room_status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="admin.php?edit_room=<?= $r['room_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                            <form method="POST" onsubmit="return confirm('Delete this room permanently?');">
                                <input type="hidden" name="action" value="delete_room">
                                <input type="hidden" name="room_id" value="<?= $r['room_id'] ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>