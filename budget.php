<?php
session_start();
require_once 'config/database.php';

$current_user_id = $_SESSION['user_id'];

// Handling budget item deletion
if (isset($_POST['delete_item'])) {
    $item_id = $_POST['item_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM budget_items WHERE item_id = ?");
        $stmt->execute([$item_id]);
        $_SESSION['success'] = "Budget item deleted successfully";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting budget item: " . $e->getMessage();
    }
    header("Location: budget.php");
    exit();
}

// Fetching events belonging to current user for the filter
try {
    $stmt = $pdo->prepare("SELECT event_id, event_name FROM events WHERE user_id = ? ORDER BY event_date DESC");
    $stmt->execute([$current_user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching events: " . $e->getMessage();
    $events = [];
}

// Fetching budget items for events belonging to current user with optional event filter
$event_filter = isset($_GET['event_id']) ? $_GET['event_id'] : null;
try {
    $query = "
        SELECT bi.*, e.event_name 
        FROM budget_items bi 
        JOIN events e ON bi.event_id = e.event_id
        WHERE e.user_id = ?
    ";
    $params = [$current_user_id];

    if ($event_filter) {
        $query .= " AND bi.event_id = ?";
        $params[] = $event_filter;
    }
    $query .= " ORDER BY bi.event_id, bi.category";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $budget_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Calculate totals
    $totals = [
        'total_budget' => 0,
        'total_paid' => 0,
        'total_pending' => 0
    ];

    foreach ($budget_items as $item) {
        $totals['total_budget'] += $item['amount'];
        if ($item['status'] === 'Paid') {
            $totals['total_paid'] += $item['amount'];
        } else {
            $totals['total_pending'] += $item['amount'];
        }
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching budget items: " . $e->getMessage();
    $budget_items = [];
    $totals = [
        'total_budget' => 0,
        'total_paid' => 0,
        'total_pending' => 0
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Budget Management - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <div class="max-w-7xl mx-auto mt-8 px-4">
    <!-- Page Header -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800">Budget Management</h1>
      <button id="openAddModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Add Budget Item
      </button>
    </div>

    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
      <div class="mb-4 p-4 bg-green-100 border border-green-200 text-green-800 rounded">
        <?php 
          echo $_SESSION['success'];
          unset($_SESSION['success']);
        ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
      <div class="mb-4 p-4 bg-red-100 border border-red-200 text-red-800 rounded">
        <?php 
          echo $_SESSION['error'];
          unset($_SESSION['error']);
        ?>
      </div>
    <?php endif; ?>

    <!-- Event Filter -->
    <div class="bg-white shadow rounded mb-6 p-4">
      <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4" data-ajax="false">
        <div>
          <label for="event_filter" class="block text-sm font-medium text-gray-700">Filter by Event</label>
          <select id="event_filter" name="event_id" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
            <option value="">All Events</option>
            <?php foreach ($events as $event): ?>
              <option value="<?php echo $event['event_id']; ?>" <?php echo $event_filter == $event['event_id'] ? 'selected' : ''; ?>>
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

    <!-- Budget Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
      <div class="bg-blue-600 text-white rounded shadow p-4">
        <h5 class="text-lg font-medium">Total Budget</h5>
        <p class="text-2xl font-bold mt-2">$<?php echo number_format($totals['total_budget'], 2); ?></p>
      </div>
      <div class="bg-green-600 text-white rounded shadow p-4">
        <h5 class="text-lg font-medium">Amount Paid</h5>
        <p class="text-2xl font-bold mt-2">$<?php echo number_format($totals['total_paid'], 2); ?></p>
      </div>
      <div class="bg-yellow-500 text-gray-900 rounded shadow p-4">
        <h5 class="text-lg font-medium">Pending Payments</h5>
        <p class="text-2xl font-bold mt-2">$<?php echo number_format($totals['total_pending'], 2); ?></p>
      </div>
    </div>

    <!-- Budget Items Table -->
    <div class="bg-white shadow rounded">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Event</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Category</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Description</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Amount</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Status</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <?php foreach ($budget_items as $item): ?>
              <tr>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($item['event_name']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($item['category']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                <td class="px-4 py-2 text-gray-700">$<?php echo number_format($item['amount'], 2); ?></td>
                <td class="px-4 py-2">
                  <?php 
                    $statusClasses = [
                      'Paid' => 'bg-green-100 text-green-800',
                      'Cancelled' => 'bg-red-100 text-red-800',
                      'Pending' => 'bg-yellow-100 text-yellow-800'
                    ];
                  ?>
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $statusClasses[$item['status']]; ?>">
                    <?php echo $item['status']; ?>
                  </span>
                </td>
                <td class="px-4 py-2">
                  <div class="flex space-x-2">
                    <button type="button" 
                            class="edit-budget bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded" 
                            data-item='<?php echo json_encode($item); ?>'
                            title="Edit Item">
                      <i class="fas fa-edit"></i>
                    </button>
                    <form method="POST" data-ajax="false" onsubmit="return confirm('Are you sure you want to delete this budget item?');">
                      <input type="hidden" name="item_id" value="<?php echo $item['item_id']; ?>">
                      <button type="submit" name="delete_item" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded" title="Delete Item">
                        <i class="fas fa-trash"></i>
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
  </div>

  <!-- Add Budget Item Modal -->
  <div id="addBudgetModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="px-4 py-3 border-b flex justify-between items-center">
        <h5 class="text-lg font-medium">Add Budget Item</h5>
        <button id="closeAddModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_budget.php" method="POST" data-ajax="false">
        <div class="p-4 space-y-4">
          <div>
            <label for="event_id" class="block text-sm font-medium text-gray-700">Event</label>
            <select id="event_id" name="event_id" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="">Select Event</option>
              <?php foreach ($events as $event): ?>
                <option value="<?php echo $event['event_id']; ?>">
                  <?php echo htmlspecialchars($event['event_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <input type="text" id="category" name="category" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="description" name="description" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
          <div>
            <label for="amount" class="block text-sm font-medium text-gray-700">Amount</label>
            <input type="number" id="amount" name="amount" step="0.01" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
        </div>
        <div class="px-4 py-3 border-t flex justify-end space-x-2">
          <button type="button" id="cancelAddModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Add Item
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Budget Item Modal -->
  <div id="editBudgetModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="px-4 py-3 border-b flex justify-between items-center">
        <h5 class="text-lg font-medium">Edit Budget Item</h5>
        <button id="closeEditModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/update_budget.php" method="POST" data-ajax="false">
        <input type="hidden" id="edit_item_id" name="item_id">
        <div class="p-4 space-y-4">
          <div>
            <label for="edit_event_id" class="block text-sm font-medium text-gray-700">Event</label>
            <select id="edit_event_id" name="event_id" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="">Select Event</option>
              <?php foreach ($events as $event): ?>
                <option value="<?php echo $event['event_id']; ?>">
                  <?php echo htmlspecialchars($event['event_name']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <div>
            <label for="edit_category" class="block text-sm font-medium text-gray-700">Category</label>
            <input type="text" id="edit_category" name="category" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_description" class="block text-sm font-medium text-gray-700">Description</label>
            <textarea id="edit_description" name="description" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
          <div>
            <label for="edit_amount" class="block text-sm font-medium text-gray-700">Amount</label>
            <input type="number" id="edit_amount" name="amount" step="0.01" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="edit_status" name="status" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="Pending">Pending</option>
              <option value="Paid">Paid</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="px-4 py-3 border-t flex justify-end space-x-2">
          <button type="button" id="cancelEditModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Update Item
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Modal toggling logic
    const openAddModalBtn = document.getElementById('openAddModal');
    const addBudgetModal = document.getElementById('addBudgetModal');
    const closeAddModalBtn = document.getElementById('closeAddModal');
    const cancelAddModalBtn = document.getElementById('cancelAddModal');

    openAddModalBtn.addEventListener('click', () => {
      addBudgetModal.classList.remove('hidden');
    });
    closeAddModalBtn.addEventListener('click', () => {
      addBudgetModal.classList.add('hidden');
    });
    cancelAddModalBtn.addEventListener('click', () => {
      addBudgetModal.classList.add('hidden');
    });

    // Edit Modal toggling
    const editBudgetModal = document.getElementById('editBudgetModal');
    const closeEditModalBtn = document.getElementById('closeEditModal');
    const cancelEditModalBtn = document.getElementById('cancelEditModal');

    closeEditModalBtn.addEventListener('click', () => {
      editBudgetModal.classList.add('hidden');
    });
    cancelEditModalBtn.addEventListener('click', () => {
      editBudgetModal.classList.add('hidden');
    });

    // Handle edit budget item button clicks
    document.querySelectorAll('.edit-budget').forEach(button => {
      button.addEventListener('click', function() {
        const item = JSON.parse(this.dataset.item);
        document.getElementById('edit_item_id').value = item.item_id;
        document.getElementById('edit_event_id').value = item.event_id;
        document.getElementById('edit_category').value = item.category;
        document.getElementById('edit_description').value = item.description || '';
        document.getElementById('edit_amount').value = item.amount;
        document.getElementById('edit_status').value = item.status;
        
        editBudgetModal.classList.remove('hidden');
      });
    });
  </script>
 
  <script src="assets/js/main.js"></script>
</body>
</html>
