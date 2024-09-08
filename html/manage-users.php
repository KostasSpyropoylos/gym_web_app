<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}
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
  <link rel="stylesheet" href="/css/manage-users.css" />

  <title>Διαχείριση Χρηστών</title>
</head>

<body>
  <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>
  <div class="content">
    <div class="main-content">
      <div class="pending-users">
        <div class="manage-users">
          <h3>Διαχείριση Χρηστών</h3>
          <h5 class="user-number">1500 Συνολικά</h5>
        </div>
        <div class="new-user" onclick="openModal('createUserModal')">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
            <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
            <path
              fill="#ffffff"
              d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304l91.4 0C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7L29.7 512C13.3 512 0 498.7 0 482.3zM504 312l0-64-64 0c-13.3 0-24-10.7-24-24s10.7-24 24-24l64 0 0-64c0-13.3 10.7-24 24-24s24 10.7 24 24l0 64 64 0c13.3 0 24 10.7 24 24s-10.7 24-24 24l-64 0 0 64c0 13.3-10.7 24-24 24s-24-10.7-24-24z" />
          </svg>
          <span class="newUserText">Νέος Χρήστης</span>
        </div>
      </div>
      <?php
      if (is_array($users)) {

        echo '<div class="card-container">';
        foreach ($users as $user) {
          // Filter users
          if ($user['isPending'] == true && $user['isActive'] === NULL) {
            $userDataJson = htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8');
            // Generate HTML for each user
            echo '    <div class="card">';
            echo '        <div class="icon">';
            echo '            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">';
            echo '                <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->';
            echo '                <path d="M96 128a128 128 0 1 0 256 0A128 128 0 1 0 96 128zm94.5 200.2l18.6 31L175.8 483.1l-36-146.9c-2-8.1-9.8-13.4-17.9-11.3C51.9 342.4 0 405.8 0 481.3c0 17 13.8 30.7 30.7 30.7l131.7 0c0 0 0 0 .1 0l5.5 0 112 0 5.5 0c0 0 0 0 .1 0l131.7 0c17 0 30.7-13.8 30.7-30.7c0-75.5-51.9-138.9-121.9-156.4c-8.1-2-15.9 3.3-17.9 11.3l-36 146.9L238.9 359.2l18.6-31c6.4-10.7-1.3-24.2-13.7-24.2L224 304l-19.7 0c-12.4 0-20.1 13.6-13.7 24.2z" />';
            echo '            </svg>';
            echo '        </div>';
            echo '        <div class="statistics">';
            echo '            <span class="label">' . htmlspecialchars($user['fullName']) . '</span>';
            echo '            <span class="number">' . htmlspecialchars($user['userName']) . '</span>';
            echo '            <div class="btn-row">';
            echo '                <button style="background-color: #ff4136" onclick="handleReject(' . $userDataJson . ')">Απόρριψη</button>';
            echo '                <button style="background-color: #00db00" onclick="handleAccept(' . $user['userId'] . ')">Αποδοχή</button>';
            echo '            </div>';
            echo '        </div>';
            echo '        <div class="toggle-details" onclick=\'openModalWithUserData(' . $userDataJson . ', "newUserModal")\'>';
            echo '            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512">';
            echo '                <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->';
            echo '                <path d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />';
            echo '            </svg>';
            echo '        </div>';
            echo '    </div>';
          }
        }
        echo '</div>';
      }  ?>


      <?php

      if ($users) {
        echo '<div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Όνομα</th>
                            <th>Email</th>
                            <th>Ρόλος</th>
                            <th>Ημ.Εγγραφής</th>
                            <th>Είδος Συνδρομής</th>
                            <th>Κατάσταση</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';

        // Loop through the users array and generate table rows
        foreach ($users as $user) {
          $role = ($user['roleId'] == 1) ? 'Γυμναστής' : (($user['roleId'] == 2) ? 'Αθλητής' : 'Admin');
          $status = ($user['isActive'] ? "Ενεργός" : "Ανενεργός");
          if ($user['isActive'] !== Null) {
            $subscriptionType = ($user['roleId'] == 1) ? 'Admin' : 'Pilates'; // Example for subscription, replace with actual logic
            $userDataJson = htmlspecialchars(json_encode($user), ENT_QUOTES, 'UTF-8');
            echo '<tr>';
            echo '<td>' . htmlspecialchars($user['fullName']) . '</td>';
            echo '<td>' . htmlspecialchars($user['email']) . '</td>';
            echo '<td>' . $role . '</td>';
            echo '<td>' . htmlspecialchars(date('d/m/Y', strtotime($user['createAt']))) . '</td>';
            echo '<td>' . $subscriptionType . '</td>';
            echo '<td>' . $status . '</td>';
            echo '<td>';
            echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512" onclick="openModalWithUserData(' . $userDataJson . ', \'detailModal\')">';
            echo '    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->';
            echo '    <path d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />';
            echo '</svg>';
            echo '</td>';
          }
        }

        echo '  </tbody>
              </table>
            </div>';
      } else {
        echo 'No users found.';
      }

      // // Close cURL session
      curl_close($ch);
      ?>

      <!-- The Create User Modal -->
      <div id="createUserModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close" onclick="closeModal('createUserModal')">&times;</span>
          <div class="form-container">
            <form action="/php/create-user.php" method="post">
              <!-- Hidden action type field for new user creation -->
              <input type="hidden" name="action_type" value="create_user" />

              <!-- Full Name -->
              <div class="item">
                <label for="full_name">Ονοματεπώνυμο</label>
                <input type="text" name="full_name" required />
              </div>

              <!-- Username -->
              <div class="item">
                <label for="user_name">Όνομα Χρήστη</label>
                <input type="text" name="user_name" required />
              </div>

              <!-- Password -->
              <div class="item">
                <label for="password">Κωδικός</label>
                <input type="password" name="password" />
              </div>

              <!-- Email -->
              <div class="item">
                <label for="email">Email</label>
                <input type="email" name="email" required />
              </div>

              <!-- Phone Number -->
              <div class="item">
                <label for="phone_number">Τηλέφωνο</label>
                <input type="text" name="phone_number" />
              </div>

              <!-- Role -->
              <div class="item">
                <label for="role">Ρόλος</label>
                <select name="role" required>
                  <option value="1">Admin</option>
                  <option value="2">Αθλητής</option>
                </select>
              </div>

              <!-- Status -->
              <div class="item">
                <label for="status">Κατάσταση</label>
                <select name="status" required>
                  <option value="true">Ενεργό</option>
                  <option value="false">Ανενεργό</option>
                </select>
              </div>

              <!-- Bio -->
              <div class="item">
                <label for="bio">Περιγραφή</label>
                <textarea name="bio"></textarea>
              </div>

              <div class="submit">
                <input type="button" class="danger" value="Ακύρωση" onclick="closeModal('createUserModal')" />
                <input type="submit" class="success" value="Προσθήκη Χρήστη" />
              </div>
            </form>
          </div>
        </div>
      </div>
      <!-- The New User Modal -->
      <div id="newUserModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close" onclick="closeModal('newUserModal')">&times;</span>
          <div class="form-container">

            <!-- Full Name -->
            <div class="item">
              <label for="fullName">Όνομα/Επώνυμο</label>
              <input disabled type="text" name="fullName" />
            </div>

            <!-- Username -->
            <div class="item">
              <label for="username">Όνομα Χρήστη</label>
              <input disabled type="text" name="username" />
            </div>

            <!-- Email -->
            <div class="item">
              <label for="email">Email</label>
              <input disabled type="email" name="email" />
            </div>

            <!-- Phone Number -->
            <div class="item">
              <label for="phoneNumber">Τηλέφωνο</label>
              <input disabled type="text" name="phoneNumber" />
            </div>

            <!-- Role -->
            <div class="item">
              <label for="role">Ρόλος</label>
              <input disabled type="text" name="role" />
            </div>

            <!-- Bio -->
            <div class="item">
              <label for="bio">Περιγραφή</label>
              <input disabled type="text" name="bio" />
            </div>
          </div>
        </div>
      </div>

      <!-- The Detail Modal -->
      <div id="detailModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close" onclick="closeModal('detailModal')">&times;</span>
          <div class="form-container">
            <form action="" method="post">
              <!-- Hidden User ID Field -->
              <input type="hidden" name="user_id" value="" /> <!-- Set this dynamically as needed -->

              <input type="hidden" id="action_type" name="action_type" value="update" />

              <!-- Full Name -->
              <div class="item">
                <label for="full_name">Αλλαγή Ονόματος Χρήστη</label>
                <input type="text" name="full_name" />
              </div>

              <!-- Username -->
              <div class="item">
                <label for="username">Αλλαγή Όνομα Χρήστη</label>
                <input type="text" name="username" />
              </div>

              <!-- Email -->
              <div class="item">
                <label for="email">Αλλαγή Email</label>
                <input type="email" name="email" />
              </div>

              <!-- Phone Number -->
              <div class="item">
                <label for="phone_number">Αλλαγή Τηλεφώνου</label>
                <input type="text" name="phone_number" />
              </div>

              <!-- Role -->
              <div class="item">
                <label for="role">Αλλαγή Ρόλου</label>
                <select name="role">
                  <option value="1">Γυμναστής</option>
                  <option value="2">Αθλητής</option>
                  <option value="3">Admin</option>
                </select>
              </div>

              <!-- Status -->
              <div class="item">
                <label for="status">Αλλαγή Κατάστασης</label>
                <select name="status">
                  <option value="true">Ενεργό</option>
                  <option value="false">Ανενεργό</option>
                </select>
              </div>

              <!-- Bio -->
              <div class="item">
                <label for="bio">Αλλαγή Περιγραφής</label>
                <textarea name="bio"></textarea>
              </div>

              <div class="submit">
                <input type="submit" class="danger" value="Διαγραφή Χρήστη" onclick="document.getElementById('action_type').value='remove';" />
                <input type="submit" class="success" value="Αποδοχή Αλλαγών" onclick="document.getElementById('action_type').value='update';" />
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
  <script>
    // Open detail modal
    function openModal(modal) {
      var modal = document.getElementById(modal);
      modal.style.display = "block";
    }
    // Cloes modal
    function closeModal(modal) {
      var modal = document.getElementById(modal);
      modal.style.display = "none";
    }

    function openModalWithUserData(user, modalName) {
      // Parse the user data from JSON if it's a strin

      console.log(modalName)
      user = {
        user
      };
      console.log("user", user)

      // Fill modal input fields with user data
      if (modalName == "detailModal") {
        document.querySelector('#detailModal input[name="user_id"]').value = user.user.userId || '';
        document.querySelector('#detailModal input[name="full_name"]').value = user.user.fullName || '';
        document.querySelector('#detailModal input[name="username"]').value = user.user.userName || '';
        document.querySelector('#detailModal input[name="email"]').value = user.user.email || '';
        document.querySelector('#detailModal input[name="phone_number"]').value = user.user.phoneNumber || '';
        document.querySelector('#detailModal select[name="role"]').value = user.user.roleId; // Assuming roleId is 1 for Admin and 2 for Athlete
        document.querySelector('#detailModal select[name="status"]').value = user.user.isActive ? true : false;
        document.querySelector('#detailModal textarea[name="bio"]').value = user.user.bio || '';
      } else {
        console.log("user", user.user.userId)

        // document.querySelector('#newUserModal input[name="user_id"]').value = user?.user?.userId ;
        document.querySelector('#newUserModal input[name="fullName"]').value = user.user.fullName;
        document.querySelector('#newUserModal input[name="username"]').value = user.user.userName || '';
        document.querySelector('#newUserModal input[name="email"]').value = user.user.email || '';
        document.querySelector('#newUserModal input[name="phoneNumber"]').value = user.user.phoneNumber || '';
        document.querySelector('#newUserModal input[name="role"]').value = user.user.roleId == 1 ? "Admin" : "Athlete"; // Assuming roleId is 1 for Admin and 2 for Athlete
        // document.querySelector('#newUserModal select[name="status"]').value = user.user.isActive ? true : false;
        document.querySelector('#newUserModal input[name="bio"]').value = user.user.bio || '';
      }

      // Open the modal
      document.getElementById(modalName).style.display = 'block';
    }

    function handleReject(user) {
      console.log("user", user)
      console.log("im here dog")
      const url = 'manage-users.php'; // URL to the same PHP file
      const data = {
        userId: user.userId,
        isPending: 0,
        isActive: 0
      };

      fetch(url, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data)
        })
        .then(response => console.log(response))
        .then(result => {
          console.log('User updated successfully', result);
          // Optionally, refresh the page or update the UI to reflect the changes
          location.reload(); // Reload the page to reflect changes
        })
        .catch(error => {
          console.error('Error:', error);
          // Handle error accordingly
        });
    }
  </script>
</body>

</html>