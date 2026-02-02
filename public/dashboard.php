<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "../config/db.php";
$currentPage = 'overview';
include "../includes/header.php";
if(!isset($_SESSION['user_name'])){
  header("Location: login.php");
  exit;
}

$username = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];
// To set the timezone to Kathmandu from UTC and show current date and time
date_default_timezone_set('Asia/Kathmandu');
?>

<!-- Main content -->
  <main class="container">
    <div class="header-row">
      <div>
        <h1>Welcome back, <?= e($username) ?></h1>
        <p class="subtitle">Here is your financial snapshot for today.</p>
      </div>

      <button class="btn-primary" id="addTransactionBtn"><i class="fas fa-plus"></i> Add Transaction</button>
    </div>

   <!-- Wallets and networth -->
   <section class="cards-row">
  <!-- Net Worth Card -->
  <div class="card net-worth">
    <p class="label"><i class="fas fa-sack-dollar"></i> Total Net Worth</p>
    <h2><span>Rs. </span><span id="netWorth">0</span></h2>
  </div>

  <!-- Wallets will be injected here -->
  <div id="walletCards"></div>
</section>

    <!-- Overlay for entry of transaction -->
    <div class = "overlay" id="overlay">
      <div class="formModal">

        <div class="modalTopSection">
          <p>Add a transaction</p>
          <button id="closeModal"><i class="fas fa-times"></i></button>
        </div>

        <div class="modalMiddleSection">
          <form id="transactionForm" method = "POST">
            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">

          <!-- Div for the radio buttons for income or expense selection -->
          <div class="transactionType">
            <label class="expense">
    <input type="radio" name="type" value="expense" checked>
    <span>Expense</span>
  </label>

  <label class="income">
    <input type="radio" name="type" value="income">
    <span>Income</span>
  </label>
          </div>

          <!-- The title of the expense -->
          <div class="formGroup">
            <label for="title">Title</label>
            <p style="color:red"><?= e($errorMessage['title'] ?? "") ?></p>
            <input
              id="title"
              type="text"
              name="title"
              placeholder="e.g., Starbucks Coffee or Monthly Rent"
              required
            >
          </div>

          <!-- Expense amount -->
          <div class="formGroup">
            <label for="amount">Amount</label>
            <p style="color:red"><?= e($errorMessage['amount'] ?? "") ?> </p>
            <div class="inputWithPrefix">
              <span>Rs.</span>
              <input id="amount"
                type="number"
                name="amount"
                placeholder="0.00"
                required
              >
            </div>
          </div>

          <!-- Expense category -->
          <div class="formGroup">
            <label for="categoryDropdown">Category</label>
            <select name="category" id="categoryDropdown" required></select>
          </div>

          <!-- The div for expense category and Date -->
          <div class="formRow">
            <div class="formGroup">
              <label for="date">Date</label>
              <input
                id="date"
                type="date"
                value="<?= date('Y-m-d') ?>"
                name="date"
              >
            </div>

            <div class="formGroup">
              <label for="time">Time</label>
              <input
                id="time"
                type="time"
                value="<?= date('H:i') ?>"
                name="time"
              >
            </div>
          </div>

          <!-- Select for the wallet/account -->
          <div class="formGroup">
            <label for="wallet">Wallet/Account</label>
            <select name="wallet" id="walletSelect" required>
              <option value="">Select wallet</option>
            </select>
          </div>

        <div class="modalBottomSection">
          <button type = "submit" class="bottomButtons">Save transaction</button>
          <button class="bottomButtons">Cancel</button>
        </div>
        </form>
      
      </div>
    </div>

</div>

<!-- Edit Transaction Modal (Dashboard) -->
<div class="overlay" id="editTransactionModal">
  <div class="formModal">
    <div class="modalTopSection">
      <p>Edit Transaction</p>
      <button id="closeEditModal"><i class="fas fa-times"></i></button>
    </div>
    <form id="editTransactionForm">
      <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
      <input type="hidden" name="id" id="editId">
      <div class="modalMiddleSection">
        <div class="formGroup">
            <label>Title</label>
            <input type="text" name="title" id="editTitle" required>
        </div>
        <div class="formGroup">
            <label>Amount</label>
            <div class="inputWithPrefix">
                <span>Rs.</span>
                <input type="number" name="amount" id="editAmount" required>
            </div>
        </div>
        <div class="formGroup">
            <label>Category</label>
            <select name="category" id="editCategory" required>
                 <option value="Dining">Dining</option>
                 <option value="Groceries">Groceries</option>
                 <option value="Shopping">Shopping</option>
                 <option value="Transit">Transit</option>
                 <option value="Entertainment">Entertainment</option>
                 <option value="Bills & Fees">Bills & Fees</option>
                 <option value="Gifts">Gifts</option>
                 <option value="Beauty">Beauty</option>
                 <option value="Work">Work</option>
                 <option value="Travel">Travel</option>
            </select>
        </div>
        <div class="formGroup">
            <label>Wallet</label>
            <select name="wallet_id" id="editWallet" required>
                <!-- Wallets populated via JS -->
            </select>
        </div>
      </div>
      <div class="modalBottomSection">
        <button type="submit" class="bottomButtons">Update Transaction</button>
      </div>
    </form>
  </div>
</div>

    <section class="transactions">

    </section>

  </main>

<?php
    include "../includes/footer.php";
?>