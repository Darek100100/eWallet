<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$accountBalance = 1000;
$transferBalance = 0;

// Handle transfer creation if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form type to determine if it's a transfer or withdrawal
    $formType = $_POST['form_type'];

    // Get amount from POST request and escape it
    $amount = $conn->real_escape_string($_POST['amount']);

    // Validate amount (e.g., it should be a positive number)
    if ($amount > 0) {
        if ($formType == 'transfer') {
            // Handle new transfer
            $stmt = $conn->prepare("INSERT INTO transfers2 (user_id, amount) VALUES (?, ?)");
        } elseif ($formType == 'withdraw') {
            // Handle withdraw money (negative amount)
            $stmt = $conn->prepare("INSERT INTO transfers2 (user_id, amount) VALUES (?, ?)");
            $amount = -$amount; // Make the amount negative for withdrawal
        }

        if ($stmt === false) {
            echo '<p>Error preparing statement: ' . $conn->error . '</p>';
        } else {
            $stmt->bind_param("id", $userId, $amount);

            // Execute statement
            if ($stmt->execute()) {
                echo '<p>Transaction completed successfully.</p>';
                echo '<p><a href="index.php">Return to Home</a></p>';
            } else {
                echo '<p>Error executing statement: ' . $stmt->error . '</p>';
            }

            // Close statement
            $stmt->close();
        }
    } else {
        echo '<p>Error: Amount must be a positive number.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>eWallet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .navbar {
            display: flex;
            background-color: #f8f9fa;
            padding: 1rem;
        }
        .navbar-brand {
            margin-right: auto;
            font-weight: bold;
        }
        .navbar-nav {
            list-style: none;
            padding: 0;
            display: flex;
        }
        .nav-item {
            margin-left: 1rem;
        }
        .nav-link {
            text-decoration: none;
            color: #007bff;
        }
        .container {
            margin-top: 2rem;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        .alert-primary {
            color: #084298;
            background-color: #cfe2ff;
            border-color: #b6d4fe;
        }
        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeeba;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .list-group {
            padding-left: 0;
            margin-bottom: 1rem;
        }
        .list-group-item {
            position: relative;
            display: block;
            padding: 0.75rem 1.25rem;
            margin-bottom: -1px;
            background-color: #fff;
            border: 1px solid rgba(0,0,0,0.125);
        }
        .d-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .form-container {
            text-align: center;
            margin: 20px 0;
        }
        .outgoing {
            color: red;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="navbar-brand" href="#">eWallet</a>
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link" href="index.php">Home</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=new_transfer">New Transfer</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=withdraw_money">Withdraw Money</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="register.php">Register</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Logout</a>
            </li>
        </ul>
    </nav>

    <div class="container">
        <?php
        if (isset($_GET['page'])) {
            if ($_GET['page'] == 'new_transfer') {
                // Display the transfer form
                echo '
                    <h2>Create Transfer</h2>
                    <form action="index.php?page=new_transfer" method="POST">
                        <input type="hidden" name="form_type" value="transfer">
                        <table>
                            <tr>
                                <td><label for="amount">Amount</label></td>
                                <td><input type="number" id="amount" name="amount" required min="0.01" step="0.01"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="form-container"><button type="submit">Create Transfer</button></td>
                            </tr>
                        </table>
                    </form>';
            } elseif ($_GET['page'] == 'withdraw_money') {
                // Display the withdraw form
                echo '
                    <h2>Withdraw Money</h2>
                    <form action="index.php?page=withdraw_money" method="POST">
                        <input type="hidden" name="form_type" value="withdraw">
                        <table>
                            <tr>
                                <td><label for="amount">Amount</label></td>
                                <td><input type="number" id="amount" name="amount" required min="0.01" step="0.01"></td>
                            </tr>
                            <tr>
                                <td colspan="2" class="form-container"><button type="submit">Withdraw Money</button></td>
                            </tr>
                        </table>
                    </form>';
            }
        } else {
            // Display account balance and transfers
            echo '<div class="alert alert-primary" role="alert">Initial account balance: '.$accountBalance.' PLN</div>';

            $sql = "SELECT amount FROM transfers2 WHERE user_id = $userId";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                echo '<ul class="list-group">';
                while($row = $result->fetch_assoc()) {
                    $transfer = $row['amount'];
                    if ($transfer > 0) {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center">Incoming transfer: '.$transfer.' PLN</li>';
                    } else {
                        echo '<li class="list-group-item d-flex justify-content-between align-items-center outgoing">Outgoing transfer: '.$transfer.' PLN</li>';
                    }
                    $accountBalance += $transfer;
                    $transferBalance += $transfer;
                }
                echo '</ul>';
            } else {
                echo '<div class="alert alert-warning" role="alert">No transfers found.</div>';
            }

            echo '<div class="alert alert-primary" role="alert">Final account balance: '.$accountBalance.' PLN</div>';

            if ($accountBalance < 0) {
                echo '<div class="alert alert-danger" role="alert">You have an overdraft!</div>';
            }
            if ($transferBalance < 0) {
                echo '<div class="alert alert-danger" role="alert">Your expenses exceeded incomes!</div>';
            }

            $conn->close();
        }
        ?>
    </div>
</body>
</html>
