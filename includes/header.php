<?php

$username = $_SESSION['user_name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://kit.fontawesome.com/8acce1427f.js" crossorigin="anonymous"></script>
</head>
<body>
    <header class="navbar">
    <div class="logo">PaisaKhai</div>

    <nav class="nav-links">
  <a href="../public/dashboard.php"
     class="<?= ($currentPage === 'overview') ? 'active' : '' ?>">
     <i class="fas fa-chart-pie"></i> Overview
  </a>

  <a href="../public/monthly_expenses.php"
     class="<?= ($currentPage === 'transactions') ? 'active' : '' ?>">
     <i class="fas fa-list-ul"></i> Transactions
  </a>

  <a href="../public/wallets.php"
     class="<?= ($currentPage === 'wallets') ? 'active' : '' ?>">
     <i class="fas fa-wallet"></i> Wallets
  </a>
</nav>


    <div class="profile">
      <span class="name"><i class="fas fa-user-circle"></i> <?= htmlspecialchars($username) ?></span>
    </div>
  </header>

  
