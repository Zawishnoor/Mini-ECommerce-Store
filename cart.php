<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: userlogin.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "MiniEcommerceStore", 3306);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

$customer_id = $_SESSION['customer_id'];
$cart_id = 0;
$items = [];
$showPaymentForm = false;
$totalAmount = 0;

if (isset($_GET['remove'])) {
    $item_id = intval($_GET['remove']);
    
    // Get cart ID first
    $stmt = $conn->prepare("SELECT CartID FROM CART WHERE CustomerID = ? ORDER BY CreatedAt DESC LIMIT 1");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 1) {
        $cart_id = $res->fetch_assoc()['CartID'];
        
        // Delete the cart item
        $stmt = $conn->prepare("DELETE FROM CARTITEMS WHERE CartItemID = ? AND CartID = ?");
        $stmt->bind_param("ii", $item_id, $cart_id);
        $stmt->execute();
    }
    
    header("Location: cart.php");
    exit();
}

$stmt = $conn->prepare("SELECT CartID FROM CART WHERE CustomerID = ? ORDER BY CreatedAt DESC LIMIT 1");
$stmt->bind_param("i", $customer_id);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 1) {
    $cart_id = $res->fetch_assoc()['CartID'];

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_qty'])) {
    list($action, $item_id) = explode('-', $_POST['update_qty']);
    $item_id = intval($item_id);

    // Get current quantity
    $stmt = $conn->prepare("SELECT Quantity FROM CARTITEMS WHERE CartItemID = ? AND CartID = ?");
    $stmt->bind_param("ii", $item_id, $cart_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        $qty = $row['Quantity'];
        if ($action === 'inc') {
            $qty++;
        } elseif ($action === 'dec' && $qty > 1) {
            $qty--;
        }

        $stmt = $conn->prepare("UPDATE CARTITEMS SET Quantity = ? WHERE CartItemID = ? AND CartID = ?");
        $stmt->bind_param("iii", $qty, $item_id, $cart_id);
        $stmt->execute();
    }

    header("Location: cart.php");
    exit();
}


    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
        if (isset($_POST['selected_items']) && count($_POST['selected_items']) > 0) {
            $_SESSION['selected_items'] = $_POST['selected_items'];
            $showPaymentForm = true;
            foreach ($_POST['selected_items'] as $item_id) {
                $price_stmt = $conn->prepare("SELECT P.Price, CI.Quantity FROM CARTITEMS CI JOIN PRODUCT P ON CI.ProductID = P.ProductID WHERE CI.CartItemID = ? AND CI.CartID = ?");
                $price_stmt->bind_param("ii", $item_id, $cart_id);
                $price_stmt->execute();
                $result = $price_stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    $totalAmount += $row['Price'] * $row['Quantity'];
                }
            }
        } else {
            echo "<script>alert('Please select at least one item to checkout.');</script>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
        $name = $_POST['full_name'];
        $phone = $_POST['phone'];
        $address = $_POST['address'];
        $pincode = $_POST['pincode'];
        $city = $_POST['city'];
        $state = $_POST['state'];
        $method = $_POST['payment_method'];
        $status = "Ordered";
        $selected_items = $_SESSION['selected_items'];

        foreach ($selected_items as $item_id) {
            $stmt = $conn->prepare("SELECT P.Price, CI.Quantity FROM CARTITEMS CI JOIN PRODUCT P ON CI.ProductID = P.ProductID WHERE CI.CartItemID = ? AND CI.CartID = ?");
            $stmt->bind_param("ii", $item_id, $cart_id);
            $stmt->execute();
            $res = $stmt->get_result();
            if ($row = $res->fetch_assoc()) {
                $amount = $row['Price'] * $row['Quantity'];

                $order_stmt = $conn->prepare("INSERT INTO ORDERS (CustomerID, Order_Date, TotalAmount, Status, Name, Phone, ShippingAddress, City, State, Pincode) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?)");
                $order_stmt->bind_param("idsssssss", $customer_id, $amount, $status, $name, $phone, $address, $city, $state, $pincode);
                $order_stmt->execute();
                $order_id = $order_stmt->insert_id;

                $payment_stmt = $conn->prepare("INSERT INTO PAYMENT (OrderID, PaymentDate, PaymentMethod, PaymentStatus, Amount) VALUES (?, NOW(), ?, 'Success', ?)");
                $payment_stmt->bind_param("isd", $order_id, $method, $amount);
                $payment_stmt->execute();

                $del_stmt = $conn->prepare("DELETE FROM CARTITEMS WHERE CartItemID = ?");
                $del_stmt->bind_param("i", $item_id);
                $del_stmt->execute();
            }
        }

        unset($_SESSION['selected_items']);
        header("Location: my_orders.php?success=1");
        exit();
    }

    $stmt = $conn->prepare("SELECT CI.CartItemID, P.Name, P.Price, P.Image_URL, CI.Quantity FROM CARTITEMS CI JOIN PRODUCT P ON CI.ProductID = P.ProductID WHERE CI.CartID = ?");
    $stmt->bind_param("i", $cart_id);
    $stmt->execute();
    $items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Cart Checkout - NEEDORE</title>
      <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #fff6f9;
            padding: 40px;
        }
        .container {
            max-width: 960px;
            margin: auto;
            background: #f2dff2ff;
            padding: 25px;
            border-radius: 12px;
        }
        .cart-item {
            display: grid;
            grid-template-columns: 40px 100px 1fr auto;
            align-items: center;
            background: #fff;
            padding: 12px;
            margin-bottom: 12px;
            border-radius: 10px;
            gap: 15px;
        }
        .cart-item img {
            width: 80px;
            height: 80px;
            object-fit: contain;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .item-info h4 {
            margin: 0 0 5px;
            font-size: 16px;
            color: #5a2e1b;
        }
        .item-info p {
            margin: 2px 0;
        }
        .remove-btn {
            background-color: #dc3545;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }
        .remove-btn:hover {
            background-color: #b02a37;
        }
        .form-section label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            width: 100%;
            padding: 10px;
             border-radius: 8px;
            border: 1.8px solid #d474cb;
            font-size: 15px;
            transition: 0.3s;
            margin-bottom: 12px;
        }
        .optional-fields {
            display: none;
        }
        .submit-btn {
            background: #efb9efff;
            color: #b014b0;
            border: 2px solid #e0aee0;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
        }
        .submit-btn:hover {
           background: #f7c3f7;
            color: #a012a0;

        }
		.back-link {
            margin-top: 25px;
            text-align: center;
        }

        input[type="checkbox"]:checked ~ .item-info {
    background-color: #ffe8f8;
    border-radius: 8px;
    transition: background 0.3s ease;
}

/* button.submit-btn {
    padding: 6px 12px;
    font-size: 14px;
    border-radius: 6px;
    margin: 0 3px;
} */


        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            .cart-item {
                grid-template-columns: 30px 60px 1fr auto;
                padding: 8px;
                gap: 8px;
            }
            .cart-item img {
                width: 50px;
                height: 50px;
            }
            .item-info h4 {
                font-size: 14px;
            }
            .item-info p {
                font-size: 13px;
            }
            .submit-btn {
                padding: 10px 15px;
                font-size: 14px;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding: 2px;
            }
            .cart-item {
                grid-template-columns: 1fr;
                grid-template-rows: auto auto auto auto;
                text-align: center;
                gap: 10px;
            }
            .cart-item img {
                margin: 0 auto;
                width: 60px;
                height: 60px;
            }
            .item-info {
                margin-bottom: 8px;
            }
            .remove-btn {
                width: 100%;
                margin-top: 8px;
                font-size: 16px;
                padding: 10px 0;
            }
            .submit-btn {
                width: 100%;
                font-size: 16px;
                padding: 14px 0;
            }
            .form-section label, input, select {
                font-size: 15px;
            }
            .form-section input, .form-section select {
                padding: 12px;
            }
            .form-section {
                min-width: 0;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <h2>My Cart</h2>
    <?php if (count($items) === 0 && !$showPaymentForm): ?>
    <p style="text-align:center; font-size: 18px;">🛒 Your cart is empty.</p>
    <div style="text-align:center; margin-top: 20px;">
        <a href="browse_products.php" class="submit-btn">← Shop Now</a>
    </div>
<?php return; endif; ?>

    <?php if (!$showPaymentForm): ?>
        <form method="post">
    <?php foreach ($items as $item): ?>
        <div class="cart-item">
            <input type="checkbox" name="selected_items[]" value="<?= $item['CartItemID'] ?>">
            <img src="<?= htmlspecialchars($item['Image_URL']) ?>">
            <div class="item-info">
                <h4><?= htmlspecialchars($item['Name']) ?></h4>
                <p>₹<?= $item['Price'] ?> x <?= $item['Quantity'] ?></p>
                <p>Subtotal: ₹<?= $item['Price'] * $item['Quantity'] ?></p>

                <div style="margin-top:10px;">
                    <button type="submit" name="update_qty" value="dec-<?= $item['CartItemID'] ?>" class="submit-btn" style="padding: 2px 10px;">−</button>
                    <span style="margin: 0 10px; font-weight:bold;"><?= $item['Quantity'] ?></span>
                    <button type="submit" name="update_qty" value="inc-<?= $item['CartItemID'] ?>" class="submit-btn" style="padding: 2px 10px;">+</button>
                </div>
            </div>
            <a class="remove-btn" href="cart.php?remove=<?= $item['CartItemID'] ?>">Remove</a>
        </div>
    <?php endforeach; ?>

            <button class="submit-btn" type="submit" name="place_order">Checkout</button>
			<div class="back-link">
        <a href="userprofile.php">← Back to Dashboard</a>
    </div>
        </form>
		
    <?php else: ?>
        <h3>Total: ₹<?= number_format($totalAmount, 2) ?></h3>
        <form method="POST" class="form-section" onsubmit="return validatePaymentForm();">
            <input type="hidden" name="confirm_payment" value="1">
            <label>Full Name: <input type="text" name="full_name" required></label>
            <label>Phone: <input type="text" name="phone" required pattern="[0-9]{10}" title="Enter 10-digit phone number"></label>
            <label>Address: <input type="text" name="address" required></label>
            <label>Pincode:<input type="text" name="pincode" required pattern="[0-9]{6}" title="Enter 6-digit pincode"></label>
            <label>City: <input type="text" name="city" required></label>
            <label>State: <input type="text" name="state" required></label>
            <label>Payment Method:
                <select name="payment_method" id="payment_method" required onchange="togglePaymentFields()">
                    <option value="">-- Choose Method --</option>
                    <option value="COD">Cash on Delivery</option>
                    <option value="UPI">UPI</option>
                    <option value="Debit Card">Debit Card</option>
                    <option value="Credit Card">Credit Card</option>
                </select>
            </label>

            <div id="upi_field" class="optional-fields">
                <label>UPI ID: <input type="text" id="upi_id" placeholder="example@upi"></label>
            </div>

            <div id="card_fields" class="optional-fields">
                <label>Card Number: <input type="text" id="card_number" maxlength="16" placeholder="Enter 16-digit card number"></label>
                <label>Expiry Date: <input type="text" id="card_expiry"></label>
                <label>CVV: <input type="password" id="card_cvv" maxlength="3" placeholder="CVV"></label>
            </div>

            <button class="submit-btn" type="submit">Confirm & Pay</button>
        </form>

        <div style="text-align: center; margin-top: 20px;">
    <a href="browse_products.php" class="submit-btn">← Continue Shopping</a>
</div>

    <?php endif; ?>
</div>
<script>
    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        document.getElementById('upi_field').style.display = (method === 'UPI') ? 'block' : 'none';
        document.getElementById('card_fields').style.display = (method === 'Debit Card' || method === 'Credit Card') ? 'block' : 'none';
    }

    function validatePaymentForm() {
        const method = document.getElementById('payment_method').value;

        if (method === 'UPI') {
            const upi = document.getElementById('upi_id').value.trim();
            if (upi === '') {
                alert("Please enter your UPI ID.");
                return false;
            }
        }

        if (method === 'Debit Card' || method === 'Credit Card') {
            const num = document.getElementById('card_number').value.trim();
            const exp = document.getElementById('card_expiry').value;
            const cvv = document.getElementById('card_cvv').value.trim();

            
            if (!exp) {
                alert("Please enter card expiry date.");
                return false;
            }
            if (cvv.length !== 3 || isNaN(cvv)) {
                alert("Please enter valid 3-digit CVV.");
                return false;
            }
        }

        return true;
    }
</script>

<?php if (isset($_GET['success'])): ?>
    <div style="background: #d4edda; padding: 15px; color: #155724; border-radius: 8px; text-align: center; margin-bottom: 20px;">
        ✅ Your order has been placed successfully!
    </div>
<?php endif; ?>

</body>
</html>