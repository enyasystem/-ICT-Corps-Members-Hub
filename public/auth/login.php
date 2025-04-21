<?php
require_once '../../src/config/database.php';
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

$error = '';
$activeTab = isset($_POST['login_type']) && $_POST['login_type'] === 'admin' ? 'admin' : 'member';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $loginType = $_POST['login_type'] ?? 'member';

        if ($loginType === 'admin') {
            // Hardcoded admin login
            if ($email === 'admin' && $password === 'admin123') {
                $_SESSION['admin_id'] = 1;
                $_SESSION['admin_name'] = 'Admin';
                header("Location: /ICT-Corps-Members-Hub/admin/index.php");
                exit;
            }
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ? AND is_admin = 1");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                header("Location: /ICT-Corps-Members-Hub/admin/index.php");
                exit;
            } else {
                $error = "Invalid admin credentials.";
            }
        } else {
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                header("Location: /ICT-Corps-Members-Hub/src/views/dashboard.php");
                exit;
            } else {
                $error = "Invalid email or password.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ICT Corps Members Hub</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body class="bg-green-50 min-h-screen flex flex-col justify-center items-center p-4">
    <div class="w-full max-w-md mx-auto">
        <!-- Logo -->
        <div class="flex justify-center mb-6">
            <a href="/ICT-Corps-Members-Hub/public" class="block">
                <div class="bg-green-700 rounded-full w-16 h-16 flex items-center justify-center shadow-lg mx-auto">
                    <span class="text-white text-3xl font-extrabold font-sans tracking-wide">ICT</span>
                </div>
            </a>
        </div>
        <!-- Main Title -->
        <h1 class="text-2xl md:text-3xl font-extrabold text-center text-green-800 mb-2">ICT Corps Members Hub</h1>
        <!-- Card Container -->
        <div class="bg-white rounded-2xl shadow-md p-6 md:p-8 mt-4 space-y-6">
            <!-- Heading -->
            <div class="text-center">
                <h2 class="text-xl md:text-2xl font-bold text-green-800">Sign In</h2>
                <p class="text-gray-500 text-sm mt-1">Enter your credentials to access your account</p>
            </div>
            <!-- Tabs -->
            <div class="flex justify-center gap-2 mb-2">
                <button type="button" class="px-6 py-2 rounded-t-lg border-b-4 <?= $activeTab === 'member' ? 'border-green-700 bg-green-50 text-green-800' : 'border-transparent bg-gray-50 text-gray-500' ?> font-bold focus:outline-none" onclick="setActiveTab('member')">Member</button>
                <button type="button" class="px-6 py-2 rounded-t-lg border-b-4 <?= $activeTab === 'admin' ? 'border-green-700 bg-green-50 text-green-800' : 'border-transparent bg-gray-50 text-gray-500' ?> font-bold focus:outline-none" onclick="setActiveTab('admin')">Admin</button>
            </div>
            <!-- Form -->
            <form id="login-form" action="login.php" method="POST" class="space-y-4">
                <input type="hidden" name="login_type" id="login-type-input" value="<?= $activeTab ?>" />
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" />
                <!-- Email/Username -->
                <div class="relative" id="email-group">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-green-700"><i class="fa-solid fa-envelope"></i></span>
                    <input type="email" name="email" id="email-input" placeholder="Enter email" required class="w-full pl-10 pr-3 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-green-200 focus:border-green-700 outline-none transition text-gray-800" />
                </div>
                <!-- Password -->
                <div class="relative" id="password-group">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-green-700"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" name="password" id="password-input" placeholder="Password" required class="w-full pl-10 pr-10 py-3 border border-gray-300 rounded-full focus:ring-2 focus:ring-green-200 focus:border-green-700 outline-none transition text-gray-800" />
                    <button type="button" tabindex="-1" class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-green-700 focus:outline-none" onclick="togglePassword()"><i class="fa-regular fa-eye" id="eye-icon"></i></button>
                </div>
                <!-- Options -->
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" class="accent-green-700 rounded" name="remember" />
                        <span class="text-gray-700">Remember me</span>
                    </label>
                    <a href="#" class="text-green-700 hover:underline">Forgot password?</a>
                </div>
                <!-- Error Message -->
                <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-2 mb-2 rounded text-center text-sm">
                    <?= $error ?>
                </div>
                <?php endif; ?>
                <!-- Primary Button -->
                <button id="login-btn" type="submit" class="w-full bg-green-700 text-white py-3 rounded-full font-bold hover:bg-green-800 transition">Sign In as <?= $activeTab === 'admin' ? 'Admin' : 'Member' ?></button>
            </form>
            <!-- Account Prompt -->
            <div class="text-center text-sm mt-2">
                Don't have an account? <a href="register.php" class="text-green-700 font-semibold hover:underline">Create an account</a>
            </div>
            <!-- Divider -->
            <div class="flex items-center gap-2 my-2">
                <hr class="flex-grow border-gray-200">
                <span class="text-xs text-gray-400 font-bold uppercase">or continue with</span>
                <hr class="flex-grow border-gray-200">
            </div>
            <!-- Social Login Buttons -->
            <div class="flex flex-col gap-3">
                <button type="button" class="w-full flex items-center justify-center gap-3 py-2 rounded-full border border-gray-200 bg-white hover:bg-gray-50 text-gray-700 font-semibold shadow-sm">
                    <span class="text-lg"><i class="fa-brands fa-google text-red-500"></i></span>
                    Continue with Google
                </button>
                <button type="button" class="w-full flex items-center justify-center gap-3 py-2 rounded-full border border-blue-100 bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold shadow-sm">
                    <span class="text-lg"><i class="fa-brands fa-facebook-f text-blue-600"></i></span>
                    Continue with Facebook
                </button>
            </div>
        </div>
    </div>
    <script>
    function togglePassword() {
        const input = document.getElementById('password-input');
        const icon = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }

    function setActiveTab(tab) {
        document.getElementById('login-type-input').value = tab;
        const memberButton = document.querySelector('button[onclick="setActiveTab(\'member\')"]');
        const adminButton = document.querySelector('button[onclick="setActiveTab(\'admin\')"]');
        const loginBtn = document.getElementById('login-btn');
        const loginForm = document.getElementById('login-form');
        const emailInput = document.getElementById('email-input');
        const passwordInput = document.getElementById('password-input');
        if (tab === 'member') {
            memberButton.classList.add('border-green-700', 'bg-green-50', 'text-green-800');
            memberButton.classList.remove('border-transparent', 'bg-gray-50', 'text-gray-500');
            adminButton.classList.add('border-transparent', 'bg-gray-50', 'text-gray-500');
            adminButton.classList.remove('border-green-700', 'bg-green-50', 'text-green-800');
            loginBtn.textContent = 'Sign In as Member';
            loginForm.action = 'login.php';
            emailInput.type = 'email';
            emailInput.name = 'email';
            emailInput.placeholder = 'Enter email';
            passwordInput.name = 'password';
        } else {
            adminButton.classList.add('border-green-700', 'bg-green-50', 'text-green-800');
            adminButton.classList.remove('border-transparent', 'bg-gray-50', 'text-gray-500');
            memberButton.classList.add('border-transparent', 'bg-gray-50', 'text-gray-500');
            memberButton.classList.remove('border-green-700', 'bg-green-50', 'text-green-800');
            loginBtn.textContent = 'Sign In as Admin';
            loginForm.action = '/ICT-Corps-Members-Hub/admin/index.php';
            emailInput.type = 'text';
            emailInput.name = 'admin_user';
            emailInput.placeholder = 'Enter admin username';
            passwordInput.name = 'admin_pass';
        }
    }

    // Client-side login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            let errors = [];
            const emailInput = document.getElementById('email-input');
            const passwordInput = document.getElementById('password-input');
            const email = emailInput.value.trim();
            const password = passwordInput.value;
            if (!email || !password) {
                errors.push('Email and password are required.');
            }
            // Only check email format if input type is email
            if (emailInput.type === 'email' && email && !/^\S+@\S+\.\S+$/.test(email)) {
                errors.push('Enter a valid email address.');
            }
            let errorDiv = document.getElementById('client-login-errors');
            if (!errorDiv) {
                errorDiv = document.createElement('div');
                errorDiv.id = 'client-login-errors';
                errorDiv.className = 'bg-red-100 text-red-700 p-2 mb-2 rounded text-center text-sm';
                loginForm.parentNode.insertBefore(errorDiv, loginForm);
            }
            errorDiv.innerHTML = '';
            if (errors.length > 0) {
                e.preventDefault();
                errors.forEach(err => {
                    const d = document.createElement('div');
                    d.textContent = err;
                    errorDiv.appendChild(d);
                });
                window.scrollTo({ top: 0, behavior: 'smooth' });
            } else {
                errorDiv.innerHTML = '';
            }
        });
    }
    </script>
</body>
</html>
