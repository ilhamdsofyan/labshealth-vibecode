# Project Identity
As of: March 6, 2026

- Project: `LabsHealth UKS`
- Stack: `Laravel 12`, `PHP 8.2`, Blade + Bootstrap UI, MySQL-backed app
- Mission: Provide a practical school clinic (UKS) system to record and manage health visits, master data, and operational reports with role-based governance.

## Product Purpose
LabsHealth UKS is a Laravel web application for daily UKS operations in a school context. It is designed to:

- Record health visits from students (`SMA`), teachers (`GURU`), employees (`KARYAWAN`), and external/public visitors (`UMUM`)
- Maintain master data for students, employee/staff, diseases, medications, and student class history
- Produce monthly recap reports and `Acc Pulang` reports
- Import legacy or bulk data from Excel/CSV
- Enforce access through role + permission rules and dynamic menu visibility

## Primary Users and Roles
The system seeds and uses these main roles:

- `superadmin`: Full access. Bypasses permission checks in middleware.
- `admin`: Manages users, roles, permissions, menus, and master/admin data according to assigned permissions.
- `petugas_uks`: Operational role for UKS visit recording and reporting (actual capabilities depend on attached permissions).

Permission model:

- Permissions are route-name based (`admin.users.index`, `visits.create`, etc.).
- Middleware (`permission`) checks named routes against user permissions.
- Route names like `login`, `logout`, Google OAuth routes, and `dashboard` are skipped from strict permission checks.
- `permission:sync` command can generate permissions from named routes.

Navigation model:

- Sidebar menus are stored in DB (`menus` table) and filtered by:
1. Active status
2. Menu-role relation (`menu_role`)
3. Optional `permission_name` against user permissions
- `superadmin` can see all active menus.

## Core Workflows
### 1. Authentication
- Email/password login via `LoginController`
- Optional Google OAuth login via Socialite (`GoogleController`)
- Inactive users (`is_active = false`) are blocked from login/use

### 2. Visit Recording (CRUD)
- Main resource: `visits`
- Data is created/updated via `VisitRequest` validation and category-specific cleanup logic.

Patient category logic:

- `SMA`: requires `student_id`; class snapshot is stored to `class_at_visit` from active class history when available.
- `GURU` / `KARYAWAN`: requires `employee_id`; class field is not used in this flow.
- `UMUM`: uses `external_patient_name` and `additional_info`; `patient_name` is set from external name.

Important visit operational fields:

- `visit_date`, `visit_time`
- `patient_category`, `patient_name`, `gender`
- `student_id`, `employee_id`
- `class_or_department`, `class_at_visit`
- `complaint`, `therapy`, `notes`
- `disease_id`, `medication_id`
- `is_acc_pulang`, `acc_pulang_reason`
- `created_by`

### 3. Reporting
- `reports.monthly`: monthly visit recap
- `reports.acc-pulang`: monthly recap filtered by `is_acc_pulang = true`
- Both use `ReportService` for aggregation
- Exports:
1. Excel (`reports.export-excel`)
2. PDF (`reports.export-pdf`)
3. Browser print view from report page

### 4. Import Operations
- Admin import module supports:
1. `students`
2. `employees`
3. `diseases`
4. `medications`
5. `visits` (legacy + spreadsheet/manual recap format)
- Import history shown in `import_logs`
- Failed rows are returned and shown with reasons/errors for operator review

## Domain Model
Key entities and relations:

- `Visit`
  - belongsTo: `User` (`created_by` as `creator`)
  - belongsTo: `Disease`, `Medication`, `Student`, `Employee`
  - uses `SoftDeletes`
  - uses `Auditable` trait (writes to `audit_logs`)

- `Student`
  - hasMany: `Visit`, `StudentClassHistory`
  - hasOne active class (`activeClass` where `is_active = true`)

- `StudentClassHistory`
  - belongsTo: `Student`
  - keeps class transitions (`class_name`, `academic_year`, `is_active`)

- `Employee`
  - hasMany: `Visit`

- `Disease`
  - hasMany: `Visit`

- `Medication`
  - hasMany: `Visit`

- `ImportLog`
  - belongsTo uploader `User` via `uploaded_by`
  - stores import file and row stats

- `AuditLog`
  - polymorphic-style auditable reference (`auditable_type`, `auditable_id`)
  - stores event + old/new values and request metadata

- `User`
  - belongsToMany: `Role` (`role_user`)
  - role/permission helpers: `isSuperAdmin`, `hasPermission`, etc.

- `Role`
  - belongsToMany: `User`, `Permission`, `Menu`

- `Permission`
  - belongsToMany: `Role`

- `Menu`
  - parent-child tree
  - belongsToMany: `Role`
  - optional route + permission mapping for visibility

## Reporting and Exports
`ReportService::getMonthlyReport(month, year, type)`:

- Pulls visits by month/year
- Optional filter for `acc_pulang`
- Groups by `disease_id`
- Computes per-category counts: `SMA`, `GURU`, `KARYAWAN`, `UMUM`
- Produces row totals and grand totals
- Sorts by total descending

Export formats:

- Excel through `MonthlyReportExport` (heading + totals row styling)
- PDF via DomPDF using dedicated Blade template
- Printable HTML table from monthly report view

## Import Pipelines
Supported import types and expected template headers:

- `students`:
  - `nis`, `name`, `gender`, `class_name`, `academic_year`
- `employees`:
  - `nip`, `name`, `gender`, `role_type`, `department`
- `diseases`:
  - `name`, `category`
- `medications`:
  - `name`, `category`
- `visits`:
  - Current import header: `visit_date`, `visit_time`, `patient_name`, `position`, `complaint`, `disease_name`, `therapy`, `medication`, `acc_pulang`, `officer_name`, `notes`

Visit import behavior:

- Primary identity lookup is by `kelas/pegawai` + `nama pasien`:
  - `SMA`: match student name + active class (`students` + `student_class_histories`)
  - `GURU/KARYAWAN`: match employee name + department/role context (`employees`)
- Legacy fallback remains available via `patient_type` + `patient_identifier` (NIS/NIP) when provided.
- Unmatched identity rows are marked failed with reason "identitas pasien tidak ditemukan".
- Disease text is normalized with `firstOrCreate` on `diseases.name`.
- Medication text is normalized with `firstOrCreate` on `medications.name` and stored as `medication_id`.
- Date and time accept Excel serial values and parseable strings.
- Import writes success/failed row counts to `import_logs` and returns failed row details for UI review.

## Access Control and Navigation
Middleware (`CheckPermission`) behavior:

- Redirects unauthenticated users to login
- Forces logout for inactive users
- Allows all routes for `superadmin`
- Skips checks on route names: `dashboard`, auth/login/logout/google routes
- For other named routes, requires user permission exactly matching route name

Menu visibility (`MenuService`) behavior:

- Loads active root menus + active children
- Applies role-filter (if menu has role assignments)
- Applies permission-filter (if `permission_name` is set)
- Recursively removes invisible children
- Hides empty container menus without route targets

## Tech and Runtime Notes
- Backend: Laravel 12
- PHP target: `^8.2`
- Auth: session auth + optional Google OAuth (Socialite)
- Frontend: Blade templates, Bootstrap 5, Bootstrap Icons, Select2 (AJAX search)
- Reporting libs: `maatwebsite/excel`, `barryvdh/laravel-dompdf`
- PWA-lite: `manifest.json`, `sw.js`, `offline.html`
- Queue/session/cache configured for database in current environment
- Default tests are still boilerplate examples only; no meaningful automated coverage of business flows yet

## Known Gaps / Cautions for AI
Current repository inconsistencies to check before implementing new changes:

- `Visit` model fillable does not include some visit fields used in form/controller flow (`external_patient_name`, `additional_info`).
- `Employee` model fillable omits `gender` while schema and import logic use it.
- Import classes update `status` on `ImportLog`, but `import_logs` migration does not define a `status` column.
- Some `Route::resource` routes (notably `show` in admin master resources) are present while corresponding `show` methods are not implemented in `StudentController`, `EmployeeController`, and `DiseaseController`.
- `UserSeeder` uses default password literal `'password'`; treat only as local bootstrap.

## Safe Change Playbook for Future AI
Before coding:

1. Read `routes/web.php` and confirm route-name impact on permissions.
2. Check migrations and compare with model `$fillable` and validation rules.
3. Validate patient-category branching (`SMA/GURU/KARYAWAN/UMUM`) before touching visit logic.
4. Confirm import column schema, validation, and DB columns are aligned.
5. Preserve role-permission-menu architecture; avoid bypass shortcuts except established `superadmin` behavior.

After coding:

1. Run route sanity checks (`php artisan route:list`).
2. Run permission sync when route names change (`php artisan permission:sync`).
3. Run tests (`php artisan test`) even if coverage is minimal.
4. Re-check report outputs and import error reporting if visit/data model is changed.

## Quick Command Reference
Setup / install:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
```

Local development:

```bash
composer run dev
```

Useful maintenance / diagnostics:

```bash
php artisan route:list --except-vendor
php artisan permission:sync
php artisan test
php artisan config:clear
php artisan cache:clear
php artisan view:clear
```

Optional report/import related checks:

```bash
php artisan about
php artisan migrate:status
```

## Public APIs / Interfaces / Types Changes
- No runtime API or database schema change in this context file work.
- Documentation interface addition: root `AI_CONTEXT.md` is now the canonical AI context contract for this repository.
