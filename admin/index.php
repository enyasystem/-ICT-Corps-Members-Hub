<?php
require_once __DIR__ . '/../src/config/database.php';
require_once __DIR__ . '/../src/models/User.php';
$userModel = new User($conn);
$users = $userModel->getAll();
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

        body {
            background-color: #f8f9fa;
        }

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

        .sidebar-link:hover {
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

        .modal-backdrop {
            z-index: 1040;
        }

        .modal {
            z-index: 1050;
        }

        @media (max-width: 992px) {
            #sidebar {
                transform: translateX(-100%);
            }

            #sidebar.active {
                transform: translateX(0);
            }

            #main-content {
                margin-left: 0;
            }

            #main-content.sidebar-active {
                margin-left: var(--sidebar-width);
            }
        }

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .user-table th {
            white-space: nowrap;
        }

        .navbar-toggler {
            display: none;
        }

        @media (max-width: 992px) {
            .navbar-toggler {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <nav id="sidebar" class="py-3">
        <div class="px-3 mb-4 border-bottom">
            <h4 class="text-primary">Admin Panel</h4>
        </div>
        <div class="d-flex flex-column">
            <a href="#" class="sidebar-link">
                <i class="fas fa-users"></i>
                Users
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-calendar"></i>
                Events
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-file-alt"></i>
                Resources
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-cog"></i>
                Site Settings
            </a>
            <a href="#" class="sidebar-link">
                <i class="fas fa-sign-out-alt"></i>
                Logout
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

            <div class="row mb-4">
                <div class="col d-flex justify-content-between align-items-center">
                    <h3 class="h5">Manage Users</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-2"></i>Add User
                    </button>
                </div>
            </div>

            <!-- User Table -->
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
                                <!-- Users will be dynamically loaded here -->
                            </tbody>
                        </table>
                    </div>
                </div>
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
                <div class="modal-body">
                    <form id="addUserForm">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State Code</label>
                            <input type="text" class="form-control" name="state_code" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role">
                                <option value="Corps Member">Corps Member</option>
                                <option value="Admin">Admin</option>
                                <option value="Supervisor">Supervisor</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="addUserForm" class="btn btn-primary">Add User</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="editUserForm">
                        <input type="hidden" name="id" id="editUserId">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" class="form-control" name="name" id="editUserName" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" id="editUserEmail" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" name="occupation" id="editUserOccupation" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State Code</label>
                            <input type="text" class="form-control" name="state_code" id="editUserStateCode" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" name="phone" id="editUserPhone" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role_id" id="editUserRole">
                                <option value="1">Corps Member</option>
                                <option value="2">Admin</option>
                                <option value="3">Supervisor</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password (leave blank to keep unchanged)</label>
                            <input type="password" class="form-control" name="password" id="editUserPassword">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" form="editUserForm" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
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

        // Helper: Fetch and render users
        function loadUsers() {
            fetch('user_actions.php?action=list')
                .then(res => res.json())
                .then(data => {
                    const tbody = document.querySelector('.user-table tbody');
                    tbody.innerHTML = '';
                    data.users.forEach(user => {
                        tbody.innerHTML += `
                        <tr>
                            <td>${user.name}</td>
                            <td>${user.email}</td>
                            <td>${user.occupation}</td>
                            <td>${user.state_code}</td>
                            <td>${user.phone}</td>
                            <td>${user.role_name || 'Member'}</td>
                            <td>
                                <button class="btn btn-success btn-sm action-btn view-btn" data-id="${user.id}"><i class="fas fa-eye"></i></button>
                                <button class="btn btn-primary btn-sm action-btn edit-btn" data-id="${user.id}"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-danger btn-sm action-btn delete-btn" data-id="${user.id}"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>`;
                    });
                });
        }

        // Add User
        const addUserForm = document.getElementById('addUserForm');
        addUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(addUserForm);
            formData.append('action', 'add');
            fetch('user_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadUsers();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
                    modal.hide();
                    addUserForm.reset();
                } else {
                    alert('Failed to add user');
                }
            });
        });

        // Delete User
        // Delegate event
        document.querySelector('.user-table tbody').addEventListener('click', function(e) {
            if (e.target.closest('.delete-btn')) {
                const id = e.target.closest('.delete-btn').getAttribute('data-id');
                if (confirm('Are you sure you want to delete this user?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    fetch('user_actions.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) loadUsers();
                        else alert('Failed to delete user');
                    });
                }
            }
        });

        // Edit User: open modal and populate fields
        document.querySelector('.user-table tbody').addEventListener('click', function(e) {
            if (e.target.closest('.edit-btn')) {
                const id = e.target.closest('.edit-btn').getAttribute('data-id');
                fetch('user_actions.php?action=list')
                    .then(res => res.json())
                    .then(data => {
                        const user = data.users.find(u => u.id == id);
                        if (user) {
                            document.getElementById('editUserId').value = user.id;
                            document.getElementById('editUserName').value = user.name;
                            document.getElementById('editUserEmail').value = user.email;
                            document.getElementById('editUserOccupation').value = user.occupation;
                            document.getElementById('editUserStateCode').value = user.state_code;
                            document.getElementById('editUserPhone').value = user.phone;
                            document.getElementById('editUserRole').value = user.role_id;
                            document.getElementById('editUserPassword').value = '';
                            const modal = new bootstrap.Modal(document.getElementById('editUserModal'));
                            modal.show();
                        }
                    });
            }
        });

        // Handle Edit User form submit
        const editUserForm = document.getElementById('editUserForm');
        editUserForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(editUserForm);
            formData.append('action', 'edit');
            fetch('user_actions.php', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadUsers();
                    const modal = bootstrap.Modal.getInstance(document.getElementById('editUserModal'));
                    modal.hide();
                } else {
                    alert('Failed to update user');
                }
            });
        });

        // Initial load
        loadUsers();

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
