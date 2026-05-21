<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .flatpickr-calendar {
        background: #1e2433;
        border: 1px solid rgba(74, 144, 226, 0.35);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.45);
        border-radius: 10px;
    }
    .flatpickr-months .flatpickr-month,
    .flatpickr-current-month .flatpickr-monthDropdown-months,
    .flatpickr-weekdays,
    span.flatpickr-weekday {
        background: #1e2433;
        color: #fff;
    }
    .flatpickr-current-month .flatpickr-monthDropdown-months { color: #fff; }
    .flatpickr-months .flatpickr-prev-month,
    .flatpickr-months .flatpickr-next-month { fill: #4a90e2; color: #4a90e2; }
    .flatpickr-months .flatpickr-prev-month:hover svg,
    .flatpickr-months .flatpickr-next-month:hover svg { fill: #6eb3ff; }
    .flatpickr-day {
        color: #e5e7eb;
        border-radius: 6px;
    }
    .flatpickr-day:hover {
        background: rgba(74, 144, 226, 0.25);
        border-color: transparent;
    }
    .flatpickr-day.today {
        border-color: #4a90e2;
    }
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #4a90e2;
        border-color: #4a90e2;
        color: #fff;
    }
    .flatpickr-time {
        border-top: 1px solid rgba(74, 144, 226, 0.2);
        background: #1a1f2e;
    }
    .flatpickr-time input,
    .flatpickr-time .flatpickr-am-pm {
        color: #fff;
        background: #1a1f2e;
    }
    .flatpickr-time input:hover,
    .flatpickr-time .flatpickr-am-pm:hover {
        background: rgba(74, 144, 226, 0.15);
    }
    .numInputWrapper span.arrowUp:after { border-bottom-color: #4a90e2; }
    .numInputWrapper span.arrowDown:after { border-top-color: #4a90e2; }
    .dms-datetime-picker[readonly],
    .dms-date-picker[readonly] {
        cursor: pointer;
        background: var(--bg-primary, #1a1f2e);
    }
</style>
