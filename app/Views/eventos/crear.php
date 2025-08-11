<?php
ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Crear Evento</h1>
    <a href="/public/eventos" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Eventos
    </a>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Información del Evento</h6>
            </div>
            <div class="card-body">
                <?php if (isset($errors['general'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?= htmlspecialchars($errors['general']) ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                    
                    <!-- Título -->
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título del Evento <span class="text-danger">*</span></label>
                        <input type="text" class="form-control <?= isset($errors['titulo']) ? 'is-invalid' : '' ?>" 
                               id="titulo" name="titulo" value="<?= htmlspecialchars($data['titulo'] ?? '') ?>" required>
                        <?php if (isset($errors['titulo'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['titulo']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Descripción -->
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4"
                                  placeholder="Describe el evento, agenda, beneficios, etc."><?= htmlspecialchars($data['descripcion'] ?? '') ?></textarea>
                    </div>
                    
                    <!-- Fechas -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha y Hora de Inicio <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control <?= isset($errors['fecha_inicio']) ? 'is-invalid' : '' ?>" 
                                       id="fecha_inicio" name="fecha_inicio" value="<?= htmlspecialchars($data['fecha_inicio'] ?? '') ?>" required>
                                <?php if (isset($errors['fecha_inicio'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['fecha_inicio']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha_fin" class="form-label">Fecha y Hora de Fin</label>
                                <input type="datetime-local" class="form-control" 
                                       id="fecha_fin" name="fecha_fin" value="<?= htmlspecialchars($data['fecha_fin'] ?? '') ?>">
                                <small class="form-text text-muted">Opcional, si el evento tiene duración específica</small>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Ubicación -->
                    <div class="mb-3">
                        <label for="ubicacion" class="form-label">Ubicación <span class="text-danger">*</span></label>
                        <textarea class="form-control <?= isset($errors['ubicacion']) ? 'is-invalid' : '' ?>" 
                                  id="ubicacion" name="ubicacion" rows="2" required
                                  placeholder="Dirección completa del evento"><?= htmlspecialchars($data['ubicacion'] ?? '') ?></textarea>
                        <?php if (isset($errors['ubicacion'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['ubicacion']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Configuración -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="cupo" class="form-label">Cupo Máximo</label>
                                <input type="number" class="form-control" id="cupo" name="cupo" 
                                       value="<?= htmlspecialchars($data['cupo'] ?? '') ?>" min="0">
                                <small class="form-text text-muted">0 = Sin límite</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="costo" class="form-label">Costo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="costo" name="costo" 
                                           value="<?= htmlspecialchars($data['costo'] ?? '0') ?>" min="0" step="0.01">
                                </div>
                                <small class="form-text text-muted">0 = Evento gratuito</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="tipo_publico" class="form-label">Tipo de Público</label>
                                <select class="form-select" id="tipo_publico" name="tipo_publico">
                                    <option value="todos" <?= ($data['tipo_publico'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>
                                        Todos (Empresas e Invitados)
                                    </option>
                                    <option value="solo_empresas" <?= ($data['tipo_publico'] ?? '') === 'solo_empresas' ? 'selected' : '' ?>>
                                        Solo Empresas
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Imagen -->
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen del Evento</label>
                        <input type="file" class="form-control <?= isset($errors['imagen']) ? 'is-invalid' : '' ?>" 
                               id="imagen" name="imagen" accept="image/*">
                        <small class="form-text text-muted">Formatos aceptados: JPG, PNG, GIF. Máximo 2MB.</small>
                        <?php if (isset($errors['imagen'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['imagen']) ?></div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Estado -->
                    <div class="mb-4">
                        <label for="estado" class="form-label">Estado del Evento</label>
                        <select class="form-select" id="estado" name="estado">
                            <option value="borrador" <?= ($data['estado'] ?? 'borrador') === 'borrador' ? 'selected' : '' ?>>
                                Borrador (no visible al público)
                            </option>
                            <option value="publicado" <?= ($data['estado'] ?? '') === 'publicado' ? 'selected' : '' ?>>
                                Publicado (visible para registro)
                            </option>
                        </select>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="/public/eventos" class="btn btn-outline-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-canaco">
                            <i class="fas fa-save me-2"></i>Crear Evento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold text-primary">Ayuda</h6>
            </div>
            <div class="card-body">
                <h6>Consejos para crear un evento exitoso:</h6>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item px-0">
                        <strong>Título:</strong> Sea descriptivo y atractivo
                    </li>
                    <li class="list-group-item px-0">
                        <strong>Descripción:</strong> Incluya agenda, beneficios y requisitos
                    </li>
                    <li class="list-group-item px-0">
                        <strong>Ubicación:</strong> Proporcione dirección completa y referencias
                    </li>
                    <li class="list-group-item px-0">
                        <strong>Imagen:</strong> Use imágenes de alta calidad relacionadas al evento
                    </li>
                    <li class="list-group-item px-0">
                        <strong>Cupo:</strong> Considere el espacio físico y los recursos disponibles
                    </li>
                </ul>
                
                <div class="mt-3">
                    <h6>Después de crear:</h6>
                    <p class="small text-muted">
                        Podrá configurar campos adicionales del formulario de registro, 
                        enviar invitaciones y gestionar asistentes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
$page_title = 'Crear Evento - CANACO Eventos';
include __DIR__ . '/../layouts/admin.php';
?>