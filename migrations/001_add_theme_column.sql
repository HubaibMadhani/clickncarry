-- Run this SQL once to add a theme column to users table
ALTER TABLE users
ADD COLUMN theme ENUM('light','dark') DEFAULT 'light';
