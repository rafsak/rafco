<?php
/**
 * Radio Mehna V2 - Admin Footer
 */
?>
        </div><!-- /.admin-page -->
    </div><!-- /.admin-content -->
</div><!-- /.admin-wrapper -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar toggle
document.getElementById('sidebarToggle')?.addEventListener('click', function() {
    document.querySelector('.admin-wrapper')?.classList.toggle('sidebar-collapsed');
});

// Theme toggle
document.getElementById('themeToggle')?.addEventListener('click', function() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme') || 'dark';
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
    this.innerHTML = next === 'dark' ? '<i class="bi bi-sun-fill"></i>' : '<i class="bi bi-moon-fill"></i>';
});

// Active sidebar link
document.querySelectorAll('.sidebar-link').forEach(link => {
    if (link.href === window.location.href) {
        link.classList.add('active');
    }
});

// Delete confirmation
document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function(e) {
        if (!confirm(this.dataset.confirm || 'Confirmer la suppression ?')) {
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
