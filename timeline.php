<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_task'])) {
    try {
      // First, verify that the event belongs to the user
        $stmt = $pdo->prepare("
            SELECT ti.event_id 
            FROM timeline_items ti 
            JOIN events e ON ti.event_id = e.event_id 
            WHERE ti.timeline_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['timeline_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to delete this task.";
            header("Location: timeline.php");
            exit();
        }
        
        $stmt = $pdo->prepare("DELETE FROM timeline_items WHERE timeline_id = ?");
        $stmt->execute([$_POST['timeline_id']]);
        
        $_SESSION['success'] = "Task deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting task: " . $e->getMessage();
    }
    header("Location: timeline.php");
    exit();
}

// Fetch user's events for filtering
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching events: " . $e->getMessage();
}


try {
    $sql = "
        SELECT ti.*, e.event_name
        FROM timeline_items ti
        JOIN events e ON ti.event_id = e.event_id
        WHERE e.user_id = ?
    ";
    $params = [$_SESSION['user_id']];
    
    if (!empty($_GET['event_id'])) {
        $sql .= " AND e.event_id = ?";
        $params[] = $_GET['event_id'];
    }
    
    $sql .= " ORDER BY ti.due_date ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tasks = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching tasks: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Timeline - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <div class="max-w-7xl mx-auto mt-8 px-4">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Timeline</h1>
      <button id="openAddTaskModal" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
        <i class="fas fa-plus mr-2"></i> Add Task
      </button>
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

    <!-- Event Filter -->
    <div class="bg-white shadow rounded mb-6 p-4">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4" data-ajax="false">
        <div>
          <label for="event_id" class="block text-sm font-medium text-gray-700">Filter by Event</label>
          <select id="event_id" name="event_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
            <option value="">All Events</option>
            <?php foreach ($events as $event): ?>
              <option value="<?php echo $event['event_id']; ?>" <?php echo isset($_GET['event_id']) && $_GET['event_id'] == $event['event_id'] ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($event['event_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="md:col-span-1 flex items-end">
          <button type="submit" class="w-[150px] bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Filter
          </button>
        </div>
      </form>
    </div>

    <!-- Timeline Card -->
    <div class="bg-white shadow rounded p-6">
      <div class="space-y-6">
        <?php foreach ($tasks as $task): ?>
          <div class="border-l-4 border-blue-500 pl-4">
            <div class="flex justify-between items-center">
              <div>
                <p class="text-sm text-gray-500"><?php echo date('M d, Y', strtotime($task['due_date'])); ?></p>
                <h5 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($task['task_name']); ?></h5>
                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($task['event_name']); ?></p>
                <?php if ($task['notes']): ?>
                  <p class="mt-1 text-gray-700"><?php echo nl2br(htmlspecialchars($task['notes'])); ?></p>
                <?php endif; ?>
              </div>
              <div>
                <?php 
                  $badgeClasses = [
                    'Completed'   => 'bg-green-500',
                    'In Progress' => 'bg-blue-500',
                    'Pending'     => 'bg-yellow-500'
                  ];
                ?>
                <span class="px-2 py-1 rounded text-white <?php echo $badgeClasses[$task['status']]; ?>">
                  <?php echo htmlspecialchars($task['status']); ?>
                </span>
              </div>
            </div>
            <div class="mt-4 flex space-x-2">
              <button type="button" 
                      class="edit-task inline-flex items-center bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-1 px-3 rounded"
                      data-task='<?php echo htmlspecialchars(json_encode($task), ENT_QUOTES, "UTF-8"); ?>'>
                <i class="fas fa-edit mr-1"></i> Edit
              </button>
              <form method="POST" class="inline" data-ajax="false" onsubmit="return confirm('Are you sure you want to delete this task?');">
                <input type="hidden" name="timeline_id" value="<?php echo $task['timeline_id']; ?>">
                <button type="submit" name="delete_task" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-1 px-3 rounded">
                  <i class="fas fa-trash mr-1"></i> Delete
                </button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Add Task Modal -->
  <div id="addTaskModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Add Task</h5>
        <button id="closeAddTaskModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_timeline.php" method="POST" data-ajax="false">
        <div class="p-4 space-y-4">
          <div>
            <label for="event_id_modal" class="block text-sm font-medium text-gray-700">Event</label>
            <select id="event_id_modal" name="event_id" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="">Select Event</option>
              <?php foreach ($events as $event): ?>
                <option value="<?php echo $event['event_id']; ?>">
                  <?php echo htmlspecialchars($event['event_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="task_name" class="block text-sm font-medium text-gray-700">Task Name</label>
            <input type="text" id="task_name" name="task_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" id="due_date" name="due_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelAddTaskModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Add Task
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Task Modal -->
  <div id="editTaskModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Edit Task</h5>
        <button id="closeEditTaskModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/update_timeline.php" method="POST" data-ajax="false">
        <input type="hidden" name="timeline_id" id="edit_task_id">
        <div class="p-4 space-y-4">
          <div>
            <label for="edit_task_name" class="block text-sm font-medium text-gray-700">Task Name</label>
            <input type="text" id="edit_task_name" name="task_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_due_date" class="block text-sm font-medium text-gray-700">Due Date</label>
            <input type="date" id="edit_due_date" name="due_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="edit_notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
          <div>
            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="edit_status" name="status" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="Pending">Pending</option>
              <option value="In Progress">In Progress</option>
              <option value="Completed">Completed</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditTaskModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Update Task
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Custom Scripts -->
  <script>
    // Toggle Add Task Modal
    const openAddTaskModalBtn = document.getElementById('openAddTaskModal');
    const addTaskModal = document.getElementById('addTaskModal');
    const closeAddTaskModalBtn = document.getElementById('closeAddTaskModal');
    const cancelAddTaskModalBtn = document.getElementById('cancelAddTaskModal');

    openAddTaskModalBtn.addEventListener('click', () => {
      addTaskModal.classList.remove('hidden');
    });
    closeAddTaskModalBtn.addEventListener('click', () => {
      addTaskModal.classList.add('hidden');
    });
    cancelAddTaskModalBtn.addEventListener('click', () => {
      addTaskModal.classList.add('hidden');
    });

    // Toggle Edit Task Modal
    const editTaskModal = document.getElementById('editTaskModal');
    const closeEditTaskModalBtn = document.getElementById('closeEditTaskModal');
    const cancelEditTaskModalBtn = document.getElementById('cancelEditTaskModal');

    closeEditTaskModalBtn.addEventListener('click', () => {
      editTaskModal.classList.add('hidden');
    });
    cancelEditTaskModalBtn.addEventListener('click', () => {
      editTaskModal.classList.add('hidden');
    });

    // Pre-fill Edit Task Modal on edit button click
    document.querySelectorAll('.edit-task').forEach(button => {
      button.addEventListener('click', function() {
        const task = JSON.parse(this.dataset.task);
        document.getElementById('edit_task_id').value = task.timeline_id;
        document.getElementById('edit_task_name').value = task.task_name;
        document.getElementById('edit_due_date').value = task.due_date;
        document.getElementById('edit_notes').value = task.notes || '';
        document.getElementById('edit_status').value = task.status;
        editTaskModal.classList.remove('hidden');
      });
    });
  </script>
  <script src="assets/js/main.js"></script>
</body>
</html>
