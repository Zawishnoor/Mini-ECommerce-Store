<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: userlogin.php");
    exit();
}

$conn = new mysqli("localhost", "root", "", "MiniEcommerceStore", 3306);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT ProductID, Name, Image_URL FROM PRODUCT";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Design My Room</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #5a2e1b;
            margin-bottom: 20px;
        }

        #product-bar {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #ccc;
            max-width: 1000px;
            margin-inline: auto;
            scroll-snap-type: x mandatory;
        }

        .product {
            flex: 0 0 auto;
            width: 90px;
            height: 90px;
            border: 1px solid #aaa;
            border-radius: 8px;
            object-fit: contain;
            cursor: grab;
            scroll-snap-align: start;
            background: #fff;
            padding: 4px;
        }

        #room {
            width: 100%;
            max-width: 1000px;
            height: 500px;
            margin: auto;
            border: 2px dashed #999;
            background: url('images/room-bg.jpg') center/cover no-repeat;
            border-radius: 12px;
            position: relative;
        }

        .drop-item {
            width: 90px;
            height: 90px;
            position: absolute;
            cursor: move;
            object-fit: contain;
        }

        #actions {
            text-align: center;
            margin-top: 25px;
        }

        button {
            padding: 10px 20px;
            margin: 0 12px;
            background-color: #5a2e1b;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #3e1d0d;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            #room {
                height: 380px;
            }

            .product, .drop-item {
                width: 70px;
                height: 70px;
            }

            button {
                font-size: 15px;
                padding: 8px 16px;
            }
        }

        @media (max-width: 480px) {
            #room {
                height: 300px;
            }

            .product, .drop-item {
                width: 60px;
                height: 60px;
            }

            button {
                font-size: 14px;
                padding: 6px 12px;
                margin: 6px 6px;
            }
        }
    </style>
</head>
<body>

<h2>🛋️ Design Your Look</h2>

<div id="product-bar">
    <?php while ($row = $result->fetch_assoc()): ?>
        <img src="<?= htmlspecialchars($row['Image_URL']) ?>"
             class="product"
             draggable="true"
             data-id="<?= $row['ProductID'] ?>"
             alt="<?= htmlspecialchars($row['Name']) ?>">
    <?php endwhile; ?>
</div>

<div id="room" ondragover="allowDrop(event)" ondrop="drop(event)"></div>

<div id="actions">
    <button onclick="buyAllItems()">🛒 Buy All Items</button>
    <button onclick="clearRoom()">❌ Clear Room</button>
    <a href="userprofile.php"><button>← Back to Dashboard</button></a>
</div>

<script>
    let draggedItem = null;
    let isFromProductBar = false;
    let droppedItems = [];

    // Handle drag from product bar
    document.querySelectorAll('.product').forEach(item => {
        item.addEventListener('dragstart', e => {
            draggedItem = e.target;
            isFromProductBar = true;
        });
    });

    function allowDrop(e) {
        e.preventDefault();
    }

    function drop(e) {
        e.preventDefault();
        if (!draggedItem) return;

        const offsetX = e.offsetX - 45;
        const offsetY = e.offsetY - 45;

        if (isFromProductBar) {
            // Clone item for new drop
            const newItem = draggedItem.cloneNode(true);
            newItem.classList.add('drop-item');
            newItem.style.left = `${offsetX}px`;
            newItem.style.top = `${offsetY}px`;
            newItem.setAttribute('draggable', 'true');

            newItem.addEventListener('dragstart', ev => {
                draggedItem = newItem;
                isFromProductBar = false;
            });

            newItem.addEventListener('dblclick', () => {
                newItem.remove();
                const id = newItem.getAttribute('data-id');
                const index = droppedItems.indexOf(id);
                if (index !== -1) droppedItems.splice(index, 1);
            });

            document.getElementById('room').appendChild(newItem);
            droppedItems.push(draggedItem.getAttribute('data-id'));

        } else {
            // Move existing dropped item
            draggedItem.style.left = `${offsetX}px`;
            draggedItem.style.top = `${offsetY}px`;
        }

        draggedItem = null;
    }

    function buyAllItems() {
        if (droppedItems.length === 0) {
            alert("You haven't added anything to your room yet!");
            return;
        }
        const uniqueIds = [...new Set(droppedItems)];
        const query = uniqueIds.map(id => `ids[]=${id}`).join('&');
        window.location.href = `add_to_cart.php?${query}`;
    }

    function clearRoom() {
        document.getElementById('room').innerHTML = "";
        droppedItems = [];
    }
</script>
</body>
</html>
