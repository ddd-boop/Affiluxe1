<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);
error_log("User  ID: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'Not set'));

// Check if the user is logged in and if the user ID is a valid integer
if (!isset($_SESSION['user_id']) || !filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT)) {
    error_log("Redirecting to signup.php because user_id is not set or invalid.");
    header("Location: signup.php");
    exit();
}

// MySQL credentials
$servername = "fdb1029.awardspace.net";
$username = "4567167_affiluxe"; 
$password = "7YxdH0s*4P59pmqw"; 
$dbname = "4567167_affiluxe";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Sorry, we're having some technical difficulties.");
}

// Fetch user information
$user_id = $_SESSION['user_id'];

// Validate that user_id is actually integer and positive before the bind_param
if (!filter_var($user_id, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])) {
    error_log("Invalid user_id in session.");
    header("Location: signup.php");
    exit();
}

$stmt = $conn->prepare("SELECT name, email, referral_code, points, activity_balance, last_spin_time FROM users WHERE id = ?");
if (!$stmt) {
    error_log("Prepare failed (fetch user): " . $conn->error);
    die("Sorry, we're having some technical difficulties.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result(); // Store the result to check if any rows were returned

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $email, $referral_code, $points, $activity_balance, $last_spin_time);
    $stmt->fetch();
} else {
    error_log("User  not found for ID: $user_id");
    die("User  not found.");
}

$stmt->close();

// Initialize message variables
$message = '';
$message_type = '';

// Check if a task button has been clicked
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task_id'])) {
        $task_id = intval($_POST['task_id']);

        // Check if the task has already been completed
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM user_tasks WHERE user_id = ? AND task_id = ?");
               if (!$check_stmt) {
            error_log("Prepare failed (check task): " . $conn->error);
            die("Sorry, we're having some technical difficulties.");
        }
        $check_stmt->bind_param("ii", $user_id, $task_id);
        $check_stmt->execute();
        $check_stmt->bind_result($count);
        $check_stmt->fetch();
        $check_stmt->close();

        if ($count === 0) {
            // Task not completed, proceed to update the user's activity balance
            $update_stmt = $conn->prepare("UPDATE users SET activity_balance = activity_balance + 1 WHERE id = ?");
            if (!$update_stmt) {
                error_log("Prepare failed (update activity): " . $conn->error);
                die("Sorry, we're having some technical difficulties.");
            }
            $update_stmt->bind_param("i", $user_id);
            if (!$update_stmt->execute()) {
                error_log("Error updating activity balance: " . $update_stmt->error);
                die("Sorry, we're having some technical difficulties.");
            }
            $update_stmt->close();

            // Insert into user_tasks to mark the task as completed
            $insert_stmt = $conn->prepare("INSERT INTO user_tasks (user_id, task_id) VALUES (?, ?)");
            if (!$insert_stmt) {
                error_log("Prepare failed (insert user_task): " . $conn->error);
                die("Sorry, we're having some technical difficulties.");
            }
            $insert_stmt->bind_param("ii", $user_id, $task_id);
            if (!$insert_stmt->execute()) {
                error_log("Error inserting into user_tasks: " . $insert_stmt->error);
                die("Sorry, we're having some technical difficulties.");
            }
            $insert_stmt->close();

            // Set success message
            $message = 'You have been credited with $1!';
            $message_type = 'success';
        } else {
            // Task already completed
            $message = 'You have already completed this task.';
            $message_type = 'info';
        }
    }

    // Check if the spin button has been clicked
    if (isset($_POST['spin_wheel'])) {
        // Check if 24 hours have passed since the last spin
        $current_time = new DateTime();
        if (!empty($last_spin_time)) {
            $last_spin = new DateTime($last_spin_time);
            $interval = $current_time->diff($last_spin);
            if ($interval->h < 24 && $interval->days == 0) {
                $message = 'You can only spin once every 24 hours.';
                $message_type = 'info';
            } else {
                // Update the user's activity balance and last spin time
                $update_stmt = $conn->prepare("UPDATE users SET activity_balance = activity_balance + 1, last_spin_time = NOW() WHERE id = ?");
                if (!$update_stmt) {
                    error_log("Prepare failed (update spin): " . $conn->error);
                    die("Sorry, we're having some technical difficulties.");
                }
                $update_stmt->bind_param("i", $user_id);
                if (!$update_stmt->execute()) {
                    error_log("Error updating activity balance and last spin time: " . $update_stmt->error);
                    die("Sorry, we're having some technical difficulties.");
                }
                $update_stmt->close();

                // Set success message
                $message = 'You have been credited with $1 for spinning!';
                $message_type = 'success';
            }
        } else {
            // First spin
            $update_stmt = $conn->prepare("UPDATE users SET activity_balance = activity_balance + 1, last_spin_time = NOW() WHERE id = ?");
            if (!$update_stmt) {
                error_log("Prepare failed (update first spin): " . $conn->error);
                die("Sorry, we're having some technical difficulties.");
            }
            $update_stmt->bind_param("i", $user_id);
            if (!$update_stmt->execute()) {
                error_log("Error updating activity balance and setting last spin time: " . $update_stmt->error);
                die("Sorry, we're having some technical difficulties.");
            }
            $update_stmt->close();

            // Set success message for the first spin
            $message = 'You have been credited with $1 for your first spin!';
            $message_type = 'success';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="images/earn.jpeg"/>
    <title>EarnMart.com</title>
    <meta charset="UTF-8">
    <meta name="theme-color" content="white" media="(prefers-color-scheme: white)">
    <meta name="theme-color" content="black">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1, maximum-scale=2, viewport-fit=cover">    
    <link rel="stylesheet" href="css/page.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> 
    <style>
        /* Modal styles */
        #messageModal {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: green;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            transition: opacity 0.5s;
        }
    </style>
</head>
<body id="body">
    <div class="nav-container">
        <div class="nav-contain">
            <div class="nav">
                <div class="bar-con" id="menu"><i class="fa fa-bars" title="menu"></i></div>
                <div class="photo" id="user">
                    <i class="fa fa-user" title="profile">
                        <a href="https://chat.whatsapp.com/DuPlJMSspGN3COOKhdF3D6">
                            <div class="test" title="EarnMart group">
                                <i class="fa fa-users"></i>
                            </div>
                        </a>  
                    </i>
                </div>
                <div class="affiluxe">
                    <div class="aff"><span style="color:red;">EarnMart</span></div>
                    <div class="h2">Welcome: <span id="dashboardUsername"><?php echo htmlspecialchars($name); ?></span></div>                  
                </div>
            </div>
        </div>
        <div class="welcome"></div>
    </div>
    
    <div class="contain" id="contain">
        <div class="ttt zindex" id="dailly">
            <div class="d-head" id="dad">
                <div class="tass-head">
                    <span class="ttext">EarnMart</span>
                </div>
                <div class="task-1">
                    <form method="POST" action="">
                        <div class="submit" id="addFundsButton1">Task (1) <br>
                            <span>Join WhatsApp group
                                <a href="https://www.facebook.com/profile.php?id=61574730646585">
                                    <button type="submit" name

<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="images/earn.jpeg"/>
    <title>EarnMart.com</title>
    <meta charset="UTF-8">
    <meta name="theme-color" content="white" media="(prefers-color-scheme: white)">
    <meta name="theme-color" content="black">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1, maximum-scale=2, viewport-fit=cover">    
    <link rel="stylesheet" href="css/page.css">
    <link rel="stylesheet" type="text/css" href="css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css"> 
    <style>
        /* Modal styles */
        #messageModal {
            display: none;
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: green;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 1000;
            transition: opacity 0.5s;
        }
    </style>
</head>
<body id="body">
    <div class="nav-container">
        <div class="nav-contain">
            <div class="nav">
                <div class="bar-con" id="menu"><i class="fa fa-bars" title="menu"></i></div>
                <div class="photo" id="user">
                    <i class="fa fa-user" title="profile">
                        <a href="https://chat.whatsapp.com/DuPlJMSspGN3COOKhdF3D6">
                            <div class="test" title="EarnMart group">
                                <i class="fa fa-users"></i>
                            </div>
                        </a>  
                    </i>
                </div>
                <div class="affiluxe">
                    <div class="aff"><span style="color:red;">EarnMart</span></div>
                    <div class="h2">Welcome: <span id="dashboardUsername"><?php echo htmlspecialchars($name); ?></span></div>                  
                </div>
            </div>
        </div>
        <div class="welcome"></div>
    </div>
    
    <div class="contain" id="contain">
        <div class="ttt zindex" id="dailly">
            <div class="d-head" id="dad">
                <div class="tass-head">
                    <span class="ttext">EarnMart</span>
                </div>
                <div class="task-1">
                    <form method="POST" action="">
                        <div class="submit" id="addFundsButton1">Task (1) <br>
                            <span>Join WhatsApp group
                                    <a href="https://www.facebook.com/profile.php?id=61574730646585">
                            <button type="submit" name="task_id" value="1" id="taskButton1">Check</button>
                                            </a>
                            </span>
                        </div> 
                        <div class="submit" id="addFundsButton2">Task (2) <br>
                            <span>Join Telegram group 
                            <button type="submit" name="task_id" value="2" id="taskButton2">Check</button>
                            </span>
                        </div> 
                        <div class="submit" id="addFundsButton3">Task (3) <br>
                            <span>Follow Facebook account 
                            <button type="submit" name="task_id" value="3" id="taskButton3">Check</button>
                            </span>
                        </div>   
                        <div class="submit" id="addFundsButton4">Task (4) <br>
                            <span>Follow Twitter (X) account 
                            <button type="submit" name="task_id" value="4" id="taskButton4">Check</button>
                            </span>
                        </div>
                        <div class="submit" id="addFundsButton5">Task (5) <br>
                            <span>Follow Instagram account 
                            <button type="submit" name="task_id" value="5" id="taskButton5">Check</button>
                            </span>
                        </div>   
                                               <div class="submit" id="addFundsButton6">Task (6) <br>
                            <span>Follow TikTok account 
                            <button type="submit" name="task_id" value="6" id="taskButton6">Check</button>
                            </span>
                        </div>       
                    </form>
                </div>
                <div class="follow">EarnMart</div>
                <div class="alertt">
                    Please ensure to complete every task in this session to avoid problems when receiving payment.
                </div>
            </div>
        </div>

        <!-- Modal for messages -->
        <div id="messageModal" style="display:none;">
            <p id="modalMessage"><?php echo htmlspecialchars($message); ?></p>
        </div>

        <div class="ttt zindex" id="withdrawal">
            <div class="tasssk" id="wy">
                <a href="">
                    <div class="ccoon">
                        <div class="ccc">
                            <i class="fa fa-arrow-left">home</i>
                        </div>
                    </div>
                </a>
                <div class="container" id="draw">
                    <h1>Referral Withdrawal</h1>
                    <form id="withdrawalForm" method="POST">
                        <input type="text" name="bank_name" placeholder="Bank Name" required />
                        <input type="text" name="account_name" placeholder="Account Name" required />
                        <input type="number" name="account_number" placeholder="Account Number" required />
                        <input type="number" name="amount" placeholder="Amount" required />
                        <input type="text" name="earnmart_username" value="<?php echo htmlspecialchars($name); ?>" required readonly />
                        <input type="text" name="country" placeholder="Country" required />
                        <button type="submit" name="withdraw">Withdraw</button>
                    </form>
                    <div id="withdrawalMessage"></div> <!-- To display messages -->
                </div>
            </div>
        </div>

        <div class="ttt zindex" id="withdrawa">
            <div class="tasssk" id="wi">
                <a href="">
                    <div class="ccoon">
                        <div class="ccc">
                            <i class="fa fa-arrow-left">home</i>
                        </div>
                    </div>
                </a>
                <div class="container" id="draw">
                    <h1>Task Withdrawal</h1>
                    <form method="POST" action="">
                        <input type="text" class="inp" placeholder="Bank Name" id="TbankName" required />
                        <input type="text" class="inp" placeholder="Account Name" id="TaccountName" required />
                        <input type="text" class="inp" placeholder="Account Number" id="TAccountNumber" required />
                        <input type="number" class="inp" placeholder="Amount" id="TAmount" required />
                        <input type="text" class="inp" placeholder="EarnMart Username" id="TEarnMartUsername" value="<?php echo htmlspecialchars($name); ?>" required readonly />
                        <input type="text" class="inp" placeholder="Country" id="Tcountry" required />
                        <button id="w">Withdraw</button>
                        <p class="error" id="TError"></p>
                    </form>
                </div>
            </div>
        </div>

        <div class="ttt zindex" id="spin-to">
            <div class="tasssk" id="spin-up">
                <div class="cconn">
                    <a href="">
                        <div class="ccc">
                            <i class="fa fa-arrow-left">home</i>
                        </div>
                    </a>
                    <div class="class">Spin your daily task</div>
                    <div class="wheel-container">
                        <div class="wheel" id="wheel">
                            <!-- Prize sectors labeled on the wheel -->
                            <span style="font-weight: bolder; color: beige;"></span>
                        </div>
                        <form method="POST" action="">
                            <button type="submit" id="spinBtn" class="btn" name="spin_wheel">Spin the Wheel</button>
                        </form>
                        <div id="prizeAmount"></div>
                        <div class="spin-balance">
                            <span class="span-2">Spin the wheel to complete today's task</span>
                            <div style="margin-top: 10px; font-size: 18px; color: #666;" id="spinmess"><?php echo isset($spin_message) ? $spin_message : ''; ?></div>
                        </div>
                    </div>
                </div>
                                </div>
        </div>

        <div class="menu hidden" id="menu-con" style="background:white !important;">
            <div class="af-con">
                <div class="pics">EarnMart</div>
                <div style="width: fit-content; height: fit-content;">
                    <div class="bell-con fa fa-bell" title="notifications" id="notifications">
                        <span class="note" style="color: red;"> Notification</span>
                    </div>
                </div>
                <div class="sponsorr">
                    <div class="ss fa fa-empire" title="sponsor post" id="sponsor"> Referral cx </div>
                </div>
                <div class="set" title="task" id="daily">
                    <div class="tt fa fa-gift"> noobietask</div>
                </div>
                <div class="withdraw-men" title="withdraw" id="channels">
                    <a href="https://t.">
                        <div class="ww fa fa-telegram"> T-Channel</div>
                    </a>
                </div>
                <div class="button-con">
                    <a href="login.php">
                        <button value="logout">Logout</button>
                    </a>
                </div>
            </div>
        </div>

        <div class="ref-container" id="container">
            <div class="ref-words">Available balance</div>
            <div class="second">
                <div class="naira">
                    <div class="n"></div>
                </div>
                <div class="ref-bal-con" title="referral balance">
                    <div class="ref-balance" id="availableBalance">$<?php echo htmlspecialchars($points); ?></div>
                    <div class="with" id="withdraw" title="Withdraw funds">
                        <i class="lock fa fa-lock"></i> Withdraw
                    </div>
                </div>
            </div>
        </div>

        <div class="list-container">
            <div class="spin" id="spin">Daily Task<br><i class="fa fa-empire"></i></div>
        </div>

        <div class="ref-no-container">
            <div class="con">
                <div class="ref-num" title="number of referrals">
                    <div class="ref-number">Referral balance:</div>
                    <div class="number-ref" id="referralBalance">$<?php echo htmlspecialchars($points); ?></div>
                </div>
                <div class="task-earnings" title="task balance">
                    <div class="task-bal">Activity balance:</div>
                    <div class="tas">$<span class="man" id="balanceAmount"><?php echo htmlspecialchars($activity_balance); ?></span></div>
                    <div class="red" id="taskw"><i class="lock fa fa-lock"></i> withdraw</div>
                </div>
            </div>
            <div class="constant">
                <div class="sponsor-con">
                    <div class="total">
                        <div style="margin: 0 auto; height: auto; width: 136px; position: relative; left: 15px; bottom: 10px;text-align:center;">
                            Total balance:</div>
                        <div class="total-bal" style="color:red;text-align:center;">
                            <span class="caa" id="totalBalance">$<?php echo htmlspecialchars($total_users); ?></span>
                            <span class="soon"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal for messages -->
    <div id="messageModal" style="display:none;">
        <p id="modalMessage"><?php echo htmlspecialchars($message); ?></p>
    </div>

    <script type="text/javascript" src="javascript/jquery-3.7.1.min.js"></script>
    <script type="text/javascript" src="javascript/page.js"></script> 

    <script>
        // Function to show the message modal
        function showMessageModal(message) {
            var modal = document.getElementById('messageModal');
            var modalMessage = document.getElementById('modalMessage');
            modalMessage.textContent = message;
            modal.style.display = 'block';
            setTimeout(function() {
                modal.style.display = 'none';
            }, 3000); // Hide after 3 seconds
        }

        // Show the message modal if there is a message
        <?php if ($message): ?>
            showMessageModal('<?php echo addslashes($message); ?>');
        <?php endif; ?>
    </script>
</body>
</html>
