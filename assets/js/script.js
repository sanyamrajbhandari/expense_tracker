// The expense/ income categories
const categories = [
  "Dining",
  "Groceries",
  "Shopping",
  "Transit",
  "Entertainment",
  "Bills & Fees",
  "Gifts",
  "Beauty",
  "Work",
  "Travel",
];

// Selecting the category select element to add categories
const categorySelect = document.getElementById("categoryDropdown");

categories.forEach((category) => {
  const option = document.createElement("option");

  option.value = category;
  option.textContent = category;

  categorySelect.appendChild(option);
});

const modal = document.getElementById("overlay");
if (modal !== null) {
  console.log("Element exists and is selected");
} else {
  console.log("Element NOT found");
}
const openBtn = document.getElementById("addTransactionBtn");
const closeBtn = document.getElementById("closeModal");

openBtn.addEventListener("click", () => {
  console.log("I am being triggered!");
  modal.classList.add("show");
});

closeBtn.addEventListener("click", () => {
  modal.classList.remove("show");
});

//to close the model when the user clicks outside the form box
modal.addEventListener("click", (e) => {
  if (e.target === modal) {
    modal.classList.remove("show");
  }
});

// To close the model on ESC key
document.addEventListener("keydown", (e) => {
  if (e.key === "Escape") {
    modal.classList.remove("show");
  }
});

// AJAX part

document.addEventListener("DOMContentLoaded", () => {
  loadDashboard();
});

function loadDashboard() {
  fetch("../public/fetch_dashboard.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return;

      renderWallets(data.wallets);
      renderTransactions(data.transactions);
      updateNetWorth(data.wallets);
    });
}

function updateNetWorth(wallets) {
  let total = 0;
  wallets.forEach((w) => (total += Number(w.balance)));
  document.getElementById("netWorth").textContent = total;
}

function renderWallets(wallets) {
  const container = document.getElementById("walletCards");
  container.innerHTML = "";

  wallets.forEach((wallet) => {
    container.innerHTML += `
            <div class="card wallet">
                <p class="label">${wallet.name}</p>
                <h3><span>Rs. </span>${wallet.balance}</h3>
            </div>
        `;
  });
}

function renderTransactions(grouped) {
  const section = document.getElementById("transactionsSection");
  section.innerHTML = "<h2>Recent Transactions</h2>";

  for (const date in grouped) {
    section.innerHTML += `
            <h3 class="date-title">${new Date(date).toDateString()}</h3>
        `;

    grouped[date].forEach((txn) => {
      section.innerHTML += `
                <div class="transaction">
                    <div>
                        <strong>${txn.title}</strong>
                        <p>${txn.wallet_name}</p>
                    </div>
                    <span class="amount ${txn.type === "expense" ? "negative" : "positive"}">
                        ${txn.type === "expense" ? "-" : "+"} ${txn.amount}
                    </span>
                </div>
            `;
    });
  }
}

//To add transaction
const transactionForm = document.getElementById("transactionForm");

transactionForm.addEventListener("submit", function (e) {
  e.preventDefault(); // stop page reload

  const formData = new FormData(transactionForm);

  fetch("../public/add_transaction.php", {
    method: "POST",
    body: formData,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        // THIS is the interaction point
        modal.classList.remove("show");
        loadDashboard(); // refresh wallets + transactions
      } else {
        alert(data.message);
      }
    })
    .catch(() => {
      alert("Something went wrong");
    });
});
