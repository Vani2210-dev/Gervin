@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl',
    'hasBackdrop' => true
])

@php
$maxWidthPx = [
    'sm'  => '384px',
    'md'  => '448px',
    'lg'  => '512px',
    'xl'  => '576px',
    '2xl' => '672px',
][$maxWidth] ?? '672px';
@endphp

<div
    id="{{ $name }}"
    data-modal
    data-has-backdrop="{{ $hasBackdrop ? 'true' : 'false' }}"
    style="display:{{ $show ? 'block' : 'none' }}; position:fixed; inset:0; z-index:9999999 !important;{{ !$hasBackdrop ? ' pointer-events:none;' : '' }}"
>
    {{-- Backdrop --}}
    @if($hasBackdrop)
    <div
        onclick="closeModal('{{ $name }}')"
        style="position:absolute; inset:0; background:rgba(0,0,0,0.55);">
    </div>
    @endif

    {{-- Content wrapper --}}
    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
        <div data-modal-content style="position:relative; background:#fff; border-radius:0.75rem; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:{{ $maxWidthPx }}; pointer-events:auto;">
            {{ $slot }}
            {{-- Resize Handle --}}
            <div class="modal-resize-handle" style="position:absolute; right:4px; bottom:4px; width:16px; height:16px; cursor:se-resize; z-index:100; display:flex; align-items:center; justify-content:center; opacity:0.6; hover:opacity:1;">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M9 1L1 9M9 5L5 9M9 8L8 9" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round"/>
                </svg>
            </div>
        </div>
    </div>
</div>

@once
<script>
    function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        if (el.getAttribute('data-has-backdrop') !== 'false') {
            document.body.style.overflow = 'hidden';
        }
        const modalBody = el.querySelector('[data-modal-content]');
        if (modalBody) {
            modalBody.style.transform = '';
            modalBody.style.width = '';
            modalBody.style.height = '';
            modalBody.style.maxWidth = '';
            modalBody.removeAttribute('data-drag-x');
            modalBody.removeAttribute('data-drag-y');
        }
    }
    function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'none';
        if (el.getAttribute('data-has-backdrop') !== 'false') {
            document.body.style.overflow = '';
        }
    }
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('[data-modal]').forEach(function (el) {
                if (el.style.display !== 'none') closeModal(el.id);
            });
        }
    });
</script>
@endonce
