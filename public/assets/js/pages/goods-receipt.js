/**
 * Goods Receipt Page JavaScript
 * ENHANCEMENTS ONLY - Form works without JavaScript!
 * Provides: Autocomplete, AJAX submission, Recent receipts
 */

class GoodsReceiptManager {
  constructor() {
    this.materialInput = document.getElementById("material-search");
    this.materialIdInput = document.getElementById("material-id");
    this.materialDropdown = document.getElementById("material-dropdown");

    this.supplierInput = document.getElementById("supplier-search");
    this.supplierIdInput = document.getElementById("supplier-id");
    this.supplierDropdown = document.getElementById("supplier-dropdown");

    this.quantityUom = document.getElementById("quantity-uom");
    this.form = document.getElementById("goods-receipt-form");

    this.selectedMaterial = null;

    this.init();
  }

  init() {
    // Material autocomplete
    if (this.materialInput) {
      this.materialInput.addEventListener(
        "input",
        debounce((e) => this.searchMaterials(e.target.value), 300)
      );

      // Close dropdown when clicking outside
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".autocomplete-wrapper")) {
          this.materialDropdown.classList.remove("autocomplete-dropdown--visible");
        }
      });
    }

    // Supplier autocomplete
    if (this.supplierInput) {
      this.supplierInput.addEventListener(
        "input",
        debounce((e) => this.searchSuppliers(e.target.value), 300)
      );

      // Close dropdown when clicking outside
      document.addEventListener("click", (e) => {
        if (!e.target.closest(".autocomplete-wrapper")) {
          this.supplierDropdown.classList.remove("autocomplete-dropdown--visible");
        }
      });
    }

    // Form submission (AJAX for better UX)
    if (this.form) {
      this.form.addEventListener("submit", (e) => this.handleSubmit(e));
    }

    // Load recent receipts
    this.loadRecentReceipts();
  }

  /**
   * Search materials via API
   */
  async searchMaterials(query) {
    if (query.length < 3) {
      this.materialDropdown.classList.remove("autocomplete-dropdown--visible");
      return;
    }

    try {
      const results = await apiRequest(`materials/search?q=${encodeURIComponent(query)}`);

      this.displayMaterialResults(results);
    } catch (error) {
      console.error("Material search error:", error);
    }
  }

  /**
   * Display material search results
   */
  displayMaterialResults(results) {
    if (!results || results.length === 0) {
      this.materialDropdown.innerHTML = `
                <div class="autocomplete-item" style="color: var(--text-secondary);">
                    No materials found
                </div>
            `;
      this.materialDropdown.classList.add("autocomplete-dropdown--visible");
      return;
    }

    this.materialDropdown.innerHTML = results
      .map(
        (material) => `
            <div class="autocomplete-item" data-id="${material.material_id}" data-uom="${escapeHtml(
          material.base_uom
        )}">
                <div class="autocomplete-item__primary">
                    ${escapeHtml(material.code)} - ${escapeHtml(material.description)}
                </div>
                <div class="autocomplete-item__secondary">
                    Base UOM: ${escapeHtml(material.base_uom)}
                </div>
            </div>
        `
      )
      .join("");

    // Add click handlers
    this.materialDropdown.querySelectorAll(".autocomplete-item").forEach((item) => {
      item.addEventListener("click", () => this.selectMaterial(item));
    });

    this.materialDropdown.classList.add("autocomplete-dropdown--visible");
  }

  /**
   * Select a material from dropdown
   */
  selectMaterial(item) {
    const materialId = item.dataset.id;
    const uom = item.dataset.uom;
    const text = item.querySelector(".autocomplete-item__primary").textContent.trim();

    // Set hidden input
    this.materialIdInput.value = materialId;

    // Set visible input
    this.materialInput.value = text;

    // Update UOM badge
    if (this.quantityUom) {
      this.quantityUom.textContent = uom;
    }

    // Store selected material
    this.selectedMaterial = { id: materialId, uom: uom };

    // Hide dropdown
    this.materialDropdown.classList.remove("autocomplete-dropdown--visible");
  }

  /**
   * Search suppliers via API
   */
  async searchSuppliers(query) {
    if (query.length < 3) {
      this.supplierDropdown.classList.remove("autocomplete-dropdown--visible");
      return;
    }

    try {
      const results = await apiRequest(`suppliers/search?q=${encodeURIComponent(query)}`);

      this.displaySupplierResults(results);
    } catch (error) {
      console.error("Supplier search error:", error);
    }
  }

  /**
   * Display supplier search results
   */
  displaySupplierResults(results) {
    if (!results || results.length === 0) {
      this.supplierDropdown.innerHTML = `
                <div class="autocomplete-item" style="color: var(--text-secondary);">
                    No suppliers found
                </div>
            `;
      this.supplierDropdown.classList.add("autocomplete-dropdown--visible");
      return;
    }

    this.supplierDropdown.innerHTML = results
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
    this.supplierDropdown.querySelectorAll(".autocomplete-item").forEach((item) => {
      item.addEventListener("click", () => this.selectSupplier(item));
    });

    this.supplierDropdown.classList.add("autocomplete-dropdown--visible");
  }

  /**
   * Select a supplier from dropdown
   */
  selectSupplier(item) {
    const supplierId = item.dataset.id;
    const text = item.querySelector(".autocomplete-item__primary").textContent.trim();

    // Set hidden input
    this.supplierIdInput.value = supplierId;

    // Set visible input
    this.supplierInput.value = text;

    // Hide dropdown
    this.supplierDropdown.classList.remove("autocomplete-dropdown--visible");
  }

  /**
   * Handle form submission (AJAX)
   */
  async handleSubmit(e) {
    e.preventDefault();

    // Validate material selected
    if (!this.materialIdInput.value) {
      showAlert("Please select a material from the dropdown", "error");
      return;
    }

    // Validate supplier selected
    if (!this.supplierIdInput.value) {
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
      const formData = new FormData(this.form);

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
        this.form.reset();
        this.materialIdInput.value = "";
        this.supplierIdInput.value = "";
        if (this.quantityUom) {
          this.quantityUom.textContent = "-";
        }

        // Reload recent receipts
        this.loadRecentReceipts();

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
  }

  /**
   * Load recent receipts (today's receipts)
   */
  async loadRecentReceipts() {
    const container = document.getElementById("recent-receipts");

    if (!container) return;

    try {
      const response = await apiRequest("receipts/recent");

      if (response.success && response.receipts) {
        this.displayRecentReceipts(response.receipts);
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
  }

  /**
   * Display recent receipts in table
   */
  displayRecentReceipts(receipts) {
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
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  window.goodsReceiptManager = new GoodsReceiptManager();
});
