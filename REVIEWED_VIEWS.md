# Reviewed Views

## resources/views/admin/lookups/admin_updates_landing.blade.php

### Summary
- Super admins only (`Auth::user()->role_super == 1`) see a set of maintenance/update buttons.
- Each button triggers a SweetAlert confirmation, swaps label to a spinner, sends an AJAX GET to a maintenance endpoint, then restores label and shows success.

### Concerns
- Uses GET for state-changing operations (CSRF risk, accidental triggering); should be POST/PUT with CSRF and server-side authorization.
- No error handling; failures leave spinner and no feedback.
- Buttons not disabled during request; double-click can trigger duplicate updates.
- Repetitive inline JS; hard to maintain and keep consistent.

### TODOs
- Convert endpoints to POST/PUT and include CSRF tokens; verify server-side auth/role checks.
- Add error/complete handling to restore button state and show failure alerts.
- Disable buttons while requests are in-flight to prevent duplicates.
- Refactor JS into a shared handler to reduce repetition.
