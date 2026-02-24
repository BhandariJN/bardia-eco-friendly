# Email Integration Setup Instructions

## Quick Start Guide

### Step 1: Install PHPMailer

Run this command in your project root:

```bash
composer require phpmailer/phpmailer
```

**If composer is not available**, download PHPMailer manually:
1. Download from: https://github.com/PHPMailer/PHPMailer/releases
2. Extract to `vendor/phpmailer/phpmailer/`

### Step 2: Run Database Migration

Import the email tables into your database:

```bash
mysql -u root -p bardiya_eco_friendly < database_email_migration.sql
```

Or via phpMyAdmin:
1. Open http://localhost/phpmyadmin
2. Select `bardiya_eco_friendly` database
3. Click "Import" tab
4. Choose `database_email_migration.sql`
5. Click "Go"

### Step 3: Configure Email Settings

Edit your `.env` file and update these values:

```env
# For Gmail SMTP
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@bardiyaecofriendly.com
MAIL_FROM_NAME=Bardiya Eco Friendly
```

### Step 4: Generate Gmail App Password

1. Go to your Google Account: https://myaccount.google.com/
2. Click "Security" in the left menu
3. Enable "2-Step Verification" if not already enabled
4. Go to "App passwords": https://myaccount.google.com/apppasswords
5. Select "Mail" and "Other (Custom name)"
6. Enter "Bardiya CMS" as the name
7. Click "Generate"
8. Copy the 16-character password
9. Paste it in `.env` as `MAIL_PASSWORD`

### Step 5: Update Autoload

Edit `composer.json` and add the new files:

```json
"autoload": {
    "files": [
        "includes/config.php",
        "includes/functions.php",
        "includes/jwt.php",
        "includes/auth.php",
        "includes/mailer.php",
        "includes/template-engine.php"
    ]
}
```

Then run:
```bash
composer dump-autoload
```

### Step 6: Access the Enhanced CMS

Open in your browser:
```
http://localhost/bardia-eco-friendly/cms/contact-submissions-enhanced.php
```

Login with:
- Username: `admin`
- Password: `admin123`

---

## Features Available

✅ **Rich Text Email Editor** - Format emails with bold, italic, lists, links  
✅ **Email Templates** - 3 pre-loaded templates with variable replacement  
✅ **Email History** - Track all sent emails per submission  
✅ **Status Auto-Update** - Automatically marks as "replied" when email sent  
✅ **Email Counter** - Shows how many emails sent to each contact  
✅ **Mobile Responsive** - Works on all devices  

---

## Testing the Email System

### Test 1: Send a Test Email

1. Go to Contact Submissions page
2. Click "✉️ Reply" on any submission
3. Select a template or write your own message
4. Click "📤 Send Email"
5. Check if email arrives in recipient's inbox

### Test 2: Check Email History

1. Click "View" on a submission that has emails sent
2. Scroll down to "📧 Email History" section
3. Verify sent emails are listed

### Test 3: Verify Database

Run this query in phpMyAdmin:

```sql
SELECT * FROM email_history ORDER BY sent_at DESC LIMIT 10;
```

You should see your sent emails logged.

---

## Troubleshooting

### Error: "Failed to send email"

**Solution 1: Check Gmail Settings**
- Make sure 2-Factor Authentication is enabled
- Generate a new App Password
- Update `.env` with the new password

**Solution 2: Check SMTP Settings**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Solution 3: Enable Less Secure Apps (Not Recommended)**
- Go to: https://myaccount.google.com/lesssecureapps
- Turn ON "Allow less secure apps"
- Use your regular Gmail password

### Error: "Class 'PHPMailer' not found"

**Solution:**
```bash
composer require phpmailer/phpmailer
composer dump-autoload
```

### Error: "Table 'email_history' doesn't exist"

**Solution:**
Run the database migration:
```bash
mysql -u root -p bardiya_eco_friendly < database_email_migration.sql
```

### Emails go to Spam

**Solution:**
1. Add SPF record to your domain DNS
2. Use a professional email address (not Gmail)
3. Consider using SendGrid or Mailgun for production

---

## Alternative SMTP Providers

### SendGrid (Recommended for Production)

1. Sign up: https://sendgrid.com/
2. Get API Key from Settings > API Keys
3. Update `.env`:

```env
MAIL_HOST=smtp.sendgrid.net
MAIL_PORT=587
MAIL_USERNAME=apikey
MAIL_PASSWORD=your-sendgrid-api-key
MAIL_ENCRYPTION=tls
```

**Free Tier:** 100 emails/day

### Mailgun

1. Sign up: https://www.mailgun.com/
2. Get SMTP credentials from Sending > Domain Settings
3. Update `.env`:

```env
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=postmaster@your-domain.mailgun.org
MAIL_PASSWORD=your-mailgun-password
MAIL_ENCRYPTION=tls
```

**Free Tier:** 5,000 emails/month

---

## API Endpoints

### Send Email Reply
```
POST /api/emails/send-reply
Authorization: Bearer <jwt_token>

{
  "submission_id": 5,
  "subject": "Re: Your Enquiry",
  "body_html": "<p>Dear John,</p><p>Thank you...</p>",
  "body_plain": "Dear John, Thank you..."
}
```

### Get Email History
```
GET /api/emails/history?submission_id=5
Authorization: Bearer <jwt_token>
```

### List Email Templates
```
GET /api/email-templates/list
Authorization: Bearer <jwt_token>
```

### Get Template with Variables
```
GET /api/email-templates/get?template_id=1&submission_id=5
Authorization: Bearer <jwt_token>
```

---

## Email Templates

### Available Variables

Use these in your templates:

- `{{guest_name}}` - Guest full name
- `{{guest_email}}` - Guest email
- `{{guest_phone}}` - Guest phone
- `{{num_guests}}` - Number of guests
- `{{preferred_package}}` - Package name
- `{{travel_dates}}` - Travel dates
- `{{message}}` - Original message
- `{{admin_name}}` - Your name
- `{{company_name}}` - Bardiya Eco Friendly
- `{{company_email}}` - Company email
- `{{company_phone}}` - Company phone

### Example Template

```html
<p>Dear {{guest_name}},</p>

<p>Thank you for your interest in {{preferred_package}}!</p>

<p>We have received your enquiry for {{num_guests}} during {{travel_dates}}.</p>

<p>Best regards,<br>
{{admin_name}}<br>
{{company_name}}</p>
```

---

## Security Notes

⚠️ **Important Security Measures:**

1. **Never commit `.env` file** - Add to `.gitignore`
2. **Use App Passwords** - Never use your main Gmail password
3. **Enable 2FA** - Always use Two-Factor Authentication
4. **Rate Limiting** - Implemented (50 emails/hour per user)
5. **HTML Sanitization** - Dangerous tags are stripped
6. **JWT Authentication** - All endpoints require valid token

---

## Production Checklist

Before going live:

- [ ] Change default admin password
- [ ] Use professional SMTP provider (SendGrid/Mailgun)
- [ ] Set up SPF and DKIM records
- [ ] Test email delivery to Gmail, Outlook, Yahoo
- [ ] Enable error logging
- [ ] Set up email monitoring
- [ ] Configure backup SMTP server
- [ ] Test on mobile devices
- [ ] Review email templates
- [ ] Train staff on email system

---

## Support

For issues or questions:
- Check the troubleshooting section above
- Review `storage/logs/error.log` for errors
- Test SMTP connection manually
- Verify database tables exist

---

**Setup Complete!** 🎉

You now have a professional email system integrated into your CMS.
