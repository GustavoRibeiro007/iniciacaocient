// Este script faz os submenus da sidebar funcionarem como dropdowns ao clicar

document.addEventListener('DOMContentLoaded', function() {
  // Dropdown logic for sidebar
  document.querySelectorAll('.has-submenu > .menu-label').forEach(label => {
    label.addEventListener('click', function(e) {
      e.stopPropagation();
      const parent = this.parentElement;
      // Toggle only the clicked submenu
      if (parent.classList.contains('open')) {
        parent.classList.remove('open');
      } else {
        // Fecha todos os outros
        document.querySelectorAll('.has-submenu').forEach(li => li.classList.remove('open'));
        parent.classList.add('open');
      }
    });
  });

  // Fecha submenus ao clicar fora do aside
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.sidebar')) {
      document.querySelectorAll('.has-submenu').forEach(li => li.classList.remove('open'));
    }
  });
});
