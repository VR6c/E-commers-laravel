@props([
    'id',
    'confirmId',
    'title'        => 'Confirm Deletion',
    'message'      => 'Are you sure you want to delete this item? This action cannot be undone.',
    'confirmLabel' => 'Delete',
    'cancelLabel'  => 'Cancel',
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 420px;">
        <div class="modal-content border-0 shadow-xl rounded-4 overflow-hidden">
            <div class="modal-body text-center p-4 pt-5">
                <div class="admin-delete-modal__icon mb-3">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <h5 class="fw-bold text-dark mb-2" id="{{ $id }}Label">{{ $title }}</h5>
                <p class="text-muted small mb-0 px-2" style="line-height: 1.5;">{{ $message }}</p>
            </div>
            <div class="modal-footer bg-light border-0 justify-content-center p-3 gap-2 pb-4">
                <button type="button" class="btn btn-secondary px-4 py-2" data-bs-dismiss="modal">
                    {{ $cancelLabel }}
                </button>
                <button type="button" class="btn btn-danger px-4 py-2 shadow-sm" id="{{ $confirmId }}">
                    <i class="bi bi-trash-fill me-1"></i> {{ $confirmLabel }}
                </button>
            </div>
        </div>
    </div>
</div>
