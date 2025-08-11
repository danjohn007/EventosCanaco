<?php
ob_start();
?>

<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">Eventos</h1>
    <a href="/public/eventos/crear" class="btn btn-canaco">
        <i class="fas fa-plus me-2"></i>Crear Evento
    </a>
</div>

<!-- Filters -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">Buscar</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($search) ?>" placeholder="Título o descripción">
            </div>
            <div class="col-md-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select" id="estado" name="estado">
                    <option value="">Todos</option>
                    <option value="borrador" <?= $estado === 'borrador' ? 'selected' : '' ?>>Borrador</option>
                    <option value="publicado" <?= $estado === 'publicado' ? 'selected' : '' ?>>Publicado</option>
                    <option value="cerrado" <?= $estado === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-outline-canaco me-2">
                    <i class="fas fa-search me-1"></i>Filtrar
                </button>
                <a href="/public/eventos" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-1"></i>Limpiar
                </a>
            </div>
            <div class="col-md-2 d-flex align-items-end justify-content-end">
                <small class="text-muted"><?= $total ?> evento(s) encontrado(s)</small>
            </div>
        </form>
    </div>
</div>

<!-- Events Grid -->
<?php if (empty($events)): ?>
    <div class="card shadow">
        <div class="card-body text-center py-5">
            <i class="fas fa-calendar-plus fa-3x text-gray-300 mb-3"></i>
            <h5>No hay eventos</h5>
            <p class="text-muted">No se encontraron eventos con los filtros aplicados.</p>
            <a href="/public/eventos/crear" class="btn btn-canaco">Crear Primer Evento</a>
        </div>
    </div>
<?php else: ?>
    <div class="row">
        <?php foreach ($events as $event): ?>
        <div class="col-lg-4 col-md-6 mb-4">
            <div class="card event-card h-100 shadow-sm">
                <?php if ($event['imagen']): ?>
                <img src="/public/storage/uploads/<?= htmlspecialchars($event['imagen']) ?>" 
                     class="card-img-top" style="height: 200px; object-fit: cover;">
                <?php endif; ?>
                
                <div class="card-body d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h5 class="card-title mb-0"><?= htmlspecialchars($event['titulo']) ?></h5>
                        <span class="badge bg-<?= $event['estado'] === 'publicado' ? 'success' : ($event['estado'] === 'borrador' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($event['estado']) ?>
                        </span>
                    </div>
                    
                    <p class="text-muted small mb-2">
                        <i class="fas fa-calendar me-1"></i>
                        <?= Utils::formatDate($event['fecha_inicio']) ?>
                        <?php if ($event['fecha_fin']): ?>
                            - <?= Utils::formatDate($event['fecha_fin']) ?>
                        <?php endif; ?>
                    </p>
                    
                    <p class="text-muted small mb-2">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?= htmlspecialchars(Utils::truncate($event['ubicacion'], 40)) ?>
                    </p>
                    
                    <?php if ($event['descripcion']): ?>
                    <p class="card-text text-muted small mb-3">
                        <?= htmlspecialchars(Utils::truncate($event['descripcion'], 100)) ?>
                    </p>
                    <?php endif; ?>
                    
                    <!-- Registration Stats -->
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <small class="text-muted d-block">Registros</small>
                            <strong><?= $event['total_registrations'] ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Cupo</small>
                            <strong><?= $event['cupo'] ?: '∞' ?></strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Asistencia</small>
                            <strong><?= $event['total_attendance'] ?></strong>
                        </div>
                    </div>
                    
                    <?php if ($event['cupo'] > 0): ?>
                    <div class="progress mb-3" style="height: 6px;">
                        <?php 
                        $percentage = ($event['total_registrations'] / $event['cupo']) * 100;
                        $percentage = min(100, $percentage);
                        ?>
                        <div class="progress-bar bg-canaco" style="width: <?= $percentage ?>%"></div>
                    </div>
                    <small class="text-muted text-center d-block mb-3">
                        <?= round($percentage, 1) ?>% ocupación
                    </small>
                    <?php endif; ?>
                    
                    <!-- Action Buttons -->
                    <div class="mt-auto">
                        <div class="btn-group w-100" role="group">
                            <a href="/public/eventos/editar?id=<?= $event['id'] ?>" 
                               class="btn btn-outline-primary btn-sm" title="Editar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="/public/eventos/asistentes?id=<?= $event['id'] ?>" 
                               class="btn btn-outline-info btn-sm" title="Asistentes">
                                <i class="fas fa-users"></i>
                            </a>
                            <?php if ($event['estado'] === 'publicado'): ?>
                            <a href="/public/evento/<?= $event['slug'] ?>" 
                               class="btn btn-outline-success btn-sm" title="Ver Público" target="_blank">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                            <?php endif; ?>
                            <button class="btn btn-outline-secondary btn-sm dropdown-toggle" 
                                    type="button" data-bs-toggle="dropdown" title="Más opciones">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#"><i class="fas fa-copy me-2"></i>Clonar</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-download me-2"></i>Exportar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" data-confirm="¿Está seguro de eliminar este evento?">
                                    <i class="fas fa-trash me-2"></i>Eliminar
                                </a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navegación de eventos">
        <ul class="pagination justify-content-center">
            <?php if ($currentPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = max(1, $currentPage - 2); $i <= min($totalPages, $currentPage + 2); $i++): ?>
            <li class="page-item <?= $i === $currentPage ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
            
            <?php if ($currentPage < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search) ?>&estado=<?= urlencode($estado) ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
<?php endif; ?>

<?php
$content = ob_get_clean();
$page_title = 'Eventos - CANACO Eventos';
include __DIR__ . '/../layouts/admin.php';
?>