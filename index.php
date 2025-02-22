<?php
session_start();

define('BASE_URL', 'http://13.229.232.151/enrichment-point-passport');

require_once 'database/config.php';

// Get the path from URL
$request_uri = str_replace('/enrichment-point-passport', '', $_SERVER['REQUEST_URI']);
$path = trim(parse_url($request_uri, PHP_URL_PATH), '/');
$params = explode('/', $path);

// Default to login if no path
$page = $params[0] ?: 'login';

// Redirect to dashboard if already logged in and trying to access login page
if ($page === 'login' && isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/dashboard");
    exit();
}

// List of pages that require authentication
$protected_pages = ['dashboard', 'profile', 'cca'];

// Check authentication for protected pages
if (in_array($page, $protected_pages) && !isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL);
    exit();
}

// Handle POST login separately
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_ic = trim($_POST["user_ic"] ?? '');
    $password = trim($_POST["password"] ?? '');

    try {
        // Updated SQL query to fetch all user information
        $sql = "SELECT 
                    user_id, 
                    user_ic, 
                    user_email, 
                    full_name, 
                    password, 
                    profile_picture,
                    role,
                    school,
                    student_id,
                    programme,
                    intake,
                    group_code,
                    enrichment_point
                FROM profiles.users 
                WHERE user_ic = :user_ic";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(":user_ic", $user_ic, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($password === $row["password"]) {
                session_regenerate_id(true);

                // Store all user data in session
                $_SESSION["user_id"] = $row["user_id"];
                $_SESSION["user_ic"] = $row["user_ic"];
                $_SESSION["user_email"] = $row["user_email"];
                $_SESSION["full_name"] = $row["full_name"];
                $_SESSION["role"] = $row["role"];
                $_SESSION["school"] = $row["school"];

                // Store profile picture if exists
                if (!empty($row["profile_picture"])) {
                    $_SESSION["profile_picture"] = $row["profile_picture"];
                }

                // Store student-specific information only if user is a student
                if ($row["role"] === "student") {
                    $_SESSION["student_id"] = $row["student_id"];
                    $_SESSION["programme"] = $row["programme"];
                    $_SESSION["intake"] = $row["intake"];
                    $_SESSION["group_code"] = $row["group_code"];
                    $_SESSION["enrichment_point"] = $row["enrichment_point"];
                }

                header("Location: " . BASE_URL . "/dashboard");
                exit();
            }
        }

        $_SESSION["error"] = "Invalid IC number or password.";
        header("Location: " . BASE_URL);
        exit();
    } catch (PDOException $e) {
        $_SESSION["error"] = "Something went wrong. Please try again later.";
        header("Location: " . BASE_URL);
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cca') {
    $operation = $_POST['operation'] ?? '';
    $club_id = $_POST['club_id'] ?? '';
    $student_id = $_SESSION['student_id'] ?? '';

    try {
        if (empty($student_id)) {
            throw new Exception("Student ID is required");
        }

        if (empty($club_id)) {
            throw new Exception("CLUB ID is required");
        }

        switch ($operation) {
            case 'join':
                // Check if already a member
                $checkSql = "SELECT club_id FROM cca.club_members 
                            WHERE student_id = :student_id 
                            AND club_id = :club_id 
                            AND status = 'Active'";

                $checkStmt = $pdo->prepare($checkSql);
                $checkStmt->bindParam(":student_id", $student_id, PDO::PARAM_STR);
                $checkStmt->bindParam(":club_id", $club_id, PDO::PARAM_INT);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    throw new Exception("Already a member of this CCA");
                }

                // Join CCA
                $joinSql = "INSERT INTO cca.club_members 
                           (club_id, student_id, join_date, status, created_at) 
                           VALUES (:club_id, :student_id, CURRENT_DATE, 'Active', CURRENT_TIMESTAMP)";

                $joinStmt = $pdo->prepare($joinSql);
                $joinStmt->bindParam(":student_id", $student_id, PDO::PARAM_STR);
                $joinStmt->bindParam(":club_id", $club_id, PDO::PARAM_INT);

                if ($joinStmt->execute()) {
                    $_SESSION['success'] = "Successfully joined the CCA";
                } else {
                    throw new Exception("Failed to join CCA");
                }
                break;

            case 'leave':
                // Update status to Inactive instead of deleting
                $leaveSql = "UPDATE cca.club_members 
                            SET status = 'Inactive' 
                            WHERE student_id = :student_id 
                            AND club_id = :club_id";

                $leaveStmt = $pdo->prepare($leaveSql);
                $leaveStmt->bindParam(":student_id", $student_id, PDO::PARAM_STR);
                $leaveStmt->bindParam(":club_id", $club_id, PDO::PARAM_INT);

                if ($leaveStmt->execute()) {
                    $_SESSION['success'] = "Successfully left the CLUB";
                } else {
                    throw new Exception("Failed to leave CLUB");
                }
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

if ($page === 'cca') {
    try {
        // Fetch all active CCAs and their member counts
        $sql = "SELECT 
        c.club_id,
        c.club_name,
        c.category,
        COUNT(DISTINCT cm.student_id) as member_count
    FROM cca.clubs c
    LEFT JOIN cca.club_members cm ON c.club_id = cm.club_id 
        AND cm.status = 'Active'
    WHERE c.status = 'Active'
    GROUP BY c.club_id, c.club_name, c.category
    ORDER BY c.category, c.club_name";

        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Group clubs by category
        $clubsByCategory = [];
        foreach ($clubs as $club) {
            $clubsByCategory[$club['category']][] = $club;
        }

        // Get user's current memberships
        if (isset($_SESSION['student_id'])) {
            $membershipSql = "SELECT club_id 
                     FROM cca.club_members 
                     WHERE student_id = :student_id 
                     AND status = 'Active'";
            $membershipStmt = $pdo->prepare($membershipSql);
            $membershipStmt->bindParam(':student_id', $_SESSION['student_id']);
            $membershipStmt->execute();
            $userMemberships = $membershipStmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $userMemberships = [];
        }

        // Make data available to template
        $pageData = [
            'clubsByCategory' => $clubsByCategory,
            'userMemberships' => $userMemberships,
            'clubMapping' => $clubMapping
        ];
    } catch (PDOException $e) {
        error_log("Error fetching CCAs: " . $e->getMessage());
        $pageData = [
            'clubsByCategory' => [],
            'userMemberships' => [],
            'error' => 'Failed to load CCA data'
        ];
    }
}

switch ($page) {
    case 'login':
        include 'templates/login.php';
        break;
    case 'dashboard':
        include 'templates/dashboard.php';
        break;
    case 'profile':
        include 'templates/profile.php';
        break;
    case 'logout':
        session_destroy();
        header("Location: " . BASE_URL);
        exit();
        break;
    case 'about':
        include 'templates/about.php';
        break;
    case 'contact':
        include 'templates/contact.php';
        break;
    case 'cca':
        if (isset($params[1])) {
            $requestedClub = $params[1];

            $clubMapping = [
                'academic' => [
                    'COPS Club' => 'cops',
                    'Geosoc Club' => 'geosoc',
                    'KNI' => 'kni',
                    'SPE Club' => 'spe',
                    'Chess Club' => 'chess',
                    'Kadet Tentera' => 'kadet-tentera'
                ],
                'arts' => [
                    'Art Club' => 'art',
                    'Movie Club' => 'movie',
                    'Music Club' => 'music',
                    'Dance Club' => 'dance'
                ],
                'culture' => [
                    'Gulingtangan' => 'gulingtangan',
                    'Hadrah' => 'hadrah',
                    'KAWAN Club' => 'kawan',
                    'Korean Culture Club' => 'korean-culture',
                    'Let\'s Japan Club' => 'lets-japan',
                    'Healing Club' => 'healing'
                ],
                'martial-arts' => [
                    'Boxing Club' => 'boxing',
                    'Hapkido Club' => 'hapkido',
                    'Judo Club' => 'judo',
                    'Karate Club' => 'karate'
                ],
                'sports' => [
                    'Badminton Club' => 'badminton',
                    'Basketball' => 'basketball',
                    'Dodgeball' => 'dodgeball',
                    'Esports Club' => 'esports',
                    'Frisbee Club' => 'frisbee',
                    'Futsal Club' => 'futsal',
                    'Hiking Club' => 'hiking',
                    'Netball' => 'netball',
                    'Pool Club' => 'pool',
                    'Squash Club' => 'squash',
                    'Touch Rugby' => 'touch-rugby',
                    'Volleyball' => 'volleyball',
                    'Zumba Club' => 'zumba'
                ]
            ];

            foreach ($clubMapping as $category => $clubs) {
                foreach ($clubs as $clubName => $clubSlug) {
                    if ($requestedClub === $clubSlug) {
                        $templatePath = "templates/clubs/{$category}/{$clubSlug}.php";
                        if (file_exists($templatePath)) {
                            include $templatePath;
                            break 2;
                        }
                    }
                }
            }

            if (!isset($templatePath) || !file_exists($templatePath)) {
                header("HTTP/1.0 404 Not Found");
                include 'templates/404.php';
            }
        } else {
            include 'templates/cca.php';
        }
        break;
    default:
        header("HTTP/1.0 404 Not Found");
        include 'templates/404.php';
        break;
}
