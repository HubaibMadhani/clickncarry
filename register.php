<?php
include 'includes/db.php';
if(isset($_POST['register'])){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "INSERT INTO users(name,email,password) VALUES('$name','$email','$password')";
    if($conn->query($sql)){
        echo "Registered successfully. <a href='login.php'>Login</a>";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<form method="POST">
    Name: <input type="text" name="name" required><br>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" name="register" value="Register">
</form>
