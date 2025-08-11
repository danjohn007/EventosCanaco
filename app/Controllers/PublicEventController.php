<?php
require_once 'BaseController.php';

class PublicEventController extends BaseController {
    
    public function __construct() {
        // Don't require auth for public pages
        $this->db = Database::getInstance();
        
        // If database is not available, show error message
        if (!$this->db->isConnected()) {
            die('Database not available. Please contact administrator.');
        }
    }
    
    public function show() {
        $slug = $this->getInput('slug');
        
        if (!$slug) {
            http_response_code(404);
            die('Event not found');
        }
        
        // Get event
        $event = $this->db->fetch(
            "SELECT * FROM eventos WHERE slug = ? AND estado = 'publicado'",
            [$slug]
        );
        
        if (!$event) {
            http_response_code(404);
            die('Event not found or not published');
        }
        
        // Get registration count
        $registrationCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM registros WHERE evento_id = ? AND estatus != 'cancelado'",
            [$event['id']]
        )['count'];
        
        // Check if event is full
        $isFull = $event['cupo'] > 0 && $registrationCount >= $event['cupo'];
        
        $this->view('public/event', [
            'event' => $event,
            'registration_count' => $registrationCount,
            'is_full' => $isFull,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function register() {
        if (!$this->isPost()) {
            Utils::redirect('/public/');
        }
        
        $eventoId = (int)$this->getInput('evento_id');
        $tipo = $this->getInput('tipo'); // 'empresa' or 'invitado'
        $lookup = $this->getInput('lookup'); // RFC or phone for lookup
        
        if (!$eventoId || !in_array($tipo, ['empresa', 'invitado'])) {
            Utils::jsonResponse(['error' => 'Invalid parameters'], 400);
        }
        
        // Get event
        $event = $this->db->fetch(
            "SELECT * FROM eventos WHERE id = ? AND estado = 'publicado'",
            [$eventoId]
        );
        
        if (!$event) {
            Utils::jsonResponse(['error' => 'Event not found'], 404);
        }
        
        // Check if event is full
        $registrationCount = $this->db->fetch(
            "SELECT COUNT(*) as count FROM registros WHERE evento_id = ? AND estatus != 'cancelado'",
            [$eventoId]
        )['count'];
        
        if ($event['cupo'] > 0 && $registrationCount >= $event['cupo']) {
            Utils::jsonResponse(['error' => 'Event is full'], 400);
        }
        
        // Handle lookup if provided
        if ($lookup) {
            $existingData = null;
            
            if ($tipo === 'empresa') {
                $rfc = Utils::validateRFC($lookup);
                if (!$rfc) {
                    Utils::jsonResponse(['error' => 'Invalid RFC format'], 400);
                }
                
                $existingData = $this->db->fetch(
                    "SELECT * FROM registros WHERE rfc = ? AND estatus != 'cancelado' ORDER BY created_at DESC LIMIT 1",
                    [$rfc]
                );
            } else {
                $phone = Utils::validatePhone($lookup);
                if (!$phone) {
                    Utils::jsonResponse(['error' => 'Invalid phone format'], 400);
                }
                
                $existingData = $this->db->fetch(
                    "SELECT * FROM registros WHERE telefono = ? AND estatus != 'cancelado' ORDER BY created_at DESC LIMIT 1",
                    [$phone]
                );
            }
            
            if ($existingData) {
                // Return existing data for pre-filling
                Utils::jsonResponse([
                    'status' => 'found',
                    'data' => $existingData
                ]);
                return;
            } else {
                Utils::jsonResponse([
                    'status' => 'not_found',
                    'message' => 'No previous registration found. Please fill the complete form.'
                ]);
                return;
            }
        }
        
        // Process full registration
        $this->validateCsrf();
        
        $data = [
            'evento_id' => $eventoId,
            'tipo' => $tipo,
            'nombre' => $this->getInput('nombre'),
            'email' => $this->getInput('email'),
            'telefono' => $this->getInput('telefono')
        ];
        
        $errors = [];
        
        // Basic validation
        if (empty($data['nombre'])) {
            $errors['nombre'] = 'Name is required';
        }
        
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Valid email is required';
        }
        
        if ($tipo === 'empresa') {
            // Company fields
            $data['rfc'] = Utils::validateRFC($this->getInput('rfc'));
            $data['razon_social'] = $this->getInput('razon_social');
            $data['nombre_comercial'] = $this->getInput('nombre_comercial');
            $data['direccion_comercial'] = $this->getInput('direccion_comercial');
            $data['direccion_fiscal'] = $this->getInput('direccion_fiscal');
            $data['puesto'] = $this->getInput('puesto');
            $data['vende'] = $this->getInput('vende');
            $data['telefono_oficina'] = $this->getInput('telefono_oficina');
            $data['fecha_aniversario'] = $this->getInput('fecha_aniversario');
            $data['numero_afiliacion'] = $this->getInput('numero_afiliacion');
            $data['es_consejero'] = $this->getInput('es_consejero') ? 1 : 0;
            
            if (!$data['rfc']) {
                $errors['rfc'] = 'Valid RFC is required';
            }
            
            if (empty($data['razon_social'])) {
                $errors['razon_social'] = 'Company name is required';
            }
        } else {
            // Guest fields
            $data['fecha_nacimiento'] = $this->getInput('fecha_nacimiento');
            $data['cargo_gubernamental'] = $this->getInput('cargo_gubernamental');
            $data['whatsapp'] = $this->getInput('whatsapp');
            
            $data['telefono'] = Utils::validatePhone($data['telefono']);
            if (!$data['telefono']) {
                $errors['telefono'] = 'Valid phone number is required';
            }
        }
        
        // Check for duplicate registration
        $duplicateCheck = "evento_id = ? AND email = ?";
        $duplicateParams = [$eventoId, $data['email']];
        
        if ($tipo === 'empresa' && $data['rfc']) {
            $duplicateCheck .= " AND rfc = ?";
            $duplicateParams[] = $data['rfc'];
        } else if ($tipo === 'invitado' && $data['telefono']) {
            $duplicateCheck .= " AND telefono = ?";
            $duplicateParams[] = $data['telefono'];
        }
        
        $duplicate = $this->db->fetch(
            "SELECT id FROM registros WHERE $duplicateCheck AND estatus != 'cancelado'",
            $duplicateParams
        );
        
        if ($duplicate) {
            $errors['general'] = 'Already registered for this event';
        }
        
        if (!empty($errors)) {
            Utils::jsonResponse(['errors' => $errors], 400);
        }
        
        try {
            // Generate unique code and QR hash
            $data['codigo_unico'] = Utils::generateUniqueCode();
            $data['qr_hash'] = Utils::generateQRHash($eventoId, $data['codigo_unico']);
            $data['estatus'] = 'registrado';
            
            // Build insert query
            $fields = array_keys($data);
            $placeholders = str_repeat('?,', count($fields) - 1) . '?';
            
            $sql = "INSERT INTO registros (" . implode(',', $fields) . ", created_at) 
                    VALUES ($placeholders, datetime('now'))";
            
            $this->db->execute($sql, array_values($data));
            $registroId = $this->db->lastInsertId();
            
            // TODO: Send email with QR code
            
            Utils::jsonResponse([
                'status' => 'success',
                'message' => 'Registration successful',
                'codigo' => $data['codigo_unico'],
                'registro_id' => $registroId
            ]);
            
        } catch (Exception $e) {
            Utils::jsonResponse(['error' => 'Registration failed: ' . $e->getMessage()], 500);
        }
    }
    
    public function historial() {
        $lookup = $this->getInput('lookup'); // RFC or phone
        $tipo = $this->getInput('tipo'); // 'empresa' or 'invitado'
        
        if (!$lookup || !in_array($tipo, ['empresa', 'invitado'])) {
            $this->view('public/historial', [
                'registrations' => [],
                'lookup' => '',
                'tipo' => 'empresa'
            ]);
            return;
        }
        
        $registrations = [];
        
        if ($tipo === 'empresa') {
            $rfc = Utils::validateRFC($lookup);
            if ($rfc) {
                $registrations = $this->db->fetchAll(
                    "SELECT r.*, e.titulo, e.fecha_inicio, e.ubicacion 
                     FROM registros r 
                     JOIN eventos e ON r.evento_id = e.id 
                     WHERE r.rfc = ? 
                     ORDER BY r.created_at DESC",
                    [$rfc]
                );
            }
        } else {
            $phone = Utils::validatePhone($lookup);
            if ($phone) {
                $registrations = $this->db->fetchAll(
                    "SELECT r.*, e.titulo, e.fecha_inicio, e.ubicacion 
                     FROM registros r 
                     JOIN eventos e ON r.evento_id = e.id 
                     WHERE r.telefono = ? 
                     ORDER BY r.created_at DESC",
                    [$phone]
                );
            }
        }
        
        $this->view('public/historial', [
            'registrations' => $registrations,
            'lookup' => $lookup,
            'tipo' => $tipo
        ]);
    }
}