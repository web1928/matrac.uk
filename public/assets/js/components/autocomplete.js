document.addEventListener("DOMContentLoaded", function () {
  /**
   * SUMMARY OF CODE COMPARISON BETWEEN INVENTORY.JS AND GOODS RECEIPT.JS
   *
   * * Function searchMaterials in both files are identical.
   * * Event listener for materialInput in both files are identical.
   * * Function displayMaterialResults in both files are identical except GOODS RECEIPT.JS includes base_uom in the dropdown items.
   * * Function selectMaterial in both files are similar but GOODS RECEIPT.JS sets a hidden input for material ID and updates a UOM badge.
   *
   */

  const materialInput = document.getElementById("material-search");
  const materialDropdown = document.getElementById("material-dropdown");

  // Only initialize if autocomplete elements exist
  if (!materialInput || !materialDropdown) {
    return;
  }

  const filterForm = document.getElementById("inventory-filter");
  const materialIdInput = document.getElementById("material-id");
  const quantityUom = document.getElementById("quantity-uom");

  //-----------------------------------------------------------------------------------------------
  /**
   * Search materials via API
   * */

  const searchMaterials = async (query) => {
    if (query.length < 3) {
      materialDropdown.classList.remove("autocomplete-dropdown--visible");
      return;
    }

    try {
      const results = await apiRequest(`materials/search?q=${encodeURIComponent(query)}`);
      //   console.log("Material search results:", results);
      displayMaterialResults(results);
    } catch (error) {
      console.error("Material search error:", error);
    }
  };
  // ----------------------------------------------------------------------------------------------
  /**
   * Material autocomplete
   * */

  if (materialInput) {
    materialInput.addEventListener(
      "input",
      debounce((e) => searchMaterials(e.target.value), 300)
    );

    // Close dropdown when clicking outside
    document.addEventListener("click", (e) => {
      if (!e.target.closest(".autocomplete-wrapper")) {
        materialDropdown.classList.remove("autocomplete-dropdown--visible");
      }
    });
  }

  // ----------------------------------------------------------------------------------------------
  /**
   * Display material search results
   * */

  function displayMaterialResults(results) {
    if (!results || results.length === 0) {
      materialDropdown.innerHTML = `
                <div class="autocomplete-item" style="color: var(--text-secondary);">
                    No materials found
                </div>
            `;
      materialDropdown.classList.add("autocomplete-dropdown--visible");
      return;
    }

    materialDropdown.innerHTML = results
      .map(
        (material) => `
            <div class="autocomplete-item" data-id="${material.material_id}" data-uom="${escapeHtml(
          material.base_uom
        )}">
                <div class="autocomplete-item__primary">
                    ${escapeHtml(material.code)} - ${escapeHtml(material.description)}
                </div>
                ${
                  filterForm
                    ? ""
                    : `<div class="autocomplete-item__secondary">Base UOM: ${escapeHtml(material.base_uom)}</div>`
                }
            </div>
        `
      )
      .join("");

    // Add click handlers
    materialDropdown.querySelectorAll(".autocomplete-item").forEach((item) => {
      item.addEventListener("click", () => selectMaterial(item));
    });

    materialDropdown.classList.add("autocomplete-dropdown--visible");
  }

  // ----------------------------------------------------------------------------------------------
  /**
   * Select a material from dropdown
   * */
  function selectMaterial(item) {
    const materialId = item.dataset.id;
    const uom = item.dataset.uom;
    const text = item.querySelector(".autocomplete-item__primary").textContent.trim();

    // Set hidden input
    materialIdInput ? (materialIdInput.value = materialId) : null;

    // Set visible input
    materialInput.value = text;

    // Update UOM badge
    quantityUom ? (quantityUom.textContent = uom) : null;

    // Hide dropdown
    materialDropdown.classList.remove("autocomplete-dropdown--visible");
  }
});
