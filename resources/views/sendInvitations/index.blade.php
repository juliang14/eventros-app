@extends('layouts.app')

@section('content')
<div class="p-4">
    {{-- 🏷️ Título --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold text-dark">Gestión de Envío de Invitaciones 💌</h1>
        <p class="text-muted small">Consulta y envía invitaciones por correo o WhatsApp.</p>
    </div>

    {{-- 🎛️ Filtros --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-6">
                    <label for="event_id" class="form-label fw-semibold">Seleccionar evento</label>
                    <select id="event_id" class="form-select" required>
                        <option value="">-- Seleccione un evento --</option>
                        @foreach($events as $event)
                            <option value="{{ $event->id }}">
                                {{ $event->title }} ({{ \Carbon\Carbon::parse($event->event_date)->format('d/m/Y H:i') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-flex justify-content-md-end align-items-center flex-wrap gap-3">
                    <button id="loadInvitations" class="btn btn-primary">
                        <i class="bi bi-search"></i> Consultar
                    </button>

                    {{-- WhatsApp --}}
                    <div class="text-center">
                        <button id="connectWhatsApp" class="btn btn-outline-success w-100 position-relative">
                            <i class="bi bi-whatsapp"></i> 
                            <span id="whatsappButtonText">Conectar WhatsApp</span>
                        </button>
                        <div id="whatsapp-status" class="small mt-1 text-center text-muted">
                            🔴 Sin conexión
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 🧾 Tabla --}}
    <div id="invitations-section" class="card shadow-sm border-0" style="display:none;">
        <div class="card-body">
            <div class="table-responsive">
                <table id="tablaInvitaciones" class="table table-striped table-hover align-middle nowrap" style="width:100%">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Invitado</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Canal</th>
                            <th>Estado</th>
                            <th>Asistencia</th>
                            <th>Última actualización</th>
                        </tr>
                    </thead>
                    <tbody id="invitation-list">
                        <tr>
                            <td colspan="8" class="text-center text-muted">Seleccione un evento para ver las invitaciones</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Botones --}}
            <div id="action-buttons" class="d-flex justify-content-end gap-2 mt-3" style="display:none;">
                {{-- Enviar todas --}}
                <form id="sendAllForm">
                    @csrf
                    <input type="hidden" name="event_id" id="form_event_id">
                    <div class="d-inline-block me-2">
                        <label for="channelSelect" class="form-label fw-semibold mb-1 small text-muted">
                            ¿Cómo deseas enviar las invitaciones?
                        </label>
                        <select name="channel" id="channelSelect" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="whatsapp">💬 Solo WhatsApp</option>
                            <option value="email">📧 Solo Email</option>
                            <option value="both">📬 Ambos</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send"></i> Enviar Todas
                    </button>
                </form>

                {{-- Reenviar fallidas --}}
                <form id="resendFailedForm" style="display:none;">
                    @csrf
                    <input type="hidden" name="event_id" id="form_event_id_failed">
                    <input type="hidden" name="resend_failed" value="1">
                    <div class="d-inline-block me-2">
                        <label for="channelSelectFailed" class="form-label fw-semibold mb-1 small text-muted">
                            ¿Cómo deseas reenviar las fallidas?
                        </label>
                        <select name="channel" id="channelSelectFailed" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="whatsapp">💬 Solo WhatsApp</option>
                            <option value="email">📧 Solo Email</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-warning text-white">
                        <i class="bi bi-arrow-repeat"></i> Reenviar Fallidas
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- 🔳 Modal WhatsApp QR --}}
<div class="modal fade" id="modalWhatsapp" tabindex="-1" aria-labelledby="modalWhatsappLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="modalWhatsappLabel"><i class="bi bi-whatsapp"></i> Conectar WhatsApp Web</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="text-muted small">Escanea este código QR para vincular tu cuenta de WhatsApp.</p>
        <div id="qrContainer" class="border rounded p-3">
            <div class="spinner-border text-success"></div>
            <p class="text-muted mt-2">Esperando QR...</p>
        </div>
      </div>
      <div class="modal-footer">
        <button id="btnRefreshQR" class="btn btn-outline-success">Actualizar QR</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
/* === CONSULTAR INVITACIONES === */
document.getElementById('loadInvitations').addEventListener('click', async () => {
    const eventId = document.getElementById('event_id').value;
    const list = document.getElementById('invitation-list');
    const section = document.getElementById('invitations-section');
    const buttons = document.getElementById('action-buttons');
    const resendForm = document.getElementById('resendFailedForm');

    if (!eventId) {
        Swal.fire('Selecciona un evento', 'Debes seleccionar un evento antes de consultar.', 'warning');
        return;
    }

    list.innerHTML = `<tr><td colspan="8" class="text-center text-muted">
        Cargando invitaciones... <div class="spinner-border spinner-border-sm text-primary ms-2"></div></td></tr>`;

    try {
        const res = await fetch(`/send-invitations/event/${eventId}/guests`);
        const data = await res.json();
        list.innerHTML = '';

        const withInvitations = data.filter(inv => inv.invitation !== null);
        if (withInvitations.length === 0) {
            list.innerHTML = `<tr><td colspan="8" class="text-center text-muted">
                No hay invitaciones previas. Puedes generar el envío ahora.
            </td></tr>`;
        } else {
            let failedCount = 0;
            withInvitations.forEach((inv, index) => {
                const statusClass = inv.invitation?.status === 'sent' ? 'text-success' :
                                    inv.invitation?.status === 'failed' ? 'text-danger' : 'text-secondary';
                if (inv.invitation?.status === 'failed') failedCount++;

                list.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>${inv.guest_name}</td>
                        <td>${inv.email ?? '-'}</td>
                        <td>${inv.phone ?? '-'}</td>
                        <td>${inv.invitation?.channel ?? '-'}</td>
                        <td class="${statusClass} fw-semibold">${inv.invitation?.status ?? '—'}</td>
                        <td>${inv.rsvp ?? '-'}</td>
                        <td>${inv.invitation?.updated_at ?? '-'}</td>
                    </tr>`;
            });
            resendForm.style.display = failedCount > 0 ? 'inline-block' : 'none';
        }

        section.style.display = 'block';
        buttons.style.display = 'flex';
        document.getElementById('form_event_id').value = eventId;
        document.getElementById('form_event_id_failed').value = eventId;

    } catch (error) {
        list.innerHTML = `<tr><td colspan="8" class="text-danger text-center">
            Error al cargar invitaciones: ${error.message}</td></tr>`;
        section.style.display = 'block';
    }
});

/* === ENVÍO DE INVITACIONES === */
document.getElementById('sendAllForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);
    const channel = formData.get('channel');

    Swal.fire({
        title: 'Enviando invitaciones...',
        text: 'Por favor espera mientras se procesan los envíos.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const endpoint =
            channel === 'whatsapp'
                ? '/send-invitations/whatsapp'
                : '/send-invitations/send';

        const res = await fetch(endpoint, { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Proceso completado',
                html: `
                    <p><b>Enviadas correctamente:</b> ${data.sent}</p>
                    <p><b>Fallidas:</b> ${data.failed}</p>
                    <p><b>Total procesadas:</b> ${data.processed ?? data.sent + data.failed}</p>
                `,
            }).then(() => document.getElementById('loadInvitations').click());
        } else {
            Swal.fire('Error', data.message || 'Hubo un problema en el envío.', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'No se pudo contactar con el servidor. ' + err.message, 'error');
    }
});

/* === REENVIAR FALLIDAS === */
document.getElementById('resendFailedForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const form = e.target;
    const formData = new FormData(form);

    Swal.fire({
        title: 'Reenviando invitaciones fallidas...',
        text: 'Esto puede tardar unos segundos.',
        allowOutsideClick: false,
        didOpen: () => Swal.showLoading(),
    });

    try {
        const res = await fetch('/send-invitations/send', { method: 'POST', body: formData });
        const data = await res.json();

        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: '✅ Reenvío completado',
                html: `
                    <p><b>Enviadas correctamente:</b> ${data.sent}</p>
                    <p><b>Fallidas:</b> ${data.failed}</p>
                    <p><b>Total procesadas:</b> ${data.processed}</p>
                `,
            }).then(() => document.getElementById('loadInvitations').click());
        } else {
            Swal.fire('Error', data.message || 'No se pudieron reenviar las invitaciones.', 'error');
        }
    } catch (err) {
        Swal.fire('Error', 'Error de conexión con el servidor. ' + err.message, 'error');
    }
});

/* === CONEXIÓN WHATSAPP (API local Node.js) === */
const qrContainer = document.getElementById('qrContainer');
const whatsappStatus = document.getElementById('whatsapp-status');
const modalWhatsapp = document.getElementById('modalWhatsapp');
let qrInterval = null;
let countdownInterval = null;
let qrExpiresIn = 60;

function startCountdown() {
    const countdownEl = document.getElementById('qrCountdown');
    clearInterval(countdownInterval);
    countdownEl.textContent = `⏳ QR válido por ${qrExpiresIn}s`;
    countdownInterval = setInterval(() => {
        qrExpiresIn--;
        countdownEl.textContent = `⏳ QR válido por ${qrExpiresIn}s`;
        if (qrExpiresIn <= 0) {
            clearInterval(countdownInterval);
            countdownEl.textContent = '⚠️ QR expirado, generando nuevo...';
            loadQRCode(true);
        }
    }, 1000);
}

async function loadQRCode(auto = false) {
    try {
        const response = await fetch("http://localhost:3000/qr");
        const data = await response.json();

        if (data.connected) {
            qrContainer.innerHTML = `<div class="text-success fw-bold">🟢 Sesión activa. Puedes enviar mensajes.</div>`;
            localStorage.setItem('whatsapp_connected', 'true');
            whatsappStatus.innerText = '🟢 Conectado';
            whatsappStatus.className = 'text-success fw-semibold';
            clearInterval(qrInterval);
            clearInterval(countdownInterval);
            const modalInstance = bootstrap.Modal.getInstance(modalWhatsapp);
            if (modalInstance) modalInstance.hide();
            return;
        }

        if (data.qr) {
            qrExpiresIn = 60;
            qrContainer.innerHTML = `<img src="${data.qr}" alt="QR" class="img-fluid rounded m-auto">
                <p id="qrCountdown" class="text-muted small mt-2"></p>`;
            startCountdown();
        } else {
            qrContainer.innerHTML = `<div class="text-muted small">Esperando QR del servidor...</div>`;
        }

        localStorage.setItem('whatsapp_connected', 'false');
        whatsappStatus.innerText = '🔴 Sin conexión';
        whatsappStatus.className = 'text-muted small';

        if (!qrInterval && !auto) {
            qrInterval = setInterval(() => loadQRCode(true), 15000);
        }

    } catch (err) {
        qrContainer.innerHTML = `<div class="text-danger">❌ Error al conectar con el servicio Node.js.</div>`;
        localStorage.setItem('whatsapp_connected', 'false');
        whatsappStatus.innerText = '🔴 Sin conexión';
        whatsappStatus.className = 'text-muted small';
        clearInterval(countdownInterval);
    }
}

document.getElementById('btnRefreshQR').addEventListener('click', () => {
    clearInterval(qrInterval);
    clearInterval(countdownInterval);
    loadQRCode();
});

modalWhatsapp.addEventListener('shown.bs.modal', () => loadQRCode());
modalWhatsapp.addEventListener('hidden.bs.modal', () => {
    clearInterval(qrInterval);
    clearInterval(countdownInterval);
    qrInterval = countdownInterval = null;
});

// ✅ Verificar conexión automáticamente al cargar la vista
document.addEventListener('DOMContentLoaded', () => {
    checkWhatsAppConnection();
});

async function checkWhatsAppConnection() {
    try {
        const response = await fetch("http://localhost:3000/qr");
        const data = await response.json();

        if (data.connected) {
            whatsappStatus.innerText = '🟢 Conectado';
            whatsappStatus.className = 'text-success fw-semibold';
            qrContainer.innerHTML = `<div class="text-success fw-bold">🟢 Sesión activa. Puedes enviar mensajes.</div>`;
            localStorage.setItem('whatsapp_connected', 'true');
            btnText.textContent = 'Desconectar WhatsApp';
            connectBtn.classList.remove('btn-outline-success');
            connectBtn.classList.add('btn-danger');
        } else {
            whatsappStatus.innerText = '🔴 Sin conexión';
            whatsappStatus.className = 'text-muted small';
            localStorage.setItem('whatsapp_connected', 'false');
            btnText.textContent = 'Conectar WhatsApp';
            connectBtn.classList.remove('btn-danger');
            connectBtn.classList.add('btn-outline-success');
        }
    } catch (error) {
        whatsappStatus.innerText = '🔴 Sin conexión (Error API)';
        whatsappStatus.className = 'text-muted small';
        localStorage.setItem('whatsapp_connected', 'false');
        btnText.textContent = 'Conectar WhatsApp';
        connectBtn.classList.remove('btn-danger');
        connectBtn.classList.add('btn-outline-success');
    }
}

/* === BOTÓN CONECTAR / DESCONECTAR WHATSAPP === */
const connectBtn = document.getElementById('connectWhatsApp');
const btnText = document.getElementById('whatsappButtonText');

connectBtn.addEventListener('click', async () => {
    const isConnected = localStorage.getItem('whatsapp_connected') === 'true';

    if (!isConnected) {
        // 🔹 Si no está conectado, abrimos el modal para escanear el QR
        const modal = new bootstrap.Modal(document.getElementById('modalWhatsapp'));
        modal.show();
    } else {
        // 🔹 Si está conectado, pedimos confirmación para cerrar sesión
        const result = await Swal.fire({
            title: '¿Desconectar WhatsApp?',
            text: 'Esto cerrará la sesión activa y deberás volver a escanear el QR para enviar mensajes.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, desconectar',
            cancelButtonText: 'Cancelar'
        });

        if (result.isConfirmed) {
            try {
                const response = await fetch("http://localhost:3000/logout");
                const data = await response.json();

                if (data.success) {
                    Swal.fire('Desconectado', 'La sesión de WhatsApp fue cerrada correctamente.', 'success');
                    localStorage.setItem('whatsapp_connected', 'false');
                    whatsappStatus.innerText = '🔴 Sin conexión';
                    whatsappStatus.className = 'text-muted small';
                    btnText.textContent = 'Conectar WhatsApp';
                    connectBtn.classList.remove('btn-danger');
                    connectBtn.classList.add('btn-outline-success');
                } else {
                    Swal.fire('Error', data.message || 'No se pudo cerrar la sesión.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'No se pudo contactar con el servicio Node.js.', 'error');
            }
        }
    }
});

</script>
@endpush
@endsection
