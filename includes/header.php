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
</head>
<body>
    <header class="navbar">
    <div class="logo">PaisaKhai</div>

    <nav class="nav-links">
  <a href="../public/dashboard.php"
     class="<?= ($currentPage === 'overview') ? 'active' : '' ?>">
     Overview
  </a>

  <a href="../public/monthly_expenses.php"
     class="<?= ($currentPage === 'transactions') ? 'active' : '' ?>">
     Transactions
  </a>

  <a href="#"
     class="<?= ($currentPage === 'wallets') ? 'active' : '' ?>">
     Wallets
  </a>
</nav>


    <div class="profile">
      <span class="name"><?= htmlspecialchars($username) ?></span>
    </div>
  </header>

  
