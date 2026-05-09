# Pampeers Babysitting Booking Platform

## System Overview

Pampeers is a web-based babysitting booking platform built with PHP and MySQL. The system is designed to connect guardians (parents) with qualified sitters, providing a secure and efficient way to manage bookings, profiles, and reviews. The platform supports three main user roles:

### Guardian
- Can search for sitters based on location, availability, and age group requirements
- Can book, review, and manage favourite sitters
- Has access to a personalized dashboard to manage bookings and profile information

### Sitter
- Can set availability, update professional information, and manage bookings
- Receives booking requests from guardians and can accept or decline them
- Has a dashboard to view upcoming jobs and manage their profile
- Can select which age groups they are willing to babysit

### Admin
- Manages user accounts, including activating, deactivating, or deleting users and sitters
- Verifies sitter credentials and oversees platform activity
- Accesses an admin dashboard for system-wide management
- Can view user roles and manage platform activity

## Database Schema

### Users Table
- Stores basic information for all users (guardians and sitters)
- Fields: userID, username, emailAddress, password, phoneNumber, role (Guardian/Sitter)
- Also stores bio information for all users

### Sitters Table
- Stores professional information specific to sitters
- Fields: sitterID, userID (foreign key), bio, experience, certifications, acceptedAges
- One-to-one relationship with Users table (not all users are sitters)
- **acceptedAges:** Comma-separated list of age groups (Baby, Toddler, Child, Kid)

### Bookings Table
- Manages booking records linking guardians and sitters
- Fields: bookingID, guardianID, sitterID, startDateTime, endDateTime, status, createdAt
- Uses **startDateTime** and **endDateTime** (DATETIME format) for precise date/time range management
- Replaces old bookingDate, bookingTime, duration columns for better reliability

## Search Mechanism (WHERE, WHEN, WHO)

The search functionality allows guardians to find suitable sitters using three dynamic filters:

### WHERE - Location Filter
- Filters sitters based on cityMunicipality using partial LIKE matching
- Only applied if user provides a location input

### WHEN - Date Availability Filter
- Checks sitter availability by ensuring no conflicting bookings in the requested date range
- Uses NOT IN with a subquery on the bookings table
- Compares against startDateTime and endDateTime for precise availability checking

### WHO - Age Groups Filter
- Filters sitters based on the acceptedAges they are willing to care for
- Supports partial matching on comma-separated age group strings
- Age group options: Baby, Toddler, Child, Kid

## Setup Instructions

1. **Clone the Repository:**
  ```bash
  git clone <repository-url>
  cd Pampeers
  ```

2. **Database Setup:**
  - Import the database schema and sample data from `sql/pampeers2.sql` into your MySQL server
  - Update `app/config/config.php` with your database credentials
  - Ensure the following tables exist: users, sitters, bookings, reviews, favourites

3. **Web Server:**
  - Place the project files in your web server's root directory (e.g., `htdocs` for XAMPP or `www` for WAMP)
  - Ensure PHP and MySQL are running

4. **File Permissions:**
  - Make sure the `app/uploads/profiles/` directory is writable for profile image uploads

5. **Access the Application:**
  - Open your browser and navigate to the appropriate URL (e.g., `http://localhost/Pampeers/public/`)

---

## Recent Updates & Improvements (Current Branch: improvedProfile)

### ✅ Database Schema Enhancements
- **Bookings Table:** Migrated from `bookingDate`, `bookingTime`, `duration` to `startDateTime` and `endDateTime` for more precise date/time management
- **Sitters Table:** Added `acceptedAges` column to store comma-separated age groups (Baby, Toddler, Child, Kid)
- **Users Table:** Confirmed `bio` column for storing user biography information

### ✅ Backend Logic Updates

#### Authentication (`app/controllers/auth/login.php`)
- **Dual Login Support:** Users can now login using either email OR username
- Updated SQL query to check both credentials

#### User Management (`app/controllers/user/becomeSitter.php`)
- **Fixed:** Resolved "Unknown column 'bio'" error
- Now correctly inserts sitter profile without bio field (bio stays in users table)

#### Smart Search Engine (`app/controllers/user/search.php`)
- **Dynamic Filter Application:** Blank fields no longer break search functionality
- **WHERE Filter:** Uses LIKE matching on cityMunicipality with partial string support
- **WHEN Filter:** Uses subquery on bookings table with NOT IN clause to check availability
- **WHO Filter:** Supports comma-separated acceptedAges with partial string matching

#### Booking Creation (`app/controllers/booking/create.php`)
- **Updated:** Now captures startDate, startTime, endDate, endTime from form
- **Conversion:** Uses PHP strtotime() and date() functions to convert to YYYY-MM-DD HH:MM:SS format
- **Database:** Properly saves to startDateTime and endDateTime columns

#### Profile Updates (`app/controllers/user/updateProfile.php`)
- **Age Groups:** Receives acceptedAges[] checkbox array from frontend
- **Storage:** Converts to comma-separated string using implode() before saving to database

### ✅ Frontend Improvements

#### Admin Dashboard (`public/admin/adminDashboard.php`)
- **Fixed:** Admin table now correctly displays user roles (Guardian / Sitter)
- Uses LEFT JOIN with IF() statement for dynamic role display

#### Guardian Dashboard (`public/guardian/guardianDashboard.php`)
- **Fixed:** Restored profile dropdown menu navigation
- **Updated:** Search inputs now match guest view layout
- **Ensured:** bootstrap.bundle.min.js is properly loaded

#### Book Sitter Form (`public/guardian/bookSitter.php`)
- **Updated:** Form now captures Start Date, Start Time, End Date, End Time
- Properly collects all datetime data required by backend

---

## 📋 Project Structure

### `app/` - Backend Application Logic
```
config/
  └── config.php              # Database connection & configuration
  
controllers/
  ├── admin/                  # Admin user management
  ├── auth/                   # Authentication (login/logout/register)
  ├── booking/                # Booking operations (create, fetch, update)
  ├── review/                 # Review submission
  ├── sitter/                 # Sitter-specific operations
  └── user/                   # User management, search, profile
  
helpers/
  └── sitter.php              # Sitter utility functions
  
middleware/
  └── auth.php                # Authentication middleware

uploads/
  └── profiles/               # User profile picture storage
```

### `public/` - Frontend & Entry Points
```
admin/
  └── adminDashboard.php      # Admin management interface
  
guardian/
  ├── bookSitter.php          # Booking form for guardians
  ├── guardianDashboard.php   # Guardian home dashboard
  ├── myBookings.php          # Guardian's booking list
  ├── myFavourites.php        # Saved favorite sitters
  └── viewSitterProfile.php   # Sitter profile view
  
sitter/
  └── sitterDashboard.php     # Sitter home dashboard
  
css/
  ├── dashboard.css
  ├── adminDashboard.css
  ├── guardianDashboard.css
  ├── sitterDashboard.css
  └── [other styling files]

editProfile.php             # Profile editing interface
guestDashboard.php          # Guest/public home page
profile.php                 # User profile display
register.php                # User registration
```

### `sql/` - Database Files
```
└── pampeers2.sql           # Database schema and sample data
```

### `storage/` - File Storage
Reserved for application data storage

---

## 🚀 Current Status & Next Steps

### ✅ Completed
- Core platform functionality (search, booking, profiles)
- Database schema optimization
- Dual login system
- Smart search with WHERE/WHEN/WHO filters
- Admin role management
- Age group filtering for sitters

### 🔄 In Progress / Pending Frontend
1. **Sitter Edit Profile Page** (`public/editProfile.php`)
   - Add checkboxes for accepted age groups (Baby, Toddler, Child, Kid)
   - **CRITICAL:** Input names must be exactly `name="acceptedAges[]"`
   - Allow sitters to update their profile with age preferences

2. **Sitter Profile Display** (`public/profile.php`)
   - Fetch and display acceptedAges on sitter's profile
   - Show age groups to both sitters and guardians
   - Improve profile visibility for search results

### 📝 Technical Notes
- All dates/times use MySQL DATETIME format (YYYY-MM-DD HH:MM:SS)
- Age groups are stored as comma-separated strings in the database
- Search filters are applied dynamically based on user input
- Profile uploads are stored in `app/uploads/profiles/`

---

## Project Configuration

For additional information about specific controllers and their functionality, refer to the inline code documentation in each PHP file. The codebase follows a Model-View-Controller (MVC) pattern with clear separation of concerns.

### Key Configuration Files
- `app/config/config.php` - Database credentials and connection settings
- `.htaccess` (if present) - URL rewriting and access control rules

---

## Contributing & Development

- **Branch:** Currently working on the `improvedProfile` branch for UI/UX enhancements
- **Testing:** Ensure all search filters work correctly with various input combinations
- **Code Style:** Follow existing PHP and HTML formatting conventions
- **Database Queries:** Always use prepared statements to prevent SQL injection

---

## Troubleshooting

### Common Issues

1. **Profile Uploads Not Working**
   - Ensure `app/uploads/profiles/` directory has write permissions (chmod 755)
   - Check that PHP file upload settings allow sufficient file size

2. **Search Not Finding Sitters**
   - Verify sitters have `acceptedAges` populated in database
   - Check date ranges don't conflict with existing bookings
   - Ensure location field matches search input

3. **Login Issues**
   - Try logging in with both username and email
   - Verify database credentials in `app/config/config.php`
   - Check that user role is correctly set (Guardian/Sitter)
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