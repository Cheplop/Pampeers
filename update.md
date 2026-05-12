Database Changes
Updated the bookings table to use startDateTime and endDateTime.

Added the acceptedAges column to the sitters table.

Confirmed the bio column belongs to the users table.

Backend (PHP) Changes
Updated app/controllers/user/becomeSitter.php to fix the crash related to the bio.

Updated app/controllers/auth/login.php to make login work with both email and username.

Updated app/controllers/user/search.php to make the WHERE, WHEN, and WHO search logic work perfectly.

Updated app/controllers/booking/create.php to make Work Queue save the correct dates and times.

Updated updateProfile.php to save the sitter's selected age groups.

Frontend (HTML) Changes
Updated public/admin/adminDashboard.php to show correct user roles (Guardian / Sitter).

Updated public/guardian/guardianDashboard.php to fix the dropdown menu and search inputs.

Updated public/bookSitter.php to ask for the correct start and end dates/times.

What the Frontender Needs to Do Next

Update public/editProfile.php:

Add checkboxes for the sitter to pick who they want to babysit (Baby, Toddler, Child, Kid).

Crucial: The HTML name for these checkboxes MUST be exactly name="acceptedAges[]".

Update Sitter Profile Views (e.g., public/profile.php):

Fetch and display the acceptedAges on the sitter's profile so both the sitter and the guardians can see what age groups they accept.