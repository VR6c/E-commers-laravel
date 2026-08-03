import './bootstrap';
import $ from 'jquery';
import 'datatables.net';
import { initLanguageSwitcher } from './admin/sidebar';
import { initCommandMenu } from './modules/command-menu';

// Make jQuery globally accessible for legacy plugins if required
window.$ = window.jQuery = $;

// Initialize modern JavaScript modules
document.addEventListener('DOMContentLoaded', () => {
    initLanguageSwitcher();
    initCommandMenu();
});

