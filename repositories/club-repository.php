<?php
class clubRepository
{
    private $pdo;
    private $profilesDB;

    public function __construct($ccaDB, $profilesDB = null)
    {
        $this->pdo = $ccaDB; // Main connection for CCA operations
        $this->profilesDB = $profilesDB; // For any operations that need profiles data
    }

    public function getAllActiveClubs()
    {
        $sql = "SELECT 
            c.club_id,
            c.club_name,
            c.category,
            COUNT(DISTINCT cm.student_id) as member_count
        FROM clubs c
        LEFT JOIN club_members cm ON c.club_id = cm.club_id 
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
                FROM club_members 
                WHERE student_id = :student_id 
                AND status = 'Active'";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getUserDetails($userId)
    {
        // Make sure we have a connection to the profiles database
        if (!$this->profilesDB) {
            error_log("No profiles database connection available");
            return null;
        }

        try {
            // Use the profiles database connection with the fully qualified table name
            $sql = "SELECT 
                user_id, 
                user_ic, 
                user_email, 
                full_name, 
                role, 
                school, 
                student_id, 
                programme, 
                intake, 
                group_code
            FROM profile.users 
            WHERE user_id = :user_id";

            $stmt = $this->profilesDB->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }

            return null;
        } catch (PDOException $e) {
            error_log("Error getting user details: " . $e->getMessage());
            return null;
        }
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
            FROM clubs c
            LEFT JOIN club_members cm ON c.club_id = cm.club_id 
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
                FROM club_members 
                WHERE student_id = :student_id 
                AND club_id = :club_id
                AND status IN ('Active', 'Pending')";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':student_id', $studentId);
        $stmt->bindParam(':club_id', $clubId);
        $stmt->execute();
        return ($stmt->rowCount() > 0);
    }

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
        FROM clubs c
        LEFT JOIN club_members cm ON c.club_id = cm.club_id 
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

    public function submitClubApplication($clubId, $userId, $studentId, $studentInfo)
    {
        try {
            // Enhanced debugging
            error_log("Starting club application with data: " . json_encode([
                'clubId' => $clubId,
                'userId' => $userId,
                'studentId' => $studentId,
                'studentInfo' => $studentInfo
            ]));

            // Begin transaction
            $this->pdo->beginTransaction();
            error_log("Transaction started");

            // Check if already a member
            if ($this->isUserMemberOfClub($studentId, $clubId)) {
                error_log("User $studentId is already a member of club $clubId");
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'You are already a member of this club'
                ];
            }

            // Check club count
            $clubCountSql = "SELECT COUNT(*) FROM club_members WHERE user_id = :user_id";
            $clubCountStmt = $this->pdo->prepare($clubCountSql);
            $clubCountStmt->bindParam(':user_id', $userId);
            $clubCountStmt->execute();
            $clubCount = $clubCountStmt->fetchColumn();
            error_log("User $userId currently has $clubCount clubs");

            if ($clubCount >= 3) {
                error_log("User $userId already has $clubCount clubs (maximum is 3)");
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Students cannot join more than 3 clubs'
                ];
            }

            // Current date
            $currentDate = date('Y-m-d');

            // VERY SIMPLE INSERT - absolutely no joins or subqueries
            $sql = "INSERT INTO club_members (
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

            // Log the SQL and parameters
            error_log("Preparing SQL: $sql");
            error_log("With parameters: " . json_encode([
                'club_id' => $clubId,
                'user_id' => $userId,
                'student_id' => $studentId,
                'join_date' => $currentDate,
                'full_name' => $studentInfo['full_name'] ?? null,
                'application_student_id' => $studentInfo['student_id'] ?? null,
                'student_email' => $studentInfo['student_email'] ?? null,
                'school' => $studentInfo['school'] ?? null,
                'course' => $studentInfo['course'] ?? null,
                'group_code' => $studentInfo['group_code'] ?? null,
                'intake' => $studentInfo['intake'] ?? null,
                'phone_number' => $studentInfo['phone_number'] ?? null
            ]));

            $stmt = $this->pdo->prepare($sql);

            // Make sure PDO throws exceptions for errors
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Bind parameters one by one for maximum clarity
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':join_date', $currentDate);

            // Use null coalescing operator for optional fields to avoid undefined index notices
            $fullName = $studentInfo['full_name'] ?? null;
            $appStudentId = $studentInfo['student_id'] ?? null;
            $studentEmail = $studentInfo['student_email'] ?? null;
            $school = $studentInfo['school'] ?? null;
            $course = $studentInfo['course'] ?? null;
            $groupCode = $studentInfo['group_code'] ?? null;
            $intake = $studentInfo['intake'] ?? null;
            $phoneNumber = $studentInfo['phone_number'] ?? null;

            $stmt->bindParam(':full_name', $fullName);
            $stmt->bindParam(':application_student_id', $appStudentId);
            $stmt->bindParam(':student_email', $studentEmail);
            $stmt->bindParam(':school', $school);
            $stmt->bindParam(':course', $course);
            $stmt->bindParam(':group_code', $groupCode);
            $stmt->bindParam(':intake', $intake);
            $stmt->bindParam(':phone_number', $phoneNumber);

            // Execute and check result
            error_log("Executing prepared statement");
            $result = $stmt->execute();

            if (!$result) {
                $error = $stmt->errorInfo();
                error_log("Error in club application: " . json_encode($error));
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Failed to submit club application: ' . ($error[2] ?? 'Unknown error')
                ];
            }

            // Get the newly inserted member ID
            $memberId = $this->pdo->lastInsertId();
            error_log("New club_members record inserted with ID: $memberId");

            // Double-check the inserted data
            $checkSql = "SELECT * FROM club_members WHERE member_id = :member_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindParam(':member_id', $memberId);
            $checkStmt->execute();
            $insertedData = $checkStmt->fetch(PDO::FETCH_ASSOC);
            error_log("Verification of inserted data: " . json_encode($insertedData));

            // Commit transaction
            error_log("Committing transaction");
            $this->pdo->commit();
            error_log("Club application successful!");
            return [
                'success' => true,
                'message' => 'Club application submitted successfully',
                'member_id' => $memberId
            ];
        } catch (PDOException $e) {
            // Rollback on error
            if ($this->pdo->inTransaction()) {
                error_log("Rolling back transaction due to exception");
                $this->pdo->rollBack();
            }

            error_log("PDO Exception in club application: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Error trace: " . $e->getTraceAsString());

            // Check for the specific error from our trigger
            if (strpos($e->getMessage(), 'Students cannot join more than 3 clubs') !== false) {
                return [
                    'success' => false,
                    'message' => 'Students cannot join more than 3 clubs'
                ];
            }

            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            // Catch any other exceptions
            if ($this->pdo->inTransaction()) {
                error_log("Rolling back transaction due to general exception");
                $this->pdo->rollBack();
            }

            error_log("General Exception in club application: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());

            return [
                'success' => false,
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ];
        }
    }
}
