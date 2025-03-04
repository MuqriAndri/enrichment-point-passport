<?php
class clubRepository {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function getAllActiveClubs() {
        $sql = "SELECT 
            c.club_id,
            c.club_name,
            c.category,
            COUNT(DISTINCT cm.student_id) as member_count
        FROM cca.clubs c
        LEFT JOIN cca.club_members cm ON c.club_id = cm.club_id 
            AND cm.status = 'Active'
        WHERE c.status = 'Active'
        GROUP BY c.club_id, c.club_name, c.category
        ORDER BY c.category, c.club_name";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getUserMemberships($studentId) {
        $sql = "SELECT club_id 
                FROM cca.club_members 
                WHERE student_id = :student_id 
                AND status = 'Active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    public function getClubDetails($clubName) {
        $sql = "SELECT 
                c.club_id,
                c.club_name,
                c.category,
                c.description,
                c.meeting_schedule,
                c.location,
                c.advisor,
                c.president,
                c.contact_email,
                c.membership_fee,
                COUNT(DISTINCT cm.student_id) as member_count
            FROM cca.clubs c
            LEFT JOIN cca.club_members cm ON c.club_id = cm.club_id 
                AND cm.status = 'Active'
            WHERE c.club_name = :club_name
            GROUP BY c.club_id, c.club_name, c.category";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':club_name', $clubName, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        return null;
    }
    
    public function isUserMemberOfClub($studentId, $clubId) {
        $sql = "SELECT club_id 
                FROM cca.club_members 
                WHERE student_id = :student_id 
                AND club_id = :club_id
                AND status = 'Active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':club_id', $clubId);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }
    
    // Add other club-related database operations
}