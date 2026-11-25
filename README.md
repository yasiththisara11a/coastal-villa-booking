# Mirissa Bay Coastal Villa – Room Booking System (IT1208 CA2)

## 1. Project Overview
A dynamic, responsive web-based coastal villa and room booking application built for **Mirissa Bay Coastal Villa** in Sri Lanka. Tourists can explore the villa, check room availability by dates and guest count, filter rooms, calculate stay costs dynamically, and submit or cancel booking requests. Administrators can manage room inventory and confirm or cancel guest reservations while preventing double-bookings.

## 2. Implemented Features
### Core Features
* **Responsive Front-End:** Built with HTML5, CSS3, and Bootstrap 5 for desktop, tablet, and mobile screens.
* **User Authentication:** Guest registration and login using PHP sessions and secure password hashing (`password_hash()` / `password_verify()`).
* **Room Availability Search:** Checks requested dates against existing `Confirmed` reservations in MySQL.
* **Dynamic Booking Form:** Real-time JavaScript night and total price calculation, client-side date validation, and server-side price recalculation.
* **Guest Dashboard (`my_bookings.php`):** View booking statuses (`Pending`, `Confirmed`, `Cancelled`) and cancel pending requests.
* **Admin Dashboard (`admin.php`):** Full CRUD operations for rooms and booking approval/cancellation with overlap protection.

### Bonus Features Implemented
1. **Search & Filter Rooms:** Filter rooms by room type (`Standard`, `Deluxe`, `Suite`) and maximum price per night.
2. **Admin Booking Statistics:** Summary cards displaying Total Rooms, Pending Bookings, Confirmed Bookings, and Total Confirmed Revenue.
3. **Interactive Location Map:** Embedded Google Map showing the villa's coastal location in Mirissa.

## 3. Software Requirements
* **Local Server Environment:** XAMPP (Apache + MySQL + PHP 8.x)
* **Database:** MySQL / MariaDB
* **Web Browser:** Chrome, Edge, or Firefox (internet connection required for Bootstrap 5 CDN and Google Maps iframe)

## 4. Installation & Configuration Instructions
1. Clone or copy the `coastal_villa` project folder into `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** from the XAMPP Control Panel.
3. Open `http://localhost/phpmyadmin` and create a new database named `coastal_villa_db`.
4. Select `coastal_villa_db`, open the **Import** tab, choose `db_structure.sql` from the project root folder, and click **Import**.
5. Verify database credentials in `includes/db.php` (default XAMPP settings: host `localhost`, user `root`, password `""`).
6. Open `http://localhost/coastal_villa/index.php` in your browser.

## 5. Sample Login Credentials
* **Administrator Account:**
  * **Email:** `adminnew@villa.com`
  * **Password:** `admin123`
* **Guest Account:**
  * **Email:** `guestnew@villa.com`
  * **Password:** `guest123`

## 6. Acknowledgements & AI Declaration
* **CSS Framework:** Bootstrap 5.3.3 (CDN) was used for responsive grid layouts and UI components.
* **Generative AI Declaration:** Generative AI tools (Google Gemini) were used during development for debugging assistance, code structuring guidance, and documentation formatting. All system logic, database queries, and code implementations have been reviewed and understood by the author.