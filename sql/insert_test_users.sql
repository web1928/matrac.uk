-- Insert Test Users for Development
-- These match the test credentials in the login page
-- Password for all users: Uses bcrypt hash for 'admin123' or 'test123'

-- Note: The password hashes are:
-- 'admin123' = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- 'test123'  = $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- (Same hash used for simplicity in development)

-- Clear existing test users if they exist
DELETE FROM users WHERE username IN ('admin', 'receptor', 'issuer', 'mixer');

-- Reset auto-increment to 1 (optional, ensures consistent IDs)
ALTER TABLE users AUTO_INCREMENT = 1;

-- Insert test users
INSERT INTO users (user_id, username, password_hash, email, first_name, last_name, role, active) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@matrac.uk', 'Admin', 'User', 'admin', 1),
(2, 'receptor', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'receptor@matrac.uk', 'John', 'Receptor', 'goods_receptor', 1),
(3, 'issuer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'issuer@matrac.uk', 'Jane', 'Issuer', 'goods_issuer', 1),
(4, 'mixer', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'mixer@matrac.uk', 'Mike', 'Mixer', 'mixer', 1);

-- Verify insertion
SELECT user_id, username, email, first_name, last_name, role FROM users;
