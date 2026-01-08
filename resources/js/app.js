import './bootstrap';
import Alpine from 'alpinejs';
import ClipboardJS from 'clipboard';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('clipboard', () => ({
        init() {
            new ClipboardJS(this.$el);
        }
    }));
});

Alpine.start();
