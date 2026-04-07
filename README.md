# PW Portal — Setup Guide

## Files Structure

```
pw-website/
├── index.php          ← Login page (Token + OTP)
├── dashboard.php      ← User's courses dashboard
├── course.php         ← Individual course detail
├── auth.php           ← Login/logout backend (AJAX)
├── api.php            ← PW API proxy (credentials hidden)
├── config.php         ← ⚙️ Configuration (edit this)
├── db.php             ← SQLite database handler
├── generate_hash.php  ← One-time password hash tool (delete after use)
├── .htaccess          ← Security rules
├── data/              ← SQLite DB stored here (auto-created)
│   └── .htaccess      ← Blocks web access to DB
└── admin/
    ├── index.php      ← Admin login
    ├── dashboard.php  ← Admin panel (all users + tokens)
    └── .htaccess
```

## Requirements
- PHP 7.4+ with cURL, PDO, PDO_SQLite extensions
- Apache with mod_rewrite (for .htaccess)

## Setup Steps

### 1. Upload all files to your server

### 2. Change the Admin Password
The default password is: `Admin@PW2025`

To set a custom password:
```bash
php generate_hash.php YourNewPassword123
```
Copy the output hash and paste into `config.php`:
```php
define('ADMIN_PASSWORD_HASH', 'PASTE_YOUR_HASH_HERE');
```
Then delete `generate_hash.php`!

### 3. Set folder permissions
```bash
chmod 750 data/
chmod 640 config.php db.php api.php auth.php
```

### 4. Visit your site
- **User Login:** `https://yourdomain.com/`
- **Admin Panel:** `https://yourdomain.com/admin/`

## Security Notes

- `config.php`, `db.php`, `api.php`, `auth.php` are blocked from direct browser access via `.htaccess`
- `data/portal.db` (SQLite DB) is also blocked from direct access
- API credentials (client_id, client_secret) are **never** sent to the frontend
- Tokens are stored only in PHP sessions and the server-side database
- Admin login has a 1-second brute-force delay on failed attempts
- Session tokens are cryptographically random (32 bytes)

## Admin Panel Features
- View ALL logged-in users
- See their: Name, Mobile, Email, Access Token, Refresh Token, Org ID
- Copy tokens with one click
- Search/filter users instantly
- Login count per day
