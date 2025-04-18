<?php
session_start();
require_once 'config/database.php';  
if (!isset($_SESSION['user_id'])) {
    header("Location: signin.php");
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_vendor'])) {
    try {
        $stmt = $pdo->prepare("
            SELECT ev.event_id 
            FROM event_vendors ev 
            JOIN events e ON ev.event_id = e.event_id 
            WHERE ev.vendor_id = ? AND e.user_id = ?
        ");
        $stmt->execute([$_POST['vendor_id'], $_SESSION['user_id']]);
        if (!$stmt->fetch()) {
            $_SESSION['error'] = "You don't have permission to delete this vendor.";
            header("Location: vendors.php");
            exit();
        }
        $stmt = $pdo->prepare("DELETE FROM vendors WHERE vendor_id = ?");
        $stmt->execute([$_POST['vendor_id']]);
        $_SESSION['success'] = "Vendor deleted successfully.";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error deleting vendor: " . $e->getMessage();
    }
    header("Location: vendors.php");
    exit();
}
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE user_id = ? ORDER BY event_date DESC");
    $stmt->execute([$_SESSION['user_id']]);
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching events: " . $e->getMessage();
}
try {
    $sql = "
        SELECT v.*, e.event_name, e.event_id, ev.status as event_status, ev.notes as event_notes
        FROM vendors v
        JOIN event_vendors ev ON v.vendor_id = ev.vendor_id
        JOIN events e ON ev.event_id = e.event_id
        WHERE e.user_id = ?
    ";
    $params = [$_SESSION['user_id']];
    if (!empty($_GET['event_id'])) {
        $sql .= " AND e.event_id = ?";
        $params[] = $_GET['event_id'];
    }
    $sql .= " ORDER BY v.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $vendors = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Error fetching vendors: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Vendors - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet" />
  <link href="assets/css/style.css" rel="stylesheet" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>
  <div class="max-w-7xl mx-auto mt-8 px-4">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-8">
      <h1 class="text-3xl font-bold text-gray-800">Vendors</h1>
      <button id="openAddVendorModal" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
        <i class="fas fa-plus mr-2"></i> Add Vendor
      </button>
    </div>
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

    <!-- Vendors Table -->
    <div class="bg-white shadow rounded overflow-x-auto">
      <table class="min-w-full">
        <thead class="bg-gray-100">
          <tr>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Name</th>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Category</th>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Contact Person</th>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Contact Info</th>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Event</th>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Status</th>
            <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <?php foreach ($vendors as $vendor): ?>
            <tr>
              <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($vendor['name']); ?></td>
              <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($vendor['category']); ?></td>
              <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($vendor['contact_person']); ?></td>
              <td class="px-4 py-2 text-gray-700">
                <?php if ($vendor['email']): ?>
                  <div><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($vendor['email']); ?></div>
                <?php endif; ?>
                <?php if ($vendor['phone']): ?>
                  <div><i class="fas fa-phone mr-1"></i><?php echo htmlspecialchars($vendor['phone']); ?></div>
                <?php endif; ?>
              </td>
              <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($vendor['event_name']); ?></td>
              <td class="px-4 py-2">
                <?php 
                  $statusClasses = [
                    'Confirmed' => 'bg-green-500',
                    'Pending'   => 'bg-yellow-500',
                    'Cancelled' => 'bg-red-500'
                  ];
                ?>
                <span class="px-2 py-1 inline-block rounded text-white <?php echo $statusClasses[$vendor['event_status']]; ?>">
                  <?php echo htmlspecialchars($vendor['event_status']); ?>
                </span>
              </td>
              <td class="px-4 py-2">
                <div class="flex space-x-2">
                  <button type="button" class="edit-vendor inline-flex items-center bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-medium py-1 px-3 rounded"
                          data-vendor='<?php echo json_encode($vendor); ?>'>
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>
                  <form method="POST" class="inline" data-ajax="false" onsubmit="return confirm('Are you sure you want to delete this vendor?');">
                    <input type="hidden" name="vendor_id" value="<?php echo $vendor['vendor_id']; ?>">
                    <button type="submit" name="delete_vendor" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white text-sm font-medium py-1 px-3 rounded">
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

  <!-- Add Vendor Modal -->
  <div id="addVendorModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Add Vendor</h5>
        <button id="closeAddVendorModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_vendor.php" method="POST" data-ajax="false">
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
            <label for="name" class="block text-sm font-medium text-gray-700">Vendor Name</label>
            <input type="text" id="name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
            <input type="text" id="category" name="category" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
            <input type="text" id="contact_person" name="contact_person" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="tel" id="phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelAddVendorModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Add Vendor
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Vendor Modal -->
  <div id="editVendorModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Edit Vendor</h5>
        <button id="closeEditVendorModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/update_vendor.php" method="POST" data-ajax="false">
        <input type="hidden" name="vendor_id" id="edit_vendor_id">
        <input type="hidden" name="event_id" id="edit_event_id">
        <div class="p-4 space-y-4">
          <div>
            <label for="edit_name" class="block text-sm font-medium text-gray-700">Vendor Name</label>
            <input type="text" id="edit_name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_category" class="block text-sm font-medium text-gray-700">Category</label>
            <input type="text" id="edit_category" name="category" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_contact_person" class="block text-sm font-medium text-gray-700">Contact Person</label>
            <input type="text" id="edit_contact_person" name="contact_person" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_email" class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" id="edit_email" name="email" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_phone" class="block text-sm font-medium text-gray-700">Phone</label>
            <input type="tel" id="edit_phone" name="phone" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_notes" class="block text-sm font-medium text-gray-700">Notes</label>
            <textarea id="edit_notes" name="notes" rows="3" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
          <div>
            <label for="edit_status" class="block text-sm font-medium text-gray-700">Status</label>
            <select id="edit_status" name="status" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="Pending">Pending</option>
              <option value="Confirmed">Confirmed</option>
              <option value="Cancelled">Cancelled</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditVendorModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Update Vendor
          </button>
        </div>
      </form>
    </div>
  </div>
  
  <script>
    // Toggle Add Vendor Modal
    const openAddVendorModalBtn = document.getElementById('openAddVendorModal');
    const addVendorModal = document.getElementById('addVendorModal');
    const closeAddVendorModalBtn = document.getElementById('closeAddVendorModal');
    const cancelAddVendorModalBtn = document.getElementById('cancelAddVendorModal');
    openAddVendorModalBtn.addEventListener('click', () => {
      addVendorModal.classList.remove('hidden');
    });
    closeAddVendorModalBtn.addEventListener('click', () => {
      addVendorModal.classList.add('hidden');
    });
    cancelAddVendorModalBtn.addEventListener('click', () => {
      addVendorModal.classList.add('hidden');
    });

    // Toggle Edit Vendor Modal
    const editVendorModal = document.getElementById('editVendorModal');
    const closeEditVendorModalBtn = document.getElementById('closeEditVendorModal');
    const cancelEditVendorModalBtn = document.getElementById('cancelEditVendorModal');
    closeEditVendorModalBtn.addEventListener('click', () => {
      editVendorModal.classList.add('hidden');
    });
    cancelEditVendorModalBtn.addEventListener('click', () => {
      editVendorModal.classList.add('hidden');
    });

    // Pre-fill Edit Vendor Modal on edit button click
    document.querySelectorAll('.edit-vendor').forEach(button => {
      button.addEventListener('click', function() {
        const vendor = JSON.parse(this.dataset.vendor);
        document.getElementById('edit_vendor_id').value = vendor.vendor_id;
        document.getElementById('edit_event_id').value = vendor.event_id;
        document.getElementById('edit_name').value = vendor.name;
        document.getElementById('edit_category').value = vendor.category;
        document.getElementById('edit_contact_person').value = vendor.contact_person;
        document.getElementById('edit_email').value = vendor.email || '';
        document.getElementById('edit_phone').value = vendor.phone || '';
        document.getElementById('edit_notes').value = vendor.event_notes || '';
        document.getElementById('edit_status').value = vendor.event_status;
        editVendorModal.classList.remove('hidden');
      });
    });
  </script>
  <script src="assets/js/main.js"></script>
</body>
</html>
