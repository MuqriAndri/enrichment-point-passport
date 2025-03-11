<?php
class clubRepository
{
    private $pdo;

    public function __construct($pdo)
    {
        $this->pdo = $pdo;
    }

    public function getAllActiveClubs()
    {
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

    public function getUserMemberships($studentId)
    {
        $sql = "SELECT club_id 
                FROM cca.club_members 
                WHERE student_id = :student_id 
                AND status = 'Active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getClubDetails($clubName)
    {
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

    public function isUserMemberOfClub($studentId, $clubId)
    {
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

    /**
     * Get club details by club ID
     * 
     * @param int $clubId The club ID
     * @return array|null Club details or null if not found
     */
    public function getClubDetailsById($clubId)
    {
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
        WHERE c.club_id = :club_id
        GROUP BY c.club_id, c.club_name, c.category";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':club_id', $clubId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }

        return null;
    }

    /**
     * Add a student to a club
     * 
     * @param int $clubId The club ID
     * @param int $userId The user ID
     * @param string $studentId The student ID
     * @return bool True if successful, false otherwise
     */
    public function joinClub($clubId, $userId, $studentId)
    {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();

            // Check if already a member
            if ($this->isUserMemberOfClub($studentId, $clubId)) {
                $this->pdo->rollBack();
                return false;
            }

            // Get current date
            $currentDate = date('Y-m-d');

            // Insert into club_members
            $sql = "INSERT INTO cca.club_members (club_id, user_id, student_id, join_date, status, role_id) 
                VALUES (:club_id, :user_id, :student_id, :join_date, 'Active', 5)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':join_date', $currentDate);
            $stmt->execute();

            // Commit transaction
            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Error joining club: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Submit a club membership application
     * 
     * @param int $clubId The club ID
     * @param int $userId The user ID
     * @param string $studentId The student ID from session
     * @param array $studentInfo Student information from form
     * @return bool True if successful, false otherwise
     */
    public function submitClubApplication($clubId, $userId, $studentId, $studentInfo)
    {
        try {
            // Begin transaction
            $this->pdo->beginTransaction();

            // Check if already a member
            if ($this->isUserMemberOfClub($studentId, $clubId)) {
                $this->pdo->rollBack();
                return false;
            }

            // Get current date
            $currentDate = date('Y-m-d');

            // Insert into club_members with 'Pending' status instead of 'Active'
            $query = "INSERT INTO club_members (
                    club_id, 
                    user_id, 
                    student_id, 
                    join_date, 
                    status, 
                    role_id,
                    full_name,
                    application_student_id,
                    student_email,
                    school,
                    course,
                    group_code,
                    intake,
                    phone_number
                ) VALUES (
                    :club_id, 
                    :user_id, 
                    :student_id, 
                    :join_date, 
                    'Pending', 
                    5,
                    :full_name,
                    :application_student_id,
                    :student_email,
                    :school,
                    :course,
                    :group_code,
                    :intake,
                    :phone_number
                )";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':join_date', $currentDate);
            $stmt->bindParam(':full_name', $studentInfo['full_name']);
            $stmt->bindParam(':application_student_id', $studentInfo['student_id']);
            $stmt->bindParam(':student_email', $studentInfo['student_email']);
            $stmt->bindParam(':school', $studentInfo['school']);
            $stmt->bindParam(':course', $studentInfo['course']);
            $stmt->bindParam(':group_code', $studentInfo['group_code']);
            $stmt->bindParam(':intake', $studentInfo['intake']);
            $stmt->bindParam(':phone_number', $studentInfo['phone_number']);
            $stmt->execute();

            // Commit transaction
            $this->pdo->commit();

            return true;
        } catch (PDOException $e) {
            // Rollback transaction on error
            $this->pdo->rollBack();
            error_log("Error submitting club application: " . $e->getMessage());
            return false;
        }
    }
}
