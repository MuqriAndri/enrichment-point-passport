<?php
session_start();
require_once "../database/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_ic = trim($_POST["user_ic"]);
    $password = trim($_POST["password"]);
    
    $sql = "SELECT user_id, user_ic, full_name, password_hash FROM users WHERE user_ic = :user_ic";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_ic", $user_ic, PDO::PARAM_STR);
        $stmt->execute();
        
        if($stmt->rowCount() == 1) {
            $row = $stmt->fetch();
            
            // For testing with temporary password
            if($password === $row["password_hash"]) {
                // Login successful
                session_regenerate_id();
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["user_ic"] = $row["user_ic"];
                $_SESSION["full_name"] = $row["full_name"];
                
                header("Location: ../dashboard.php");
                exit;
            }
            
            /* Use this for production with proper password hashing:
            if(password_verify($password, $row["password_hash"])) {
                session_regenerate_id();
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["user_ic"] = $row["user_ic"];
                $_SESSION["full_name"] = $row["full_name"];
                
                header("Location: ../dashboard.php");
                exit;
            }
            */
        }
        
        $_SESSION["error"] = "Invalid IC number or password.";
        header("Location: ../templates/login-page.php");
        exit;
        
    } catch(PDOException $e) {
        $_SESSION["error"] = "Something went wrong. Please try again later.";
        header("Location: ../templates/login-page.php");
        exit;
    }
}
?>