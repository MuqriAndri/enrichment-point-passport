<?php
/** 
 * This controller handles the process of students joining clubs.
 * It provides functions to display available clubs, check if a student
 * is already a member, and join a club.
 */

class ClubJoinController {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    /**
     * Get all active clubs
     * 
     * @return array Array of active clubs
     */
    public function getActiveClubs() {
        try {
            $query = "SELECT club_id, club_name, category, description 
                      FROM clubs 
                      WHERE status = 'Active' 
                      ORDER BY club_name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // Log the error
            error_log("Error fetching clubs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Check if a student is already a member of a specific club
     * 
     * @param int $clubId The club ID
     * @param int $userId The user ID
     * @return bool True if already a member, false otherwise
     */
    public function isClubMember($clubId, $userId) {
        try {
            $query = "SELECT member_id FROM club_members 
                      WHERE club_id = :club_id 
                      AND user_id = :user_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            error_log("Error checking club membership: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get all clubs that a student is a member of
     * 
     * @param int $userId The user ID
     * @return array Array of clubs the student is a member of
     */
    public function getUserClubs($userId) {
        try {
            $query = "SELECT c.club_id, c.club_name, c.category, cm.join_date, cm.status 
                      FROM clubs c
                      JOIN club_members cm ON c.club_id = cm.club_id
                      WHERE cm.user_id = :user_id
                      ORDER BY c.club_name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching user clubs: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Join a club
     * 
     * @param int $clubId The club ID
     * @param int $userId The user ID
     * @param string $studentId The student ID
     * @return array Response array with status and message
     */
    public function joinClub($clubId, $userId, $studentId) {
        try {
            // Begin transaction
            $this->conn->beginTransaction();
            
            // Check if already a member
            if ($this->isClubMember($clubId, $userId)) {
                $this->conn->rollBack();
                return [
                    'status' => 'error',
                    'message' => 'You are already a member of this club'
                ];
            }
            
            // Get current date
            $currentDate = date('Y-m-d');
            
            // Insert into club_members
            $query = "INSERT INTO club_members (club_id, user_id, student_id, join_date, status, role_id) 
                      VALUES (:club_id, :user_id, :student_id, :join_date, 'Active', 5)";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':join_date', $currentDate);
            $stmt->execute();
            
            // Commit transaction
            $this->conn->commit();
            
            return [
                'status' => 'success',
                'message' => 'Successfully joined the club'
            ];
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->conn->rollBack();
            error_log("Error joining club: " . $e->getMessage());
            
            return [
                'status' => 'error',
                'message' => 'An error occurred while trying to join the club'
            ];
        }
    }
    
    /**
     * Get club details
     * 
     * @param int $clubId The club ID
     * @return array|null Club details or null if not found
     */
    public function getClubDetails($clubId) {
        try {
            $query = "SELECT c.*, 
                      (SELECT COUNT(*) FROM club_members WHERE club_id = c.club_id AND status = 'Active') as member_count 
                      FROM clubs c 
                      WHERE c.club_id = :club_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching club details: " . $e->getMessage());
            return null;
        }
    }
}