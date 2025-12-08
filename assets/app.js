/*
 * Main application entrypoint
 */

// Start Stimulus application
import './bootstrap.js';

// Import Bootstrap
import 'bootstrap';

// Import styles
import './styles/app.scss';

// Import Turbo for fast page loads
import '@hotwired/turbo';

// Initialize syntax highlighting for code blocks
import hljs from 'highlight.js';
import 'highlight.js/styles/github.css';

document.addEventListener('DOMContentLoaded', () => {
    // Highlight all code blocks
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);
    });
});

// Re-highlight code blocks after Turbo navigation
document.addEventListener('turbo:load', () => {
    document.querySelectorAll('pre code').forEach((block) => {
        hljs.highlightElement(block);
    });
});

// Console welcome message
console.log('Symfony Demo Application loaded');
