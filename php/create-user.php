<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $action_type = $_POST['action_type'];

    if ($action_type == 'create_user') {
        $fullName = $_POST['full_name'];
        $userName = $_POST['user_name'];
        $password = $_POST['password'];
        $email = $_POST['email'];
        $phoneNumber = $_POST['phone_number'];
        $roleId = $_POST['role'];
        $bio = $_POST['bio'];
        $createAt = date('Y-m-d H:i:s');
        // Prepare the POST data
        $postData = json_encode([
            'fullName' => $fullName,
            'userName' => $userName,
            'password' => $password,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'roleId' => (int)$roleId,
            'bio' => $bio,
            'createAt'=>$createAt ,
            'isPending' => true,
            // 'isActive' => null,
        ]);
        echo $postData;

        // API URL
        $apiUrl = 'http://localhost:8080/GymWebService/rest/users/new'; // Change this if necessary

        // Initialize cURL
        $ch = curl_init($apiUrl);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($postData)
        ]);

        // Execute cURL request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Close cURL resource
        curl_close($ch);

        // Check the response
        if ($httpCode == 201) { // HTTP Status 201 Created
            header('Location: /html/manage-users.php');
            exit();
        } else {
            echo "Failed to create user. HTTP Status Code: " . $httpCode;
            echo "<br>Response: " . htmlspecialchars($response);
        }
    }
} else {
    echo "Invalid request method.";
}