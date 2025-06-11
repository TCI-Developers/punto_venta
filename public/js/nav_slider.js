const toggleBtn = document.getElementById('toggleSidebar');
const sidebar = document.getElementById('sidebar');

toggleBtn.addEventListener('click', () => {
  sidebar.classList.toggle('collapsed');
  document.body.classList.toggle('sidebar-collapsed');

  // En pantallas pequeñas, usar clase 'active'
  if (window.innerWidth <= 768) {
    sidebar.classList.toggle('active');
  }
});
