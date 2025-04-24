<?php
require_once 'repositories/history-repository.php';

function getHistoryData($ccaDB, $profilesDB, $studentId, $semester = null)
{
    // Set default values in case of errors
    $defaultData = [
        'overallPoints' => 0,
        'clubHistory' => [],
        'selectedSemester' => $semester ?: '1'
    ];

    // Guard against null database connections
    if ($profilesDB === null || $ccaDB === null) {
        error_log("History Handler: Database connection is null");
        return $defaultData;
    }

    try {
        // Create a repository instance to use
        $historyRepository = new HistoryRepository($ccaDB, $profilesDB);
        
        // Get student's total EP from profiles database
        $totalEP = $historyRepository->getStudentEP($studentId);
        
        // Get club history data for the student for the selected semester
        $clubHistory = $historyRepository->getClubHistory($studentId, $semester);
        
        // Return all the data
        return [
            'overallPoints' => $totalEP,
            'clubHistory' => $clubHistory,
            'selectedSemester' => $semester ?: '1'
        ];
    } catch (Exception $e) {
        error_log("History Handler: Unexpected error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return $defaultData;
    }
} 