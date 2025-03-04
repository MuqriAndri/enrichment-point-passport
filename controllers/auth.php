<?php
function handleLogin($pdo) {
    $userIC = trim($_POST["user_ic"] ?? '');
    $password = trim($_POST["password"] ?? '');
    
    try {
        $userRepo = new UserRepository($pdo);
        $user = $userRepo->getUserByIC($userIC);
        
        if ($user && $password === $user["password"]) {
            session_regenerate_id(true);

            // Store all user data in session
            $_SESSION["user_id"] = $user["user_id"];
            $_SESSION["user_ic"] = $user["user_ic"];
            $_SESSION["user_email"] = $user["user_email"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["role"] = $user["role"];
            $_SESSION["school"] = $user["school"];

            // Store profile picture if exists
            if (!empty($user["profile_picture"])) {
                $_SESSION["profile_picture"] = $user["profile_picture"];
            }

            // Store student-specific information only if user is a student
            if ($user["role"] === "student") {
                $_SESSION["student_id"] = $user["student_id"];
                $_SESSION["programme"] = $user["programme"];
                $_SESSION["intake"] = $user["intake"];
                $_SESSION["group_code"] = $user["group_code"];
                $_SESSION["enrichment_point"] = $user["enrichment_point"];
            }

            header("Location: " . BASE_URL . "/dashboard");
            exit();
        }
        
        $_SESSION["error"] = "Invalid IC number or password.";
        header("Location: " . BASE_URL);
        exit();
    } catch (PDOException $e) {
        $_SESSION["error"] = "Something went wrong. Please try again later.";
        header("Location: " . BASE_URL);
        exit();
    }
}