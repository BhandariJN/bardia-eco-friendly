# 🌿 Bardiya Eco Friendly - API Documentation

This documentation outlines the RESTful API endpoints for the Bardiya Eco Friendly platform.

---

## 🔑 Authentication

Most management endpoints require a **JSON Web Token (JWT)** for authorization.

- **Auth Header**: `Authorization: Bearer <your_jwt_token>`
- **Token Expiry**: Default is 1 hour (configurable in `.env`).

---

## 1. Authentication Endpoints

### Login

- **Method**: `POST`
- **Endpoint**: `/api/auth/login`
- **Body**:
  ```json
  { "username": "admin", "password": "yourpassword" }
  ```
- **Response**: `200 OK` with user data and `token`.

### Verify Token

- **Method**: `GET`
- **Endpoint**: `/api/auth/verify`
- **Auth**: Required (Bearer Token)
- **Response**: `200 OK` if token is valid.

---

## 2. Package & Categories

### List Categories (Public)

- **Method**: `GET`
- **Endpoint**: `/api/package-categories/list`
- **Fields**: `id`, `name`, `slug`, `display_order`, `is_active`, `pkg_count`.

### Create/Edit/Delete Category

- **Auth**: Required
- **Endpoints**: `/api/package-categories/[create|update|delete]`

### List Packages (Public)

- **Method**: `GET`
- **Endpoint**: `/api/packages/list`
- **Params**: `category_id` (optional filter)
- **Details**: Returns full package details including an array of `features`.

### Save Package Features

- **Method**: `POST`
- **Endpoint**: `/api/package-features/save`
- **Auth**: Required
- **Body**:
  ```json
  { "package_id": 1, "features": ["Feature 1", "Feature 2"] }
  ```

---

## 3. Gallery & Images

### List Gallery (Public)

- **Method**: `GET`
- **Endpoint**: `/api/gallery-images/list`
- **Params**: `category_id` (optional filter)

### Image Upload

- **Method**: `POST`
- **Endpoint**: `/api/gallery-images/upload`
- **Auth**: Required
- **Content-Type**: `multipart/form-data`
- **Fields**: `category_id`, `images[]` (multiple files supported).

---

## 4. Contact & Inquiries

### Submit Inquiry (Public)

- **Method**: `POST`
- **Endpoint**: `/api/contact-submissions/submit`
- **Body**:
  ```json
  {
    "full_name": "John Doe",
    "email": "john@example.com",
    "phone": "9800000000",
    "num_guests": "2",
    "message": "Interested in Safari",
    "preferred_package": "Jungle Walk",
    "travel_dates": "Next Monday"
  }
  ```

### Manage Submissions

- **Auth**: Required
- **Endpoints**: `/api/contact-submissions/[list|update-status|delete]`

---

## 5. Communications (Email)

### Send Reply

- **Method**: `POST`
- **Endpoint**: `/api/emails/send-reply`
- **Auth**: Required
- **Body**:
  ```json
  {
    "submission_id": 10,
    "subject": "Re: Inquiry",
    "body_html": "<p>Hello...</p>"
  }
  ```

### Email History

- **Method**: `GET`
- **Endpoint**: `/api/emails/history?submission_id=10`
- **Auth**: Required

---

## 6. Global Response Format

The API follows a consistent JSON response structure:

```json
{
  "status": "success | error",
  "data": { ... } | [ ... ],
  "message": "Human readable message"
}
```

### Common Status Codes:

- `200`: Success.
- `201`: Resource Created.
- `400`: Bad Request (Invalid input).
- `401`: Unauthorized (Invalid/missing token).
- `404`: Not Found.
- `405`: Method Not Allowed.
- `500`: Internal Server Error.

---

_Bardiya Eco Friendly API v1.2_
