<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Event ID not provided.";
    header("Location: events.php");
    exit();
}

try {
    // Fetching event details
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ? AND user_id = ?");
    $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
    $event = $stmt->fetch();
    
    if (!$event) {
        $_SESSION['error'] = "Event not found or you don't have permission to view it.";
        header("Location: events.php");
        exit();
    }
    
    // Fetching guest count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE event_id = ?");
    $stmt->execute([$_GET['id']]);
    $guest_count = $stmt->fetchColumn();
    
    // Fetching vendor count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_vendors WHERE event_id = ?");
    $stmt->execute([$_GET['id']]);
    $vendor_count = $stmt->fetchColumn();
    
    // Fetching budget total
    $stmt = $pdo->prepare("SELECT SUM(amount) FROM budget_items WHERE event_id = ?");
    $stmt->execute([$_GET['id']]);
    $budget_total = $stmt->fetchColumn() ?? 0;
    
    // Fetching timeline items
    $stmt = $pdo->prepare("SELECT * FROM timeline_items WHERE event_id = ? ORDER BY due_date ASC");
    $stmt->execute([$_GET['id']]);
    $timeline_items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching event details: " . $e->getMessage();
    header("Location: events.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo htmlspecialchars($event['event_name']); ?> - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <div class="max-w-7xl mx-auto mt-8 px-4">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0"><?php echo htmlspecialchars($event['event_name']); ?></h1>
      <div class="flex space-x-3">
        <a href="events.php" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded inline-flex items-center">
          <i class="fas fa-arrow-left mr-2"></i> Back to Events
        </a>
        <button id="openEditEventModal" class="bg-yellow-500 hover:bg-yellow-600 text-white font-medium py-2 px-4 rounded inline-flex items-center">
          <i class="fas fa-edit mr-2"></i> Edit Event
        </button>
      </div>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded relative">
        <?php 
          echo $_SESSION['success'];
          unset($_SESSION['success']);
        ?>
        <button class="absolute top-2 right-2 text-xl leading-none" onclick="this.parentElement.style.display='none';">&times;</button>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded relative">
        <?php 
          echo $_SESSION['error'];
          unset($_SESSION['error']);
        ?>
        <button class="absolute top-2 right-2 text-xl leading-none" onclick="this.parentElement.style.display='none';">&times;</button>
      </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2 space-y-6">
        <!-- Event Overview Card -->
        <div class="bg-white shadow rounded p-6">
          <h5 class="text-xl font-semibold text-gray-800 mb-4">Event Overview</h5>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <p class="text-gray-700"><strong>Date:</strong> <?php echo date('M d, Y', strtotime($event['event_date'])); ?></p>
              <p class="text-gray-700"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
              <p class="text-gray-700">
                <strong>Status:</strong>
                <span class="px-2 py-1 inline-block rounded text-white <?php echo $event['status'] == 'Active' ? 'bg-green-500' : 'bg-gray-500'; ?>">
                  <?php echo htmlspecialchars($event['status']); ?>
                </span>
              </p>
            </div>
            <div>
              <p class="text-gray-700"><strong>Total Guests:</strong> <?php echo $guest_count; ?></p>
              <p class="text-gray-700"><strong>Total Vendors:</strong> <?php echo $vendor_count; ?></p>
              <p class="text-gray-700"><strong>Total Budget:</strong> $<?php echo number_format($budget_total, 2); ?></p>
            </div>
          </div>
          <?php if ($event['description']): ?>
            <div class="mt-4">
              <h6 class="text-lg font-medium text-gray-800">Description</h6>
              <p class="text-gray-700 whitespace-pre-wrap"><?php echo htmlspecialchars($event['description']); ?></p>
            </div>
          <?php endif; ?>
        </div>

        <!-- Timeline Card -->
        <div class="bg-white shadow rounded p-6">
          <div class="flex justify-between items-center mb-4">
            <h5 class="text-xl font-semibold text-gray-800">Timeline</h5>
            <button id="openAddTimelineModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-1 px-3 rounded inline-flex items-center text-sm">
              <i class="fas fa-plus mr-1"></i> Add Task
            </button>
          </div>
          <div class="space-y-4">
            <?php foreach ($timeline_items as $item): ?>
              <div class="p-4 border-l-4 border-blue-500 bg-gray-50 rounded">
                <div class="flex justify-between items-center">
                  <div>
                    <p class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($item['due_date'])); ?></p>
                    <h6 class="text-lg font-medium text-gray-800"><?php echo htmlspecialchars($item['task_name']); ?></h6>
                    <p class="text-gray-700"><?php echo htmlspecialchars($item['notes']); ?></p>
                    <?php 
                      $badgeClasses = [
                        'Completed'   => 'bg-green-500',
                        'In Progress' => 'bg-blue-500',
                        'Pending'     => 'bg-yellow-500'
                      ];
                    ?>
                    <span class="mt-2 inline-block px-2 py-1 text-xs font-semibold text-white rounded <?php echo $badgeClasses[$item['status']]; ?>">
                      <?php echo htmlspecialchars($item['status']); ?>
                    </span>
                  </div>
                  <div class="flex space-x-2">
                    <button type="button" class="edit-task bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded" 
                            data-task='<?php echo json_encode($item); ?>'>
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" action="handlers/delete_timeline.php" onsubmit="return confirm('Are you sure you want to delete this task?');">
                      <input type="hidden" name="task_id" value="<?php echo $item['timeline_id']; ?>">
                      <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                      <button type="submit" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded">
                        <i class="fas fa-trash"></i>
                      </button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Quick Actions Card -->
      <div class="space-y-6">
        <div class="bg-white shadow rounded p-6">
          <h5 class="text-xl font-semibold text-gray-800 mb-4">Quick Actions</h5>
          <div class="space-y-3">
            <a href="guests.php?event_id=<?php echo $event['event_id']; ?>" class="block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
              <i class="fas fa-users mr-2"></i> Manage Guests
            </a>
            <a href="vendors.php?event_id=<?php echo $event['event_id']; ?>" class="block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
              <i class="fas fa-truck mr-2"></i> Manage Vendors
            </a>
            <a href="budget.php?event_id=<?php echo $event['event_id']; ?>" class="block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
              <i class="fas fa-dollar-sign mr-2"></i> Manage Budget
            </a>
            <a href="seating.php?event_id=<?php echo $event['event_id']; ?>" class="block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
              <i class="fas fa-chair mr-2"></i> Manage Seating
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Event Modal -->
  <div id="editEventModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Edit Event</h5>
        <button id="closeEditEventModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/update_event.php" method="POST">
        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
        <div class="p-4 space-y-4">
          <div>
            <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="event_name" name="event_name" value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
          </div>
          <div>
            <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
            <input type="date" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="event_date" name="event_date" value="<?php echo $event['event_date']; ?>" required>
          </div>
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="description" name="description" rows="3"><?php echo htmlspecialchars($event['description'] ?? ''); ?></textarea>
          </div>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="status" name="status" required>
              <option value="Active" <?php echo $event['status'] == 'Active' ? 'selected' : ''; ?>>Active</option>
              <option value="Completed" <?php echo $event['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
              <option value="Cancelled" <?php echo $event['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditEventModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">Cancel</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">Update Event</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Timeline Task Modal -->
  <div id="addTimelineModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Add Timeline Task</h5>
        <button id="closeAddTimelineModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_timeline.php" method="POST">
        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
        <div class="p-4 space-y-4">
          <div>
            <label for="task_name" class="block text-sm font-medium text-gray-700">Task Name</label>
            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="task_name" name="task_name" required>
          </div>
          <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="due_date" name="due_date" required>
          </div>
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="notes" name="notes" rows="3"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelAddTimelineModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">Cancel</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">Add Task</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Timeline Task Modal -->
  <div id="editTimelineModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Edit Task</h5>
        <button id="closeEditTimelineModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/update_timeline.php" method="POST">
        <input type="hidden" name="task_id" id="edit_task_id">
        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
        <div class="p-4 space-y-4">
          <div>
            <label for="edit_task_name" class="block text-sm font-medium text-gray-700">Task Name</label>
            <input type="text" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="edit_task_name" name="task_name" required>
          </div>
          <div>
            <label for="edit_due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="edit_due_date" name="due_date" required>
          </div>
          <div>
            <label for="edit_notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="edit_notes" name="notes" rows="3"></textarea>
          </div>
          <div>
            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select class="mt-1 block w-full border border-gray-300 rounded-md p-2" id="edit_status" name="status" required>
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditTimelineModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">Cancel</button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">Update Task</button>
        </div>
      </form>
    </div>
  </div>


  <script>
    // Edit Task Modal: Pre-fill edit form when edit button is clicked
    document.querySelectorAll('.edit-task').forEach(button => {
      button.addEventListener('click', function() {
        const task = JSON.parse(this.dataset.task);
        document.getElementById('edit_task_id').value = task.task_id;
        document.getElementById('edit_task_name').value = task.task_name;
        document.getElementById('edit_due_date').value = task.due_date;
        document.getElementById('edit_notes').value = task.notes || '';
        document.getElementById('edit_status').value = task.status;
        document.getElementById('editTimelineModal').classList.remove('hidden');
      });
    });

    // Modal toggling for Edit Event Modal
    document.getElementById('openEditEventModal').addEventListener('click', () => {
      document.getElementById('editEventModal').classList.remove('hidden');
    });
    document.getElementById('closeEditEventModal').addEventListener('click', () => {
      document.getElementById('editEventModal').classList.add('hidden');
    });
    document.getElementById('cancelEditEventModal').addEventListener('click', () => {
      document.getElementById('editEventModal').classList.add('hidden');
    });

    // Modal toggling for Add Timeline Modal
    document.getElementById('openAddTimelineModal').addEventListener('click', () => {
      document.getElementById('addTimelineModal').classList.remove('hidden');
    });
    document.getElementById('closeAddTimelineModal').addEventListener('click', () => {
      document.getElementById('addTimelineModal').classList.add('hidden');
    });
    document.getElementById('cancelAddTimelineModal').addEventListener('click', () => {
      document.getElementById('addTimelineModal').classList.add('hidden');
    });

    // Modal toggling for Edit Timeline Modal
    document.getElementById('closeEditTimelineModal').addEventListener('click', () => {
      document.getElementById('editTimelineModal').classList.add('hidden');
    });
    document.getElementById('cancelEditTimelineModal').addEventListener('click', () => {
      document.getElementById('editTimelineModal').classList.add('hidden');
    });
  </script>

  <script src="assets/js/main.js"></script>
</body>
</html>
