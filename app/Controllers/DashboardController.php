<?php
require_once 'BaseController.php';

class DashboardController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        // Get dashboard metrics
        $metrics = $this->getDashboardMetrics();
        
        // Get recent events
        $recentEvents = $this->getRecentEvents();
        
        $this->view('dashboard/index', [
            'user' => $user,
            'metrics' => $metrics,
            'events' => $recentEvents,
            'page_title' => 'Dashboard'
        ]);
    }
    
    private function getDashboardMetrics() {
        $today = date('Y-m-d');
        $tomorrow = date('Y-m-d', strtotime('+1 day'));
        
        // Total events
        $totalEvents = $this->db->fetch(
            "SELECT COUNT(*) as count FROM eventos WHERE estado != 'borrador'"
        )['count'];
        
        // Events today
        $eventsToday = $this->db->fetch(
            "SELECT COUNT(*) as count FROM eventos 
             WHERE DATE(fecha_inicio) = ? AND estado = 'publicado'",
            [$today]
        )['count'];
        
        // Upcoming events (next 7 days)
        $upcomingEvents = $this->db->fetch(
            "SELECT COUNT(*) as count FROM eventos 
             WHERE fecha_inicio BETWEEN ? AND DATE_ADD(?, INTERVAL 7 DAY) 
             AND estado = 'publicado'",
            [$tomorrow, $today]
        )['count'];
        
        // Total registrations
        $totalRegistrations = $this->db->fetch(
            "SELECT COUNT(*) as count FROM registros WHERE estatus != 'cancelado'"
        )['count'];
        
        // Registrations today
        $registrationsToday = $this->db->fetch(
            "SELECT COUNT(*) as count FROM registros 
             WHERE DATE(created_at) = ? AND estatus != 'cancelado'",
            [$today]
        )['count'];
        
        // Attendance today
        $attendanceToday = $this->db->fetch(
            "SELECT COUNT(*) as count FROM registros r
             JOIN eventos e ON r.evento_id = e.id
             WHERE DATE(e.fecha_inicio) = ? AND r.estatus = 'asistio'",
            [$today]
        )['count'];
        
        // Average occupation rate
        $occupationData = $this->db->fetch(
            "SELECT 
                AVG(CASE WHEN e.cupo > 0 THEN (r.registered / e.cupo) * 100 ELSE 0 END) as avg_occupation
             FROM eventos e
             LEFT JOIN (
                SELECT evento_id, COUNT(*) as registered 
                FROM registros 
                WHERE estatus != 'cancelado' 
                GROUP BY evento_id
             ) r ON e.id = r.evento_id
             WHERE e.estado = 'publicado'"
        );
        
        $avgOccupation = round($occupationData['avg_occupation'] ?? 0, 1);
        
        return [
            'total_events' => $totalEvents,
            'events_today' => $eventsToday,
            'upcoming_events' => $upcomingEvents,
            'total_registrations' => $totalRegistrations,
            'registrations_today' => $registrationsToday,
            'attendance_today' => $attendanceToday,
            'avg_occupation' => $avgOccupation
        ];
    }
    
    private function getRecentEvents() {
        $limit = Auth::isSuperAdmin() ? "" : "AND e.gestor_id = " . $this->getCurrentUser()['id'];
        
        return $this->db->fetchAll(
            "SELECT 
                e.*,
                COUNT(r.id) as total_registrations,
                COUNT(CASE WHEN r.estatus = 'asistio' THEN 1 END) as total_attendance
             FROM eventos e
             LEFT JOIN registros r ON e.id = r.evento_id AND r.estatus != 'cancelado'
             WHERE 1=1 $limit
             GROUP BY e.id
             ORDER BY e.created_at DESC
             LIMIT 10"
        );
    }
}