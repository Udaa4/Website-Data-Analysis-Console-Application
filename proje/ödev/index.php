<?php
session_start();
$conn = new mysqli("localhost", "root", "", "utopia_store");
if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: kullancıgirişkayit.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: kullancıgirişkayit.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mağaza - Ütopya</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            line-height: 1.6;
        }

        header {
            background-color: #1c1c1c;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        header .logo h1 {
            font-size: 2.5rem;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
        }

        header nav {
            display: flex;
            align-items: center;
        }

        header nav a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            font-size: 1.1rem;
            font-weight: 600;
        }

        header nav a:hover {
            color: #f4f4f4;
        }

        header .dropdown select {
            padding: 10px;
            background-color: #333;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
        }

        main {
            margin: 20px;
        }

        .category-products {
            margin-bottom: 40px;
        }

        .category-products h2 {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
        }

        .product-card {
            background-color: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-align: center;
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 8px;
        }

        .product-card h3 a {
            font-size: 1.2rem;
            margin: 15px 0;
            color: #333;
            text-decoration: none;
        }

        .product-card h3 a:hover {
            color: #007bff;
        }

        .product-card p {
            font-size: 1rem;
            color: #777;
            margin-bottom: 20px;
        }

        .product-card button {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .product-card button:hover {
            background-color: #0056b3;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
        }

        header nav a.geri,
        header nav a.back-btn {
            background-color: #ff5722;
            padding: 10px 20px;
            border-radius: 5px;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        header nav a.geri:hover,
        header nav a.back-btn:hover {
            background-color: #e64a19;
        }

        footer {
            background-color: #1c1c1c;
            color: white;
            text-align: center;
            padding: 20px;
            font-size: 1rem;
            position: relative;
            bottom: 0;
            width: 100%;
        }

        @media (max-width: 768px) {
            .product-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 480px) {
            header .logo h1 {
                font-size: 1.8rem;
            }

            header nav {
                flex-direction: column;
                align-items: flex-start;
            }

            header nav a {
                margin: 10px 0;
            }

            .product-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="logo">
            <h1>BİLGİSAYAR TEKNOLOJİ MAĞAZASI</h1>
        </div>
        <nav>
            <div class="dropdown">
                <select id="categorySelect" onchange="saveCategoryAndShow(this.value)">
                    <option value="biraz">Kategoriler</option>
                    <option value="cat1">ANAKART</option>
                    <option value="cat2">İŞLEMCİ</option>
                    <option value="cat3">EKRAN KARTI</option>
                    <option value="cat4">MONİTÖR</option>
                    <option value="cat5">RAM</option>
                </select>
            </div>
            <a href="sepet.php" class="geri">Sepetim</a>
            <a href="index.php?logout=true" class="back-btn">Çıkış Yap</a>
        </nav>
    </header>

    <main>
        <section id="biraz" class="category-products">
            <h2>Öne Çıkan Ürünler</h2>
            <div class="product-grid">
                <?php
                $sql = "SELECT * FROM products LIMIT 4";
                $result = $conn->query($sql);
                while ($row = $result->fetch_assoc()) {
                    echo "<div class='product-card'>";
                    echo "<img src='{$row['image']}' alt='{$row['name']}'>";
                    echo "<h3><a href='product_detail.php?id={$row['id']}'>{$row['name']}</a></h3>";
                    echo "<p>Fiyat: {$row['price']}₺</p>";
                    echo "<button onclick=\"addToCart('{$row['name']}', {$row['price']})\">Sepete Ekle</button>";
                    echo "</div>";
                }
                ?>
            </div>
        </section>

        <?php
        $categories = $conn->query("SELECT * FROM categories");
        while ($cat = $categories->fetch_assoc()) {
            echo "<section id='cat{$cat['id']}' class='category-products'>";
            echo "<h2>{$cat['name']}</h2>";
            echo "<div class='product-grid'>";
            $products = $conn->query("SELECT * FROM products WHERE category_id = {$cat['id']}");
            while ($prod = $products->fetch_assoc()) {
                echo "<div class='product-card'>";
                echo "<img src='{$prod['image']}' alt='{$prod['name']}'>";
                echo "<h3><a href='product_detail.php?id={$prod['id']}'>{$prod['name']}</a></h3>";
                echo "<p>Fiyat: {$prod['price']}₺</p>";
                echo "<button onclick=\"addToCart('{$prod['name']}', {$prod['price']})\">Sepete Ekle</button>";
                echo "</div>";
            }
            echo "</div>";
            echo "</section>";
        }
        ?>
    </main>

    <footer>
        <p>© 2025 Ütopya E-Ticaret</p>
    </footer>

    <script>
        let cart = JSON.parse(localStorage.getItem('cart')) || [];
        function addToCart(productName, productPrice) {
            cart.push({ name: productName, price: productPrice });
            localStorage.setItem('cart', JSON.stringify(cart));
            alert(`${productName} sepete eklendi!`);
        }
        function saveCategoryAndShow(category) {
            localStorage.setItem('selectedCategory', category);
            showCategory(category);
        }
        function showCategory(category) {
            var categories = document.querySelectorAll('.category-products');
            categories.forEach(function (cat) {
                cat.style.display = 'none';
            });
            if (category !== "none") {
                var selectedCategory = document.getElementById(category);
                if (selectedCategory) {
                    selectedCategory.style.display = 'block';
                }
            }
        }
        window.onload = function () {
            const selectedCategory = localStorage.getItem('selectedCategory');
            if (selectedCategory) {
                document.getElementById('categorySelect').value = selectedCategory;
                showCategory(selectedCategory);
            }
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>