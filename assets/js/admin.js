document.addEventListener('DOMContentLoaded', () => {
    // Función para copiar el link de invitación
    window.copyInvite = (token) => {
        const url = `${window.location.origin}/index.php?t=${token}`;
        navigator.clipboard.writeText(url).then(() => {
            alert('¡Enlace de invitación copiado al portapapeles!');
        });
    };

    // Previsualización de imagen antes de subir
    const skinInput = document.querySelector('input[type="file"]');
    if (skinInput) {
        skinInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                console.log("Archivo seleccionado: " + this.files[0].name);
                // Aquí podrías añadir un preview 2D
            }
        });
    }
});