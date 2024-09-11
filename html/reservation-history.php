<?php
session_start();
// Function to fetch schedules by serviceId from the API

if (!isset($_SESSION['user'])) {
    // If the user session is not set, redirect to the login page
    header('Location: login.php'); // Adjust the login page URL as needed
    exit();
}
function getReservationByUserId($userId)
{
    $apiUrl = "localhost:8080/GymWebService/rest/reservations/user/$userId"; 

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the content type

    // Execute cURL request
    $response = curl_exec($ch);

    // Check if there are any cURL errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        return [];
    }

    // Close cURL session
    curl_close($ch);

    // Decode the JSON response to a PHP array
    $reservations = json_decode($response, true);

    // Return the reservation data or empty array if JSON decoding fails
    return json_last_error() === JSON_ERROR_NONE ? $reservations : [];
}

// Fetch reservations for a specific user ID
$userId = $_SESSION['userId'];
$reservations = getReservationByUserId($userId);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservations</title>
    <link rel="stylesheet" href="/css/global-styles.css" />
    <link rel="stylesheet" href="/css/manage-users.css" />
    <style>
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>

    <div class="content">
        <div class="main-content">
            <h1>Reservation Details</h1>

            <?php if (!empty($reservations)): ?>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Service Name</th>
                                <th>Day of Week</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservations as $reservation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($reservation['status']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['schedule']['service']['serviceName']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['schedule']['dayOfWeek']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['schedule']['startTime']); ?></td>
                                    <td><?php echo htmlspecialchars($reservation['schedule']['endTime']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No reservations found for this user.</p>
                <?php endif; ?>
                </div>
        </div>
    </div>

</body>

</html>