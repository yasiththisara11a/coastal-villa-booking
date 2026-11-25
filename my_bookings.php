<?php
require_once 'includes/header.php';

// Ensure user is logged in as a guest
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'guest') {
    header("Location: login.php");
    exit;
}

$message = '';

// Handle Cancellation Request (Only if booking belongs to logged-in user AND is Pending)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking_id'])) {
    $booking_id = (int)$_POST['cancel_booking_id'];

    $cancelStmt = $pdo->prepare("
        UPDATE bookings 
        SET booking_status = 'Cancelled' 
        WHERE booking_id = ? AND user_id = ? AND booking_status = 'Pending'
    ");
    $cancelStmt->execute([$booking_id, $_SESSION['user_id']]);

    if ($cancelStmt->rowCount() > 0) {
        $message = "Booking #$booking_id has been cancelled.";
    }
}

// Fetch all bookings for the logged-in guest
$stmt = $pdo->prepare("
    SELECT b.*, r.room_name, r.room_type 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.room_id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC
");
$stmt->execute([$_SESSION['user_id']]);
$bookings = $stmt->fetchAll();
?>

<h2 class="mb-4">My Bookings</h2>

<?php if ($message): ?>
    <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if (empty($bookings)): ?>
    <div class="alert alert-secondary">
        You haven't made any bookings yet. <a href="rooms.php">Explore our rooms</a> to book your stay!
    </div>
<?php else: ?>
    <div class="table-responsive shadow-sm bg-white rounded">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-primary">
                <tr>
                    <th>Booking ID</th>
                    <th>Room</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                    <th>Total Cost</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?= $b['booking_id'] ?></td>
                        <td>
                            <strong><?= htmlspecialchars($b['room_name']) ?></strong><br>
                            <small class="text-muted"><?= htmlspecialchars($b['room_type']) ?></small>
                        </td>
                        <td><?= htmlspecialchars($b['check_in']) ?></td>
                        <td><?= htmlspecialchars($b['check_out']) ?></td>
                        <td class="fw-semibold">LKR <?= number_format($b['total_amount'], 2) ?></td>
                        <td>
                            <?php
                                $badge = 'bg-warning text-dark';
                                if ($b['booking_status'] === 'Confirmed') $badge = 'bg-success';
                                if ($b['booking_status'] === 'Cancelled') $badge = 'bg-danger';
                            ?>
                            <span class="badge <?= $badge ?>"><?= htmlspecialchars($b['booking_status']) ?></span>
                        </td>
                        <td>
                            <?php if ($b['booking_status'] === 'Pending'): ?>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to cancel this booking?');">
                                    <input type="hidden" name="cancel_booking_id" value="<?= $b['booking_id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm">Cancel</button>
                                </form>
                            <?php else: ?>
                                <span class="text-muted small">No actions</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>