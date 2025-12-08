/*
 * Admin-specific JavaScript entrypoint
 *
 * Loads additional features for admin panel:
 * - Enhanced UI interactions
 * - Confirmation dialogs
 * - Admin-specific components
 */

// Import Bootstrap Alert component for auto-hiding alerts
import { Alert } from 'bootstrap';

// Admin-specific functionality
document.addEventListener('DOMContentLoaded', () => {
    // Add confirmation to delete buttons
    document.querySelectorAll('[data-confirm]').forEach((element) => {
        element.addEventListener('click', (e) => {
            const message = element.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // Auto-hide success alerts after 5 seconds
    document.querySelectorAll('.alert-success').forEach((alert) => {
        setTimeout(() => {
            const bsAlert = new Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

console.log('Admin panel loaded');
