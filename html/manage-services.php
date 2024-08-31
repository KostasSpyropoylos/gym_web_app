<?php
session_start();
// Check if the user session is set
if (!isset($_SESSION['user'])) {
  // If the user session is not set, redirect to the login page
  header('Location: login.php'); // Adjust the login page URL as needed
  exit();
}

$apiUrl = 'localhost:8080/GymWebService/rest/users';

$ch = curl_init($apiUrl);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
  echo 'Error: ' . curl_error($ch);
} else {
  // Decode the JSON response
  $users = json_decode($response, true);


  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $user_id = isset($_POST['user_id']) ? $_POST['user_id'] : null;
    $full_name = isset($_POST['full_name']) ? $_POST['full_name'] : null;
    $username = isset($_POST['username']) ? $_POST['username'] : null;
    $email = isset($_POST['email']) ? $_POST['email'] : null;
    $phone_number = isset($_POST['phone_number']) ? $_POST['phone_number'] : null;
    $role = isset($_POST['role']) ? $_POST['role'] : null;
    $status = isset($_POST['status']) ? $_POST['status'] : null;
    $bio = isset($_POST['bio']) ? $_POST['bio'] : null;
    $action_type = isset($_POST['action_type']) ? $_POST['action_type'] : 'update';

    // Check if user_id is provided
    if (!$user_id) {
      echo "User ID is required.";
      exit;
    }
    if ($action_type == 'update') {
      // API URL with the user ID
      $apiUrl = "http://localhost:8080/GymWebService/rest/users/updateUser/$user_id";

      // Prepare data for PUT request
      $data = [
        "userId" => $user_id,
        "userName" => $username,
        "email" => $email,
        "fullName" => $full_name,
        "phoneNumber" => $phone_number,
        "roleId" => (int)$role,
        "bio" => $bio,
        "createAt" => date('Y-m-d H:i:s'), // Adjust the date as needed or set this as a fixed value
        "isPending" => $status === 'true' ? true : false,
        "isActive" => $status === 'true' ? true : null
      ];

      // Initialize cURL
      $ch = curl_init($apiUrl);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen(json_encode($data))
      ]);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

      // Execute cURL request and capture the response
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      // Close cURL session
      curl_close($ch);

      // Handle the response
      if ($httpCode == 200) {
        echo "<script>
                alert('User updated successfully!');
                window.location.href = window.location.href;
              </script>";
      } else {
        echo "<script>
                alert('Failed to update user. HTTP Status Code: " . $httpCode . "');
                console.log('Response: " . $response . "');
              </script>";
      }
    } elseif ($action_type == 'remove') {
      // API URL with the user ID for removal
      $apiUrl = "localhost:8080/GymWebService/rest/users/$user_id";

      // Initialize cURL for DELETE request
      $ch = curl_init($apiUrl);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

      // Execute cURL request and capture the response
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

      // Close cURL session
      curl_close($ch);

      // Handle the response
      if ($httpCode == 204) {
        echo "<script>
                alert('User removed successfully!');
                window.location.href = window.location.href;
              </script>";
      } else {
        echo "<script>
                alert('Failed to remove user. HTTP Status Code: " . $httpCode . " " . $user_id . "');
                console.log('Response: " . $response . "');
              </script>";
      }
    } else {
      echo "Invalid action type.";
    }
  }
  if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Read the input data
    parse_str(file_get_contents("php://input"), $put_vars);
    $input = json_decode(file_get_contents("php://input"), true);

    // Check if required fields are set
    if (isset($input['userId']) && isset($input['isPending']) && isset($input['isActive'])) {
      $userId = intval($input['userId']);
      $isPending = intval($input['isPending']);
      $isActive = intval($input['isActive']);

      // Define the API URL
      $apiUrl = "http://localhost:8080/GymWebService/rest/users/$userId";

      // Initialize cURL
      $ch = curl_init($apiUrl);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
      curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
      ]);
      curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'userId' => $userId,
        'isPending' => $isPending,
        'isActive' => $isActive
      ]));

      // Execute the request
      $response = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      // Send response
      if ($httpCode == 200) {
        echo json_encode(['message' => 'User updated successfully']);
      } else {
        http_response_code($httpCode);
        echo json_encode(['error' => 'Failed to update user']);
      }
      exit();
    } else {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid input']);
      exit();
    }
  }
}


?>



<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="/css/global-styles.css" />
  <link rel="stylesheet" href="/css/manage-programs.css" />

  <title>Διαχείριση Χρηστών</title>
</head>

<body>
  <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>
  <div class="content">
    <div class="main-content">
      <div class="pending-users">
        <div class="manage-users">
          <h3>Διαχείριση Υπηρεσιών</h3>
          <h5 class="user-number">15 Συνολικά</h5>
        </div>
        <div class="new-user" onclick="openServiceModal()">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
            <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path
              fill="#ffffff"
              d="M256 80c0-17.7-14.3-32-32-32s-32 14.3-32 32l0 144L48 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l144 0 0 144c0 17.7 14.3 32 32 32s32-14.3 32-32l0-144 144 0c17.7 0 32-14.3 32-32s-14.3-32-32-32l-144 0 0-144z" />
          </svg>
          <span class="newUserText">Νέα Υπηρεσία</span>
        </div>
      </div>
      <div class="table-container">
        <table>
          <thead>
            <tr>
              <th>Όνομα Υπηρεσίας</th>
              <!-- Service Name -->
              <th>Περιγραφή</th>
              <!-- Description -->
              <th>Τιμή</th>
              <!-- Price -->
              <th>Διάρκεια</th>
              <!-- Duration -->
              <th>Κατηγορία</th>
              <!-- Category -->
              <th>Κατάσταση</th>
              <!-- Status -->
              <th>Εκπαιδευτής</th>
              <!-- Instructor -->
              <th></th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Προσωπική Εκπαίδευση</td>
              <td>Ατομικές συνεδρίες εκπαίδευσης</td>
              <td>50€ ανά ώρα</td>
              <td>60 λεπτά</td>
              <td>Δύναμη</td>
              <td>Ενεργή</td>
              <td>Γιάννης Παπαδόπουλος</td>
              <td>
                <span onclick="openModal()">
                  <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512">
                    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path
                      d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />
                  </svg>
                </span>
              </td>
            </tr>
            <tr>
              <td>Ομαδικά Μαθήματα</td>
              <td>Ομαδικά μαθήματα γυμναστικής</td>
              <td>15€ ανά μάθημα</td>
              <td>45 λεπτά</td>
              <td>Καρδιοαναπνευστική</td>
              <td>Ενεργή</td>
              <td>Μαρία Νικολάου</td>
              <td>
                <span onclick="openModal(this)">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 128 512">
                    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path
                      d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />
                  </svg>
                </span>
              </td>
            </tr>
            <tr>
              <td>Διατροφική Συμβουλευτική</td>
              <td>Προσωπικά προγράμματα διατροφής</td>
              <td>30€ ανά συνεδρία</td>
              <td>30 λεπτά</td>
              <td>Διατροφή</td>
              <td>Ανενεργή</td>
              <td>Ελένη Δεληγιάννη</td>
              <td>
                <span onclick="openModal(this)">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 128 512">
                    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path
                      d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />
                  </svg>
                </span>
              </td>
            </tr>
            <tr>
              <td>Πιλάτες</td>
              <td>Μαθήματα Πιλάτες για αρχάριους</td>
              <td>20€ ανά μάθημα</td>
              <td>50 λεπτά</td>
              <td>Ευλυγισία</td>
              <td>Ενεργή</td>
              <td>Νίκος Αλεξίου</td>
              <td>
                <span onclick="openModal(this)">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 128 512">
                    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path
                      d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />
                  </svg>
                </span>
              </td>
            </tr>
            <tr>
              <td>Yoga</td>
              <td>Μαθήματα γιόγκα για όλες τις ηλικίες</td>
              <td>25€ ανά μάθημα</td>
              <td>60 λεπτά</td>
              <td>Ευεξία</td>
              <td>Ενεργή</td>
              <td>Σοφία Κωνσταντίνου</td>
              <td>
                <span onclick="openModal(this)">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 128 512">
                    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path
                      d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />
                  </svg>
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- The New Service Modal -->
      <div id="newServiceModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close" onclick="closeModal('newServiceModal')">&times;</span>
          <div class="form-container">
            <form action="">
              <div class="item">

                <label for="name">Ονόματος Υπηρεσίας</label>
                <input />
              </div>
              <div class="item">
                <label for="name">Περιγραφής</label>
                <input />
              </div>
              <div class="item">
                <label for="name">Τιμής</label>
                <input />
              </div>
              <div class="item">
                <label for="name">Διάρκειας</label>
                <input />
              </div>
              <div class="item">
                <label for="name">Κατηγορίας</label>
                <input />
              </div>
              <div class="item">
                <label for="name">Κατάστασης</label>
                <input />
              </div>
              <div class="item">
                <label for="name">Εκπαιδευτή</label>
                <input />
              </div>
              <div class="submit">
                <input type="submit" class="danger" value="Ακύρωση">
                <input type="submit" class="success" value="Προσθήκη Υπηρεσίας">
              </div>
            </form>
          </div>
        </div>
      </div>

      <!-- The Detail Modal -->
      <div id="detailModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close" onclick="closeModal('detailModal')">&times;</span>
          <div class="form-container">
            <form action="">
              <div class="item">

                <label for="name"> Αλλαγή Ονόματος Υπηρεσίας</label>
                <input />
              </div>
              <div class="item">
                <label for="name"> Αλλαγή Περιγραφής</label>
                <input />
              </div>
              <div class="item">
                <label for="name"> Αλλαγή Τιμής</label>
                <input />
              </div>
              <div class="item">
                <label for="name"> Αλλαγή Διάρκειας</label>
                <input />
              </div>
              <div class="item">
                <label for="name"> Αλλαγή Κατηγορίας</label>
                <input />
              </div>
              <div class="item">
                <label for="name"> Αλλαγή Κατάστασης</label>
                <input />
              </div>
              <div class="item">
                <label for="name"> Αλλαγή Εκπαιδευτή</label>
                <input />
              </div>
              <div class="submit">
                <input type="submit" class="danger" value="Διαγραφή Υπηρεσίας">
                <input type="submit" class="success" value="Αποδοχή Αλλαγών">
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</body>
<script>
  // Open detail modal
  function openModal() {
    var modal = document.getElementById("detailModal");
    modal.style.display = "block";
  }
  // Open new service modal
  function openServiceModal() {
    var modal = document.getElementById("newServiceModal");
    modal.style.display = "block";
  }
  // Cloes modal
  function closeModal(modal) {
    var modal = document.getElementById(modal);
    modal.style.display = "none";
  }
</script>

</html>