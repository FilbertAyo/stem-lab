# Admin vs Client Separation Plan — Subdomain Strategy

**Goal:** Keep client on main domain (`adilishastemlabs.com`) and serve admin from a subdomain (`admin.adilishastemlabs.com`), with one codebase and one database.

---

## Current Setup (Summary)

- **Stack:** Laravel (single app), path-based separation.
- **Client:** `adilishastemlabs.com/`, `.../login`, `.../dashboard`, etc.
- **Admin:** `adilishastemlabs.com/admin/login`, `.../admin/dashboard`, etc.
- **Database:** Single MySQL/DB; shared users, schools, competitions, 3D models, etc.

---

## Recommended Approach: Same App, Domain-Based Routing

**Use one Laravel application, one database, and split behaviour by host (main domain vs admin subdomain).**

| Aspect | Recommendation |
|--------|----------------|
| **Codebase** | Keep one repo, one Laravel app. |
| **Database** | Keep one database. Admin and client share the same data. |
| **Deployment** | One deployment. Both domains point to the same app (same server or same load balancer). |
| **Routing** | Main domain → client + public routes. Admin subdomain → admin routes (no `/admin` path prefix). |
| **Sessions / cookies** | Shared cookie domain (e.g. `.adilishastemlabs.com`) so you can optionally share auth; or separate cookie per subdomain for stricter isolation. |

### Why this approach?

1. **Single source of truth** — One DB, one codebase; no sync or duplication.
2. **Simpler ops** — One server, one deploy, one set of env vars (with optional per-domain overrides).
3. **Clear separation by URL** — Client = main domain, Admin = subdomain, without splitting the app.
4. **Easier to harden** — You can lock admin subdomain by IP, firewall, or WAF without touching client.

### When to consider a different approach

- **Fully separate apps:** Only if you later split into different teams, different release cycles, or different tech stacks. Not needed for “client on main, admin on subdomain.”
- **Separate DBs:** Only if you have strict compliance (e.g. admin data in a different region). For most cases, one DB is simpler and correct.

---

## Database: Keep One

- **Use a single database.**  
  Admin and client are two interfaces over the same business data: users, roles, schools, classes, clubs, competitions, 3D models, VR experiments, etc. Splitting into two databases would force you to either duplicate data or build sync/APIs, which is unnecessary here.

- **Access control** is already in the app (middleware `admin` vs `teacher`/`auth`), not in the DB. Keeping one DB does not weaken security if your app and server are configured correctly.

- **Optional (only if you really need it):** Same DB server, two databases (e.g. `stem_lab` for app, `stem_lab_admin_audit` for audit logs). Still one “main” DB for the app; the second is for a specific use case, not for separating admin vs client.

---

## Implementation Outline

### 1. DNS

- **Main:** `adilishastemlabs.com` → your server (or load balancer).
- **Admin:** `admin.adilishastemlabs.com` → **same** server (or same load balancer).  
  Same IP or same LB so both hosts hit the same Laravel `public/` (or same proxy to it).

### 2. Web server (Nginx/Apache)

- **Option A (simplest):** Two server blocks (vhosts) that both use the **same document root** (e.g. `.../stem-lab/public`).  
  - `server_name adilishastemlabs.com www.adilishastemlabs.com;`  
  - `server_name admin.adilishastemlabs.com;`  
  No path changes; Laravel will see the request host and you route by domain.

- **Option B:** Reverse proxy: admin subdomain → same backend as main domain. Same idea: one app, two hostnames.

### 3. Laravel: route by domain

- **Main domain** (`adilishastemlabs.com`, `www.adilishastemlabs.com`):  
  - Serve: `/`, `/login`, `/register`, `/dashboard`, `/models/3d`, `/vr-experiments`, competitions, etc. (current client + public routes).  
  - Optional: redirect `/admin` and `/admin/*` to `https://admin.adilishastemlabs.com/...` so old links and bookmarks move to the subdomain.

- **Admin subdomain** (`admin.adilishastemlabs.com`):  
  - Serve admin routes **without** the `admin` prefix: e.g. `/login`, `/dashboard`, `/schools`, `/competitions`, etc.  
  - So: `admin.adilishastemlabs.com/login`, `admin.adilishastemlabs.com/dashboard`, etc.

Implementation options:

- **Option A — Route groups by `domain()`:**  
  In `routes/web.php` (or a dedicated `routes/admin.php`), wrap all current `prefix('admin')` routes in a group like:

  ```php
  Route::domain('admin.adilishastemlabs.com')->group(function () {
      // Admin routes here WITHOUT prefix('admin')
      // Login: /login, Dashboard: /dashboard, etc.
  });
  ```

  And keep the rest of the routes for the default (main) domain.

- **Option B — Middleware that checks host:**  
  Create middleware that aborts or redirects if a non-admin host hits admin-only routes (or if admin host hits client-only routes). Use this in addition to domain-based route registration so behaviour is explicit.

You’ll also need to **generate URLs for the admin area** using the admin domain (e.g. `route('admin.dashboard')` should use `admin.adilishastemlabs.com`). Laravel’s `URL::forceRootUrl()` or a `APP_URL_ADMIN` config can be used when the current request is from the admin subdomain so `url()`, `route()` etc. use the correct host.

### 4. Sessions and cookies

- **Shared session (single login for both sides):**  
  Set cookie domain so it’s valid for both main and subdomain, e.g. in `.env`:

  ```env
  SESSION_DOMAIN=.adilishastemlabs.com
  ```

  Then a user logged in on the main domain could, in theory, also be “logged in” on the admin subdomain (if you allow it). Whether you want that depends on policy (e.g. admins must use only admin subdomain and have a separate login there).

- **Stricter option (recommended for admin):**  
  Keep admin and client sessions separate:  
  - Use **different session cookie names** for admin (e.g. `SESSION_COOKIE=adilisha_session` for main, and for admin subdomain set something like `adilisha_admin_session` via config that’s keyed by the request host).  
  - Or don’t set `SESSION_DOMAIN` (or set it only for main domain); then admin subdomain gets its own cookie scope.  
  That way, logging in at `admin.adilishastemlabs.com/login` does not reuse the client session and vice versa.

Choose one of these and document it so future auth changes (e.g. 2FA, “remember me”) stay consistent.

### 5. Environment and config

- **Single deployment:** One `.env`; `APP_URL` can be the main site (`https://adilishastemlabs.com`).  
  For links that must point to the admin subdomain, use a dedicated config (e.g. `config/app.php`):

  ```php
  'admin_url' => env('APP_ADMIN_URL', 'https://admin.adilishastemlabs.com'),
  ```

  Use this when generating admin login, password reset, or dashboard links (e.g. in mail notifications, redirects).

- **Optional:** If you use two vhosts with different env files (e.g. `admin.adilishastemlabs.com` loads `.env.admin`), you could set `APP_URL` to the admin URL in that env and keep `APP_ADMIN_URL` only there. One app, two env files is possible but not required.

### 6. Auth and redirects

- **Client login:** Only on main domain (`adilishastemlabs.com/login`).  
- **Admin login:** Only on subdomain (`admin.adilishastemlabs.com/login`).  
  Your existing `AuthenticatedSessionController` and `admin` middleware can stay; add checks (or route placement) so that:
  - Admin login form and `store` are only registered when the request host is the admin subdomain (or under the admin route group).
  - After admin login, redirect to `admin.adilishastemlabs.com/dashboard` (using `APP_ADMIN_URL` or similar).

- **Redirect old admin paths:**  
  On the main domain, add a fallback so that `adilishastemlabs.com/admin` and `adilishastemlabs.com/admin/*` permanently redirect (301) to `https://admin.adilishastemlabs.com/` and `https://admin.adilishastemlabs.com/*`. That way bookmarks and old links keep working.

### 7. Asset and storage URLs

- **Assets:** If you use `asset()`, Vite, or mix, they typically use `APP_URL`. For the admin subdomain, ensure admin views are loaded when the request is already to `admin.adilishastemlabs.com`, so `APP_URL` for that request can be set to the admin URL (if you use a separate env for that vhost), or you override `URL::forceRootUrl()` for admin routes so assets and links use the correct domain.  
- **Storage:** If you have links to stored files (e.g. competition attachments, reports), they’re likely served from the same app (e.g. `/storage/...`). As long as both domains point to the same app, the same storage URLs work; just ensure they’re generated with the correct scheme (HTTPS) and host if you need them to work from both domains.

### 8. Security (optional but recommended)

- **Admin subdomain:**  
  - Restrict by IP (office/VPN) in Nginx/Apache or firewall if only certain networks should access admin.  
  - Use HTTPS only; consider HSTS.  
  - Keep using your existing `admin` middleware and role checks.

- **Main domain:**  
  - No change to current client auth and middleware.

---

## Session Configuration

**IMPORTANT:** You need to decide on session/cookie strategy:

### Option 1: Separate Sessions (Recommended for Security)
- **Leave `SESSION_DOMAIN` unset or null** in `.env`
- Admin and client will have separate sessions
- More secure: admin login on subdomain doesn't share session with client
- Users must login separately on each domain

### Option 2: Shared Sessions
- Set `SESSION_DOMAIN=.adilishastemlabs.com` in `.env` (note the leading dot)
- Admin and client can share the same session cookie
- Less secure: if someone gets admin session, they could access client side too
- Users login once, can access both domains

**Recommendation:** Use Option 1 (separate sessions) for better security isolation between admin and client.

---

## Summary Checklist

| Step | Action |
|------|--------|
| 1 | DNS: point `admin.adilishastemlabs.com` to same server as main domain. |
| 2 | Web server: add vhost for `admin.adilishastemlabs.com` with same document root (or proxy to same backend). |
| 3 | Laravel: add domain-based route group for `admin.adilishastemlabs.com` with current admin routes (without `/admin` prefix). |
| 4 | Laravel: ensure admin URLs (login, dashboard, emails) use `APP_ADMIN_URL` (or equivalent). |
| 5 | Sessions: decide shared vs separate cookie; set `SESSION_DOMAIN` and/or cookie name accordingly. |
| 6 | Main domain: redirect `/admin` and `/admin/*` to `https://admin.adilishastemlabs.com/...`. |
| 7 | Keep one database; no DB split needed. |
| 8 | (Optional) Harden admin subdomain (IP restrict, WAF, etc.). |

---

## What *not* to do (for your case)

- **Don’t split the database** for “admin vs client” unless you have a specific compliance or isolation requirement. One DB keeps everything consistent and simpler.
- **Don’t create two Laravel apps** just to get a subdomain; one app with domain-based routing is enough and easier to maintain.
- **Don’t forget redirects** from `adilishastemlabs.com/admin/*` to the subdomain so existing links and bookmarks continue to work.

If you tell me your preferred session strategy (shared vs separate admin/client sessions) and whether you use Nginx or Apache, I can outline the exact route and config changes (file-by-file) next.
