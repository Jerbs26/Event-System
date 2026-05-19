<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) session_start();
requireAdmin();

$conn = getConnection();

// Handle delete user
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delId = (int)$_GET['id'];
    if ($delId !== (int)$_SESSION['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
        $stmt->bind_param("i", $delId);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            setFlash('success', 'User deleted successfully.');
        } else {
            setFlash('error', 'Could not delete user (cannot delete admins).');
        }
        $stmt->close();
    } else {
        setFlash('error', 'You cannot delete your own account.');
    }
    $conn->close();
    header('Location: /event-system/admin/users.php');
    exit();
}

$users = $conn->query("
    SELECT u.id, u.name, u.email, u.role, u.created_at,
        COUNT(r.id) AS reg_count
    FROM users u
    LEFT JOIN registrations r ON u.id = r.user_id
    WHERE u.role != 'admin'
    GROUP BY u.id
    ORDER BY u.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

$conn->close();

$pageTitle = 'Manage Users';
$base = '/';
include __DIR__ . '/../includes/header.php';
?>

<div class="page-wrap">
    <div class="page-header">
        <h1>Manage Users</h1>
    </div>

    <div class="search-bar" style="margin-bottom:1.5rem;">
        <div class="search-group" style="flex:1;">
            <label class="form-label" for="searchInput">Search Users</label>
            <input type="text" id="searchInput" class="form-control" placeholder="Search by name or email...">
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Events Registered</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $i => $user): ?>
                <tr data-search="<?= htmlspecialchars(strtolower($user['name'] . ' ' . $user['email'])) ?>">
                    <td style="color:var(--text-muted); font-size:0.8rem;"><?= $i + 1 ?></td>
                    <td class="td-primary"><?= htmlspecialchars($user['name']) ?></td>
                    <td style="color:var(--text-secondary);"><?= htmlspecialchars($user['email']) ?></td>
                    <td><span class="badge badge-<?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                    <td style="text-align:center; font-weight:600; color:var(--accent);"><?= $user['reg_count'] ?></td>
                    <td style="color:var(--text-muted); font-size:0.82rem;"><?= date('M j, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if ($user['id'] !== (int)$_SESSION['user_id'] && $user['role'] !== 'admin'): ?>
                            <a href="/event-system/admin/users.php?action=delete&id=<?= $user['id'] ?>"
                                class="btn btn-danger btn-sm btn-confirm-delete">Remove</a>
                        <?php else: ?>
                            <span style="font-size:0.8rem; color:var(--text-muted);">
                                <?= $user['id'] === (int)$_SESSION['user_id'] ? '(You)' : 'Admin' ?>
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>