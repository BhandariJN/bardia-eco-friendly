    </div><!-- /.cms-content -->
</main><!-- /.cms-main -->

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('active');
}

// Confirm helper used by delete buttons
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item? This action cannot be undone.');
}

// Auto-dismiss alerts after 4 seconds
document.querySelectorAll('.alert').forEach(function(el) {
    setTimeout(function() { el.style.opacity = '0'; el.style.transition = 'opacity .4s'; setTimeout(function(){ el.remove(); }, 400); }, 4000);
});
</script>
</body>
</html>
