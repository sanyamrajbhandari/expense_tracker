<?php
require "../config/db.php";
$currentPage = 'transactions';
include "../includes/header.php";

if (!isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}
?>

<main class="container monthly-layout">

  <!-- Left month sidebar -->
  <aside class="month-sidebar" id="monthList">
    <!-- Months injected via JS -->
  </aside>

  <!-- Transactions -->
  <section class="monthly-transactions">
   <h1 class="page-title">Monthly Expense History</h1>
  <p class="page-subtitle">
    Track your personal spending and transaction trends over time.
  </p>

   <div class="transactions-header">
     <div class="header-left">
       <h2 id="monthTitle" class="month-heading">Loading...</h2>
       <span class="transaction-count" id="totalItems">0 total items</span>
     </div>
     <div class="header-right">
       <div class="search-box">
         <i class="fas fa-search"></i>
         <input type="text" id="searchInput" placeholder="Search for transactions">
       </div>
     </div>
   </div>
  <div id="transactionList"></div>
  </section>

</main>

<!-- Unified Transaction Modal (Reused from Dashboard) -->
<div class = "overlay" id="overlay">
  <div class="formModal">
    <div class="modalTopSection">
      <p id="modalTitle">Edit Transaction</p>
      <button id="closeModal"><i class="fas fa-times"></i></button>
    </div>

    <div class="modalMiddleSection">
      <form id="transactionForm" method = "POST">
        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
        <input type="hidden" name="id" id="transactionId">

        <!-- Div for the radio buttons for income or expense selection -->
        <div class="transactionType">
          <label class="expense">
            <input type="radio" name="type" id="typeExpense" value="expense" checked>
            <span>Expense</span>
          </label>
          <label class="income">
            <input type="radio" name="type" id="typeIncome" value="income">
            <span>Income</span>
          </label>
        </div>

        <!-- The title of the expense -->
        <div class="formGroup">
          <label for="title">Title</label>
          <input id="title" type="text" name="title" required>
        </div>

        <!-- Expense amount -->
        <div class="formGroup">
          <label for="amount">Amount</label>
          <div class="inputWithPrefix">
            <span>Rs.</span>
            <input id="amount" type="number" name="amount" required>
          </div>
        </div>

        <!-- Expense category -->
        <div class="formGroup">
          <label for="categoryDropdown">Category</label>
          <select name="category" id="categoryDropdown" required>
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

        <!-- The div for expense category and Date -->
        <div class="formRow">
          <div class="formGroup">
            <label for="date">Date</label>
            <input id="date" type="date" name="date">
          </div>
          <div class="formGroup">
            <label for="time">Time</label>
            <input id="time" type="time" name="time">
          </div>
        </div>

        <!-- Select for the wallet/account -->
        <div class="formGroup">
          <label for="walletSelect">Wallet/Account</label>
          <select name="wallet" id="walletSelect" required>
            <option value="">Select wallet</option>
          </select>
        </div>

        <div class="modalBottomSection">
          <button type = "submit" id="submitBtn" class="bottomButtons">Update Transaction</button>
          <button type="button" class="bottomButtons" id="cancelModal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="../assets/js/monthly_expenses.js"></script>
