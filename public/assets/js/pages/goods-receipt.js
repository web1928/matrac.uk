/**
 * Goods Receipt Page JavaScript
 * ENHANCEMENTS ONLY - Form works without JavaScript!
 * Provides: Autocomplete, AJAX submission, Recent receipts
 */

document.addEventListener("DOMContentLoaded", () => {
  const materialIdInput = document.getElementById("material-id");
  const quantityUom = document.getElementById("quantity-uom");

  const supplierInput = document.getElementById("supplier-search");
  const supplierIdInput = document.getElementById("supplier-id");
  const supplierDropdown = document.getElementById("supplier-dropdown");

  const form = document.getElementById("goods-receipt-form");

  /**
   * Search suppliers via API
   */
  const searchSuppliers = async (query) => {
    if (query.length < 3) {
      supplierDropdown.classList.remove("autocomplete-dropdown--visible");
      return;
    }

    try {
      const results = await apiRequest(`suppliers/search?q=${query}`);

      displaySupplierResults(results);
    } catch (error) {
      console.error("Supplier search error:", error);
    }
  };

  // Supplier autocomplete
  if (supplierInput) {
    supplierInput.addEventListener(
      "input",
      debounce((e) => searchSuppliers(e.target.value), 300)
    );

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".autocomplete-wrapper")) {
        supplierDropdown.classList.remove("autocomplete-dropdown--visible");
      }
    });
  }

  // Form submission (AJAX for better UX)
  if (form) {
    form.addEventListener("submit", (e) => handleSubmit(e));
  }

  /**
   * Display supplier search results
   */
  function displaySupplierResults(results) {
    if (!results || results.length === 0) {
      supplierDropdown.innerHTML = `
                <div class="autocomplete-item" style="color: var(--text-secondary);">
                    No suppliers found
                </div>
            `;
      supplierDropdown.classList.add("autocomplete-dropdown--visible");
      return;
    }

    supplierDropdown.innerHTML = results
      .map(
        (supplier) => `
            <div class="autocomplete-item" data-id="${supplier.supplier_id}">
                <div class="autocomplete-item__primary">
                    ${escapeHtml(supplier.supplier_name)}
                </div>
                ${
                  supplier.contact_name
                    ? `<div class="autocomplete-item__secondary">
                        Contact: ${escapeHtml(supplier.contact_name)}
                    </div>`
                    : ""
                }
            </div>
        `
      )
      .join("");

    // Add click handlers
    supplierDropdown.querySelectorAll(".autocomplete-item").forEach((item) => {
      item.addEventListener("click", () => selectSupplier(item));
    });

    supplierDropdown.classList.add("autocomplete-dropdown--visible");
  }

  /**
   * Select a supplier from dropdown
   */
  function selectSupplier(item) {
    const supplierId = item.dataset.id;
    const text = item.querySelector(".autocomplete-item__primary").textContent.trim();

    // Set hidden input
    supplierIdInput.value = supplierId;

    // Set visible input
    supplierInput.value = text;

    // Hide dropdown
    supplierDropdown.classList.remove("autocomplete-dropdown--visible");
  }

  /**
   * Handle form submission (AJAX)
   */
  const handleSubmit = async (e) => {
    e.preventDefault();

    // Validate material selected
    if (!materialIdInput.value) {
      showAlert("Please select a material from the dropdown", "error");
      return;
    }

    // Validate supplier selected
    if (!supplierIdInput.value) {
      showAlert("Please select a supplier from the dropdown", "error");
      return;
    }

    const submitBtn = document.getElementById("submit-btn");
    const originalText = submitBtn.textContent;

    // Disable button and show loading
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner"></span> Processing...';

    try {
      // Get form data
      const formData = new FormData(form);

      // Submit via fetch (using FormData)
      const response = await fetch(url("/goods-receipt"), {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.success) {
        // Show success message
        showAlert(`✓ Material received successfully! Batch code: ${result.batch_code}`, "success", 7000);

        // Reset form
        form.reset();
        materialIdInput.value = "";
        supplierIdInput.value = "";
        if (quantityUom) {
          quantityUom.textContent = "-";
        }

        // Reload recent receipts
        loadRecentReceipts();

        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      } else {
        // Show error
        showAlert(result.error || "Receipt failed", "error");

        // Re-enable button
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    } catch (error) {
      console.error("Form submission error:", error);
      showAlert("An error occurred. Please try again.", "error");

      // Re-enable button
      submitBtn.disabled = false;
      submitBtn.textContent = originalText;
    }
  };

  /**
   * Load recent receipts (today's receipts)
   */
  const loadRecentReceipts = async () => {
    const container = document.getElementById("recent-receipts");

    if (!container) return;

    try {
      const response = await apiRequest("receipts/recent");

      if (response.success && response.receipts) {
        displayRecentReceipts(response.receipts);
      } else {
        container.innerHTML = `
                    <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                        No receipts today
                    </p>
                `;
      }
    } catch (error) {
      console.error("Load recent receipts error:", error);
      container.innerHTML = `
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                    Unable to load recent receipts
                </p>
            `;
    }
  };

  // Load recent receipts
  loadRecentReceipts();

  /**
   * Display recent receipts in table
   */
  function displayRecentReceipts(receipts) {
    const container = document.getElementById("recent-receipts");

    if (receipts.length === 0) {
      container.innerHTML = `
                <p style="color: var(--text-secondary); text-align: center; padding: 2rem;">
                    No receipts today
                </p>
            `;
      return;
    }

    container.innerHTML = `
            <table class="table table--striped">
                <thead>
                    <tr>
                        <th>Time</th>
                        <th>Batch Code</th>
                        <th>Material</th>
                        <th>Quantity</th>
                        <th>Supplier</th>
                    </tr>
                </thead>
                <tbody>
                    ${receipts
                      .map(
                        (receipt) => `
                        <tr>
                            <td>${new Date(receipt.receipt_date).toLocaleTimeString("en-GB", {
                              hour: "2-digit",
                              minute: "2-digit",
                            })}</td>
                            <td><strong>${escapeHtml(receipt.internal_batch_code)}</strong></td>
                            <td>${escapeHtml(receipt.material_code)} - ${escapeHtml(receipt.material_description)}</td>
                            <td>${formatNumber(receipt.delivered_quantity, 3)} ${escapeHtml(
                          receipt.delivered_qty_uom
                        )}</td>
                            <td>${receipt.supplier_name ? escapeHtml(receipt.supplier_name) : "-"}</td>
                        </tr>
                    `
                      )
                      .join("")}
                </tbody>
            </table>
        `;
  }
});
