<script src="{{ asset('backend/assets/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/Chart.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/dynamic-pie-chart.js') }}"></script>
<script src="{{ asset('backend/assets/js/moment.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/fullcalendar.js') }}"></script>
<script src="{{ asset('backend/assets/js/jvectormap.min.js') }}"></script>
<script src="{{ asset('backend/assets/js/world-merc.js') }}"></script>
<script src="{{ asset('backend/assets/js/polyfill.js') }}"></script>
<script src="{{ asset('backend/assets/js/main.js') }}"></script>
<!--alert session-->
<script src="{{ asset('backend/assets/js/alert-session.js') }}"></script>

<!--data table responsive-->
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const themeToggle = document.getElementById('theme-toggle');
    const themeIcon = document.getElementById('theme-icon');
    const body = document.body;
    
    // Check localStorage for theme preference
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    // Apply saved theme with transition
    function applyTheme(theme) {
        if (theme === 'dark') {
            body.classList.add('dark-mode');
            themeIcon.classList.replace('lni-night', 'lni-sun');
            themeIcon.style.color = '#ffc107';
        } else {
            body.classList.remove('dark-mode');
            themeIcon.classList.replace('lni-sun', 'lni-night');
            themeIcon.style.color = '';
        }
    }
    
    // Initial apply
    applyTheme(savedTheme);
    
    // Toggle theme on button click with smooth transition
    themeToggle.addEventListener('click', function() {
        // Disable transitions during the toggle
        body.style.transition = 'none';
        
        if (body.classList.contains('dark-mode')) {
            localStorage.setItem('theme', 'light');
            themeIcon.classList.replace('lni-sun', 'lni-night');
            themeIcon.style.color = '';
            body.classList.remove('dark-mode');
        } else {
            localStorage.setItem('theme', 'dark');
            themeIcon.classList.replace('lni-night', 'lni-sun');
            themeIcon.style.color = '#ffc107';
            body.classList.add('dark-mode');
        }
        
        // Force reflow to ensure transition works
        void body.offsetWidth;
        
        // Re-enable transitions
        body.style.transition = 'background-color 0.3s ease, color 0.3s ease';
    });
    
    // Detect system preference
    const prefersDarkScheme = window.matchMedia('(prefers-color-scheme: dark)');
    
    // Listen for system theme changes
    prefersDarkScheme.addListener((e) => {
        if (e.matches && !localStorage.getItem('theme')) {
            applyTheme('dark');
        } else if (!e.matches && !localStorage.getItem('theme')) {
            applyTheme('light');
        }
    });
    
    // Add smooth transition to all elements
    const style = document.createElement('style');
    style.textContent = `
        body, body * {
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
        }
    `;
    document.head.appendChild(style);
});
</script>
