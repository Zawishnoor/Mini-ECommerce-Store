<?php
$conn = new mysqli("localhost", "root", "", "MiniEcommerceStore", 3306);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

if (!isset($_GET['admin_id'])) die("Unauthorized access!");
$admin_id = intval($_GET['admin_id']);

$stmt = $conn->prepare("SELECT Name, Email FROM ADMIN WHERE AdminID = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
if ($res->num_rows > 0) {
    $admin = $res->fetch_assoc();
    $adminName = $admin['Name'];
    $adminEmail = $admin['Email'];
} else {
    die("Admin not found.");
}

$orderCount = $conn->query("SELECT COUNT(*) as total FROM ORDERS")->fetch_assoc()['total'] ?? 0;
$customerCount = $conn->query("SELECT COUNT(*) as total FROM CUSTOMER")->fetch_assoc()['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - NEEDORE</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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

    .admin-info {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .admin-info i {
      font-size: 28px;
    }

    .admin-info span {
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

    .dashboard {
      padding: 40px 30px;
      display: flex;
      flex-direction: column;
      gap: 40px;
      width: 100%;
      max-width: 1500px;
    }

    .card-row {
      display: flex;
      flex-wrap: wrap;
      gap: 30px;
    }

    .card {
      background: linear-gradient(rgba(246, 246, 246, 0.8), rgba(233, 207, 238, 0.8));
      padding: 20px;
      border-radius: 12px;
      width: 280px;
      text-align: center;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
      transition: transform 0.3s, box-shadow 0.3s;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
      border: 2px solid #c488ec;
    }

    .manage-card {
      width: 600px;
      max-width: 100%;
      text-align: left;
    }

    .manage-card h3 {
      font-size: 22px;
      text-align: center;
      margin-bottom: 15px;
    }

    .manage-card form {
      display: flex;
      flex-direction: column;
      gap: 12px;
    }

    .manage-card select,
    .manage-card button {
      font-size: 16px;
      padding: 12px 16px;
    }

    button {
      background-color: #db86dbff;
      color: #341e40ff;
      border: 2px solid #e0aee0;
      font-weight: 600;
      font-size: 0.95rem;
      border-radius: 9999px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      cursor: pointer;
      transition: all 0.3s ease-in-out;
    }

    button:hover {
      background-color: #ca70d8;
    }

    select {
      background-color: white;
      color: black;
      border: 1px solid #e0aee0;
      border-radius: 5px;
    }

    .footer {
      background: #f8e1f0;
      color: #333;
      padding: 15px;
      text-align: center;
      font-size: 14px;
      margin-top: auto;
      width: 100%;
    }

    /* Responsive Styles */
    @media (min-width: 769px) {
  .dashboard {
    margin-top: 60px;
    margin-left: 60px;
  }
}

    @media (max-width: 900px) {
      header {
        flex-direction: column;
        align-items: center;
        text-align: center;
      }

      .admin-info {
        flex-direction: column;
        margin-top: 10px;
      }

      .dashboard {
        padding: 20px;
      }

      .card-row {
        flex-direction: column;
        align-items: center;
      }

      .card,
      .manage-card {
        width: 90%;
      }

      .logout-btn {
        margin-top: 10px;
      }
    }

    @media (max-width: 480px) {
      .manage-card select,
      .manage-card button {
        font-size: 14px;
        padding: 10px 12px;
        width: 100%;
      }

      .admin-info span {
        font-size: 16px;
      }

      .footer {
        font-size: 12px;
      }
    }
  </style>
</head>
<body>

  <header>
    <h2>NEEDORE</h2>
    <div class="admin-info">
      <i class="fas fa-user-circle"></i>
      <span><?= htmlspecialchars($adminName) ?></span>
      <a href="admin_login.php" class="logout-btn">Logout</a>
    </div>
  </header>

  <div class="dashboard">
    <!-- First Row -->
    <div class="card-row">
      <div class="card">
        <h3>No Of Orders</h3>
        <p style="font-size: 24px; color: green; margin-top:10px;"><b><?= $orderCount ?></b></p>
      </div>

      <div class="card">
        <h3>Registered Customers</h3>
        <p style="font-size: 24px; color: #007bff; margin-top:10px;"><b><?= $customerCount ?></b></p>
      </div>
    </div>

    <!-- Second Row -->
    <div class="card-row">
      <div class="card manage-card">
        <h3>Manage Products</h3>
        <form method="GET" action="manage_products.php">
          <input type="hidden" name="admin_id" value="<?= $admin_id ?>">
          <label for="action">Select Action:</label>
          <select name="action" required>
            <option value="">-- Choose --</option>
            <option value="add">Add Product</option>
            <option value="edit">Edit Product</option>
            <option value="delete">Delete Product</option>
          </select>
          <button type="submit">Next</button>
        </form>
      </div>
    </div>
  </div>

  <div class="footer">
    Email: <?= htmlspecialchars($adminEmail) ?>
  </div>

</body>
</html>