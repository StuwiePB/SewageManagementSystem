{{-- Shared spacing (rem-based) for report flow pages — consistent gaps, less clipping on small viewports. --}}
<style>
    :root {
        --rflow-gap-section: 1.25rem;
        --rflow-gap-textpair: 0.625rem;
        --rflow-gap-card-text: 0.5rem;
        --rflow-gap-grid: 0.875rem;
        --rflow-gap-inline: 0.5rem;
        --rflow-gap-stack-tight: 0.5rem;
    }
    .rflow-main-stack {
        position: fixed;
        left: 0;
        right: 0;
        top: calc(11vh + 28px);
        z-index: 5;
        display: flex;
        flex-direction: column;
        gap: var(--rflow-gap-section);
    }
    .rflow-main-stack--inert,
    .rflow-main-stack--inert * {
        pointer-events: none;
    }
    .rflow-header-col {
        display: flex;
        flex-direction: column;
        gap: var(--rflow-gap-textpair);
        max-width: calc(100% - 120px);
        margin-left: calc(20px + (40px - 21px) / 2 + 30px + 24px);
    }
    .rflow-header-line-primary {
        color: #fff;
        font-size: 13px;
        font-family: Poppins, sans-serif;
        font-weight: 600;
        line-height: 1.45;
    }
    .rflow-header-line-secondary {
        color: rgba(255, 255, 255, 0.55);
        font-size: 11px;
        font-family: Poppins, sans-serif;
        font-weight: 200;
        line-height: 1.45;
    }
    .rflow-choices-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: var(--rflow-gap-grid);
        padding: 4px 2px 12px;
    }
    .rflow-choice-text {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        justify-content: flex-start;
        gap: var(--rflow-gap-card-text);
        padding: 10px 8px 10px 10px;
        width: 100%;
        box-sizing: border-box;
        text-align: left;
        min-height: 0;
    }
    .rflow-choice-text .choice-tagline { line-height: 1.35; }
    .rflow-choice-text .choice-subtitle { line-height: 1.4; }
    .rflow-preview-header {
        pointer-events: none;
        gap: var(--rflow-gap-section);
    }
    .rflow-info-stack {
        display: flex;
        flex-direction: column;
        gap: var(--rflow-gap-stack-tight);
    }
    .rflow-name-phone-row {
        display: flex;
        gap: var(--rflow-gap-inline);
        align-items: stretch;
    }
    .rflow-details-block {
        gap: 0.75rem;
    }
    .rflow-location-prompt {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 1.5rem 1.25rem;
        text-align: center;
        gap: var(--rflow-gap-textpair);
    }
</style>
