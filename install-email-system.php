<?php
/**
 * Email System Installation Script
 * Run this once to set up the email integration
 * 
 * Usage: php install-email-system.php
 * Or visit: http://localhost/bardia-eco-friendly/install-email-system.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=================================================\n";
echo "  Bardiya Eco Friendly - Email System Installer\n";
echo "=================================================\n\n";

// Load configuration
require_once __DIR__ . '/includes/config.php';

$errors = [];
$success = [];

// Step 1: Check PHPMailer
echo "[1/5] Checking PHPMailer installation...\n";
if (file_exists(__DIR__ . '/vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    $success[] = "✓ PHPMailer is installed";
    echo "  ✓ PHPMailer found\n";
} else {
    $errors[] = "✗ PHPMailer not found. Run: composer require phpmailer/phpmailer";
    echo "  ✗ PHPMailer NOT found\n";
    echo "  → Run: composer require phpmailer/phpmailer\n";
}

// Step 2: Check .env configuration
echo "\n[2/5] Checking email configuration...\n";
$requiredEnvVars = ['MAIL_HOST', 'MAIL_PORT', 'MAIL_USERNAME', 'MAIL_PASSWORD'];
$envComplete = true;

foreach ($requiredEnvVars as $var) {
    if (empty($_ENV[$var]) || $_ENV[$var] === 'your-email@gmail.com' || $_ENV[$var] === 'your-app-password') {
        $errors[] = "✗ $var not configured in .env";
        echo "  ✗ $var not configured\n";
        $envComplete = false;
    } else {
        echo "  ✓ $var configured\n";
    }
}

if ($envComplete) {
    $success[] = "✓ Email configuration complete";
}

// Step 3: Create database tables
echo "\n[3/5] Creating database tables...\n";

// Check if tables already exist
$tableCheck = $conn->query("SHOW TABLES LIKE 'email_history'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo "  ℹ email_history table already exists\n";
} else {
    // Create email_history table
    $sql = "CREATE TABLE IF NOT EXISTS `email_history` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        $success[] = "✓ email_history table created";
        echo "  ✓ email_history table created\n";
    } else {
        $errors[] = "✗ Failed to create email_history table: " . $conn->error;
        echo "  ✗ Failed to create email_history table\n";
    }
}

// Create email_templates table
$tableCheck = $conn->query("SHOW TABLES LIKE 'email_templates'");
if ($tableCheck && $tableCheck->num_rows > 0) {
    echo "  ℹ email_templates table already exists\n";
} else {
    $sql = "CREATE TABLE IF NOT EXISTS `email_templates` (
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
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    if ($conn->query($sql)) {
        $success[] = "✓ email_templates table created";
        echo "  ✓ email_templates table created\n";
    } else {
        $errors[] = "✗ Failed to create email_templates table: " . $conn->error;
        echo "  ✗ Failed to create email_templates table\n";
    }
}

// Step 4: Update contact_submissions table
echo "\n[4/5] Updating contact_submissions table...\n";

$columnCheck = $conn->query("SHOW COLUMNS FROM contact_submissions LIKE 'email_count'");
if ($columnCheck && $columnCheck->num_rows > 0) {
    echo "  ℹ email_count column already exists\n";
} else {
    $sql = "ALTER TABLE `contact_submissions` 
            ADD COLUMN `email_count` INT NOT NULL DEFAULT 0 AFTER `status`,
            ADD COLUMN `last_email_sent_at` DATETIME DEFAULT NULL AFTER `email_count`";
    
    if ($conn->query($sql)) {
        $success[] = "✓ contact_submissions table updated";
        echo "  ✓ Added email tracking columns\n";
    } else {
        $errors[] = "✗ Failed to update contact_submissions: " . $conn->error;
        echo "  ✗ Failed to add columns\n";
    }
}

// Step 5: Insert default templates
echo "\n[5/5] Installing default email templates...\n";

$templates = [
    [
        'name' => 'booking_enquiry_response',
        'subject' => 'Re: Your Enquiry - Bardiya Eco Friendly',
        'body_html' => '<p>Dear {{guest_name}},</p>

<p>Thank you for your interest in Bardiya Eco Friendly!</p>

<p>We have received your enquiry for <strong>{{preferred_package}}</strong> for {{num_guests}} during {{travel_dates}}.</p>

<p>Our team will review your request and get back to you within 24 hours with availability and detailed information.</p>

<p>If you have any immediate questions, feel free to call us at {{company_phone}}.</p>

<p>Best regards,<br>
{{admin_name}}<br>
{{company_name}} Team</p>',
        'description' => 'Standard response for booking enquiries'
    ],
    [
        'name' => 'general_enquiry_response',
        'subject' => 'Re: Your Message - Bardiya Eco Friendly',
        'body_html' => '<p>Dear {{guest_name}},</p>

<p>Thank you for contacting Bardiya Eco Friendly.</p>

<p>We appreciate your interest in our eco-friendly homestays and safari experiences in beautiful Bardiya, Nepal.</p>

<p>We have received your message and will respond with detailed information shortly.</p>

<p>Looking forward to hosting you in Bardiya!</p>

<p>Warm regards,<br>
{{admin_name}}<br>
{{company_name}}</p>',
        'description' => 'General response for enquiries'
    ],
    [
        'name' => 'booking_confirmation',
        'subject' => 'Booking Confirmed - Bardiya Eco Friendly',
        'body_html' => '<p>Dear {{guest_name}},</p>

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
        'description' => 'Booking confirmation template'
    ]
];

foreach ($templates as $template) {
    $check = $conn->prepare("SELECT id FROM email_templates WHERE name = ?");
    $check->bind_param('s', $template['name']);
    $check->execute();
    
    if ($check->get_result()->num_rows > 0) {
        echo "  ℹ Template '{$template['name']}' already exists\n";
    } else {
        $stmt = $conn->prepare("INSERT INTO email_templates (name, subject, body_html, description) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('ssss', $template['name'], $template['subject'], $template['body_html'], $template['description']);
        
        if ($stmt->execute()) {
            echo "  ✓ Installed template: {$template['name']}\n";
        } else {
            echo "  ✗ Failed to install template: {$template['name']}\n";
        }
        $stmt->close();
    }
    $check->close();
}

// Summary
echo "\n=================================================\n";
echo "  Installation Summary\n";
echo "=================================================\n\n";

if (!empty($success)) {
    echo "✓ SUCCESS:\n";
    foreach ($success as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (!empty($errors)) {
    echo "✗ ERRORS:\n";
    foreach ($errors as $msg) {
        echo "  $msg\n";
    }
    echo "\n";
}

if (empty($errors)) {
    echo "🎉 Installation completed successfully!\n\n";
    echo "Next steps:\n";
    echo "1. Configure email settings in .env file\n";
    echo "2. Visit: http://localhost/bardia-eco-friendly/cms/contact-submissions-enhanced.php\n";
    echo "3. Login and test sending an email\n\n";
} else {
    echo "⚠️  Installation completed with errors.\n";
    echo "Please fix the errors above and run this script again.\n\n";
}

echo "=================================================\n";
?>
