const utils = {
  // Format date to local string
  formatDate: (date) => {
    return new Date(date).toLocaleDateString();
  },

  // Format currency
  formatCurrency: (amount) => {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount);
  },

  showLoading: () => {
    const spinner = document.createElement('div');
    spinner.id = 'loading-spinner';
    spinner.className = 'fixed inset-0 flex items-center justify-center bg-black bg-opacity-50';
    spinner.innerHTML = `
      <div class="w-16 h-16 border-4 border-blue-600 border-t-transparent rounded-full animate-spin"></div>
    `;
    document.body.appendChild(spinner);
  },

  // Hide loading spinner
  hideLoading: () => {
    const spinner = document.getElementById('loading-spinner');
    if (spinner) {
      spinner.remove();
    }
  },

showAlert: (message, type = 'success') => {
    const alertDiv = document.createElement('div');
    const typeClasses = type === 'success' ? 'bg-green-100 border border-green-200 text-green-800' : 'bg-red-100 border border-red-200 text-red-800';
    alertDiv.className = `p-3 rounded relative ${typeClasses}`;
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="absolute top-2 right-2 text-xl leading-none" onclick="this.parentElement.remove()">&times;</button>
    `;
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', () => utils.insertAlert(alertDiv));
    } else {
      utils.insertAlert(alertDiv);
    }
  
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      alertDiv.remove();
    }, 5000);
  },
  
  // Helper function to insert the alert into the DOM
  insertAlert: (alertDiv) => {
    let container = document.querySelector('.container');
  
    if (!container) {
      container = document.createElement('div');
      container.className = 'container';
      document.body.prepend(container);
    }
  
    container.prepend(alertDiv);
  }
  
  
    
};

// Form validation
const formValidator = {
  validateEmail: (email) => {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
  },

  validatePhone: (phone) => {
    const re = /^\+?[\d\s-]{10,}$/;
    return re.test(phone);
  },

  validateRequired: (value) => {
    return value.trim() !== '';
  }
};

// Event handlers
document.addEventListener('DOMContentLoaded', () => {

  document.querySelectorAll('form.ajax-form').forEach(form => {
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      utils.showLoading();

      try {
        const formData = new FormData(form);
        const response = await fetch(form.action, {
          method: form.method,
          body: formData
        });
        const data = await response.json();
        if (data.success) {
          utils.showAlert(data.message, 'success');
          if (data.redirect) {
            window.location.href = data.redirect;
          }
        } else {
          utils.showAlert(data.message, 'danger');
        }
      } catch (error) {
        utils.showAlert('An error occurred. Please try again.', 'danger');
      } finally {
        utils.hideLoading();
      }
    });
  });

  // Handle dynamic table sorting
  const tables = document.querySelectorAll('table[data-sortable]');
  tables.forEach(table => {
    const headers = table.querySelectorAll('th[data-sort]');
    headers.forEach(header => {
      header.addEventListener('click', () => {
        const column = header.dataset.sort;
        const rows = Array.from(table.querySelectorAll('tbody tr'));
        const direction = header.dataset.direction === 'asc' ? -1 : 1;
        
        rows.sort((a, b) => {
          const aValue = a.querySelector(`td[data-${column}]`).dataset[column];
          const bValue = b.querySelector(`td[data-${column}]`).dataset[column];
          return direction * (aValue > bValue ? 1 : -1);
        });

        const tbody = table.querySelector('tbody');
        tbody.innerHTML = '';
        rows.forEach(row => tbody.appendChild(row));

        header.dataset.direction = direction === 1 ? 'asc' : 'desc';
      });
    });
  });
});

window.utils = utils;
window.formValidator = formValidator;
