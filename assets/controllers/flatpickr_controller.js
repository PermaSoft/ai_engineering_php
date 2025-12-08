import { Controller } from '@hotwired/stimulus';
import flatpickr from 'flatpickr';

/*
 * Stimulus controller for Flatpickr date/time picker
 *
 * Usage:
 * <input type="text"
 *        data-controller="flatpickr"
 *        data-flatpickr-enable-time-value="true"
 *        data-flatpickr-date-format-value="Y-m-d H:i:S"
 *        data-flatpickr-alt-format-value="F j, Y at H:i"
 * />
 */
export default class extends Controller {
    static values = {
        enableTime: { type: Boolean, default: false },
        dateFormat: { type: String, default: 'Y-m-d' },
        altFormat: { type: String, default: 'F j, Y' },
        time24hr: { type: Boolean, default: true },
    }

    connect() {
        this.flatpickr = flatpickr(this.element, {
            enableTime: this.enableTimeValue,
            dateFormat: this.dateFormatValue,
            altInput: true,
            altFormat: this.altFormatValue,
            time_24hr: this.time24hrValue,
            locale: this.getLocaleFromHtmlLang(),
        });
    }

    disconnect() {
        if (this.flatpickr) {
            this.flatpickr.destroy();
        }
    }

    getLocaleFromHtmlLang() {
        const htmlLang = document.documentElement.lang;
        if (htmlLang && htmlLang !== 'en') {
            // Import locale if not English
            import(`flatpickr/dist/l10n/${htmlLang}.js`)
                .then((localeModule) => {
                    if (this.flatpickr && localeModule.default && localeModule.default[htmlLang]) {
                        this.flatpickr.set('locale', localeModule.default[htmlLang]);
                    }
                })
                .catch(() => {
                    console.warn(`Flatpickr locale for "${htmlLang}" not found, using default.`);
                });
        }
        return 'default';
    }
}
