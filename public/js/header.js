document.addEventListener('DOMContentLoaded', function() {
    const burgerMenu = document.getElementById('burgerMenu');
    const burgerNav = document.getElementById('burgerNav');
    
    if (burgerMenu && burgerNav) {
        burgerMenu.addEventListener('click', function() {
            burgerNav.classList.toggle('active');
        });
        
        document.addEventListener('click', function(event) {
            if (!burgerMenu.contains(event.target) && !burgerNav.contains(event.target)) {
                burgerNav.classList.remove('active');
            }
        });
    }
});
