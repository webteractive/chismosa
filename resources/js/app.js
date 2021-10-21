require('./bootstrap');

import Alpine from 'alpinejs'
 
window.Alpine = Alpine

document.addEventListener('alpine:init', () => {
    Alpine.data('clipboard', () => ({
        init() {
            new ClipboardJS(this.$el)
        }
    }))
})
 
Alpine.start()
