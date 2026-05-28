<div class="modal fade" id="evidenceDetailModal" tabindex="-1" aria-labelledby="evidenceDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="evidenceDetailModalLabel"><i class="bi bi-info-circle-fill me-2"></i>Detalles de la Marcación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold" id="modalEvidenceUser"></h6>
                        <p class="text-muted small mb-2" id="modalEvidenceDate"></p>
                        
                        <div class="mb-3">
                            <p class="fw-bold mb-1">Descripción:</p>
                            <p id="modalEvidenceDesc" class="bg-light p-3 rounded-3 small" style="min-height: 80px;"></p>
                        </div>
                        
                        <p class="fw-bold mb-1">Ubicación GPS:</p>
                        <div id="modalEvidenceMap"></div>
                    </div>
                    <div class="col-md-6">
                        <p class="fw-bold mb-1">Foto de Evidencia:</p>
                        <div id="modalEvidencePhoto" class="bg-light rounded-3 d-flex align-items-center justify-content-center" style="height: 250px;">
                            <!-- Photo or placeholder will be injected here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const evidenceModal = document.getElementById('evidenceDetailModal');
        if (evidenceModal) {
            evidenceModal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;

                const user = button.getAttribute('data-user');
                const date = button.getAttribute('data-date');
                const desc = button.getAttribute('data-desc');
                const lat = button.getAttribute('data-lat');
                const lon = button.getAttribute('data-lon');
                const foto = button.getAttribute('data-foto');

                const modalUser = evidenceModal.querySelector('#modalEvidenceUser');
                const modalDate = evidenceModal.querySelector('#modalEvidenceDate');
                const modalDesc = evidenceModal.querySelector('#modalEvidenceDesc');
                const modalMap = evidenceModal.querySelector('#modalEvidenceMap');
                const modalPhoto = evidenceModal.querySelector('#modalEvidencePhoto');

                if (modalUser) modalUser.textContent = user || 'Desconocido';
                if (modalDate) modalDate.textContent = date ? `Fecha: ${date}` : '';
                if (modalDesc) modalDesc.textContent = desc || 'No se proporcionó descripción.';

                if (modalMap) {
                    if (lat && lon) {
                        modalMap.innerHTML = `<a href="https://www.google.com/maps?q=${lat},${lon}" target="_blank" class="btn btn-outline-primary"><i class="bi bi-geo-alt-fill me-2"></i>Ver en Google Maps</a>`;
                    } else {
                        modalMap.innerHTML = '<p class="text-muted small">No se registró ubicación.</p>';
                    }
                }

                if (modalPhoto) {
                    if (foto) {
                        modalPhoto.innerHTML = `<a href="${foto}" target="_blank"><img src="${foto}" class="img-fluid rounded-3" style="max-height: 250px;" alt="Evidencia"></a>`;
                    } else {
                        modalPhoto.innerHTML = '<span class="text-muted small">No se adjuntó foto.</span>';
                    }
                }
            });
        }
    });
</script>
