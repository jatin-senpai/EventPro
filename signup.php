<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $errors = [];
    if (empty($username)) {
        $errors[] = "Username is required";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username must be at least 3 characters long";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ? OR email = ?");
            $stmt->execute([$username, $email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = "Username or email already exists";
            }
        } catch(PDOException $e) {
            $errors[] = "Error checking existing user: " . $e->getMessage();
        }
    }
    if (empty($errors)) {
        try {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (username, email, password, full_name)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$username, $email, $hashed_password, $full_name]);
            
            $_SESSION['success'] = "Account created successfully! Please sign in.";
            header("Location: signin.php");
            exit();
        } catch(PDOException $e) {
            $errors[] = "Error creating account: " . $e->getMessage();
        }
    }
}

$page_title = 'Sign Up';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - EventPro</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body class="relative bg-cover bg-center" style="background-image: url('assets/images/image2.jpg');">
  <div class="absolute inset-0 bg-black opacity-70"></div>
  <div class="min-h-screen flex items-center justify-center relative z-10">
    <div class="w-full max-w-lg px-6">
      <div class="bg-black bg-opacity-50 shadow-lg rounded-lg overflow-hidden">
        <div class="p-6 text-white">
          <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold  mb-2"><span class="text-blue-600">Event</span>Pro</h1>
            <p class="text-gray-200">Create your account to get started.</p>
          </div>
          <?php if (!empty($errors)): ?>
            <div class="mb-4 p-3 bg-red-100 border border-red-200 text-red-800 rounded relative">
              <ul class="list-disc ml-5">
                <?php foreach ($errors as $error): ?>
                  <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="absolute top-2 right-2 text-xl text-white" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
          <?php endif; ?>
          <form method="POST" action="" novalidate data-ajax="false">
            <div class="mb-4">
              <label for="full_name" class="block text-sm font-medium text-gray-200 mb-1">Full Name</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-user"></i></span>
                <input type="text" id="full_name" name="full_name" value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div class="mb-4">
              <label for="username" class="block text-sm font-medium text-gray-200 mb-1">Username</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-at"></i></span>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <p class="mt-1 text-xs text-gray-300">Username must be at least 3 characters long.</p>
            </div>
            <div class="mb-4">
              <label for="email" class="block text-sm font-medium text-gray-200 mb-1">Email</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-envelope"></i></span>
                <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div class="mb-4">
              <label for="password" class="block text-sm font-medium text-gray-200 mb-1">Password</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-lock"></i></span>
                <input type="password" id="password" name="password" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
              <p class="mt-1 text-xs text-gray-300">Password must be at least 6 characters long.</p>
            </div>
            <div class="mb-6">
              <label for="confirm_password" class="block text-sm font-medium text-gray-200 mb-1">Confirm Password</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-lock"></i></span>
                <input type="password" id="confirm_password" name="confirm_password" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div>
              <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-lg inline-flex items-center justify-center transition-transform transform hover:scale-105">
                <i class="fas fa-user-plus mr-2"></i> Create Account
              </button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center mt-6">
        <p class="text-gray-300">Already have an account? <a href="signin.php" class="text-blue-600 font-medium hover:underline">Sign In</a></p>
      </div>
    </div>
  </div>
  
  <script src="assets/js/main.js"></script>
</body>
</html>
