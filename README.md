# Pampeers PHP Web Application - Architecture Overview

Based on the workspace structure, here's a comprehensive breakdown of the application:

## Root Directory Structure
The application follows a standard backend-organized PHP architecture with four main directories:

## `app/` - Backend Application Logic
The core of the application containing all server-side logic.

### `config/`
- `config.php` - Central configuration file that:
  - Sets timezone to Asia/Manila
  - Initializes PHP sessions
  - Contains database connection credentials (MySQLi connection to 'pampeers' database)
  - Defines constants and establishes the primary database connection `$conn`

### `middleware/`
- `auth.php` - Authentication and authorization middleware providing:
  - `requireAuth()` - Verifies user is logged in (checks `$_SESSION['user_id']`), redirects to login if not
  - `requireRole($role)` - Enforces role-based access control (admin, user, sitter roles)
  - Used to protect restricted endpoints throughout the application

### `controllers/` - Feature-Based Business Logic
Organized into functional modules, each handling specific domain logic:

#### Authentication Module (`auth/`)
- `login.php` - Handles user login (validates credentials, sets sessions)
- `logout.php` - Clears user sessions
- `register.php` - New user registration

#### Booking Module (`booking/`)
- `create.php` - Creates new pet sitting bookings with UUID generation
- `fetchUserBookings.php` - Retrieves bookings for a user
- `fetchSitterBookings.php` - Retrieves bookings for a pet sitter
- `updateStatus.php` - Updates booking status (e.g., pending → accepted/cancelled)

#### Messaging Module (`message/`)
- `fetchInbox.php` - Retrieves user conversations
- `fetchConversation.php` - Loads message history between two users
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
- `pampeers.sql` - Complete database schema (SQL dump)
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