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
        $targetEP = 64;
        
        // Check if target is exceeded
        $targetExceeded = $totalEP > $targetEP;
        
        // Cap completion percentage at 100% for display purposes
        $completionPercentage = min(100, $totalEP > 0 ? round(($totalEP / $targetEP) * 100) : 0);
        
        // Ensure remaining EP is never negative
        $remainingEP = max(0, $targetEP - $totalEP);
        
        // 3. Get EP by semester
        $epPerSemester = $epRepository->getEPBySemester($studentId);
        error_log("EP Per Semester count: " . count($epPerSemester));
        
        // If no data found, use basic distribution with numeric semesters
        if (empty($epPerSemester)) {
            error_log("No semester data found, using fallback distribution");
            $epPerSemester = [
                ['semester' => '1st Semester 2024', 'points_earned' => round($totalEP * 0.6)],
                ['semester' => '2nd Semester 2024', 'points_earned' => round($totalEP * 0.4)]
            ];
        }
        
        // 4. Calculate cumulative totals
        $cumulativeEP = [];
        $runningTotal = 0;
        foreach ($epPerSemester as $semester) {
            $runningTotal += $semester['points_earned'];
            $cumulativeEP[$semester['semester']] = $runningTotal;
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