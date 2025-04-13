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
                c.latitude,
                c.longitude,
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
            c.latitude,
            c.longitude,
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

    public function isClubOfficer($userId, $clubId)
    {
        try {
            $sql = "SELECT cm.member_id 
                    FROM club_members cm
                    WHERE cm.user_id = :user_id 
                    AND cm.club_id = :club_id 
                    AND cm.status = 'Active'
                    AND cm.role_id IN (1, 2)"; // 1 = President, 2 = Vice President
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':user_id', $userId);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            
            return ($stmt->rowCount() > 0);
        } catch (PDOException $e) {
            error_log("Error checking club officer status: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get club gallery images
     * 
     * @param int $clubId The club ID
     * @return array Array of gallery images
     */
    public function getClubGallery($clubId)
    {
        try {
            $sql = "SELECT * FROM club_gallery WHERE club_id = :club_id ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting club gallery: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get club activities
     * 
     * @param int $clubId The club ID
     * @param string $status Optional filter by status
     * @return array Array of activities
     */
    public function getClubActivities($clubId, $status = null)
    {
        try {
            $sql = "SELECT * FROM club_activities WHERE club_id = :club_id";
            
            if ($status) {
                $sql .= " AND status = :status";
            }
            
            $sql .= " ORDER BY start_datetime DESC";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            
            if ($status) {
                $stmt->bindParam(':status', $status);
            }
            
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting club activities: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get club locations
     * 
     * @param int $clubId The club ID
     * @return array Array of locations
     */
    public function getClubLocations($clubId)
    {
        try {
            $sql = "SELECT * FROM club_location WHERE club_id = :club_id AND is_active = 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting club locations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Create a new club activity
     * 
     * @param int $clubId The club ID
     * @param array $activityData Activity data (title, description, location, start_datetime, end_datetime, points_awarded, attendance_code, status)
     * @return array Array with success status and message
     */
    public function createClubActivity($clubId, $activityData)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO club_activities (
                club_id,
                title,
                description,
                location,
                start_datetime,
                end_datetime,
                points_awarded,
                attendance_code,
                status,
                created_at
            ) VALUES (
                :club_id,
                :title,
                :description,
                :location,
                :start_datetime,
                :end_datetime,
                :points_awarded,
                :attendance_code,
                :status,
                NOW()
            )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':title', $activityData['title']);
            $stmt->bindParam(':description', $activityData['description']);
            $stmt->bindParam(':location', $activityData['location']);
            $stmt->bindParam(':start_datetime', $activityData['start_datetime']);
            $stmt->bindParam(':end_datetime', $activityData['end_datetime']);
            $stmt->bindParam(':points_awarded', $activityData['points_awarded']);
            
            // Generate a random 6-character attendance code if not provided
            if (!isset($activityData['attendance_code']) || empty($activityData['attendance_code'])) {
                $activityData['attendance_code'] = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 6));
            }
            $stmt->bindParam(':attendance_code', $activityData['attendance_code']);
            
            // Default status is "Upcoming" if not provided
            $status = isset($activityData['status']) ? $activityData['status'] : 'Upcoming';
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            $activityId = $this->pdo->lastInsertId();

            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Activity created successfully',
                'activity_id' => $activityId,
                'attendance_code' => $activityData['attendance_code']
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error creating club activity: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to create activity: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get a specific club activity
     * 
     * @param int $activityId The activity ID
     * @return array|false Activity data or false on failure
     */
    public function getActivityById($activityId)
    {
        try {
            $sql = "SELECT * FROM club_activities WHERE activity_id = :activity_id";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':activity_id', $activityId);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting activity by ID: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update a club activity
     * 
     * @param int $activityId The activity ID
     * @param array $activityData Activity data to update
     * @return array Array with success status and message
     */
    public function updateActivity($activityId, $activityData)
    {
        try {
            $this->pdo->beginTransaction();

            // Build the update query dynamically based on provided fields
            $sql = "UPDATE club_activities SET updated_at = NOW()";
            $params = [];

            // Only include fields that are provided in the update
            $allowedFields = [
                'title', 'description', 'location', 'start_datetime', 
                'end_datetime', 'points_awarded', 'attendance_code', 'status'
            ];

            foreach ($allowedFields as $field) {
                if (isset($activityData[$field])) {
                    $sql .= ", $field = :$field";
                    $params[$field] = $activityData[$field];
                }
            }

            $sql .= " WHERE activity_id = :activity_id";
            $params['activity_id'] = $activityId;

            $stmt = $this->pdo->prepare($sql);
            
            // Bind all parameters
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }

            $stmt->execute();
            $rowCount = $stmt->rowCount();

            $this->pdo->commit();
            
            if ($rowCount > 0) {
                return [
                    'success' => true,
                    'message' => 'Activity updated successfully'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'No changes made or activity not found'
                ];
            }
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error updating activity: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update activity: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Delete a club activity
     * 
     * @param int $activityId The activity ID
     * @return array Array with success status and message
     */
    public function deleteActivity($activityId)
    {
        try {
            $this->pdo->beginTransaction();

            // First check if the activity exists
            $checkSql = "SELECT club_id FROM club_activities WHERE activity_id = :activity_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindParam(':activity_id', $activityId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() === 0) {
                return [
                    'success' => false,
                    'message' => 'Activity not found'
                ];
            }

            // Check if there's attendance records
            $attendanceSql = "SELECT COUNT(*) FROM club_attendance WHERE activity_id = :activity_id";
            $attendanceStmt = $this->pdo->prepare($attendanceSql);
            $attendanceStmt->bindParam(':activity_id', $activityId);
            $attendanceStmt->execute();
            $attendanceCount = $attendanceStmt->fetchColumn();

            if ($attendanceCount > 0) {
                // If there's attendance, just mark it as cancelled instead of deleting
                $updateSql = "UPDATE club_activities SET status = 'Cancelled', updated_at = NOW() WHERE activity_id = :activity_id";
                $updateStmt = $this->pdo->prepare($updateSql);
                $updateStmt->bindParam(':activity_id', $activityId);
                $updateStmt->execute();

                $this->pdo->commit();
                return [
                    'success' => true,
                    'message' => 'Activity cancelled instead of deleted due to existing attendance records'
                ];
            } else {
                // If no attendance, delete the activity
                $deleteSql = "DELETE FROM club_activities WHERE activity_id = :activity_id";
                $deleteStmt = $this->pdo->prepare($deleteSql);
                $deleteStmt->bindParam(':activity_id', $activityId);
                $deleteStmt->execute();

                $this->pdo->commit();
                return [
                    'success' => true,
                    'message' => 'Activity deleted successfully'
                ];
            }
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error deleting activity: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to delete activity: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Record attendance for an activity
     * 
     * @param int $activityId The activity ID
     * @param string $studentId The student ID
     * @param string $attendanceCode The attendance code to verify
     * @return array Array with success status and message
     */
    public function recordActivityAttendance($activityId, $studentId, $attendanceCode)
    {
        try {
            $this->pdo->beginTransaction();

            // First verify the attendance code
            $verifySql = "SELECT club_id, status FROM club_activities 
                         WHERE activity_id = :activity_id AND attendance_code = :attendance_code";
            $verifyStmt = $this->pdo->prepare($verifySql);
            $verifyStmt->bindParam(':activity_id', $activityId);
            $verifyStmt->bindParam(':attendance_code', $attendanceCode);
            $verifyStmt->execute();
            $activity = $verifyStmt->fetch(PDO::FETCH_ASSOC);

            if (!$activity) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Invalid attendance code or activity not found'
                ];
            }

            // Check if activity status is valid for attendance
            if ($activity['status'] !== 'Active' && $activity['status'] !== 'Ongoing') {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Cannot record attendance for ' . strtolower($activity['status']) . ' activities'
                ];
            }

            // Check if student is a member of the club
            $memberSql = "SELECT member_id FROM club_members 
                         WHERE club_id = :club_id AND student_id = :student_id AND status = 'Active'";
            $memberStmt = $this->pdo->prepare($memberSql);
            $memberStmt->bindParam(':club_id', $activity['club_id']);
            $memberStmt->bindParam(':student_id', $studentId);
            $memberStmt->execute();
            
            if ($memberStmt->rowCount() === 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'You must be an active member of the club to record attendance'
                ];
            }

            // Check if attendance has already been recorded
            $checkSql = "SELECT attendance_id FROM club_attendance 
                        WHERE activity_id = :activity_id AND student_id = :student_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindParam(':activity_id', $activityId);
            $checkStmt->bindParam(':student_id', $studentId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Attendance already recorded for this activity'
                ];
            }

            // Record the attendance
            $insertSql = "INSERT INTO club_attendance (
                activity_id, 
                club_id, 
                student_id, 
                check_in_time
            ) VALUES (
                :activity_id,
                :club_id,
                :student_id,
                NOW()
            )";
            
            $insertStmt = $this->pdo->prepare($insertSql);
            $insertStmt->bindParam(':activity_id', $activityId);
            $insertStmt->bindParam(':club_id', $activity['club_id']);
            $insertStmt->bindParam(':student_id', $studentId);
            $insertStmt->execute();

            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Attendance recorded successfully'
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error recording attendance: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to record attendance: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get attendance for an activity
     * 
     * @param int $activityId The activity ID
     * @return array Array of attendees
     */
    public function getActivityAttendance($activityId)
    {
        try {
            $sql = "SELECT 
                        ca.attendance_id,
                        ca.student_id,
                        cm.full_name,
                        ca.check_in_time
                    FROM club_attendance ca
                    JOIN club_members cm ON ca.student_id = cm.student_id
                    WHERE ca.activity_id = :activity_id
                    ORDER BY ca.check_in_time DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':activity_id', $activityId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting activity attendance: " . $e->getMessage());
            return [];
        }
    }
}
