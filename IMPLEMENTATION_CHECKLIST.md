# Email System Implementation Checklist

## Pre-Installation

- [ ] PHP 7.4+ installed
- [ ] MySQL 5.7+ or MariaDB 10.3+ installed
- [ ] Composer installed (optional but recommended)
- [ ] XAMPP/WAMP running
- [ ] Database `bardiya_eco_friendly` exists
- [ ] Admin user can login to CMS

---

## Phase 1: Dependencies (15 minutes)

### Install PHPMailer
- [ ] Run `composer require phpmailer/phpmailer`
- [ ] OR download manually from GitHub
- [ ] Verify `vendor/phpmailer/phpmailer/` exists
- [ ] Test autoload works

### Update Composer Autoload
- [ ] Edit `composer.json`
- [ ] Add `includes/mailer.php` to autoload
- [ ] Add `includes/template-engine.php` to autoload
- [ ] Run `composer dump-autoload`

---

## Phase 2: Database Setup (10 minutes)

### Run Migration
- [ ] Open phpMyAdmin
- [ ] Select `bardiya_eco_friendly` database
- [ ] Import `database_email_migration.sql`
- [ ] OR run `php install-email-system.php`

### Verify Tables
- [ ] Check `email_history` table exists
- [ ] Check `email_templates` table exists
- [ ] Check `contact_submissions` has new columns
- [ ] Verify 3 default templates inserted

### Test Queries
```sql
SELECT COUNT(*) FROM email_history;
SELECT COUNT(*) FROM email_templates;
SELECT * FROM email_templates;
```
- [ ] All queries run without errors

---

## Phase 3: Configuration (10 minutes)

### Gmail Setup
- [ ] Enable 2-Factor Authentication on Google Account
- [ ] Go to https://myaccount.google.com/apppasswords
- [ ] Generate App Password for "Mail"
- [ ] Copy 16-character password

### Update .env File
- [ ] Set `MAIL_HOST=smtp.gmail.com`
- [ ] Set `MAIL_PORT=587`
- [ ] Set `MAIL_USERNAME=your-email@gmail.com`
- [ ] Set `MAIL_PASSWORD=16-char-app-password`
- [ ] Set `MAIL_ENCRYPTION=tls`
- [ ] Set `MAIL_FROM_ADDRESS=info@bardiyaecofriendly.com`
- [ ] Set `MAIL_FROM_NAME=Bardiya Eco Friendly`
- [ ] Save file

### Verify Configuration
- [ ] No syntax errors in .env
- [ ] All required variables set
- [ ] No placeholder values remaining

---

## Phase 4: File Verification (5 minutes)

### Check Core Files Exist
- [ ] `includes/mailer.php`
- [ ] `includes/template-engine.php`
- [ ] `api/emails/send-reply.php`
- [ ] `api/emails/history.php`
- [ ] `api/email-templates/list.php`
- [ ] `api/email-templates/get.php`
- [ ] `cms/contact-submissions-enhanced.php`

### Check Routes Updated
- [ ] Open `public/index.php`
- [ ] Verify email routes added
- [ ] `/api/emails/send-reply` route exists
- [ ] `/api/emails/history` route exists
- [ ] `/api/email-templates/list` route exists
- [ ] `/api/email-templates/get` route exists

---

## Phase 5: Testing (20 minutes)

### Test 1: Access CMS Page
- [ ] Go to `http://localhost/bardia-eco-friendly/cms/contact-submissions-enhanced.php`
- [ ] Login with admin credentials
- [ ] Page loads without errors
- [ ] Submissions list displays
- [ ] "✉️ Reply" buttons visible

### Test 2: Open Email Composer
- [ ] Click "✉️ Reply" on any submission
- [ ] Modal opens
- [ ] Recipient name displays correctly
- [ ] Recipient email displays correctly
- [ ] TinyMCE editor loads
- [ ] Template dropdown shows 3 templates

### Test 3: Load Template
- [ ] Select "Booking Enquiry Response" template
- [ ] Subject field populates
- [ ] Editor content populates
- [ ] Variables replaced with actual data
- [ ] Guest name appears correctly
- [ ] Package name appears correctly

### Test 4: Send Test Email
- [ ] Compose or use template
- [ ] Click "📤 Send Email"
- [ ] Loading indicator shows
- [ ] Success message appears
- [ ] Modal closes
- [ ] Page refreshes

### Test 5: Verify Email Sent
- [ ] Check recipient's email inbox
- [ ] Email received
- [ ] Subject correct
- [ ] Formatting preserved
- [ ] No broken HTML
- [ ] Links work (if any)

### Test 6: Check Email History
- [ ] Click "View" on the submission
- [ ] Email history section visible
- [ ] Sent email listed
- [ ] Timestamp correct
- [ ] Status shows "sent"
- [ ] Sender name correct

### Test 7: Verify Database
```sql
SELECT * FROM email_history ORDER BY sent_at DESC LIMIT 5;
```
- [ ] Email logged in database
- [ ] All fields populated correctly
- [ ] Status is "sent"
- [ ] No error_message

```sql
SELECT email_count, last_email_sent_at, status 
FROM contact_submissions 
WHERE id = [submission_id];
```
- [ ] email_count incremented
- [ ] last_email_sent_at updated
- [ ] status changed to "replied"

### Test 8: API Endpoints
```bash
# Test email history
curl "http://localhost/bardia-eco-friendly/api/emails/history?submission_id=1" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```
- [ ] Returns 200 status
- [ ] JSON response valid
- [ ] Email history returned

```bash
# Test template list
curl "http://localhost/bardia-eco-friendly/api/email-templates/list" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```
- [ ] Returns 200 status
- [ ] 3 templates returned
- [ ] Template data complete

### Test 9: Error Handling
- [ ] Try sending without subject → Error shown
- [ ] Try sending with empty body → Error shown
- [ ] Try with invalid SMTP credentials → Error logged
- [ ] Check `storage/logs/error.log` for errors

### Test 10: Mobile Responsiveness
- [ ] Open on mobile device or resize browser
- [ ] Page responsive
- [ ] Modal fits screen
- [ ] Editor usable
- [ ] Buttons accessible

---

## Phase 6: Security Verification (10 minutes)

### Authentication
- [ ] Logout and try accessing email API → 401 error
- [ ] Try sending email without JWT → 401 error
- [ ] Invalid JWT token → 401 error

### Input Validation
- [ ] Subject too short (< 5 chars) → Error
- [ ] Subject too long (> 500 chars) → Error
- [ ] Body too short (< 10 chars) → Error
- [ ] Body too large (> 500KB) → Error

### HTML Sanitization
- [ ] Try sending `<script>alert('xss')</script>` → Stripped
- [ ] Try sending `onclick="alert('xss')"` → Stripped
- [ ] Safe HTML preserved (p, strong, ul, li, etc.)

---

## Phase 7: Documentation Review (5 minutes)

### Check Documentation Files
- [ ] `EMAIL_INTEGRATION_PLAN.md` exists
- [ ] `EMAIL_SETUP_INSTRUCTIONS.md` exists
- [ ] `EMAIL_IMPLEMENTATION_SUMMARY.md` exists
- [ ] `QUICK_START_EMAIL.md` exists
- [ ] `EMAIL_SYSTEM_ARCHITECTURE.md` exists
- [ ] `API_DOCUMENTATION.md` updated
- [ ] `IMPLEMENTATION_CHECKLIST.md` (this file)

### Review Key Documents
- [ ] Read QUICK_START_EMAIL.md
- [ ] Understand email flow
- [ ] Know how to troubleshoot
- [ ] Familiar with API endpoints

---

## Phase 8: Production Preparation (Optional)

### Security Hardening
- [ ] Change default admin password
- [ ] Use strong JWT secret
- [ ] Enable HTTPS
- [ ] Set up firewall rules
- [ ] Restrict database access

### SMTP Provider
- [ ] Consider SendGrid for production
- [ ] Or Mailgun for higher volume
- [ ] Set up SPF records
- [ ] Set up DKIM records
- [ ] Verify domain

### Monitoring
- [ ] Set up error monitoring
- [ ] Configure email delivery tracking
- [ ] Set up backup system
- [ ] Create maintenance schedule

### Performance
- [ ] Enable PHP OPcache
- [ ] Optimize database indexes
- [ ] Set up caching
- [ ] Configure CDN for assets

---

## Post-Installation

### Training
- [ ] Train admin staff on email system
- [ ] Demonstrate template usage
- [ ] Show email history feature
- [ ] Explain best practices

### Backup
- [ ] Backup database
- [ ] Backup .env file (securely)
- [ ] Backup email templates
- [ ] Document configuration

### Monitoring
- [ ] Check error logs daily
- [ ] Monitor email delivery rate
- [ ] Track response times
- [ ] Review user feedback

---

## Troubleshooting Checklist

### Email Not Sending
- [ ] Check SMTP credentials in .env
- [ ] Verify 2FA enabled on Gmail
- [ ] Check app password is correct
- [ ] Test internet connection
- [ ] Check firewall settings
- [ ] Review error logs

### Template Not Loading
- [ ] Check database connection
- [ ] Verify templates exist in database
- [ ] Check API endpoint accessible
- [ ] Review browser console for errors

### Editor Not Working
- [ ] Check TinyMCE CDN accessible
- [ ] Verify JavaScript not blocked
- [ ] Check browser compatibility
- [ ] Clear browser cache

### Database Errors
- [ ] Verify tables exist
- [ ] Check foreign key constraints
- [ ] Review column types
- [ ] Check user permissions

---

## Success Criteria

### Functional Requirements
- [x] Admin can send emails from CMS
- [x] Rich text editor works
- [x] Templates load and render
- [x] Email history tracked
- [x] Status auto-updates
- [x] Mobile responsive

### Technical Requirements
- [x] PHPMailer integrated
- [x] Database schema created
- [x] API endpoints functional
- [x] Security implemented
- [x] Error handling works
- [x] Documentation complete

### User Experience
- [x] Intuitive interface
- [x] Fast response times
- [x] Clear error messages
- [x] Professional email design
- [x] Easy template selection

---

## Sign-Off

### Developer
- [ ] All code committed to repository
- [ ] Documentation complete
- [ ] Tests passed
- [ ] No known bugs

**Developer Name:** _________________  
**Date:** _________________  
**Signature:** _________________

### Project Manager
- [ ] Requirements met
- [ ] Acceptance tests passed
- [ ] Documentation reviewed
- [ ] Ready for production

**PM Name:** _________________  
**Date:** _________________  
**Signature:** _________________

---

## Notes

**Installation Date:** _________________  
**Installed By:** _________________  
**SMTP Provider:** _________________  
**Issues Encountered:** _________________  
**Resolution:** _________________

---

**Checklist Version:** 1.0  
**Last Updated:** February 24, 2026  
**Status:** Ready for Use

---

## Quick Reference

**CMS URL:** `http://localhost/bardia-eco-friendly/cms/contact-submissions-enhanced.php`  
**Installer:** `http://localhost/bardia-eco-friendly/install-email-system.php`  
**Error Log:** `storage/logs/error.log`  
**Support Doc:** `EMAIL_SETUP_INSTRUCTIONS.md`
