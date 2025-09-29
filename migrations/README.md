This folder contains SQL migrations for the project.

How to run migrations on Windows (XAMPP):

1) Using PHP migration runner (recommended):
   - From project root run (PowerShell):
     # Use the full path to the PHP binary bundled with XAMPP
     "C:\\xampp\\php\\php.exe" .\migrate.php

2) Or run SQL directly via MySQL CLI:
   - Apply a single migration file (example):
     mysql -u root -p myshop < "d:\xampp\htdocs\trade zone\migrations\002_add_deleted_flag.sql"

Notes:
- The migration runner records applied files in the `migrations` table so SQL files are not re-applied.
- If `php` is not available on your PATH (common on Windows/XAMPP), use the full path to the XAMPP php.exe as shown above.
- If you prefer, run the SQL in phpMyAdmin by importing the `.sql` files.
