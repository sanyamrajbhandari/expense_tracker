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

  <h2 id="monthTitle" class="month-heading">Loading...</h2>
  <div id="transactionList"></div>
  </section>

</main>

<script src="../assets/js/monthly_expenses.js"></script>
