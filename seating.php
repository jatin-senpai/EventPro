<?php
session_start();
require_once 'config/database.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}

// Handle table deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_table'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT sa.event_id 
            FROM seating_arrangements sa 
            JOIN events e ON sa.event_id = e.event_id 
            WHERE sa.table_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['table_id'], $_SESSION['user_id']]);
        
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to delete this table.";
            header("Location: seating.php");
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM seating_arrangements WHERE table_id = ?");
        $stmt->execute([$_POST['table_id']]);
        
        $_SESSION['success'] = "Table deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting table: " . $e->getMessage();
    }
    header("Location: seating.php");
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
        SELECT sa.*, e.event_name
        FROM seating_arrangements sa
        JOIN events e ON sa.event_id = e.event_id
        WHERE e.user_id = ?
    ";
    $params = [$_SESSION['user_id']];
    
    if (!empty($_GET['event_id'])) {
        $sql .= " AND e.event_id = ?";
        $params[] = $_GET['event_id'];
    }
    
    $sql .= " ORDER BY sa.table_number ASC";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $tables = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching tables: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Seating - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <div class="max-w-7xl mx-auto mt-8 px-4">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Seating Arrangements</h1>
      <button id="openAddTableModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Add Table
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

    <!-- Event Filter -->
    <div class="bg-white shadow rounded mb-6 p-4">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

    <!-- Tables Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <?php foreach ($tables as $table): ?>
        <div class="bg-white shadow rounded p-4">
          <h5 class="text-xl font-semibold text-gray-800">Table <?php echo htmlspecialchars($table['table_number']); ?></h5>
          <p class="text-gray-700 mt-2">
            <strong>Event:</strong> <?php echo htmlspecialchars($table['event_name']); ?><br>
            <strong>Capacity:</strong> <?php echo htmlspecialchars($table['capacity']); ?> seats<br>
            <strong>Location:</strong> <?php echo htmlspecialchars($table['location']); ?>
          </p>
          <?php if ($table['notes']): ?>
            <p class="mt-2 text-sm text-gray-500 whitespace-pre-wrap"><?php echo nl2br(htmlspecialchars($table['notes'])); ?></p>
          <?php endif; ?>
          <div class="mt-4 flex space-x-2">
            <button type="button" class="edit-table bg-yellow-500 hover:bg-yellow-600 text-white text-sm py-1 px-3 rounded inline-flex items-center"
                    data-table='<?php echo json_encode($table); ?>'>
              <i class="fas fa-edit mr-1"></i> Edit
            </button>
            <form method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this table?');">
              <input type="hidden" name="table_id" value="<?php echo $table['table_id']; ?>">
              <button type="submit" name="delete_table" class="bg-red-600 hover:bg-red-700 text-white text-sm py-1 px-3 rounded inline-flex items-center">
                <i class="fas fa-trash mr-1"></i> Delete
              </button>
            </form>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Add Table Modal -->
  <div id="addTableModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Add Table</h5>
        <button id="closeAddTableModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_seating.php" method="POST">
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
            <label for="table_number" class="block text-sm font-medium text-gray-700">Table Number</label>
            <input type="number" id="table_number" name="table_number" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
            <input type="number" id="capacity" name="capacity" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" id="location" name="location" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelAddTableModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Add Table
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Table Modal -->
  <div id="editTableModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Edit Table</h5>
        <button id="closeEditTableModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/update_seating.php" method="POST">
        <input type="hidden" name="table_id" id="edit_table_id">
        <div class="p-4 space-y-4">
          <div>
            <label for="edit_table_number" class="block text-sm font-medium text-gray-700">Table Number</label>
            <input type="number" id="edit_table_number" name="table_number" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_capacity" class="block text-sm font-medium text-gray-700">Capacity</label>
            <input type="number" id="edit_capacity" name="capacity" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_location" class="block text-sm font-medium text-gray-700">Location</label>
            <input type="text" id="edit_location" name="location" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="edit_notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditTableModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Update Table
          </button>
        </div>
      </form>
    </div>
  </div>
  
  <script>
    // Toggle Add Table Modal
    const openAddTableModalBtn = document.getElementById('openAddTableModal');
    const addTableModal = document.getElementById('addTableModal');
    const closeAddTableModalBtn = document.getElementById('closeAddTableModal');
    const cancelAddTableModalBtn = document.getElementById('cancelAddTableModal');

    openAddTableModalBtn.addEventListener('click', () => {
      addTableModal.classList.remove('hidden');
    });
    closeAddTableModalBtn.addEventListener('click', () => {
      addTableModal.classList.add('hidden');
    });
    cancelAddTableModalBtn.addEventListener('click', () => {
      addTableModal.classList.add('hidden');
    });

    // Toggle Edit Table Modal
    const editTableModal = document.getElementById('editTableModal');
    const closeEditTableModalBtn = document.getElementById('closeEditTableModal');
    const cancelEditTableModalBtn = document.getElementById('cancelEditTableModal');

    closeEditTableModalBtn.addEventListener('click', () => {
      editTableModal.classList.add('hidden');
    });
    cancelEditTableModalBtn.addEventListener('click', () => {
      editTableModal.classList.add('hidden');
    });

    // Pre-fill Edit Table Modal on edit button click
    document.querySelectorAll('.edit-table').forEach(button => {
      button.addEventListener('click', function() {
        const table = JSON.parse(this.dataset.table);
        document.getElementById('edit_table_id').value = table.table_id;
        document.getElementById('edit_table_number').value = table.table_number;
        document.getElementById('edit_capacity').value = table.capacity;
        document.getElementById('edit_location').value = table.location;
        document.getElementById('edit_notes').value = table.notes || '';
        editTableModal.classList.remove('hidden');
      });
    });
  </script>
  <script src="assets/js/main.js"></script>
</body>
</html>
