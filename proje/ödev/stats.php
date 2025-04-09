<?php
$conn = new mysqli("localhost", "root", "", "utopia_store");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

// En çok satılan ürünler
$top_sold = $conn->query("SELECT p.name, SUM(s.quantity) as total_sold 
                         FROM sales s 
                         JOIN products p ON s.product_id = p.id 
                         GROUP BY p.id 
                         ORDER BY total_sold DESC 
                         LIMIT 3");

// En az satılan ürünler
$least_sold = $conn->query("SELECT p.name, SUM(s.quantity) as total_sold 
                           FROM sales s 
                           JOIN products p ON s.product_id = p.id 
                           GROUP BY p.id 
                           ORDER BY total_sold ASC 
                           LIMIT 3");

// En çok görüntülenen ürünler
$top_viewed = $conn->query("SELECT name, view_count 
                           FROM products 
                           ORDER BY view_count DESC 
                           LIMIT 3");

// En çok tercih edilen kategori
$top_category = $conn->query("SELECT c.name, SUM(s.quantity) as total_sold 
                             FROM sales s 
                             JOIN products p ON s.product_id = p.id 
                             JOIN categories c ON p.category_id = c.id 
                             GROUP BY c.id 
                             ORDER BY total_sold DESC 
                             LIMIT 1");

// Günlük ciro
$daily_revenue = $conn->query("SELECT SUM(total_amount) as revenue 
                              FROM sales 
                              WHERE DATE(sale_date) = CURDATE()");

$data = [
    "top_sold" => $top_sold->fetch_all(MYSQLI_ASSOC),
    "least_sold" => $least_sold->fetch_all(MYSQLI_ASSOC),
    "top_viewed" => $top_viewed->fetch_all(MYSQLI_ASSOC),
    "top_category" => $top_category->fetch_assoc(),
    "daily_revenue" => $daily_revenue->fetch_assoc()['revenue']
];

header('Content-Type: application/json');
echo json_encode($data);

$conn->close();
?>