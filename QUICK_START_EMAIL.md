# Email System - Quick Start Guide

## 🚀 5-Minute Setup

### 1. Install PHPMailer
```bash
composer require phpmailer/phpmailer
```

### 2. Run Installer
Visit: `http://localhost/bardia-eco-friendly/install-email-system.php`

Or run:
```bash
php install-email-system.php
```

### 3. Configure Gmail

**Get App Password:**
1. Go to: https://myaccount.google.com/apppasswords
2. Generate password for "Mail"
3. Copy 16-character code

**Update .env:**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=paste-16-char-code-here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=info@bardiyaecofriendly.com
MAIL_FROM_NAME=Bardiya Eco Friendly
```

### 4. Test It!
1. Go to: `http://localhost/bardia-eco-friendly/cms/contact-submissions-enhanced.php`
2. Login: `admin` / `admin123`
3. Click "✉️ Reply" on any submission
4. Select a template
5. Click "📤 Send Email"

---

## 📧 How to Send Email

### From CMS:
1. **Open Submissions** → `cms/contact-submissions-enhanced.php`
2. **Click Reply** → "✉️ Reply" button
3. **Choose Template** → Optional, select from dropdown
4. **Edit Message** → Use rich text editor
5. **Send** → Click "📤 Send Email"

### Using API:
```bash
curl -X POST http://localhost/bardia-eco-friendly/api/emails/send-reply \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN" \
  -d '{
    "submission_id": 5,
    "subject": "Re: Your Enquiry",
    "body_html": "<p>Dear Guest,</p><p>Thank you...</p>"
  }'
```

---

## 📋 Email Templates

### Available Templates:
1. **Booking Enquiry Response** - For new booking requests
2. **General Enquiry Response** - For general questions
3. **Booking Confirmation** - For confirmed bookings

### Template Variables:
```
{{guest_name}}        → Guest full name
{{guest_email}}       → Guest email
{{guest_phone}}       → Guest phone
{{num_guests}}        → Number of guests
{{preferred_package}} → Package name
{{travel_dates}}      → Travel dates
{{message}}           → Original message
{{admin_name}}        → Your name
{{company_name}}      → Bardiya Eco Friendly
{{company_email}}     → Company email
{{company_phone}}     → Company phone
```

---

## 🔧 Troubleshooting

### Email Not Sending?

**Check 1: SMTP Settings**
```env
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_ENCRYPTION=tls
```

**Check 2: App Password**
- Must be 16 characters
- No spaces
- Generated from Google Account

**Check 3: 2FA Enabled**
- Gmail requires 2-Factor Authentication
- Enable at: https://myaccount.google.com/security

**Check 4: Error Logs**
```bash
cat storage/logs/error.log
```

### Common Errors:

**"Class 'PHPMailer' not found"**
```bash
composer require phpmailer/phpmailer
composer dump-autoload
```

**"Table 'email_history' doesn't exist"**
```bash
php install-email-system.php
```

**"SMTP connect() failed"**
- Check internet connection
- Verify SMTP credentials
- Try port 465 with SSL

---

## 📊 Features

✅ Rich text editor (TinyMCE)  
✅ Email templates with variables  
✅ Email history tracking  
✅ Mobile responsive  
✅ Auto-status update  
✅ HTML & plain text support  
✅ Error logging  
✅ Secure (JWT auth)  

---

## 🔗 Quick Links

- **CMS Email Page:** `cms/contact-submissions-enhanced.php`
- **Installation Script:** `install-email-system.php`
- **Full Documentation:** `EMAIL_SETUP_INSTRUCTIONS.md`
- **API Docs:** `API_DOCUMENTATION.md`
- **Implementation Plan:** `EMAIL_INTEGRATION_PLAN.md`

---

## 📞 Support

**Issue?** Check:
1. `.env` configuration
2. `storage/logs/error.log`
3. Database tables exist
4. PHPMailer installed

**Still stuck?**
- Review `EMAIL_SETUP_INSTRUCTIONS.md`
- Check Gmail security settings
- Test SMTP connection manually

---

## ✨ Pro Tips

1. **Use Templates** - Saves time and ensures consistency
2. **Personalize** - Edit template before sending
3. **Preview** - Check formatting before sending
4. **Track History** - View sent emails in submission details
5. **Respond Fast** - Aim for <24 hour response time

---

**Ready to go!** 🎉

Start sending professional emails directly from your CMS.
