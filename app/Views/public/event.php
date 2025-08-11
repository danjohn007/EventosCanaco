<?php
ob_start();
?>

<div class="container-fluid p-0">
    <div class="row g-0 min-vh-100">
        <!-- Left side - Event Banner/Image -->
        <div class="col-lg-6 d-flex align-items-center justify-content-center bg-light">
            <div class="p-4 text-center">
                <?php if ($event['imagen']): ?>
                    <img src="/public/storage/uploads/<?= htmlspecialchars($event['imagen']) ?>" 
                         class="img-fluid rounded shadow-lg mb-4" style="max-height: 400px;">
                <?php else: ?>
                    <!-- Default CANACO event image/banner -->
                    <div class="bg-canaco text-white rounded shadow-lg p-5 mb-4" style="min-height: 300px; display: flex; align-items: center; justify-content: center;">
                        <div class="text-center">
                            <div class="mb-3">
                                <div style="background: white; color: var(--canaco-green); padding: 20px; border-radius: 10px; display: inline-block;">
                                    <h4 class="mb-0">CÁMARA DE COMERCIO</h4>
                                    <p class="mb-0">SERVICIOS Y TURISMO DE QUERÉTARO</p>
                                </div>
                            </div>
                            <h3>Te invita al encuentro de negocios y cocktail donde presentaremos la plataforma tecnológica:</h3>
                            <h2 class="text-warning">enlacecanaco.org</h2>
                            <div class="row text-center mt-4">
                                <div class="col-3">
                                    <div class="border border-light rounded p-2">
                                        <small>INVITADO ESPECIAL</small>
                                        <div>SERGIO ANTONIO</div>
                                        <div>VITO CONTRERAS</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="border border-light rounded p-2">
                                        <small>CODE DRESS</small>
                                        <div>FORMAL</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="border border-light rounded p-2">
                                        <small>SEDE</small>
                                        <div>SALÓN</div>
                                        <div>SOCIAL</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="border border-light rounded p-2">
                                        <small>FECHA</small>
                                        <div><?= date('d/M', strtotime($event['fecha_inicio'])) ?></div>
                                        <div><?= date('Y', strtotime($event['fecha_inicio'])) ?></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <small>HORA:</small>
                                <div><?= date('H:i', strtotime($event['fecha_inicio'])) ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Event branding footer -->
                <div class="text-muted">
                    <small>Powered by CANACO Eventos</small>
                </div>
            </div>
        </div>
        
        <!-- Right side - Event Details and Registration -->
        <div class="col-lg-6 bg-canaco text-white">
            <div class="p-4 p-lg-5">
                <!-- Event Header -->
                <div class="mb-4">
                    <h2 class="mb-3"><?= htmlspecialchars($event['titulo']) ?></h2>
                    
                    <div class="row g-3 mb-4">
                        <div class="col-sm-6">
                            <strong>ID del evento:</strong> <?= $event['id'] ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>Fecha:</strong> <?= Utils::formatDate($event['fecha_inicio'], 'Y-m-d') ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>Hora del evento:</strong> <?= Utils::formatDate($event['fecha_inicio'], 'H:i') ?>
                        </div>
                        <div class="col-sm-6">
                            <strong>Boletos disponibles:</strong> 
                            <?php if ($event['cupo'] > 0): ?>
                                <?= max(0, $event['cupo'] - $registration_count) ?>
                            <?php else: ?>
                                Ilimitados
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <strong>Descripción del evento:</strong>
                        <p><?= htmlspecialchars($event['descripcion']) ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <strong>Dirección del evento:</strong>
                        <p><?= htmlspecialchars($event['ubicacion']) ?></p>
                    </div>
                    
                    <div class="mb-4">
                        <strong>Acceso del público:</strong> 
                        <?= $event['tipo_publico'] === 'solo_empresas' ? 'Solo Empresas' : 'Todos' ?>
                    </div>
                </div>
                
                <!-- Registration Form -->
                <?php if ($is_full): ?>
                    <div class="alert alert-warning">
                        <h5>Evento Lleno</h5>
                        <p>Este evento ha alcanzado su capacidad máxima. No se pueden realizar más registros.</p>
                    </div>
                <?php else: ?>
                    <div class="card bg-white text-dark">
                        <div class="card-body">
                            <h5 class="card-title text-canaco mb-4">¿Eres empresa o invitado general?</h5>
                            
                            <!-- Type Selection -->
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <select class="form-select" id="tipoSelector">
                                        <option value="empresa">Empresa</option>
                                        <?php if ($event['tipo_publico'] === 'todos'): ?>
                                        <option value="invitado">Invitado general</option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <button type="button" class="btn btn-canaco w-100" id="buscarBtn">
                                        Buscar
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Lookup Input -->
                            <div class="mb-3">
                                <input type="text" class="form-control" id="lookupInput" 
                                       placeholder="Ingrese su RFC" name="lookup">
                                <small class="form-text text-muted" id="lookupHelp">
                                    Ingrese su RFC para verificar si tiene datos registrados anteriormente
                                </small>
                            </div>
                            
                            <!-- Registration Form (initially hidden) -->
                            <div id="registrationForm" style="display: none;">
                                <form id="eventRegistrationForm">
                                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                    <input type="hidden" name="evento_id" value="<?= $event['id'] ?>">
                                    <input type="hidden" name="tipo" id="tipoHidden" value="empresa">
                                    
                                    <div id="formFields">
                                        <!-- Dynamic form fields will be inserted here -->
                                    </div>
                                    
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-canaco btn-lg">
                                            <i class="fas fa-ticket-alt me-2"></i>
                                            <span id="submitButtonText">Obtener boleto</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                            
                            <!-- Registration Success -->
                            <div id="registrationSuccess" style="display: none;" class="text-center">
                                <div class="alert alert-success">
                                    <h5><i class="fas fa-check-circle me-2"></i>¡Registro Exitoso!</h5>
                                    <p>Su código de acceso es: <strong id="codigoGenerado"></strong></p>
                                    <p>Le hemos enviado un correo con su boleto y código QR.</p>
                                </div>
                                <button type="button" class="btn btn-outline-canaco" onclick="window.print()">
                                    <i class="fas fa-print me-2"></i>Imprimir Comprobante
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Footer Links -->
                <div class="mt-4 text-center">
                    <a href="/public/historial" class="text-white text-decoration-none">
                        <i class="fas fa-history me-1"></i>Ver historial de eventos
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tipoSelector = document.getElementById('tipoSelector');
    const lookupInput = document.getElementById('lookupInput');
    const lookupHelp = document.getElementById('lookupHelp');
    const buscarBtn = document.getElementById('buscarBtn');
    const registrationForm = document.getElementById('registrationForm');
    const formFields = document.getElementById('formFields');
    const tipoHidden = document.getElementById('tipoHidden');
    
    // Update placeholder and help text based on type
    function updateLookupPlaceholder() {
        const tipo = tipoSelector.value;
        tipoHidden.value = tipo;
        
        if (tipo === 'empresa') {
            lookupInput.placeholder = 'Ingrese su RFC';
            lookupHelp.textContent = 'Ingrese su RFC para verificar si tiene datos registrados anteriormente';
        } else {
            lookupInput.placeholder = 'Ingrese su teléfono';
            lookupHelp.textContent = 'Ingrese su teléfono para verificar si tiene datos registrados anteriormente';
        }
        
        lookupInput.value = '';
        registrationForm.style.display = 'none';
    }
    
    tipoSelector.addEventListener('change', updateLookupPlaceholder);
    
    // Handle lookup
    buscarBtn.addEventListener('click', function() {
        const tipo = tipoSelector.value;
        const lookup = lookupInput.value.trim();
        
        if (!lookup) {
            alert('Por favor ingrese su ' + (tipo === 'empresa' ? 'RFC' : 'teléfono'));
            return;
        }
        
        // Send lookup request
        fetch('/public/registro', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                evento_id: '<?= $event['id'] ?>',
                tipo: tipo,
                lookup: lookup
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'found') {
                // Pre-fill form with existing data
                showRegistrationForm(data.data);
            } else {
                // Show empty form
                showRegistrationForm();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showRegistrationForm();
        });
    });
    
    function showRegistrationForm(existingData = null) {
        const tipo = tipoSelector.value;
        let fields = '';
        
        // Common fields
        fields += `
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Nombre completo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" name="nombre" required 
                           value="${existingData?.nombre || ''}">
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" name="email" required 
                           value="${existingData?.email || ''}">
                </div>
            </div>
        `;
        
        if (tipo === 'empresa') {
            fields += `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">RFC <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="rfc" required 
                               value="${existingData?.rfc || lookupInput.value}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Razón Social <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="razon_social" required 
                               value="${existingData?.razon_social || ''}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombre comercial</label>
                        <input type="text" class="form-control" name="nombre_comercial" 
                               value="${existingData?.nombre_comercial || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Puesto</label>
                        <input type="text" class="form-control" name="puesto" 
                               value="${existingData?.puesto || ''}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección comercial</label>
                    <textarea class="form-control" name="direccion_comercial" rows="2">${existingData?.direccion_comercial || ''}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Dirección fiscal</label>
                    <textarea class="form-control" name="direccion_fiscal" rows="2">${existingData?.direccion_fiscal || ''}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">¿Qué vende?</label>
                        <select class="form-select" name="vende">
                            <option value="">Seleccionar...</option>
                            <option value="productos" ${existingData?.vende === 'productos' ? 'selected' : ''}>Productos</option>
                            <option value="servicios" ${existingData?.vende === 'servicios' ? 'selected' : ''}>Servicios</option>
                            <option value="ambos" ${existingData?.vende === 'ambos' ? 'selected' : ''}>Ambos</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono de oficina</label>
                        <input type="tel" class="form-control" name="telefono_oficina" 
                               value="${existingData?.telefono_oficina || ''}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha aniversario</label>
                        <input type="date" class="form-control" name="fecha_aniversario" 
                               value="${existingData?.fecha_aniversario || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Número de afiliación</label>
                        <input type="text" class="form-control" name="numero_afiliacion" 
                               value="${existingData?.numero_afiliacion || ''}">
                    </div>
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="es_consejero" value="1" 
                               ${existingData?.es_consejero ? 'checked' : ''}>
                        <label class="form-check-label">
                            Soy consejero de la Cámara de Comercio de Querétaro
                        </label>
                    </div>
                </div>
            `;
        } else {
            fields += `
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" name="telefono" required 
                               value="${existingData?.telefono || lookupInput.value}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">WhatsApp</label>
                        <input type="tel" class="form-control" name="whatsapp" 
                               value="${existingData?.whatsapp || ''}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Fecha de nacimiento</label>
                        <input type="date" class="form-control" name="fecha_nacimiento" 
                               value="${existingData?.fecha_nacimiento || ''}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Cargo gubernamental</label>
                        <input type="text" class="form-control" name="cargo_gubernamental" 
                               value="${existingData?.cargo_gubernamental || ''}">
                    </div>
                </div>
            `;
        }
        
        formFields.innerHTML = fields;
        registrationForm.style.display = 'block';
    }
    
    // Handle form submission
    document.getElementById('eventRegistrationForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch('/public/registro', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('codigoGenerado').textContent = data.codigo;
                registrationForm.style.display = 'none';
                document.getElementById('registrationSuccess').style.display = 'block';
            } else if (data.errors) {
                let errorMsg = 'Por favor corrija los siguientes errores:\n';
                Object.values(data.errors).forEach(error => {
                    errorMsg += '- ' + error + '\n';
                });
                alert(errorMsg);
            } else {
                alert(data.error || 'Error en el registro');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error en el registro. Por favor intente nuevamente.');
        });
    });
});
</script>

<?php
$content = ob_get_clean();
$page_title = htmlspecialchars($event['titulo']) . ' - CANACO Eventos';
include __DIR__ . '/../layouts/public.php';
?>