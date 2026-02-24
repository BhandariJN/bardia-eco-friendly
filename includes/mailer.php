<?php
/**
 * Email Helper Class
 * Handles email sending via SMTP using PHPMailer
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Send email via SMTP
 * 
 * @param string $to Recipient email
 * @param string $toName Recipient name
 * @param string $subject Email subject
 * @param string $bodyHtml HTML body
 * @param string|null $bodyPlain Plain text body (optional)
 * @return array ['success' => bool, 'message' => string, 'error' => string|null]
 */
function sendEmail(string $to, string $toName, string $subject, string $bodyHtml, ?string $bodyPlain = null): array
{
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST'] ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USERNAME'] ?? '';
        $mail->Password   = $_ENV['MAIL_PASSWORD'] ?? '';
        $mail->SMTPSecure = $_ENV['MAIL_ENCRYPTION'] ?? 'tls';
        $mail->Port       = (int) ($_ENV['MAIL_PORT'] ?? 587);
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'Bardiya Eco Friendly'
        );
        $mail->addAddress($to, $toName);
        $mail->addReplyTo(
            $_ENV['MAIL_FROM_ADDRESS'] ?? 'noreply@example.com',
            $_ENV['MAIL_FROM_NAME'] ?? 'Bardiya Eco Friendly'
        );

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $bodyHtml;
        $mail->AltBody = $bodyPlain ?? strip_tags($bodyHtml);

        $mail->send();
        
        return [
            'success' => true,
            'message' => 'Email sent successfully',
            'error'   => null
        ];

    } catch (Exception $e) {
        $errorMsg = "Email sending failed: {$mail->ErrorInfo}";
        logError($errorMsg);
        
        return [
            'success' => false,
            'message' => 'Failed to send email',
            'error'   => $mail->ErrorInfo
        ];
    }
}

/**
 * Send reply email to contact submission
 * 
 * @param int $submissionId Contact submission ID
 * @param string $subject Email subject
 * @param string $bodyHtml HTML body
 * @param string|null $bodyPlain Plain text body
 * @param int $userId User ID sending the email
 * @return array ['success' => bool, 'message' => string, 'email_id' => int|null]
 */
function sendReplyEmail(int $submissionId, string $subject, string $bodyHtml, ?string $bodyPlain, int $userId): array
{
    global $conn;

    // Get submission details
    $stmt = $conn->prepare("SELECT full_name, email FROM contact_submissions WHERE id = ?");
    $stmt->bind_param('i', $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return [
            'success' => false,
            'message' => 'Contact submission not found',
            'email_id' => null
        ];
    }
    
    $submission = $result->fetch_assoc();
    $stmt->close();

    // Send email
    $emailResult = sendEmail(
        $submission['email'],
        $submission['full_name'],
        $subject,
        $bodyHtml,
        $bodyPlain
    );

    // Log email history
    $status = $emailResult['success'] ? 'sent' : 'failed';
    $errorMessage = $emailResult['error'];
    
    $emailId = logEmailHistory(
        $submissionId,
        $submission['email'],
        $submission['full_name'],
        $subject,
        $bodyHtml,
        $bodyPlain,
        $userId,
        $status,
        $errorMessage
    );

    // Update submission email count and last sent date
    if ($emailResult['success']) {
        $conn->query("UPDATE contact_submissions 
                      SET email_count = email_count + 1, 
                          last_email_sent_at = NOW(),
                          status = 'replied'
                      WHERE id = $submissionId");
    }

    return [
        'success'  => $emailResult['success'],
        'message'  => $emailResult['message'],
        'email_id' => $emailId
    ];
}

/**
 * Log email to history table
 * 
 * @param int $submissionId
 * @param string $recipientEmail
 * @param string $recipientName
 * @param string $subject
 * @param string $bodyHtml
 * @param string|null $bodyPlain
 * @param int $userId
 * @param string $status 'sent'|'failed'|'pending'
 * @param string|null $errorMessage
 * @return int Email history ID
 */
function logEmailHistory(
    int $submissionId,
    string $recipientEmail,
    string $recipientName,
    string $subject,
    string $bodyHtml,
    ?string $bodyPlain,
    int $userId,
    string $status = 'sent',
    ?string $errorMessage = null
): int {
    global $conn;

    $stmt = $conn->prepare(
        "INSERT INTO email_history 
        (submission_id, recipient_email, recipient_name, subject, body_html, body_plain, sent_by_user_id, status, error_message) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
    );
    
    $stmt->bind_param(
        'issssssss',
        $submissionId,
        $recipientEmail,
        $recipientName,
        $subject,
        $bodyHtml,
        $bodyPlain,
        $userId,
        $status,
        $errorMessage
    );
    
    $stmt->execute();
    $emailId = $stmt->insert_id;
    $stmt->close();

    return $emailId;
}

/**
 * Get email history for a submission
 * 
 * @param int $submissionId
 * @return array
 */
function getEmailHistory(int $submissionId): array
{
    global $conn;

    $stmt = $conn->prepare(
        "SELECT eh.id, eh.subject, eh.body_html, eh.sent_at, eh.status, eh.error_message,
                u.username AS sent_by
         FROM email_history eh
         LEFT JOIN users u ON eh.sent_by_user_id = u.id
         WHERE eh.submission_id = ?
         ORDER BY eh.sent_at DESC"
    );
    
    $stmt->bind_param('i', $submissionId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $row['id'] = (int) $row['id'];
        $history[] = $row;
    }
    
    $stmt->close();
    return $history;
}

/**
 * Sanitize HTML content for email
 * Removes dangerous tags and attributes
 * 
 * @param string $html
 * @return string
 */
function sanitizeEmailHtml(string $html): string
{
    // Allow safe HTML tags
    $allowedTags = '<p><br><strong><b><em><i><u><a><ul><ol><li><h1><h2><h3><h4><h5><h6><table><tr><td><th><thead><tbody><img><span><div>';
    
    $html = strip_tags($html, $allowedTags);
    
    // Remove dangerous attributes
    $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
    $html = preg_replace('/on\w+="[^"]*"/i', '', $html);
    $html = preg_replace('/on\w+=\'[^\']*\'/i', '', $html);
    
    return $html;
}
