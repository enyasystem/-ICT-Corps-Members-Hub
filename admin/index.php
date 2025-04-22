<?php
session_start();
require_once __DIR__ . '/../src/config/database.php';

// Toasts array for notifications
$toasts = [];

// --- Simple Admin Auth (MVP) ---
if (!isset($_SESSION['admin_logged_in'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['admin_user'], $_POST['admin_pass'])) {
        // Hardcoded admin credentials for MVP
        if ($_POST['admin_user'] === 'admin' && $_POST['admin_pass'] === 'admin123') {
            $_SESSION['admin_logged_in'] = true;
            header('Location: index.php');
            exit;
        } else {
            $toasts[] = ['type' => 'error', 'msg' => 'Invalid admin credentials.'];
        }
    }
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Login - ICT Corps Members Hub</title>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    </head>
    <body class="bg-gradient-to-br from-green-50 to-green-200 flex items-center justify-center min-h-screen">
        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2"></div>
        <form method="POST" class="bg-white/80 backdrop-blur-lg p-8 rounded-2xl shadow-xl w-full max-w-sm border border-green-100">
            <div class="flex justify-center mb-6">
                <span class="bg-green-700 rounded-full w-14 h-14 flex items-center justify-center shadow-lg"><i class="fa fa-user-shield text-white text-2xl"></i></span>
            </div>
            <h2 class="text-2xl font-bold mb-6 text-green-800 text-center">Admin Login</h2>
            <div class="mb-4">
                <label class="block mb-1">Username</label>
                <input type="text" name="admin_user" required class="w-full border rounded px-3 py-2">
            </div>
            <div class="mb-6">
                <label class="block mb-1">Password</label>
                <input type="password" name="admin_pass" required class="w-full border rounded px-3 py-2">
            </div>
            <button type="submit" class="w-full bg-green-700 text-white py-3 rounded font-semibold hover:bg-green-800 transition">Login</button>
        </form>
        <?php if (!empty($toasts)): ?>
        <script>
        window.addEventListener('DOMContentLoaded',function(){
            const toasts = <?php echo json_encode($toasts); ?>;
            const c = document.getElementById('toast-container');
            toasts.forEach(t=>{
                const d=document.createElement('div');
                d.className=`px-5 py-3 rounded shadow-lg flex items-center gap-3 mb-2 ${t.type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`;
                d.innerHTML=`<i class='fa ${t.type==='success'?'fa-check-circle':'fa-times-circle'} text-xl'></i><span>${t.msg}</span>`;
                c.appendChild(d);
                setTimeout(()=>{d.style.opacity='0';setTimeout(()=>d.remove(),400)},3500);
            });
        });
        </script>
        <?php endif; ?>
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
    <title>Admin Dashboard - ICT Corps Members Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
      .glass { background: rgba(255,255,255,0.85); backdrop-filter: blur(12px); border-radius: 1.5rem; box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.10); border: 1px solid rgba(0,135,81,0.08); }
      .sidebar-link.active { background: linear-gradient(90deg,#008751 0%,#00b16a 100%); color: #fff !important; }
      .sidebar-link { transition: background 0.2s, color 0.2s; }
      .sidebar-link:hover { background: #e6f9f0; color: #008751 !important; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-green-200 min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 min-h-screen bg-white/80 border-r border-green-100 flex flex-col py-8 px-4 glass">
        <div class="flex items-center gap-3 mb-10">
            <span class="bg-green-700 rounded-full w-10 h-10 flex items-center justify-center"><i class="fa fa-user-shield text-white text-xl"></i></span>
            <span class="font-extrabold text-green-900 text-lg tracking-tight">Admin Panel</span>
        </div>
        <nav class="flex flex-col gap-2">
            <a href="?tab=users" class="sidebar-link px-4 py-3 rounded-lg font-semibold flex items-center gap-2 <?= $tab==='users' ? 'active' : 'text-green-900' ?>"><i class="fa fa-users"></i> Users</a>
            <a href="?tab=events" class="sidebar-link px-4 py-3 rounded-lg font-semibold flex items-center gap-2 <?= $tab==='events' ? 'active' : 'text-green-900' ?>"><i class="fa fa-calendar"></i> Events</a>
            <a href="?tab=resources" class="sidebar-link px-4 py-3 rounded-lg font-semibold flex items-center gap-2 <?= $tab==='resources' ? 'active' : 'text-green-900' ?>"><i class="fa fa-book"></i> Resources</a>
            <a href="?tab=settings" class="sidebar-link px-4 py-3 rounded-lg font-semibold flex items-center gap-2 <?= $tab==='settings' ? 'active' : 'text-green-900' ?>"><i class="fa fa-cog"></i> Site Settings</a>
        </nav>
        <form method="post" action="?logout=1" class="mt-auto pt-10">
            <button class="w-full bg-red-600 px-4 py-2 rounded-lg text-white font-bold hover:bg-red-700 flex items-center gap-2 justify-center"><i class="fa fa-sign-out-alt"></i> Logout</button>
        </form>
    </aside>
    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2"></div>
        <div class="max-w-6xl mx-auto">
            <div class="mb-8 flex items-center justify-between">
                <h1 class="text-3xl font-extrabold text-green-900 tracking-tight">Admin Dashboard</h1>
                <span class="text-green-700 font-semibold text-lg">ICT Corps Members Hub</span>
            </div>
            <div class="glass p-8">
                <?php
                // --- USERS TAB ---
                if ($tab === 'users') {
                    $roles = [];
                    $roles_result = $conn->query("SELECT id, display_name FROM roles ORDER BY id");
                    while ($row = $roles_result->fetch_assoc()) $roles[] = $row;
                    $unique_roles = ['president', 'vice-president', 'treasurer'];
                    try {
                        if (isset($_POST['delete_user_id'])) {
                            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
                            $stmt->bind_param('i', $_POST['delete_user_id']);
                            $stmt->execute();
                            $toasts[] = ['type' => 'success', 'msg' => 'User deleted.'];
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
                                        $toasts[] = ['type' => 'error', 'msg' => 'This role (' . htmlspecialchars(ucwords(str_replace('-', ' ', $role_name))) . ') is already assigned to another member.'];
                                        $role_id = null;
                                    }
                                }
                            }
                            $stmt = $conn->prepare("INSERT INTO users (name, email, occupation, state_code, phone, password, role_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
                            $hashed = password_hash('password', PASSWORD_DEFAULT);
                            $stmt->bind_param('ssssssi', $_POST['name'], $_POST['email'], $_POST['occupation'], $_POST['state_code'], $_POST['phone'], $hashed, $role_id);
                            $stmt->execute();
                            $toasts[] = ['type' => 'success', 'msg' => 'User added (default password: password).'];
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
                                    $toasts[] = ['type' => 'error', 'msg' => 'This role (' . htmlspecialchars(ucwords(str_replace('-', ' ', $role_name))) . ') is already assigned to another member.'];
                                } else {
                                    $stmt = $conn->prepare("UPDATE users SET role_id=? WHERE id=?");
                                    $stmt->bind_param('ii', $role_id, $_POST['update_role_id']);
                                    $stmt->execute();
                                    $toasts[] = ['type' => 'success', 'msg' => 'User role updated.'];
                                }
                            } else {
                                $stmt = $conn->prepare("UPDATE users SET role_id=? WHERE id=?");
                                $stmt->bind_param('ii', $role_id, $_POST['update_role_id']);
                                $stmt->execute();
                                $toasts[] = ['type' => 'success', 'msg' => 'User role updated.'];
                            }
                        }
                    } catch (mysqli_sql_exception $e) {
                        $msg = $e->getMessage();
                        if (strpos($msg, 'Duplicate entry') !== false && strpos($msg, "for key 'email'") !== false) {
                            $toasts[] = ['type' => 'error', 'msg' => 'A user with this email already exists.'];
                        } else {
                            $toasts[] = ['type' => 'error', 'msg' => 'Database error: ' . htmlspecialchars($msg)];
                        }
                    }
                    $result = $conn->query("SELECT u.id, u.name, u.email, u.occupation, u.state_code, u.phone, r.display_name AS role_name, r.id AS role_id FROM users u LEFT JOIN roles r ON u.role_id = r.id ORDER BY u.id DESC LIMIT 30");
                    $users = [];
                    while ($row = $result->fetch_assoc()) $users[] = $row;
                    ?>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Manage Users</h2>
                        <button onclick="document.getElementById('addUserModal').classList.remove('hidden')" class="bg-green-700 text-white px-4 py-2 rounded">Add User</button>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full border rounded">
                        <thead class="bg-green-100">
                            <tr>
                                <th class="px-4 py-2">Name</th>
                                <th class="px-4 py-2">Email</th>
                                <th class="px-4 py-2">Occupation</th>
                                <th class="px-4 py-2">State Code</th>
                                <th class="px-4 py-2">Phone</th>
                                <th class="px-4 py-2">Role</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"> <?= htmlspecialchars($user['name']) ?> </td>
                                <td class="px-4 py-2"> <?= htmlspecialchars($user['email']) ?> </td>
                                <td class="px-4 py-2"> <?= htmlspecialchars($user['occupation']) ?> </td>
                                <td class="px-4 py-2"> <?= htmlspecialchars($user['state_code']) ?> </td>
                                <td class="px-4 py-2"> <?= htmlspecialchars($user['phone']) ?> </td>
                                <td class="px-4 py-2">
                                    <form method="post" style="display:inline-block;">
                                        <input type="hidden" name="update_role_id" value="<?= $user['id'] ?>">
                                        <select name="role_id" class="border rounded px-2 py-1 text-xs">
                                            <?php foreach ($roles as $role): ?>
                                                <option value="<?= $role['id'] ?>" <?= $user['role_id'] == $role['id'] ? 'selected' : '' ?>><?= htmlspecialchars($role['display_name']) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="ml-1 px-2 py-1 bg-green-600 text-white rounded text-xs">Save</button>
                                    </form>
                                </td>
                                <td class="px-4 py-2 flex gap-2">
                                    <form method="post" onsubmit="return confirm('Delete this user?')">
                                        <input type="hidden" name="delete_user_id" value="<?= $user['id'] ?>">
                                        <button class="bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <!-- Add User Modal -->
                    <div id="addUserModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
                        <div class="bg-white p-6 rounded shadow w-full max-w-md relative">
                            <button onclick="document.getElementById('addUserModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500">&times;</button>
                            <h3 class="text-lg font-bold mb-4">Add User</h3>
                            <form method="post">
                                <input type="hidden" name="add_user" value="1">
                                <div class="mb-2">
                                    <label>Name</label>
                                    <input type="text" name="name" required class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Email</label>
                                    <input type="email" name="email" required class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Occupation</label>
                                    <input type="text" name="occupation" class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>State Code</label>
                                    <input type="text" name="state_code" class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Phone</label>
                                    <input type="text" name="phone" class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Role</label>
                                    <select name="role_id" class="w-full border rounded px-2 py-1">
                                        <option value="">Select Role</option>
                                        <?php foreach ($roles as $role): ?>
                                            <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['display_name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded mt-2">Add User</button>
                            </form>
                        </div>
                    </div>
                    <?php
                }
                // --- EVENTS TAB ---
                if ($tab === 'events') {
                    if (isset($_POST['delete_event_id'])) {
                        $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
                        $stmt->bind_param('i', $_POST['delete_event_id']);
                        $stmt->execute();
                        $toasts[] = ['type' => 'success', 'msg' => 'Event deleted.'];
                    }
                    if (isset($_POST['add_event'])) {
                        $img = $_FILES['image']['error'] === UPLOAD_ERR_OK ? '/public/uploads/event_' . time() . '_' . $_FILES['image']['name'] : '';
                        if ($img) move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . basename($img));
                        $stmt = $conn->prepare("INSERT INTO events (title, description, event_date, image) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param('ssss', $_POST['title'], $_POST['description'], $_POST['event_date'], $img);
                        $stmt->execute();
                        $toasts[] = ['type' => 'success', 'msg' => 'Event added.'];
                    }
                    $result = $conn->query("SELECT id, title, description, event_date, image FROM events ORDER BY event_date DESC LIMIT 30");
                    $events = [];
                    while ($row = $result->fetch_assoc()) $events[] = $row;
                    ?>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Manage Events</h2>
                        <button onclick="document.getElementById('addEventModal').classList.remove('hidden')" class="bg-green-700 text-white px-4 py-2 rounded">Add Event</button>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full border rounded">
                        <thead class="bg-green-100">
                            <tr>
                                <th class="px-4 py-2">Title</th>
                                <th class="px-4 py-2">Date</th>
                                <th class="px-4 py-2">Image</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($events as $event): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"> <?= htmlspecialchars($event['title']) ?> </td>
                                <td class="px-4 py-2"> <?= htmlspecialchars($event['event_date']) ?> </td>
                                <td class="px-4 py-2">
                                    <?php if ($event['image']): ?>
                                        <img src="<?= htmlspecialchars($event['image']) ?>" class="w-12 h-12 object-cover rounded" alt="Event Image">
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 flex gap-2">
                                    <form method="post" onsubmit="return confirm('Delete this event?')">
                                        <input type="hidden" name="delete_event_id" value="<?= $event['id'] ?>">
                                        <button class="bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <!-- Add Event Modal -->
                    <div id="addEventModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
                        <div class="bg-white p-6 rounded shadow w-full max-w-md relative">
                            <button onclick="document.getElementById('addEventModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500">&times;</button>
                            <h3 class="text-lg font-bold mb-4">Add Event</h3>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="add_event" value="1">
                                <div class="mb-2">
                                    <label>Title</label>
                                    <input type="text" name="title" required class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Description</label>
                                    <textarea name="description" class="w-full border rounded px-2 py-1"></textarea>
                                </div>
                                <div class="mb-2">
                                    <label>Date</label>
                                    <input type="date" name="event_date" required class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Image</label>
                                    <input type="file" name="image" accept="image/*" class="w-full">
                                </div>
                                <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded mt-2">Add Event</button>
                            </form>
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
                        $toasts[] = ['type' => 'success', 'msg' => 'Resource deleted.'];
                    }
                    if (isset($_POST['add_resource'])) {
                        $img = $_FILES['image']['error'] === UPLOAD_ERR_OK ? '/public/uploads/resource_' . time() . '_' . $_FILES['image']['name'] : '';
                        if ($img) move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../public/uploads/' . basename($img));
                        $stmt = $conn->prepare("INSERT INTO resources (title, description, link, image) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param('ssss', $_POST['title'], $_POST['description'], $_POST['link'], $img);
                        $stmt->execute();
                        $toasts[] = ['type' => 'success', 'msg' => 'Resource added.'];
                    }
                    $result = $conn->query("SELECT id, title, description, link, image FROM resources ORDER BY id DESC LIMIT 30");
                    $resources = [];
                    while ($row = $result->fetch_assoc()) $resources[] = $row;
                    ?>
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Manage Resources</h2>
                        <button onclick="document.getElementById('addResourceModal').classList.remove('hidden')" class="bg-green-700 text-white px-4 py-2 rounded">Add Resource</button>
                    </div>
                    <div class="overflow-x-auto">
                    <table class="min-w-full border rounded">
                        <thead class="bg-green-100">
                            <tr>
                                <th class="px-4 py-2">Title</th>
                                <th class="px-4 py-2">Link</th>
                                <th class="px-4 py-2">Image</th>
                                <th class="px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                            <tr class="border-b">
                                <td class="px-4 py-2"> <?= htmlspecialchars($resource['title']) ?> </td>
                                <td class="px-4 py-2"><a href="<?= htmlspecialchars($resource['link']) ?>" target="_blank" class="text-green-700 underline">View</a></td>
                                <td class="px-4 py-2">
                                    <?php if ($resource['image']): ?>
                                        <img src="<?= htmlspecialchars($resource['image']) ?>" class="w-12 h-12 object-cover rounded" alt="Resource Image">
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-2 flex gap-2">
                                    <form method="post" onsubmit="return confirm('Delete this resource?')">
                                        <input type="hidden" name="delete_resource_id" value="<?= $resource['id'] ?>">
                                        <button class="bg-red-600 text-white px-2 py-1 rounded">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    </div>
                    <!-- Add Resource Modal -->
                    <div id="addResourceModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center hidden z-50">
                        <div class="bg-white p-6 rounded shadow w-full max-w-md relative">
                            <button onclick="document.getElementById('addResourceModal').classList.add('hidden')" class="absolute top-2 right-2 text-gray-500">&times;</button>
                            <h3 class="text-lg font-bold mb-4">Add Resource</h3>
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="add_resource" value="1">
                                <div class="mb-2">
                                    <label>Title</label>
                                    <input type="text" name="title" required class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Description</label>
                                    <textarea name="description" class="w-full border rounded px-2 py-1"></textarea>
                                </div>
                                <div class="mb-2">
                                    <label>Link</label>
                                    <input type="url" name="link" required class="w-full border rounded px-2 py-1">
                                </div>
                                <div class="mb-2">
                                    <label>Image</label>
                                    <input type="file" name="image" accept="image/*" class="w-full">
                                </div>
                                <button type="submit" class="bg-green-700 text-white px-4 py-2 rounded mt-2">Add Resource</button>
                            </form>
                        </div>
                    </div>
                    <?php
                }
                // --- SITE SETTINGS TAB ---
                if ($tab === 'settings') {
                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $settings = [
                            'primary_color' => $_POST['primary_color'] ?? '#008751',
                            'secondary_color' => $_POST['secondary_color'] ?? '#00b16a',
                            'homepage_content' => $_POST['homepage_content'] ?? '',
                        ];
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
                        $toasts[] = ['type' => 'success', 'msg' => 'Settings saved.'];
                    }
                    $settings = [];
                    if (file_exists(__DIR__ . '/../public/uploads/site_settings.json')) {
                        $settings = json_decode(file_get_contents(__DIR__ . '/../public/uploads/site_settings.json'), true);
                    }
                    ?>
                    <h2 class="text-xl font-bold mb-4">Site Settings (CMS)</h2>
                    <form method="post" enctype="multipart/form-data" class="space-y-6 max-w-xl">
                        <div>
                            <label class="block font-semibold mb-1">Site Logo</label>
                            <input type="file" name="site_logo" class="border rounded px-2 py-1 w-full">
                            <?php if (!empty($settings['site_logo'])): ?>
                                <img src="<?= htmlspecialchars($settings['site_logo']) ?>" alt="Current Logo" class="h-16 mt-2">
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Primary Color</label>
                            <input type="color" name="primary_color" value="<?= htmlspecialchars($settings['primary_color'] ?? '#008751') ?>" class="w-16 h-10 p-0 border-none">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Secondary Color</label>
                            <input type="color" name="secondary_color" value="<?= htmlspecialchars($settings['secondary_color'] ?? '#00b16a') ?>" class="w-16 h-10 p-0 border-none">
                        </div>
                        <div>
                            <label class="block font-semibold mb-1">Homepage Content</label>
                            <textarea name="homepage_content" rows="5" class="w-full border rounded px-2 py-1"><?= htmlspecialchars($settings['homepage_content'] ?? '') ?></textarea>
                        </div>
                        <button type="submit" class="bg-green-700 text-white px-6 py-2 rounded">Save Settings</button>
                    </form>
                    <?php
                }
                ?>
            </div>
        </div>
    </main>
    <?php if (!empty($toasts)): ?>
    <script>
    window.addEventListener('DOMContentLoaded',function(){
        const toasts = <?php echo json_encode($toasts); ?>;
        const c = document.getElementById('toast-container');
        toasts.forEach(t=>{
            const d=document.createElement('div');
            d.className=`px-5 py-3 rounded shadow-lg flex items-center gap-3 mb-2 ${t.type==='success'?'bg-green-600 text-white':'bg-red-600 text-white'}`;
            d.innerHTML=`<i class='fa ${t.type==='success'?'fa-check-circle':'fa-times-circle'} text-xl'></i><span>${t.msg}</span>`;
            c.appendChild(d);
            setTimeout(()=>{d.style.opacity='0';setTimeout(()=>d.remove(),400)},3500);
        });
    });
    </script>
    <?php endif; ?>
    <script>
        document.addEventListener('click', function(e) {
            if (e.target.id === 'addUserModal') {
                document.getElementById('addUserModal').classList.add('hidden');
            }
            if (e.target.id === 'addEventModal') {
                document.getElementById('addEventModal').classList.add('hidden');
            }
            if (e.target.id === 'addResourceModal') {
                document.getElementById('addResourceModal').classList.add('hidden');
            }
        });
    </script>
</body>
</html>
<?php
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
