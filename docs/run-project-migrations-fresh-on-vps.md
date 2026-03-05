# Run only project-related migrations fresh on VPS

Use this when you want to **drop and re-run only** these 8 migrations (and leave the rest of the DB intact):

- `2026_02_11_000004_create_projects_table`
- `2026_02_11_000005_create_project_tag_table`
- `2026_02_11_000006_create_equipment_project_table`
- `2026_02_11_000007_create_project_steps_table`
- `2026_02_11_000008_create_project_resources_table`
- `2026_02_11_000009_create_project_codes_table`
- `2026_02_11_000010_create_platforms_table`
- `2026_02_11_000011_create_project_platform_table`

## Steps on the VPS

### 1. (Optional) Backup the database

```bash
php artisan db:show
# Then use mysqldump or your provider’s backup tool if needed.
```

### 2. Remove migration records and drop the tables

Use one of the options below.

#### Option A: Laravel Tinker (recommended)

```bash
cd /path/to/stem-lab
php artisan tinker
```

Then paste:

```php
// Remove migration records so these 8 are considered "pending" again
DB::table('migrations')->whereIn('migration', [
    '2026_02_11_000004_create_projects_table',
    '2026_02_11_000005_create_project_tag_table',
    '2026_02_11_000006_create_equipment_project_table',
    '2026_02_11_000007_create_project_steps_table',
    '2026_02_11_000008_create_project_resources_table',
    '2026_02_11_000009_create_project_codes_table',
    '2026_02_11_000010_create_platforms_table',
    '2026_02_11_000011_create_project_platform_table',
])->delete();

// Drop tables (order matters because of foreign keys)
DB::statement('SET FOREIGN_KEY_CHECKS = 0');
Schema::dropIfExists('project_platform');
Schema::dropIfExists('project_codes');
Schema::dropIfExists('project_resources');
Schema::dropIfExists('project_steps');
Schema::dropIfExists('equipment_project');
Schema::dropIfExists('project_tag');
Schema::dropIfExists('platforms');
Schema::dropIfExists('projects');
DB::statement('SET FOREIGN_KEY_CHECKS = 1');

exit
```

#### Option B: Raw SQL (e.g. `mysql` client)

```sql
-- Use your app database
USE your_database_name;

DELETE FROM migrations WHERE migration IN (
  '2026_02_11_000004_create_projects_table',
  '2026_02_11_000005_create_project_tag_table',
  '2026_02_11_000006_create_equipment_project_table',
  '2026_02_11_000007_create_project_steps_table',
  '2026_02_11_000008_create_project_resources_table',
  '2026_02_11_000009_create_project_codes_table',
  '2026_02_11_000010_create_platforms_table',
  '2026_02_11_000011_create_project_platform_table'
);

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS project_platform;
DROP TABLE IF EXISTS project_codes;
DROP TABLE IF EXISTS project_resources;
DROP TABLE IF EXISTS project_steps;
DROP TABLE IF EXISTS equipment_project;
DROP TABLE IF EXISTS project_tag;
DROP TABLE IF EXISTS platforms;
DROP TABLE IF EXISTS projects;
SET FOREIGN_KEY_CHECKS = 1;
```

Run it with:

```bash
mysql -u your_user -p your_database_name < project_migrations_fresh.sql
```

(or paste into `mysql` / your DB UI).

### 3. Re-run the migrations

```bash
php artisan migrate
```

Only these 8 migrations will be pending, so only they will run. All other tables and migration history stay as they are.
