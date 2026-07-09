# LAGS Contact Lite

A lightweight, custom-built WordPress contact form plugin with a dynamic admin interface for managing submissions.

---

## 🚀 Features

### Frontend
- AJAX form submission (no page reload)
- Real-time field validation
- Clean and minimal UI
- Graceful fallback (works without JavaScript)

### Admin Dashboard
- View and manage messages in a custom admin panel
- Mark messages as read/unread (AJAX-powered)
- Live unread message counter in admin menu
- Search and filter messages
- Pagination for large datasets

### Architecture & Performance
- Custom database table for optimized storage
- Modular structure (separated templates, logic, and data layer)
- Efficient queries with pagination and filtering

### Security
- Nonce verification for all actions
- Capability checks for admin operations
- Sanitization and escaping of all inputs/outputs

---

## 🧱 Tech Stack

- PHP (WordPress Plugin API)
- JavaScript (Vanilla, AJAX)
- MySQL (via `$wpdb`)
- HTML/CSS

---

## 📦 Installation

1. Upload the plugin to:
2. 2. Activate the plugin via WordPress admin
3. Add the shortcode to any page: **[lags_contact_form]**


---

## 🧠 What This Project Demonstrates

- Building a full CRUD-style system inside WordPress
- Designing scalable plugin architecture
- Implementing AJAX interactions with real-time UI updates
- Applying WordPress security best practices
- Managing state between frontend, backend, and database

---

## 👤 Author

**LAGS**
