// Sidebar Toggle Functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const mainSidebar = document.querySelector('.main-sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (sidebarToggle && mainSidebar && contentWrapper) {
        sidebarToggle.addEventListener('click', function() {
            mainSidebar.classList.toggle('collapsed');
            contentWrapper.classList.toggle('expanded');
            
            // Save sidebar state to localStorage
            const isCollapsed = mainSidebar.classList.contains('collapsed');
            localStorage.setItem('sidebarCollapsed', isCollapsed);
        });
        
        // Restore sidebar state from localStorage
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
        if (sidebarCollapsed === 'true') {
            mainSidebar.classList.add('collapsed');
            contentWrapper.classList.add('expanded');
        }
    }
});

// Close sidebar on mobile when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.querySelector('.main-sidebar');
    const toggle = document.querySelector('.sidebar-toggle');
    
    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
            if (!sidebar.classList.contains('collapsed')) {
                sidebar.classList.add('collapsed');
                document.querySelector('.content-wrapper').classList.add('expanded');
            }
        }
    }
});

// Handle window resize
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.main-sidebar');
    const contentWrapper = document.querySelector('.content-wrapper');
    
    if (window.innerWidth > 768) {
        // On desktop, restore saved state
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed');
        if (sidebarCollapsed === 'true') {
            sidebar.classList.add('collapsed');
            contentWrapper.classList.add('expanded');
        } else {
            sidebar.classList.remove('collapsed');
            contentWrapper.classList.remove('expanded');
        }
    } else {
        // On mobile, always collapse sidebar
        sidebar.classList.add('collapsed');
        contentWrapper.classList.add('expanded');
    }
});
