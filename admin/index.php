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
   
    //CSS
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
                                <tr>
                                    <td>John Doe</td>
                                    <td>john@example.com</td>
                                    <td>Developer</td>
                                    <td>LA/21A/1234</td>
                                    <td>+234 801 234 5678</td>
                                    <td>Corps Member</td>
                                    <td>
                                        <button class="btn btn-success btn-sm action-btn">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-primary btn-sm action-btn">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-danger btn-sm action-btn">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
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
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Occupation</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">State Code</label>
                            <input type="text" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Phone</label>
                            <input type="tel" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select">
                                <option>Corps Member</option>
                                <option>Admin</option>
                                <option>Supervisor</option>
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

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        // Sidebar Toggle Functionality
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('main-content').classList.toggle('sidebar-active');
        });

        // Form Submission Handler
        document.getElementById('addUserForm').addEventListener('submit', function(e) {
            e.preventDefault();
            // Add your form submission logic here
            
            // Close modal after submission
            const modal = bootstrap.Modal.getInstance(document.getElementById('addUserModal'));
            modal.hide();
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
