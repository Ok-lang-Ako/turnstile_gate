<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers to allow cross-origin requests
header('Access-Control-Allow-Origin: *');

// Database connection
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'qr_scanner';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get QR data from GET parameter
$qr_data = isset($_GET['qr_data']) ? $_GET['qr_data'] : '';
$direction = isset($_GET['direction']) ? $_GET['direction'] : '';
// Function to read and display log file
function displayLogFile() {
    global $conn;
    $sql = "SELECT l.*, a.name, a.id_number, a.photo 
            FROM scan_logs l 
            LEFT JOIN authorized_codes a ON l.qr_data = a.qr_hash 
            ORDER BY l.timestamp DESC LIMIT 50";
    $result = $conn->query($sql);
    
    $output = "";
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $output .= "<div class='log-entry'>";
            $output .= "<span class='timestamp'>[" . $row['timestamp'] . "]</span> ";
            $output .= "<span class='qr-data'>" . $row['qr_data'] . "</span> ";
            $output .= "<span class='status'>" . $row['status'] . "</span>";
            if ($row['name']) {
                $output .= " - " . $row['name'];
                if ($row['id_number']) {
                    $output .= " (ID: " . $row['id_number'] . ")";
                }
            }
            // Show image if photo exists
            if ($row['photo']) {
                $photo = $row['photo'];
                $photo = '/' . ltrim(str_replace('\\', '/', $photo), '/');
                $output .= "<div class='log-photo'><img src='" . $photo . "' alt='User Photo' onerror='this.style.display=\"none\"'></div>";
            }
            $output .= "</div>";
        }
    } else {
        $output = "No QR codes scanned yet.";
    }
    return $output;
}

// If it's a direct browser request (not from ESP32)
if (empty($_GET)) {
    ?>
<!DOCTYPE html>
<html>

<head>
    <title>Access Control Monitor</title>
    <link href="dist\css\bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-icons-1.11.3\font\bootstrap-icons.css">
</head>

<body class="bg-dark text-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card bg-dark border-success">
                    <div class="card-body text-center">
                        <?php
                            if (isset($_GET['photo']) && !empty($_GET['photo'])) {
                                $photo = '/' . ltrim(str_replace('\\', '/', $photo), '/');
                                echo '<img src="' . $photo . '" class="img-fluid rounded mb-3" style="max-height: 300px;" alt="Current Scan">';
                                if (isset($_GET['name']) && !empty($_GET['name'])) {
                                    echo '<h3 class="text-success mb-3">' . htmlspecialchars($_GET['name']) . '</h3>';
                                }
                                if (isset($_GET['status'])) {
                                    $statusClass = $_GET['status'] === 'Authorized' ? 'text-success' : 'text-danger';
                                    echo '<h4 class="' . $statusClass . ' mb-3">' . 
                                         ($_GET['status'] === 'Authorized' ? 'ACCESS GRANTED' : 'ACCESS DENIED') . '</h4>';
                                }
                            } else {
                                echo '<i class="bi bi-upc-scan display-1 text-success mb-3"></i>';
                                echo '<h2 class="text-success mb-3">WAITING FOR SCAN</h2>';
                                echo '<div class="d-flex align-items-center justify-content-center">
                                        <div class="spinner-grow text-success me-2" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                        <span>System Ready</span>
                                      </div>';
                            }
                            ?>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <a href="log_display.php" class="btn btn-outline-success me-2">
                        <i class="bi bi-list-ul me-2"></i>VIEW LOGS
                    </a>
                    <button class="btn btn-success" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>REFRESH
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="dist\js\bootstrap.bundle.min.js"></script>
    <script>
    let eventSource;
    let normalViewTimeout;

    function startEventSource() {
        if (eventSource) {
            eventSource.close();
        }

        eventSource = new EventSource('qr_receive.php?events=1');

        eventSource.onmessage = function(event) {
            const data = JSON.parse(event.data);
            if (data.type === 'scan') {
                handlePhotoDisplay(data.photo, data.name, data.status);
            }
        };

        eventSource.onerror = function() {
            eventSource.close();
            setTimeout(startEventSource, 1000);
        };
    }

    function handlePhotoDisplay(photoUrl, name, status) {
        if (normalViewTimeout) {
            clearTimeout(normalViewTimeout);
        }

        const cardBody = document.querySelector('.card-body');
        cardBody.innerHTML = `
                    <img src="${photoUrl}" class="img-fluid rounded mb-3" style="max-height: 300px;" alt="Current Scan">
                    ${name ? `<h3 class="text-success mb-3">${name}</h3>` : ''}
                    <h4 class="${status === 'Authorized' ? 'text-success' : 'text-danger'} mb-3">
                        ${status === 'Authorized' ? 'ACCESS GRANTED' : 'ACCESS DENIED'}
                    </h4>
                `;

        normalViewTimeout = setTimeout(function() {
            cardBody.innerHTML = `
                        <i class="bi bi-upc-scan display-1 text-success mb-3"></i>
                        <h2 class="text-success mb-3">WAITING FOR SCAN</h2>
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="spinner-grow text-success me-2" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <span>System Ready</span>
                        </div>
                    `;
        }, 10000);
    }

    startEventSource();

    <?php
            if (isset($_GET['photo']) && !empty($_GET['photo'])) {
                $name = isset($_GET['name']) ? $_GET['name'] : '';
                $status = isset($_GET['status']) ? $_GET['status'] : 'Unauthorized';
                $photo = isset($_GET['photo']) ? $_GET['photo'] : '';
                echo "handlePhotoDisplay('" . htmlspecialchars($photo) . "', '" . 
                     htmlspecialchars($name) . "', '" . htmlspecialchars($status) . "');";
            }
            ?>
    </script>
</body>

</html>
<?php
    exit;
}

// Handle Server-Sent Events
if (isset($_GET['events'])) {
    header('Content-Type: text/event-stream');
    header('Cache-Control: no-cache');
    header('Connection: keep-alive');
    
    // Keep the connection alive
    while (true) {
        // Check for new scans in the last 2 seconds
        $sql = "SELECT l.*, a.name, a.id_number, a.photo 
                FROM scan_logs l 
                LEFT JOIN authorized_codes a ON l.qr_data = a.qr_hash 
                WHERE l.timestamp >= DATE_SUB(NOW(), INTERVAL 2 SECOND)
                ORDER BY l.timestamp DESC LIMIT 1";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $photo = $row['photo'] ? str_replace('\\', '/', $row['photo']) : null;
            $photo = $photo ? str_replace('//', '/', $photo) : null;
            
            $data = [
                'type' => 'scan',
                'status' => $row['status'],
                'name' => $row['name'],
                'photo' => $photo
            ];
            
            echo "data: " . json_encode($data) . "\n\n";
            ob_flush();
            flush();
        }
        
        // Sleep for a short time to prevent excessive database queries
        sleep(1);
    }
   
}

// Handle direction check request
if (isset($_GET['check_direction']) && isset($_GET['qr_data'])) {
    $qr_data = htmlspecialchars($_GET['qr_data'], ENT_QUOTES, 'UTF-8');
    
    // Get the last recorded direction for this QR code
    $stmt = $conn->prepare("SELECT direction FROM scan_logs WHERE qr_data = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt->bind_param("s", $qr_data);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $last_direction = "OUT";  // Default to OUT if no previous record
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $last_direction = $row['direction'];
    }
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'last_direction' => $last_direction
    ]);
    exit;
}

// Handle ESP32 requests
if (!empty($qr_data)) {
    // Sanitize the input
    $qr_data = htmlspecialchars($_GET['qr_data'], ENT_QUOTES, 'UTF-8');
    $direction = isset($_GET['direction']) ? $_GET['direction'] : 'OUT';
    
    // Check if QR code exists in authorized list
    $stmt = $conn->prepare("SELECT name, id_number, photo FROM authorized_codes WHERE qr_hash = ?");
    $stmt->bind_param("s", $qr_data);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $status = "Unauthorized";
    $name = null;
    $photo = null;
    $id_number = null;
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $status = "Authorized";
        $name = $row['name'];
        $id_number = $row['id_number'];
        $photo = str_replace('\\', '/', $row['photo']);
        $photo = str_replace('//', '/', $photo);
        
        // Log the scan with direction only if authorized
        $stmt = $conn->prepare("INSERT INTO scan_logs (qr_data, status, direction) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $qr_data, $status, $direction);
        $stmt->execute();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'message' => 'QR code data received',
        'data' => $qr_data,
        'authorized' => ($status === "Authorized"),
        'name' => $name,
        'id_number' => $id_number,
        'photo' => $photo,
        'direction' => $direction
    ], JSON_UNESCAPED_SLASHES);
} else {
    // Send error response
    header('Content-Type: application/json');
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'No QR code data received'
    ]);
}

$conn->close();
?>
