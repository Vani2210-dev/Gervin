@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
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
    style="display:{{ $show ? 'block' : 'none' }}; position:fixed; inset:0; z-index:9999;"
>
    {{-- Backdrop --}}
    <div
        onclick="closeModal('{{ $name }}')"
        style="position:absolute; inset:0; background:rgba(0,0,0,0.55);">
    </div>

    {{-- Content wrapper --}}
    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
        <div style="position:relative; background:#fff; border-radius:0.75rem; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:{{ $maxWidthPx }}; pointer-events:auto;">
            {{ $slot }}
        </div>
    </div>
</div>

@once
<script>
    function openModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
    function closeModal(id) {
        var el = document.getElementById(id);
        if (!el) return;
        el.style.display = 'none';
        document.body.style.overflow = '';
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
