@php
    $deletionReasons = \App\Models\Report::deletionReasonOptions();
@endphp

@once
@push('styles')
<style>
    .delete-report-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(10, 12, 20, 0.78);
        z-index: 2100;
        align-items: center;
        justify-content: center;
        padding: 1.25rem;
        overscroll-behavior: none;
    }
    .delete-report-overlay.is-open { display: flex; }
    .delete-report-dialog {
        background: var(--bg-secondary);
        border: 1px solid rgba(106, 150, 255, 0.28);
        border-radius: 12px;
        padding: 1.35rem 1.5rem 1.5rem;
        max-width: 440px;
        width: 100%;
        font-family: 'Poppins', 'Inter', sans-serif;
        overscroll-behavior: contain;
    }
    .delete-report-dialog h2 {
        margin: 0 0 0.35rem;
        font-size: 1.15rem;
        font-weight: 700;
        color: var(--text-primary);
    }
    .delete-report-lead {
        margin: 0 0 1rem;
        font-size: 0.8125rem;
        line-height: 1.45;
        color: var(--text-secondary);
    }
    .delete-report-reasons {
        display: flex;
        flex-direction: column;
        gap: 0.45rem;
        margin-bottom: 1.1rem;
    }
    .delete-report-reasons label {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        padding: 0.5rem 0.65rem;
        border-radius: 8px;
        border: 1px solid rgba(106, 150, 255, 0.2);
        cursor: pointer;
        font-size: 0.8125rem;
        color: var(--text-primary);
        line-height: 1.35;
    }
    .delete-report-reasons label:has(input:checked) {
        border-color: rgba(255, 91, 91, 0.45);
        background: rgba(255, 91, 91, 0.06);
    }
    .delete-report-reasons input { margin-top: 0.2rem; flex-shrink: 0; }
    .delete-report-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        justify-content: flex-end;
    }
</style>
@endpush
@endonce

<div id="delete-report-overlay" class="delete-report-overlay" aria-hidden="true">
    <div class="delete-report-dialog" role="dialog" aria-modal="true" aria-labelledby="delete-report-title">
        <h2 id="delete-report-title">Remove report</h2>
        <p class="delete-report-lead">Select a reason. This is saved with the record for your team’s audit trail.</p>
        <form id="delete-report-form" method="post" action="#">
            @csrf
            @method('DELETE')
            <p style="margin: 0 0 0.5rem; font-size: 0.8125rem; font-weight: 600; color: var(--text-primary);">Reason for removal</p>
            <div class="delete-report-reasons" role="radiogroup" aria-label="Reason for removal">
                @foreach ($deletionReasons as $value => $label)
                    <label>
                        <input type="radio" name="deletion_reason" value="{{ $value }}" required />
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
            <div class="delete-report-actions">
                <button type="button" class="btn btn-secondary" id="delete-report-cancel">Cancel</button>
                <button type="submit" class="btn btn-danger">Remove report</button>
            </div>
        </form>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    var overlay = document.getElementById('delete-report-overlay');
    var form = document.getElementById('delete-report-form');
    var cancel = document.getElementById('delete-report-cancel');
    if (!overlay || !form) return;

    var scrollLockPrev = '';

    function lockScroll() {
        if (scrollLockPrev !== '') return;
        scrollLockPrev = document.body.style.overflow || '';
        document.documentElement.style.overflow = 'hidden';
        document.body.style.overflow = 'hidden';
    }

    function unlockScroll() {
        document.documentElement.style.overflow = '';
        document.body.style.overflow = scrollLockPrev;
        scrollLockPrev = '';
    }

    function closeModal() {
        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');
        form.setAttribute('action', '#');
        form.reset();
        unlockScroll();
    }

    function openModal(actionUrl) {
        form.setAttribute('action', actionUrl);
        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');
        lockScroll();
    }

    document.querySelectorAll('.js-open-delete-report').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var url = btn.getAttribute('data-delete-url');
            if (!url) return;
            document.querySelectorAll('.cust-rep-menu.is-open').forEach(function (m) { m.classList.remove('is-open'); });
            document.querySelectorAll('.cust-rep-menu-btn[aria-expanded="true"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
            openModal(url);
        });
    });

    if (cancel) cancel.addEventListener('click', closeModal);

    overlay.addEventListener('click', function (e) {
        if (e.target === overlay) closeModal();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) closeModal();
    });
})();
</script>
@endpush
@endonce
