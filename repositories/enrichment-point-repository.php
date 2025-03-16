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
}
