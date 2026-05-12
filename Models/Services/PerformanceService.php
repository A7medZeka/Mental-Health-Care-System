<?php
class PerformanceService {
    private $db;

    public function __construct() {
        $this->db = SingletonDatabase::getInstance()->getConnection();
    }

    public function getDashboardData(int $days): array {
        // 1. Fetch all active therapists
        $stmt = $this->db->prepare("
            SELECT u.user_id, u.first_name, u.last_name, t.specialization 
            FROM users u 
            LEFT JOIN therapists t ON u.user_id = t.therapist_id 
            WHERE u.role = 'Therapist' 
            AND u.status IN ('Active', 'Registered', 'Screened', 'Matched')
        ");
        $stmt->execute();
        $therapists = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // 2. Fetch Global KPIs for the selected period
        $kpis = ['avg_rating' => 0.0, 'total_reviews' => 0, 'sessions_completed' => 0, 'no_show_rate' => 0.0];

        // Global Sessions
        $sessStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'Completed' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $sessStmt->execute([$days]);
        $kpis['sessions_completed'] = (int)$sessStmt->fetchColumn();

        // Global No-Show Rate
        $totApptStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $totApptStmt->execute([$days]);
        $totAppts = (int)$totApptStmt->fetchColumn();

        $nsStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE status = 'No-Show' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $nsStmt->execute([$days]);
        $nsAppts = (int)$nsStmt->fetchColumn();
        $kpis['no_show_rate'] = $totAppts > 0 ? round(($nsAppts / $totAppts) * 100, 1) : 0.0;

        // Global Reviews
        $revStmt = $this->db->prepare("SELECT AVG(rating) as avg_r, COUNT(*) as cnt FROM therapist_reviews WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
        $revStmt->execute([$days]);
        $revData = $revStmt->fetch(PDO::FETCH_ASSOC);

        $kpis['avg_rating'] = $revData['cnt'] > 0 ? round((float)$revData['avg_r'], 1) : 0.0;
        $kpis['total_reviews'] = (int)$revData['cnt'];

        // 3. Fetch Real Data for Each Therapist (NO MOCK DATA)
        $colors = ['#2F8F7E', '#48B6A2', '#F4B41A', '#8F5E2F', '#6c757d'];

        foreach ($therapists as $index => &$t) {
            $tid = $t['user_id'];
            $t['initials'] = strtoupper(substr($t['first_name'], 0, 1) . substr($t['last_name'], 0, 1));
            $t['color'] = $colors[$index % 5];

            // REAL Rating & Reviews
            $trStmt = $this->db->prepare("SELECT AVG(rating) as r_avg, COUNT(*) as r_count FROM therapist_reviews WHERE therapist_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $trStmt->execute([$tid, $days]);
            $trData = $trStmt->fetch(PDO::FETCH_ASSOC);
            $t['rating'] = round((float)($trData['r_avg'] ?? 0), 1);
            $t['reviews_count'] = (int)($trData['r_count'] ?? 0);

            // REAL Sessions
            $tSessStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE therapist_id = ? AND status = 'Completed' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tSessStmt->execute([$tid, $days]);
            $tSess = (int)$tSessStmt->fetchColumn();

            // REAL Patients
            $tPatStmt = $this->db->prepare("SELECT COUNT(DISTINCT patient_id) FROM appointments WHERE therapist_id = ? AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tPatStmt->execute([$tid, $days]);
            $tPat = (int)$tPatStmt->fetchColumn();

            // REAL No-Show Rate
            $tTotApptStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE therapist_id = ? AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tTotApptStmt->execute([$tid, $days]);
            $tTotAppts = (int)$tTotApptStmt->fetchColumn();

            $tNsStmt = $this->db->prepare("SELECT COUNT(*) FROM appointments WHERE therapist_id = ? AND status = 'No-Show' AND appointment_date >= DATE_SUB(NOW(), INTERVAL ? DAY)");
            $tNsStmt->execute([$tid, $days]);
            $tNs = (int)$tNsStmt->fetchColumn();

            $tNoShowRate = $tTotAppts > 0 ? round(($tNs / $tTotAppts) * 100, 1) : 0.0;

            // Default to 0% for everything if there are no reviews
            $breakdown = ['star5' => 0, 'star4' => 0, 'star3' => 0, 'star2' => 0, 'star1' => 0];

            if ($t['reviews_count'] > 0) {
                // Calculate REAL Rating Breakdown
                $bdStmt = $this->db->prepare("
                    SELECT rating, COUNT(*) as cnt 
                    FROM therapist_reviews 
                    WHERE therapist_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY) 
                    GROUP BY rating
                ");
                $bdStmt->execute([$tid, $days]);
                $bdRows = $bdStmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($bdRows as $row) {
                    $star = round((float)$row['rating']);
                    if ($star >= 1 && $star <= 5) {
                        $breakdown['star' . $star] = round(($row['cnt'] / $t['reviews_count']) * 100);
                    }
                }
            }

            // Fetch REAL Recent Feedback (If none exists, it will return an empty array)
            $fbStmt = $this->db->prepare("
                SELECT rating as stars, created_at, review_text as text 
                FROM therapist_reviews 
                WHERE therapist_id = ? 
                  AND review_text IS NOT NULL 
                  AND TRIM(review_text) != '' 
                  AND created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
                ORDER BY created_at DESC 
                LIMIT 5
            ");
            $fbStmt->execute([$tid, $days]);
            $feedbackRows = $fbStmt->fetchAll(PDO::FETCH_ASSOC);

            $feedback = [];
            foreach ($feedbackRows as $fb) {
                $feedback[] = [
                    'stars' => round((float)$fb['stars']),
                    'time'  => date('M j, Y', strtotime($fb['created_at'])),
                    'text'  => htmlspecialchars($fb['text'], ENT_QUOTES, 'UTF-8')
                ];
            }

            // Bind everything to the therapist details
            $t['details'] = [
                'sessions'  => $tSess,
                'patients'  => $tPat,
                'no_show'   => $tNoShowRate,
                'breakdown' => $breakdown,
                'feedback'  => $feedback
            ];
        }

        // Sort therapists by rating (highest first)
        usort($therapists, fn($a, $b) => $b['rating'] <=> $a['rating']);

        return ['kpis' => $kpis, 'therapists' => $therapists];
    }
}