<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$ROLE_PERMISSIONS = [
    "admin" => ["*"],
    "viewer" => [
        "stock.view",
        "batch.view",
        "drug.view",
        "location.view",
        "notice.view",
    ],
];

function normalize_role($role)
{
    $role = strtolower(trim((string) $role));
    return $role !== "" ? $role : "viewer";
}

function current_user_role()
{
    return normalize_role($_SESSION["role"] ?? "viewer");
}

function ensure_user_role($conn)
{
    if (!isset($_SESSION["user"]) || isset($_SESSION["role"])) {
        return;
    }

    $username = $_SESSION["user"];
    $stmt = $conn->prepare("SELECT role FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($role);

    if ($stmt->fetch()) {
        $_SESSION["role"] = normalize_role($role);
    } else {
        $_SESSION["role"] = "viewer";
    }

    $stmt->close();
}

function user_can($permission)
{
    global $ROLE_PERMISSIONS;
    $role = current_user_role();

    if (!isset($ROLE_PERMISSIONS[$role])) {
        $role = "viewer";
    }

    $permissions = $ROLE_PERMISSIONS[$role];
    return in_array("*", $permissions, true) || in_array($permission, $permissions, true);
}

function require_permission($permission)
{
    if (!user_can($permission)) {
        http_response_code(403);
        echo "无权限访问该功能。";
        exit();
    }
}
?>
