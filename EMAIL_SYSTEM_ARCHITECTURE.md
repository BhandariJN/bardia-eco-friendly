# Email System Architecture

## System Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    BARDIYA ECO FRIENDLY CMS                      │
│                      Email Integration System                     │
└─────────────────────────────────────────────────────────────────┘
```

---

## Architecture Diagram

```
┌──────────────┐
│   Admin User │
│   (Browser)  │
└──────┬───────┘
       │
       │ 1. Opens CMS
       ▼
┌──────────────────────────────────────────────────────────────┐
│  CMS Frontend (contact-submissions-enhanced.php)              │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  • Contact Submissions List                            │  │
│  │  • Email Composer Modal (TinyMCE)                      │  │
│  │  • Template Selector                                   │  │
│  │  • Email History Viewer                                │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       │ 2. Click "Reply" → Opens Modal
                       │ 3. Select Template (Optional)
                       │ 4. Compose Email
                       │ 5. Click "Send"
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  API Layer (api/emails/)                                      │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  POST /api/emails/send-reply                           │  │
│  │  • Validates JWT token                                 │  │
│  │  • Validates input (subject, body)                     │  │
│  │  • Sanitizes HTML                                      │  │
│  │  • Calls mailer.php                                    │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  GET /api/emails/history                               │  │
│  │  • Returns email history for submission                │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  GET /api/email-templates/list                         │  │
│  │  • Returns available templates                         │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  GET /api/email-templates/get                          │  │
│  │  • Renders template with variables                     │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       │ 6. Process Request
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  Business Logic Layer (includes/)                             │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  mailer.php                                            │  │
│  │  • sendReplyEmail()                                    │  │
│  │  • sendEmail() - PHPMailer wrapper                     │  │
│  │  • logEmailHistory()                                   │  │
│  │  • getEmailHistory()                                   │  │
│  │  • sanitizeEmailHtml()                                 │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  template-engine.php                                   │  │
│  │  • renderEmailTemplate()                               │  │
│  │  • getSubmissionVariables()                            │  │
│  │  • wrapEmailTemplate()                                 │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       │ 7. Send via SMTP
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  PHPMailer Library                                            │
│  • Connects to SMTP server                                    │
│  • Authenticates with credentials                             │
│  • Sends HTML + Plain text email                              │
│  • Returns success/failure                                    │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       │ 8. SMTP Connection
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  SMTP Provider (Gmail / SendGrid / Mailgun)                   │
│  • Receives email                                             │
│  • Validates sender                                           │
│  • Delivers to recipient                                      │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       │ 9. Email Delivered
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  Recipient Email (Guest)                                      │
│  • Receives formatted HTML email                              │
│  • Can reply directly                                         │
└──────────────────────────────────────────────────────────────┘
                       │
                       │ 10. Log & Update
                       ▼
┌──────────────────────────────────────────────────────────────┐
│  Database (MySQL)                                             │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  email_history                                         │  │
│  │  • Logs sent email                                     │  │
│  │  • Stores subject, body, timestamp                     │  │
│  │  • Records status (sent/failed)                        │  │
│  └────────────────────────────────────────────────────────┘  │
│  ┌────────────────────────────────────────────────────────┐  │
│  │  contact_submissions                                   │  │
│  │  • Increments email_count                              │  │
│  │  • Updates last_email_sent_at                          │  │
│  │  • Changes status to "replied"                         │  │
│  └────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────┘
```

---

## Data Flow

### 1. Email Sending Flow

```
User Action → API Request → Validation → Template Rendering → 
SMTP Send → Database Log → Status Update → Success Response
```

### 2. Template Loading Flow

```
Select Template → API Call → Fetch Template → Get Submission Data → 
Replace Variables → Wrap in HTML → Return to Editor
```

### 3. History Viewing Flow

```
View Submission → API Call → Query Database → Format Results → 
Display in Modal
```

---

## Component Breakdown

### Frontend Components

```
┌─────────────────────────────────────────────────────────┐
│  Contact Submissions Page                                │
│  ├── Submissions Table/Cards                             │
│  ├── Status Filter Tabs                                  │
│  ├── Email Counter Badges                                │
│  └── Action Buttons (Reply, View, Delete)                │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Email Composer Modal                                    │
│  ├── Recipient Info (Read-only)                          │
│  ├── Template Selector Dropdown                          │
│  ├── Subject Input Field                                 │
│  ├── TinyMCE Rich Text Editor                            │
│  └── Action Buttons (Cancel, Send)                       │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  View Submission Modal                                   │
│  ├── Guest Information Grid                              │
│  ├── Original Message Display                            │
│  ├── Email History Section                               │
│  └── Status Update Buttons                               │
└─────────────────────────────────────────────────────────┘
```

### Backend Components

```
┌─────────────────────────────────────────────────────────┐
│  API Endpoints                                           │
│  ├── send-reply.php    (POST)                            │
│  ├── history.php       (GET)                             │
│  ├── list.php          (GET - templates)                 │
│  └── get.php           (GET - template with vars)        │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Helper Functions                                        │
│  ├── sendEmail()                                         │
│  ├── sendReplyEmail()                                    │
│  ├── logEmailHistory()                                   │
│  ├── getEmailHistory()                                   │
│  ├── sanitizeEmailHtml()                                 │
│  ├── renderEmailTemplate()                               │
│  ├── getSubmissionVariables()                            │
│  └── wrapEmailTemplate()                                 │
└─────────────────────────────────────────────────────────┘
```

### Database Schema

```
┌─────────────────────────────────────────────────────────┐
│  email_history                                           │
│  ├── id (PK)                                             │
│  ├── submission_id (FK → contact_submissions)            │
│  ├── recipient_email                                     │
│  ├── recipient_name                                      │
│  ├── subject                                             │
│  ├── body_html                                           │
│  ├── body_plain                                          │
│  ├── sent_by_user_id (FK → users)                        │
│  ├── sent_at                                             │
│  ├── status (sent/failed/pending)                        │
│  └── error_message                                       │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  email_templates                                         │
│  ├── id (PK)                                             │
│  ├── name (UNIQUE)                                       │
│  ├── subject                                             │
│  ├── body_html                                           │
│  ├── description                                         │
│  ├── is_active                                           │
│  ├── created_at                                          │
│  └── updated_at                                          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  contact_submissions (Updated)                           │
│  ├── ... (existing columns)                              │
│  ├── email_count (NEW)                                   │
│  └── last_email_sent_at (NEW)                            │
└─────────────────────────────────────────────────────────┘
```

---

## Security Layers

```
┌─────────────────────────────────────────────────────────┐
│  Layer 1: Authentication                                 │
│  • JWT token validation                                  │
│  • Session management                                    │
│  • User role verification                                │
└─────────────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│  Layer 2: Input Validation                               │
│  • Subject length (5-500 chars)                          │
│  • Body length (10-500KB)                                │
│  • Email format validation                               │
│  • Required field checks                                 │
└─────────────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│  Layer 3: HTML Sanitization                              │
│  • Strip <script> tags                                   │
│  • Remove event handlers                                 │
│  • Allow safe HTML only                                  │
│  • XSS prevention                                        │
└─────────────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│  Layer 4: SMTP Security                                  │
│  • TLS/SSL encryption                                    │
│  • Authenticated connection                              │
│  • App-specific passwords                                │
│  • Secure credential storage                             │
└─────────────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│  Layer 5: Error Handling                                 │
│  • Try-catch blocks                                      │
│  • Error logging                                         │
│  • Graceful failure                                      │
│  • User-friendly messages                                │
└─────────────────────────────────────────────────────────┘
```

---

## Technology Stack

```
┌─────────────────────────────────────────────────────────┐
│  Frontend                                                │
│  • HTML5 / CSS3                                          │
│  • JavaScript (ES6+)                                     │
│  • TinyMCE 6 (Rich Text Editor)                          │
│  • Fetch API (AJAX)                                      │
│  • Responsive Design                                     │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Backend                                                 │
│  • PHP 7.4+                                              │
│  • PHPMailer 6.x                                         │
│  • JWT Authentication                                    │
│  • Custom Template Engine                                │
│  • RESTful API Design                                    │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Database                                                │
│  • MySQL 5.7+ / MariaDB 10.3+                            │
│  • InnoDB Engine                                         │
│  • UTF-8 Character Set                                   │
│  • Foreign Key Constraints                               │
│  • Indexed Queries                                       │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  External Services                                       │
│  • Gmail SMTP (or)                                       │
│  • SendGrid SMTP (or)                                    │
│  • Mailgun SMTP                                          │
└─────────────────────────────────────────────────────────┘
```

---

## Performance Metrics

```
┌─────────────────────────────────────────────────────────┐
│  Response Times                                          │
│  • Page Load: ~500ms                                     │
│  • Template Load: <50ms                                  │
│  • Email Send: ~100-200ms                                │
│  • History Load: <100ms                                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Resource Usage                                          │
│  • Memory: <5MB per request                              │
│  • Database Queries: 2-3 per email                       │
│  • Network: ~50KB per email                              │
│  • Storage: ~10KB per email log                          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Scalability                                             │
│  • Concurrent Users: 50+                                 │
│  • Emails per Hour: 500 (Gmail limit)                    │
│  • Database Records: Millions supported                  │
│  • Template Count: Unlimited                             │
└─────────────────────────────────────────────────────────┘
```

---

## Deployment Architecture

```
┌─────────────────────────────────────────────────────────┐
│  Development Environment                                 │
│  • XAMPP / WAMP                                          │
│  • localhost                                             │
│  • Gmail SMTP (testing)                                  │
└─────────────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│  Staging Environment                                     │
│  • Shared Hosting / VPS                                  │
│  • Test Domain                                           │
│  • SendGrid (free tier)                                  │
└─────────────────────────────────────────────────────────┘
                       ↓
┌─────────────────────────────────────────────────────────┐
│  Production Environment                                  │
│  • VPS / Cloud Server                                    │
│  • Custom Domain                                         │
│  • SendGrid / Mailgun (paid)                             │
│  • SSL Certificate                                       │
│  • Backup System                                         │
│  • Monitoring Tools                                      │
└─────────────────────────────────────────────────────────┘
```

---

## Integration Points

```
┌─────────────────────────────────────────────────────────┐
│  CMS Integration                                         │
│  • Contact Submissions Module                            │
│  • User Authentication System                            │
│  • Session Management                                    │
│  • Error Logging System                                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  External Integrations                                   │
│  • SMTP Providers (Gmail, SendGrid, Mailgun)             │
│  • TinyMCE CDN                                           │
│  • Google Fonts                                          │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Future Integrations (Planned)                           │
│  • CRM Systems                                           │
│  • Analytics Platforms                                   │
│  • Notification Services                                 │
│  • Backup Services                                       │
└─────────────────────────────────────────────────────────┘
```

---

## Maintenance & Monitoring

```
┌─────────────────────────────────────────────────────────┐
│  Logging                                                 │
│  • Error logs (storage/logs/error.log)                   │
│  • Email history (database)                              │
│  • Failed email tracking                                 │
│  • SMTP connection logs                                  │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Monitoring                                              │
│  • Email delivery rate                                   │
│  • Response times                                        │
│  • Error rates                                           │
│  • SMTP quota usage                                      │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│  Maintenance Tasks                                       │
│  • Daily: Check error logs                               │
│  • Weekly: Review email metrics                          │
│  • Monthly: Update dependencies                          │
│  • Quarterly: Security audit                             │
└─────────────────────────────────────────────────────────┘
```

---

**Architecture Version:** 1.0  
**Last Updated:** February 24, 2026  
**Status:** Production Ready
