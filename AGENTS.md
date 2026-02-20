# AGENTS.md

## Project overview
- Laravel 8 application (PHP ^7.3 or ^8.0)
- Frontend: Laravel Mix + Vue 2 + Bootstrap 4 + jQuery
- Database: MySQL 5.7 (see docker-compose)

## Repo layout
- app/: application code (models, controllers, services, jobs, traits)
- routes/: web and API routes
- resources/: Blade views, JS, and SCSS sources
- public/: built assets and public entrypoint
- database/: migrations, factories, seeders
- tests/: PHPUnit tests

## Setup (local)
- Install PHP deps: `composer install`
- Install JS deps: `npm install` (or `yarn`)
- Update `.env` values for local settings
- Generate app key if needed: `php artisan key:generate`

## Setup (Docker)
- Start: `docker compose up --build`
- App: http://localhost:8000
- MySQL: localhost:33066, db `campus_africa`, user `root`, password `root`

## Common commands
- Dev server: `php artisan serve`
- Migrate: `php artisan migrate`
- Queue worker: `php artisan queue:work`
- Build assets: `npm run dev` / `npm run prod`
- Watch assets: `npm run watch` / `npm run hot`

## Tests
- Run tests: `./vendor/bin/phpunit` (or `php artisan test`)

## Code style
- PHP: StyleCI Laravel preset (see `.styleci.yml`)
- JS/CSS: Prettier config in `.prettierrc` (tabWidth 4, printWidth 160)
- Follow Laravel conventions; prefer existing services/traits when extending behavior

## Notes
- Do not commit secrets from `.env`
- Use `artisan` generators for new models, controllers, and migrations
- Application status logic: there is legacy logic tied to user files and flags (e.g., mapping `fileType` like `proof_of_payment`, `copyID`, `studentCard`, `proofRegistration`, `bursaryApproval`, `bankStatement`, `moveIn`, and `other*` to status fields such as `fee_approved`, `id_data_approved`, `student_card_approved`, `registration_approved`, `bursary_approved`, `bank_approved`, and `rob_*` steps). Be mindful of cancelled bookings and soft-deleted records when investigating inconsistencies.
- When investigating application status for a student, the admin suggested checking whether the user has linked files; current code paths may loop through files per user, which is heavy and not necessarily useful as-is. Consider summarizing counts or aggregations instead of per-file loops.
- The `booking-summary` view/function reports per-property totals (beds, bookings allocated, waiting list, booking %). Keep this in mind for future analysis; we can review this function together when ready.
- `booking_summary` logic (BookingReportController): counts only bookings for the selected year where `booking.cancelled != '1'`, StudentsInfo exists for `userUid`+`year`, and `StudentsInfo.rob_60_cancelled != 1`. Allocation rules: allocated if `rob_10_fee == '2'` and `rob_40_bed == '2'`; waitlisted if `rob_10_fee == '2'` and `rob_40_bed != '2'`. Room types exclude "Building Manager"; properties exclude `status != null` and (for year >= 2026) residence IDs 3,4,7.
- Temporary: set booking fee to 0 for sharing rooms in `ApplicationController` around line 780.
- ROB rules note: if an `other*` document is created then later cancelled/removed, ensure `rob_45_other` is reset (otherwise approvals can be blocked even when all other ROB steps are approved). Example seen: all `rob_*` steps approved but `rob_45_other` stuck at `1`.
- Temp lease window for showing the generate button is computed in `HomeController::check_user_status()` using `Lookups::TEMP_LEASE_WINDOW` (Jan 1 through end-of-Feb when the end day is configured as Feb 28, to handle leap years).
- The admin UI shows the temp-lease generate button only when `generate_temp_lease` is true, `rob_51_temp_lease_approve == 3`, and `role_allow_generate_temp_lease == 1` (note `check_user_status()` currently returns `role_allow_generate_temp_lease = 1` without checking the logged-in user).
- Temp lease status mapping is documented as `0=Rejected, 1=Approved, 2=Pending, 3=Not Set` (migration comment + UI mapping), but `createTempLeasePDF()` and `resendTempLease()` currently set `rob_51_temp_lease_approve` to `0`, which can show "Rejected" immediately after generation. Keep this mismatch in mind when fixing flow/state bugs.
- Temp lease flow (current): generate button -> `/generate-temp-lease` sets `rob_51_temp_lease_approve = 2` and logs action -> `/createTempLease/{studentId}` builds the PDF, creates/updates `tempMoveIn`, creates a temp-lease token, emails the signing link, then sets `rob_51_temp_lease_approve = 0`; signing `/save-signed-temp-lease` stores the signed PDF, marks `tempMoveIn` as Uploaded, sets `rob_51_temp_lease_approve = 2`; admin approval/rejection via `action_data` sets `rob_51_temp_lease_approve` to 1/0 and updates `tempMoveIn` status.
- Temp lease regen/resend buttons in `app_profile.blade.php` are gated by `role_allow_generate_temp_lease`.
- Temp lease approve/reject in the document modal now updates `tempMoveIn` UI immediately (status/banner + close modal) to match `moveIn` behavior.
- Reports view: sections for All Documents / Institution & Funding Docs / Compatibility are gated by `role_all_apps_report` + `role_lookup` (not `role_manage_users`).
- Step 5 booking guardrails: `ApplicationController::step_5()` now checks auth before starting a DB transaction, loads booking via `StudentsInfo.bookingId` with fallback to user+year, and logs out + redirects to login if the booking is missing or cancelled.
## Project TODOs
- See `TODOS.md`
