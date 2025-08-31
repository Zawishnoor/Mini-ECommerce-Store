<?php
$conn = new mysqli("localhost", "root", "", "MiniEcommerceStore", 3306);
if ($conn->connect_error) die("Connection failed.");

if (!isset($_GET['admin_id'], $_GET['action'])) die("Invalid access");

$admin_id = intval($_GET['admin_id']);
$action = $_GET['action'];

$subcategories = $conn->query("SELECT * FROM SUBCATEGORY");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Products</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style>
        * {
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    margin: 0;
    padding: 0;
    min-height: 100vh;
    background: linear-gradient(rgba(255, 240, 245, 0.9), rgba(255, 240, 245, 0.9)),
                url('https://png.pngtree.com/thumb_back/fh260/background/20210609/pngtree-3d-render-online-shopping-with-mobile-and-bag-image_727266.jpg') no-repeat center center/cover;
    display: flex;
    align-items: flex-start;
    /* justify-content: center; */
    padding: 40px 15px;
}

.form-container {
    margin-left:200px;
    margin-top:150px;
    background: white;
    padding: 30px 25px;
    border-radius: 12px;
    max-width: 450px;
    width: 100%;
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

h2 {
    text-align: center;
    margin-bottom: 25px;
    color: #b014b0;
}

label {
    font-weight: bold;
    display: block;
    margin-top: 15px;
    color: #333;
}

select, button {
    width: 100%;
    padding: 12px;
    margin-top: 10px;
    border: 1.8px solid #d474cb;
    border-radius: 8px;
    font-size: 15px;
    background-color: white;
    transition: border 0.3s;
}

select:focus {
    outline: none;
    border-color: #b014b0;
}

button {
    background-color: #f5def5;
    color: #b014b0;
    font-weight: bold;
    margin-top: 20px;
    border: 2px solid #e0aee0;
    transition: background-color 0.3s ease;
    cursor: pointer;
}

button:hover {
    background-color: #f7c3f7;
    color: #a012a0;
}

.btn-back {
    display: block;
    text-align: center;
    text-decoration: none;
    margin-top: 18px;
    padding: 10px;
    background: #dc52ffff;
    color: white;
    border-radius: 6px;
    font-size: 15px;
    font-weight: bold;
    transition: background-color 0.3s ease;
}

.btn-back:hover {
    background-color: #d92dd3ff;
}

@media (max-width: 480px) {
    body {
        padding: 20px 10px;
        background-size: cover;
    }

    .form-container {
        padding: 20px;
        margin-left:1px;;
    }

    h2 {
        font-size: 1.4rem;
    }

    select, button {
        font-size: 14px;
        padding: 10px;
    }

    .btn-back {
        font-size: 14px;
        padding: 8px;
    }
}


    </style>
</head>
<body>

<div class="form-container">
    <form method="GET" action="manage_action.php">
        <h2><?= ucfirst($action) ?> Product</h2>
        <input type="hidden" name="admin_id" value="<?= $admin_id ?>">
        <input type="hidden" name="action" value="<?= htmlspecialchars($action) ?>">

        <label>Select Subcategory:</label>
        <select name="subcat_id" required>
            <?php foreach ($subcategories as $s): ?>
                <option value="<?= $s['SubCategoryID'] ?>"><?= $s['SubCategoryName'] ?></option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Proceed</button>

        <a class="btn-back" href="admin_dashboard.php?admin_id=<?= $admin_id ?>">Back to Dashboard</a>
    </form>
</div>

</body>
</html>
