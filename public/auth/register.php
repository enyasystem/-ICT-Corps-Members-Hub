<?php
require_once '../../src/config/database.php';

$errors = [];
session_start();

// CSRF token generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF token validation
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = 'Invalid CSRF token. Please refresh the page and try again.';
    } else {
        // Collect and sanitize input
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $state_code = trim($_POST['state_code']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Handle profile picture upload
        $profile_picture = null;
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '', pathinfo($_FILES['profile_picture']['name'], PATHINFO_FILENAME));
            $filename = 'profile_' . uniqid() . '_' . $safeName . '.' . $ext;
            $target = '/public/uploads/' . $filename;
            $abs_target = __DIR__ . '/../uploads/' . $filename;
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $abs_target)) {
                $profile_picture = $target;
            } else {
                $errors[] = "Failed to upload profile picture.";
            }
        }

        // Basic validation
        if (empty($email) || empty($state_code) || empty($password) || empty($confirm_password)) {
            $errors[] = "Email, state code, and password fields are required.";
        }
        // State code pattern validation (PL/24C/1234)
        if (!empty($state_code) && !preg_match('/^[A-Z]{2}\/\d{2}[A-Z]\/\d{4}$/i', $state_code)) {
            $errors[] = "State code must follow the pattern PL/24C/1234.";
        }
        if ($password !== $confirm_password) {
            $errors[] = "Passwords do not match.";
        }

        // Check if email exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = "Email already registered.";
        }

        // If no errors, insert user
        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            // Insert user with all fields
            $stmt = $conn->prepare("INSERT INTO users (name, email, state_code, occupation, phone, socials, portfolio, contact_private, password, profile_picture) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $occupation = $_POST['occupation'] ?? null;
            $phone = $_POST['phone'] ?? null;
            $socials = $_POST['socials'] ?? null;
            $portfolio = $_POST['portfolio'] ?? null;
            $contact_private = !empty($_POST['contact_private']) ? 1 : 0;
            $profile_picture_param = $profile_picture;
            $stmt->bind_param('ssssssssss', $name, $email, $state_code, $occupation, $phone, $socials, $portfolio, $contact_private, $hashed, $profile_picture_param);
            $ok = $stmt->execute();
            $user_id = $conn->insert_id;
            // Save skills (if any)
            if (!empty($_POST['skills'])) {
                $skills = array_filter(array_map('trim', explode(',', $_POST['skills'])));
                foreach ($skills as $skill) {
                    // Try to find skill id, or insert if not exists
                    $skill_stmt = $conn->prepare("SELECT id FROM skills WHERE name = ?");
                    $skill_stmt->bind_param('s', $skill);
                    $skill_stmt->execute();
                    $skill_res = $skill_stmt->get_result();
                    if ($row = $skill_res->fetch_assoc()) {
                        $skill_id = $row['id'];
                    } else {
                        $insert_skill = $conn->prepare("INSERT INTO skills (name) VALUES (?)");
                        $insert_skill->bind_param('s', $skill);
                        $insert_skill->execute();
                        $skill_id = $conn->insert_id;
                    }
                    $user_skill_stmt = $conn->prepare("INSERT INTO user_skills (user_id, skill_id) VALUES (?, ?)");
                    $user_skill_stmt->bind_param('ii', $user_id, $skill_id);
                    $user_skill_stmt->execute();
                }
            }
            // Save business info if provided
            $business_name = trim($_POST['business_name'] ?? '');
            $business_description = trim($_POST['business_description'] ?? '');
            if ($business_name) {
                $biz_stmt = $conn->prepare("INSERT INTO businesses (name, description) VALUES (?, ?)");
                $biz_stmt->bind_param('ss', $business_name, $business_description);
                $biz_stmt->execute();
                $business_id = $conn->insert_id;
                $update_user_biz = $conn->prepare("UPDATE users SET business_id=? WHERE id=?");
                $update_user_biz->bind_param('ii', $business_id, $user_id);
                $update_user_biz->execute();
            }
            // Save PPA if provided (assuming a column exists)
            $ppa = trim($_POST['ppa'] ?? '');
            if ($ppa) {
                $conn->query("ALTER TABLE users ADD COLUMN IF NOT EXISTS ppa VARCHAR(255)");
                $ppa_stmt = $conn->prepare("UPDATE users SET ppa=? WHERE id=?");
                $ppa_stmt->bind_param('si', $ppa, $user_id);
                $ppa_stmt->execute();
            }
            header("Location: register.php?registered=1");
            exit;
        }
    }
}

// Prepare toast messages
$toast_messages = [];
if (!empty($errors)) {
    foreach ($errors as $e) {
        $toast_messages[] = [
            'type' => 'error',
            'msg' => $e
        ];
    }
}
if (isset($_GET['registered']) && $_GET['registered'] == 1) {
    $toast_messages[] = [
        'type' => 'success',
        'msg' => 'Registration successful! You can now sign in.'
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account</title>
  <!-- Tailwind CSS CDN -->
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Bootstrap CSS CDN -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- FontAwesome CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    body { background-color: #f8fafc; }
    .form-box { max-width: 500px; margin: auto; }
    .form-control:focus { box-shadow: none; border-color: #4f46e5; }
    .checkbox-label { font-size: 0.95rem; }
    .input-group-text { cursor: pointer; }
  </style>
</head>
<body>
  <!-- Toast Notification Container -->
  <div id="toast-container" class="fixed top-5 right-5 z-50 flex flex-col gap-2"></div>
  <div class="min-h-screen flex items-center justify-center px-4">
    <div class="bg-white p-6 rounded-lg shadow-md w-full form-box">
      <h2 class="text-2xl font-semibold text-center mb-1">Create an Account</h2>
      <p class="text-center text-gray-500 mb-4">Join the ICT Corps Members community</p>
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <!-- Name -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-user mr-2"></i>Full Name</label>
          <input type="text" class="form-control" name="name" placeholder="John Doe" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>
        <!-- Primary Place of Assignment (PPA) -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-building mr-2"></i>Primary Place of Assignment (PPA)</label>
          <input type="text" class="form-control" name="ppa" placeholder="e.g. XYZ School" value="<?= htmlspecialchars($_POST['ppa'] ?? '') ?>">
        </div>
        <!-- State Code -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-hashtag mr-2"></i>State Code <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="state_code" placeholder="PL/23B/1234" required value="<?= htmlspecialchars($_POST['state_code'] ?? '') ?>">
        </div>
        <!-- Occupation -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-briefcase mr-2"></i>Occupation (Primary)</label>
          <input type="text" class="form-control" name="occupation" placeholder="Software Developer" value="<?= htmlspecialchars($_POST['occupation'] ?? '') ?>">
        </div>
        <!-- Skillset(s) -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-cogs mr-2"></i>Skillset(s)</label>
          <div id="skills-container" class="flex flex-wrap gap-2 mb-2"></div>
          <div class="input-group">
            <select id="skills-select" class="form-select">
              <option value="" disabled selected>Add a skill...</option>
              <option value="php">PHP</option>
              <option value="javascript">JavaScript</option>
              <option value="uiux">UI/UX</option>
              <option value="python">Python</option>
              <option value="networking">Networking</option>
              <option value="digital marketing">Digital Marketing</option>
              <option value="html">HTML</option>
              <option value="css">CSS</option>
              <option value="other">Other (type below)</option>
            </select>
            <input type="text" id="custom-skill" class="form-control" placeholder="Type a skill and press Enter" style="display:none;">
          </div>
          <input type="hidden" name="skills" id="skills-input">
          <small class="text-muted">Click to add, or type and press Enter for custom skills.</small>
        </div>
        <!-- Business Info -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-briefcase mr-2"></i>Business Name</label>
          <input type="text" class="form-control" name="business_name" placeholder="Business Name" value="<?= htmlspecialchars($_POST['business_name'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Business Description</label>
          <textarea class="form-control" name="business_description" placeholder="Describe your business..."><?= htmlspecialchars($_POST['business_description'] ?? '') ?></textarea>
        </div>
        <!-- General Contact -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-share-alt mr-2"></i>General Social URL</label>
          <input type="text" class="form-control" name="socials" placeholder="e.g. https://twitter.com/you" value="<?= htmlspecialchars($_POST['socials'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Portfolio Link</label>
          <input type="url" class="form-control" name="portfolio" placeholder="https://portfolio.com/you" value="<?= htmlspecialchars($_POST['portfolio'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" class="form-control" name="phone" placeholder="e.g. 08012345678" value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" class="form-control" name="email" placeholder="john.doe@example.com" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <!-- Contact Privacy Toggle -->
        <div class="form-check form-switch mb-4">
          <input class="form-check-input" type="checkbox" id="contact_private" name="contact_private" <?= !empty($_POST['contact_private']) ? 'checked' : '' ?>>
          <label class="form-check-label checkbox-label" for="contact_private">
            Keep my contact info private
          </label>
        </div>
        <!-- Password and Confirm Password -->
        <div class="row mb-3">
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-lock mr-2"></i>Password</label>
            <div class="input-group">
              <input type="password" class="form-control" name="password" placeholder="********" id="password" required>
              <span class="input-group-text">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>
          <div class="col-md-6">
            <label class="form-label"><i class="fas fa-lock mr-2"></i>Confirm Password</label>
            <div class="input-group">
              <input type="password" class="form-control" name="confirm_password" placeholder="********" id="confirmPassword" required>
              <span class="input-group-text">
                <i class="fas fa-eye"></i>
              </span>
            </div>
          </div>
        </div>
        <!-- Terms and Conditions -->
        <div class="form-check mb-4">
          <input class="form-check-input" type="checkbox" id="termsCheck" required>
          <label class="form-check-label checkbox-label" for="termsCheck">
            I agree to the <a href="#" class="text-green-600 hover:underline">terms and conditions</a>
          </label>
        </div>
        <!-- Submit Button -->
        <button type="submit" class="w-full bg-green-700 hover:bg-green-800 text-white py-2 px-4 rounded-lg">
          Create Account
        </button>
        <div class="text-center mt-3">
          Already have an account? <a href="login.php" class="text-green-700 font-semibold hover:underline">Sign In</a>
        </div>
      </form>
    </div>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Toast notification logic
      const toastData = <?php echo json_encode($toast_messages); ?>;
      const toastContainer = document.getElementById('toast-container');
      function showToast(type, msg) {
        const toast = document.createElement('div');
        toast.className = `toast-notification px-5 py-3 rounded shadow-lg flex items-center gap-3 mb-2 ${type === 'success' ? 'bg-green-600 text-white' : 'bg-red-600 text-white'}`;
        toast.innerHTML = `<i class='fa ${type === 'success' ? 'fa-check-circle' : 'fa-times-circle'} text-xl'></i><span>${msg}</span>`;
        toastContainer.appendChild(toast);
        setTimeout(() => {
          toast.style.opacity = '0';
          setTimeout(() => toast.remove(), 400);
        }, 3500);
      }
      if (toastData && toastData.length) {
        toastData.forEach(t => showToast(t.type, t.msg));
      }

      // Toggle password visibility
      document.querySelectorAll('.input-group-text').forEach(function(toggleBtn) {
        toggleBtn.addEventListener('click', function() {
          const input = this.parentElement.querySelector('input');
          const icon = this.querySelector('i');
          if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
          } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
          }
        });
      });

      // Skills logic
      const skillsContainer = document.getElementById('skills-container');
      const skillsSelect = document.getElementById('skills-select');
      const customSkill = document.getElementById('custom-skill');
      const skillsInput = document.getElementById('skills-input');
      let selectedSkills = [];
      function renderSkills() {
        skillsContainer.innerHTML = '';
        selectedSkills.forEach(skill => {
          const tag = document.createElement('span');
          tag.className = 'badge bg-green-200 text-green-800 px-2 py-1 rounded-pill d-flex align-items-center gap-1';
          tag.innerHTML = `${skill} <i class='fas fa-times cursor-pointer' style='font-size:12px;' data-skill='${skill}'></i>`;
          skillsContainer.appendChild(tag);
        });
        skillsInput.value = selectedSkills.join(',');
      }
      skillsContainer.addEventListener('click', function(e) {
        if (e.target.tagName === 'I' && e.target.dataset.skill) {
          selectedSkills = selectedSkills.filter(s => s !== e.target.dataset.skill);
          renderSkills();
        }
      });
      if (skillsSelect) {
        skillsSelect.addEventListener('change', function() {
          if (this.value === 'other') {
            customSkill.style.display = '';
            customSkill.focus();
          } else if (this.value && !selectedSkills.includes(this.value)) {
            selectedSkills.push(this.value);
            renderSkills();
            this.selectedIndex = 0;
            customSkill.style.display = 'none';
          }
        });
      }
      if (customSkill) {
        customSkill.addEventListener('keydown', function(e) {
          if (e.key === 'Enter' && this.value.trim()) {
            const val = this.value.trim();
            if (!selectedSkills.includes(val)) {
              selectedSkills.push(val);
              renderSkills();
            }
            this.value = '';
            e.preventDefault();
          }
        });
      }
    });
  </script>
</body>
</html>
