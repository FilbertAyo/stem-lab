# Local Admin Panel Setup Guide

This guide explains how to run the admin panel locally on your development machine.

## Overview

The admin panel uses subdomain-based routing. In production, it runs on `admin.adilishastemlabs.com`, but for local development, you'll use `admin.localhost`.

## Step 1: Update Environment Configuration

The `.env` file has been updated with:
```
APP_ADMIN_URL=http://admin.localhost
```

## Step 2: Configure Local Domain Resolution

You need to add the admin subdomain to your system's hosts file so your browser can resolve `admin.localhost`.

### macOS / Linux

1. Open Terminal
2. Edit the hosts file with sudo:
   ```bash
   sudo nano /etc/hosts
   ```
3. Add this line:
   ```
   127.0.0.1    admin.localhost
   ```
4. Save and exit (Ctrl+X, then Y, then Enter)

### Windows

1. Open Notepad as Administrator (Right-click → Run as administrator)
2. Open the file: `C:\Windows\System32\drivers\etc\hosts`
3. Add this line:
   ```
   127.0.0.1    admin.localhost
   ```
4. Save the file

## Step 3: Configure Apache Virtual Host

Since you're using Apache, you need to set up a virtual host for `admin.localhost`. Choose one of the following options:

### Option A: Using Apache Virtual Host (Recommended for Apache)

1. **Copy the virtual host configuration:**
   ```bash
   sudo cp apache-admin-localhost.conf /etc/apache2/extra/httpd-vhosts.conf
   ```
   
   Or if you're using MAMP/XAMPP, copy it to your Apache configuration directory:
   - **MAMP:** `/Applications/MAMP/conf/apache/extra/httpd-vhosts.conf`
   - **XAMPP (macOS):** `/Applications/XAMPP/etc/httpd.conf` or `/Applications/XAMPP/etc/extra/httpd-vhosts.conf`
   - **Native Apache (macOS):** `/etc/apache2/extra/httpd-vhosts.conf` or `/private/etc/apache2/extra/httpd-vhosts.conf`

2. **Edit the virtual host file** to match your setup (update the DocumentRoot path if needed):
   ```bash
   sudo nano /etc/apache2/extra/httpd-vhosts.conf
   ```
   
   Or add this to your existing virtual hosts file:
   ```apache
   <VirtualHost *:80>
       ServerName admin.localhost
       DocumentRoot "/Users/ayo/Desktop/projects/stem-lab/public"
       
       <Directory "/Users/ayo/Desktop/projects/stem-lab/public">
           Options Indexes FollowSymLinks
           AllowOverride All
           Require all granted
       </Directory>
       
       ErrorLog "/Users/ayo/Desktop/projects/stem-lab/storage/logs/apache-admin-error.log"
       CustomLog "/Users/ayo/Desktop/projects/stem-lab/storage/logs/apache-admin-access.log" common
   </VirtualHost>
   ```

3. **Enable virtual hosts** (if not already enabled):
   ```bash
   # Edit Apache config
   sudo nano /etc/apache2/httpd.conf
   ```
   
   Make sure this line is uncommented:
   ```apache
   Include /private/etc/apache2/extra/httpd-vhosts.conf
   ```
   (or the path to your vhosts file)

4. **Restart Apache:**
   ```bash
   sudo apachectl restart
   ```
   
   Or if using MAMP/XAMPP, restart through their control panel.

5. **Access the admin panel:**
   - **Admin Login:** http://admin.localhost/login
   - **Admin Dashboard:** http://admin.localhost/dashboard (after login)

### Option B: Using Laravel Development Server (Easier Alternative)

If you prefer not to configure Apache, you can use Laravel's built-in server:

1. **Start the Laravel server:**
   ```bash
   php artisan serve --host=0.0.0.0 --port=8000
   ```

2. **Access the admin panel:**
   - **Admin Login:** http://admin.localhost:8000/login
   - **Admin Dashboard:** http://admin.localhost:8000/dashboard (after login)

   Note: You'll need to use port 8000 when using Laravel's development server.

## Step 5: Login Credentials

You'll need an admin user account in your database. If you don't have one, you can:

1. Create one via Tinker:
   ```bash
   php artisan tinker
   ```
   Then run:
   ```php
   \App\Models\User::create([
       'name' => 'Admin User',
       'email' => 'admin@example.com',
       'password' => bcrypt('password'),
       'role' => 'admin',
       'is_active' => true,
   ]);
   ```

2. Or use a seeder if you have one:
   ```bash
   php artisan db:seed --class=AdminUserSeeder
   ```

## Alternative: Using Laravel Valet

If you're using Laravel Valet, you can:

1. Link your project:
   ```bash
   valet link stem-lab
   ```

2. Create a custom domain:
   ```bash
   valet link stem-lab admin.stem-lab.test
   ```

3. Update `.env`:
   ```
   APP_ADMIN_URL=http://admin.stem-lab.test
   ```

4. Access at: http://admin.stem-lab.test/login

## Troubleshooting

### Domain not resolving
- Make sure you've added the entry to `/etc/hosts` (macOS/Linux) or `C:\Windows\System32\drivers\etc\hosts` (Windows)
- Try flushing your DNS cache:
  - macOS: `sudo dscacheutil -flushcache; sudo killall -HUP mDNSResponder`
  - Windows: `ipconfig /flushdns`
  - Linux: `sudo systemd-resolve --flush-caches` or restart your network service

### Port conflicts
- If port 8000 is in use, use a different port: `php artisan serve --port=8080`
- Update your browser URL accordingly: `http://admin.localhost:8080/login`

### Session issues
- Make sure your `.env` has `SESSION_DRIVER=database` or `SESSION_DRIVER=file`
- Clear config cache: `php artisan config:clear`
- Clear application cache: `php artisan cache:clear`

### Route not found / Apache 404 Error
- **If using Apache:** Make sure you've configured the virtual host correctly and restarted Apache
- **Check Apache error logs:** Look at `/Users/ayo/Desktop/projects/stem-lab/storage/logs/apache-admin-error.log` for details
- Clear route cache: `php artisan route:clear`
- Make sure you're accessing via the admin subdomain (`admin.localhost`), not the main domain (`localhost`)
- Verify your hosts file has the entry: `127.0.0.1 admin.localhost`
- Try using Laravel's development server instead: `php artisan serve --host=0.0.0.0 --port=8000`

## Notes

- The admin panel requires users with `role = 'admin'` and `is_active = true`
- Admin sessions don't use "remember me" functionality for security
- The main application remains accessible at `http://localhost:8000`
