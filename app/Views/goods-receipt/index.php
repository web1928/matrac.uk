<?php
// Set page data
$pageTitle = 'Goods Receipt';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => '/dashboard'],
    ['label' => 'Goods Receipt', 'url' => null]
];
$additionalScripts = ['js/pages/goods-receipt.js'];

// Start output buffering for layout
ob_start();
?>

<h1 class="page-title">Goods Receipt</h1>

<!-- Alert container for messages -->
<div id="alert-container"></div>

<!-- Goods Receipt Form -->
<div class="card">
    <div class="card__header">
        <h3 class="card__title">Receipt New Material</h3>
    </div>
    <div class="card__body">
        <form id="goods-receipt-form" method="POST">
            <input type="hidden" name="csrf_token" value="<?= h(generateCsrfToken()) ?>">

            <!-- Material Selection with Autocomplete -->
            <div class="form-group">
                <label for="material-search" class="form-label form-label--required">Material</label>
                <div class="autocomplete-wrapper">
                    <input
                        type="text"
                        id="material-search"
                        class="form-input"
                        placeholder="Type material code or description (min 3 characters)..."
                        autocomplete="off"
                        required>
                    <input type="hidden" id="material-id" name="material_id" required>
                    <div id="material-dropdown" class="autocomplete-dropdown"></div>
                </div>
                <span class="form-help">Start typing to search by code or description</span>
            </div>

            <!-- Quantity -->
            <div class="form-group">
                <label for="quantity" class="form-label form-label--required">Quantity</label>
                <div class="input-group">
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        class="form-input"
                        step="0.001"
                        min="0.001"
                        required>
                    <span class="input-group__suffix" id="quantity-uom">-</span>
                </div>
                <span class="form-help">Enter quantity in the material's base unit of measure</span>
            </div>

            <!-- Supplier with Autocomplete -->
            <div class="form-group">
                <label for="supplier-search" class="form-label form-label--required">Supplier</label>
                <div class="autocomplete-wrapper">
                    <input
                        type="text"
                        id="supplier-search"
                        class="form-input"
                        placeholder="Type supplier name (min 3 characters)..."
                        autocomplete="off"
                        required>
                    <input type="hidden" id="supplier-id" name="supplier_id" required>
                    <div id="supplier-dropdown" class="autocomplete-dropdown"></div>
                </div>
                <span class="form-help">Start typing to search suppliers</span>
            </div>

            <!-- Primary Supplier Use By and Batch Code -->
            <div class="form-grid-2">
                <div class="form-group">
                    <label for="supplier-useby-1" class="form-label form-label--required">Supplier Use By Date</label>
                    <input
                        type="date"
                        id="supplier-useby-1"
                        name="supplier_useby_1"
                        class="form-input"
                        required>
                </div>

                <div class="form-group">
                    <label for="supplier-batch-1" class="form-label form-label--required">Supplier Batch Code</label>
                    <input
                        type="text"
                        id="supplier-batch-1"
                        name="supplier_batch_code_1"
                        class="form-input"
                        maxlength="50"
                        required>
                </div>
            </div>

            <!-- Additional Supplier Use By and Batch Codes (Optional) -->
            <div class="info-card">
                <h4 class="info-card__header">
                    Additional Batch Codes (Optional)
                </h4>

                <!-- Batch 2 -->
                <div class="form-grid-2 form-grid--spaced">
                    <div class="form-group">
                        <label for="supplier-useby-2" class="form-label">Use By Date 2</label>
                        <input type="date" id="supplier-useby-2" name="supplier_useby_2" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="supplier-batch-2" class="form-label">Batch Code 2</label>
                        <input type="text" id="supplier-batch-2" name="supplier_batch_code_2" class="form-input" maxlength="50">
                    </div>
                </div>

                <!-- Batch 3 -->
                <div class="form-grid-2 form-grid--spaced">
                    <div class="form-group">
                        <label for="supplier-useby-3" class="form-label">Use By Date 3</label>
                        <input type="date" id="supplier-useby-3" name="supplier_useby_3" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="supplier-batch-3" class="form-label">Batch Code 3</label>
                        <input type="text" id="supplier-batch-3" name="supplier_batch_code_3" class="form-input" maxlength="50">
                    </div>
                </div>

                <!-- Batch 4 -->
                <div class="form-grid-2">
                    <div class="form-group">
                        <label for="supplier-useby-4" class="form-label">Use By Date 4</label>
                        <input type="date" id="supplier-useby-4" name="supplier_useby_4" class="form-input">
                    </div>
                    <div class="form-group">
                        <label for="supplier-batch-4" class="form-label">Batch Code 4</label>
                        <input type="text" id="supplier-batch-4" name="supplier_batch_code_4" class="form-input" maxlength="50">
                    </div>
                </div>
            </div>

            <!-- Purchase Order, Haulier, Delivery Note -->
            <div class="form-grid-3">
                <div class="form-group">
                    <label for="po-number" class="form-label">Purchase Order Number</label>
                    <input type="text" id="po-number" name="po_number" class="form-input" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="haulier-name" class="form-label">Haulier Name</label>
                    <input type="text" id="haulier-name" name="haulier_name" class="form-input" maxlength="100">
                </div>

                <div class="form-group">
                    <label for="delivery-note" class="form-label">Delivery Note Reference</label>
                    <input type="text" id="delivery-note" name="delivery_note_ref" class="form-input" maxlength="50">
                </div>
            </div>

            <!-- Additional Receipt Information -->
            <div class="info-card">
                <h4 class="info-card__header">
                    Additional Information (Optional)
                </h4>

                <div class="form-grid-3 form-grid--spaced">
                    <!-- Silo Assignment -->
                    <div class="form-group">
                        <label for="silo-no" class="form-label">Silo No.</label>
                        <select id="silo-no" name="silo_no" class="form-select">
                            <option value="">Select Silo...</option>
                            <option value="S1">S1</option>
                            <option value="S2">S2</option>
                            <option value="S3">S3</option>
                            <option value="S1 & S3">S1 & S3</option>
                        </select>
                    </div>

                    <!-- COC/COA Attached -->
                    <div class="form-group">
                        <label for="coc-coa-attached" class="form-label">COC/COA Attached?</label>
                        <select id="coc-coa-attached" name="coc_coa_attached" class="form-select">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- RMA Sheet -->
                    <div class="form-group">
                        <label for="rma-sheet" class="form-label">RMA Sheet Attached & Completed?</label>
                        <select id="rma-sheet" name="rma_sheet_completed" class="form-select">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2 form-grid--spaced">
                    <!-- Matches Delivery Note -->
                    <div class="form-group">
                        <label for="matches-delivery" class="form-label">Matches Delivery Note?</label>
                        <select id="matches-delivery" name="matches_delivery_note" class="form-select">
                            <option value="">Select...</option>
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>

                    <!-- Booked-In Confirmation -->
                    <div class="form-group">
                        <label for="bookin-confirmation" class="form-label">Booked-In Confirmation No.</label>
                        <input type="text" id="bookin-confirmation" name="bookin_confirmation_no" class="form-input" maxlength="50">
                    </div>
                </div>

                <!-- Comments -->
                <div class="form-group">
                    <label for="receipt-comments" class="form-label">Comments</label>
                    <textarea id="receipt-comments" name="receipt_comments" class="form-textarea" rows="3" placeholder="Additional notes or comments about this receipt..."></textarea>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="form-actions form-actions--right">
                <button type="button" class="btn btn--secondary" onclick="window.location.href='<?= url('/dashboard') ?>'">
                    Cancel
                </button>
                <button type="submit" class="btn btn--primary" id="submit-btn">
                    Receipt Material
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Recent Receipts -->
<div class="card mt-lg">
    <div class="card__header">
        <h3 class="card__title">Recent Receipts (Today)</h3>
    </div>
    <div class="card__body">
        <div id="recent-receipts">
            <p class="loading-text">
                Loading recent receipts...
            </p>
        </div>
    </div>
</div>

<?php
// Get buffered content
$content = ob_get_clean();

// Include layout
include __DIR__ . '/../layouts/main.php';
?>