<?php
function handleClubAction($pdo) {
    $operation = $_POST['operation'] ?? '';
    $clubId = $_POST['club_id'] ?? '';
    $studentId = $_SESSION['student_id'] ?? '';

    try {
        if (empty($studentId)) {
            throw new Exception("Student ID is required");
        }

        if (empty($clubId)) {
            throw new Exception("CLUB ID is required");
        }

        switch ($operation) {
            case 'join':
                joinClub($pdo, $clubId, $studentId);
                break;

            case 'leave':
                leaveClub($pdo, $clubId, $studentId);
                break;

            default:
                throw new Exception("Invalid operation");
        }

        // Redirect back to CCA page with success message
        header("Location: " . BASE_URL . "/cca");
        exit();
    } catch (Exception $e) {
        $_SESSION["error"] = $e->getMessage();
        header("Location: " . BASE_URL . "/cca");
        exit();
    }
}

function joinClub($pdo, $clubId, $studentId) {
    // Check if already a member
    $checkSql = "SELECT club_id FROM cca.club_members 
                WHERE student_id = :student_id 
                AND club_id = :club_id 
                AND status = 'Active'";

    $checkStmt = $pdo->prepare($checkSql);
    $checkStmt->bindParam(":student_id", $studentId, PDO::PARAM_STR);
    $checkStmt->bindParam(":club_id", $clubId, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        throw new Exception("Already a member of this CCA");
    }

    // Join CCA
    $joinSql = "INSERT INTO cca.club_members 
               (club_id, student_id, join_date, status, created_at) 
               VALUES (:club_id, :student_id, CURRENT_DATE, 'Active', CURRENT_TIMESTAMP)";

    $joinStmt = $pdo->prepare($joinSql);
    $joinStmt->bindParam(":student_id", $studentId, PDO::PARAM_STR);
    $joinStmt->bindParam(":club_id", $clubId, PDO::PARAM_INT);

    if ($joinStmt->execute()) {
        $_SESSION['success'] = "Successfully joined the CCA";
    } else {
        throw new Exception("Failed to join CCA");
    }
}

function leaveClub($pdo, $clubId, $studentId) {
    // Update status to Inactive instead of deleting
    $leaveSql = "UPDATE cca.club_members 
                SET status = 'Inactive' 
                WHERE student_id = :student_id 
                AND club_id = :club_id";

    $leaveStmt = $pdo->prepare($leaveSql);
    $leaveStmt->bindParam(":student_id", $studentId, PDO::PARAM_STR);
    $leaveStmt->bindParam(":club_id", $clubId, PDO::PARAM_INT);

    if ($leaveStmt->execute()) {
        $_SESSION['success'] = "Successfully left the CLUB";
    } else {
        throw new Exception("Failed to leave CLUB");
    }
}