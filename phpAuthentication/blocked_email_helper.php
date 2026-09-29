<?php
/**
 * YP4K Resort - Blocked Email Helper
 * Creates the blocked_emails table and checks whether an address is blocked.
 */

function ensure_blocked_emails_table(?mysqli $conn): bool {
    if (!$conn) {
        return false;
    }

    $sql = "CREATE TABLE IF NOT EXISTS `blocked_emails` (
        `id` INT NOT NULL AUTO_INCREMENT,
        `email` VARCHAR(255) NOT NULL,
        `user_id` INT DEFAULT NULL,
        `reason` VARCHAR(255) DEFAULT NULL,
        `blocked_by` INT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `uniq_blocked_email` (`email`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $created = (bool) $conn->query($sql);
    $conn->query("ALTER TABLE `blocked_emails` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

    return $created;
}

function normalize_blocked_email(mixed $email): string {
    return strtolower(trim((string) $email));
}

function is_email_blocked(?mysqli $conn, mixed $email): bool {
    if (!$conn) {
        return false;
    }

    ensure_blocked_emails_table($conn);

    $email = normalize_blocked_email($email);
    if ($email === '') {
        return false;
    }

    $stmt = $conn->prepare("SELECT id FROM blocked_emails WHERE email = ? LIMIT 1");
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $blocked = $result && $result->num_rows > 0;
    $stmt->close();

    return $blocked;
}

function blocked_email_message($context = 'login') {
    switch ($context) {
        case 'register':
            return 'This email address has been blocked and cannot be used to register.';
        case 'forgot':
            return 'This email address has been blocked. Password reset is not allowed.';
        case 'login':
        default:
            return 'This email address has been blocked and cannot log in.';
    }
}

function kick_blocked_user_session(?mysqli $conn, bool $jsonResponse = false): void {
    if (!$conn || !isset($_SESSION)) {
        return;
    }

    $userEmail = $_SESSION['user_email'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;

    if (empty($userEmail) && $userId) {
        $stmt = $conn->prepare("SELECT email FROM users WHERE id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result && $result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $userEmail = $row['email'] ?? '';
                if ($userEmail !== '') {
                    $_SESSION['user_email'] = $userEmail;
                }
            }
            $stmt->close();
        }
    }

    if ($userEmail !== '' && is_email_blocked($conn, $userEmail)) {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();

        if ($jsonResponse) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => blocked_email_message('login'),
                'blocked' => true
            ]);
            exit;
        }

        header('Location: ../Authentication/login.php?error=' . urlencode(blocked_email_message('login')));
        exit;
    }
}
