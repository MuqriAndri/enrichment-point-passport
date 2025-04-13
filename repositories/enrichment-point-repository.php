<?php
class EnrichmentPointRepository
{
    private $ccaDB;
    private $profilesDB;

    public function __construct($ccaDB, $profilesDB)
    {
        $this->ccaDB = $ccaDB;
        $this->profilesDB = $profilesDB;
    }

    public function getStudentEP($studentId)
    {
        try {
            $sql = "SELECT enrichment_point FROM users WHERE student_id = :student_id";
            $stmt = $this->profilesDB->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            error_log("getStudentEP result: " . json_encode($result));
            return $result && isset($result['enrichment_point']) ? intval($result['enrichment_point']) : 0;
        } catch (PDOException $e) {
            error_log("getStudentEP error: " . $e->getMessage());
            return 0;
        }
    }

    public function getEPBySemester($studentId)
    {
        try {
            $sql = "SELECT 
                    semester,
                    SUM(points) as points_earned
                FROM (
                    SELECT 
                        CONCAT(
                            CASE 
                                WHEN MONTH(a.check_in_time) BETWEEN 1 AND 6 THEN '1st '
                                ELSE '2nd '
                            END,
                            'Semester ', YEAR(a.check_in_time)
                        ) AS semester,
                        act.points_awarded as points,
                        act.activity_id
                    FROM club_attendance a
                    JOIN club_activities act ON a.club_id = act.club_id
                    WHERE a.student_id = :student_id
                    GROUP BY semester, act.activity_id
                ) as unique_activities
                GROUP BY semester
                ORDER BY 
                    SUBSTRING_INDEX(semester, ' ', -1), -- Year
                    CASE 
                        WHEN SUBSTRING_INDEX(semester, ' ', 1) = '1st' THEN 1
                        ELSE 2
                    END";
            $stmt = $this->ccaDB->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("getEPBySemester count: " . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log("getEPBySemester error: " . $e->getMessage());
            return [];
        }
    }

    public function getActivityDetails($studentId)
    {
        try {
            $sql = "SELECT 
                    act.title AS activity,
                    CASE 
                        WHEN c.category = 'Sports' OR c.category = 'Martial Arts' THEN 'Sports'
                        WHEN c.category = 'Academic' THEN 'Academic'
                        WHEN c.category = 'Arts' OR c.category = 'Culture' THEN 'Arts'
                        ELSE 'Service'
                    END AS type,
                    CONCAT(
                        CASE 
                            WHEN MONTH(act.start_datetime) BETWEEN 1 AND 6 THEN '1st '
                            ELSE '2nd '
                        END,
                        'Semester ', YEAR(act.start_datetime)
                    ) AS semester,
                    DATE_FORMAT(act.start_datetime, '%b %d, %Y') AS completion_date,
                    act.points_awarded AS points
                FROM club_activities act
                JOIN clubs c ON act.club_id = c.club_id
                JOIN club_attendance a ON act.club_id = a.club_id
                WHERE a.student_id = :student_id
                GROUP BY act.activity_id
                ORDER BY act.start_datetime DESC";
            $stmt = $this->ccaDB->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("getActivityDetails count: " . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log("getActivityDetails error: " . $e->getMessage());
            return [];
        }
    }

    public function getEPDistribution($studentId)
    {
        try {
            // First calculate the actual sum of points from activities
            $sqlSum = "SELECT 
                    SUM(points) as total_points
                FROM (
                    SELECT 
                        act.points_awarded as points,
                        act.activity_id
                    FROM club_activities act
                    JOIN club_attendance a ON act.club_id = a.club_id
                    WHERE a.student_id = :student_id
                    GROUP BY act.activity_id
                ) as unique_activities";
            $stmtSum = $this->ccaDB->prepare($sqlSum);
            $stmtSum->bindParam(':student_id', $studentId);
            $stmtSum->execute();
            $totalPoints = $stmtSum->fetchColumn();

            // If no points, return empty array
            if ($totalPoints <= 0) {
                return [];
            }

            $sql = "SELECT 
                    type,
                    SUM(points) as points,
                    (SUM(points) / :total_points * 100) AS percentage
                FROM (
                    SELECT 
                        CASE 
                            WHEN c.category = 'Sports' OR c.category = 'Martial Arts' THEN 'Sports'
                            WHEN c.category = 'Academic' THEN 'Academic'
                            WHEN c.category = 'Arts' OR c.category = 'Culture' THEN 'Arts'
                            ELSE 'Service'
                        END AS type,
                        act.points_awarded as points,
                        act.activity_id
                    FROM club_activities act
                    JOIN clubs c ON act.club_id = c.club_id
                    JOIN club_attendance a ON act.club_id = a.club_id
                    WHERE a.student_id = :student_id
                    GROUP BY type, act.activity_id
                ) as unique_activities
                GROUP BY type
                ORDER BY points DESC";
            $stmt = $this->ccaDB->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->bindParam(':total_points', $totalPoints);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("getEPDistribution count: " . count($result));
            return $result;
        } catch (PDOException $e) {
            error_log("getEPDistribution error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Award enrichment points for club activity attendance
     * 
     * @param int $attendanceId The club attendance ID
     * @param string $studentId The student ID
     * @param int $activityId The activity ID
     * @param float $pointsAwarded The points to award
     * @return array Array with success status and message
     */
    public function awardPointsForClubAttendance($attendanceId, $studentId, $activityId, $pointsAwarded)
    {
        try {
            $this->ccaDB->beginTransaction();

            // Check if points have already been awarded for this attendance
            $checkSql = "SELECT record_id FROM enrichment_points 
                        WHERE reference_id = :attendance_id 
                        AND reference_type = 'club_attendance'";
            $checkStmt = $this->ccaDB->prepare($checkSql);
            $checkStmt->bindParam(':attendance_id', $attendanceId);
            $checkStmt->execute();
            
            if ($checkStmt->rowCount() > 0) {
                $this->ccaDB->rollBack();
                return [
                    'success' => false,
                    'message' => 'Points have already been awarded for this attendance'
                ];
            }

            // Get activity details
            $activitySql = "SELECT ca.title, c.club_name 
                           FROM club_activities ca
                           JOIN clubs c ON ca.club_id = c.club_id
                           WHERE ca.activity_id = :activity_id";
            $activityStmt = $this->ccaDB->prepare($activitySql);
            $activityStmt->bindParam(':activity_id', $activityId);
            $activityStmt->execute();
            $activityInfo = $activityStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$activityInfo) {
                $this->ccaDB->rollBack();
                return [
                    'success' => false,
                    'message' => 'Activity not found'
                ];
            }

            // Get current semester
            $semesterSql = "SELECT semester_id, semester_name FROM active_semester LIMIT 1";
            $semesterStmt = $this->ccaDB->prepare($semesterSql);
            $semesterStmt->execute();
            $semesterInfo = $semesterStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$semesterInfo) {
                $this->ccaDB->rollBack();
                return [
                    'success' => false,
                    'message' => 'No active semester found'
                ];
            }

            // Calculate for which category these points should be awarded
            // For club activities, we'll use the 'club_involvement' category
            $categorySql = "SELECT category_id FROM enrichment_point_categories 
                           WHERE category_code = 'club_involvement' LIMIT 1";
            $categoryStmt = $this->ccaDB->prepare($categorySql);
            $categoryStmt->execute();
            $categoryInfo = $categoryStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$categoryInfo) {
                $this->ccaDB->rollBack();
                return [
                    'success' => false,
                    'message' => 'Club involvement category not found'
                ];
            }

            // Create description for the points
            $description = "Attendance at ".$activityInfo['club_name']." activity: ".$activityInfo['title'];

            // Insert the points record
            $insertSql = "INSERT INTO enrichment_points (
                student_id, 
                semester_id,
                category_id,
                points_awarded,
                description,
                reference_type,
                reference_id,
                awarded_date,
                created_at
            ) VALUES (
                :student_id,
                :semester_id,
                :category_id,
                :points_awarded,
                :description,
                'club_attendance',
                :reference_id,
                NOW(),
                NOW()
            )";
            
            $insertStmt = $this->ccaDB->prepare($insertSql);
            $insertStmt->bindParam(':student_id', $studentId);
            $insertStmt->bindParam(':semester_id', $semesterInfo['semester_id']);
            $insertStmt->bindParam(':category_id', $categoryInfo['category_id']);
            $insertStmt->bindParam(':points_awarded', $pointsAwarded);
            $insertStmt->bindParam(':description', $description);
            $insertStmt->bindParam(':reference_id', $attendanceId);
            $insertStmt->execute();

            $pointsRecordId = $this->ccaDB->lastInsertId();

            // Update the club attendance to mark that points were awarded
            $updateSql = "UPDATE club_attendance SET 
                          points_awarded = :points_awarded,
                          points_awarded_at = NOW() 
                          WHERE attendance_id = :attendance_id";
            $updateStmt = $this->ccaDB->prepare($updateSql);
            $updateStmt->bindParam(':points_awarded', $pointsAwarded);
            $updateStmt->bindParam(':attendance_id', $attendanceId);
            $updateStmt->execute();

            $this->ccaDB->commit();
            
            return [
                'success' => true,
                'message' => 'Enrichment points awarded successfully',
                'points_record_id' => $pointsRecordId,
                'points_awarded' => $pointsAwarded,
                'semester' => $semesterInfo['semester_name']
            ];
        } catch (PDOException $e) {
            if ($this->ccaDB->inTransaction()) {
                $this->ccaDB->rollBack();
            }
            error_log("Error awarding points for club attendance: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to award points: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get club activities with enrichment points for a student
     * 
     * @param string $studentId The student ID
     * @return array Array of club activities with enrichment points
     */
    public function getClubActivityPoints($studentId)
    {
        try {
            $sql = "SELECT 
                       ep.record_id,
                       ep.points_awarded,
                       ep.description,
                       ep.awarded_date,
                       ca.title as activity_title,
                       ca.start_datetime as activity_date,
                       c.club_name,
                       c.club_id,
                       s.semester_name
                   FROM enrichment_points ep
                   JOIN club_attendance ca_attendance ON ep.reference_id = ca_attendance.attendance_id
                   JOIN club_activities ca ON ca_attendance.activity_id = ca.activity_id
                   JOIN clubs c ON ca.club_id = c.club_id
                   JOIN active_semester s ON ep.semester_id = s.semester_id
                   WHERE ep.student_id = :student_id
                   AND ep.reference_type = 'club_attendance'
                   ORDER BY ep.awarded_date DESC";
            $stmt = $this->ccaDB->prepare($sql);
            $stmt->bindParam(':student_id', $studentId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error getting club activity points: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Process pending club activity enrichment points
     * This method will automatically award points for all club attendances that haven't been processed yet
     * 
     * @return array Array with success status and statistics
     */
    public function processPendingClubActivityPoints()
    {
        try {
            $this->ccaDB->beginTransaction();

            // Find all club attendances that don't have points awarded yet
            $pendingSql = "SELECT 
                              ca_attendance.attendance_id,
                              ca_attendance.student_id,
                              ca_attendance.activity_id,
                              ca.points_awarded as activity_points
                          FROM club_attendance ca_attendance
                          JOIN club_activities ca ON ca_attendance.activity_id = ca.activity_id
                          WHERE ca_attendance.points_awarded IS NULL
                          AND ca.status = 'Completed'";
            $pendingStmt = $this->ccaDB->prepare($pendingSql);
            $pendingStmt->execute();
            $pendingAttendances = $pendingStmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($pendingAttendances)) {
                $this->ccaDB->rollBack();
                return [
                    'success' => true,
                    'message' => 'No pending club activities found for points processing',
                    'processed_count' => 0
                ];
            }

            $processedCount = 0;
            $failedCount = 0;
            
            foreach ($pendingAttendances as $attendance) {
                $result = $this->awardPointsForClubAttendance(
                    $attendance['attendance_id'],
                    $attendance['student_id'],
                    $attendance['activity_id'],
                    $attendance['activity_points']
                );
                
                if ($result['success']) {
                    $processedCount++;
                } else {
                    $failedCount++;
                    error_log("Failed to process points for attendance ID {$attendance['attendance_id']}: {$result['message']}");
                }
            }

            $this->ccaDB->commit();
            
            return [
                'success' => true,
                'message' => "Processed $processedCount club activities for enrichment points",
                'processed_count' => $processedCount,
                'failed_count' => $failedCount,
                'total_pending' => count($pendingAttendances)
            ];
        } catch (PDOException $e) {
            if ($this->ccaDB->inTransaction()) {
                $this->ccaDB->rollBack();
            }
            error_log("Error processing pending club activity points: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to process pending points: ' . $e->getMessage()
            ];
        }
    }
}
