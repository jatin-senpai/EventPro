<?php
session_start();
require_once 'config/database.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    $errors = [];
    
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                
                header("Location: index.php");
                exit();
            } else {
                $errors[] = "Invalid username or password";
            }
        } catch(PDOException $e) {
            $errors[] = "Error signing in: " . $e->getMessage();
        }
    }
}

$page_title = 'Sign In';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In - EventPro</title>
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
    <div class="w-full max-w-md px-6">
      <div class="bg-black bg-opacity-50 shadow-lg rounded-lg overflow-hidden">
        <div class="p-6 text-white">
          <div class="text-center mb-8">
            <h1 class="text-4xl font-extrabold mb-2"><span class="text-blue-600">Event</span>Pro</h1>
            <p class="text-gray-200">Welcome back! Please sign in to continue.</p>
          </div>
          <?php if (isset($_SESSION['success'])): ?>
            <div class="mb-4 p-3 bg-green-100 border border-green-200 text-green-800 rounded relative">
              <?php 
              echo $_SESSION['success'];
              unset($_SESSION['success']);
              ?>
              <button type="button" class="absolute top-2 right-2 text-xl text-white" onclick="this.parentElement.style.display='none';">&times;</button>
            </div>
          <?php endif; ?>
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
              <label for="username" class="block text-sm font-medium text-gray-200 mb-1">Username</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-user"></i></span>
                <input type="text" id="username" name="username" value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div class="mb-6">
              <label for="password" class="block text-sm font-medium text-gray-200 mb-1">Password</label>
              <div class="flex items-center border border-gray-300 rounded-md bg-black bg-opacity-30">
                <span class="px-3 text-gray-300"><i class="fas fa-lock"></i></span>
                <input type="password" id="password" name="password" required class="w-full p-2 bg-transparent text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
              </div>
            </div>
            <div>
              <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg text-lg inline-flex items-center justify-center">
                <i class="fas fa-sign-in-alt mr-2"></i> Sign In
              </button>
            </div>
          </form>
        </div>
      </div>
      <div class="text-center mt-6">
        <p class="text-gray-300">Don't have an account? <a href="signup.php" class="text-blue-600 font-medium hover:underline">Sign Up</a></p>
      </div>
    </div>
  </div>
  
  <script src="assets/js/main.js"></script>
</body>
</html>
