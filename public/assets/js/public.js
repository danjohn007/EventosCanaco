// Public event registration functionality

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts after 5 seconds
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach(function(alert) {
            if (alert.querySelector('.btn-close')) {
                alert.querySelector('.btn-close').click();
            } else {
                alert.style.display = 'none';
            }
        });
    }, 5000);
    
    // RFC validation
    function validateRFC(rfc) {
        rfc = rfc.toUpperCase().replace(/\s/g, '');
        const pattern = /^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/;
        return pattern.test(rfc);
    }
    
    // Phone validation
    function validatePhone(phone) {
        const cleaned = phone.replace(/\D/g, '');
        return cleaned.length >= 10 && cleaned.length <= 15;
    }
    
    // Format phone number as user types
    function formatPhone(input) {
        const value = input.value.replace(/\D/g, '');
        let formatted = '';
        
        if (value.length > 0) {
            if (value.length <= 3) {
                formatted = value;
            } else if (value.length <= 6) {
                formatted = value.slice(0, 3) + '-' + value.slice(3);
            } else if (value.length <= 10) {
                formatted = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
            } else {
                formatted = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
            }
        }
        
        input.value = formatted;
    }
    
    // Format RFC as user types
    function formatRFC(input) {
        let value = input.value.toUpperCase().replace(/[^A-ZÑ&0-9]/g, '');
        input.value = value;
    }
    
    // Add event listeners for formatting
    document.addEventListener('input', function(e) {
        if (e.target.type === 'tel' || e.target.name === 'telefono' || e.target.name === 'whatsapp' || e.target.name === 'telefono_oficina') {
            formatPhone(e.target);
        } else if (e.target.name === 'rfc') {
            formatRFC(e.target);
        }
    });
    
    // Form validation
    function validateForm(form) {
        const errors = [];
        const formData = new FormData(form);
        const tipo = formData.get('tipo');
        
        // Common validations
        if (!formData.get('nombre')) {
            errors.push('El nombre es requerido');
        }
        
        const email = formData.get('email');
        if (!email || !isValidEmail(email)) {
            errors.push('Un email válido es requerido');
        }
        
        if (tipo === 'empresa') {
            const rfc = formData.get('rfc');
            if (!rfc || !validateRFC(rfc)) {
                errors.push('Un RFC válido es requerido');
            }
            
            if (!formData.get('razon_social')) {
                errors.push('La razón social es requerida');
            }
        } else {
            const telefono = formData.get('telefono');
            if (!telefono || !validatePhone(telefono)) {
                errors.push('Un teléfono válido es requerido');
            }
        }
        
        return errors;
    }
    
    function isValidEmail(email) {
        const pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return pattern.test(email);
    }
    
    // Show loading state
    function setLoading(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.classList.add('loading');
            button.querySelector('span').textContent = 'Procesando...';
        } else {
            button.disabled = false;
            button.classList.remove('loading');
            button.querySelector('span').textContent = 'Obtener boleto';
        }
    }
    
    // Global form submission handler
    window.submitRegistration = function(form) {
        const submitButton = form.querySelector('button[type="submit"]');
        const errors = validateForm(form);
        
        if (errors.length > 0) {
            alert('Por favor corrija los siguientes errores:\n' + errors.join('\n'));
            return false;
        }
        
        setLoading(submitButton, true);
        
        const formData = new FormData(form);
        
        fetch('/public/registro', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            setLoading(submitButton, false);
            
            if (data.status === 'success') {
                showSuccess(data.codigo);
            } else if (data.errors) {
                let errorMsg = 'Por favor corrija los siguientes errores:\n';
                Object.values(data.errors).forEach(error => {
                    errorMsg += '- ' + error + '\n';
                });
                alert(errorMsg);
            } else {
                alert(data.error || 'Error en el registro');
            }
        })
        .catch(error => {
            setLoading(submitButton, false);
            console.error('Error:', error);
            alert('Error en el registro. Por favor intente nuevamente.');
        });
        
        return false;
    };
    
    function showSuccess(codigo) {
        const successDiv = document.getElementById('registrationSuccess');
        const codigoSpan = document.getElementById('codigoGenerado');
        const registrationForm = document.getElementById('registrationForm');
        
        if (successDiv && codigoSpan && registrationForm) {
            codigoSpan.textContent = codigo;
            registrationForm.style.display = 'none';
            successDiv.style.display = 'block';
            
            // Scroll to success message
            successDiv.scrollIntoView({ behavior: 'smooth' });
        }
    }
    
    // Print functionality
    window.printTicket = function() {
        window.print();
    };
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });
});

// Utility functions for other pages
function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text).then(function() {
            showToast('Copiado al portapapeles');
        });
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showToast('Copiado al portapapeles');
    }
}

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    toast.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(function() {
        if (toast.parentNode) {
            toast.remove();
        }
    }, 3000);
}