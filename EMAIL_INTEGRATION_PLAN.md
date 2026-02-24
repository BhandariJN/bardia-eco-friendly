# Email Integration Plan - CMS Reply System
## Senior Developer Implementation Guide

**Project:** Bardiya Eco Friendly CMS  
**Feature:** Direct Email Reply System with Rich Text Editor  
**Prepared By:** Senior Backend Developer (10+ years experience)  
**Date:** February 24, 2026

---

## Executive Summary

Implement a professional email reply system within the CMS that allows admins to:
1. Reply to contact submissions directly from the CMS
2. Compose emails with a rich text editor (WYSIWYG)
3. Send emails via SMTP without opening external mail clients
4. Track email history and status
5. Use email templates for common responses

---

## Technical Architecture

### 1. Technology Stack

**Email Library:** PHPMailer (Industry Standard)
- Mature, well-maintained library
- SMTP support with authentication
- HTML email support
- Attachment support
- Error handling and logging

**Rich Text Editor:** TinyMCE or Quill.js
- **TinyMCE** (Recommended): Full-featured, professional
- **Quill.js** (Alternative): Lightweight, modern
- Both support HTML output, formatting, links, images

**SMTP Provider Options:**
- Gmail SMTP (Free, 500 emails/day)
- SendGrid (Free tier: 100 emails/day)
- Mailgun (Free tier: 5,000 emails/month)
- Amazon SES (Pay-as-you-go, very cheap)
- Custom SMTP server

---

## Implementation Plan

### Phase 1: Dependencies & Configuration (Day 1)

#### 1.1 Install PHPMailer
```bash
composer require phpmailer/phpmailer
```

#### 1.2 Update .env Configuration
```env
# Email Configuration
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@bardiyaecofriendly.com
MAIL_FROM_NAME=Bardiya Eco Friendly
```

#### 1.3 Create Email Helper Class
**File:** `includes/mailer.php`
- Initialize PHPMailer with config
- Send email function
- HTML template support
- Error logging

---

### Phase 2: Database Schema Updates (Day 1)

#### 2.1 Create Email History Table
```sql
CREATE TABLE `email_history` (
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
```

#### 2.2 Create Email Templates Table
```sql
CREATE TABLE `email_templates` (
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
```

#### 2.3 Add Email Count to Contact Submissions
```sql
ALTER TABLE `contact_submissions` 
ADD COLUMN `email_count` INT NOT NULL DEFAULT 0 AFTER `status`,
ADD COLUMN `last_email_sent_at` DATETIME DEFAULT NULL AFTER `email_count`;
```

---

### Phase 3: Backend Implementation (Day 2-3)

#### 3.1 Create Mailer Helper Class
**File:** `includes/mailer.php`

**Functions:**
- `sendEmail($to, $toName, $subject, $bodyHtml, $bodyPlain = null)`
- `sendReplyEmail($submissionId, $subject, $bodyHtml)`
- `logEmailHistory($submissionId, $recipientEmail, $subject, $bodyHtml, $status, $error = null)`
- `getEmailHistory($submissionId)`

**Features:**
- SMTP authentication
- HTML and plain text support
- Error handling and logging
- Automatic retry logic (optional)
- Email validation

#### 3.2 Create Email API Endpoints

**File:** `api/emails/send-reply.php`
```php
POST /api/emails/send-reply
Authentication: Required (JWT)

Request Body:
{
  "submission_id": 5,
  "subject": "Re: Your Enquiry - Bardiya Eco Friendly",
  "body_html": "<p>Dear John,</p><p>Thank you for your enquiry...</p>",
  "body_plain": "Dear John, Thank you for your enquiry..."
}

Response:
{
  "status": "success",
  "message": "Email sent successfully",
  "data": {
    "email_id": 15,
    "sent_at": "2026-02-24 14:30:00"
  }
}
```

**File:** `api/emails/history.php`
```php
GET /api/emails/history?submission_id=5
Authentication: Required (JWT)

Response:
{
  "status": "success",
  "data": [
    {
      "id": 15,
      "subject": "Re: Your Enquiry",
      "sent_at": "2026-02-24 14:30:00",
      "sent_by": "admin",
      "status": "sent"
    }
  ]
}
```

**File:** `api/email-templates/list.php`
```php
GET /api/email-templates/list
Authentication: Required (JWT)

Response:
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "booking_confirmation",
      "subject": "Booking Confirmation - {{guest_name}}",
      "body_html": "<p>Dear {{guest_name}},</p>...",
      "description": "Standard booking confirmation template"
    }
  ]
}
```

---

### Phase 4: Frontend CMS Implementation (Day 3-4)

#### 4.1 Update Contact Submissions Page
**File:** `cms/contact-submissions.php`

**Add "Reply" Button:**
- Add reply button next to "View" button
- Opens modal with email composer
- Pre-fills recipient info from submission

**Email History Section:**
- Show email count badge on each submission
- Display sent emails in view modal
- Show timestamps and status

#### 4.2 Create Email Composer Modal

**Features:**
- Rich text editor (TinyMCE)
- Subject line input
- Recipient info (read-only, pre-filled)
- Template selector dropdown
- Preview button
- Send button with loading state
- Character/word count

**HTML Structure:**
```html
<div class="modal-backdrop" id="emailModal">
    <div class="modal" style="width:min(900px,96vw);">
        <h3>Reply to: <span id="emailRecipientName"></span></h3>
        
        <form id="emailForm">
            <input type="hidden" id="submissionId">
            
            <!-- Template Selector -->
            <div class="form-group">
                <label>Quick Template (Optional)</label>
                <select id="templateSelect">
                    <option value="">-- Select Template --</option>
                    <option value="1">Booking Confirmation</option>
                    <option value="2">General Enquiry Response</option>
                </select>
            </div>
            
            <!-- Subject -->
            <div class="form-group">
                <label>Subject *</label>
                <input type="text" id="emailSubject" required>
            </div>
            
            <!-- Rich Text Editor -->
            <div class="form-group">
                <label>Message *</label>
                <textarea id="emailBody"></textarea>
            </div>
            
            <!-- Actions -->
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeEmailModal()">Cancel</button>
                <button type="button" class="btn btn-secondary" onclick="previewEmail()">Preview</button>
                <button type="submit" class="btn btn-primary">
                    <span id="sendBtnText">Send Email</span>
                    <span id="sendBtnLoader" style="display:none;">Sending...</span>
                </button>
            </div>
        </form>
    </div>
</div>
```

#### 4.3 Integrate TinyMCE Editor

**CDN Include (in header.php):**
```html
<script src="https://cdn.tiny.cloud/1/YOUR_API_KEY/tinymce/6/tinymce.min.js"></script>
```

**Initialize Editor:**
```javascript
tinymce.init({
    selector: '#emailBody',
    height: 400,
    menubar: false,
    plugins: [
        'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
        'preview', 'anchor', 'searchreplace', 'visualblocks', 'code',
        'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'
    ],
    toolbar: 'undo redo | formatselect | bold italic underline | ' +
             'alignleft aligncenter alignright alignjustify | ' +
             'bullist numlist outdent indent | link image | removeformat | help',
    content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }',
    branding: false
});
```

#### 4.4 JavaScript Email Sending Logic

```javascript
async function sendEmail(submissionId, subject, bodyHtml) {
    const sendBtn = document.getElementById('sendBtnText');
    const loader = document.getElementById('sendBtnLoader');
    
    sendBtn.style.display = 'none';
    loader.style.display = 'inline';
    
    try {
        const response = await fetch('/bardia-eco-friendly/api/emails/send-reply', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + localStorage.getItem('jwt_token')
            },
            body: JSON.stringify({
                submission_id: submissionId,
                subject: subject,
                body_html: bodyHtml,
                body_plain: stripHtml(bodyHtml)
            })
        });
        
        const data = await response.json();
        
        if (data.status === 'success') {
            alert('Email sent successfully!');
            closeEmailModal();
            location.reload(); // Refresh to show updated status
        } else {
            alert('Error: ' + data.message);
        }
    } catch (error) {
        alert('Failed to send email: ' + error.message);
    } finally {
        sendBtn.style.display = 'inline';
        loader.style.display = 'none';
    }
}

function stripHtml(html) {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
}
```

---

### Phase 5: Email Templates Management (Day 4)

#### 5.1 Create Template Management Page
**File:** `cms/email-templates.php`

**Features:**
- List all templates
- Create new template
- Edit existing template
- Delete template
- Preview template
- Variable placeholders support

**Supported Variables:**
- `{{guest_name}}` - Guest full name
- `{{guest_email}}` - Guest email
- `{{guest_phone}}` - Guest phone
- `{{num_guests}}` - Number of guests
- `{{preferred_package}}` - Package name
- `{{travel_dates}}` - Travel dates
- `{{message}}` - Original message
- `{{admin_name}}` - Sender name
- `{{company_name}}` - Company name

#### 5.2 Template Rendering Engine

**File:** `includes/template-engine.php`

```php
function renderEmailTemplate($templateHtml, $variables) {
    foreach ($variables as $key => $value) {
        $templateHtml = str_replace('{{' . $key . '}}', $value, $templateHtml);
    }
    return $templateHtml;
}
```

---

### Phase 6: Security & Validation (Day 5)

#### 6.1 Security Measures

1. **Authentication:**
   - All email endpoints require JWT authentication
   - Verify user has admin role

2. **Input Validation:**
   - Sanitize subject and body
   - Validate email addresses
   - Limit email body size (max 500KB)
   - Rate limiting (max 50 emails/hour per user)

3. **XSS Prevention:**
   - Strip dangerous HTML tags from editor output
   - Use HTMLPurifier library for sanitization

4. **SMTP Security:**
   - Use TLS/SSL encryption
   - Store credentials in .env (never in code)
   - Use app-specific passwords (Gmail)

#### 6.2 Validation Rules

```php
// Subject validation
if (strlen($subject) < 5 || strlen($subject) > 500) {
    jsonResponse(400, 'error', null, 'Subject must be 5-500 characters');
}

// Body validation
if (strlen($bodyHtml) < 10 || strlen($bodyHtml) > 500000) {
    jsonResponse(400, 'error', null, 'Email body too short or too long');
}

// Email validation
if (!filter_var($recipientEmail, FILTER_VALIDATE_EMAIL)) {
    jsonResponse(400, 'error', null, 'Invalid recipient email');
}
```

---

### Phase 7: Testing & Quality Assurance (Day 5-6)

#### 7.1 Unit Tests

**Test Cases:**
1. Send email with valid data
2. Send email with invalid recipient
3. Send email without authentication
4. Template rendering with variables
5. HTML sanitization
6. SMTP connection failure handling
7. Rate limiting enforcement

#### 7.2 Integration Tests

1. Complete email flow from CMS
2. Template selection and rendering
3. Email history tracking
4. Status updates after sending
5. Error handling and logging

#### 7.3 Manual Testing Checklist

- [ ] Send test email to personal account
- [ ] Verify HTML formatting in Gmail, Outlook, Apple Mail
- [ ] Test rich text editor features (bold, italic, links, lists)
- [ ] Test template selection and variable replacement
- [ ] Verify email appears in sent history
- [ ] Test error handling (wrong SMTP credentials)
- [ ] Test mobile responsiveness of composer modal
- [ ] Verify email count updates on submission
- [ ] Test concurrent email sending
- [ ] Verify logs are created for failed emails

---

## File Structure

```
bardia-eco-friendly/
├── includes/
│   ├── mailer.php              # NEW - PHPMailer wrapper
│   └── template-engine.php     # NEW - Email template renderer
├── api/
│   ├── emails/
│   │   ├── send-reply.php      # NEW - Send email endpoint
│   │   └── history.php         # NEW - Email history endpoint
│   └── email-templates/
│       ├── list.php            # NEW - List templates
│       ├── create.php          # NEW - Create template
│       ├── update.php          # NEW - Update template
│       └── delete.php          # NEW - Delete template
├── cms/
│   ├── contact-submissions.php # MODIFIED - Add reply button
│   └── email-templates.php     # NEW - Template management
└── storage/
    └── logs/
        └── email.log           # NEW - Email sending logs
```

---

## Configuration Guide

### Gmail SMTP Setup

1. **Enable 2-Factor Authentication** on your Google account
2. **Generate App Password:**
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Copy the 16-character password
3. **Update .env:**
   ```env
   MAIL_HOST=smtp.gmail.com
   MAIL_PORT=587
   MAIL_USERNAME=your-email@gmail.com
   MAIL_PASSWORD=your-16-char-app-password
   MAIL_ENCRYPTION=tls
   ```

### SendGrid Setup (Alternative)

1. **Create SendGrid account** (free tier)
2. **Generate API Key**
3. **Update .env:**
   ```env
   MAIL_HOST=smtp.sendgrid.net
   MAIL_PORT=587
   MAIL_USERNAME=apikey
   MAIL_PASSWORD=your-sendgrid-api-key
   MAIL_ENCRYPTION=tls
   ```

---

## Default Email Templates

### Template 1: Booking Enquiry Response
```html
<p>Dear {{guest_name}},</p>

<p>Thank you for your interest in Bardiya Eco Friendly!</p>

<p>We have received your enquiry for <strong>{{preferred_package}}</strong> for {{num_guests}} during {{travel_dates}}.</p>

<p>Our team will review your request and get back to you within 24 hours with availability and detailed information.</p>

<p>If you have any immediate questions, feel free to call us at +977-084-123456.</p>

<p>Best regards,<br>
{{admin_name}}<br>
Bardiya Eco Friendly Team</p>
```

### Template 2: General Enquiry Response
```html
<p>Dear {{guest_name}},</p>

<p>Thank you for contacting Bardiya Eco Friendly.</p>

<p>We appreciate your interest in our eco-friendly homestays and safari experiences.</p>

<p>[Your custom response here]</p>

<p>Looking forward to hosting you in beautiful Bardiya!</p>

<p>Warm regards,<br>
{{admin_name}}<br>
Bardiya Eco Friendly</p>
```

### Template 3: Booking Confirmation
```html
<p>Dear {{guest_name}},</p>

<p>Great news! Your booking has been confirmed.</p>

<p><strong>Booking Details:</strong></p>
<ul>
    <li>Package: {{preferred_package}}</li>
    <li>Guests: {{num_guests}}</li>
    <li>Dates: {{travel_dates}}</li>
</ul>

<p>We will send you detailed information about your stay, including directions and what to bring, closer to your arrival date.</p>

<p>If you need to make any changes, please contact us at least 48 hours in advance.</p>

<p>We look forward to welcoming you!</p>

<p>Best regards,<br>
{{admin_name}}<br>
Bardiya Eco Friendly Team</p>
```

---

## Performance Considerations

1. **Async Email Sending:**
   - Consider implementing queue system for bulk emails
   - Use background jobs (cron) for non-urgent emails

2. **Caching:**
   - Cache email templates in memory
   - Reduce database queries for template loading

3. **Rate Limiting:**
   - Implement per-user rate limits
   - Prevent spam and abuse

4. **Monitoring:**
   - Log all email attempts
   - Track delivery rates
   - Monitor SMTP errors

---

## Maintenance & Support

### Daily Tasks
- Monitor email logs for failures
- Check SMTP connection status

### Weekly Tasks
- Review email delivery rates
- Update templates based on feedback
- Clean up old email history (optional)

### Monthly Tasks
- Review and optimize email templates
- Check SMTP provider limits
- Update PHPMailer library

---

## Cost Estimation

### Development Time
- Phase 1-2: 1 day (Dependencies & Database)
- Phase 3: 2 days (Backend Implementation)
- Phase 4: 2 days (Frontend CMS)
- Phase 5: 1 day (Templates)
- Phase 6-7: 2 days (Security & Testing)
- **Total: 8 days** (1 senior developer)

### Ongoing Costs
- **Gmail SMTP:** Free (500 emails/day limit)
- **SendGrid:** Free tier (100 emails/day)
- **Mailgun:** Free tier (5,000 emails/month)
- **TinyMCE:** Free (with branding) or $49/month (premium)

---

## Success Metrics

1. **Email Delivery Rate:** >95%
2. **Response Time:** <24 hours
3. **User Satisfaction:** Reduced manual email client usage
4. **Error Rate:** <2%
5. **Template Usage:** >60% of emails use templates

---

## Future Enhancements

1. **Email Scheduling:** Schedule emails for later
2. **Bulk Email:** Send to multiple recipients
3. **Email Analytics:** Track open rates, click rates
4. **Attachments:** Support file attachments
5. **Email Signatures:** Custom signatures per user
6. **Auto-Reply:** Automatic acknowledgment emails
7. **Email Threading:** Group related emails
8. **Mobile App:** Send emails from mobile CMS

---

## Conclusion

This implementation provides a professional, secure, and user-friendly email system integrated directly into the CMS. The rich text editor allows for formatted, professional emails, while templates ensure consistency and save time. The system is scalable, maintainable, and follows industry best practices.

**Recommended Next Steps:**
1. Review and approve this plan
2. Set up SMTP provider account
3. Begin Phase 1 implementation
4. Schedule testing with real users
5. Deploy to production with monitoring

---

**Document Version:** 1.0  
**Prepared By:** Senior Backend Developer  
**Review Status:** Pending Approval  
**Estimated Completion:** 8 working days
