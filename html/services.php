<?php
// Function to fetch services from the API
session_start();


function fetchServicesFromAPI()
{
    $apiUrl = "localhost:8080/GymWebService/rest/services"; 

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
    $services = json_decode($response, true);

    // Check if the response is a valid JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'JSON Error: ' . json_last_error_msg();
        return []; // Return an empty array in case of JSON error
    }

    return $services;
}

// Fetch the services
$services = fetchServicesFromAPI();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="/css/index.css" />
    <link rel="stylesheet" href="/css/global-styles.css" />
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <title>WEGOJIM</title>
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>
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

        /* Grid layout */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 20px;
        }

        /* Card styling */
        .card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            padding: 20px;
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h2 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #333;
        }

        .card p {
            font-size: 1rem;
            margin-bottom: 15px;
            color: #666;
        }

        .card .price {
            font-size: 1.2rem;
            color: #007BFF;
            margin-bottom: 10px;
        }

        .card .duration,
        .card .category {
            font-size: 1rem;
            color: #333;
            margin-bottom: 10px;
        }

        /* Responsive styling */
        @media (max-width: 1024px) {
            .grid-container {
                grid-template-columns: 1fr 1fr;
                grid-gap: 10px;
            }
        }

        @media (max-width: 768px) {
            .grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="sidebar"><?php require '../shared/sidebar.php'; ?></div>
    
    <div class="content">
        <div class="main-content">
            <div class="container">
                <h1>Οι υπηρεσίες</h1>

                <div class="grid-container">
                    <?php if (!empty($services)): ?>
                        <?php foreach ($services as $service): ?>
                            <div class="card">
                                <h2><?php echo htmlspecialchars($service['serviceName']); ?></h2>
                                <p><?php echo htmlspecialchars($service['description']); ?></p>
                                <div class="price">Τιμή: <?php echo htmlspecialchars($service['price']); ?>€</div>
                                <div class="duration">Διάρκεια: <?php echo htmlspecialchars($service['duration']); ?> λεπτά</div>
                                <div class="category">Κατηγορία: <?php echo htmlspecialchars($service['category']); ?></div>
                                <!-- Dynamic URL based on service_id -->
                                <a href="service-details.php?service_id=<?php echo htmlspecialchars($service['serviceId']); ?>">Δες περισσότερα</a>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>Δεν βρέθηκαν υπηρεσίες.</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</body>

</html>