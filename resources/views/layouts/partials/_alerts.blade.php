{{-- ── Flash Alerts ──────────────────────────────────────────────────── --}}
@if(session('success'))
<div role="alert" aria-live="polite" class="alert-success flex items-center gap-2">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('success_html'))
<div role="alert" aria-live="polite" class="alert-success flex items-center gap-2">
    <i class="fa-solid fa-circle-check" aria-hidden="true"></i>
    <span>{!! session('success_html') !!}</span>
</div>
@endif
@if(session('error'))
<div role="alert" aria-live="assertive" class="alert-error flex items-center gap-2">
    <i class="fa-solid fa-circle-xmark" aria-hidden="true"></i>
    <span>{{ session('error') }}</span>
</div>
@endif
