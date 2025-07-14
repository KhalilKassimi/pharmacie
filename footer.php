</div>
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Fonction pour confirmer la suppression
    function confirmDelete() {
        return confirm('Êtes-vous sûr de vouloir supprimer cet élément ?');
    }

    // Fonction pour valider les formulaires
    function validateForm() {
        var requiredFields = document.querySelectorAll('[required]');
        var valid = true;
        
        requiredFields.forEach(function(field) {
            if (!field.value.trim()) {
                field.classList.add('error');
                valid = false;
            } else {
                field.classList.remove('error');
            }
        });
        
        return valid;
    }
    </script>
</body>
</html>