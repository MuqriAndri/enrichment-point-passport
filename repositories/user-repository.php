<?php
class userRepository {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getUserByIC($userIC) {
        $sql = "SELECT 
                user_id, 
                user_ic, 
                user_email, 
                full_name, 
                password, 
                profile_picture,
                role,
                school,
                student_id,
                programme,
                intake,
                group_code,
                enrichment_point
            FROM profiles.users 
            WHERE user_ic = :user_ic";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(":user_ic", $userIC, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
    
    // Add other user-related database operations
}