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

<script src="../assets/js/monthly_expenses.js"></script>
