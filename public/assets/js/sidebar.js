/**
 * Sidebar Navigation Toggle
 * Handles sliding sidebar menu with backdrop overlay
 */

class SidebarManager {
    constructor() {
        this.sidebar = document.querySelector('.sidebar');
        this.tab = document.querySelector('.sidebar-tab');
        this.backdrop = document.querySelector('.sidebar-backdrop');
        this.isOpen = false;
        
        this.init();
    }
    
    init() {
        // Tab click handler
        if (this.tab) {
            this.tab.addEventListener('click', () => this.toggle());
        }
        
        // Backdrop click handler
        if (this.backdrop) {
            this.backdrop.addEventListener('click', () => this.close());
        }
        
        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.close();
            }
        });
        
        // Close sidebar when clicking nav links (better UX)
        const navLinks = document.querySelectorAll('.sidebar__nav-item');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                // Small delay to allow navigation to start
                setTimeout(() => this.close(), 100);
            });
        });
    }
    
    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }
    
    open() {
        if (!this.sidebar || !this.backdrop || !this.tab) return;
        
        this.sidebar.classList.add('sidebar--open');
        this.backdrop.classList.add('sidebar-backdrop--visible');
        this.isOpen = true;
        
        // Change tab icon to close
        const icon = this.tab.querySelector('.sidebar-tab__icon');
        if (icon) {
            icon.textContent = '×';
        }
        
        // Prevent body scroll when sidebar is open
        document.body.style.overflow = 'hidden';
    }
    
    close() {
        if (!this.sidebar || !this.backdrop || !this.tab) return;
        
        this.sidebar.classList.remove('sidebar--open');
        this.backdrop.classList.remove('sidebar-backdrop--visible');
        this.isOpen = false;
        
        // Change tab icon back to menu
        const icon = this.tab.querySelector('.sidebar-tab__icon');
        if (icon) {
            icon.textContent = '☰';
        }
        
        // Restore body scroll
        document.body.style.overflow = '';
    }
}

// Initialize sidebar when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.sidebarManager = new SidebarManager();
});
