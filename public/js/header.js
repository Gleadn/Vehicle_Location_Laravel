// Script pour le menu burger du header

document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const burgerNav = document.getElementById('burgerNav');
    
    if (burgerMenu && burgerNav) {
        burgerMenu.addEventListener('click', function() {
            burgerNav.classList.toggle('active');
        });
        
        // Fermer le menu si on clique ailleurs
        document.addEventListener('click', function(event) {
            if (!burgerMenu.contains(event.target) && !burgerNav.contains(event.target)) {
                burgerNav.classList.remove('active');
            }
        });
    }
});
