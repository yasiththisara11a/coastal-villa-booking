<?php require_once 'includes/header.php'; ?>

<!-- 1. Enlarged Full-Width Hero Banner (Option 2 Style) -->
<section class="hero-full text-center text-lg-start">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <span class="badge rounded-pill px-3 py-2 mb-3" style="background-color: #d99b52;">MIRISSA • SOUTHERN COAST, SRI LANKA</span>
                <h1 class="display-4 fw-bold mb-3">Experience Coastal Luxury in Sri Lanka</h1>
                <p class="lead mb-4 text-light">Wake up to golden beaches, tropical palm gardens, and warm Sri Lankan hospitality just steps from Mirissa Bay.</p>
                <a href="rooms.php" class="btn btn-sand btn-pill btn-lg px-4 shadow">Explore Our Rooms</a>
            </div>
        </div>
    </div>
</section>

<!-- 2. Floating Quick Availability Search Bar -->
<div class="container mb-5">
    <div class="floating-search">
        <form action="rooms.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">CHECK-IN DATE</label>
                <input type="date" name="check_in" class="form-control rounded-pill" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">CHECK-OUT DATE</label>
                <input type="date" name="check_out" class="form-control rounded-pill" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small text-muted">GUESTS</label>
                <select name="guests" class="form-select rounded-pill">
                    <option value="1">1 Guest</option>
                    <option value="2" selected>2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-teal btn-pill w-100 py-2">Search Availability</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Our Facilities Section (Option 1 Style Cards) -->
<div class="container mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold" style="color: #0f5c63;">Our Villa Facilities</h2>
        <p class="text-muted">Everything you need for a relaxing oceanfront getaway</p>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card facility-card h-100 text-center p-4">
                <div class="card-body">
                    <div class="icon-badge">🏖️</div>
                    <h5 class="fw-bold">Beachfront Access</h5>
                    <p class="text-muted mb-0">Direct private pathway to Mirissa beach, just 30 seconds from your doorstep.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card facility-card h-100 text-center p-4">
                <div class="card-body">
                    <div class="icon-badge">🏊</div>
                    <h5 class="fw-bold">Ocean View & Dining</h5>
                    <p class="text-muted mb-0">Fresh daily seafood, authentic Sri Lankan breakfast, and sunset terrace dining.</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card facility-card h-100 text-center p-4">
                <div class="card-body">
                    <div class="icon-badge">📶</div>
                    <h5 class="fw-bold">Free Wi-Fi & A/C</h5>
                    <p class="text-muted mb-0">High-speed fiber internet, air conditioning, hot water, and airport transfers.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 4. Compact Location & Map Card (Option 2 Style) -->
<div class="container mb-5">
    <div class="location-card">
        <div class="row g-0 align-items-center">
            <div class="col-md-5">
                <iframe 
                    class="compact-map"
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15873.57564556226!2d80.4497416!3d5.948262!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3ae13fce3177dc59%3A0xa2d8a78b36dc9c90!2sMirissa!5e0!3m2!1sen!2slk!4v1700000000000" 
                    allowfullscreen="" 
                    loading="lazy">
                </iframe>
            </div>
            <div class="col-md-7 p-4 p-lg-5">
                <span class="badge rounded-pill mb-2" style="background-color: #e4f1f1; color: #0f5c63;">LOCATION & CONTACT</span>
                <h4 class="fw-bold mb-2">Mirissa Bay Coastal Villa</h4>
                <p class="text-muted mb-3">Beachside Road, Mirissa, Southern Province, Sri Lanka — 5 minutes from Mirissa Harbour & Parrot Rock.</p>
                <div class="d-flex flex-wrap gap-4 text-muted small mb-3">
                    <span>📞 +94 77 123 4567</span>
                    <span>✉️ stay@mirissabayvilla.lk</span>
                </div>
                <a href="https://maps.google.com/?q=Mirissa,Sri+Lanka" target="_blank" class="btn btn-teal btn-pill btn-sm">Open in Google Maps</a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>