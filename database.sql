-- ============================================================
-- Bardiya Eco Friendly - Complete Database Schema
-- Homestay Booking Backend API
-- 
-- Database Engine : MySQL 5.7+ / MariaDB 10.3+
-- Character Set   : utf8mb4 (full Unicode support)
-- Collation       : utf8mb4_unicode_ci
--
-- Usage:
--   mysql -u root -p < database.sql
--   OR import via phpMyAdmin (XAMPP → http://localhost/phpmyadmin)
-- ============================================================

-- ============================================================
-- 1. CREATE DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `bardiya_eco_friendly`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `bardiya_eco_friendly`;

-- ============================================================
-- 2. USERS TABLE (Admin Authentication / JWT Auth)
-- ============================================================
-- Stores admin users who can log in via /api/auth/login
-- Passwords are hashed with PHP's password_hash (bcrypt)
-- Roles: 'admin' (default), extensible for future roles
-- ============================================================
DROP TABLE IF EXISTS `bookings`;
DROP TABLE IF EXISTS `homestays`;
DROP TABLE IF EXISTS `pages`;
DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
    `id`         INT           AUTO_INCREMENT PRIMARY KEY,
    `username`   VARCHAR(100)  NOT NULL,
    `password`   VARCHAR(255)  NOT NULL COMMENT 'Bcrypt hash via password_hash()',
    `role`       VARCHAR(50)   NOT NULL DEFAULT 'admin',
    `created_at` DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Constraints
    UNIQUE KEY `uk_users_username` (`username`),

    -- Indexes
    INDEX `idx_users_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Admin users for JWT authentication';

-- ============================================================
-- 3. HOMESTAYS TABLE (Property Listings)
-- ============================================================
-- Stores eco-friendly homestay properties in Bardiya
-- Referenced by bookings table (parent table)
-- is_available: 1 = open for booking, 0 = temporarily closed
-- ============================================================
CREATE TABLE `homestays` (
    `id`              INT            AUTO_INCREMENT PRIMARY KEY,
    `name`            VARCHAR(255)   NOT NULL,
    `description`     TEXT           DEFAULT NULL,
    `location`        VARCHAR(255)   NOT NULL,
    `price_per_night` DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `max_guests`      INT            NOT NULL DEFAULT 1,
    `image_url`       VARCHAR(500)   DEFAULT NULL,
    `is_available`    TINYINT(1)     NOT NULL DEFAULT 1 COMMENT '1=available, 0=unavailable',
    `created_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Indexes for common queries
    INDEX `idx_homestays_location` (`location`),
    INDEX `idx_homestays_available` (`is_available`),
    INDEX `idx_homestays_price` (`price_per_night`),
    INDEX `idx_homestays_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Eco-friendly homestay property listings';

-- ============================================================
-- 4. BOOKINGS TABLE (Guest Reservations)
-- ============================================================
-- Stores booking/reservation records for homestays
-- FK: homestay_id → homestays.id (CASCADE on delete)
-- Status values: 'confirmed', 'pending', 'cancelled', 'completed'
-- Cancel is soft-delete (status → 'cancelled')
-- ============================================================
CREATE TABLE `bookings` (
    `id`           INT            AUTO_INCREMENT PRIMARY KEY,
    `homestay_id`  INT            NOT NULL,
    `guest_name`   VARCHAR(255)   NOT NULL,
    `guest_email`  VARCHAR(255)   DEFAULT NULL,
    `guest_phone`  VARCHAR(50)    DEFAULT NULL,
    `check_in`     DATE           NOT NULL,
    `check_out`    DATE           NOT NULL,
    `guests_count` INT            NOT NULL DEFAULT 1,
    `total_price`  DECIMAL(10,2)  NOT NULL DEFAULT 0.00,
    `status`       VARCHAR(50)    NOT NULL DEFAULT 'confirmed' COMMENT 'confirmed|pending|cancelled|completed',
    `created_at`   DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- Foreign Key
    CONSTRAINT `fk_bookings_homestay`
        FOREIGN KEY (`homestay_id`) REFERENCES `homestays`(`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    -- Indexes for common queries
    INDEX `idx_bookings_homestay` (`homestay_id`),
    INDEX `idx_bookings_status` (`status`),
    INDEX `idx_bookings_checkin` (`check_in`),
    INDEX `idx_bookings_checkout` (`check_out`),
    INDEX `idx_bookings_created` (`created_at`),
    INDEX `idx_bookings_guest_email` (`guest_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Guest booking/reservation records';

-- ============================================================
-- 5. PAGES TABLE (CMS Content)
-- ============================================================
-- Stores static/dynamic CMS pages (About, Contact, etc.)
-- Slug must be unique for clean URL routing
-- Status values: 'draft', 'published', 'archived'
-- ============================================================
CREATE TABLE `pages` (
    `id`         INT            AUTO_INCREMENT PRIMARY KEY,
    `title`      VARCHAR(255)   NOT NULL,
    `slug`       VARCHAR(255)   NOT NULL,
    `content`    LONGTEXT       DEFAULT NULL,
    `status`     VARCHAR(50)    NOT NULL DEFAULT 'draft' COMMENT 'draft|published|archived',
    `created_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Constraints
    UNIQUE KEY `uk_pages_slug` (`slug`),

    -- Indexes
    INDEX `idx_pages_status` (`status`),
    INDEX `idx_pages_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='CMS content pages';


-- ============================================================
-- 6. DEFAULT ADMIN USER
-- ============================================================
-- Username : admin
-- Password : admin123
-- Hash generated via: php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"
--
-- ⚠️  IMPORTANT: Change this password in production!
-- Generate a new hash:
--   php -r "echo password_hash('your_secure_password', PASSWORD_BCRYPT);"
-- ============================================================
INSERT INTO `users` (`username`, `password`, `role`) VALUES
('admin', '$2y$10$s0UIe3lcvkkNMnFSGtNiXehLa98Db3lYQxjtsnxENLu5xMCuRxilq', 'admin');
-- NOTE: The hash above is for password "admin123"
-- If it doesn't work, regenerate: php -r "echo password_hash('admin123', PASSWORD_BCRYPT);"


-- ============================================================
-- 7. SAMPLE SEED DATA (for development/testing)
-- ============================================================
-- Remove or comment out this section before deploying to production

-- Sample Homestays
INSERT INTO `homestays` (`name`, `description`, `location`, `price_per_night`, `max_guests`, `image_url`, `is_available`) VALUES
('Bardiya Jungle Lodge', 'An eco-friendly lodge nestled at the edge of Bardiya National Park. Wake up to the sounds of exotic birds and enjoy guided jungle walks.', 'Thakurdwara, Bardiya', 2500.00, 4, NULL, 1),
('Tharu Heritage Homestay', 'Experience authentic Tharu culture in a traditional mud-walled home. Includes organic meals and cultural performances.', 'Gulariya, Bardiya', 1500.00, 3, NULL, 1),
('Riverside Eco Retreat', 'A serene retreat on the banks of the Karnali River. Perfect for bird watching, fishing, and kayaking.', 'Rajapur, Bardiya', 3000.00, 6, NULL, 1),
('Green Village Homestay', 'Community-run homestay promoting sustainable tourism. Solar powered with organic farm-to-table dining.', 'Baniyabhar, Bardiya', 1200.00, 2, NULL, 1),
('Wildlife Safari Camp', 'Glamping-style eco camp with guided safaris into Bardiya National Park. Spot Bengal tigers and one-horned rhinos.', 'Thakurdwara, Bardiya', 4500.00, 5, NULL, 1);

-- Sample Bookings
INSERT INTO `bookings` (`homestay_id`, `guest_name`, `guest_email`, `guest_phone`, `check_in`, `check_out`, `guests_count`, `total_price`, `status`) VALUES
(1, 'Ram Sharma',      'ram.sharma@example.com',    '+977-9801234567', '2026-03-01', '2026-03-03', 2, 5000.00,  'confirmed'),
(2, 'Sita Thapa',      'sita.thapa@example.com',    '+977-9812345678', '2026-03-05', '2026-03-07', 3, 3000.00,  'confirmed'),
(3, 'John Smith',      'john.smith@example.com',    '+1-5551234567',   '2026-03-10', '2026-03-14', 4, 12000.00, 'confirmed'),
(1, 'Gita Poudel',     'gita.poudel@example.com',   '+977-9845678901', '2026-02-20', '2026-02-22', 1, 5000.00,  'cancelled'),
(4, 'Hari Bahadur KC', 'hari.kc@example.com',       '+977-9867890123', '2026-04-01', '2026-04-03', 2, 2400.00,  'pending');

-- Sample CMS Pages
INSERT INTO `pages` (`title`, `slug`, `content`, `status`) VALUES
('About Us', 'about-us', '<h2>Welcome to Bardiya Eco Friendly</h2>\n<p>We are a community-driven initiative promoting sustainable and eco-friendly tourism in the beautiful Bardiya district of Nepal. Our mission is to connect travelers with authentic local experiences while preserving the natural beauty and cultural heritage of the region.</p>\n<p>All our homestays follow strict eco-friendly guidelines including solar energy usage, organic farming, waste management, and support for local communities.</p>', 'published'),

('Contact Us', 'contact-us', '<h2>Get in Touch</h2>\n<p><strong>Email:</strong> info@bardiyaecofriendly.com</p>\n<p><strong>Phone:</strong> +977-084-123456</p>\n<p><strong>Address:</strong> Thakurdwara, Bardiya, Nepal</p>\n<p><strong>Office Hours:</strong> Sunday - Friday, 9:00 AM - 5:00 PM (NPT)</p>', 'published'),

('Terms & Conditions', 'terms-and-conditions', '<h2>Terms and Conditions</h2>\n<p>By using our services, you agree to the following terms:</p>\n<ul>\n<li>Bookings must be made at least 24 hours in advance.</li>\n<li>Cancellations made 48+ hours before check-in receive a full refund.</li>\n<li>Guests must respect the local environment and community guidelines.</li>\n<li>Maximum guest limits must be adhered to for safety purposes.</li>\n</ul>', 'published'),

('Privacy Policy', 'privacy-policy', '<h2>Privacy Policy</h2>\n<p>Your privacy is important to us. We collect only the minimum information necessary to process your booking and improve our services. We do not share your personal data with third parties without your consent.</p>', 'draft');


-- ============================================================
-- 8. PACKAGE CATEGORIES TABLE (tabs: Homestay, Safari)
-- ============================================================
DROP TABLE IF EXISTS `comparison_values`;
DROP TABLE IF EXISTS `comparison_features`;
DROP TABLE IF EXISTS `package_features`;
DROP TABLE IF EXISTS `packages`;
DROP TABLE IF EXISTS `package_categories`;

CREATE TABLE `package_categories` (
    `id`            BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100)   NOT NULL COMMENT 'e.g. Homestay, Safari',
    `slug`          VARCHAR(100)   NOT NULL COMMENT 'e.g. homestay, safari',
    `display_order` INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_pkgcat_slug` (`slug`),
    INDEX `idx_pkgcat_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Package category tabs (Homestay, Safari, etc.)';

-- ============================================================
-- 9. PACKAGES TABLE (pricing cards per category)
-- ============================================================
CREATE TABLE `packages` (
    `id`            INT            AUTO_INCREMENT PRIMARY KEY,
    `category_id`   BIGINT         NOT NULL,
    `icon`          VARCHAR(10)    DEFAULT NULL COMMENT 'Emoji e.g. 🏡',
    `name`          VARCHAR(100)   NOT NULL COMMENT 'e.g. Rustic, Deep Wild',
    `duration`      VARCHAR(50)    DEFAULT NULL COMMENT 'e.g. 2 Nights · 3 Days',
    `price`         DECIMAL(10,2)  NOT NULL,
    `currency`      VARCHAR(5)     NOT NULL DEFAULT '₹',
    `price_note`    VARCHAR(50)    DEFAULT NULL COMMENT 'e.g. Twin sharing',
    `description`   TEXT           NOT NULL,
    `is_featured`   TINYINT(1)     NOT NULL DEFAULT 0,
    `display_order` INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT `fk_packages_category`
        FOREIGN KEY (`category_id`) REFERENCES `package_categories`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_packages_category` (`category_id`),
    INDEX `idx_packages_featured` (`is_featured`),
    INDEX `idx_packages_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Tour/stay packages per category';

-- ============================================================
-- 10. PACKAGE FEATURES TABLE (checklist per card)
-- ============================================================
CREATE TABLE `package_features` (
    `id`            INT            AUTO_INCREMENT PRIMARY KEY,
    `package_id`    INT            NOT NULL,
    `feature_text`  VARCHAR(255)   NOT NULL,
    `display_order` INT            NOT NULL DEFAULT 0,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_pkgfeat_package`
        FOREIGN KEY (`package_id`) REFERENCES `packages`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_pkgfeat_package` (`package_id`),
    INDEX `idx_pkgfeat_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bullet-point features per package';

-- Package categories seed data
INSERT INTO `package_categories` (`name`, `slug`, `display_order`, `is_active`) VALUES
('Homestay', 'homestay', 1, 1),
('Safari',   'safari',   2, 1);



-- ============================================================
-- 12. GALLERY CATEGORIES TABLE
-- ============================================================
DROP TABLE IF EXISTS `gallery_images`;
DROP TABLE IF EXISTS `gallery_categories`;

CREATE TABLE `gallery_categories` (
    `id`            BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100)   NOT NULL,
    `slug`          VARCHAR(100)   NOT NULL,
    `display_order` INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    UNIQUE KEY `uk_galcat_slug` (`slug`),
    INDEX `idx_galcat_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Gallery tab categories';

-- ============================================================
-- 13. GALLERY IMAGES TABLE
-- ============================================================
CREATE TABLE `gallery_images` (
    `id`            BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `category_id`   BIGINT         NOT NULL,
    `image_url`     VARCHAR(500)   NOT NULL COMMENT 'Relative path e.g. /storage/gallery/tiger.jpg',
    `alt_text`      VARCHAR(255)   DEFAULT NULL,
    `display_order` INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT `fk_galimg_category`
        FOREIGN KEY (`category_id`) REFERENCES `gallery_categories`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,

    INDEX `idx_galimg_category` (`category_id`),
    INDEX `idx_galimg_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Gallery images with category';

-- ============================================================
-- 14. GALLERY SEED DATA
-- ============================================================
INSERT INTO `gallery_categories` (`name`, `slug`, `display_order`, `is_active`) VALUES
('Gallery',  'gallery',  1, 1),
('Wildlife', 'wildlife', 2, 1);


-- ============================================================
-- 15. CONTACT METHODS TABLE (Call, Email, WhatsApp cards)
-- ============================================================
DROP TABLE IF EXISTS `contact_submissions`;
DROP TABLE IF EXISTS `contact_methods`;
DROP TABLE IF EXISTS `social_links`;

CREATE TABLE `contact_methods` (
    `id`            BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `icon`          VARCHAR(10)    NOT NULL COMMENT 'Emoji e.g. 📞',
    `title`         VARCHAR(100)   NOT NULL COMMENT 'e.g. Call Us',
    `detail`        VARCHAR(255)   NOT NULL COMMENT 'e.g. +91 98765 43210',
    `href`          VARCHAR(500)   NOT NULL COMMENT 'e.g. tel:+919876543210',
    `description`   TEXT           DEFAULT NULL COMMENT 'e.g. Available 8 AM – 9 PM',
    `display_order` INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX `idx_contact_methods_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Contact cards (Call, Email, WhatsApp)';

-- ============================================================
-- 16. CONTACT SUBMISSIONS TABLE (form entries)
-- ============================================================
CREATE TABLE `contact_submissions` (
    `id`                BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `full_name`         VARCHAR(255)   NOT NULL,
    `email`             VARCHAR(255)   NOT NULL,
    `phone`             VARCHAR(20)    NOT NULL,
    `num_guests`        VARCHAR(20)    NOT NULL COMMENT 'e.g. 2 Guests',
    `preferred_package` VARCHAR(100)   DEFAULT NULL COMMENT 'e.g. Deep Wild — Week (4N/5D)',
    `travel_dates`      VARCHAR(255)   DEFAULT NULL COMMENT 'e.g. First week of March 2026',
    `message`           TEXT           NOT NULL,
    `status`            ENUM('new','read','replied','archived') NOT NULL DEFAULT 'new',
    `created_at`        DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_contact_sub_status` (`status`),
    INDEX `idx_contact_sub_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Enquiry/contact form submissions';

-- ============================================================
-- 17. SOCIAL LINKS TABLE
-- ============================================================
CREATE TABLE `social_links` (
    `id`            BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `icon_name`     VARCHAR(100)   NOT NULL COMMENT 'Full icon name e.g. fa-facebook',
    `label`         VARCHAR(50)    NOT NULL COMMENT 'e.g. Instagram',
    `href`          VARCHAR(500)   NOT NULL COMMENT 'e.g. https://instagram.com/...',
    `display_order` INT            NOT NULL DEFAULT 0,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,

    INDEX `idx_social_links_order` (`display_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Social media links';



-- ============================================================
-- 18. USEFUL QUERIES (Reference)
-- ============================================================
-- These are NOT executed — kept here as developer reference.


-- List available homestays:
-- SELECT * FROM homestays WHERE is_available = 1 ORDER BY price_per_night ASC;

-- Get bookings with homestay details:
-- SELECT b.*, h.name AS homestay_name, h.location AS homestay_location
-- FROM bookings b
-- LEFT JOIN homestays h ON b.homestay_id = h.id
-- ORDER BY b.created_at DESC;

-- Count bookings per homestay:
-- SELECT h.name, COUNT(b.id) AS total_bookings
-- FROM homestays h
-- LEFT JOIN bookings b ON h.id = b.homestay_id
-- GROUP BY h.id, h.name
-- ORDER BY total_bookings DESC;

-- Get published pages:
-- SELECT id, title, slug FROM pages WHERE status = 'published' ORDER BY created_at ASC;

-- Revenue report by homestay:
-- SELECT h.name, SUM(b.total_price) AS total_revenue, COUNT(b.id) AS booking_count
-- FROM bookings b
-- JOIN homestays h ON b.homestay_id = h.id
-- WHERE b.status != 'cancelled'
-- GROUP BY h.id, h.name
-- ORDER BY total_revenue DESC;
