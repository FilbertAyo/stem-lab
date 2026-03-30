<style>
    .competition-form-hero {
        background: linear-gradient(120deg, #0f172a 0%, #1e3a5f 45%, #0c4a6e 100%);
        color: #e2e8f0;
        border-radius: 0.75rem;
        padding: 1.5rem 1.75rem;
        position: relative;
        overflow: hidden;
    }
    .competition-form-hero::after {
        content: '';
        position: absolute;
        right: -20%;
        top: -40%;
        width: 55%;
        height: 180%;
        background: radial-gradient(circle, rgba(56, 189, 248, 0.18) 0%, transparent 70%);
        pointer-events: none;
    }
    .competition-form-hero h5 {
        color: #f8fafc;
    }
    .competition-phase-journey-head {
        background: linear-gradient(135deg, rgba(var(--bs-primary-rgb), 0.08) 0%, rgba(13, 110, 253, 0.06) 100%);
        border-radius: 0.5rem;
        padding: 1rem 1.25rem;
        border: 1px dashed rgba(var(--bs-primary-rgb), 0.25);
    }
    .competition-phase-rail {
        position: relative;
        padding-left: 0.5rem;
    }
    .competition-phase-rail::before {
        content: '';
        position: absolute;
        left: 0.65rem;
        top: 0.5rem;
        bottom: 0.5rem;
        width: 2px;
        background: linear-gradient(180deg, var(--bs-primary) 0%, rgba(var(--bs-primary-rgb), 0.15) 100%);
        border-radius: 2px;
    }
    .competition-phase-card.phase-row {
        position: relative;
        margin-left: 1.75rem;
        border-radius: 0.5rem;
        border: 1px solid var(--bs-border-color);
        background: var(--bs-body-bg);
        box-shadow: 0 0.125rem 0.5rem rgba(0, 0, 0, 0.04);
    }
    .competition-phase-card.phase-row::before {
        content: attr(data-step);
        position: absolute;
        left: -2.35rem;
        top: 1rem;
        width: 1.75rem;
        height: 1.75rem;
        border-radius: 50%;
        background: var(--bs-primary);
        color: #fff;
        font-size: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
        z-index: 1;
        box-shadow: 0 0 0 3px var(--bs-body-bg);
    }
    .competition-step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 1.65rem;
        height: 1.65rem;
        border-radius: 0.35rem;
        background: rgba(var(--bs-primary-rgb), 0.12);
        color: var(--bs-primary);
        font-size: 0.8rem;
        font-weight: 700;
        margin-right: 0.5rem;
    }
</style>
