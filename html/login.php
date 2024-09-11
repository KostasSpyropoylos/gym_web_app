<?php
session_start(); // Start session to handle user session data
if (!isset($_SESSION['user'])) { // Check if user session is not set (user not logged in)

  if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Ensure the request is a POST request
    // Check which form was submitted (login or registration)
    if (isset($_POST['form_type'])) {
      if ($_POST['form_type'] === 'login') { // Handle login form submission
        $email = $_POST['login_email'];
        $password = $_POST['login_password'];

        // Prepare the data for the API request
        $postData = array(
          'email' => $email,
          'password' => $password
        );

        // Convert data to JSON format
        $jsonData = json_encode($postData);

        // API endpoint URL for login
        $apiUrl = 'localhost:8080/GymWebService/rest/users'; 

        // Initialize cURL session
        $ch = curl_init($apiUrl);

        // Set cURL options for a POST request with JSON data
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set content type to JSON
        curl_setopt($ch, CURLOPT_POST, true); // Specify this is a POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Attach the JSON payload

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
          echo 'Error: ' . curl_error($ch); // Display cURL error if present
        } else {
          // Decode the JSON response
          $responseData = json_decode($response, true);

          // Check the response status
          if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) { // Success if HTTP status is 200
            // Login successful, set session variables
            session_start(); // Start a new session
            $_SESSION['user'] = $responseData; // Store user data in session
            $_SESSION['userId'] = $responseData['userId']; // Store user ID
            $_SESSION['userRole'] = $responseData['roleId']; // Store user role

            header('Location: ./index.php'); // Redirect to home page after successful login
            exit;
          } else {
            // Login failed
            echo $responseData;
            echo 'Invalid email or password. Please try again.'; // Error message on login failure
          }
        }

        // Close cURL session after login request
        curl_close($ch);
      } elseif ($_POST['form_type'] === 'register') { // Handle registration form submission
        // Registration form was submitted
        $username = $_POST['username'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $confirmPassword = $_POST['confirm-password'];

        // Ensure password and confirm password match
        if ($password !== $confirmPassword) {
          echo "Passwords do not match!";
          exit();
        }
        $currentDateTime = date('Y-m-d H:i:s'); // Get current date and time

        // Prepare data for the registration API call
        $data = [
          'userName' => $username,             // Username provided by the user
          'fullName' => $username,             // Full name (can be adjusted to capture a full name separately)
          'email' => $email,                   // User's email
          'password' => $password,             // User's password
          'createAt' => $currentDateTime,      // Timestamp of registration
          'roleId' => 1,                       // Default role for normal users
          'phoneNumber' => "",                 // Empty phone number for now
          'bio' => "",                         // Empty bio for now
          'isPending' => 1,                    // Pending approval
          'isActive' => null                   // Initially inactive
        ];

        // Convert the data to JSON format
        $jsonData = json_encode($data);

        // Initialize cURL for the registration API call
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/GymWebService/rest/users/new'); // API endpoint for new user registration
        curl_setopt($ch, CURLOPT_POST, 1); // Specify this is a POST request
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Attach the JSON payload
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return response as a string
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Content-Length: ' . strlen($jsonData) // Set content length
        ]);

        // Execute the registration request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
          echo 'cURL Error: ' . curl_error($ch); // Display cURL error if present
        } else {
          // Get the HTTP status code of the response
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

          // Output detailed response for debugging
          echo "<pre>HTTP Code: $httpCode</pre>";
          echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";

          if ($httpCode == 200) {
            echo "Registration successful!"; // Registration success message
          } else {
            echo "Registration failed. HTTP Code: " . $httpCode; // Error message if registration fails
          }
        }

        // Close the cURL session after registration request
        curl_close($ch);
      }
    }
  }
} else {
  header('Location: ./index.php'); // Redirect to home page if user is already logged in
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <link rel="stylesheet" href="../css/login.css" />
  <link rel="stylesheet" href="../css/global-styles.css" />
</head>

<body>
  <div class="container login-container">
    <h2>Login</h2>
    <form method="POST">
      <input type="hidden" name="form_type" value="login" /> <!-- Hidden input to identify the login form -->
      <label for="login_email">Email</label>
      <input type="email" id="login_email" name="login_email" required />

      <label for="login_password">Password</label>
      <input type="password" id="login_password" name="login_password" required />

      <button type="submit">Login</button>
      <p class="text-center">
        Don't have an account?
        <a href="#" onclick="showRegister()">Register</a>
      </p>
    </form>
  </div>

  <!-- Registration Form -->
  <div class="container register-container" style="display: none;"> <!-- Hidden by default, shown via JS -->
    <h2>Register</h2>
    <form method="POST">
      <input type="hidden" name="form_type" value="register" /> <!-- Hidden input to identify the registration form -->
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <label for="confirm-password">Confirm Password</label>
      <input type="password" id="confirm-password" name="confirm-password" required />

      <button type="submit">Register</button>
      <p class="text-center">
        Already have an account? <a href="#" onclick="showLogin()">Login</a>
      </p>
    </form>
  </div>

  <script>
    function showRegister() {
      document.querySelector(".login-container").style.display = "none";
      document.querySelector(".register-container").style.display = "block";
    }

    function showLogin() {
      document.querySelector(".login-container").style.display = "block";
      document.querySelector(".register-container").style.display = "none";
    }
  </script>
</body>

</html>