<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';

// --- Simple Admin Auth (MVP) ---
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_user'], $_POST['admin_pass'])) {
        // Hardcoded admin credentials for MVP
        if ($_POST['admin_user'] === 'admin' && $_POST['admin_pass'] === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: index.php');
            exit;
        } else {
            $login_error = 'Invalid admin credentials.';
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - ICT Corps Members Hub</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
        <form method="POST" class="bg-white p-4 rounded shadow w-100" style="max-width: 400px;">
            <h2 class="mb-4 text-primary">Admin Login</h2>
            <?php if (!empty($login_error)): ?>
                <div class="alert alert-danger mb-3"> <?= $login_error ?> </div>
            <?php endif; ?>
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="admin_user" required class="form-control">
            </div>
            <div class="mb-4">
                <label class="form-label">Password</label>
                <input type="password" name="admin_pass" required class="form-control">
            </div>
            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    </body>
    </html>
    <?php
    exit;
}

// --- Admin Dashboard UI ---
$tab = $_GET['tab'] ?? 'users';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --sidebar-width: 250px;
        }
        body { background-color: #f8f9fa; }
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            background-color: #ffffff;
            border-right: 1px solid #dee2e6;
            transition: transform 0.3s ease-in-out;
            z-index: 1000;
        }
        #main-content {
            margin-left: var(--sidebar-width);
            transition: margin 0.3s ease-in-out;
        }
        .sidebar-link {
            color: #6c757d;
            text-decoration: none;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            transition: all 0.3s;
        }
        .sidebar-link.active, .sidebar-link:hover {
            background-color: #e9ecef;
            color: #0d6efd;
        }
        .sidebar-link i {
            margin-right: 10px;
            width: 20px;
        }
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .modal-backdrop { z-index: 1040; }
        .modal { z-index: 1050; }
        @media (max-width: 992px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.active { transform: translateX(0); }
            #main-content { margin-left: 0; }
            #main-content.sidebar-active { margin-left: var(--sidebar-width); }
        }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .user-table th { white-space: nowrap; }
        .navbar-toggler { display: none; }
        @media (max-width: 992px) { .navbar-toggler { display: block; } }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="py-3">
        <div class="px-3 mb-4 border-bottom">
            <h4 class="text-primary">Admin Panel</h4>
        </div>
        <div class="d-flex flex-column">
            <a href="?tab=users" class="sidebar-link<?= $tab==='users' ? ' active' : '' ?>">
                <i class="fas fa-users"></i> Users
            </a>
            <a href="?tab=events" class="sidebar-link<?= $tab==='events' ? ' active' : '' ?>">
                <i class="fas fa-calendar"></i> Events
            </a>
            <a href="?tab=resources" class="sidebar-link<?= $tab==='resources' ? ' active' : '' ?>">
                <i class="fas fa-file-alt"></i> Resources
            </a>
            <a href="?tab=settings" class="sidebar-link<?= $tab==='settings' ? ' active' : '' ?>">
                <i class="fas fa-cog"></i> Site Settings
            </a>
            <a href="?logout=1" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </nav>
    <!-- Main Content -->
    <div id="main-content">
        <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom">
            <div class="container-fluid">
                <button id="sidebarToggle" class="navbar-toggler border-0">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h1 class="navbar-brand mb-0 h1">Admin Dashboard</h1>
            </div>
        </nav>
        <div class="container-fluid py-4">
            <div class="row mb-4">
                <div class="col">
                    <h2 class="h4 text-secondary">ICT Corps Members Hub</h2>
                </div>
            </div>
            <?php
            // --- USERS TAB ---
            if ($tab === 'users') {
                // Fetch all roles for dropdown
                $roles = [];
                $roles_result = $conn->query("SELECT id, display_name FROM roles ORDER BY id");
                while ($row = $roles_result->fetch_assoc()) $roles[] = $row;
                // Define unique roles
                $unique_roles = ['president', 'vice-president', 'treasurer'];
                // Handle CRUD actions
                if (isset($_POST['delete_user_id'])) {
                    $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
                    $stmt->bind_param('i', $_POST['delete_user_id']);
                    $stmt->execute();
                    echo '<div class="alert alert-success mb-3">User deleted.</div>';
                }
                if (isset($_POST['add_user'])) {
                    $role_id = isset($_POST['role_id']) ? intval($_POST['role_id']) : null;
                    $role_name = null;
                    if ($role_id) {
                        $role_stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
                        $role_stmt->bind_param('i', $role_id);
                        $role_stmt->execute();
                        $role_res = $role_stmt->get_result();
                        $role_row = $role_res->fetch_assoc();
                        $role_name = $role_row ? strtolower($role_row['name']) : null;
                        if ($role_name && in_array($role_name, $unique_roles)) {
                            $check_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE role_id = ?");
                            $check_stmt->bind_param('i', $role_id);
                            $check_stmt->execute();
                            $check_res = $check_stmt->get_result();
                            $cnt = $check_res->fetch_assoc()['cnt'] ?? 0;
                            if ($cnt > 0) {
                                echo '<div class="alert alert-danger mb-3">This role ('.htmlspecialchars(ucwords(str_replace('-', ' ', $role_name))).') is already assigned to another member.</div>';
                                $role_id = null; // Prevent assignment
                            }
                        }
                    }
                    $stmt = $conn->prepare("INSERT INTO users (name, email, occupation, state_code, phone, password, role_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $hashed = password_hash('password', PASSWORD_DEFAULT);
                    $stmt->bind_param('ssssssi', $_POST['name'], $_POST['email'], $_POST['occupation'], $_POST['state_code'], $_POST['phone'], $hashed, $role_id);
                    $stmt->execute();
                    echo '<div class="alert alert-success mb-3">User added (default password: password).</div>';
                }
                if (isset($_POST['update_role_id']) && isset($_POST['role_id'])) {
                    $role_id = intval($_POST['role_id']);
                    $role_name = null;
                    $role_stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
                    $role_stmt->bind_param('i', $role_id);
                    $role_stmt->execute();
                    $role_res = $role_stmt->get_result();
                    $role_row = $role_res->fetch_assoc();
                    $role_name = $role_row ? strtolower($role_row['name']) : null;
                    if ($role_name && in_array($role_name, $unique_roles)) {
                        $check_stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM users WHERE role_id = ? AND id != ?");
                        $check_stmt->bind_param('ii', $role_id, $_POST['update_role_id']);
                        $check_stmt->execute();
                        $check_res = $check_stmt->get_result();
                        $cnt = $check_res->fetch_assoc()['cnt'] ?? 0;
                        if ($cnt > 0) {
                            echo '<div class="alert alert-danger mb-3">This role ('.htmlspecialchars(ucwords(str_replace('-', ' ', $role_name))).') is already assigned to another member.</div>';
                        } else {
                            $stmt = $conn->prepare("UPDATE users SET role_id=? WHERE id=?");
                            $stmt->bind_param('ii', $role_id, $_POST['update_role_id']);
                            $stmt->execute();
                            echo '<div class="alert alert-success mb-3">User role updated.</div>';
                        }
                    } else {
                        $stmt = $conn->prepare("UPDATE users SET role_id=? WHERE id=?");
                        $stmt->bind_param('ii', $role_id, $_POST['update_role_id']);
                        $stmt->execute();
                        echo '<div class="alert alert-success mb-3">User role updated.</div>';
                    }
                }
                // Join users with roles for display
                $result = $conn->query("SELECT u.id, u.name, u.email, u.occupation, u.state_code, u.phone, r.display_name AS role_name, r.id AS role_id FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC LIMIT 30");
                $users = [];
                while ($row = $result->fetch_assoc()) $users[] = $row;
                ?>
                <div class="row mb-4">
                    <div class="col d-flex justify-content-between align-items-center">
                        <h3 class="h5">Manage Users</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-2"></i>Add User
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover user-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Occupation</th>
                                        <th>State Code</th>
                                        <th>Phone</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['name']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><?= htmlspecialchars($user['occupation']) ?></td>
                                        <td><?= htmlspecialchars($user['state_code']) ?></td>
                                        <td><?= htmlspecialchars($user['phone']) ?></td>
                                        <td>
                                            <form method="post" class="d-inline-block">
                                                <input type="hidden" name="update_role_id" value="<?= $user['id'] ?>">
                                                <select name="role_id" class="form-select form-select-sm d-inline-block w-auto">
                                                    <?php foreach ($roles as $role): ?>
                                                        <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['display_name']) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <button type="submit" class="btn btn-success btn-sm action-btn ms-1"><i class="fas fa-save"></i></button>
                                            </form>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this user?')" class="d-inline-block">
                                                <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                                <button class="btn btn-danger btn-sm action-btn"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Add User Modal -->
                <div class="modal fade" id="addUserModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New User</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post">
                                <div class="modal-body">
                                    <input type="hidden" name="add_user" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Name</label>
                                        <input type="text" name="name" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Occupation</label>
                                        <input type="text" name="occupation" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">State Code</label>
                                        <input type="text" name="state_code" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Phone</label>
                                        <input type="text" name="phone" class="form-control">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select name="role_id" class="form-select">
                                            <option value="">Select Role</option>
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['display_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add User</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
            // --- EVENTS TAB ---
            if ($tab === 'events') {
                // Handle CRUD actions
                if (isset($_POST['delete_event_id'])) {
                    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
                    $stmt->bind_param('i', $_POST['delete_event_id']);
                    $stmt->execute();
                    echo '<div class="alert alert-success mb-3">Event deleted.</div>';
                }
                if (isset($_POST['add_event'])) {
                    $img = $_FILES['image']['error'] === UPLOAD_ERR_OK ? '/public/uploads/event_' . time() . '_' . $_FILES['image']['name'] : '';
                    if ($img) move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . basename($img));
                    $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $_POST['title'], $_POST['description'], $_POST['event_date'], $img);
                    $stmt->execute();
                    echo '<div class="alert alert-success mb-3">Event added.</div>';
                }
                $result = $conn->query("SELECT id, title, description, event_date, image FROM events ORDER BY event_date DESC LIMIT 30");
                $events = [];
                while ($row = $result->fetch_assoc()) $events[] = $row;
                ?>
                <div class="row mb-4">
                    <div class="col d-flex justify-content-between align-items-center">
                        <h3 class="h5">Manage Events</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEventModal">
                            <i class="fas fa-plus me-2"></i>Add Event
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Date</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($events as $event): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($event['title']) ?></td>
                                        <td><?= htmlspecialchars($event['event_date']) ?></td>
                                        <td>
                                            <?php if ($event['image']): ?>
                                                <img src="<?= htmlspecialchars($event['image']) ?>" class="rounded" style="width:48px;height:48px;object-fit:cover;" alt="Event Image">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this event?')" class="d-inline-block">
                                                <input type="hidden" name="delete_event_id" value="<?= $event['id'] ?>">
                                                <button class="btn btn-danger btn-sm action-btn"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Add Event Modal -->
                <div class="modal fade" id="addEventModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Event</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="add_event" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Date</label>
                                        <input type="date" name="event_date" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" accept="image/*" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Event</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
            // --- RESOURCES TAB ---
            if ($tab === 'resources') {
                if (isset($_POST['delete_resource_id'])) {
                    $stmt = $conn->prepare("DELETE FROM resources WHERE id=?");
                    $stmt->bind_param('i', $_POST['delete_resource_id']);
                    $stmt->execute();
                    echo '<div class="alert alert-success mb-3">Resource deleted.</div>';
                }
                if (isset($_POST['add_resource'])) {
                    $img = $_FILES['image']['error'] === UPLOAD_ERR_OK ? '/public/uploads/resource_' . time() . '_' . $_FILES['image']['name'] : '';
                    if ($img) move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . basename($img));
                    $stmt = $conn->prepare("INSERT INTO resources (title, description, link, image) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param('ssss', $_POST['title'], $_POST['description'], $_POST['link'], $img);
                    $stmt->execute();
                    echo '<div class="alert alert-success mb-3">Resource added.</div>';
                }
                $result = $conn->query("SELECT id, title, description, link, image FROM resources ORDER BY id DESC LIMIT 30");
                $resources = [];
                while ($row = $result->fetch_assoc()) $resources[] = $row;
                ?>
                <div class="row mb-4">
                    <div class="col d-flex justify-content-between align-items-center">
                        <h3 class="h5">Manage Resources</h3>
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addResourceModal">
                            <i class="fas fa-plus me-2"></i>Add Resource
                        </button>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Title</th>
                                        <th>Link</th>
                                        <th>Image</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($resources as $resource): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($resource['title']) ?></td>
                                        <td><a href="<?= htmlspecialchars($resource['link']) ?>" target="_blank" class="text-primary text-decoration-underline">View</a></td>
                                        <td>
                                            <?php if ($resource['image']): ?>
                                                <img src="<?= htmlspecialchars($resource['image']) ?>" class="rounded" style="width:48px;height:48px;object-fit:cover;" alt="Resource Image">
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="post" onsubmit="return confirm('Delete this resource?')" class="d-inline-block">
                                                <input type="hidden" name="delete_resource_id" value="<?= $resource['id'] ?>">
                                                <button class="btn btn-danger btn-sm action-btn"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <!-- Add Resource Modal -->
                <div class="modal fade" id="addResourceModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Add New Resource</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="post" enctype="multipart/form-data">
                                <div class="modal-body">
                                    <input type="hidden" name="add_resource" value="1">
                                    <div class="mb-3">
                                        <label class="form-label">Title</label>
                                        <input type="text" name="title" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea name="description" class="form-control"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Link</label>
                                        <input type="url" name="link" class="form-control" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Image</label>
                                        <input type="file" name="image" accept="image/*" class="form-control">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary">Add Resource</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php
            }
            // --- SITE SETTINGS TAB ---
            if ($tab === 'settings') {
                // Save settings
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    $settings = [
                        'primary_color' => $_POST['primary_color'] ?? '#008751',
                        'secondary_color' => $_POST['secondary_color'] ?? '#00b16a',
                        'homepage_content' => $_POST['homepage_content'] ?? '',
                    ];
                    // Handle logo upload
                    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === UPLOAD_ERR_OK) {
                        $ext = pathinfo($_FILES['site_logo']['name'], PATHINFO_EXTENSION);
                        $logoName = 'site_logo_' . time() . '.' . $ext;
                        $target = '/public/uploads/' . $logoName;
                        $abs_target = __DIR__ . '/../public/uploads/' . $logoName;
                        if (move_uploaded_file($_FILES['site_logo']['tmp_name'], $abs_target)) {
                            $settings['site_logo'] = $target;
                        }
                    } else if (file_exists(__DIR__ . '/../public/uploads/site_settings.json')) {
                        $old = json_decode(file_get_contents(__DIR__ . '/../public/uploads/site_settings.json'), true);
                        if (isset($old['site_logo'])) $settings['site_logo'] = $old['site_logo'];
                    }
                    file_put_contents(__DIR__ . '/../public/uploads/site_settings.json', json_encode($settings));
                    echo '<div class="alert alert-success mb-3">Settings saved.</div>';
                }
                // Load settings
                $settings = [];
                if (file_exists(__DIR__ . '/../public/uploads/site_settings.json')) {
                    $settings = json_decode(file_get_contents(__DIR__ . '/../public/uploads/site_settings.json'), true);
                }
                ?>
                <div class="row mb-4">
                    <div class="col">
                        <h3 class="h5">Site Settings (CMS)</h3>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data" class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Site Logo</label>
                                <input type="file" name="site_logo" class="form-control">
                                <?php if (!empty($settings['site_logo'])): ?>
                                    <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Current Logo" class="h-100 mt-2" style="max-height:64px;">
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Primary Color</label>
                                <input type="color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color'] ?? '#008751') ?>" class="form-control form-control-color" style="width: 3rem; height: 3rem;">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Secondary Color</label>
                                <input type="color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color'] ?? '#00b16a') ?>" class="form-control form-control-color" style="width: 3rem; height: 3rem;">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Homepage Content</label>
                                <textarea name="homepage_content" rows="5" class="form-control"><?= htmlspecialchars($settings['homepage_content'] ?? '') ?></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Save Settings</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script>
        // Sidebar Toggle Functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('sidebar-active');
        });
        // Responsive Sidebar
        function handleResize() {
            if (window.innerWidth > 992) {
                document.getElementById('sidebar').classList.remove('active');
                document.getElementById('main-content').classList.remove('sidebar-active');
            }
        }
        window.addEventListener('resize', handleResize);
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>
<?php
// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
