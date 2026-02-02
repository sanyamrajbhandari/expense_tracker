<?php
require "../config/db.php";
$currentPage = 'wallets';
include "../includes/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>

<main class="container wallets-layout">
  <!-- Left Side: Wallet List -->
  <section class="wallets-list-section">
      <div class="section-header">
          <div>
            <h1 class="page-title">My Wallets</h1>
            <p class="subtitle">Manage your active accounts and balances</p>
          </div>
      <button id="addWalletBtn" class="btn-primary"><i class="fas fa-plus"></i> Add Wallet</button>
      </div>

      <div id="walletsPageGrid" class="wallet-cards-grid">
          <!-- Wallets injected via JS -->
          <div class="loading-state">Loading wallets...</div>
      </div>
  </section>

  <!-- Right Side: Wallet Details -->
  <section class="wallet-details-section" id="walletDetails">
        <!-- Details injected via JS -->
        <div class="empty-state">Select a wallet to view details</div>
  </section>

  <!-- Add Wallet Modal -->
  <div class="overlay" id="addWalletModal">
    <div class="formModal">
      <div class="modalTopSection">
        <p>Add New Wallet</p>
        <button id="closeWalletModal"><i class="fas fa-times"></i></button>
      </div>
      <form id="addWalletForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <div class="modalMiddleSection">
            <div class="formGroup">
                <label>Wallet Name</label>
                <input type="text" name="name" required placeholder="e.g. Cash, Savings">
            </div>
            <div class="formGroup">
                <label>Initial Balance</label>
                <div class="inputWithPrefix">
                    <span>Rs.</span>
                    <input type="number" name="balance" step="0.01" required placeholder="0.00">
                </div>
            </div>
        </div>
        <div class="modalBottomSection">
            <button type="button" class="bottomButtons" id="cancelWalletBtn">Cancel</button>
            <button type="submit" class="bottomButtons">Create Wallet</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Edit Wallet Modal -->
  <div class="overlay" id="editWalletModal">
    <div class="formModal">
      <div class="modalTopSection">
        <p>Edit Wallet</p>
        <button id="closeEditWalletModal"><i class="fas fa-times"></i></button>
      </div>
      <form id="editWalletForm">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="id" id="editWalletId">
        <div class="modalMiddleSection">
            <div class="formGroup">
                <label>Wallet Name</label>
                <input type="text" name="name" id="editWalletName" required>
            </div>
            <!-- Balance editing is restricted or we can allow it with warning -->
            <!-- Let's allow simple name edit first as per common practice, but plan mentioned update_wallet supports balance -->
            <div class="formGroup">
                <label>Balance (Manual Adjustment)</label>
                <div class="inputWithPrefix">
                    <span>Rs.</span>
                    <input type="number" name="balance" id="editWalletBalance" step="0.01" required>
                </div>
            </div>
        </div>
        <div class="modalBottomSection">
            <button type="submit" class="bottomButtons">Update Wallet</button>
        </div>
      </form>
    </div>
  </div>

</main>
<script src="../assets/js/wallets.js"></script>
<?php include "../includes/footer.php"; ?>
