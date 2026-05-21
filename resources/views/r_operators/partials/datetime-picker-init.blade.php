<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    (function () {
        function normalizeValue(value) {
            if (!value) return '';
            return String(value).trim().replace('T', ' ');
        }

        function syncLivewire(input, value) {
            input.value = value;
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function initDmsDatePickers(root) {
            if (typeof flatpickr === 'undefined') return;

            root.querySelectorAll('.dms-datetime-picker').forEach(function (input) {
                if (input._flatpickr) return;

                const initial = normalizeValue(input.value);

                flatpickr(input, {
                    enableTime: true,
                    time_24hr: true,
                    dateFormat: 'Y-m-d H:i',
                    allowInput: true,
                    defaultDate: initial || null,
                    disableMobile: true,
                    onChange: function (_dates, dateStr) {
                        syncLivewire(input, dateStr);
                    },
                    onClose: function (_dates, dateStr) {
                        if (dateStr) syncLivewire(input, dateStr);
                    },
                });
            });

            root.querySelectorAll('.dms-date-picker').forEach(function (input) {
                if (input._flatpickr) return;

                flatpickr(input, {
                    enableTime: false,
                    dateFormat: 'Y-m-d',
                    allowInput: true,
                    defaultDate: input.value || null,
                    disableMobile: true,
                    onChange: function (_dates, dateStr) {
                        syncLivewire(input, dateStr);
                    },
                });
            });
        }

        window.initDmsDatePickers = function (root) {
            initDmsDatePickers(root || document);
        };

        document.addEventListener('DOMContentLoaded', function () {
            initDmsDatePickers(document);
        });

        document.addEventListener('livewire:initialized', function () {
            initDmsDatePickers(document);

            if (typeof Livewire !== 'undefined') {
                Livewire.hook('morph.updated', function ({ el }) {
                    initDmsDatePickers(el);
                });
            }
        });

        document.addEventListener('livewire:navigated', function () {
            initDmsDatePickers(document);
        });
    })();
</script>
