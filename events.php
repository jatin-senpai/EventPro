<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Handle event deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_event'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$_POST['event_id'], $_SESSION['user_id']]);
        $_SESSION['success'] = "Event deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
    }
    header("Location: events.php");
    exit();
}

// Fetch user's events
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching events: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Events - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <div class="max-w-7xl mx-auto mt-8 px-4">
    
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Events</h1>
      <button id="openAddEventModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Add Event
      </button> 
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

    <!-- Events Table -->
    <div class="bg-white shadow rounded overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Event Name</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Date</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Location</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Status</th>
            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach ($events as $event): ?>
            <tr>
              <td class="px-4 py-3 text-gray-700"><?php echo htmlspecialchars($event['event_name']); ?></td>
              <td class="px-4 py-3 text-gray-700"><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
              <td class="px-4 py-3 text-gray-700"><?php echo htmlspecialchars($event['location']); ?></td>
              <td class="px-4 py-3">
                <span class="px-2 py-1 inline-block rounded text-white <?php echo $event['status'] == 'Active' ? 'bg-green-500' : 'bg-gray-500'; ?>">
                  <?php echo htmlspecialchars($event['status']); ?>
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex space-x-2">
                  <a href="event_details.php?id=<?php echo $event['event_id']; ?>" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium py-1 px-3 rounded inline-flex items-center">
                    <i class="fas fa-eye mr-1"></i> View
                  </a>
                  <button type="button" class="edit-event bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-1 px-3 rounded inline-flex items-center" data-event='<?php echo json_encode($event); ?>'>
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>
                  <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this event?');">
                    <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                    <button type="submit" name="delete_event" class="bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-1 px-3 rounded inline-flex items-center">
                      <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Add Event Modal -->
  <div id="addEventModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Add Event</h5>
        <button id="closeAddEventModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_event.php" method="POST">
        <div class="p-4 space-y-4">
          <div>
            <label for="event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
            <input type="text" id="event_name" name="event_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
            <input type="date" id="event_date" name="event_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" id="location" name="location" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelAddEventModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Add Event
          </button>
        </div>
      </form>
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
        <input type="hidden" name="event_id" id="edit_event_id">
        <div class="p-4 space-y-4">
          <div>
            <label for="edit_event_name" class="block text-sm font-medium text-gray-700">Event Name</label>
            <input type="text" id="edit_event_name" name="event_name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_event_date" class="block text-sm font-medium text-gray-700">Event Date</label>
            <input type="date" id="edit_event_date" name="event_date" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" id="edit_location" name="location" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="edit_description" name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
          <div>
            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="edit_status" name="status" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="Active">Active</option>
              <option value="Completed">Completed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditEventModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Update Event
          </button>
        </div>
      </form>
    </div>
  </div>
  <script>
    // Toggle Add Event Modal
    const openAddEventModalBtn = document.getElementById('openAddEventModal');
    const addEventModal = document.getElementById('addEventModal');
    const closeAddEventModalBtn = document.getElementById('closeAddEventModal');
    const cancelAddEventModalBtn = document.getElementById('cancelAddEventModal');

    openAddEventModalBtn.addEventListener('click', () => {
      addEventModal.classList.remove('hidden');
    });
    closeAddEventModalBtn.addEventListener('click', () => {
      addEventModal.classList.add('hidden');
    });
    cancelAddEventModalBtn.addEventListener('click', () => {
      addEventModal.classList.add('hidden');
    });

    // Toggle Edit Event Modal
    const editEventModal = document.getElementById('editEventModal');
    const closeEditEventModalBtn = document.getElementById('closeEditEventModal');
    const cancelEditEventModalBtn = document.getElementById('cancelEditEventModal');

    closeEditEventModalBtn.addEventListener('click', () => {
      editEventModal.classList.add('hidden');
    });
    cancelEditEventModalBtn.addEventListener('click', () => {
      editEventModal.classList.add('hidden');
    });

    // Pre-fill Edit Event Modal form on edit button click
    document.querySelectorAll('.edit-event').forEach(button => {
      button.addEventListener('click', function() {
        const eventData = JSON.parse(this.dataset.event);
        document.getElementById('edit_event_id').value = eventData.event_id;
        document.getElementById('edit_event_name').value = eventData.event_name;
        document.getElementById('edit_event_date').value = eventData.event_date;
        document.getElementById('edit_location').value = eventData.location;
        document.getElementById('edit_description').value = eventData.description || '';
        document.getElementById('edit_status').value = eventData.status;
        editEventModal.classList.remove('hidden');
      });
    });
  </script>
</body>
</html>
