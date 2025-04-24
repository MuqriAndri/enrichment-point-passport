<?php
require_once 'repositories/enrichment-point-repository.php';

function getEnrichmentPointData($ccaDB, $profilesDB, $studentId)
{
    // Set default values in case of errors
    $defaultData = [
        'totalEP' => 0,
        'targetEP' => 64,
        'completionPercentage' => 0,
        'remainingEP' => 64,
        'epPerSemester' => [],
        'cumulativeEP' => [],
        'activityDetails' => [],
        'epDistribution' => []
    ];

    // Guard against null database connections
    if ($profilesDB === null || $ccaDB === null) {
        error_log("EP Handler: Database connection is null");
        return $defaultData;
    }

    try {
        // Create a repository instance to use
        $epRepository = new EnrichmentPointRepository($ccaDB, $profilesDB);
        
        // 1. Get student's total EP from profiles database
        $totalEP = $epRepository->getStudentEP($studentId);
        error_log("Total EP for student $studentId: $totalEP");
        
        // 2. Calculate basic metrics
        $targetEP = 64; // Keep target at 64 EP
        
        // Check if target is exceeded
        $targetExceeded = $totalEP > $targetEP;
        
        // Cap completion percentage at 100% for display purposes
        $completionPercentage = min(100, $totalEP > 0 ? round(($totalEP / $targetEP) * 100) : 0);
        
        // Ensure remaining EP is never negative
        $remainingEP = max(0, $targetEP - $totalEP);
        
        // 3. Get EP by semester (get all semesters, not just current)
        $epPerSemester = $epRepository->getEPBySemester($studentId);
        error_log("EP Per Semester count: " . count($epPerSemester));
        
        // 4. Calculate cumulative totals based on actual data
        $cumulativeEP = [];
        $runningTotal = 0;
        
        // Calculate cumulative EP from semester data
        foreach ($epPerSemester as $semester) {
            $runningTotal += $semester['points_earned'];
            $cumulativeEP[$semester['semester']] = $runningTotal;
        }
        
        // If the final calculated EP doesn't match the total EP from the user profile,
        // adjust the most recent semester to match
        if (!empty($epPerSemester) && $runningTotal != $totalEP) {
            $lastSemesterKey = count($epPerSemester) - 1;
            $lastSemester = $epPerSemester[$lastSemesterKey]['semester'];
            $diff = $totalEP - $runningTotal;
            
            // Update both the points_earned and cumulative values
            $epPerSemester[$lastSemesterKey]['points_earned'] += $diff;
            $cumulativeEP[$lastSemester] = $totalEP;
        }
        
        // 5. Get activity details - using the repository
        $activityDetails = $epRepository->getActivityDetails($studentId);
        error_log("Activity Details count: " . count($activityDetails));
        
        // 6. Get EP distribution by activity type
        $epDistribution = $epRepository->getEPDistribution($studentId);
        error_log("EP Distribution count: " . count($epDistribution));
        
        // Return all the data
        return [
            'totalEP' => $totalEP,
            'targetEP' => $targetEP,
            'completionPercentage' => $completionPercentage,
            'remainingEP' => $remainingEP,
            'targetExceeded' => $targetExceeded,
            'epPerSemester' => $epPerSemester,
            'cumulativeEP' => $cumulativeEP,
            'activityDetails' => $activityDetails,
            'epDistribution' => $epDistribution,
            'getBadgeClass' => 'getBadgeClass'
        ];
    } catch (Exception $e) {
        error_log("EP Handler: Unexpected error: " . $e->getMessage());
        error_log("Error trace: " . $e->getTraceAsString());
        return $defaultData;
    }
}

// Helper function for badges - keep this as a global function
function getBadgeClass($type)
{
    switch ($type) {
        case 'Academic':
            return 'academic';
        case 'Sports':
            return 'leadership';
        case 'Service':
            return 'service';
        case 'Arts':
            return 'professional';
        default:
            return 'academic';
    }
}