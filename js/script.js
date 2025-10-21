window.addEventListener('scroll', function() {
    const titulo = document.getElementById('titulo');
    const scrollY = window.scrollY;
    const opacity = Math.max(0, 1 - (scrollY / 200)); // Desaparece gradualmente en los primeros 200px de scroll
    titulo.style.opacity = opacity;
    titulo.style.transform = `scale(${Math.max(0.8, 1 - (scrollY / 300))})`; // Se reduce ligeramente
});


