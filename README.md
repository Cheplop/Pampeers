# Pampeers Babysitting Booking Platform

## System Overview

Pampeers is a web-based babysitting booking platform built with PHP and MySQL. The system is designed to connect guardians (parents) with qualified sitters, providing a secure and efficient way to manage bookings, profiles, and reviews. The platform supports three main user roles:

### Guardian
- Can search for sitters based on location, availability, and age group requirements.
- Can book, review, and manage favourite sitters.
- Has access to a personalized dashboard to manage bookings and profile information.

### Sitter
- Can set availability, update professional information, and manage bookings.
- Receives booking requests from guardians and can accept or decline them.
- Has a dashboard to view upcoming jobs and manage their profile.

### Admin
- Manages user accounts, including activating, deactivating, or deleting users and sitters.
- Verifies sitter credentials and oversees platform activity.
- Accesses an admin dashboard for system-wide management.

## Database Logic

- **Users Table:** Stores basic information for all users (guardians and sitters), such as name, email, password, and role.
- **Sitters Table:** Stores professional information specific to sitters (e.g., bio, experience, certifications). Each sitter is linked to a user via a one-to-one relationship (one user can be one sitter, but not all users are sitters).
- **Bookings Table:** Manages booking records, linking guardians and sitters. Each booking includes `startDateTime` and `endDateTime` fields to specify the booking period.

## Search Mechanism

The search functionality allows guardians to find suitable sitters using three main filters:

- **Location (WHERE):** Filters sitters based on the location field in the sitters or users table using SQL `WHERE` clauses.
- **Date Availability (WHEN):** Checks sitter availability by ensuring there are no conflicting bookings in the specified date range (`startDateTime` and `endDateTime`).
- **Age Groups (WHO):** Filters sitters based on the age groups they are willing to care for, typically stored as a field in the sitters table.

## Setup Instructions

1. **Clone the Repository:**
  ```bash
  git clone <repository-url>
  ```
2. **Database Setup:**
  - Import the database schema and sample data from `sql/pampeers2.sql` into your MySQL server.
  - Update `app/config/config.php` with your database credentials.
3. **Web Server:**
  - Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP or `www` for WAMP).
  - Ensure PHP and MySQL are running.
4. **File Permissions:**
  - Make sure the `app/uploads/profiles/` directory is writable for profile image uploads.
5. **Access the Application:**
  - Open your browser and navigate to the appropriate URL (e.g., `http://localhost/Pampeers/public/`).

---

For further details, see the in-code documentation and comments provided in each PHP file.
- `send.php` - Sends messages between users
- `sendSupport.php` - Sends messages to admin support

#### Payment Module (`payment/`)
- `create.php` - Creates payment records for bookings
- `fetchByBooking.php` - Retrieves payment info for a specific booking
- `updateStatus.php` - Updates payment status (pending → paid/failed)

#### User Management (`user/`)
- `updateProfile.php` - Updates user profile information
- `becomeSitter.php` - Converts regular user to pet sitter
- `fetchDashboard.php` - Gets user dashboard data
- `deactivateAccount.php` - Deactivates user account

#### Sitter Management (`sitter/`)
- `updateProfile.php` - Updates sitter-specific profile data
- `toggleAvailability.php` - Sitter sets availability status
- `fetchDashboard.php` - Gets sitter dashboard data

#### Admin Management (`admin/`)
- `deleteUser.php` - Removes user from system
- `deactivateUser.php` - Deactivates user account
- `reactivateUser.php` - Reactivates deactivated user

### `uploads/`
- Directory reserved for storing user-uploaded files (profile pictures, documents, etc.)
- Currently shown as empty; would grow as users upload content

## `public/` - Frontend & Entry Points
Contains presentation layer and user-facing pages.

- `.htaccess` - Apache configuration for URL routing and access rules

### `admin/` - Admin dashboard interface
- Currently empty; likely contains admin management pages for user control, booking oversight, etc.

### `user/` - User portal interface  
- Currently empty; likely contains pages like:
  - Dashboard (user overview)
  - Booking interface
  - Messaging/inbox UI
  - Profile management
  - Payment history

## `sql/` - Database
- `Pampeers.sql` - Complete database schema (SQL dump)
  - Defines all tables for users, bookings, payments, messages, etc.
  - Used for initial database setup and restoration

## `storage/` - File Storage
- Currently empty directory
- Intended for session storage, cache files, or other runtime data
- Separates temporary/runtime data from application code

## Application Flow Summary

1. **User starts** → Accesses `public/user/` pages (HTML/forms)
2. **Form submission** → Sends POST/GET request to appropriate controller in `app/controllers/`
3. **Authentication check** → Middleware verifies user session and role
4. **Business logic** → Controller queries database via `$conn` from config
5. **Response** → Redirects or returns data to frontend interface

## Role-Based User Types
- **User** - Can book sitters, make payments, message sitters
- **Sitter** - Can set availability, receive bookings, message users
- **Admin** - Can manage users, deactivate accounts, oversee system

This is a typical **three-tier architecture** (presentation → application → data) built with vanilla PHP, MySQLi, and sessions for a pet-sitting marketplace platform.</content>
<parameter name="filePath">c:\xampp\htdocs\samplePampeers\README.md