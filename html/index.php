<?php
session_start(); // Start the session to access session variables

// Check if userRole is set in session; default to 1 if not. (0 means no role or default role)
$user_role = isset($_SESSION['userRole']) ? $_SESSION['userRole'] : 1;  

$apiUrl = "localhost:8080/GymWebService/rest/announcements"; // API endpoint for fetching announcements
$ch = curl_init($apiUrl); // Initialize cURL session with the API URL

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the API response as a string
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set content type as JSON

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
  echo 'Error: ' . curl_error($ch); // Display cURL error if present
  return []; // Return empty array in case of error
} else {
  // Decode the JSON response to a PHP array
  $announcements = json_decode($response, true);
}

// Function to format timestamp for display
function formatTimestamp($timestamp)
{
  $date = new DateTime($timestamp); // Create a DateTime object from the timestamp
  // Format it to a readable format like "August 28, 2024, 9:10 PM"
  return $date->format('F j, Y, g:i A');
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/css/index.css" />
  <link rel="stylesheet" href="/css/global-styles.css" />
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
  <title>WEGOJIM</title>
  <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
</head>

<body>
  <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>

  <div class="content">
    <div class="main-content">
      <?php if ($user_role == 3): ?>
        <div class="container">
          <h1>Admin Announcements</h1>

          <!-- Announcement Form -->
          <div class="announcement-form">
            <h2>Create New Announcement</h2>
            <form action="process_announcement.php" method="POST">
              <div class="item">
                <label for="title">Τίτλος:</label>
                <input type="text" id="title" name="title" required>
              </div>
              <div class="item">
                <label for="content">Περιεχόμενο:</label>
                <textarea id="content" name="content" rows="5" required></textarea>
              </div>
              <div class="submit">
                <input type="submit" class="success" value="Προσθήκη Υπηρεσίας" />
              </div>
            </form>
          </div>


        </div>
        <!-- Announcement List -->
        <div class="table-container">
          <table class="announcement-table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Content</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php if (!empty($announcements)): ?>
                <?php foreach ($announcements as $announcement): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($announcement['title']); ?></td>
                    <td><?php echo htmlspecialchars($announcement['content']); ?></td>
                    <td><?php echo formatTimestamp($announcement['createdAt']); ?></td>
                    <td class="btn-row">
                      <button class="edit-btn">Edit</button>
                      <button class="delete-btn">Delete</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="5">No announcements found.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
      <?php if (($user_role == 1 || $user_role == 2) || !isset($user)): ?>
        <div class="container">
          <h1>Ανακοινώσεις</h1>

          <div class="grid-container">
            <?php if (!empty($announcements)): ?>

              <?php foreach ($announcements as $announcement): ?>
                <div class="card">
                  <h2><?php echo htmlspecialchars($announcement['title']); ?></h2>
                  <p><?php echo htmlspecialchars($announcement['content']); ?></p>
                  <div class="date">Created: <?php echo formatTimestamp($announcement['createdAt']); ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <p>No announcements available at the moment.</p>
            <?php endif; ?>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </div>
</body>

</html>