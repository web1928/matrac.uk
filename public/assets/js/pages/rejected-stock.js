/**
 * Rejected Stock Page JavaScript
 * Displays rejected inventory with details
 */

class RejectedStockManager {
  constructor() {
    this.init();
    this.initModalClose();
  }

  init() {
    this.loadRejectedStock();
  }

  /**
   * Initialize modal close functionality
   */
  initModalClose() {
    // Close modals when clicking close button or backdrop
    document.querySelectorAll(".modal").forEach((modal) => {
      // Close button
      const closeBtn = modal.querySelector(".modal__close");
      if (closeBtn) {
        closeBtn.addEventListener("click", () => {
          modal.classList.remove("modal--visible");
        });
      }

      // Backdrop click (click on overlay, not dialog)
      modal.addEventListener("click", (e) => {
        if (e.target === modal) {
          modal.classList.remove("modal--visible");
        }
      });
    });

    // ESC key to close modals
    document.addEventListener("keydown", (e) => {
      if (e.key === "Escape") {
        document.querySelectorAll(".modal--visible").forEach((modal) => {
          modal.classList.remove("modal--visible");
        });
      }
    });
  }

  /**
   * Load rejected stock data
   */
  async loadRejectedStock() {
    try {
      const response = await apiRequest("rejected-stock/data");

      if (response.success) {
        this.displayRejectedStock(response.rejected_stock);
        this.updateSummary(response.summary);
      } else {
        showAlert("Failed to load rejected stock", "error");
      }
    } catch (error) {
      console.error("Load rejected stock error:", error);
      showAlert("Failed to load rejected stock", "error");
    }
  }

  /**
   * Display rejected stock in table
   */
  displayRejectedStock(stock) {
    const container = document.getElementById("rejected-container");

    if (stock.length === 0) {
      container.innerHTML = `
                <div class="empty-state">
                    <div class="empty-state__icon">✓</div>
                    <div class="empty-state__title">No Rejected Stock</div>
                    <div class="empty-state__message">There is currently no rejected inventory.</div>
                </div>
            `;
      return;
    }

    container.innerHTML = `
            <table class="table table--striped">
                <thead>
                    <tr>
                        <th>Batch Code</th>
                        <th>Material</th>
                        <th>Stage</th>
                        <th>Quantity</th>
                        <th>Rejected Date</th>
                        <th>Rejected By</th>
                        <th>Reason</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    ${stock
                      .map(
                        (item) => `
                        <tr>
                            <td><strong>${escapeHtml(item.internal_batch_code)}</strong></td>
                            <td>${escapeHtml(item.material_code)} - ${escapeHtml(item.material_description)}</td>
                            <td>${escapeHtml(item.stage_name)}</td>
                            <td>${formatNumber(item.quantity, 3)} ${escapeHtml(item.base_uom)}</td>
                            <td>${item.rejected_date ? new Date(item.rejected_date).toLocaleString("en-GB") : "-"}</td>
                            <td>${
                              item.rejected_by ? escapeHtml(item.first_name) + " " + escapeHtml(item.last_name) : "-"
                            }</td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="${escapeHtml(
                              item.rejection_reason || ""
                            )}">
                                ${item.rejection_reason ? escapeHtml(item.rejection_reason) : "-"}
                            </td>
                            <td>
                                <button class="table-action-btn" onclick="window.rejectedStockManager.viewBatchDetails(${
                                  item.batch_id
                                })">
                                    View Details
                                </button>
                            </td>
                        </tr>
                    `
                      )
                      .join("")}
                </tbody>
            </table>
        `;
  }

  /**
   * Update summary cards
   */
  updateSummary(summary) {
    document.getElementById("rejected-batches").textContent = summary.rejected_batches || 0;
    document.getElementById("rejected-quantity").textContent = summary.total_quantity
      ? formatNumber(summary.total_quantity, 0)
      : 0;
    document.getElementById("rejected-materials").textContent = summary.materials_affected || 0;
  }

  /**
   * View batch details
   */
  async viewBatchDetails(batchId) {
    try {
      const response = await apiRequest(`batch/details?batch_id=${batchId}`);

      if (response.success) {
        this.displayBatchDetails(response);
      } else {
        showAlert("Failed to load batch details", "error");
      }
    } catch (error) {
      console.error("View batch details error:", error);
      showAlert("Failed to load batch details", "error");
    }
  }

  /**
   * Display batch details in modal
   */
  displayBatchDetails(data) {
    const batch = data.batch;
    const inventory = data.inventory;
    const transactions = data.transactions;

    const content = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
                <div>
                    <h4 style="margin-bottom: 0.5rem;">Batch Information</h4>
                    <table style="width: 100%; font-size: 0.875rem;">
                        <tr><td style="padding: 0.25rem 0;"><strong>Batch Code:</strong></td><td>${escapeHtml(
                          batch.internal_batch_code
                        )}</td></tr>
                        <tr><td><strong>Material:</strong></td><td>${escapeHtml(batch.material_code)} - ${escapeHtml(
      batch.material_description
    )}</td></tr>
                        <tr><td><strong>Supplier:</strong></td><td>${
                          batch.supplier_name ? escapeHtml(batch.supplier_name) : "-"
                        }</td></tr>
                        <tr><td><strong>Delivered Qty:</strong></td><td>${formatNumber(
                          batch.delivered_quantity,
                          3
                        )} ${escapeHtml(batch.delivered_qty_uom)}</td></tr>
                        <tr><td><strong>Receipt Date:</strong></td><td>${new Date(batch.receipt_date).toLocaleString(
                          "en-GB"
                        )}</td></tr>
                    </table>
                </div>
                <div>
                    <h4 style="margin-bottom: 0.5rem;">Supplier Information</h4>
                    <table style="width: 100%; font-size: 0.875rem;">
                        <tr><td style="padding: 0.25rem 0;"><strong>Use By Date:</strong></td><td>${
                          batch.supplier_useby_1 ? formatDateUK(new Date(batch.supplier_useby_1)) : "-"
                        }</td></tr>
                        <tr><td><strong>Batch Code:</strong></td><td>${batch.supplier_batch_code_1 || "-"}</td></tr>
                        <tr><td><strong>PO Number:</strong></td><td>${batch.po_number || "-"}</td></tr>
                        <tr><td><strong>Haulier:</strong></td><td>${batch.haulier_name || "-"}</td></tr>
                        <tr><td><strong>Delivery Note:</strong></td><td>${batch.delivery_note_ref || "-"}</td></tr>
                    </table>
                </div>
            </div>
            
            <h4 style="margin-bottom: 0.5rem;">Current Inventory Breakdown</h4>
            <table class="table table--compact" style="margin-bottom: 2rem;">
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>Status</th>
                        <th>Quantity</th>
                    </tr>
                </thead>
                <tbody>
                    ${inventory
                      .map(
                        (inv) => `
                        <tr>
                            <td>${escapeHtml(inv.stage_name)}</td>
                            <td><span class="badge badge--${inv.is_available ? "success" : "error"}">${escapeHtml(
                          inv.status_name
                        )}</span></td>
                            <td>${formatNumber(inv.quantity, 3)} ${escapeHtml(batch.base_uom)}</td>
                        </tr>
                    `
                      )
                      .join("")}
                </tbody>
            </table>
            
            <h4 style="margin-bottom: 0.5rem;">Transaction History</h4>
            <table class="table table--compact">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>From → To</th>
                        <th>Quantity</th>
                        <th>User</th>
                        <th>Date</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    ${transactions
                      .map(
                        (t) => `
                        <tr>
                            <td><strong>${escapeHtml(t.transaction_type)}</strong></td>
                            <td>${t.from_stage || "-"} → ${t.to_stage || "-"}</td>
                            <td>${formatNumber(t.quantity, 3)} ${escapeHtml(batch.base_uom)}</td>
                            <td>${escapeHtml(t.first_name)} ${escapeHtml(t.last_name)}</td>
                            <td>${new Date(t.created_at).toLocaleString("en-GB")}</td>
                            <td>${t.notes || "-"}</td>
                        </tr>
                    `
                      )
                      .join("")}
                </tbody>
            </table>
        `;

    document.getElementById("batch-detail-content").innerHTML = content;
    document.getElementById("batch-detail-modal").classList.add("modal--visible");
  }
}

// Initialize when DOM is ready
document.addEventListener("DOMContentLoaded", () => {
  window.rejectedStockManager = new RejectedStockManager();
});
