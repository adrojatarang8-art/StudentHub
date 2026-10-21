# Practical 15 — Local XAMPP / Apache Deployment

## Simple localhost (recommended for practical submission)

1. Unzip directly to `C:\xampp\htdocs\practical15\`.
2. Start Apache + MySQL. Check `http://localhost/` and `http://localhost/phpmyadmin/`.
3. **Existing DB:** export a backup; select `studenthub_db`; import `practical15.sql`. **New DB:** import `sql/Practical15_Fresh_Install.sql` instead.
4. Confirm project in browser: `http://localhost/practical15/`.
5. Use existing test login, then test `admin.php`, `student_directory.php`, `event_management.php`, `ajax_events.php`.
6. Keep folder `uploads/event_posters/` writable by Apache. It contains `.htaccess` to block running uploaded scripts.

## Intermediate extension: optional Apache Virtual Host (Windows XAMPP)

**Do not edit Virtual Hosts unless normal localhost works first.** Back up both files before editing.

In `C:\xampp\apache\conf\extra\httpd-vhosts.conf` append:

```apache
<VirtualHost *:80>
    ServerName studenthub.local
    DocumentRoot "C:/xampp/htdocs/practical15"
    <Directory "C:/xampp/htdocs/practical15">
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php index.html
    </Directory>
    ErrorLog "logs/studenthub-local-error.log"
    CustomLog "logs/studenthub-local-access.log" common
</VirtualHost>
```

Ensure `httpd.conf` includes `conf/extra/httpd-vhosts.conf`. Edit **Windows Notepad as Administrator** and add to `C:\Windows\System32\drivers\etc\hosts`:

```text
127.0.0.1 studenthub.local
```

Restart Apache. Test `http://studenthub.local/` and `http://studenthub.local/login.php`. If another `localhost` Virtual Host is already in use, preserve it; do not blindly replace your Apache config. With this hostname, PHP session cookie paths are generated from each request path.

## Port troubleshooting

If Apache runs on port `8080`, use `http://localhost:8080/practical15/` and adjust the Virtual Host accordingly.

## Production note (NOT deployed by this ZIP)

Static `HTML/CSS/JS` pages could be previewed on static hosting, but **PHP sessions, API endpoints, file uploads and MySQL do not run on GitHub Pages**. For full online deployment you need a PHP-compatible server (HTTPS, PHP 8.0+, MySQL, private DB credentials, configured upload directory, backups). Do **not** upload `.sql` dumps containing personal data or `.env` files to a public GitHub repository.
