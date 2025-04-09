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

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];

// Görüntülenme sayısını artır
$sql = "UPDATE products SET view_count = view_count + 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();

// Ürün bilgilerini çek
$sql = "SELECT p.*, c.name as category_name FROM products p 
        JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $product['name']; ?> - Ütopya</title>
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
        }

        .back-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #ff5722;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 10px;
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

        img {
            width: 100%;
            max-width: 400px;
            height: auto;
            border-radius: 8px;
        }

        button {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #0056b3;
        }
    </style>
</head>

<body>
    <header>
        <h1>Ütopya</h1>
        <a href="index.php" class="back-btn">Ana Sayfaya Dön</a>
    </header>

    <main>
        <h2><?php echo $product['name']; ?></h2>
        <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        <p>Kategori: <?php echo $product['category_name']; ?></p>
        <p>Fiyat: <?php echo $product['price']; ?>₺</p>
        <p>Görüntülenme: <?php echo $product['view_count']; ?></p>
        <button onclick="addToCart('<?php echo $product['name']; ?>', <?php echo $product['price']; ?>)">Sepete
            Ekle</button>
    </main>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        function addToCart(productName, productPrice) {
            cart.push({ name: productName, price: productPrice });
            localStorage.setItem('cart', JSON.stringify(cart));
            alert(`${productName} sepete eklendi!`);
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>