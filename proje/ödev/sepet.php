<?php
session_start();
$conn = new mysqli("localhost", "root", "", "utopia_store");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if (!isset($_SESSION['user_id'])) {
    header("Location: kullancıgirişkayit.php");
    exit();
}

if (isset($_POST['purchase'])) {
    $cart = json_decode($_POST['cart_data'], true);
    if (!empty($cart)) {
        foreach ($cart as $item) {
            $product_name = $item['name'];
            $price = $item['price'];
            $quantity = 1;

            $sql = "SELECT id FROM products WHERE name = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $product_name);
            $stmt->execute();
            $result = $stmt->get_result();
            $product = $result->fetch_assoc();

            if ($product) {
                $product_id = $product['id'];
                $total_amount = $price * $quantity;
                $sql = "INSERT INTO sales (product_id, quantity, total_amount) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iid", $product_id, $quantity, $total_amount);
                $stmt->execute();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sepet - Ütopya</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }

        header {
            background: #1c1c1c;
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .logo h1 {
            margin: 0;
        }

        .back-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #ff5722;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
            transition: 0.3s;
        }

        .back-btn:hover {
            background: #004099;
        }

        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            color: #333;
        }

        .cart-items {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .cart-item h3 {
            margin: 0;
            font-size: 18px;
            color: #333;
        }

        .cart-item p {
            margin: 5px 0 0;
            color: #666;
            font-size: 16px;
        }

        .remove-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: 0.3s;
        }

        .remove-btn:hover {
            background: #b02a37;
        }

        .clear-cart-btn {
            display: block;
            width: 100%;
            padding: 12px;
            background: #ff9800;
            color: white;
            border: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 15px;
            transition: 0.3s;
        }

        .clear-cart-btn:hover {
            background: #e68900;
        }

        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background: white;
            padding: 15px;
            box-shadow: 0px -2px 5px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .purchase-btn {
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            font-size: 18px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
        }

        .purchase-btn:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .confirmation-message {
            text-align: center;
            background: #28a745;
            color: white;
            padding: 15px;
            font-size: 18px;
            font-weight: bold;
            border-radius: 5px;
            display: none;
            margin-top: 15px;
        }

        @media (max-width: 768px) {
            main {
                width: 90%;
            }

            .cart-item {
                flex-direction: column;
                align-items: flex-start;
            }

            .remove-btn {
                margin-top: 10px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <h1>Ütopya</h1>
        </div>
        <a href="index.php" class="back-btn">Ana Sayfaya Dön</a>
    </header>

    <main>
        <h2>Sepetiniz</h2>
        <div class="cart-items" id="cartItems"></div>
        <button class="clear-cart-btn" id="clearCartBtn">Tümünü Temizle</button>
    </main>

    <div class="confirmation-message" id="confirmationMessage">
        Satın alma işlemi başarılı!
    </div>

    <footer>
        <button class="purchase-btn" id="purchaseBtn">Satın Al (0₺)</button>
    </footer>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        function displayCartItems() {
            const cartItemsContainer = document.getElementById('cartItems');
            const purchaseBtn = document.getElementById('purchaseBtn');
            cartItemsContainer.innerHTML = '';
            let totalPrice = 0;
            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<p>Sepetiniz boş.</p>';
                purchaseBtn.innerText = 'Satın Al (0₺)';
            } else {
                cart.forEach((item, index) => {
                    totalPrice += item.price;
                    const cartItem = document.createElement('div');
                    cartItem.classList.add('cart-item');
                    cartItem.innerHTML = `
                        <div>
                            <h3>${item.name}</h3>
                            <p>Fiyat: ${item.price}₺</p>
                        </div>
                        <button class="remove-btn" onclick="removeFromCart(${index})">Ürünü Kaldır</button>
                    `;
                    cartItemsContainer.appendChild(cartItem);
                });
                purchaseBtn.innerText = `Satın Al (${totalPrice}₺)`;
            }
        }
        function removeFromCart(index) {
            cart.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cart));
            displayCartItems();
        }
        document.getElementById('clearCartBtn').addEventListener('click', function () {
            cart = [];
            localStorage.removeItem('cart');
            displayCartItems();
        });
        document.getElementById('purchaseBtn').addEventListener('click', function () {
            if (cart.length === 0) {
                alert("Sepetiniz boş! Lütfen ürün ekleyin.");
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            const input = document.createElement('input');
            input.name = 'cart_data';
            input.value = JSON.stringify(cart);
            form.appendChild(input);
            const purchaseInput = document.createElement('input');
            purchaseInput.name = 'purchase';
            purchaseInput.value = 'true';
            form.appendChild(purchaseInput);
            document.body.appendChild(form);
            form.submit();
            localStorage.removeItem('cart');
            cart = [];
            const confirmationMessage = document.getElementById('confirmationMessage');
            confirmationMessage.style.display = 'block';
            setTimeout(() => {
                confirmationMessage.style.display = 'none';
            }, 3000);
            displayCartItems();
        });
        window.onload = function () {
            displayCartItems();
        };
    </script>
</body>

</html>

<?php $conn->close(); ?>