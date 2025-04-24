<?php
class HistoryRepository
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
            return $result && isset($result['enrichment_point']) ? intval($result['enrichment_point']) : 0;
        } catch (PDOException $e) {
            error_log("getStudentEP error: " . $e->getMessage());
            return 0;
        }
    }

    public function getClubHistory($studentId, $semester = null)
    {
        try {
            // Build the SQL query based on whether a semester is specified
            $sql = "SELECT 
                    c.club_name,
                    SUM(act.points_awarded) AS ep_earned,
                    CASE 
                        WHEN cm.is_active = 1 THEN 'Active'
                        ELSE 'Inactive'
                    END AS status,
                    cm.role
                FROM club_memberships cm
                JOIN clubs c ON cm.club_id = c.club_id
                LEFT JOIN club_attendance ca ON cm.club_id = ca.club_id AND ca.student_id = cm.student_id
                LEFT JOIN club_activities act ON ca.club_id = act.club_id";
            
            $params = [':student_id' => $studentId];
            
            // Add semester filter if specified
            if ($semester) {
                $semesterMapping = $this->getSemesterMapping($semester);
                $sql .= " WHERE cm.student_id = :student_id 
                        AND (
                            (YEAR(ca.check_in_time) = :year AND MONTH(ca.check_in_time) BETWEEN :start_month AND :end_month)
                            OR (ca.check_in_time IS NULL AND cm.student_id = :student_id2)
                        )";
                $params[':year'] = $semesterMapping['year'];
                $params[':start_month'] = $semesterMapping['start_month'];
                $params[':end_month'] = $semesterMapping['end_month'];
                $params[':student_id2'] = $studentId;
            } else {
                $sql .= " WHERE cm.student_id = :student_id";
            }
            
            $sql .= " GROUP BY c.club_id, cm.role, cm.is_active
                      ORDER BY ep_earned DESC
                      LIMIT 10";
            
            $stmt = $this->ccaDB->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            $stmt->execute();
            
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (PDOException $e) {
            error_log("getClubHistory error: " . $e->getMessage());
            return [];
        }
    }
    
    private function getSemesterMapping($semester)
    {
        $currentYear = date('Y');
        
        switch ($semester) {
            case '1':
                return [
                    'year' => $currentYear,
                    'start_month' => 1,
                    'end_month' => 6
                ];
            case '2':
                return [
                    'year' => $currentYear,
                    'start_month' => 7,
                    'end_month' => 12
                ];
            case '3':
                return [
                    'year' => $currentYear - 1,
                    'start_month' => 1,
                    'end_month' => 6
                ];
            case '4':
                return [
                    'year' => $currentYear - 1,
                    'start_month' => 7,
                    'end_month' => 12
                ];
            case '5':
                return [
                    'year' => $currentYear - 2,
                    'start_month' => 1,
                    'end_month' => 6
                ];
            case '6':
                return [
                    'year' => $currentYear - 2,
                    'start_month' => 7,
                    'end_month' => 12
                ];
            default:
                return [
                    'year' => $currentYear,
                    'start_month' => 1,
                    'end_month' => 6
                ];
        }
    }
} 