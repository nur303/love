# Love Animation & Proposal - PHP Backend & Database

This project contains the interactive Love proposal animation with a full PHP backend and MySQL database to capture visitor responses ("Yes" / "No") along with optional messages and visitor statistics.

---

## 🚀 Features
- **Interactive Proposal Webpage** (`index.php` / `love.html`): High-speed animations with Mo.js and audio feedback.
- **Database Storage**: Captures every response:
  - Choice (`yes` or `no`)
  - Optional heartfelt note or reply message
  - Visitor IP address
  - Browser / User agent
  - Timestamp
- **Zero-Configuration Auto-Setup**: Automatically creates the database `love_db` and the table `responses` on first visit if they do not exist.
- **SQLite Fallback**: If MySQL is offline, automatically saves responses to `database.sqlite` so no responses are ever lost.
- **Admin Dashboard** (`admin.php`):
  - Password protected (default password: `love123`).
  - Summary stats: Total clicks, Total Yes (❤️), Total No (💔), and Acceptance Rate (%).
  - Detailed response table with delete actions.
  - CSV export of all recorded responses.

---

## 🛠️ How to Run

### Method 1: Using XAMPP (Recommended)

1. **Start Apache & MySQL in XAMPP**:
   - Open **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
   - Click **Start** next to **Apache** and **MySQL**.
2. **Access Project**:
   - If this folder is inside your XAMPP `htdocs` directory (e.g. `C:\xampp\htdocs\love`), simply open your browser and navigate to:
     ```
     http://localhost/love/
     ```
   - If this project is in `d:\E-commerce Website\love`, you can either:
     - Create a directory symlink or copy this folder to `C:\xampp\htdocs\love`, OR
     - Use Method 2 below.

### Method 2: Using PHP Built-in Server

You can run the site directly using PHP from your terminal:
```bash
cd "d:\E-commerce Website\love"
C:\xampp\php\php.exe -S localhost:8000
```
Then visit:
- **Proposal page**: [http://localhost:8000](http://localhost:8000)
- **Admin dashboard**: [http://localhost:8000/admin.php](http://localhost:8000/admin.php)

---

## 🔐 Admin Dashboard Access

- **URL**: `admin.php` (e.g., `http://localhost/love/admin.php` or `http://localhost:8000/admin.php`)
- **Default Password**: `love123`
- You can change the password at any time in `config.php`:
  ```php
  define('ADMIN_PASSWORD', 'your_new_password');
  ```

---

## 🗄️ Database Structure

If you prefer to import the database manually via phpMyAdmin:
1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin) in your browser.
2. Click **Import**.
3. Choose `schema.sql` and click **Go**.

### `responses` Table Schema:
| Column | Type | Description |
|---|---|---|
| `id` | INT (PK, Auto Increment) | Unique ID of response |
| `choice` | VARCHAR(10) | 'yes' or 'no' |
| `message` | TEXT (Nullable) | Optional reply note from visitor |
| `visitor_ip` | VARCHAR(45) | IP address of the respondent |
| `user_agent` | TEXT | Browser/device details |
| `created_at` | DATETIME | Timestamp of submission |

---

## 📁 File Structure
- `index.php` - Main interactive animation page (served by web server).
- `love.html` - Static HTML version with AJAX connection.
- `config.php` - Database and admin configuration.
- `db.php` - PDO database connection handler with auto-setup.
- `schema.sql` - SQL schema file.
- `api/submit.php` - REST API endpoint for recording responses.
- `admin.php` - Admin dashboard to view and manage responses.
