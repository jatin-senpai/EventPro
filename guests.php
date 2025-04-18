<?php
session_start();
require_once 'config/database.php';

// Getting currently logged-in user id
$current_user_id = $_SESSION['user_id'];
// Handle guest deletion
if (isset($_POST['delete_guest'])) {
    $guest_id = $_POST['guest_id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM guests WHERE guest_id = ?");
        $stmt->execute([$guest_id]);
        $_SESSION['success'] = "Guest deleted successfully";
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error deleting guest: " . $e->getMessage();
    }
    header("Location: guests.php");
    exit();
}
// Fetching only events belonging to the current user for the filter
try {
    $stmt = $pdo->prepare("SELECT event_id, event_name FROM events WHERE user_id = ? ORDER BY event_date DESC");
    $stmt->execute([$current_user_id]);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching events: " . $e->getMessage();
    $events = [];
}
// Fetching guests with optional event filter AND ensure events belong to current user
$event_filter = isset($_GET['event_id']) ? $_GET['event_id'] : null;
try {
    $query = "
        SELECT g.*, e.event_name 
        FROM guests g 
        JOIN events e ON g.event_id = e.event_id
        WHERE e.user_id = ?
    ";
    $params = [$current_user_id];

    if ($event_filter) {
        $query .= " AND g.event_id = ?";
        $params[] = $event_filter;
    }

    $query .= " ORDER BY g.name ASC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $guests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching guests: " . $e->getMessage();
    $guests = [];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Guest Management - EventPro</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body class="font-inter bg-gray-50">
  <?php include 'includes/navbar.php'; ?>

  <div class="max-w-7xl mx-auto mt-8 px-4">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
      <h1 class="text-3xl font-bold text-gray-800 mb-4 md:mb-0">Guest Management</h1>
      <button id="openAddGuestModal" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
        <i class="fas fa-plus mr-2"></i> Add New Guest
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

    <!-- Guest List Table -->
    <div class="bg-white shadow rounded">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-100">
            <tr>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Name</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Event</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Email</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Phone</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">RSVP Status</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Number of Guests</th>
              <th class="px-4 py-2 text-left text-sm font-semibold text-gray-600">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <?php foreach ($guests as $guest): ?>
              <tr>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($guest['name']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($guest['event_name']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($guest['email']); ?></td>
                <td class="px-4 py-2 text-gray-700"><?php echo htmlspecialchars($guest['phone']); ?></td>
                <td class="px-4 py-2">
                  <?php 
                    $rsvpClasses = [
                      'Confirmed' => 'bg-green-500',
                      'Declined'  => 'bg-red-500',
                      'Pending'   => 'bg-yellow-500'
                    ];
                  ?>
                  <span class="px-2 py-1 inline-block rounded text-white <?php echo $rsvpClasses[$guest['rsvp_status']]; ?>">
                    <?php echo $guest['rsvp_status']; ?>
                  </span>
                </td>
                <td class="px-4 py-2 text-gray-700"><?php echo $guest['number_of_guests']; ?></td>
                <td class="px-4 py-2">
                  <div class="flex space-x-2">
                    <button type="button" class="edit-guest bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded" 
                            data-guest='<?php echo json_encode($guest); ?>'
                            title="Edit Guest">
                      <i class="fas fa-edit"></i>
                    </button>
                    <!-- After -->
                    <form method="POST" class="inline" data-ajax="false" onsubmit="return confirm('Are you sure you want to delete this guest?');">
                      <input type="hidden" name="guest_id" value="<?php echo $guest['guest_id']; ?>">
                      <button type="submit" name="delete_guest" class="bg-red-600 hover:bg-red-700 text-white p-2 rounded" title="Delete Guest">
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

  <!-- Add Guest Modal -->
  <div id="addGuestModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Add New Guest</h5>
        <button id="closeAddGuestModal" class="text-gray-600 hover:text-gray-800">&times;</button>
      </div>
      <form action="handlers/add_guest.php" method="POST" novalidate data-ajax="false">
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
            <label for="name" class="block text-sm font-medium text-gray-700">Guest Name</label>
            <input type="text" id="name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
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
            <label for="number_of_guests" class="block text-sm font-medium text-gray-700">Number of Guests</label>
            <input type="number" id="number_of_guests" name="number_of_guests" value="1" min="1" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="dietary_restrictions" class="block text-sm font-medium text-gray-700">Dietary Restrictions</label>
            <textarea id="dietary_restrictions" name="dietary_restrictions" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelAddGuestModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Add Guest
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Guest Modal -->
  <div id="editGuestModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg max-h-[80vh] overflow-y-auto">
      <div class="flex justify-between items-center px-4 py-3 border-b">
        <h5 class="text-lg font-medium text-gray-800">Edit Guest</h5>
        <button type="button" id="closeEditGuestModal" class="text-gray-600 hover:text-gray-800 text-2xl">&times;</button>
      </div>
      <form action="handlers/update_guest.php" method="POST" data-ajax="false">
        <input type="hidden" id="edit_guest_id" name="guest_id">
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
            <label for="edit_name" class="block text-sm font-medium text-gray-700">Guest Name</label>
            <input type="text" id="edit_name" name="name" required class="mt-1 block w-full border border-gray-300 rounded-md p-2">
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
            <label for="edit_number_of_guests" class="block text-sm font-medium text-gray-700">Number of Guests</label>
            <input type="number" id="edit_number_of_guests" name="number_of_guests" min="1" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
          </div>
          <div>
            <label for="edit_dietary_restrictions" class="block text-sm font-medium text-gray-700">Dietary Restrictions</label>
            <textarea id="edit_dietary_restrictions" name="dietary_restrictions" rows="2" class="mt-1 block w-full border border-gray-300 rounded-md p-2"></textarea>
          </div>
          <div>
            <label for="edit_rsvp_status" class="block text-sm font-medium text-gray-700">RSVP Status</label>
            <select id="edit_rsvp_status" name="rsvp_status" class="mt-1 block w-full border border-gray-300 rounded-md p-2">
              <option value="Pending">Pending</option>
              <option value="Confirmed">Confirmed</option>
              <option value="Declined">Declined</option>
            </select>
          </div>
        </div>
        <div class="flex justify-end items-center px-4 py-3 border-t space-x-2">
          <button type="button" id="cancelEditGuestModal" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
            Update Guest
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    // Toggle Add Guest Modal
    const openAddGuestModalBtn = document.getElementById('openAddGuestModal');
    const addGuestModal = document.getElementById('addGuestModal');
    const closeAddGuestModalBtn = document.getElementById('closeAddGuestModal');
    const cancelAddGuestModalBtn = document.getElementById('cancelAddGuestModal');

    openAddGuestModalBtn.addEventListener('click', () => {
      addGuestModal.classList.remove('hidden');
    });
    closeAddGuestModalBtn.addEventListener('click', () => {
      addGuestModal.classList.add('hidden');
    });
    cancelAddGuestModalBtn.addEventListener('click', () => {
      addGuestModal.classList.add('hidden');
    });

    document.addEventListener('DOMContentLoaded', () => {
        const editGuestModal = document.getElementById('editGuestModal');
        const closeEditGuestModalBtn = document.getElementById('closeEditGuestModal');
        const cancelEditGuestModalBtn = document.getElementById('cancelEditGuestModal');

        // Edit button click handler
        document.querySelectorAll('.edit-guest').forEach(button => {
            button.addEventListener('click', function() {
                const guest = JSON.parse(this.dataset.guest);
                document.getElementById('edit_guest_id').value = guest.guest_id;
                document.getElementById('edit_event_id').value = guest.event_id;
                document.getElementById('edit_name').value = guest.name;
                document.getElementById('edit_email').value = guest.email || '';
                document.getElementById('edit_phone').value = guest.phone || '';
                document.getElementById('edit_number_of_guests').value = guest.number_of_guests;
                document.getElementById('edit_dietary_restrictions').value = guest.dietary_restrictions || '';
                document.getElementById('edit_rsvp_status').value = guest.rsvp_status;
                editGuestModal.classList.remove('hidden');
            });
        });

        // Close handlers
        closeEditGuestModalBtn.addEventListener('click', () => {
            editGuestModal.classList.add('hidden');
        });

        cancelEditGuestModalBtn.addEventListener('click', () => {
            editGuestModal.classList.add('hidden');
        });
    });
  </script>
  <script src="assets/js/main.js"></script>
</body>
</html>
