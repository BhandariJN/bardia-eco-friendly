-- ============================================================
-- Email Integration - Database Migration
-- Add email functionality to Bardiya Eco Friendly CMS
-- ============================================================

USE `bardiya_eco_friendly`;

-- ============================================================
-- 1. EMAIL HISTORY TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_history` (
    `id`                BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `submission_id`     BIGINT         NOT NULL,
    `recipient_email`   VARCHAR(255)   NOT NULL,
    `recipient_name`    VARCHAR(255)   NOT NULL,
    `subject`           VARCHAR(500)   NOT NULL,
    `body_html`         LONGTEXT       NOT NULL,
    `body_plain`        LONGTEXT       DEFAULT NULL,
    `sent_by_user_id`   INT            NOT NULL,
    `sent_at`           DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status`            ENUM('sent','failed','pending') NOT NULL DEFAULT 'sent',
    `error_message`     TEXT           DEFAULT NULL,
    
    CONSTRAINT `fk_email_submission`
        FOREIGN KEY (`submission_id`) REFERENCES `contact_submissions`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    CONSTRAINT `fk_email_user`
        FOREIGN KEY (`sent_by_user_id`) REFERENCES `users`(`id`)
        ON DELETE CASCADE ON UPDATE CASCADE,
        
    INDEX `idx_email_submission` (`submission_id`),
    INDEX `idx_email_sent_at` (`sent_at`),
    INDEX `idx_email_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Email reply history for contact submissions';

-- ============================================================
-- 2. EMAIL TEMPLATES TABLE
-- ============================================================
CREATE TABLE IF NOT EXISTS `email_templates` (
    `id`            BIGINT         AUTO_INCREMENT PRIMARY KEY,
    `name`          VARCHAR(100)   NOT NULL,
    `subject`       VARCHAR(500)   NOT NULL,
    `body_html`     LONGTEXT       NOT NULL,
    `description`   TEXT           DEFAULT NULL,
    `is_active`     TINYINT(1)     NOT NULL DEFAULT 1,
    `created_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    DATETIME       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    UNIQUE KEY `uk_template_name` (`name`),
    INDEX `idx_template_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Email templates for quick replies';

-- ============================================================
-- 3. UPDATE CONTACT SUBMISSIONS TABLE
-- ============================================================
ALTER TABLE `contact_submissions` 
ADD COLUMN IF NOT EXISTS `email_count` INT NOT NULL DEFAULT 0 AFTER `status`,
ADD COLUMN IF NOT EXISTS `last_email_sent_at` DATETIME DEFAULT NULL AFTER `email_count`;

-- ============================================================
-- 4. INSERT DEFAULT EMAIL TEMPLATES
-- ============================================================
INSERT INTO `email_templates` (`name`, `subject`, `body_html`, `description`, `is_active`) VALUES
('booking_enquiry_response', 'Re: Your Enquiry - Bardiya Eco Friendly', 
'<p>Dear {{guest_name}},</p>

<p>Thank you for your interest in Bardiya Eco Friendly!</p>

<p>We have received your enquiry for <strong>{{preferred_package}}</strong> for {{num_guests}} during {{travel_dates}}.</p>

<p>Our team will review your request and get back to you within 24 hours with availability and detailed information.</p>

<p>If you have any immediate questions, feel free to call us at {{company_phone}}.</p>

<p>Best regards,<br>
{{admin_name}}<br>
{{company_name}} Team</p>',
'Standard response for booking enquiries', 1),

('general_enquiry_response', 'Re: Your Message - Bardiya Eco Friendly',
'<p>Dear {{guest_name}},</p>

<p>Thank you for contacting Bardiya Eco Friendly.</p>

<p>We appreciate your interest in our eco-friendly homestays and safari experiences in beautiful Bardiya, Nepal.</p>

<p>We have received your message and will respond with detailed information shortly.</p>

<p>Looking forward to hosting you in Bardiya!</p>

<p>Warm regards,<br>
{{admin_name}}<br>
{{company_name}}</p>',
'General response for enquiries', 1),

('booking_confirmation', 'Booking Confirmed - Bardiya Eco Friendly',
'<p>Dear {{guest_name}},</p>

<p>🎉 Great news! Your booking has been confirmed.</p>

<p><strong>Booking Details:</strong></p>
<ul>
    <li><strong>Package:</strong> {{preferred_package}}</li>
    <li><strong>Guests:</strong> {{num_guests}}</li>
    <li><strong>Dates:</strong> {{travel_dates}}</li>
</ul>

<p>We will send you detailed information about your stay, including directions and what to bring, closer to your arrival date.</p>

<p><strong>Important:</strong> If you need to make any changes, please contact us at least 48 hours in advance.</p>

<p>We look forward to welcoming you to Bardiya!</p>

<p>Best regards,<br>
{{admin_name}}<br>
{{company_name}} Team</p>

<p style="margin-top: 20px; padding: 15px; background: #f0fdf4; border-left: 4px solid #10b981;">
<strong>📞 Contact Us:</strong><br>
Phone: {{company_phone}}<br>
Email: {{company_email}}
</p>',
'Booking confirmation template', 1);

-- ============================================================
-- 5. VERIFICATION QUERIES
-- ============================================================
-- Check if tables were created successfully
SELECT 'email_history table' AS table_name, COUNT(*) AS row_count FROM email_history
UNION ALL
SELECT 'email_templates table', COUNT(*) FROM email_templates;

-- Show template names
SELECT id, name, subject, is_active FROM email_templates ORDER BY id;
