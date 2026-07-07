import '../css/app.css';
import { createApp, type Component } from 'vue';
import ThemeToggle from './components/ThemeToggle.vue';
import VoteButton from './components/VoteButton.vue';
import CommentSection from './components/CommentSection.vue';
import TagsInputIsland from './components/TagsInputIsland.vue';
import AdminStatusSelect from './components/AdminStatusSelect.vue';
import TabNav from './components/TabNav.vue';

// Define a registry of island components
const components: Record<string, Component> = {
    ThemeToggle,
    VoteButton,
    CommentSection,
    TagsInputIsland,
    AdminStatusSelect,
    TabNav,
};

export function mountIslands(root: ParentNode = document): void {
    root.querySelectorAll('[data-vue-component]').forEach(el => {
        const compName = el.getAttribute('data-vue-component');
        if (compName && components[compName]) {
            let props: Record<string, unknown> = {};
            try {
                props = JSON.parse(el.getAttribute('data-props') || '{}');
            } catch (error) {
                console.error(`Invalid data-props JSON for Vue component ${compName}.`, error);
                return;
            }
            createApp(components[compName], props).mount(el);
        } else {
            console.warn(`Vue component ${compName} not found in registry.`);
        }
    });
}

// Scan the DOM for island mount points. (The dark-mode class is applied by an
// inline script in the layout <head> to avoid a flash of the wrong theme.)
document.addEventListener('DOMContentLoaded', () => mountIslands());
