<?php
require_once 'BaseController.php';

class EventosController extends BaseController {
    
    public function index() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        
        // Get filter parameters
        $search = $this->getInput('search', '');
        $estado = $this->getInput('estado', '');
        $page = max(1, (int)$this->getInput('page', 1));
        $perPage = 12;
        
        // Build where clause
        $whereConditions = [];
        $params = [];
        
        if ($search) {
            $whereConditions[] = "(titulo LIKE ? OR descripcion LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if ($estado) {
            $whereConditions[] = "estado = ?";
            $params[] = $estado;
        }
        
        // Filter by user role
        if (!Auth::isSuperAdmin()) {
            $whereConditions[] = "gestor_id = ?";
            $params[] = $user['id'];
        }
        
        $whereClause = $whereConditions ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        // Get total count
        $totalQuery = "SELECT COUNT(*) as count FROM eventos $whereClause";
        $total = $this->db->fetch($totalQuery, $params)['count'];
        
        // Get events with pagination
        $offset = ($page - 1) * $perPage;
        $eventsQuery = "
            SELECT 
                e.*,
                COUNT(r.id) as total_registrations,
                COUNT(CASE WHEN r.estatus = 'asistio' THEN 1 END) as total_attendance
            FROM eventos e
            LEFT JOIN registros r ON e.id = r.evento_id AND r.estatus != 'cancelado'
            $whereClause
            GROUP BY e.id
            ORDER BY e.created_at DESC
            LIMIT $perPage OFFSET $offset
        ";
        
        $events = $this->db->fetchAll($eventsQuery, $params);
        
        // Calculate pagination
        $totalPages = ceil($total / $perPage);
        
        $this->view('eventos/index', [
            'events' => $events,
            'search' => $search,
            'estado' => $estado,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'total' => $total,
            'user' => $user
        ]);
    }
    
    public function crear() {
        $this->requireAuth();
        
        $user = $this->getCurrentUser();
        $errors = [];
        $data = [];
        
        if ($this->isPost()) {
            $this->validateCsrf();
            
            $data = [
                'titulo' => $this->getInput('titulo'),
                'descripcion' => $this->getInput('descripcion'),
                'fecha_inicio' => $this->getInput('fecha_inicio'),
                'fecha_fin' => $this->getInput('fecha_fin'),
                'ubicacion' => $this->getInput('ubicacion'),
                'cupo' => (int)$this->getInput('cupo', 0),
                'costo' => (float)$this->getInput('costo', 0),
                'tipo_publico' => $this->getInput('tipo_publico', 'todos'),
                'estado' => $this->getInput('estado', 'borrador')
            ];
            
            // Validation
            if (empty($data['titulo'])) {
                $errors['titulo'] = 'El título es requerido';
            }
            
            if (empty($data['fecha_inicio'])) {
                $errors['fecha_inicio'] = 'La fecha de inicio es requerida';
            }
            
            if (empty($data['ubicacion'])) {
                $errors['ubicacion'] = 'La ubicación es requerida';
            }
            
            // Handle image upload
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = Utils::uploadFile($_FILES['imagen'], ['jpg', 'jpeg', 'png']);
                if ($imagen) {
                    $data['imagen'] = $imagen;
                } else {
                    $errors['imagen'] = 'Error al subir la imagen';
                }
            }
            
            if (empty($errors)) {
                try {
                    // Generate slug
                    $data['slug'] = Utils::generateSlug($data['titulo']);
                    $data['gestor_id'] = $user['id'];
                    
                    // Insert event
                    $sql = "INSERT INTO eventos (titulo, slug, descripcion, fecha_inicio, fecha_fin, 
                                                ubicacion, cupo, imagen, estado, costo, tipo_publico, gestor_id, created_at) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, datetime('now'))";
                    
                    $this->db->execute($sql, [
                        $data['titulo'], $data['slug'], $data['descripcion'],
                        $data['fecha_inicio'], $data['fecha_fin'], $data['ubicacion'],
                        $data['cupo'], $data['imagen'] ?? null, $data['estado'],
                        $data['costo'], $data['tipo_publico'], $data['gestor_id']
                    ]);
                    
                    $eventoId = $this->db->lastInsertId();
                    
                    // Log action
                    Utils::log($user['id'], 'create', 'eventos', $eventoId, "Evento creado: {$data['titulo']}");
                    
                    Utils::redirect('/public/eventos?success=created');
                    
                } catch (Exception $e) {
                    $errors['general'] = 'Error al crear el evento: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('eventos/crear', [
            'data' => $data,
            'errors' => $errors,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function editar() {
        $this->requireAuth();
        
        $id = (int)$this->getInput('id');
        $user = $this->getCurrentUser();
        
        if (!$id) {
            Utils::redirect('/public/eventos?error=invalid_id');
        }
        
        // Get event
        $event = $this->db->fetch("SELECT * FROM eventos WHERE id = ?", [$id]);
        
        if (!$event) {
            Utils::redirect('/public/eventos?error=not_found');
        }
        
        // Check permissions
        if (!Auth::isSuperAdmin() && $event['gestor_id'] != $user['id']) {
            Utils::redirect('/public/eventos?error=permission_denied');
        }
        
        $errors = [];
        $data = $event;
        
        if ($this->isPost()) {
            $this->validateCsrf();
            
            $data = array_merge($data, [
                'titulo' => $this->getInput('titulo'),
                'descripcion' => $this->getInput('descripcion'),
                'fecha_inicio' => $this->getInput('fecha_inicio'),
                'fecha_fin' => $this->getInput('fecha_fin'),
                'ubicacion' => $this->getInput('ubicacion'),
                'cupo' => (int)$this->getInput('cupo', 0),
                'costo' => (float)$this->getInput('costo', 0),
                'tipo_publico' => $this->getInput('tipo_publico', 'todos'),
                'estado' => $this->getInput('estado', 'borrador')
            ]);
            
            // Validation
            if (empty($data['titulo'])) {
                $errors['titulo'] = 'El título es requerido';
            }
            
            if (empty($data['fecha_inicio'])) {
                $errors['fecha_inicio'] = 'La fecha de inicio es requerida';
            }
            
            if (empty($data['ubicacion'])) {
                $errors['ubicacion'] = 'La ubicación es requerida';
            }
            
            // Handle image upload
            if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
                $imagen = Utils::uploadFile($_FILES['imagen'], ['jpg', 'jpeg', 'png']);
                if ($imagen) {
                    $data['imagen'] = $imagen;
                } else {
                    $errors['imagen'] = 'Error al subir la imagen';
                }
            }
            
            if (empty($errors)) {
                try {
                    // Update event
                    $sql = "UPDATE eventos SET titulo = ?, descripcion = ?, fecha_inicio = ?, 
                                             fecha_fin = ?, ubicacion = ?, cupo = ?, estado = ?, 
                                             costo = ?, tipo_publico = ?, updated_at = datetime('now')";
                    $params = [
                        $data['titulo'], $data['descripcion'], $data['fecha_inicio'],
                        $data['fecha_fin'], $data['ubicacion'], $data['cupo'],
                        $data['estado'], $data['costo'], $data['tipo_publico']
                    ];
                    
                    if (isset($data['imagen']) && $data['imagen'] !== $event['imagen']) {
                        $sql .= ", imagen = ?";
                        $params[] = $data['imagen'];
                    }
                    
                    $sql .= " WHERE id = ?";
                    $params[] = $id;
                    
                    $this->db->execute($sql, $params);
                    
                    // Log action
                    Utils::log($user['id'], 'update', 'eventos', $id, "Evento actualizado: {$data['titulo']}");
                    
                    Utils::redirect('/public/eventos?success=updated');
                    
                } catch (Exception $e) {
                    $errors['general'] = 'Error al actualizar el evento: ' . $e->getMessage();
                }
            }
        }
        
        $this->view('eventos/editar', [
            'event' => $event,
            'data' => $data,
            'errors' => $errors,
            'csrf_token' => $this->generateCsrfToken()
        ]);
    }
    
    public function asistentes() {
        $this->requireAuth();
        
        $id = (int)$this->getInput('id');
        $user = $this->getCurrentUser();
        
        if (!$id) {
            Utils::redirect('/public/eventos?error=invalid_id');
        }
        
        // Get event
        $event = $this->db->fetch("SELECT * FROM eventos WHERE id = ?", [$id]);
        
        if (!$event) {
            Utils::redirect('/public/eventos?error=not_found');
        }
        
        // Check permissions
        if (!Auth::isSuperAdmin() && $event['gestor_id'] != $user['id']) {
            Utils::redirect('/public/eventos?error=permission_denied');
        }
        
        // Get registrations
        $registrations = $this->db->fetchAll(
            "SELECT * FROM registros WHERE evento_id = ? ORDER BY created_at DESC",
            [$id]
        );
        
        $this->view('eventos/asistentes', [
            'event' => $event,
            'registrations' => $registrations
        ]);
    }
}