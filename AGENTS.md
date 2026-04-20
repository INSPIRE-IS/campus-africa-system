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
- `booking_summary` logic (BookingReportController): counts only bookings for the selected year where `booking.cancelled != '1'`, StudentsInfo exists for `userUid`+`year`, and `StudentsInfo.rob_60_cancelled != 1`. Allocation rules: allocated if `rob_10_fee == '2'` and `rob_40_bed == '2'`; waitlisted if `rob_10_fee == '2'` and `rob_40_bed != '2'`. Room types exclude "Building Manager"; properties exclude `status != null` and (for year >= 2026) residence IDs 3,4,7. SQL note: in MySQL, `booking.cancelled != 1` excludes NULLs; use `(cancelled != 1 OR cancelled IS NULL)` to match UI counts.
- Temporary: set booking fee to 0 for sharing rooms in `ApplicationController` around line 780.
- ROB rules note: if an `other*` document is created then later cancelled/removed, ensure `rob_45_other` is reset (otherwise approvals can be blocked even when all other ROB steps are approved). Example seen: all `rob_*` steps approved but `rob_45_other` stuck at `1`.
- Temp lease window for showing the generate button is computed in `HomeController::check_user_status()` using `Lookups::TEMP_LEASE_WINDOW` (Jan 1 through end-of-Feb when the end day is configured as Feb 28, to handle leap years).
- The admin UI shows the temp-lease generate button only when `generate_temp_lease` is true, `rob_51_temp_lease_approve == 3`, and `role_allow_generate_temp_lease == 1` (note `check_user_status()` currently returns `role_allow_generate_temp_lease = 1` without checking the logged-in user).
- Temp lease status mapping is documented as `0=Rejected, 1=Approved, 2=Pending, 3=Not Set` (migration comment + UI mapping), but `createTempLeasePDF()` and `resendTempLease()` currently set `rob_51_temp_lease_approve` to `0`, which can show "Rejected" immediately after generation. Keep this mismatch in mind when fixing flow/state bugs.
- Temp lease flow (current): generate button -> `/generate-temp-lease` sets `rob_51_temp_lease_approve = 2` and logs action -> `/createTempLease/{studentId}` builds the PDF, creates/updates `tempMoveIn`, creates a temp-lease token, emails the signing link, then sets `rob_51_temp_lease_approve = 0`; signing `/save-signed-temp-lease` stores the signed PDF, marks `tempMoveIn` as Uploaded, sets `rob_51_temp_lease_approve = 2`; admin approval/rejection via `action_data` sets `rob_51_temp_lease_approve` to 1/0 and updates `tempMoveIn` status.
- Temp lease regen/resend buttons in `app_profile.blade.php` are gated by `role_allow_generate_temp_lease`.
- Lease buttons in `app_profile.blade.php`: **Regen** calls `/createLease` or `/createTempLease` (rebuilds the PDF, overwrites/creates `moveIn`/`tempMoveIn`, resets status to Rejected, creates a new token, emails link). **Resend** calls `/resendLease` or `/resendTempLease` (invalidates old token, creates a new token, emails link; does not rebuild the PDF). Use regen when content needs updating; use resend when only the link/email needs to be reissued.
- Temp lease approve/reject in the document modal now updates `tempMoveIn` UI immediately (status/banner + close modal) to match `moveIn` behavior.
- Reports view: sections for All Documents / Institution & Funding Docs / Compatibility are gated by `role_all_apps_report` + `role_lookup` (not `role_manage_users`).
- Reports downloads:
  - `/download-selected-docs` (reports page) dispatches `DownloadSelectedDocsJob` -> `download:selected_docs` (console). Filters are applied when values are not "All": `year`, `property`, `institution`, `studentType`, and `lease_filter`. The command only zips `user_files.fileId = moveIn` for the selected year, and `lease_filter` maps to `Uploaded`/`Approved` status.
  - Reports buttons are wired via jQuery click handlers; avoid duplicate inline `onClick` to prevent double job dispatch.
- Step 5 booking guardrails: `ApplicationController::step_5()` now checks auth before starting a DB transaction, loads booking via `StudentsInfo.bookingId` with fallback to user+year, and logs out + redirects to login if the booking is missing or cancelled.
- Admin "Add Document" (app profile) is gated by: allowed residence, `role_upload_docs == 1`, `rob_60_cancelled == 0`, and either `rob_50_approve == 0` or (`role_modify_approved == 1` and `rob_50_approve == 2`). See `resources/views/admin/app_profile.blade.php`.
- Admin "Update Rates" button (`#openPaymentModal` in `app_profile.blade.php`) is shown only when `Auth::user()->role_sys_admin == 1`. It opens `#paymentModal`, which posts to `/update-booking-payment` (`HomeController::updateBookingPayment`) to update `booking_payments.monthly_rental` and `funded_monthly`, and recalculates `monthly_rental_total` and `total_funded` as `* 10`. The controller logs an `ActionList` entry "Booking Payment Updated"; there is no server-side role check beyond auth, so UI gating is the only access control.
- Cancel booking vs deallocate bed (admin app profile):
  - Cancel booking (`POST /cancel/booking` → `HomeController::cancel_booking`): sets `StudentsInfo.rob_60_cancelled=1`, `rob_40_bed=1`, `rob_50_approve=0`; clears booking bed fields (`property_data_id`, floor/unit/room/bed/bed_code), clears `room_rate`, sets `booking.cancelled=1`; clears `Property.user_id_room` for the user; deletes lease file (`user_files.fileId=moveIn`); logs `ActionList` with reason if provided.
  - Deallocate bed (`POST /unallocate-bed` → `HomeController::unallocate_bed`): clears booking bed fields and `Property.user_id_room`; sets `rob_40_bed=1`, `rob_50_approve=0`; deletes lease file (`moveIn`); logs `ActionList` "Unallocated Bed …". It does **not** set `rob_60_cancelled` or `booking.cancelled`.
  - UI entry points: `resources/views/admin/app_profile.blade.php` uses `cancelBooking(bookingId, userId)` (role `role_cancel_booking`) and bed deallocation via `unallocate_bed` in the bed‑change flow (role `role_change_bed`).
- Bed selection and residence permissions (app profile):
  - Student type is set in `/student/edit/{studentId}` (controller: `HomeController::student_edit` + `edit_student`) and during registration/booking flows (`new_student` / `newBookingForm`). It is stored on both `users.studentType` and `students_info.studentType` for the selected year.
  - Allowed residences for bed selection are gated by `institution_properties` for the student's `institutionStudy` and funding type (columns: `cash`, `bursary`, `nsfas`). This is separate from room-type eligibility shown on `/room-types`.
  - The "Residence Permissions" admin checkbox UI updates `institution_properties` (per institution). If a property is unchecked for a funding type, it will show as "Not Allowed" in the bed-change modal.
  - Property permissions for the logged-in admin (table `property_permission`) are also enforced in `/check-rooms/{userId}`. If a residence is "Not Allowed", the option is disabled; the property value stays blank, so `/get-booking-info` returns no beds.
  - `/check-rooms/{userId}` and `/check-all-rooms/{userId}` build the residence/room-type lists for the bed-change modals.
  - `/get-booking-info` (POST) builds floor/unit/bed lists from `property_data` where `room_available IS NULL`, `inventory=1`, and year is within `year`/`year_end`. It marks beds as booked if a current-year booking exists and the student is not approved/cancelled.
- Booking fee requirement logic (UI + reports): `proof_of_payment` is required only when booking fee > 0 and not waived (`booking_fee_waived` or `rob_b_waived`). If fee is 0/null or waived, PoP should be omitted from required docs and `rob_10_fee` should be `2`.
- Irregularity Report (app profile) is visible to `role_sys_admin` and `role_bm_report`. Actions require a comment (stored in `comments` + `ActionList`). Current actions: recompute `rob_45_other` from other docs + waiver flags; clear booking fee requirement (sets PoP approved + `rob_10_fee=2`) when fee is not required; **fix bed allocation** when `bed_code` is set but `rob_40_bed != 2` (sets `rob_40_bed=2`).
- ROB anomaly report (`report:rob-anomalies`) flags bed allocation issues when `bed_code` is set but `rob_40_bed != 2` (included for approval-ready‑except‑bed, or when `--include-not-ready` is used).
- Report command `report:rob-anomalies`: by default only includes approval-ready students (rob_15/20/25/30/35/40 all `2` and `rob_50_approve != 2`). Use `--include-not-ready` to include earlier-stage records. Flags: `rob_45_other` mismatch, `rob_10_fee != 2` when fee not required, PoP missing/not approved when fee required, missing booking/payment. Output includes room type, bed code, bookingStatus.
- Lease templates show admin fee/deposit as 0 unless `studentType` is `Cash / Direct` or `Bursary` with `nsfas_bursar != 1` (or waived flags). This means `NRF` currently falls into the "0" path.
- Lease contact display logic (kin/sponsor): templates use `LeaseContactService::resolve($data)` to render Next‑of‑Kin and parent/guardian acknowledgement. It shows kin if present, otherwise falls back to sponsor fields; if both are blank, it renders placeholders. The `app_parent_kin` checkbox only copies sponsor -> kin at save time (see `ApplicationController::step_3_kin`), and studentType only affects onboarding requirements, not lease rendering.
- Lease generation guard: `PDFController::createPDF()` now blocks lease generation with a 422 error if required Next‑of‑Kin fields are missing, aligned to the Step 3 Next‑of‑Kin requirements (see `LeaseContactService::missingFields()`), so admins must complete those fields before generating a lease.
- Debt Assurity (cash only):
  - `user_files.fileId = assurity_doc`, status tracked by `students_info.rob_65_debt` using temp-lease style semantics (`0=Rejected, 1=Approved, 2=Pending, 3=Not Set/Not Required`).
  - Tokens stored in `assurity_tokens`; sponsor signs via `/sign-assurity/{token}` and `POST /save-signed-assurity/{token}`.
  - Admin actions (sys admin only): `POST /generate-assurity/{studentId}` (regen + send link) and `POST /resend-assurity` (resend link).
  - Auto-trigger: after Step 5 payment submission, the system auto-requests Debt Assurity for Cash students (emails sponsor signing link + emails student reminder). Implemented in PayGate return handlers and PoP upload handler.
  - Lease generation + final approval blocked when cash student and `rob_65_debt != 1`.
- Cash sponsor consent flags (Step 2 sponsor flow):
  - On `/step-2/online-application` for `Cash / Direct`, the sponsor form requires two acknowledgements:
    - `c_credit_risk`: student acknowledges approval is subject to sponsor meeting Campus Africa credit risk profile and/or a payment arrangement.
    - `c_credit_check`: student confirms sponsor consents to a credit check for the application.
  - Stored on `students_info` (and legacy `users`) and validated client-side in `resources/views/application/step_2_spon.blade.php`.
- Auth / roles / permissions (important distinctions):
  - `users.userRole` is a coarse "account type" flag used across controllers:
    - `0` = student/applicant (portal).
    - `1` = system user (admin/staff). The current admin add/edit flow hard-codes system users to `userRole = 1`.
    - Some legacy checks also allow `userRole` `2`/`3`, but the current admin add/edit flow does not set those values (2/3 appear to be legacy).
  - `users.template` is the "Permissions Template" preset used in the admin add/edit UI to auto-check permission checkboxes:
    - `1` = Sales Team, `2` = Building Manager, `3` = HO Approver (see `resources/views/admin/userAdd.blade.php` and `resources/views/admin/userEdit.blade.php`).
    - This is a convenience preset only; the effective access control is determined by the per-permission columns (`role_*`).
  - Document approve/reject endpoint: `POST /action/file` (`HomeController::action_file`) currently gates only on `Auth::user()->userRole` being `1/2/3` (i.e., "not a student"), and does not enforce `role_approve_docs` server-side. UI gating may hide buttons, but the API will still accept requests from any staff account.
- Staging uses new funding-type fee/deposit logic (via lookup `deposit_applies` + lease fee config) in the application/admin flows, but lease PDFs still use the older studentType branching. Live fix should be the temporary studentType adjustment; full alignment with funding rules to follow when staging changes are approved for live.
- Property active/inactive: `property.status` remains legacy for application/control flow. New `property.active_date` / `property.inactive_date` are introduced for year‑based reporting filters (reports page only for now). Long term, the goal is to migrate more logic to date‑based activity and reduce reliance on `status`.
- TODO: add login/session tracking (prefer session rows with `user_id`, `login_at`, `logout_at` nullable, `ip_address`, `user_agent`, `session_id`, optional `login_method`; or at least `users.last_login_at` + `last_login_ip`).- Lease PDFs now render admin fee/deposit directly from `BookingPayment` with waiver flags only (no studentType branching) to align with funding lookup logic.
- `temp/` (gitignored) is a scratchpad folder for investigations and reference documents (not used by app code). Current contents:
  - `temp/CLIENT_SIGNOFF.md`: draft acceptance checklist for the assurity + related flow changes (compare/merge with root `CLIENT_SIGNOFF.md` as needed).
  - `temp/DEED OF SURETY.docx`: source legal wording used to build/update `resources/views/PDF/assurity.blade.php`.
  - `temp/laravel260319ca.log`: captured Laravel log snapshot used for debugging production/staging issues.
  - `temp/REVIEWED_VIEWS.md`: ad-hoc notes/review findings for specific Blade views (e.g., admin maintenance/update screens).
  - `temp/TODOS1.md`: scratch TODO list (may overlap with root `TODOS.md` / `TODOS1.md`).
  - `temp/UPGRADE.md`: Laravel upgrade research notes (dependency blockers, risks, staged upgrade plan).
## Project TODOs
- See `TODOS.md`
- Admin "Add Document" (app profile) is gated by: allowed residence, `role_upload_docs == 1`, `rob_60_cancelled == 0`, and either `rob_50_approve == 0` or (`role_modify_approved == 1` and `rob_50_approve == 2`). See `resources/views/admin/app_profile.blade.php`.
- Lease templates show admin fee/deposit as 0 unless `studentType` is `Cash / Direct` or `Bursary` with `nsfas_bursar != 1` (or waived flags). This means `NRF` currently falls into the "0" path.
- Staging uses new funding-type fee/deposit logic (via lookup `deposit_applies` + lease fee config) in the application/admin flows, but lease PDFs still use the older studentType branching. Live fix should be the temporary studentType adjustment; full alignment with funding rules to follow when staging changes are approved for live.


