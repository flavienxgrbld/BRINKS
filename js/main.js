/**
 * BRINKS - Système de Gestion de Convois
 * Fichier JavaScript Principal
 */

// ========== NOTIFICATIONS ==========
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            ${getNotificationIcon(type)}
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

function getNotificationIcon(type) {
    const icons = {
        success: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#48bb78" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>',
        error: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f56565" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>',
        info: '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#4299e1" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'
    };
    return icons[type] || icons.info;
}

// Animation de sortie pour les notifications
const style = document.createElement('style');
style.textContent = `
    @keyframes slideOut {
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ========== UTILITAIRES ==========

/**
 * Formater une date et heure
 */
function formatDateTime(datetime) {
    if (!datetime) return '';
    const date = new Date(datetime);
    return date.toLocaleString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

/**
 * Formater une date seulement
 */
function formatDate(date) {
    if (!date) return '';
    const d = new Date(date);
    return d.toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}

/**
 * Calculer la durée entre deux dates
 */
function calculateDuration(startDate, endDate) {
    if (!startDate || !endDate) return null;
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    const diff = end - start;
    
    const days = Math.floor(diff / (1000 * 60 * 60 * 24));
    const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
    
    return { days, hours, minutes };
}

/**
 * Formater la durée
 */
function formatDuration(duration) {
    if (!duration) return 'N/A';
    
    const parts = [];
    if (duration.days > 0) parts.push(`${duration.days}j`);
    if (duration.hours > 0) parts.push(`${duration.hours}h`);
    if (duration.minutes > 0) parts.push(`${duration.minutes}min`);
    
    return parts.length > 0 ? parts.join(' ') : '0min';
}

/**
 * Debounce function
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Valider un email
 */
function isValidEmail(email) {
    const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return regex.test(email);
}

/**
 * Échapper les caractères HTML
 */
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

/**
 * Obtenir la classe de badge selon le statut
 */
function getStatusBadgeClass(status) {
    const classes = {
        'EN_COURS': 'warning',
        'TERMINE': 'success',
        'ANNULE': 'danger'
    };
    return classes[status] || 'secondary';
}

/**
 * Obtenir la classe de badge selon le rôle
 */
function getRoleBadgeClass(role) {
    const classes = {
        'ADMIN': 'danger',
        'USER': 'info',
        'CHEF': 'danger',
        'CONVOYEUR': 'info',
        'CONTROLEUR': 'secondary'
    };
    return classes[role] || 'secondary';
}

// ========== REQUÊTES API ==========

/**
 * Wrapper pour les requêtes fetch avec gestion d'erreurs
 */
async function apiRequest(url, options = {}) {
    try {
        const response = await fetch(url, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('API Request Error:', error);
        showNotification('Erreur de communication avec le serveur', 'error');
        throw error;
    }
}

/**
 * GET request
 */
async function apiGet(url) {
    return apiRequest(url, { method: 'GET' });
}

/**
 * POST request
 */
async function apiPost(url, data) {
    return apiRequest(url, {
        method: 'POST',
        body: JSON.stringify(data)
    });
}

/**
 * PUT request
 */
async function apiPut(url, data) {
    return apiRequest(url, {
        method: 'PUT',
        body: JSON.stringify(data)
    });
}

/**
 * DELETE request
 */
async function apiDelete(url) {
    return apiRequest(url, { method: 'DELETE' });
}

// ========== MODALS ==========

/**
 * Ouvrir un modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Fermer un modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

/**
 * Fermer le modal en cliquant en dehors
 */
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal')) {
        event.target.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// ========== TABLEAUX ==========

/**
 * Trier un tableau
 */
function sortTable(table, column, direction = 'asc') {
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    rows.sort((a, b) => {
        const aValue = a.children[column].textContent.trim();
        const bValue = b.children[column].textContent.trim();
        
        if (!isNaN(aValue) && !isNaN(bValue)) {
            return direction === 'asc' ? aValue - bValue : bValue - aValue;
        }
        
        return direction === 'asc' 
            ? aValue.localeCompare(bValue, 'fr')
            : bValue.localeCompare(aValue, 'fr');
    });
    
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Filtrer un tableau
 */
function filterTable(table, searchTerm) {
    const tbody = table.querySelector('tbody');
    const rows = tbody.querySelectorAll('tr');
    
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm.toLowerCase()) ? '' : 'none';
    });
}

// ========== FORMULAIRES ==========

/**
 * Obtenir les données d'un formulaire
 */
function getFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return null;
    
    const formData = new FormData(form);
    const data = {};
    
    for (let [key, value] of formData.entries()) {
        data[key] = value;
    }
    
    return data;
}

/**
 * Réinitialiser un formulaire
 */
function resetForm(formId) {
    const form = document.getElementById(formId);
    if (form) {
        form.reset();
    }
}

/**
 * Valider un formulaire
 */
function validateForm(formId, rules) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    let isValid = true;
    const errors = [];
    
    for (let [field, rule] of Object.entries(rules)) {
        const input = form.querySelector(`[name="${field}"]`);
        if (!input) continue;
        
        const value = input.value.trim();
        
        if (rule.required && !value) {
            errors.push(`${rule.label || field} est requis`);
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
        
        if (rule.email && value && !isValidEmail(value)) {
            errors.push(`${rule.label || field} doit être un email valide`);
            isValid = false;
            input.classList.add('error');
        }
        
        if (rule.minLength && value.length < rule.minLength) {
            errors.push(`${rule.label || field} doit contenir au moins ${rule.minLength} caractères`);
            isValid = false;
            input.classList.add('error');
        }
    }
    
    if (!isValid) {
        showNotification(errors[0], 'error');
    }
    
    return isValid;
}

// ========== EXPORT ==========

/**
 * Exporter des données en CSV
 */
function exportToCSV(data, filename = 'export.csv') {
    if (!data || data.length === 0) {
        showNotification('Aucune donnée à exporter', 'error');
        return;
    }
    
    const headers = Object.keys(data[0]);
    const csvContent = [
        headers.join(','),
        ...data.map(row => 
            headers.map(header => {
                const value = row[header] || '';
                return `"${String(value).replace(/"/g, '""')}"`;
            }).join(',')
        )
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    link.click();
}

// ========== IMPRESSION ==========

/**
 * Imprimer une section spécifique
 */
function printSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (!section) return;
    
    const printWindow = window.open('', '', 'height=600,width=800');
    printWindow.document.write('<html><head><title>Impression</title>');
    printWindow.document.write('<link rel="stylesheet" href="/css/style.css">');
    printWindow.document.write('</head><body>');
    printWindow.document.write(section.innerHTML);
    printWindow.document.write('</body></html>');
    printWindow.document.close();
    
    setTimeout(() => {
        printWindow.print();
    }, 250);
}

// ========== STOCKAGE LOCAL ==========

/**
 * Sauvegarder dans le localStorage
 */
function saveToLocalStorage(key, value) {
    try {
        localStorage.setItem(key, JSON.stringify(value));
        return true;
    } catch (error) {
        console.error('Error saving to localStorage:', error);
        return false;
    }
}

/**
 * Récupérer depuis le localStorage
 */
function getFromLocalStorage(key, defaultValue = null) {
    try {
        const item = localStorage.getItem(key);
        return item ? JSON.parse(item) : defaultValue;
    } catch (error) {
        console.error('Error getting from localStorage:', error);
        return defaultValue;
    }
}

/**
 * Supprimer du localStorage
 */
function removeFromLocalStorage(key) {
    try {
        localStorage.removeItem(key);
        return true;
    } catch (error) {
        console.error('Error removing from localStorage:', error);
        return false;
    }
}

// ========== CONFIRMATION ==========

/**
 * Dialogue de confirmation personnalisé
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// ========== CHARGEMENT ==========

/**
 * Afficher un état de chargement
 */
function showLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.innerHTML = `
            <div class="loading-state">
                <div class="spinner"></div>
                <p>Chargement...</p>
            </div>
        `;
    }
}

/**
 * Masquer l'état de chargement
 */
function hideLoading(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        const loadingState = element.querySelector('.loading-state');
        if (loadingState) {
            loadingState.remove();
        }
    }
}

// ========== INITIALISATION ==========

/**
 * Initialisation au chargement du DOM
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('BRINKS System Loaded');
    
    // Ajouter les écouteurs d'événements globaux
    
    // ESC pour fermer les modals
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => {
                if (modal.style.display === 'flex') {
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                }
            });
        }
    });
    
    // Confirmation avant de quitter une page avec formulaire modifié
    const forms = document.querySelectorAll('form');
    let formModified = false;
    
    forms.forEach(form => {
        form.addEventListener('change', function() {
            formModified = true;
        });
        
        form.addEventListener('submit', function() {
            formModified = false;
        });
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (formModified) {
            e.preventDefault();
            e.returnValue = '';
            return '';
        }
    });
});

// Exporter les fonctions utiles globalement
window.BRINKS = {
    showNotification,
    formatDateTime,
    formatDate,
    calculateDuration,
    formatDuration,
    apiGet,
    apiPost,
    apiPut,
    apiDelete,
    openModal,
    closeModal,
    sortTable,
    filterTable,
    getFormData,
    resetForm,
    validateForm,
    exportToCSV,
    printSection,
    saveToLocalStorage,
    getFromLocalStorage,
    removeFromLocalStorage,
    confirmAction,
    showLoading,
    hideLoading,
    getStatusBadgeClass,
    getRoleBadgeClass,
    isValidEmail,
    escapeHtml
};
