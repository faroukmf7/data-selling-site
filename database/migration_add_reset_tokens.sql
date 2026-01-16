-- Add password reset token columns to users table
ALTER TABLE users ADD COLUMN reset_token VARCHAR(255) NULL AFTER password_hash;
ALTER TABLE users ADD COLUMN reset_token_expires DATETIME NULL AFTER reset_token;

-- Create index for faster lookups
CREATE INDEX idx_reset_token ON users(reset_token);
CREATE INDEX idx_reset_token_expires ON users(reset_token_expires);
