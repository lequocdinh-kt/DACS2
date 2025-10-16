<?php
// User DB helper functions
require_once __DIR__ . '/database.php';

/**
 * Get user by username or email
 * @param PDO $db
 * @param string $identifier username or email
 * @return array|false
 */
function get_user_by_username_or_email($identifier) {
    global $db;
    $sql = 'SELECT userID, username, password, email, roleID FROM `user` WHERE username = :ident OR email = :ident LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':ident', $identifier);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user by email only
 * @param string $email
 * @return array|false
 */
function get_user_by_email($email) {
    global $db;
    $sql = 'SELECT userID, username, password, email, roleID FROM `user` WHERE email = :email LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Get user by id
 */
function get_user_by_id($id) {
    global $db;
    $sql = 'SELECT userID, username, email, roleID FROM `user` WHERE userID = :id LIMIT 1';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Create a new user
 */
function create_user($data) {
    global $db;
    $sql = 'INSERT INTO `user` (username, password, email, phone, dateOfBirth, sex, cccd, roleID) VALUES (:username, :password, :email, :phone, :dob, :sex, :cccd, :role)';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':username', $data['username']);
    $stmt->bindValue(':password', password_hash($data['password'], PASSWORD_DEFAULT));
    $stmt->bindValue(':email', $data['email']);
    $stmt->bindValue(':phone', $data['phone'] ?? null);
    $stmt->bindValue(':dob', $data['dateOfBirth'] ?? null);
    $stmt->bindValue(':sex', $data['sex'] ?? null);
    $stmt->bindValue(':cccd', $data['cccd'] ?? null);
    $stmt->bindValue(':role', $data['roleID'] ?? 2, PDO::PARAM_INT);
    if ($stmt->execute()) {
        return $db->lastInsertId();
    }
    return false;
}

/**
 * Check if username or email exists
 */
function email_or_username_exists($identifier) {
    global $db;
    $sql = 'SELECT COUNT(*) FROM `user` WHERE username = :ident OR email = :ident';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':ident', $identifier);
    $stmt->execute();
    return $stmt->fetchColumn() > 0;
}


/**
 * Authenticate user by email + password
 * Returns user array on success, false on failure
 */
function authenticate_user($email, $password) {
    $user = get_user_by_email($email);
    if (!$user) return false;
    if (password_verify($password, $user['password'])) {
        // remove password before returning
        unset($user['password']);
        return $user;
    }
    return false;
}

/**
 * Update last login or other metadata (optional)
 */
function update_last_login($userID) {
    global $db;
    $sql = 'UPDATE `user` SET last_login = NOW() WHERE userID = :id';
    $stmt = $db->prepare($sql);
    $stmt->bindValue(':id', $userID, PDO::PARAM_INT);
    try { return $stmt->execute(); } catch (Exception $e) { return false; }
}

?>
