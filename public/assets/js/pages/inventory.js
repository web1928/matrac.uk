/**
 * Inventory Page JavaScript
 * ENHANCEMENTS ONLY - PHP renders the page!
 * NO table rendering, NO initial data loading
 */

/**
 * Initialize modal close functionality
 */
document.addEventListener("DOMContentLoaded", function () {
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

  // QA Form submission handler
  const form = document.getElementById("qa-action-form");

  if (form) {
    form.addEventListener("submit", async function (e) {
      e.preventDefault();

      const submitBtn = document.getElementById("qa-submit-btn");
      const originalText = submitBtn.textContent;
      submitBtn.disabled = true;
      submitBtn.innerHTML = '<span class="spinner"></span> Processing...';

      try {
        const formData = new FormData(this);

        const response = await fetch(url("/inventory/hold-status"), {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (result.success) {
          showAlert(result.message, "success");

          // Close modal
          document.getElementById("qa-action-modal").classList.remove("modal--visible");

          // Reload page to show updated inventory
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showAlert(result.error || "Action failed", "error");
          submitBtn.disabled = false;
          submitBtn.textContent = originalText;
        }
      } catch (error) {
        console.error("QA action error:", error);
        showAlert("An error occurred", "error");
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    });
  }

  // QA Cancel button handler
  const cancelBtn = document.getElementById("qa-cancel-btn");
  if (cancelBtn) {
    cancelBtn.addEventListener("click", () => {
      document.getElementById("qa-action-modal").classList.remove("modal--visible");
    });
  }
});

/**
 * Open QA Action Modal
 */
function openQAModal(inventoryId, actionType, quantity, uom, batchCode, materialCode, currentStatus) {
  const modal = document.getElementById("qa-action-modal");
  const title = document.getElementById("qa-modal-title");
  const submitBtn = document.getElementById("qa-submit-btn");

  // Set title
  const titles = {
    hold: "Put On Hold",
    release: "Release from Hold",
    reject: "Reject Stock",
  };
  title.textContent = titles[actionType];

  // Set form values
  document.getElementById("qa-inventory-id").value = inventoryId;
  document.getElementById("qa-action-type").value = actionType;
  document.getElementById("qa-quantity").value = quantity;
  document.getElementById("qa-quantity").max = quantity;
  document.getElementById("qa-uom").textContent = uom;
  document.getElementById("qa-notes").value = "";

  // Set batch info
  document.getElementById("qa-batch-info").innerHTML = `
        <div><strong>Batch:</strong> ${escapeHtml(batchCode)}</div>
        <div><strong>Material:</strong> ${escapeHtml(materialCode)}</div>
        <div><strong>Current Status:</strong> ${escapeHtml(currentStatus)}</div>
        <div><strong>Available Quantity:</strong> ${formatNumber(quantity, 3)} ${escapeHtml(uom)}</div>
    `;

  // Update submit button
  submitBtn.textContent = titles[actionType];
  if (actionType === "reject") {
    submitBtn.style.backgroundColor = "#666";
  } else {
    submitBtn.style.backgroundColor = "";
  }

  // Show modal
  modal.classList.add("modal--visible");
}

/**
 * View Batch Details
 */
async function viewBatchDetails(batchId) {
  try {
    const response = await apiRequest(`batch/details?batch_id=${batchId}`);

    if (response.success) {
      displayBatchDetails(response);
    } else {
      showAlert("Failed to load batch details", "error");
    }
  } catch (error) {
    console.error("View batch details error:", error);
    showAlert("Failed to load batch details", "error");
  }
}

/**
 * Display Batch Details in Modal
 */
function displayBatchDetails(data) {
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
                        <td><span class="badge badge--${inv.is_available ? "success" : "warning"}">${escapeHtml(
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
