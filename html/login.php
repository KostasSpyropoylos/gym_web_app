<?php
session_start();
if (!isset($_SESSION['user'])) {


  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check which form was submitted (login or registration)
    if (isset($_POST['form_type'])) {
      if ($_POST['form_type'] === 'login') {
        $email = $_POST['login_email'];
        $password = $_POST['login_password'];

        // Prepare the data for the API request
        $postData = array(
          'email' => $email,
          'password' => $password
        );

        // Convert data to JSON format
        $jsonData = json_encode($postData);

        // API endpoint URL
        $apiUrl = 'localhost:8080/GymWebService/rest/users'; // Replace with your actual API URL

        // Initialize cURL session
        $ch = curl_init($apiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
          echo 'Error: ' . curl_error($ch);
        } else {
          // Decode the JSON response
          $responseData = json_decode($response, true);

          // Check the response status
          if (curl_getinfo($ch, CURLINFO_HTTP_CODE) == 200) {
            // Login successful
            // Here you can set session variables or cookies as needed
            session_start();
            $_SESSION['user'] = $responseData; // Store the user data in session
            $_SESSION['userId'] = $responseData['userId'];

            $_SESSION['userRole'] = $responseData['roleId'];

            header('Location: ./index.php'); // Redirect to home page after successful login
            exit;
          } else {
            // Login failed
            echo $responseData;
            echo 'Invalid email or password. Please try again.';
          }
        }

        // Close cURL session
        curl_close($ch);
      }elseif ($_POST['form_type'] === 'register') {
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
        $currentDateTime = date('Y-m-d H:i:s');
  
        // Prepare data for the registration API call
        $data = [
          'userName' => $username,             // The username provided by the user
          'fullName' => $username,             // You may change this to capture a full name instead of username
          'email' => $email,                   // Email address
          'password' => $password,             // User's password
          'createAt' => $currentDateTime,      // Current datetime when the registration is created
          'roleId' => 1,                       // Role ID, assuming 1 is the default for normal users
          'phoneNumber' => "",                 // Empty phone number for now, can be added later
          'bio' => "",                         // Empty bio for now, can be added later
          'isPending' => 1,                    // Assuming the account is pending approval (set to 1)
          'isActive' => null                   // The account is inactive initially (null)
        ];
  
        // Convert the data to JSON
        $jsonData = json_encode($data);
  
        // Initialize cURL for registration API call
        // Initialize cURL for the registration API call
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/GymWebService/rest/users/new'); // Ensure this endpoint is correct
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
          'Content-Type: application/json',
          'Content-Length: ' . strlen($jsonData)
        ]);
  
        // Execute the registration request
        $response = curl_exec($ch);
  
        // Check for cURL errors
        if (curl_errno($ch)) {
          echo 'cURL Error: ' . curl_error($ch);
        } else {
          // Get the HTTP status code of the response
          $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
          // Output detailed response for debugging
          echo "<pre>HTTP Code: $httpCode</pre>";
          echo "<pre>Response: " . htmlspecialchars($response) . "</pre>";
  
          if ($httpCode == 200) {
            echo "Registration successful!";
          } else {
            echo "Registration failed. HTTP Code: " . $httpCode;
          }
        }
  
        // Close the cURL session
      }
    } 
    curl_close($ch);
  }
} else {
  header('Location: ./index.php'); // Redirect to home page after successful login
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