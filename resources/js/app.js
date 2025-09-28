import './bootstrap';
import './main';

import.meta.glob([
    '../images/**',
    '../fonts/**'
])

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
