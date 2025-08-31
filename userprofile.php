<?php
session_start();

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: userlogin.php");
    exit();
}

if (!isset($_SESSION['customer_id'])) {
    header("Location: userlogin.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "MiniEcommerceStore", 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$customer_id = $_SESSION['customer_id'];
$stmt = $conn->prepare("SELECT Name, Email FROM CUSTOMER WHERE CustomerID = ?");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
    $row = $result->fetch_assoc();
    $customerName = $row['Name'];
    $customerEmail = $row['Email'];
} else {
    $customerName = "User";
    $customerEmail = "Not Available";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - NEEDORE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
       

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Poppins', sans-serif;
      background: linear-gradient(rgba(255, 255, 255, 0.48), rgba(255, 255, 255, 0.21)),
                  url('https://png.pngtree.com/thumb_back/fh260/background/20210609/pngtree-3d-render-online-shopping-with-mobile-and-bag-image_727266.jpg') no-repeat center center/cover;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      color: #2b2b2b;
    }

        header {
      background: rgba(255, 255, 255, 0.7);
      padding: 12px 30px;
      border-radius: 0 0 2rem 2rem;
      backdrop-filter: blur(10px);
      display: flex;
      
      justify-content: space-between;
      align-items: center;
      box-shadow: 0 4px 12px rgba(103, 95, 95, 0.1);
      flex-wrap: wrap;
      z-index: 1000;
    }

        header h2 {
      font-size: 24px;
      margin-left:25px;
      color: #5a2e1b;
      font-weight: 700;
    }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-info i {
            font-size: 28px;
            color: #333;
        }

        .user-info span {
            color: #5a2e1b; 
            font-weight: bold;
            font-size: 18px;
        }

        .logout-btn {
            background-color: #c716dbb0;
            color: white;
            border: none;
            padding: 8px 16px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-left:10px;
            margin-right:30px;
            border-radius: 9999px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background-color: #db285eff;
        }

        .dashboard-wrapper {
            padding: 60px 60px;
        }
        .dashboard-columns {
            display: flex;
            align-items: flex-start;
            gap: 60px;
            flex-wrap: wrap;
        }
        .dashboard-col {
            display: flex;
            flex-direction: column;
            gap: 30px;
        }
        .center-col {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: flex-start;
            padding-top: 60px;
        }
        .card {
             background: linear-gradient(rgba(246, 246, 246, 0.8), rgba(233, 207, 238, 0.8));
            width: 250px;
            height: 250px;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            transition: transform 0.3s, box-shadow 0.3s; 
            padding: 20px;
    
        }
        .card:hover {
            transform: scale(1.03);
        }
        .card i {
            font-size: 36px;
            margin-bottom: 10px;
            color: #5a2e1b;
        }
        .card a {
            text-decoration: none;
            font-weight: bold;
            color: #5a2e1b;
            font-size: 18px;
        }

        .footer {
            background-color: #1F2937;
            padding: 15px;
            text-align: center;
            margin-top: auto;
            color: #D1D5DB;
        }
        @media (max-width: 900px) {
            .dashboard-wrapper {
                padding: 32px 8px;
            }
            .dashboard-columns {
                flex-direction: column;
                gap: 32px;
                align-items: stretch;
            }
            .dashboard-col, .center-col {
                flex-direction: row;
                justify-content: center;
                align-items: center;
                gap: 16px;
            }
            .dashboard-col {
                margin-bottom: 0;
            }
            .center-col {
                padding-top: 0;
            }
        }
        @media (max-width: 600px) {
            header {
                flex-direction: column;
                gap: 10px;
                padding: 10px 4px;
               
            }
            header h2{
                margin-left:6px;
            }
            .dashboard-wrapper {
                padding: 16px 2px;
            }
            .dashboard-columns {
                gap: 16px;
            }
            .card {
                width: 90vw;
                max-width: 320px;
                height: auto;
                min-height: 160px;
                padding: 18px 8px;
            }
            .logout-btn {
            margin-left:130px;
            margin-right:0px;
        }
        }
        @media (min-width: 601px) and (max-width: 1024px) {
            .card {
                width: 220px;
                height: 340px;
                max-width: 90vw;
                padding: 32px 16px;
            }
        }
        @media (max-width: 900px) {
            .dashboard-columns {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 24px;
                justify-content: center;
            }
            .dashboard-col {
                flex-direction: row;
                flex-wrap: wrap;
                align-items: stretch;
                justify-content: center;
                gap: 16px;
            }
            .center-col {
                width: 100%;
                flex-direction: column;
                align-items: center;
                justify-content: flex-start;
                gap: 0;
                padding-top: 0;
            }
            .center-col .card {
                width: 100%;
                max-width: 340px;
                margin: 0 auto 12px auto;
            }
            .card {
                flex: 1 1 260px;
                max-width: 340px;
                min-width: 180px;
                width: 100%;
                margin: 0 auto;
                height: auto;
                min-height: 160px;
                padding: 18px 8px;
            }
        }
        @media (max-width: 600px) {
            .dashboard-columns {
                flex-direction: column;
                gap: 12px;
            }
            .dashboard-col {
                flex-direction: column;
                gap: 8px;
            }
            .center-col {
                width: 100%;
                align-items: center;
            }
            .center-col .card {
                width: 100%;
                max-width: 100%;
                margin: 0 0 12px 0;
            }
            .card {
                width: 100%;
                max-width: 100%;
                min-width: 0;
                margin: 0 0 12px 0;
                min-height: 120px;
                padding: 14px 4px;
            }
        }
        @media (max-width: 900px) {
            .center-col .card {
                width: 100%;
                max-width: 340px;
                margin: 0 auto 12px auto;
                min-height: 160px;
                height: auto;
                max-height: 340px;
            }
        }
        @media (max-width: 600px) {
            .center-col .card {
                width: 100%;
                max-width: 100%;
                margin: 0 0 12px 0;
            }
        }
        @media (max-width: 900px) {
            .dashboard-wrapper {
                padding-left: min(7vw, 40px);
                padding-right: min(7vw, 40px);
            }
        }
        @media (max-width: 600px) {
            .dashboard-wrapper {
                padding-left: min(6vw, 24px);
                padding-right: min(6vw, 24px);
            }
        }
    </style>
</head>
<body>

<header>
    <h2>NEEDORE</h2>
    <div class="user-info">
        <i class="fas fa-user-circle"></i>
        <span><?= htmlspecialchars($customerName) ?></span>
        <a href="?logout=true" class="logout-btn">Logout</a>
    </div>
</header>
<div class="dashboard-wrapper">
    <div class="dashboard-columns">
        <div class="dashboard-col">
            <div class="card">
                <i class="fas fa-store"></i>
                <a href="browse_products.php">Browse Products</a>
            </div>
            <div class="card">
                <i class="fas fa-shopping-cart"></i>
                <a href="cart.php">My Cart</a>
            </div>
        </div>
        <div class="dashboard-col">
            <div class="card">
                <i class="fas fa-clipboard-list"></i>
                <a href="my_orders.php">My Orders</a>
            </div>
            <div class="card">
                <i class="fas fa-user-edit"></i>
                <a href="edit_profile.php">Edit Profile</a>
            </div>
        </div>
        <div class="dashboard-col center-col">
            <div class="card">
                <i class="fas fa-couch"></i>
                <a href="myroom.php">Design My Look</a>
            </div>
        </div>
    </div>
</div>

<div class="footer">
    Logged in as: <?= htmlspecialchars($customerEmail) ?>
</div>

</body>
</html>
