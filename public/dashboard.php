<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require "../config/db.php";
include "../includes/header.php";

if(!$_SESSION['user_name']){
  header("Location: login.php");
  exit;
}

$username = $_SESSION['user_name'];
$userId = $_SESSION['user_id'];
// To set the timezone to Kathmandu from UTC and show current date and time
date_default_timezone_set('Asia/Kathmandu');


// To fetch the wallets
try{
  $getWallets = "SELECT * FROM wallets WHERE user_id = ?";
  $stmt = $conn->prepare($getWallets);
  $stmt->execute([$userId]);
  $wallets= $stmt->fetchAll();

}catch(Exception $e){
  echo "An error occured while trying to fetch wallets " . $e-> getMessage();
}

if($_SERVER['REQUEST_METHOD']== "POST"){
    $type = $_POST['type'];
    $title = trim($_POST['title']??"");
    $amount = trim($_POST['amount']??"");
    $category = $_POST['category'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $account = $_POST['wallet'];
    $errorMessage = [];

    if(empty($title)){
      $errorMessage['title'] = "Please state the tile of the expense";
    }

    if(empty($amount)){
      $errorMessage['amount'] = "Please enter the amount of the expense";
    }

    if($amount<0){
      $errorMessage['amount'] = "Please enter positive amount.";
    }

    if(empty($errorMessage)){
      $transactionQuery = "INSERT INTO transactions (user_id, wallet_id, type, title, category, amount, transaction_datetime) VALUES (?,?,?,?,?,?,?) ";
      $stmt = $conn->prepare($transactionQuery);
      $stmt->execute([$userId,$account,$type,$title,$category,$amount,$date . ' ' . $time. ':00']);
    }




}



?>

<!-- Main content -->
  <main class="container">
    <div class="header-row">
      <div>
        <h1>Welcome back, <?= htmlspecialchars($username) ?></h1>
        <p class="subtitle">Here is your financial snapshot for today.</p>
      </div>

      <button class="btn-primary">+ Add Transaction</button>
    </div>

   <!-- Wallets and networth -->
    <section class="cards-row">

      <!-- Net Worth Card -->
      <div class="card net-worth">
        <p class="label">Total Net Worth</p>
        <h2>$45,230.50</h2>
      </div>

      <!-- Wallets -->
      <div class="card wallet">
        <p class="label">Cash</p>
        <h3>$450.00</h3>
      </div>

      <div class="card wallet">
        <p class="label">Bank Account</p>
        <h3>$12,300.00</h3>
      </div>

      <div class="card wallet">
        <p class="label">Savings</p>
        <h3>$32,480.50</h3>
      </div>

    </section>

    <!-- Overlay for entry of transaction -->
    <div class="overlay">
      <div class="formModal">

        <div class="modalTopSection">
          <p>Add a transaction</p>
          <button class="closeModal">&times;</button>
        </div>

        <div class="modalMiddleSection">
          <form method = "POST">

          <!-- Div for the radio buttons for income or expense selection -->
          <div class="transactionType">
            <label>
              <input type="radio" name="type" value="expense" checked>
              Expense
            </label>

            <label>
              <input type="radio" name="type" value="income">
              Income
            </label>
          </div>

          <!-- The title of the expense -->
          <div class="formGroup">
            <label for="title">Title</label>
            <p style="color:red"><?= $errorMessage['title']?? "" ?></p>
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
            <p style="color:red"><?= $errorMessage['amount']??"" ?> </p>
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
            <label for="wallet">Wallet/Amount</label>
            <select name="wallet" required>
              <?php foreach($wallets as $wallet): ?>
                <option value = <?= $wallet['id'] ?>> <?= $wallet['name'] ?> </option>
              <?php endforeach ?>
            </select>
          </div>
        </div>

        <div class="modalBottomSection">
          <button type = "submit" class="bottomButtons">Save transaction</button>
          <button class="bottomButtons">Cancel</button>
        </div>
              </form>
      
      </div>
    </div>


    <!-- Recent transactions -->
    <section class="transactions">
      <h2>Recent Transactions</h2>

      <div class="transaction">
        <div>
          <strong>Weekly Grocery Run</strong>
          <p>Bank Account</p>
        </div>
        <span class="amount negative">- $120.50</span>
      </div>

      <div class="transaction">
        <div>
          <strong>Monthly Salary</strong>
          <p>Bank Account</p>
        </div>
        <span class="amount positive">+ $3,500.00</span>
      </div>

      <div class="transaction">
        <div>
          <strong>Sushi Dinner Night</strong>
          <p>Cash</p>
        </div>
        <span class="amount negative">- $45.00</span>
      </div>
    </section>

  </main>

<?php
    include "../includes/footer.php";
?>