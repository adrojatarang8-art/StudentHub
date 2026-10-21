# Practical 12: Event Management CRUD with Poster Upload and File Validation

## 📌 Problem Definition
Develop event management functionality for admin users. Admin can add, view, update, delete, and upload event posters with file type and file size validation.

---

## 🚀 Features Implemented
1. **Full CRUD Operations**:
   - **Create**: Add new events with title, category, date, venue, status, poster image, and description.
   - **Read**: View list of events with filtering (Category, Open/Closed Status) and search (by title or venue).
   - **Update**: Edit existing events, retain existing poster, upload replacement poster, or remove poster.
   - **Delete**: Delete event with automatic server-side cleanup of the associated poster image using `unlink()`.

2. **Strict Server-Side File Upload Validation**:
   - **File Size Validation**: Maximum 2MB allowed (`2 * 1024 * 1024` bytes).
   - **Extension Whitelist**: Only `.jpg`, `.jpeg`, `.png`, and `.webp` allowed.
   - **MIME Type Inspection**: Verifies binary content using PHP `finfo` (`FILEINFO_MIME_TYPE`).
   - **Unique Renaming**: Safe unique name generation (`event_<timestamp>_<random_hex>.<ext>`) to prevent overwrite and directory traversal attacks.

3. **Security & Extensions**:
   - **Uploads Directory Protection**: `.htaccess` denies execution of PHP scripts in `uploads/`.
   - **Audit Trail**: Every create, update, and delete action is recorded in the `audit_logs` table.
   - **SQL Injection Prevention**: PDO Prepared Statements for all database queries.
   - **XSS Protection**: All user output escaped with `htmlspecialchars()`.

---

## ⚙️ How to Set Up in XAMPP
1. Place this folder in `c:\xampp\htdocs\practical12`.
2. Start **Apache** and **MySQL** in XAMPP Control Panel.
3. Open your browser and go to `http://localhost/phpmyadmin`.
4. Import `database.sql` into phpMyAdmin (it will create `event_db` with `events` and `audit_logs` tables).
5. Open your browser and navigate to:
   ```
   http://localhost/practical12/
   ```
