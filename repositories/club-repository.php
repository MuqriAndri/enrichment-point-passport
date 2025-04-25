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

    public function getPendingApplications($clubId)
    {
        try {
            $sql = "SELECT * FROM club_members 
                    WHERE club_id = :club_id 
                    AND status = 'Pending'
                    ORDER BY created_at DESC";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting pending applications: " . $e->getMessage());
            return [];
        }
    }
    
    public function getClubMembers($clubId)
    {
        try {
            $sql = "SELECT m.*, r.role_name 
                    FROM club_members m
                    LEFT JOIN club_roles r ON m.role_id = r.role_id
                    WHERE m.club_id = :club_id 
                    AND m.status = 'Active'
                    ORDER BY m.role_id ASC, m.full_name ASC";
                    
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting club members: " . $e->getMessage());
            return [];
        }
    }
    
    public function updateApplicationStatus($memberId, $newStatus)
    {
        try {
            $this->pdo->beginTransaction();
            
            $sql = "UPDATE club_members 
                   SET status = :status
                   WHERE member_id = :member_id";
                   
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':status', $newStatus);
            $stmt->bindParam(':member_id', $memberId);
            $stmt->execute();
            
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Application status updated successfully'
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error updating application status: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to update application status: ' . $e->getMessage()
            ];
        }
    }

    public function removeMember($memberId)
    {
        try {
            $this->pdo->beginTransaction();
            
            // First check if this is a club president (role_id = 1)
            $checkSql = "SELECT role_id FROM club_members WHERE member_id = :member_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindParam(':member_id', $memberId);
            $checkStmt->execute();
            
            $memberRole = $checkStmt->fetchColumn();
            
            // Cannot remove a club president
            if ($memberRole == 1) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Club presidents cannot be removed. Transfer presidency first.'
                ];
            }
            
            // Update the member status to Inactive
            $sql = "UPDATE club_members 
                   SET status = 'Inactive'
                   WHERE member_id = :member_id";
                   
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':member_id', $memberId);
            $stmt->execute();
            
            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Member removed successfully'
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log("Error removing member: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to remove member: ' . $e->getMessage()
            ];
        }
    }

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

    public function createClubActivity($clubId, $activityData)
    {
        try {
            $this->pdo->beginTransaction();

            $sql = "INSERT INTO club_activities (
                club_id,
                title,
                description,
                activity_type,
                start_datetime,
                end_datetime,
                location_name,
                location_details,
                latitude,
                longitude,
                points_awarded,
                status,
                created_at
            ) VALUES (
                :club_id,
                :title,
                :description,
                :activity_type,
                :start_datetime,
                :end_datetime,
                :location_name,
                :location_details,
                :latitude,
                :longitude,
                :points_awarded,
                :status,
                NOW()
            )";

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->bindParam(':title', $activityData['title']);
            $stmt->bindParam(':description', $activityData['description'], PDO::PARAM_STR);
            
            // Default activity type if not specified
            $activityType = isset($activityData['activity_type']) ? $activityData['activity_type'] : 'Regular Session';
            $stmt->bindParam(':activity_type', $activityType);
            
            $stmt->bindParam(':start_datetime', $activityData['start_datetime']);
            $stmt->bindParam(':end_datetime', $activityData['end_datetime']);
            
            $locationName = isset($activityData['location_name']) ? $activityData['location_name'] : '';
            $stmt->bindParam(':location_name', $locationName);
            
            $locationDetails = isset($activityData['location_details']) ? $activityData['location_details'] : null;
            $stmt->bindParam(':location_details', $locationDetails, PDO::PARAM_STR);
            
            // Location coordinates (optional)
            $latitude = isset($activityData['latitude']) ? $activityData['latitude'] : null;
            $longitude = isset($activityData['longitude']) ? $activityData['longitude'] : null;
            $stmt->bindParam(':latitude', $latitude);
            $stmt->bindParam(':longitude', $longitude);
            
            $pointsAwarded = isset($activityData['points_awarded']) ? $activityData['points_awarded'] : 2;
            $stmt->bindParam(':points_awarded', $pointsAwarded);
            
            // Default status is "Planned" if not provided
            $status = isset($activityData['status']) ? $activityData['status'] : 'Planned';
            $stmt->bindParam(':status', $status);

            $stmt->execute();
            $activityId = $this->pdo->lastInsertId();

            $this->pdo->commit();
            
            return [
                'success' => true,
                'message' => 'Activity created successfully',
                'activity_id' => $activityId
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

    public function updateActivity($activityId, $activityData)
    {
        try {
            $this->pdo->beginTransaction();

            // Build the update query dynamically based on provided fields
            $sql = "UPDATE club_activities SET last_modified_at = NOW()";
            $params = [];

            // Only include fields that are provided in the update
            $allowedFields = [
                'title', 'description', 'activity_type', 'start_datetime', 
                'end_datetime', 'location_name', 'location_details', 'latitude', 
                'longitude', 'radius_meters', 'points_awarded', 'status'
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
            
            $clubId = $checkStmt->fetchColumn();

            // Since club_attendance doesn't have activity_id, we'll just check if there are any attendance records for this club
            // If this is too broad, we would need to modify the club_attendance table to include activity_id
            $attendanceSql = "SELECT COUNT(*) FROM club_attendance WHERE club_id = :club_id";
            $attendanceStmt = $this->pdo->prepare($attendanceSql);
            $attendanceStmt->bindParam(':club_id', $clubId);
            $attendanceStmt->execute();
            $attendanceCount = $attendanceStmt->fetchColumn();

            // If there's attendance, just mark it as cancelled instead of deleting
            $updateSql = "UPDATE club_activities SET status = 'Cancelled', last_modified_at = NOW() WHERE activity_id = :activity_id";
            $updateStmt = $this->pdo->prepare($updateSql);
            $updateStmt->bindParam(':activity_id', $activityId);
            $updateStmt->execute();

            $this->pdo->commit();
            return [
                'success' => true,
                'message' => 'Activity cancelled successfully'
            ];
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

    public function recordActivityAttendance($activityId, $studentId)
    {
        try {
            $this->pdo->beginTransaction();

            // First get the activity details
            $activitySql = "SELECT club_id, status FROM club_activities 
                         WHERE activity_id = :activity_id";
            $activityStmt = $this->pdo->prepare($activitySql);
            $activityStmt->bindParam(':activity_id', $activityId);
            $activityStmt->execute();
            $activity = $activityStmt->fetch(PDO::FETCH_ASSOC);

            if (!$activity) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Activity not found'
                ];
            }

            // Check if activity status is valid for attendance
            if ($activity['status'] !== 'Ongoing' && $activity['status'] !== 'Planned') {
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
                        WHERE club_id = :club_id AND student_id = :student_id";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindParam(':club_id', $activity['club_id']);
            $checkStmt->bindParam(':student_id', $studentId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Attendance already recorded for this activity'
                ];
            }

            // Get user's current location for attendance
            // In a real app, you would get this from the client
            $latitude = 4.8855970;  // Default value
            $longitude = 114.9316160;  // Default value

            // Record the attendance
            $insertSql = "INSERT INTO club_attendance (
                club_id, 
                student_id, 
                check_in_time,
                latitude,
                longitude,
                attendance_status
            ) VALUES (
                :club_id,
                :student_id,
                NOW(),
                :latitude,
                :longitude,
                'Present'
            )";
            
            $insertStmt = $this->pdo->prepare($insertSql);
            $insertStmt->bindParam(':club_id', $activity['club_id']);
            $insertStmt->bindParam(':student_id', $studentId);
            $insertStmt->bindParam(':latitude', $latitude);
            $insertStmt->bindParam(':longitude', $longitude);
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

    public function getActivityAttendance($activityId)
    {
        try {
            // First get the club ID for this activity
            $activitySql = "SELECT club_id FROM club_activities WHERE activity_id = :activity_id";
            $activityStmt = $this->pdo->prepare($activitySql);
            $activityStmt->bindParam(':activity_id', $activityId);
            $activityStmt->execute();
            
            if ($clubId = $activityStmt->fetchColumn()) {
                // Use the club attendance method instead since activity_id doesn't exist in club_attendance
                return $this->getClubAttendance($clubId);
            }
            
            return [];
        } catch (PDOException $e) {
            error_log("Error getting activity attendance: " . $e->getMessage());
            return [];
        }
    }

    public function getClubAttendance($clubId)
    {
        try {
            $sql = "SELECT 
                        ca.attendance_id,
                        ca.student_id,
                        cu.full_name,
                        ca.check_in_time
                    FROM club_attendance ca
                    JOIN profiles.users cu ON ca.student_id = cu.student_id
                    WHERE ca.club_id = :club_id
                    ORDER BY ca.check_in_time DESC";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':club_id', $clubId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting club attendance: " . $e->getMessage());
            return [];
        }
    }

    public function recordAttendance($clubId, $studentId, $latitude, $longitude, $accuracy = null, $deviceInfo = null, $ipAddress = null)
    {
        try {
            // Log the attempt
            error_log("Starting attendance recording for student_id: $studentId, club_id: $clubId");
            error_log("Location data: lat=$latitude, lng=$longitude");
            
            $this->pdo->beginTransaction();
            error_log("Transaction started");

            // Check if the student is a member of the club
            $memberSql = "SELECT member_id FROM club_members 
                         WHERE club_id = :club_id AND student_id = :student_id AND status = 'Active'";
            $memberStmt = $this->pdo->prepare($memberSql);
            $memberStmt->bindParam(':club_id', $clubId);
            $memberStmt->bindParam(':student_id', $studentId);
            $memberStmt->execute();
            
            if ($memberStmt->rowCount() === 0) {
                error_log("Student $studentId is not an active member of club $clubId");
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'You must be an active member of the club to record attendance'
                ];
            }
            error_log("Student membership verified");

            // Check if attendance has already been recorded today
            $checkSql = "SELECT attendance_id FROM club_attendance 
                        WHERE club_id = :club_id AND student_id = :student_id 
                        AND DATE(check_in_time) = CURDATE()";
            $checkStmt = $this->pdo->prepare($checkSql);
            $checkStmt->bindParam(':club_id', $clubId);
            $checkStmt->bindParam(':student_id', $studentId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                error_log("Student $studentId already has attendance for club $clubId today");
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Attendance already recorded for today'
                ];
            }
            error_log("No duplicate attendance found for today");

            // Set default accuracy if not provided
            if ($accuracy === null) {
                $accuracy = mt_rand(3, 10); // Random accuracy between 3-10 meters
            }
            
            // Handle null device_info and ip_address
            if ($deviceInfo === null) {
                $deviceInfo = 'Unknown Device';
            }
            
            // Prepare the SQL
            $insertSql = "INSERT INTO club_attendance (
                club_id, 
                student_id, 
                check_in_time,
                latitude,
                longitude,
                accuracy_meters,
                is_valid,
                attendance_status,
                device_info,
                ip_address,
                created_at
            ) VALUES (
                :club_id,
                :student_id,
                NOW(),
                :latitude,
                :longitude,
                :accuracy,
                1,
                'Present',
                :device_info,
                :ip_address,
                NOW()
            )";
            error_log("SQL prepared: $insertSql");
            
            $insertStmt = $this->pdo->prepare($insertSql);
            
            // Explicitly convert and validate values
            $clubIdInt = (int)$clubId;
            $latitudeFloat = (float)$latitude;
            $longitudeFloat = (float)$longitude;
            $accuracyFloat = (float)$accuracy;
            
            error_log("Binding parameters with values: clubId=$clubIdInt, studentId=$studentId, lat=$latitudeFloat, lng=$longitudeFloat, accuracy=$accuracyFloat");
            
            $insertStmt->bindParam(':club_id', $clubIdInt);
            $insertStmt->bindParam(':student_id', $studentId);
            $insertStmt->bindParam(':latitude', $latitudeFloat);
            $insertStmt->bindParam(':longitude', $longitudeFloat);
            $insertStmt->bindParam(':accuracy', $accuracyFloat);
            $insertStmt->bindParam(':device_info', $deviceInfo);
            $insertStmt->bindParam(':ip_address', $ipAddress);
            
            // Execute the statement
            $result = $insertStmt->execute();
            error_log("SQL execution result: " . ($result ? "Success" : "Failed"));
            
            if (!$result) {
                $error = $insertStmt->errorInfo();
                error_log("SQL Error: " . json_encode($error));
                $this->pdo->rollBack();
                return [
                    'success' => false,
                    'message' => 'Database error: ' . ($error[2] ?? 'Unknown error')
                ];
            }

            $attendanceId = $this->pdo->lastInsertId();
            error_log("New attendance record created with ID: $attendanceId");

            $this->pdo->commit();
            error_log("Transaction committed successfully");
            
            return [
                'success' => true,
                'message' => 'Attendance recorded successfully',
                'attendance_id' => $attendanceId
            ];
        } catch (PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
                error_log("Transaction rolled back due to exception");
            }
            error_log("PDO Exception in recordAttendance: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Failed to record attendance: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
                error_log("Transaction rolled back due to general exception");
            }
            error_log("General Exception in recordAttendance: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            
            return [
                'success' => false,
                'message' => 'Failed to record attendance: ' . $e->getMessage()
            ];
        }
    }
}
