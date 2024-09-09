<?php
session_start();
// Function to fetch schedules by serviceId from the API

if (!isset($_SESSION['user'])) {
    // If the user session is not set, redirect to the login page
    header('Location: login.php'); // Adjust the login page URL as needed
    exit();
}

function fetchSchedulesByServiceId($serviceId)
{
    // Your API endpoint, replace with the actual API URL
    $apiUrl = "localhost:8080/GymWebService/rest/schedules/schedule/" . $serviceId;

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); // Set the appropriate content type

    // Execute the request and get the response
    $response = curl_exec($ch);

    // Check if there were any errors
    if (curl_errno($ch)) {
        echo 'cURL Error: ' . curl_error($ch);
        return []; // Return an empty array in case of error
    }

    // Close the cURL session
    curl_close($ch);

    // Decode the JSON response to a PHP array
    $schedules = json_decode($response, true);

    // Check if the response is valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'JSON Error: ' . json_last_error_msg();
        return []; // Return an empty array in case of JSON error
    }

    return $schedules;
}

// Get serviceId from the query parameter (e.g., from a URL like service_schedule.php?service_id=1)
$serviceId = isset($_GET['service_id']) ? intval($_GET['service_id']) : 0;

// Fetch the schedules based on the serviceId
$schedules = fetchSchedulesByServiceId($serviceId);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/css/index.css" />
    <link rel="stylesheet" href="/css/global-styles.css" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />

    <title>Λεπτομέρειες Προγραμμάτος</title>
    <style>
        .container {
            width: 100%;
            margin: 0 auto;
            padding: 20px 0;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
        }

        .schedule-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .schedule-card:hover {
            transform: translateY(-5px);
        }

        .schedule-card h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
            color: #333;
        }

        .schedule-card p {
            font-size: 1rem;
            margin-bottom: 10px;
            color: #666;
        }

        .schedule-card .label {
            font-weight: bold;
            color: #333;
        }

        .service-details,
        .trainer-details {
            margin-top: 20px;
            padding: 15px;
            background-color: #f9f9f9;
            border-radius: 8px;
        }

        .service-details h3,
        .trainer-details h3 {
            margin-top: 0;
        }

        .table-header {
            margin-top: 30px;
        }

        @media(max-width: 768px) {
            .schedule-card {
                padding: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>
    
    <div class="content">
        <div class="main-content">
            <div class="container">
                <h1>Λεπτομέρειες Προγράμματος για <?php echo htmlspecialchars($schedules[0]['service']['serviceName']); ?></h1>

                <?php if (!empty($schedules)): ?>
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="schedule-card">
                            <h2>Πρόγραμμα για <?php echo htmlspecialchars($schedule['service']['serviceName']); ?></h2>
                            <p class="label">Περιγραφή:</p>
                            <p><?php echo htmlspecialchars($schedule['service']['description']); ?></p>

                            <div class="service-details">
                                <h3>Λεπτομέρειες Υπηρεσίας:</h3>
                                <p><span class="label">Τιμή:</span> €<?php echo htmlspecialchars($schedule['service']['price']); ?></p>
                                <p><span class="label">Διάρκεια:</span> <?php echo $schedule['service']['duration'] > 0 ? htmlspecialchars($schedule['service']['duration']) . ' λεπτά' : 'Δεν υπάρχει συγκεκριμένη διάρκεια'; ?></p>
                                <p><span class="label">Κατηγορία:</span> <?php echo htmlspecialchars($schedule['service']['category'] ?? 'Χωρίς κατηγορία'); ?></p>
                            </div>

                            <div class="trainer-details">
                                <h3>Λεπτομέρειες Εκπαιδευτή:</h3>
                                <p><span class="label">Εκπαιδευτής:</span> <?php echo htmlspecialchars($schedule['user']['fullName']); ?></p>
                            </div>

                            <div class="schedule-details">
                                <h3>Πληροφορίες Προγράμματος:</h3>
                                <p><span class="label">Ημέρα:</span> <?php echo htmlspecialchars($schedule['dayOfWeek']); ?></p>
                                <p><span class="label">Ώρα Έναρξης:</span> <?php echo htmlspecialchars($schedule['startTime']); ?></p>
                                <p><span class="label">Ώρα Λήξης:</span> <?php echo htmlspecialchars($schedule['endTime']); ?></p>
                                <p><span class="label">Μέγιστη Χωρητικότητα:</span> <?php echo htmlspecialchars($schedule['maxCapacity']); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Δεν βρέθηκαν προγράμματα για αυτή την υπηρεσία.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>