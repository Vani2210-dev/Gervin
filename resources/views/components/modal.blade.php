@props([
    'name' => null,
    'id' => null,
    'title' => null,
    'size' => null,
    'show' => false,
    'maxWidth' => null,
    'hasBackdrop' => true,
    'transparent' => false
])

@php
$modalId = $name ?? $id ?? $attributes->get('id') ?? 'modal_' . substr(md5(uniqid(rand(), true)), 0, 8);

if (!$maxWidth && $size) {
    $maxWidth = match($size) {
        'modal-sm' => 'sm',
        'modal-md' => '2xl',
        'modal-lg' => '4xl',
        'modal-xl' => '6xl',
        default    => '2xl',
    };
}
$maxWidth = $maxWidth ?? '2xl';

$maxWidthPx = [
    'sm'  => '384px',
    'md'  => '448px',
    'lg'  => '512px',
    'xl'  => '576px',
    '2xl' => '672px',
    '3xl' => '768px',
    '4xl' => '896px',
    '5xl' => '1024px',
    '6xl' => '1152px',
    '7xl' => '1280px',
    'full'=> '95vw',
][$maxWidth] ?? ($maxWidth && (str_ends_with($maxWidth, 'px') || str_ends_with($maxWidth, '%') || str_ends_with($maxWidth, 'vw')) ? $maxWidth : '672px');

// Khi transparent = true: không nền, không shadow, co vừa kích thước ảnh, không giới hạn max-width (dùng cho image viewer)
$contentStyle = $transparent
    ? "position:relative; width:fit-content; pointer-events:auto;"
    : "position:relative; background:#fff; border-radius:0.75rem; box-shadow:0 20px 60px rgba(0,0,0,0.3); width:100%; max-width:{$maxWidthPx}; pointer-events:auto;";
@endphp

<div
    id="{{ $modalId }}"
    data-modal
    data-has-backdrop="{{ $hasBackdrop ? 'true' : 'false' }}"
    style="display:{{ $show ? 'block' : 'none' }}; position:fixed; inset:0; z-index:9999999 !important;{{ !$hasBackdrop ? ' pointer-events:none;' : '' }}"
>
    {{-- Backdrop --}}
    @if($hasBackdrop)
    <div
        onclick="closeModal('{{ $modalId }}')"
        style="position:absolute; inset:0; background:rgba(0,0,0,0.55);">
    </div>
    @endif

    {{-- Content wrapper --}}
    <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center; padding:1rem; pointer-events:none;">
        <div data-modal-content style="{{ $contentStyle }}">
            @if($title)
            <div class="px-6 py-4 border-b border-neutral-200 flex items-center justify-between bg-neutral-50 rounded-t-xl">
                <h5 class="font-bold text-base text-neutral-800 mb-0">{{ $title }}</h5>
                <button type="button" onclick="closeModal('{{ $modalId }}')" class="text-neutral-400 hover:text-neutral-700 text-xl leading-none transition-colors">&times;</button>
            </div>
            @endif
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
        if (el.getAttribute('data-has-backdrop') !== 'false') {
            document.body.style.overflow = 'hidden';
        }
        const modalBody = el.querySelector('[data-modal-content]');
        if (modalBody) {
            modalBody.style.transform = '';
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
