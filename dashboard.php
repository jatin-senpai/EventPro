<?php

session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
$page_title = 'Dashboard';
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM events WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $total_events = $stmt->fetchColumn();
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM events 
        WHERE user_id = ? AND event_date >= CURDATE() AND status = 'Active'
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_events = $stmt->fetchColumn();
    
    // Get total guests
    $stmt = $pdo->prepare("
        SELECT COUNT(g.guest_id) 
        FROM guests g 
        JOIN events e ON g.event_id = e.event_id 
        WHERE e.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_guests = $stmt->fetchColumn();
    // Get total budget
    $stmt = $pdo->prepare("
        SELECT SUM(b.amount) 
        FROM budget_items b 
        JOIN events e ON b.event_id = e.event_id 
        WHERE e.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $total_budget = $stmt->fetchColumn() ?: 0;
    // Get recent events
    $stmt = $pdo->prepare("
        SELECT * 
        FROM events 
        WHERE user_id = ? 
        ORDER BY event_date DESC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $recent_events = $stmt->fetchAll();
    
    // Get upcoming tasks
    $stmt = $pdo->prepare("
        SELECT t.*, e.event_name 
        FROM timeline_items t 
        JOIN events e ON t.event_id = e.event_id 
        WHERE e.user_id = ? AND t.due_date >= CURDATE() AND t.status != 'Completed'
        ORDER BY t.due_date ASC 
        LIMIT 5
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $upcoming_tasks = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching dashboard data: " . $e->getMessage();
}

include 'includes/header.php';
?>

<div class="max-w-7xl mx-auto mt-8 px-4">
  <!-- Header Section -->
  <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
    <a href="events.php" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
      <i class="fas fa-plus mr-2"></i> Create New Event
    </a>
  </div>

  <!-- Alerts -->
  <?php if (isset($_SESSION['success'])): ?>
    <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-800 rounded relative">
      <?php 
        echo $_SESSION['success'];
        unset($_SESSION['success']);
      ?>
      <button type="button" class="absolute top-2 right-2 text-xl" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['error'])): ?>
    <div class="mb-6 p-4 bg-red-100 border border-red-200 text-red-800 rounded relative">
      <?php 
        echo $_SESSION['error'];
        unset($_SESSION['error']);
      ?>
      <button type="button" class="absolute top-2 right-2 text-xl" onclick="this.parentElement.style.display='none';">&times;</button>
    </div>
  <?php endif; ?>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
    <div class="flex items-center p-6 bg-white shadow rounded">
      <div class="p-3 bg-blue-600 text-white rounded-full">
        <i class="fas fa-calendar-alt"></i>
      </div>
      <div class="ml-4">
        <h3 class="text-2xl font-bold"><?php echo $total_events; ?></h3>
        <p class="text-gray-600">Total Events</p>
      </div>
    </div>
    <div class="flex items-center p-6 bg-white shadow rounded">
      <div class="p-3" style="background-color: #4caf50;">
        <i class="fas fa-clock text-white"></i>
      </div>
      <div class="ml-4">
        <h3 class="text-2xl font-bold"><?php echo $upcoming_events; ?></h3>
        <p class="text-gray-600">Upcoming Events</p>
      </div>
    </div>
    <div class="flex items-center p-6 bg-white shadow rounded">
      <div class="p-3" style="background-color: #ff9800;">
        <i class="fas fa-users text-white"></i>
      </div>
      <div class="ml-4">
        <h3 class="text-2xl font-bold"><?php echo $total_guests; ?></h3>
        <p class="text-gray-600">Total Guests</p>
      </div>
    </div>
    <div class="flex items-center p-6 bg-white shadow rounded">
      <div class="p-3" style="background-color: #e91e63;">
        <i class="fas fa-dollar-sign text-white"></i>
      </div>
      <div class="ml-4">
        <h3 class="text-2xl font-bold">$<?php echo number_format($total_budget, 2); ?></h3>
        <p class="text-gray-600">Total Budget</p>
      </div>
    </div>
  </div>

  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
    <!-- Recent Events -->
    <div class="bg-white shadow rounded">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-semibold text-gray-800 inline-flex items-center">
          <i class="fas fa-calendar-alt text-blue-600 mr-2"></i> Recent Events
        </h5>
        <a href="events.php" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded">
          View All
        </a>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Event Name</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Date</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Status</th>
              <th class="px-4 py-2"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach ($recent_events as $event): ?>
              <tr>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($event['event_name']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                <td class="px-4 py-2">
                  <span class="px-2 py-1 inline-block rounded text-white <?php echo $event['status'] == 'Active' ? 'bg-green-500' : 'bg-gray-500'; ?>">
                    <?php echo htmlspecialchars($event['status']); ?>
                  </span>
                </td>
                <td class="px-4 py-2">
                  <a href="event_details.php?id=<?php echo $event['event_id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded inline-flex items-center">
                    <i class="fas fa-eye"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($recent_events)): ?>
              <tr>
                <td colspan="4" class="text-center py-4 text-gray-600">No events found</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Upcoming Tasks -->
    <div class="bg-white shadow rounded">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-semibold text-gray-800 inline-flex items-center">
          <i class="fas fa-tasks text-blue-600 mr-2"></i> Upcoming Tasks
        </h5>
        <a href="timeline.php" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded">
          View All
        </a>
      </div>
      <div class="p-4 overflow-x-auto">
        <table class="min-w-full">
          <thead>
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Task</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Event</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Due Date</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach ($upcoming_tasks as $task): ?>
              <tr>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($task['task_name']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($task['event_name']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo date('M d, Y', strtotime($task['due_date'])); ?></td>
                <td class="px-4 py-2">
                  <?php 
                    $badgeClass = $task['status'] == 'Completed' ? 'bg-green-500' : 
                                  ($task['status'] == 'In Progress' ? 'bg-yellow-500' : 'bg-blue-500');
                  ?>
                  <span class="px-2 py-1 inline-block rounded text-white <?php echo $badgeClass; ?>">
                    <?php echo htmlspecialchars($task['status']); ?>
                  </span>
                </td>
              </tr>
            <?php endforeach; ?>
            <?php if (empty($upcoming_tasks)): ?>
              <tr>
                <td colspan="4" class="text-center py-4 text-gray-600">No upcoming tasks</td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div id="mobile-menu" class="md:hidden hidden px-4 pb-4 space-y-3"></div>

<?php include 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }
});
</script>


</body>
</html>
