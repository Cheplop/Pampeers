# Pampeers - System Updates & Fixes Log

This document serves as a comprehensive log of the recent fixes, database updates, and feature implementations made to the Pampeers platform.

---

## 🗄️ 1. Database Schema Changes

Several key modifications were made to the `pampeers2` database to improve data integrity and support new features:

* **Bookings Table Overhaul:**
  * Added `startDateTime` (DATETIME) and `endDateTime` (DATETIME).
  * Removed the old `bookingDate`, `bookingTime`, and `duration` columns. This makes date-range querying much more reliable.
* **Sitters Table Enhancement:**
  * Added `acceptedAges` (`VARCHAR(255)`). This allows sitters to select multiple age groups they are willing to babysit.
* **Users Table Alignment:**
  * Confirmed that the `bio` column now correctly resides in the `users` table instead of the `sitters` table.

---

## ⚙️ 2. Backend Logic Updates (PHP)

* **Fix: Become a Sitter (`app/controllers/user/becomeSitter.php`)**
  * **Issue:** The page was crashing with an "Unknown column 'bio'" error.
  * **Fix:** Removed `bio` from the `INSERT INTO sitters` SQL query, aligning it with the new database schema.

* **Feature: Dual Login (`app/controllers/auth/login.php`)**
  * **Issue:** Users could only log in with their email.
  * **Fix:** Updated the SQL query to `WHERE emailAddress = ? OR username = ?`, allowing users to log in using either credential.

* **Feature: Smart Search Engine (`app/controllers/user/search.php`)**
  * **Issue:** Blank fields broke the search, and it didn't support checking dates or age preferences properly.
  * **Fix:** Rewrote the search logic to dynamically apply filters only if the user provided input:
    * **WHERE:** Uses `LIKE` to partially match the `cityMunicipality`.
    * **WHEN:** Uses `NOT IN` combined with a subquery on the `bookings` table to hide sitters who are already booked on the requested dates.
    * **WHO:** Uses `LIKE` on the new `acceptedAges` column to match strings like "Toddler" even if the sitter selected multiple options (e.g., "Baby,Toddler,Kid").

* **Update: Booking Creation (`app/controllers/booking/create.php`)**
  * **Fix:** Updated to capture `bookingDate`, `startTime`, `endDate`, and `endTime` from the HTML form. Used PHP's `strtotime` and `date()` to convert these into strict `YYYY-MM-DD HH:MM:SS` strings for the new database columns.

* **Update: Saving Profile Data (`updateProfile.php`)**
  * **Fix:** Added logic to receive the `acceptedAges[]` checkbox array from the frontend, convert it into a comma-separated string using `implode()`, and save it into the `sitters` table.

---

## 🎨 3. Frontend & UI Fixes (HTML/UI)

* **Fix: Admin Dashboard Roles (`public/admin/adminDashboard.php`)**
  * **Issue:** Admin table only showed "Guardian" for all users.
  * **Fix:** Wrote a `LEFT JOIN` SQL query with an `IF()` statement to dynamically output **"Guardian / Sitter"** if the user also has a profile in the `sitters` table.

* **Fix: Guardian Dashboard Navigation & Search (`public/guardian/guardianDashboard.php`)**
  * **Issue:** The profile dropdown menu went missing, and the search bar inputs didn't match the guest view.
  * **Fix:** Restored the Bootstrap dropdown HTML and ensured `bootstrap.bundle.min.js` was loaded. Swapped the plain text "Who" input with the proper `<select>` dropdown (Baby, Toddler, Child, Kid) to match the `guestDashboard`.

* **Update: Booking Form (`bookSitter.php`)**
  * **Fix:** Updated the HTML `<form>` to ask the guardian for Start Date, Start Time, End Date, and End Time, ensuring all data necessary for the backend is properly collected.

---

## 🚀 Next Steps for Frontend Team
1. Ensure the Sitter Edit Profile page (`editProfile.php`) includes checkboxes for the accepted ages, specifically named `name="acceptedAges[]"`.
2. Update the public view of the Sitter Profile so guardians can see the age groups the sitter has chosen.
