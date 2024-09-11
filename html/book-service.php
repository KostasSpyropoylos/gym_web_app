<?php
session_start(); // Start the session to access session variables

// Function to fetch schedules by serviceId from the API

if (!isset($_SESSION['user'])) {
    // If the user session is not set, redirect to the login page
    header('Location: login.php'); // Adjust the login page URL as needed
    exit(); // Ensure the script stops executing after redirection
} else {

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Debugging: Check the entire POST data
        // print_r($_POST); // Uncomment this to see the POST data for debugging purposes

        // Check if the dropdown (servicesDropdown) has been submitted and is not empty
        if (!empty($_POST['servicesDropdown'])) {
            // Capture the service ID from the form submission
            $selectedServiceId = $_POST['servicesDropdown'];
            $userId = $_SESSION['userId']; // Fetch userId from session

            // Debugging: Output the selected values
            // echo "Selected Service ID: $selectedServiceId, User ID: $userId"; // Useful for checking inputs

            // Prepare data for API call
            $data = [
                'user' => [
                    'userId' => $userId
                ],
                'schedule' => [
                    'scheduleId' => $selectedServiceId
                ],
                'status' => 'Booked' // Setting status to 'Booked'
            ];

            // Convert data array to JSON
            $jsonData = json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            // Initialize cURL session
            $ch = curl_init();

            // Set cURL options for the POST request
            curl_setopt($ch, CURLOPT_URL, 'http://localhost:8080/GymWebService/rest/reservations/new'); // API endpoint
            curl_setopt($ch, CURLOPT_POST, 1); // Specify this is a POST request
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData); // Attach the JSON payload
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json', // Setting the content type to JSON
                'Content-Length: ' . strlen($jsonData) // Set the content length
            ]);

            // Execute the request and capture the response
            $response = curl_exec($ch);

            // Check for cURL errors
            if (curl_errno($ch)) {
                echo 'Error: ' . curl_error($ch); // Print cURL error message
            } else {
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get the HTTP response code
                // Check if the request was successful (HTTP code 200-299)
                if ($httpCode >= 200 && $httpCode < 300) {
                    echo 'Response from API: ' . $response; // Display the API response
                } else {
                    echo "Failed to book. HTTP Code: $httpCode"; // Handle non-2xx HTTP codes
                }
            }

            // Close cURL session
            curl_close($ch);
        } else {
            echo "No service selected."; // Message when no option is chosen from the dropdown
        }
    }
}

function fetchSchedulesFromAPI()
{
   
    $apiUrl = "localhost:8080/GymWebService/rest/schedules"; 

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl); // API URL to fetch schedules
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the appropriate content type

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check if there were any errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch); // Print cURL error
        return []; // Return an empty array in case of error
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response to a PHP array
    $schedules = json_decode($response, true);

    // Check if the response is a valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'JSON Error: ' . json_last_error_msg(); // Print JSON error message
        return []; // Return an empty array in case of JSON error
    }

    return $schedules; // Return the array of schedules
}

// Fetch the schedules
$schedules = fetchSchedulesFromAPI(); // Populate the $schedules array with the API response
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WEGOJIM</title>
    <link rel="stylesheet" href="/css/global-styles.css" />
    <link rel="stylesheet" href="/css/manage-users.css" />
</head>

<body>
    <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>

    <div class="content">
        <div class="main-content">
            <div class="container">
                <h3>Διενέργεια Κράτησης</h3>

                <div class="grid-container">
                    <?php if (!empty($schedules)): ?>
                        <form method="POST">
                            <label>Διαλέξτε υπηρεσία</label>
                            <select name="servicesDropdown">
                                <option value="">Select a service</option>
                                <?php
                                // Loop through the schedules and create option elements
                                foreach ($schedules as $schedule) {
                                    echo '<option value="' . $schedule['scheduleId'] . '">' . $schedule['service']['serviceName'] . '</option>';
                                }
                                ?>
                            </select>

                            <div class="submit">
                                <input type="submit" class="success" value="Κλείσε Κράτηση" />
                            </div>
                        </form>
                    <?php else: ?>
                        <p>Δεν βρέθηκαν υπηρεσίες.</p>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </div>

</body>

</html>