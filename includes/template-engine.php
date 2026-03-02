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
 * Fetch active social links from database
 */
function getSocialLinks(mysqli $conn): array
{
    $links = [];
    $res = $conn->query("SELECT label, href FROM social_links WHERE is_active = 1 ORDER BY display_order ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $links[] = $row;
        }
    }
    return $links;
}

/**
 * Fetch active contact methods from database
 */
function getContactMethods(mysqli $conn): array
{
    $methods = [];
    $res = $conn->query("SELECT icon_name, label, value FROM contact_methods WHERE is_active = 1 ORDER BY display_order ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $methods[] = $row;
        }
    }
    return $methods;
}

/**
 * Wrap email content in standard HTML template
 * 
 * @param string $content Email body content
 * @param array $socialLinks Array of social links from DB
 * @param array $contactMethods Array of contact methods from DB
 * @return string Full HTML email
 */
function wrapEmailTemplate(string $content, array $socialLinks = [], array $contactMethods = []): string
{
    $headerStyle = "background: #10b981; color: white; padding: 30px 20px; text-align: center; border-radius: 8px 8px 0 0;";
    $bodyStyle   = "background: #ffffff; padding: 30px; border: 1px solid #e5e7eb; border-top: none; font-family: Arial, sans-serif; line-height: 1.6; color: #333;";
    $footerStyle = "background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #6b7280; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px;";
    $linkStyle   = "color: #10b981; text-decoration: none;";

    // Build social links HTML
    $socialHtml = '';
    if (!empty($socialLinks)) {
        $socialHtml .= '<p style="margin-top: 15px;">';
        $items = [];
        foreach ($socialLinks as $link) {
            $label = htmlspecialchars($link['label']);
            $href = htmlspecialchars($link['href']);
            $items[] = "<a href=\"$href\" style=\"$linkStyle\">$label</a>";
        }
        $socialHtml .= implode(' | ', $items);
        $socialHtml .= '</p>';
    }

    // Build contact methods HTML
    $contactHtml = '';
    if (!empty($contactMethods)) {
        $contactHtml .= '<p style="margin: 0 0 10px 0;">';
        $items = [];
        foreach ($contactMethods as $method) {
            $label = htmlspecialchars($method['label']);
            $value = htmlspecialchars($method['value']);
            $icon = '';
            if (stripos($method['icon_name'] ?? '', 'mail') !== false || stripos($label, 'email') !== false) $icon = '📧 ';
            if (stripos($method['icon_name'] ?? '', 'phone') !== false || stripos($label, 'phone') !== false) $icon = '📞 ';
            if (stripos($method['icon_name'] ?? '', 'whatsapp') !== false) $icon = '💬 ';
            
            $items[] = "$icon$value";
        }
        $contactHtml .= implode(' | ', $items);
        $contactHtml .= '</p>';
    }

    // Company info
    $companyName  = "Bardiya Eco Friendly";
    $companyAddr  = "Thakurdwara, Bardiya, Nepal";
    $companyEmail = "inquiry@bardiaecofriendlyhomestay.com";
    $companyPhone = "+977-9845000000";

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 20px; background-color: #f3f4f6;">
    <div style="max-width: 600px; margin: 0 auto;">
        <div style="$headerStyle">
            <h2 style="margin: 0; font-family: Arial, sans-serif;">🌿 $companyName</h2>
            <p style="margin: 5px 0 0 0; opacity: 0.9; font-size: 14px;">Sustainable Tourism in Bardiya, Nepal</p>
        </div>
        <div style="$bodyStyle">
            $content
        </div>
        <div style="$footerStyle">
            <p style="margin: 0 0 10px 0;"><strong>$companyName</strong></p>
            <p style="margin: 0 0 10px 0;">$companyAddr</p>
            <p style="margin: 0 0 10px 0;">📧 $companyEmail | 📞 $companyPhone</p>
            $contactHtml
            $socialHtml
            <p style="margin-top: 15px; font-size: 11px; color: #9ca3af;">
                You received this email because you contacted us through our website.
            </p>
        </div>
    </div>
</body>
</html>
HTML;
}
