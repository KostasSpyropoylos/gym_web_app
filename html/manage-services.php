<?php
session_start();
// Check if the user session is set
if (!isset($_SESSION['user'])) {
  // If the user session is not set, redirect to the login page
  header('Location: login.php'); // Adjust the login page URL as needed
  exit();
}

$apiUrl = 'localhost:8080/GymWebService/rest/services';

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
  $services = json_decode($response, true);

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Collect form data
    $action_type = isset($_POST['action_type']) ? $_POST['action_type'] : null;

    // Check if action_type is provided
    $service_id = isset($_POST['serviceId']) ? $_POST['serviceId'] : null;
    if ($service_id !== null) {
      if ($action_type === 'update') {
        // Variables for updating a service
        $service_name = isset($_POST['service_name']) ? $_POST['service_name'] : null;
        $description = isset($_POST['description']) ? $_POST['description'] : null;
        $price = isset($_POST['price']) ? $_POST['price'] : null;
        $duration = isset($_POST['duration']) ? $_POST['duration'] : null;
        $category = isset($_POST['category']) ? $_POST['category'] : null;

        // Check if service_id is provided
        // if ($service_id !== null) {
        // API URL for updating a service
        $apiUrl = "http://localhost:8080/GymWebService/rest/services/updateService/$service_id";

        // Prepare data for PUT request
        $data = json_encode([
          "serviceName" => $service_name,
          "description" => $description,
          "price" => $price,
          "duration" => $duration,
          "category" => $category,
        ]);

        // Initialize cURL for PUT request
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data)
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // Execute cURL request and capture the response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL session
        curl_close($ch);

        // Handle the response
        if ($httpCode == 200) {
          echo "<script>
                  alert('Service updated successfully!');
                  window.location.href = window.location.href; // Reload the current page
                </script>";
        } else {
          echo "<script>
                  alert('Failed to update service. HTTP Status Code: " . htmlspecialchars($httpCode, ENT_QUOTES) . "');
                  console.log('Response: " . htmlspecialchars($response, ENT_QUOTES) . "');
                </script>";
        }
      } else {
        echo "<script>
              alert('Service ID is missing. $action_type') 
              ;
            </script>";
        // }
      }

      if ($action_type === 'remove') {
        // Variables for removing a service

        // if ($service_id !== null) {
        // Properly formatted API URL with the Service ID for removal
        $apiUrl = "http://localhost:8080/GymWebService/rest/services/$service_id";

        // Initialize cURL for DELETE request
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL request and capture the response
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Check for cURL errors
        if ($response === false) {
          $curlError = curl_error($ch);
          curl_close($ch);
          echo "<script>
                    alert('cURL Error: " . htmlspecialchars($curlError, ENT_QUOTES) . "');
                  </script>";
        } else {
          // Close cURL session
          curl_close($ch);

          // Handle the response
          if ($httpCode == 204) {
            echo "<script>
          window.location.href = window.location.href; // Reload the current page
          alert('Service removed successfully!');
                      </script>";
          } else {
            echo "<script>
          console.log('Response: " . htmlspecialchars($response, ENT_QUOTES) . "');
          alert('Failed to remove service. HTTP Status Code: " . htmlspecialchars($httpCode, ENT_QUOTES) . "');
                      </script>";
          }
          // }
        }
      } else {
        echo "<script>
                alert('Service ID is missing. $action_type');
              </script>" ;
      }
    } else {
      if ($action_type === 'create') {
        // Variables for creating a service
        $service_name = isset($_POST['name']) ? $_POST['name'] : null;
        $description = isset($_POST['desc']) ? $_POST['desc'] : null;
        $price = isset($_POST['servicePrice']) ? $_POST['servicePrice'] : null;
        $duration = isset($_POST['serviceDuration']) ? $_POST['serviceDuration'] : null;
        $category = isset($_POST['serviceCategory']) ? $_POST['serviceCategory'] : null;

        // Prepare the POST data
        $data = json_encode([
          "serviceName" => $service_name,
          "description" => $description,
          "price" => $price,
          "duration" => $duration,
          "category" => $category,
        ]);

        // API URL for creating a new service
        $apiUrl = 'http://localhost:8080/GymWebService/rest/services/new'; // Adjust the endpoint as needed

        // Initialize cURL for POST request
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Content-Length: ' . strlen($data)
        ]);

        // Execute cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL resource
        curl_close($ch);

        // Check the response
        if ($httpCode == 201) { // HTTP Status 201 Created
          echo "<script>
                alert('Service created successfully!');
                window.location.href = 'manage-services.php'; // Redirect to the manage services page
              </script>";
        } else {
          echo "<script>
                alert('Failed to create service. HTTP Status Code: " . htmlspecialchars($httpCode, ENT_QUOTES) . "');
                console.log('Response: " . htmlspecialchars($response, ENT_QUOTES) . "');
              </script>";
        }
      }
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
      <?php

      if ($services) {
        echo '<div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Όνομα Υπηρεσίας</th>
                            <th>Περιγραφή</th>
                            <th>Τιμή</th>
                            <th>Διάρκεια</th>
                            <th>Κατηγορία</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>';

        // Loop through the users array and generate table rows
        foreach ($services as $service) {

          $serviceDataJson = htmlspecialchars(json_encode($service), ENT_QUOTES, 'UTF-8');
          echo '<tr>';
          echo '<td>' . htmlspecialchars($service['serviceName']) . '</td>';
          echo '<td>' . htmlspecialchars($service['description']) . '</td>';
          echo '<td>' . $service['price'] . '</td>';
          echo '<td>' . $service['duration'] . '</td>';
          echo '<td>' . $service['category'] . '</td>';
          echo '<td>';
          echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 512" onclick="openModalWithServiceData(' . $serviceDataJson . ', \'detailModal\')">';
          echo '    <!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->';
          echo '    <path d="M64 360a56 56 0 1 0 0 112 56 56 0 1 0 0-112zm0-160a56 56 0 1 0 0 112 56 56 0 1 0 0-112zM120 96A56 56 0 1 0 8 96a56 56 0 1 0 112 0z" />';
          echo '</svg>';
          echo '</td>';
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

      <!-- The New Service Modal -->
      <div id="newServiceModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
          <span class="close" onclick="closeModal('newServiceModal')">&times;</span>
          <div class="form-container">
            <form action="" method="post">
              <input type="hidden" id="action_type" name="action_type" value="create" />
              <div class="item">

                <label for="name">Όνομα Υπηρεσίας</label>
                <input id="name" name="name" type="text" />
              </div>
              <div class="item">
                <label for="desc">Περιγραφή</label>
                <input id="desc" name="desc" type="text" />
              </div>
              <div class="item">
                <label for="servicePrice">Τιμή</label>
                <input id="servicePrice" name="servicePrice" type="text" />
              </div>
              <div class="item">
                <label for="serviceDuration">Διάρκεια</label>
                <input id="serviceDuration" name="serviceDuration" type="text" />
              </div>
              <div class="item">
                <label for="serviceCategory">Κατηγορία</label>
                <input id="serviceCategory" name="serviceCategory" type="text" />
              </div>
              <div class="submit">
                <input type="button" class="danger" value="Απόρριψη Αλλαγών" onclick="closeModal('newServiceModal')" />
                <input type="submit" class="success" value="Δημιουργία Υπηρεσίας" onclick="document.getElementById('action_type').value='create';" />
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
            <form action="" method="post">
              <!-- Hidden User ID Field -->
              <input type="hidden" name="serviceId" value="" /> <!-- Set this dynamically as needed -->

              <input type="hidden" id="action_type" name="action_type" value="delete" />
              <div class="item">

                <label for="serviceName"> Αλλαγή Ονόματος Υπηρεσίας</label>
                <input id="serviceName" name="serviceName" type="text" />
              </div>
              <div class="item">
                <label for="description"> Αλλαγή Περιγραφής</label>
                <input id="description" name="description" type="text" />
              </div>
              <div class="item">
                <label for="price"> Αλλαγή Τιμής</label>
                <input id="price" name="price" type="text" />
              </div>
              <div class="item">
                <label for="duration"> Αλλαγή Διάρκειας</label>
                <input id="duration" name="duration" type="text" />
              </div>
              <div class="item">
                <label for="category"> Αλλαγή Κατηγορίας</label>
                <input id="category" name="category" type="text" />
              </div>
              <div class="submit">
                <input type="submit" class="danger" value="Διαγραφή Υπηρεσίας" onclick="document.getElementById('action_type').value='remove';" />
                <input type="submit" class="success" value="Αποδοχή Αλλαγών" onclick="document.getElementById('action_type').value='update';" />
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>
</body>
<script>
  function openServiceModal() {
    document.getElementById("newServiceModal").style.display = 'block';
  }
  // Open new service modal
  function openModalWithServiceData(data, modalName) {
    data = {
      data
    };
    console.log("data", data)

    // Fill modal input fields with user data
    if (modalName == "detailModal") {
      document.querySelector('#detailModal input[name="serviceId"]').value = data.data.serviceId;
      document.querySelector('#detailModal input[name="serviceName"]').value = data.data.serviceName;
      document.querySelector('#detailModal input[name="description"]').value = data.data.description;
      document.querySelector('#detailModal input[name="price"]').value = data.data.price;
      document.querySelector('#detailModal input[name="duration"]').value = data.data.duration;
      document.querySelector('#detailModal input[name="category"]').value = data.data.category;

    }

    // Open the modal
    document.getElementById(modalName).style.display = 'block';
  }
  // Cloes modal
  function closeModal(modal) {
    var modal = document.getElementById(modal);
    modal.style.display = "none";
  }
</script>

</html>