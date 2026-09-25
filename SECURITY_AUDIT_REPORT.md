# YP4K Resort — Security Audit & Vulnerability Assessment Report

**Date of Assessment:** September 20, 2026  
**Target Repository / System:** YP4K Resort - Booking & Trail Guide System  
**Audit Scope:** All server-side PHP endpoints, frontend JavaScript, configuration files, database schemas/seeders, authentication flows, and storage directories.

---

## Executive Summary

During a comprehensive security audit of the **YP4K Resort** application, multiple critical- and high-severity vulnerabilities were identified. These vulnerabilities include **hardcoded production credentials**, **direct OTP exposure via public API endpoints**, **unrestricted public administrator registration**, **arbitrary file upload leading to Remote Code Execution (RCE)**, **unauthenticated database wipe/truncate endpoints**, **sensitive data exposure in the web root**, and **complete absence of CSRF protection**.

Immediate remediation is strongly recommended before this codebase is exposed to the public Internet.

---

## Severity Breakdown

| Severity | Count | Primary Impact |
| :--- | :---: | :--- |
| **Critical** | 5 | Remote Code Execution (RCE), Full System Takeover, Account Takeover, Credential Leakage |
| **High** | 5 | Denial of Service (Database Wipe), Sensitive Financial & Personal PII Leak, Broken Authentication |
| **Medium** | 4 | Cross-Site Request Forgery (CSRF), Insecure Direct Object References (IDOR), Client Data Leakage |
| **Low / Informational** | 4 | Overly Permissive Directory Permissions, Missing Security Headers, CORS Wildcard |

---

## 1. Critical Vulnerabilities

### 1.1. Hardcoded Plaintext Google SMTP App Password & Credentials
* **Severity:** Critical (CVSS: 9.8)
* **Location:** [`phpAuthentication/email_helper.php`](file:///c:/xampp/htdocs/YP4K_Resort/phpAuthentication/email_helper.php#L19-L20)
* **Details:**  
  The application hardcodes active Google SMTP credentials directly in the source code:
  ```php
  'username' => 'yp4kvillaberderesort@gmail.com',
  'password' => 'nlujincybvvryxhq', // Active Google App Password
  ```
* **Impact:**  
  Anyone with access to the codebase (or through repository exposure / public GitHub repo) can send emails masquerading as the resort, hijack password reset workflows, or access the resort's Google services.
* **Remediation:**  
  - Immediately revoke and regenerate the Google App Password in the Google Account Security console.
  - Move all credentials into an environment variable file (`.env`) placed outside the public document root (`htdocs`).
  - Use `getenv()` or a `.env` parser like `vlucas/phpdotenv`.

---

### 1.2. Direct OTP Exposure via API (Authentication & Verification Bypass)
* **Severity:** Critical (CVSS: 9.8)
* **Location:** [`php/get_verification_data.php`](file:///c:/xampp/htdocs/YP4K_Resort/php/get_verification_data.php#L22-L32)
* **Details:**  
  The endpoint `php/get_verification_data.php` responds to client requests by outputting the plaintext verification OTP stored in session:
  ```php
  $verification_code = $_SESSION['verification_code'] ?? '';
  ...
  echo json_encode([
      'success' => true,
      'email' => $email,
      'verification_code' => $verification_code, // EXPOSED TO CLIENT
      'user_id' => $user_id,
      'user_type' => $user_type
  ]);
  ```
  Additionally, if no session exists, it accepts `$_GET['email']` and queries `otp_verifications`.
* **Impact:**  
  An attacker does not need access to the victim's email inbox to complete registration or account verification. Any attacker who registers an email can instantly fetch the OTP from the JSON response and automatically verify the account.
* **Remediation:**  
  - Never return `verification_code` or `otp` in HTTP responses.
  - The client only needs confirmation that a verification email was dispatched.

---

### 1.3. Public Administrator Self-Registration
* **Severity:** Critical (CVSS: 9.8)
* **Locations:**  
  - [`Authentication/admin-register.php`](file:///c:/xampp/htdocs/YP4K_Resort/Authentication/admin-register.php)
  - [`php/admin_register_process.php`](file:///c:/xampp/htdocs/YP4K_Resort/php/admin_register_process.php)
* **Details:**  
  The admin registration flow is completely exposed to the public. There is no invitation token, secret administrator registration key, or verification check that the requester is an authorized operator.
  Combined with **Vulnerability 1.2 (OTP Leak)**, an attacker can:
  1. Submit the admin registration form at `/Authentication/admin-register.php`.
  2. Query `/php/get_verification_data.php` to obtain the OTP.
  3. Submit the OTP to `/php/verify_otp.php`.
  4. Gain full, immediate Administrator access to the resort management portal.
* **Remediation:**  
  - Completely disable or remove public admin registration routes.
  - Admin accounts must only be provisioned by existing Super Administrators via an internal, authenticated interface (`add_staff_account.php` pattern) or database CLI seeders.

---

### 1.4. Arbitrary File Upload Leading to Remote Code Execution (RCE)
* **Severity:** Critical (CVSS: 9.8)
* **Location:** [`php/add_edit_accommodation.php`](file:///c:/xampp/htdocs/YP4K_Resort/php/add_edit_accommodation.php#L45-L51)
* **Details:**  
  In `add_edit_accommodation.php`, uploaded files are moved to `assets/uploads/accommodations/` with **zero extension or MIME-type validation**:
  ```php
  $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
  $fileName = uniqid() . '.' . $fileExtension;
  $uploadPath = $uploadDir . $fileName;

  if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath)) {
      $imagePath = 'assets/uploads/accommodations/' . $fileName;
  }
  ```
  Unlike `upload_payment_proof.php` (which enforces image MIME types), this endpoint accepts arbitrary files.
* **Impact:**  
  An authenticated administrator (or an attacker who self-registered via Vulnerability 1.3) can upload a file named `shell.php`. The server will save it as `assets/uploads/accommodations/<uniqid>.php`. Navigating to that URL executes arbitrary PHP code, giving the attacker full operating system command execution on the host server.
* **Remediation:**  
  - Enforce a strict file extension whitelist (`jpg`, `jpeg`, `png`, `webp`).
  - Validate MIME types server-side using `finfo(FILEINFO_MIME_TYPE)`.
  - Validate file contents with `getimagesize()` to ensure they are valid images.
  - Disable PHP script execution inside upload directories using `.htaccess` (`php_flag engine off` / `SetHandler none`) or Nginx configuration.

---

### 1.5. Plaintext Passwords and PII Exposed in Public SQL Dumps
* **Severity:** Critical (CVSS: 9.1)
* **Locations:**  
  - [`yp4k_resort_db.sql`](file:///c:/xampp/htdocs/YP4K_Resort/yp4k_resort_db.sql)
  - [`database/yp4k_resort_db.sql`](file:///c:/xampp/htdocs/YP4K_Resort/database/yp4k_resort_db.sql)
  - [`database/yp4k_resort_db_updated.sql`](file:///c:/xampp/htdocs/YP4K_Resort/database/yp4k_resort_db_updated.sql)
* **Details:**  
  SQL database dumps containing actual production/testing user records are placed directly inside the web document root:
  - Lines 462-470 of `yp4k_resort_db.sql` reveal customer records with plaintext passwords (`"password":"Saiyon05"`, `"password":"Admin321"`), full names, personal phone numbers (`09563695447`), and email addresses.
  - Anyone visiting `http://<host>/yp4k_resort_db.sql` can download the entire resort database without authentication.
* **Remediation:**  
  - Immediately remove all `.sql` files from public web-accessible directories (`htdocs`).
  - Add `*.sql` to `.gitignore`.
  - Invalidate and force password resets for all exposed accounts.

---

## 2. High-Severity Vulnerabilities

### 2.1. Unauthenticated Database Wipe / Truncate Endpoints
* **Severity:** High (CVSS: 8.6)
* **Locations:**  
  - [`database/seed_accommodations.php`](file:///c:/xampp/htdocs/YP4K_Resort/database/seed_accommodations.php#L11)
  - [`database/seed_activities.php`](file:///c:/xampp/htdocs/YP4K_Resort/database/seed_activities.php#L14)
  - [`database/initialize_booking_system.php`](file:///c:/xampp/htdocs/YP4K_Resort/database/initialize_booking_system.php)
* **Details:**  
  These scripts are directly web-accessible without any authentication or CLI-only check:
  - `seed_accommodations.php` executes `TRUNCATE TABLE accommodations;`
  - `seed_activities.php` executes `SET FOREIGN_KEY_CHECKS = 0; TRUNCATE TABLE activities;`
* **Impact:**  
  Any internet user sending a simple GET request to `http://<host>/database/seed_accommodations.php` will permanently wipe all active resort accommodations or activities from the live database.
* **Remediation:**  
  - Restrict migration and seeding scripts to CLI execution only:
    ```php
    if (php_sapi_name() !== 'cli') {
        http_response_code(403);
        die('CLI execution only');
    }
    ```
  - Move the `database/` folder outside the public web root.

---

### 2.2. Unauthenticated Notification & User / Guest Information Leak
* **Severity:** High (CVSS: 7.5)
* **Location:** [`Admin_Dashboard/get_notifications.php`](file:///c:/xampp/htdocs/YP4K_Resort/Admin_Dashboard/get_notifications.php#L1-L35)
* **Details:**  
  This endpoint has **no session or authentication checks**:
  ```php
  <?php
  require_once __DIR__ . '/config.php';
  admin_include('db_config.php');
  global $conn;
  header('Content-Type: application/json');
  // Queries latest bookings and user full names and outputs them
  ```
* **Impact:**  
  Any anonymous internet user can periodically poll this URL to scrape newly registered user names and guest booking activities without logging in.
* **Remediation:**  
  Add admin session verification at the top of the file:
  ```php
  session_start();
  if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
      http_response_code(403);
      echo json_encode(['error' => 'Unauthorized']);
      exit;
  }
  ```

---

### 2.3. Full User Table Dumped into Client-Side DOM / JavaScript
* **Severity:** High (CVSS: 7.5)
* **Location:** [`Admin_Dashboard/users.php`](file:///c:/xampp/htdocs/YP4K_Resort/Admin_Dashboard/users.php#L24-L345)
* **Details:**  
  In `users.php`, the query uses `SELECT u.* FROM users u` and embeds the complete result set into client JavaScript:
  ```php
  const usersData = <?php echo json_encode($users); ?>;
  ```
* **Impact:**  
  The JSON payload includes sensitive columns such as password hashes (`password`), `reset_token`, `verification_token`, and `remember_token`. If an admin account has XSS or if a lower-privileged viewer accesses the page, all user hashes and reset tokens are readable from the page source.
* **Remediation:**  
  Select only non-sensitive columns (`id`, `first_name`, `last_name`, `email`, `phone_number`, `created_at`, `is_verified`). Never select or serialize `password`, `reset_token`, or session tokens.

---

### 2.4. Plaintext Passwords Stored in Registration OTP Table
* **Severity:** High (CVSS: 7.4)
* **Locations:**  
  - [`php/admin_register_process.php`](file:///c:/xampp/htdocs/YP4K_Resort/php/admin_register_process.php#L129-L139)
  - [`phpAuthentication/register_process.php`](file:///c:/xampp/htdocs/YP4K_Resort/phpAuthentication/register_process.php)
* **Details:**  
  During user/admin registration, the application serializes the raw plaintext password into `user_data` JSON and stores it in the `otp_verifications` table pending verification:
  ```php
  $userData = [
      ...
      'password' => $password // Raw plaintext password!
  ];
  $userDataJson = json_encode($userData);
  $otp_stmt->bind_param("sssss", $email, $otp, $userType, $userDataJson, $otpExpiry);
  ```
* **Impact:**  
  Anyone with temporary read access to the database or SQL backups can harvest plaintext passwords of registered users.
* **Remediation:**  
  Hash the password with `password_hash($password, PASSWORD_DEFAULT)` immediately upon receiving the registration request, before inserting it into any database table or session.

---

### 2.5. Publicly Accessible Payment Proofs & Receipts
* **Severity:** High (CVSS: 7.1)
* **Locations:**  
  - `assets/uploads/payment_proofs/`
  - `uploads/receipts/`
* **Details:**  
  Payment proofs (GCash transaction receipts containing transaction numbers, account names, phone numbers, and balances) are uploaded to web-accessible directories with predictable filenames (e.g., `proof_{user_id}_{timestamp}.png` and `receipt_{booking_id}_{timestamp}.png`).
* **Impact:**  
  Without `.htaccess` protection or authentication middleware, third parties can scrape or enumerate payment receipts, exposing customer financial and personal details.
* **Remediation:**  
  - Store payment proofs outside the web document root (e.g., `../storage/receipts/`).
  - Serve files through an authenticated download script (e.g., `download_receipt.php?id=...`) that checks whether the logged-in user owns the booking or is an admin.

---

## 3. Medium-Severity Vulnerabilities

### 3.1. Absence of CSRF (Cross-Site Request Forgery) Protection
* **Severity:** Medium (CVSS: 6.5)
* **Locations:** All state-changing endpoints in `php/`:
  - `cancel_booking.php`
  - `change_password.php`
  - `update_profile.php`
  - `block_date.php`
  - `add_edit_activity.php`
  - `add_edit_bundle.php`
  - `add_edit_accommodation.php`
  - `update_booking_status.php`
* **Details:**  
  Although `phpAuthentication/session_helper.php` defines `generate_csrf_token()` and `verify_csrf_token()`, **none** of the API endpoints validate CSRF tokens.
* **Impact:**  
  An attacker can lure a logged-in user or administrator to a malicious page that secretly posts to `php/change_password.php`, `php/cancel_booking.php`, or `php/block_date.php`, executing actions on their behalf.
* **Remediation:**  
  Include a CSRF token with every POST/PUT/DELETE request and validate it server-side using `verify_csrf_token($_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['csrf_token'])`.

---

---

### 3.3. Broken / Non-Functional Password Reset Flow
* **Severity:** Medium (CVSS: 5.0)
* **Locations:**  
  - [`phpAuthentication/forgot_password_process.php`](file:///c:/xampp/htdocs/YP4K_Resort/phpAuthentication/forgot_password_process.php#L27-L53)
* **Details:**  
  - Line 27 queries column `firstName` from `users`, but the actual schema uses `first_name`. This triggers a database exception when requested.
  - Lines 46-48 have email delivery commented out:
    ```php
    // sendPasswordResetEmail($user['email'], $reset_token, $user['firstName']);
    ```
  - Line 53 writes the reset token to the server's error log.
* **Impact:**  
  Users cannot legitimately reset their passwords. If error logs are exposed, password reset tokens are leaked.
* **Remediation:**  
  Fix column name to `first_name` and enable `EmailHelper::sendPasswordResetEmail()`.

---

### 3.4. Session Inconsistencies & Authentication Guards Not Applied
* **Severity:** Medium (CVSS: 4.8)
* **Locations:**  
  - [`phpAuthentication/session_helper.php`](file:///c:/xampp/htdocs/YP4K_Resort/phpAuthentication/session_helper.php#L37-L44)
  - [`phpAuthentication/login_process.php`](file:///c:/xampp/htdocs/YP4K_Resort/phpAuthentication/login_process.php#L47-L50)
  - [`Staff_Dashboard/settings.php`](file:///c:/xampp/htdocs/YP4K_Resort/Staff_Dashboard/settings.php#L1072)
* **Details:**  
  - `session_helper.php` requires `$_SESSION['logged_in'] === true`, but `login_process.php` never sets `$_SESSION['logged_in']`.
  - Neither `require_admin_login()` nor `require_user_login()` are ever invoked in the endpoints.
  - `Staff_Dashboard/settings.php` calls `php/change_password.php`, which only queries `users` with `$_SESSION['user_id']`. Because staff are authenticated in `admins` with `$_SESSION['admin_id']`, staff members cannot update their password.

---

## 4. Low & Informational Findings

| Finding | Details | Remediation |
| :--- | :--- | :--- |
| **Overly Permissive Permissions** | `mkdir(..., 0777, true)` is used in `add_edit_activity.php`, `add_edit_accommodation.php`, `upload_receipt.php`. | Use `0755` for directories and `0644` for files. |
| **CORS Wildcard** | `header('Access-Control-Allow-Origin: *');` in `get_packages.php`. | Restrict CORS to trusted domain origins. |
| **Cookie `secure` Flag on HTTP** | `session_helper.php` and `login_process.php` set `secure = true` cookies, which modern browsers reject over plain HTTP (local development). | Make the `secure` cookie flag dynamic: `isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on'`. |
| **Missing HTTP Security Headers** | No `Content-Security-Policy`, `X-Frame-Options: DENY`, `X-Content-Type-Options: nosniff` headers configured. | Add security headers in Apache `.htaccess` or centralized PHP configuration. |

---

## 5. Complete Inventory of Endpoints Audited

| Endpoint | Method | Authentication Guard | Key Risks Identified |
| :--- | :---: | :---: | :--- |
| `phpAuthentication/db_config.php` | N/A | Configuration | Root password blank; should use environment variables. |
| `phpAuthentication/email_helper.php` | N/A | Class / Helper | **CRITICAL:** Hardcoded Gmail SMTP password. |
| `php/get_verification_data.php` | GET | None / Weak | **CRITICAL:** Leaks verification OTP in JSON response. |
| `php/admin_register_process.php` | POST | Public | **CRITICAL:** Public admin creation + plaintext password in OTP table. |
| `Authentication/admin-register.php` | GET/POST | Public | **CRITICAL:** Public registration UI for admin accounts. |
| `php/add_edit_accommodation.php` | POST | Admin Session | **CRITICAL:** Unrestricted arbitrary file upload (RCE). |
| `database/*.sql` | GET | None (Webroot) | **CRITICAL:** Database dumps with personal emails & passwords in webroot. |
| `database/seed_*.php` | GET | None | **HIGH:** Publicly callable scripts that `TRUNCATE` tables. |
| `database/initialize_booking_system.php` | GET | None | **HIGH:** Executes SQL migration scripts without auth. |
| `Admin_Dashboard/get_notifications.php` | GET | Admin Session | Properly guarded. |
| `Admin_Dashboard/users.php` | GET | Admin Session | Properly scoped; sensitive fields stripped. |
| `php/upload_payment_proof.php` | POST | User Session | Has MIME checking; storage directory lacks direct access protection. |
| `php/upload_receipt.php` | POST | User Session | Creates `0777` dirs; stored in webroot without access restrictions. |
| `php/add_edit_activity.php` | POST | Admin Session | Extension check without MIME verification. |
| `php/add_edit_bundle.php` | POST/GET | Admin Session | Extension check only; unparameterized `DELETE` queries. |
| `php/block_date.php` | POST | Admin Session | No CSRF verification. |
| `php/cancel_booking.php` | POST | User Session | Validates ownership; lacks CSRF verification. |
| `php/update_booking_status.php` | POST | Admin Session | No CSRF verification. |
| `php/update_booking.php` | POST | Admin Session | No CSRF verification. |
| `php/change_password.php` | POST | User Session | Fails for Staff; no CSRF token check. |
| `php/update_profile.php` | POST | User Session | No CSRF token check. |
| `php/create_booking.php` | POST | User Session | Re-calculates price server-side (Good). No CSRF token check. |
| `php/process_walkin_booking.php` | POST | Admin/Staff | Validates input; no CSRF token check. |
| `php/get_all_bookings.php` | GET | Admin Session | Properly scoped to admin. |
| `php/get_booking_details.php` | GET | User/Admin | Checks ownership if user; no IDOR vulnerability found. |
| `php/get_calendar_data.php` | GET | Admin/Staff | Properly guarded. |
| `php/get_calendar_bookings.php` | GET | Admin/Staff | Properly guarded. |
| `php/get_packages.php` | GET | Public | Public read-only catalog; CORS wildcard `*`. |
| `php/get_activities.php` | GET | Public | Public read-only catalog. |
| `php/get_accommodations.php` | GET | Public | Public read-only catalog. |
| `php/get_bundle_availability.php` | GET | Public | Availability query; input parameters cast to int. |
| `php/check_availability.php` | GET | Public | Availability check; queries prepared properly. |
| `php/verify_otp.php` | POST | Public | Validates OTP hash and attempts; hashes password upon account insert. |
| `php/resend_otp.php` | POST | Public | Has cooldown verification. |
| `php/send_arrival_reminder.php` | POST | Admin Session | Properly guarded. |
| `php/fix_address_codes.php` | CLI | None | Rebuilds addresses from external API; restricted to CLI. |
| `phpAuthentication/test_registration.php` | POST | None | Debug script returning dummy OTP `123456`; should be removed. |

---

## 6. Recommended Action Plan

### Priority 1: Immediate Containment (Within 24 Hours)
1. **Revoke Google App Password:** Change/revoke `nlujincybvvryxhq` in Google account security settings immediately.
2. **Remove Exposed Files from Webroot:**
   - Delete or move `yp4k_resort_db.sql` and `database/*.sql` outside `htdocs`.
   - Remove `phpAuthentication/test_registration.php`.
3. **Patch OTP Leak:** In `php/get_verification_data.php`, remove `'verification_code' => $verification_code` from the JSON response.
4. **Disable Public Admin Registration:** Remove or restrict `Authentication/admin-register.php` and `php/admin_register_process.php`.
5. **Patch File Upload in `add_edit_accommodation.php`:** Add MIME and extension whitelisting identical to `upload_payment_proof.php`.

### Priority 2: Hardening & Access Control (Within 1 Week)
1. **Implement CSRF Verification:** Include and validate CSRF tokens on all POST/PUT requests.
2. **Hash Passwords in Pre-Registration:** Never store unhashed passwords in `otp_verifications`.
