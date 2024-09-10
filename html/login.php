<?php
session_start();
if (!isset($_SESSION['user'])) {
  // Check if the form is submitted
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve email and password from the form
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
    <form method="post">
      <label for="login_email">Email</label>
      <input type="email" id="login_email" name="login_email" required />

      <label for="login_password">Password</label>
      <input
        type="password"
        id="login_password"
        name="login_password"
        required />

      <button type="submit">Login</button>
      <p class="text-center">
        Don't have an account?
        <a href="#" onclick="showRegister()">Register</a>
      </p>
    </form>
  </div>
  <div class="container register-container">
    <h2>Register</h2>
    <form action="#">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required />

      <label for="email">Email</label>
      <input type="email" id="email" name="email" required />

      <label for="password">Password</label>
      <input type="password" id="password" name="password" required />

      <label for="confirm-password">Confirm Password</label>
      <input
        type="password"
        id="confirm-password"
        name="confirm-password"
        required />

      <button type="submit">Register</button>
      <p class="text-center">
        Already have an account? <a onclick="showLogin()">Login</a>
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