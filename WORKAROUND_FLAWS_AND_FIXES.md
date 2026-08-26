# CAMS Flaws And Required Fixes

## Critical Security Issues

- **Inactive users can still log in.** The login query does not filter for `status = 'active'`.
- **Forced password changes can be bypassed for newly imported OWWA users.** Imported users are marked `password_change_required = 1`, but are immediately logged in and redirected through the dashboard path.
- **There is no CSRF protection.** User creation, editing, deletion, password changes, and imports can be triggered by forged requests.
- **Setup scripts are publicly accessible.** Scripts such as `setup/reset_user_password.php`, `setup/fix_passwords.php`, and `setup/add_password_change_required.php` can modify the database without authentication.
- **Password reset uses GET and accepts any username.** Anyone who can access `setup/reset_user_password.php` can reset another user's password.
- **`setup/fix_passwords.php` resets every user's password to the same known password.**
- **The session ID is not regenerated after successful login.** This creates a session-fixation risk.
- **Database credentials are hardcoded and duplicated.** Several files connect as `root` with an empty password instead of using one secure configuration.

## Data And Authorization Problems

- **Username uniqueness is not checked** when creating or editing users.
- **Roles and status values are not validated server-side.** The application trusts POST data in the user-management forms.
- **Edit-user password length is only checked in HTML**, not in PHP.
- **User and role updates are not transactional.** A user update can succeed while role changes fail, leaving inconsistent permissions.
- **OWWA import can create duplicate or conflicting accounts**, especially during concurrent requests.
- **OWWA users receive a predictable default password**, which is also exposed in setup pages and documentation.
- **Role selection order determines the primary role**, which can cause inconsistent redirects for multi-role users.

## Information Disclosure

- Setup and test pages expose usernames, emails, roles, database state, and password-reset links.
- Database connection errors are displayed directly to users.
- `setup/setup_database.php` exposes test credentials after setup.

## Broken Or Incomplete Project Structure

- `config/database.php` is referenced throughout the application but is absent from the workspace.
- `database.sql` is referenced by the README and setup scripts but is absent from the workspace.
- The documentation claims files and features that are not currently present, so installation cannot reliably work.

## Recommended Fix Order

1. Restore and secure the database configuration and schema.
2. Remove or protect all setup and diagnostic scripts.
3. Enforce active-user checks and forced password changes in the authentication flow.
4. Add CSRF protection and regenerate the session ID after login.
5. Centralize database connections and remove hardcoded credentials.
6. Add server-side validation and database uniqueness constraints.
7. Wrap user and role updates in transactions and harden OWWA imports.