<?php
session_start();
$conn = new mysqli("localhost", "root", "", "utopia_store");

if ($conn->connect_error) {
    die("Bağlantı hatası: " . $conn->connect_error);
}

$sql = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($sql);

if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            header("Location: index.php");
            exit();
        } else {
            $login_error = "Hatalı şifre!";
        }
    } else {
        $login_error = "Kullanıcı bulunamadı!";
    }
}

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        $register_success = "Kayıt başarılı! Giriş yapabilirsiniz.";
    } else {
        $register_error = "Kayıt başarısız! Kullanıcı adı zaten alınmış olabilir.";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kullanıcı Girişi ve Kayıt - Ütopya</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        input {
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
        }

        button:hover {
            background-color: #0056b3;
        }

        .error {
            color: red;
            text-align: center;
            margin: 10px 0;
        }

        .success {
            color: green;
            text-align: center;
            margin: 10px 0;
        }

        .toggle {
            text-align: center;
            margin-top: 20px;
        }

        .toggle a {
            color: #007bff;
            text-decoration: none;
        }

        .toggle a:hover {
            text-decoration: underline;
        }
    </style>
</head>

<body>
    <div class="container">
        <div id="login-form">
            <h2>Giriş Yap</h2>
            <?php if (isset($login_error))
                echo "<p class='error'>$login_error</p>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Kullanıcı Adı" required>
                <input type="password" name="password" placeholder="Şifre" required>
                <button type="submit" name="login">Giriş Yap</button>
            </form>
            <div class="toggle">
                <p>Hesabınız yok mu? <a href="#" onclick="showRegister()">Kayıt Ol</a></p>
            </div>
        </div>

        <div id="register-form" style="display: none;">
            <h2>Kayıt Ol</h2>
            <?php if (isset($register_error))
                echo "<p class='error'>$register_error</p>"; ?>
            <?php if (isset($register_success))
                echo "<p class='success'>$register_success</p>"; ?>
            <form method="POST">
                <input type="text" name="username" placeholder="Kullanıcı Adı" required>
                <input type="password" name="password" placeholder="Şifre" required>
                <button type="submit" name="register">Kayıt Ol</button>
            </form>
            <div class="toggle">
                <p>Hesabınız var mı? <a href="#" onclick="showLogin()">Giriş Yap</a></p>
            </div>
        </div>
    </div>

    <script>
        function showRegister() {
            document.getElementById('login-form').style.display = 'none';
            document.getElementById('register-form').style.display = 'block';
        }
        function showLogin() {
            document.getElementById('login-form').style.display = 'block';
            document.getElementById('register-form').style.display = 'none';
        }
    </script>
</body>

</html>

<?php $conn->close(); ?>