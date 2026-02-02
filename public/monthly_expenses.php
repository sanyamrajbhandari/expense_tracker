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
         <input type="text" id="searchInput" placeholder="Filter list...">
       </div>
     </div>
   </div>
  <div id="transactionList"></div>
  </section>

</main>

<!-- Edit Transaction Modal -->
<div class="overlay" id="editTransactionModal">
  <div class="formModal">
    <div class="modalTopSection">
      <p>Edit Transaction</p>
      <button id="closeEditModal"><i class="fas fa-times"></i></button>
    </div>
    <form id="editTransactionForm">
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
      </div>
      <div class="modalBottomSection">
        <button type="submit" class="bottomButtons">Update Transaction</button>
      </div>
    </form>
  </div>
</div>

<script src="../assets/js/monthly_expenses.js"></script>
