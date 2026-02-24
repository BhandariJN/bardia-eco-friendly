# Bardiya Eco Friendly - Frontend API Documentation

**Version:** 1.0  
**Base URL:** `http://localhost/bardiya-eco-friendly/api`  
**Authentication:** JWT Bearer Token (where required)

---

## Table of Contents

1. [Authentication](#authentication)
2. [Homestays](#homestays)
3. [Bookings](#bookings)
4. [Pages (CMS)](#pages-cms)
5. [Package Categories](#package-categories)
6. [Packages](#packages)
7. [Package Features](#package-features)
8. [Gallery Categories](#gallery-categories)
9. [Gallery Images](#gallery-images)
10. [Contact Methods](#contact-methods)
11. [Contact Submissions](#contact-submissions)
12. [Social Links](#social-links)
13. [Email System](#email-system)
14. [Error Handling](#error-handling)

---

## Authentication

### Login

**Endpoint:** `POST /api/auth/login`  
**Authentication:** None (Public)  
**Description:** Authenticate admin user and receive JWT token

**Request Body:**
```json
{
  "username": "admin",
  "password": "admin123"
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Login successful",
  "data": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
      "id": 1,
      "username": "admin",
      "role": "admin"
    }
  }
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Username and password are required."
}
```

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Invalid username or password."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use POST."
}
```

---

### Verify Token

**Endpoint:** `GET /api/auth/verify`  
**Authentication:** Required (Bearer Token)  
**Description:** Verify if the current JWT token is valid

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Token is valid",
  "data": {
    "user_id": 1,
    "role": "admin",
    "iat": 1709020800,
    "exp": 1709024400
  }
}
```

**Error Responses:**

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

```json
{
  "status": "error",
  "message": "Invalid or expired token."
}
```

---

## Homestays

### List All Homestays

**Endpoint:** `GET /api/homestays/list`  
**Authentication:** None (Public)  
**Description:** Retrieve all homestay properties

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Bardiya Jungle Lodge",
      "description": "An eco-friendly lodge nestled at the edge of Bardiya National Park...",
      "location": "Thakurdwara, Bardiya",
      "price_per_night": 2500.00,
      "max_guests": 4,
      "image_url": null,
      "is_available": true,
      "created_at": "2026-02-24 10:30:00",
      "updated_at": "2026-02-24 10:30:00"
    }
  ]
}
```

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch homestays."
}
```

---

## Bookings

### Create Booking

**Endpoint:** `POST /api/bookings/create`  
**Authentication:** Required (Bearer Token)  
**Description:** Create a new booking for a homestay

**Headers:**
```
Authorization: Bearer <your_jwt_token>
Content-Type: application/json
```

**Request Body:**
```json
{
  "homestay_id": 1,
  "guest_name": "John Doe",
  "guest_email": "john.doe@example.com",
  "guest_phone": "+977-9801234567",
  "check_in": "2026-03-15",
  "check_out": "2026-03-18",
  "guests_count": 2,
  "total_price": 7500.00
}
```

**Field Requirements:**
- `homestay_id` (required): Integer, must exist in homestays table
- `guest_name` (required): String
- `guest_email` (optional): String, valid email format
- `guest_phone` (optional): String
- `check_in` (required): Date in YYYY-MM-DD format
- `check_out` (required): Date in YYYY-MM-DD format
- `guests_count` (optional): Integer, defaults to 1
- `total_price` (optional): Decimal, defaults to 0.00

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Booking created successfully.",
  "data": {
    "id": 15
  }
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "homestay_id, guest_name, check_in, and check_out are required."
}
```

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

- **404 Not Found:**
```json
{
  "status": "error",
  "message": "Homestay not found."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use POST."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to create booking."
}
```

---

### List All Bookings

**Endpoint:** `GET /api/bookings/list`  
**Authentication:** Required (Bearer Token)  
**Description:** Retrieve all bookings with homestay details

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "homestay_id": 1,
      "homestay_name": "Bardiya Jungle Lodge",
      "guest_name": "Ram Sharma",
      "guest_email": "ram.sharma@example.com",
      "guest_phone": "+977-9801234567",
      "check_in": "2026-03-01",
      "check_out": "2026-03-03",
      "guests_count": 2,
      "total_price": 5000.00,
      "status": "confirmed",
      "created_at": "2026-02-20 14:30:00"
    }
  ]
}
```

**Booking Status Values:**
- `confirmed` - Booking is confirmed
- `pending` - Booking is pending confirmation
- `cancelled` - Booking has been cancelled
- `completed` - Booking has been completed

**Error Responses:**

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch bookings."
}
```

---

### Cancel Booking

**Endpoint:** `DELETE /api/bookings/cancel`  
**Authentication:** Required (Bearer Token)  
**Description:** Cancel an existing booking (soft delete - sets status to 'cancelled')

**Headers:**
```
Authorization: Bearer <your_jwt_token>
Content-Type: application/json
```

**Request Body:**
```json
{
  "id": 5
}
```

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Booking cancelled successfully."
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Valid booking ID is required."
}
```

```json
{
  "status": "error",
  "message": "Booking is already cancelled."
}
```

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

- **404 Not Found:**
```json
{
  "status": "error",
  "message": "Booking not found."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use DELETE."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to cancel booking."
}
```

---

## Pages (CMS)

### List All Pages

**Endpoint:** `GET /api/pages/list`  
**Authentication:** None (Public)  
**Description:** Retrieve all CMS pages (About, Contact, Terms, etc.)

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "title": "About Us",
      "slug": "about-us",
      "content": "<h2>Welcome to Bardiya Eco Friendly</h2><p>We are a community-driven initiative...</p>",
      "status": "published",
      "created_at": "2026-02-15 09:00:00",
      "updated_at": "2026-02-15 09:00:00"
    }
  ]
}
```

**Page Status Values:**
- `draft` - Page is in draft mode
- `published` - Page is published and visible
- `archived` - Page is archived

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch pages."
}
```

---

## Package Categories

### List All Package Categories

**Endpoint:** `GET /api/package-categories/list`  
**Authentication:** None (Public)  
**Description:** Retrieve all package category tabs (Homestay, Safari, etc.)

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Homestay",
      "slug": "homestay",
      "display_order": 1,
      "is_active": true
    },
    {
      "id": 2,
      "name": "Safari",
      "slug": "safari",
      "display_order": 2,
      "is_active": true
    }
  ]
}
```

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch package categories."
}
```

---

## Packages

### List All Packages

**Endpoint:** `GET /api/packages/list`  
**Authentication:** None (Public)  
**Description:** Retrieve all tour/stay packages with features

**Query Parameters:**
- `category_id` (optional): Filter packages by category ID

**Example Requests:**
- All packages: `GET /api/packages/list`
- Homestay packages only: `GET /api/packages/list?category_id=1`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "category_id": 1,
      "category_name": "Homestay",
      "category_slug": "homestay",
      "icon": "🏡",
      "name": "Rustic",
      "duration": "2 Nights · 3 Days",
      "price": 4500.00,
      "currency": "₹",
      "price_note": "Twin sharing",
      "description": "Experience authentic village life with modern comfort...",
      "is_featured": false,
      "display_order": 1,
      "is_active": true,
      "features": [
        "Accommodation in traditional homestay",
        "All meals included (breakfast, lunch, dinner)",
        "Guided village tour",
        "Cultural performance evening"
      ]
    }
  ]
}
```

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch packages."
}
```

---

## Package Features

### List Package Features

**Endpoint:** `GET /api/package-features/list`  
**Authentication:** None (Public)  
**Description:** Retrieve bullet-point features for a specific package

**Query Parameters:**
- `package_id` (required): Package ID to fetch features for

**Example Request:**
```
GET /api/package-features/list?package_id=1
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "package_id": 1,
      "feature_text": "Accommodation in traditional homestay",
      "display_order": 1
    },
    {
      "id": 2,
      "package_id": 1,
      "feature_text": "All meals included (breakfast, lunch, dinner)",
      "display_order": 2
    }
  ]
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Valid package_id query parameter is required."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch package features."
}
```

---

## Gallery Categories

### List All Gallery Categories

**Endpoint:** `GET /api/gallery-categories/list`  
**Authentication:** None (Public)  
**Description:** Retrieve all gallery category tabs

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "Gallery",
      "slug": "gallery",
      "display_order": 1,
      "is_active": true,
      "created_at": "2026-02-15 10:00:00"
    },
    {
      "id": 2,
      "name": "Wildlife",
      "slug": "wildlife",
      "display_order": 2,
      "is_active": true,
      "created_at": "2026-02-15 10:00:00"
    }
  ]
}
```

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch gallery categories."
}
```

---

## Gallery Images

### List Gallery Images

**Endpoint:** `GET /api/gallery-images/list`  
**Authentication:** None (Public)  
**Description:** Retrieve gallery images, optionally filtered by category

**Query Parameters:**
- `category_id` (optional): Filter images by category ID

**Example Requests:**
- All images: `GET /api/gallery-images/list`
- Wildlife images only: `GET /api/gallery-images/list?category_id=2`

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "categoryId": 1,
      "categorySlug": "gallery",
      "categoryName": "Gallery",
      "imageUrl": "/storage/gallery/1771863194_image.jpg",
      "altText": "Bardiya National Park landscape",
      "displayOrder": 1,
      "isActive": true,
      "createdAt": "2026-02-20 15:30:00"
    }
  ]
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "category_id must be a positive integer."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch gallery images."
}
```

---

## Contact Methods

### List Contact Methods

**Endpoint:** `GET /api/contact-methods/list`  
**Authentication:** None (Public)  
**Description:** Retrieve contact method cards (Call, Email, WhatsApp)

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "icon": "📞",
      "title": "Call Us",
      "detail": "+91 98765 43210",
      "href": "tel:+919876543210",
      "description": "Available 8 AM – 9 PM",
      "display_order": 1,
      "is_active": true
    },
    {
      "id": 2,
      "icon": "✉️",
      "title": "Email Us",
      "detail": "info@bardiyaecofriendly.com",
      "href": "mailto:info@bardiyaecofriendly.com",
      "description": "We'll respond within 24 hours",
      "display_order": 2,
      "is_active": true
    }
  ]
}
```

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch contact methods."
}
```

---

## Contact Submissions

### Submit Contact Form

**Endpoint:** `POST /api/contact-submissions/submit`  
**Authentication:** None (Public)  
**Description:** Submit an enquiry/contact form

**Request Body:**
```json
{
  "full_name": "Jane Smith",
  "email": "jane.smith@example.com",
  "phone": "+977-9812345678",
  "num_guests": "4 Guests",
  "preferred_package": "Deep Wild — Week (4N/5D)",
  "travel_dates": "First week of March 2026",
  "message": "I would like to book a safari package for my family. Please provide more details about accommodation and activities."
}
```

**Field Requirements:**
- `full_name` (required): String
- `email` (required): String, valid email format
- `phone` (required): String
- `num_guests` (required): String (e.g., "2 Guests", "4 Guests")
- `preferred_package` (optional): String
- `travel_dates` (optional): String
- `message` (required): String

**Success Response (201):**
```json
{
  "status": "success",
  "message": "Your enquiry has been submitted. We will be in touch shortly.",
  "data": {
    "id": 25
  }
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Full name is required."
}
```

```json
{
  "status": "error",
  "message": "Email is required."
}
```

```json
{
  "status": "error",
  "message": "Invalid email address."
}
```

```json
{
  "status": "error",
  "message": "Phone number is required."
}
```

```json
{
  "status": "error",
  "message": "Number of guests is required."
}
```

```json
{
  "status": "error",
  "message": "Message is required."
}
```

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use POST."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to submit enquiry. Please try again."
}
```

---

## Social Links

### List Social Links

**Endpoint:** `GET /api/social-links/list`  
**Authentication:** None (Public)  
**Description:** Retrieve social media links for footer/header

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "icon_name": "fa-facebook",
      "label": "Facebook",
      "href": "https://facebook.com/bardiyaecofriendly",
      "display_order": 1,
      "is_active": true
    },
    {
      "id": 2,
      "icon_name": "fa-instagram",
      "label": "Instagram",
      "href": "https://instagram.com/bardiyaecofriendly",
      "display_order": 2,
      "is_active": true
    }
  ]
}
```

**Error Responses:**

- **405 Method Not Allowed:**
```json
{
  "status": "error",
  "message": "Method not allowed. Use GET."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch social links."
}
```

---

## Email System

### Send Email Reply

**Endpoint:** `POST /api/emails/send-reply`  
**Authentication:** Required (Bearer Token)  
**Description:** Send email reply to a contact submission

**Headers:**
```
Authorization: Bearer <your_jwt_token>
Content-Type: application/json
```

**Request Body:**
```json
{
  "submission_id": 5,
  "subject": "Re: Your Enquiry - Bardiya Eco Friendly",
  "body_html": "<p>Dear John,</p><p>Thank you for your enquiry...</p>",
  "body_plain": "Dear John, Thank you for your enquiry..."
}
```

**Field Requirements:**
- `submission_id` (required): Integer, must exist in contact_submissions
- `subject` (required): String, 5-500 characters
- `body_html` (required): String, 10-500,000 characters, HTML content
- `body_plain` (optional): String, plain text version

**Success Response (200):**
```json
{
  "status": "success",
  "message": "Email sent successfully.",
  "data": {
    "email_id": 15,
    "sent_at": "2026-02-24 14:30:00"
  }
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Valid submission_id is required."
}
```

```json
{
  "status": "error",
  "message": "Subject must be at least 5 characters."
}
```

```json
{
  "status": "error",
  "message": "Email body must be at least 10 characters."
}
```

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to send email"
}
```

---

### Get Email History

**Endpoint:** `GET /api/emails/history`  
**Authentication:** Required (Bearer Token)  
**Description:** Retrieve email history for a contact submission

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Query Parameters:**
- `submission_id` (required): Contact submission ID

**Example Request:**
```
GET /api/emails/history?submission_id=5
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 15,
      "subject": "Re: Your Enquiry - Bardiya Eco Friendly",
      "body_html": "<p>Dear John...</p>",
      "sent_at": "2026-02-24 14:30:00",
      "status": "sent",
      "error_message": null,
      "sent_by": "admin"
    }
  ]
}
```

**Email Status Values:**
- `sent` - Email sent successfully
- `failed` - Email sending failed
- `pending` - Email queued for sending

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Valid submission_id query parameter is required."
}
```

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

---

### List Email Templates

**Endpoint:** `GET /api/email-templates/list`  
**Authentication:** Required (Bearer Token)  
**Description:** Retrieve all active email templates

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 1,
      "name": "booking_enquiry_response",
      "subject": "Re: Your Enquiry - Bardiya Eco Friendly",
      "body_html": "<p>Dear {{guest_name}},</p>...",
      "description": "Standard response for booking enquiries",
      "is_active": true,
      "created_at": "2026-02-15 10:00:00",
      "updated_at": "2026-02-15 10:00:00"
    }
  ]
}
```

**Template Variables:**
Available placeholders in templates:
- `{{guest_name}}` - Guest full name
- `{{guest_email}}` - Guest email address
- `{{guest_phone}}` - Guest phone number
- `{{num_guests}}` - Number of guests
- `{{preferred_package}}` - Package name
- `{{travel_dates}}` - Travel dates
- `{{message}}` - Original message
- `{{admin_name}}` - Admin username
- `{{company_name}}` - Company name
- `{{company_email}}` - Company email
- `{{company_phone}}` - Company phone

**Error Responses:**

- **401 Unauthorized:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

- **500 Internal Server Error:**
```json
{
  "status": "error",
  "message": "Failed to fetch email templates."
}
```

---

### Get Template with Variables

**Endpoint:** `GET /api/email-templates/get`  
**Authentication:** Required (Bearer Token)  
**Description:** Get a specific template with variables replaced from submission data

**Headers:**
```
Authorization: Bearer <your_jwt_token>
```

**Query Parameters:**
- `template_id` (required): Email template ID
- `submission_id` (required): Contact submission ID

**Example Request:**
```
GET /api/email-templates/get?template_id=1&submission_id=5
```

**Success Response (200):**
```json
{
  "status": "success",
  "data": {
    "template_name": "booking_enquiry_response",
    "subject": "Re: Your Enquiry - Bardiya Eco Friendly",
    "body_html": "<p>Dear John Smith,</p><p>Thank you for your interest in Bardiya Eco Friendly!</p><p>We have received your enquiry for <strong>Safari Package</strong> for 4 Guests during March 2026.</p>..."
  }
}
```

**Error Responses:**

- **400 Bad Request:**
```json
{
  "status": "error",
  "message": "Valid template_id is required."
}
```

```json
{
  "status": "error",
  "message": "Valid submission_id is required."
}
```

- **404 Not Found:**
```json
{
  "status": "error",
  "message": "Template not found."
}
```

```json
{
  "status": "error",
  "message": "Submission not found."
}
```

---

## Error Handling

### Standard Error Response Format

All API endpoints follow a consistent error response structure:

```json
{
  "status": "error",
  "message": "Human-readable error message"
}
```

### HTTP Status Codes

| Status Code | Description |
|-------------|-------------|
| 200 | OK - Request succeeded |
| 201 | Created - Resource created successfully |
| 400 | Bad Request - Invalid input or missing required fields |
| 401 | Unauthorized - Authentication required or token invalid |
| 404 | Not Found - Resource not found |
| 405 | Method Not Allowed - HTTP method not supported for endpoint |
| 500 | Internal Server Error - Server-side error occurred |

### Common Error Scenarios

1. **Missing Authentication Token:**
```json
{
  "status": "error",
  "message": "Access denied. No token provided."
}
```

2. **Invalid/Expired Token:**
```json
{
  "status": "error",
  "message": "Invalid or expired token."
}
```

3. **Invalid JSON Input:**
```json
{
  "status": "error",
  "message": "Invalid JSON input"
}
```

4. **Database Connection Error:**
```json
{
  "status": "error",
  "message": "Database connection failed: [error details]"
}
```

5. **Endpoint Not Found:**
```json
{
  "status": "error",
  "message": "Endpoint not found."
}
```

---

## CORS Configuration

All API endpoints support Cross-Origin Resource Sharing (CORS) with the following headers:

```
Access-Control-Allow-Origin: *
Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS
Access-Control-Allow-Headers: Content-Type, Authorization
```

Preflight `OPTIONS` requests are automatically handled and return a `200 OK` response.

---

## Authentication Flow

### For Protected Endpoints

1. **Login** to obtain JWT token:
```bash
POST /api/auth/login
{
  "username": "admin",
  "password": "admin123"
}
```

2. **Store the token** from the response

3. **Include token in subsequent requests:**
```bash
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

4. **Token expires** after 3600 seconds (1 hour) by default

5. **Verify token validity** (optional):
```bash
GET /api/auth/verify
Authorization: Bearer <your_token>
```

---

## Data Types Reference

### Homestay Object
```typescript
{
  id: number
  name: string
  description: string | null
  location: string
  price_per_night: number (decimal)
  max_guests: number
  image_url: string | null
  is_available: boolean
  created_at: string (datetime)
  updated_at: string (datetime)
}
```

### Booking Object
```typescript
{
  id: number
  homestay_id: number
  homestay_name: string
  guest_name: string
  guest_email: string | null
  guest_phone: string | null
  check_in: string (date YYYY-MM-DD)
  check_out: string (date YYYY-MM-DD)
  guests_count: number
  total_price: number (decimal)
  status: "confirmed" | "pending" | "cancelled" | "completed"
  created_at: string (datetime)
}
```

### Package Object
```typescript
{
  id: number
  category_id: number
  category_name: string
  category_slug: string
  icon: string | null
  name: string
  duration: string | null
  price: number (decimal)
  currency: string
  price_note: string | null
  description: string
  is_featured: boolean
  display_order: number
  is_active: boolean
  features: string[]
}
```

### Gallery Image Object
```typescript
{
  id: number
  categoryId: number
  categorySlug: string
  categoryName: string
  imageUrl: string
  altText: string | null
  displayOrder: number
  isActive: boolean
  createdAt: string (datetime)
}
```

---

## Notes

1. **Date Format:** All dates use `YYYY-MM-DD` format (e.g., `2026-03-15`)
2. **Datetime Format:** All timestamps use `YYYY-MM-DD HH:MM:SS` format
3. **Decimal Numbers:** Prices use 2 decimal places (e.g., `2500.00`)
4. **Boolean Values:** Returned as `true` or `false` (not 1/0)
5. **Null Values:** Optional fields may return `null` if not set
6. **Character Encoding:** All responses use UTF-8 encoding
7. **Content Type:** All responses have `Content-Type: application/json; charset=utf-8`

---

**Document Version:** 1.0  
**Last Updated:** February 24, 2026  
**Prepared By:** Senior Backend Developer
