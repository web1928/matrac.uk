/**
 * Utility Functions
 * Shared JavaScript helpers for forms, API calls, etc.
 */

/**
 * Build URL with proper slashes
 *
 * @param {string} path - Path to append to BASE_URL
 * @returns {string} Full URL
 */
function url(path) {
  const baseUrl = BASE_URL.replace(/\/$/, "");
  const cleanPath = path.replace(/^\//, "");
  return `${baseUrl}/${cleanPath}`;
}

/**
 * Make API request with error handling
 * Automatically prepends BASE_URL and /api/ to endpoint
 *
 * @param {string} endpoint - API endpoint (e.g., 'materials/search')
 * @param {object} options - Fetch options
 * @returns {Promise} Response data
 */
async function apiRequest(endpoint, options = {}) {
  // Build full URL - automatically adds /api/
  const url = `${BASE_URL.replace(/\/$/, "")}/api/${endpoint.replace(/^\//, "")}`;

  try {
    const response = await fetch(url, {
      headers: {
        "Content-Type": "application/json",
        ...options.headers,
      },
      ...options,
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    return await response.json();
  } catch (error) {
    console.error("API Request failed:", error);
    throw error;
  }
}

/**
 * Show alert message
 *
 * @param {string} message - Alert message
 * @param {string} type - Alert type (success, error, warning, info)
 * @param {number} duration - Auto-dismiss duration in ms (0 = no auto-dismiss)
 */
function showAlert(message, type = "info", duration = 5000) {
  const container = document.querySelector(".alert-container") || createAlertContainer();

  const alert = document.createElement("div");
  alert.className = `alert alert--${type}`;
  alert.innerHTML = `
        <div class="alert__message">${escapeHtml(message)}</div>
    `;

  container.appendChild(alert);

  // Auto-dismiss if duration specified
  if (duration > 0) {
    setTimeout(() => {
      alert.style.opacity = "0";
      setTimeout(() => alert.remove(), 300);
    }, duration);
  }

  return alert;
}

/**
 * Create alert container if it doesn't exist
 */
function createAlertContainer() {
  const container = document.createElement("div");
  container.className = "alert-container";
  container.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        z-index: 9999;
        max-width: 400px;
    `;
  document.body.appendChild(container);
  return container;
}

/**
 * Escape HTML to prevent XSS
 *
 * @param {string} text - Text to escape
 * @returns {string} Escaped text
 */
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

/**
 * Format date to YYYY-MM-DD
 *
 * @param {Date} date - Date object
 * @returns {string} Formatted date
 */
function formatDate(date) {
  if (!(date instanceof Date)) {
    date = new Date(date);
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  return `${year}-${month}-${day}`;
}

/**
 * Format date to DD/MM/YYYY
 *
 * @param {Date} date - Date object
 * @returns {string} Formatted date
 */
function formatDateUK(date) {
  if (!(date instanceof Date)) {
    date = new Date(date);
  }

  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");

  return `${day}/${month}/${year}`;
}

/**
 * Debounce function - delays execution until after wait time
 * Useful for autocomplete search
 *
 * @param {Function} func - Function to debounce
 * @param {number} wait - Wait time in ms
 * @returns {Function} Debounced function
 */
function debounce(func, wait = 300) {
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
 * Show loading spinner in element
 *
 * @param {HTMLElement} element - Target element
 */
function showLoading(element) {
  element.classList.add("loading");
  element.disabled = true;

  const spinner = document.createElement("span");
  spinner.className = "spinner";
  element.appendChild(spinner);
}

/**
 * Hide loading spinner
 *
 * @param {HTMLElement} element - Target element
 */
function hideLoading(element) {
  element.classList.remove("loading");
  element.disabled = false;

  const spinner = element.querySelector(".spinner");
  if (spinner) {
    spinner.remove();
  }
}

/**
 * Validate form inputs
 *
 * @param {HTMLFormElement} form - Form element
 * @returns {boolean} True if valid
 */
function validateForm(form) {
  let isValid = true;

  // Clear previous errors
  form.querySelectorAll(".form-error").forEach((el) => el.remove());
  form.querySelectorAll(".form-input--invalid").forEach((el) => {
    el.classList.remove("form-input--invalid");
  });

  // Validate required fields
  form.querySelectorAll("[required]").forEach((input) => {
    if (!input.value.trim()) {
      showFieldError(input, "This field is required");
      isValid = false;
    }
  });

  return isValid;
}

/**
 * Show field validation error
 *
 * @param {HTMLElement} input - Input element
 * @param {string} message - Error message
 */
function showFieldError(input, message) {
  input.classList.add("form-input--invalid");

  const error = document.createElement("span");
  error.className = "form-error";
  error.textContent = message;

  input.parentElement.appendChild(error);
}

/**
 * Confirm dialog
 *
 * @param {string} message - Confirmation message
 * @returns {boolean} User's choice
 */
function confirmDialog(message) {
  return confirm(message);
}

/**
 * Parse query string parameters
 *
 * @returns {object} Query parameters
 */
function getQueryParams() {
  const params = {};
  const searchParams = new URLSearchParams(window.location.search);

  for (const [key, value] of searchParams) {
    params[key] = value;
  }

  return params;
}

/**
 * Format number with thousand separators
 *
 * @param {number} num - Number to format
 * @param {number} decimals - Decimal places
 * @returns {string} Formatted number
 */
function formatNumber(num, decimals = 2) {
  return Number(num).toLocaleString("en-GB", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  });
}
