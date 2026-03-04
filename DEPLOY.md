# Production deploy checklist

After pushing to GitHub and deploying to production, run these on the **server** so admin and client project routes work (e.g. `/projects`, `/projects/2/edit`, client project show).

## 1. Clear all caches (required)

Cached routes/config from a previous deploy can cause **404 Page Not Found** for new or existing routes (e.g. `admin.adilishastemlabs.com/projects/2/edit` or client project view).

```bash
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

## 2. Environment

Ensure production `.env` has:

- `APP_ADMIN_URL` = exact admin base URL (e.g. `https://admin.adilishastemlabs.com`)  
  Admin routes are bound to the **host** of this URL; if it’s wrong or missing, admin URLs can 404.

## 3. Migrations

```bash
php artisan migrate --force
```

## 4. Re-cache (optional, after the above)

Only after clearing and confirming routes work:

```bash
php artisan config:cache
php artisan route:cache
```

---

**If you still get 404:** Run `php artisan route:list` on the server and confirm that `admin.projects.edit` and `client.projects.show` appear and that the domain/host matches the URL you’re opening.
