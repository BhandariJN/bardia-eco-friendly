<?php
/**
 * Email Template Engine
 * Renders email templates with variable replacement
 */

/**
 * Render email template with variables
 * 
 * @param string $templateHtml Template HTML with {{variable}} placeholders
 * @param array $variables Associative array of variable => value
 * @return string Rendered HTML
 */
function renderEmailTemplate(string $templateHtml, array $variables): string
{
    foreach ($variables as $key => $value) {
        $placeholder = '{{' . $key . '}}';
        $templateHtml = str_replace($placeholder, htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'), $templateHtml);
    }
    
    // Remove any remaining unreplaced placeholders
    $templateHtml = preg_replace('/\{\{[^}]+\}\}/', '', $templateHtml);
    
    return $templateHtml;
}

/**
 * Get variables from contact submission
 * 
 * @param array $submission Contact submission data
 * @param string $adminName Admin name sending the email
 * @return array Variables for template rendering
 */
function getSubmissionVariables(array $submission, string $adminName = 'Admin'): array
{
    return [
        'guest_name'        => $submission['full_name'] ?? '',
        'guest_email'       => $submission['email'] ?? '',
        'guest_phone'       => $submission['phone'] ?? '',
        'num_guests'        => $submission['num_guests'] ?? '',
        'preferred_package' => $submission['preferred_package'] ?? 'N/A',
        'travel_dates'      => $submission['travel_dates'] ?? 'N/A',
        'message'           => $submission['message'] ?? '',
        'admin_name'        => $adminName,
        'company_name'      => 'Bardiya Eco Friendly',
        'company_email'     => $_ENV['MAIL_FROM_ADDRESS'] ?? 'info@bardiyaecofriendly.com',
        'company_phone'     => '+977-084-123456',
    ];
}

/**
 * Wrap email content in standard HTML template
 * 
 * @param string $content Email body content
 * @return string Full HTML email
 */
function wrapEmailTemplate(string $content): string
{
    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .email-body {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        a {
            color: #10b981;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background: #10b981;
            color: white !important;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 0;
        }
        .button:hover {
            background: #059669;
        }
    </style>
</head>
<body>
    <div class="email-header">
        <h2 style="margin: 0;">🌿 Bardiya Eco Friendly</h2>
        <p style="margin: 5px 0 0 0; opacity: 0.9;">Sustainable Tourism in Bardiya, Nepal</p>
    </div>
    <div class="email-body">
        $content
    </div>
    <div class="email-footer">
        <p><strong>Bardiya Eco Friendly</strong></p>
        <p>Thakurdwara, Bardiya, Nepal</p>
        <p>📧 info@bardiyaecofriendly.com | 📞 +977-084-123456</p>
        <p style="margin-top: 15px;">
            <a href="https://facebook.com/bardiyaecofriendly">Facebook</a> | 
            <a href="https://instagram.com/bardiyaecofriendly">Instagram</a>
        </p>
        <p style="margin-top: 15px; font-size: 11px; color: #9ca3af;">
            You received this email because you contacted us through our website.
        </p>
    </div>
</body>
</html>
HTML;
}
