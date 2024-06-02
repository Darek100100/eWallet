<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Konfiguracja połączenia z bazą danych
$servername = "fdb1032.awardspace.net";
$username = "4488312_czarny";
$password = "Haslo123";
$dbname = "4488312_czarny";

// Enable error logging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get user_id from session
    $user_id = $_SESSION['user_id'];
    // Get amount from POST request and escape it
    $amount = $conn->real_escape_string($_POST['amount']);

    // Validate amount (e.g., it should be a positive number)
    if ($amount > 0) {
        // Prepare and bind
        $stmt = $conn->prepare("INSERT INTO transfers2 (user_id, amount) VALUES (?, ?)");
        if ($stmt === false) {
            echo "Error preparing statement: " . $conn->error;
        } else {
            $stmt->bind_param("id", $user_id, $amount);

            // Execute statement
            if ($stmt->execute()) {
                echo "New transfer created successfully.";
            } else {
                echo "Error: " . $stmt->error;
            }

            // Close statement
            $stmt->close();
        }
    } else {
        echo "Error: Amount must be a positive number.";
    }
}

// Close connection
$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Transfer - eWallet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            margin: 0 auto;
            max-width: 600px;
        }
        h2 {
            text-align: center;
        }
        table {
            width: 100%;
            margin: 20px 0;
        }
        td {
            padding: 10px;
        }
        .form-container {
            text-align: center;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create Transfer</h2>
        <form action="create_transfer.php" method="POST">
            <table>
                <tr>
                    <td><label for="amount">Amount</label></td>
                    <td><input type="number" id="amount" name="amount" required min="0.01" step="0.01"></td>
                </tr>
                <tr>
                    <td colspan="2" class="form-container"><button type="submit">Create Transfer</button></td>
                </tr>
            </table>
        </form>
    </div>
</body>
</html>
