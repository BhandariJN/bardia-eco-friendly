# 🌿 Bardiya Eco Friendly - CMS User Manual

Welcome to the **Bardiya Eco Friendly Content Management System (CMS)**. This manual provides a comprehensive guide on how to manage your website's packages, gallery, communications, and account security.

---

## 1. Getting Started

### 1.1 Accessing the CMS

1.  Navigate to `http://your-domain.com/cms/login.php`.
2.  Enter your **Username** and **Password**.
3.  If you forget your password, click **"Forgot Password?"** and enter your official recovery email to receive a reset link.

### 1.2 Navigation

- **Sidebar**: Use the left-hand sidebar to switch between different management modules.
- **Mobile View**: On smaller screens, use the **hamburger icon (☰)** in the top navigation bar to toggle the menu.
- **Logout**: Accessible at the bottom of the sidebar.

---

## 2. Package Management

The Package module is the core of your business listings. It is structured hierarchically: **Category > Package > Features**.

### 2.1 Package Categories

- **Name**: The public name (e.g., "Wilderness Safaris").
- **Slug**: A URL-friendly identifier. _Important_: Avoid changing slugs once a page is indexed by Google, as it can break external links.
- **Display Order**: Controls the sequence of categories in your website navigation (Lower numbers appear first).
- **Active Status**: Instantly hide an entire category and all its packages from the website without deleting them.

### 2.2 Package Details

When adding or editing a package, pay attention to these specific fields:

- **Icon**: Use the emoji picker to select a representative icon (e.g., 🐘 for Safaris, 🏠 for Homestays). This appears in the package header.
- **Price & Currency**: Enter numerical prices. The currency defaults to **₹** but can be adjusted.
- **Price Note**: A short label like "per person", "per night", or "all-inclusive". This provides clarity to the guest.
- **Duration**: Specify the timeframe (e.g., "3 Days / 2 Nights").
- **Description**: A detailed summary of the experience. Use engaging language here to convert visitors into guests.
- **Featured Package**: Toggle this **ON** to place the package in the "Featured" section of your homepage, giving it higher visibility.

### 2.3 Package Features (Line-by-Line)

- **Logic**: Each line you type in the text area becomes a distinct "feature" on the website.
- **Example**:
  ```text
  Professional Guide included
  All meals provided
  Airport pickup available
  ```
- These will appear as a clean bulleted list on the package details page.

---

## 3. Gallery Management

A high-quality gallery is your best marketing tool. The CMS handles image processing and categorization.

### 3.1 Gallery Categories

- Group images by theme. For example, a "Wildlife" category allows users to filter specifically for animal photos, while "Resort" shows the property.

### 3.2 Image Handling & SEO

- **Bulk Upload**: Select multiple files from your computer. The system automatically handles the upload and creates database entries.
- **Alt Text (Crucial)**: Describe the image contents (e.g., "Bengal Tiger crossing a river in Bardiya"). This is what search engines use to "read" your photos and what screen readers use for visually impaired users.
- **Display Order**: Essential for crafting the "story" of your gallery. Set primary stunning shots to `1`, `2`, and `3`.
- **Active Toggle**: Useful for seasonal photos (e.g., hiding winter photos during the summer).

---

## 4. Contact & Communications

### 4.1 Contact Methods (Location Cards)

The contact page features "cards" for different ways to reach you.

- **Title**: The headline (e.g., "Visit Our Office").
- **Detail**: The primary contact info (e.g., a specific phone number or address).
- **Description**: A multi-line field for additional info (e.g., "Open Mon-Fri, 9 AM to 5 PM" or "Located near the park entrance").
- **Icon**: Pick a relevant symbol (📞, 📧, 📍).

### 4.2 Inbox (Submissions)

- **Managing Inquiries**:
  - **Status**: Track the lifecycle of an inquiry: `New` (fresh), `Read` (acknowledged), `Replied` (resolved), or `Archived` (hidden).
  - **Direct Reply**: Click **"Send Email"** to use the **Integrated Email Reply** system.
  - **Templates**: Select a template to auto-fill the guest's name and signature. The system uses **TinyMCE** for professional rich-text editing.
  - **Tracking**: Every email sent is logged in the **Email History** section at the bottom of each submission.

### 4.3 Social Links

Connect your social media presence to your website footer.

- **Label**: The platform name (e.g., "Instagram").
- **URL (Href)**: Paste the **full link** starting with `https://`.
- **Icon Selection**: Use the icon picker to match the brand.
- **Display Order**: Determines the sequence of icons (e.g., Facebook usually comes first).

---

## 5. Account & Security

### 5.1 Profile Management

- **Location**: **Account > My Profile**.
- **Email Update**: You can change your official recovery email here. **Note**: For security, you must enter your current password to change this email.
- **Security Alerts**: If your recovery email is changed, the system sends an automatic alert to the _old_ email address to prevent unauthorized takeovers.

### 5.2 Password Security

- **Changing Password**: Enter your current password and your new password (minimum 8 characters).
- **Re-Authentication**: After changing your password, the system will automatically log you out. You must log back in with your new credentials.

---

## 6. Technical Best Practices

- **Image Optimization**: Before uploading gallery images, ensure they are optimized (less than 500KB recommended) for faster page loading.
- **Browser Support**: Use modern browsers like Chrome, Firefox, or Safari for the best CMS experience.
- **Responsiveness**: The CMS is fully mobile-responsive. You can manage your business from a tablet or smartphone during jungle safaris!

---

_Generated by Bardiya Eco Friendly Technical Team_
