import '../css/app.css';
import { createApp, type Component } from 'vue';
import ThemeToggle from './components/ThemeToggle.vue';

// Define a registry of island components
const components: Record<string, Component> = {
    ThemeToggle,
};

// Scan the DOM for island mount points
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-vue-component]').forEach(el => {
        const compName = el.getAttribute('data-vue-component');
        if (compName && components[compName]) {
            const props = JSON.parse(el.getAttribute('data-props') || '{}');
            createApp(components[compName], props).mount(el);
        } else {
            console.warn(`Vue component ${compName} not found in registry.`);
        }
    });

    // Handle Dark Mode toggle early (if not handled by inline script in head)
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
});
