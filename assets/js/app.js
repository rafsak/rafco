(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach((alertElement) => {
        setTimeout(() => {
            alertElement.classList.add('d-none');
        }, 6000);
    });
})();
