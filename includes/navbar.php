<?php
if (!isset($_SESSION)) {
    session_start();
}
?>
<nav class="bg-white shadow">
  <div class="max-w-7xl mx-auto px-4">
    <div class="flex justify-between items-center py-4">
      <a href="index.php" class="text-2xl font-bold text-gray-900"><span class="text-blue-600">Event</span>Pro</a>
      <!-- Desktop Menu -->
      <div class="hidden md:flex space-x-6">
        <?php if (isset($_SESSION['user_id'])): ?>
          <a href="dashboard.php" class="flex items-center text-gray-700 hover:text-gray-900">
            <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
          </a>
          <a href="events.php" class="flex items-center text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'font-semibold' : ''; ?>">
            <i class="fas fa-calendar-alt mr-1"></i> Events
          </a>
          <a href="guests.php" class="flex items-center text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'guests.php' ? 'font-semibold' : ''; ?>">
            <i class="fas fa-users mr-1"></i> Guests
          </a>
          <a href="vendors.php" class="flex items-center text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'vendors.php' ? 'font-semibold' : ''; ?>">
            <i class="fas fa-truck mr-1"></i> Vendors
          </a>
          <a href="budget.php" class="flex items-center text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'budget.php' ? 'font-semibold' : ''; ?>">
            <i class="fas fa-dollar-sign mr-1"></i> Budget
          </a>
          <a href="timeline.php" class="flex items-center text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'timeline.php' ? 'font-semibold' : ''; ?>">
            <i class="fas fa-tasks mr-1"></i> Timeline
          </a>
          <a href="seating.php" class="flex items-center text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'seating.php' ? 'font-semibold' : ''; ?>">
            <i class="fas fa-chair mr-1"></i> Seating
          </a>
          <span class="flex items-center text-gray-700">
            <i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
          </span>
          <a href="handlers/logout.php" class="flex items-center text-gray-700 hover:text-gray-900">
            <i class="fas fa-sign-out-alt mr-1"></i> Logout
          </a>
        <?php else: ?>
          <a href="signin.php" class="flex items-center text-gray-700 hover:text-gray-900">
            <i class="fas fa-sign-in-alt mr-1"></i> Sign In
          </a>
          <a href="signup.php" class="flex items-center text-gray-700 hover:text-gray-900">
            <i class="fas fa-user-plus mr-1"></i> Sign Up
          </a>
        <?php endif; ?>
      </div>
      <!-- Mobile Menu Button -->
      <div class="md:hidden">
        <button id="mobile-menu-button" class="text-gray-700 focus:outline-none">
          <i class="fas fa-bars"></i>
        </button>
      </div>
    </div>
  </div>
  <!-- Mobile Menu -->
  <div id="mobile-menu" class="md:hidden hidden px-4 pb-4 space-y-3">
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="dashboard.php" class="block text-gray-700 hover:text-gray-900">
        <i class="fas fa-tachometer-alt mr-1"></i> Dashboard
      </a>
      <a href="events.php" class="block text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'font-semibold' : ''; ?>">
        <i class="fas fa-calendar-alt mr-1"></i> Events
      </a>
      <a href="guests.php" class="block text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'guests.php' ? 'font-semibold' : ''; ?>">
        <i class="fas fa-users mr-1"></i> Guests
      </a>
      <a href="vendors.php" class="block text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'vendors.php' ? 'font-semibold' : ''; ?>">
        <i class="fas fa-truck mr-1"></i> Vendors
      </a>
      <a href="budget.php" class="block text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'budget.php' ? 'font-semibold' : ''; ?>">
        <i class="fas fa-dollar-sign mr-1"></i> Budget
      </a>
      <a href="timeline.php" class="block text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'timeline.php' ? 'font-semibold' : ''; ?>">
        <i class="fas fa-tasks mr-1"></i> Timeline
      </a>
      <a href="seating.php" class="block text-gray-700 hover:text-gray-900 <?php echo basename($_SERVER['PHP_SELF']) == 'seating.php' ? 'font-semibold' : ''; ?>">
        <i class="fas fa-chair mr-1"></i> Seating
      </a>
      <div class="border-t pt-2">
        <span class="block text-gray-700">
          <i class="fas fa-user mr-1"></i> <?php echo htmlspecialchars($_SESSION['full_name']); ?>
        </span>
        <a href="handlers/logout.php" class="block text-gray-700 hover:text-gray-900 mt-1">
          <i class="fas fa-sign-out-alt mr-1"></i> Logout
        </a>
      </div>
    <?php else: ?>
      <a href="signin.php" class="block text-gray-700 hover:text-gray-900">
        <i class="fas fa-sign-in-alt mr-1"></i> Sign In
      </a>
      <a href="signup.php" class="block text-gray-700 hover:text-gray-900">
        <i class="fas fa-user-plus mr-1"></i> Sign Up
      </a>
    <?php endif; ?>
  </div>
</nav>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const mobileMenuButton = document.getElementById("mobile-menu-button");
    const mobileMenu = document.getElementById("mobile-menu");

    mobileMenuButton.addEventListener("click", function () {
        mobileMenu.classList.toggle("hidden");
    });
});
</script>

