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

if (categorySelect) {
  categories.forEach((category) => {
    const option = document.createElement("option");

    option.value = category;
    option.textContent = category;

    categorySelect.appendChild(option);
  });
}

const modal = document.getElementById("overlay");
const openBtn = document.getElementById("addTransactionBtn");
const closeBtn = document.getElementById("closeModal");
const cancelBtn = document.getElementById("cancelModal");
const modalTitle = document.getElementById("modalTitle");
const submitBtn = document.getElementById("submitBtn");
const transactionForm = document.getElementById("transactionForm");
const transactionIdInput = document.getElementById("transactionId");

if (openBtn) {
  openBtn.addEventListener("click", () => {
    // Resetting form for "Add" mode
    transactionForm.reset();
    transactionIdInput.value = "";
    
    // Setting default date and time
    const now = new Date();
    const dateInput = document.getElementById("date");
    const timeInput = document.getElementById("time");
    if (dateInput) {
      dateInput.value = now.toISOString().split('T')[0];
    }
    if (timeInput) {
      timeInput.value = now.toTimeString().substring(0, 5);
    }

    modalTitle.textContent = "Add a transaction";
    submitBtn.textContent = "Save transaction";
    modal.classList.add("show");
  });
}

if (closeBtn) {
  closeBtn.addEventListener("click", () => {
    modal.classList.remove("show");
  });
}

if (cancelBtn) {
  cancelBtn.addEventListener("click", () => {
    modal.classList.remove("show");
  });
}

//to close the model when the user clicks outside the form box
if (modal) {
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
}

// AJAX part
function renderWalletOptions(wallets) {
  const select = document.getElementById("walletSelect");
  if (!select) return; // Add check
  select.innerHTML = '<option value="">Select wallet</option>';

  wallets.forEach((wallet) => {
    const option = document.createElement("option");
    option.value = wallet.id;
    option.textContent = wallet.name;
    select.appendChild(option);
  });
}

document.addEventListener("DOMContentLoaded", () => {
  // Only load dashboard if we are on the dashboard page
  if (document.getElementById("netWorth")) {
    loadDashboard();
  }
});

function loadDashboard() {
  fetch("api/fetch/fetch_dashboard.php")
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return;

      renderWallets(data.wallets);
      renderWalletOptions(data.wallets);
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

  // Limit to 5 wallets
  const walletsToShow = wallets.slice(0, 5);

  walletsToShow.forEach((wallet) => {
    container.innerHTML += `
      <div class="card wallet">
        <p class="label">${wallet.name}</p>
        <h3><span>Rs. </span>${wallet.balance}</h3>
      </div>
    `;
  });

  // If there are more than 5, showing "View all card"
  if (wallets.length > 5) {
    container.innerHTML += `
        <div class="card wallet view-all-card" onclick="window.location.href='wallets.php'" style="cursor: pointer; display: flex; align-items: center; justify-content: center; background: #eef2ff; color: #4f46e5;">
            <div style="text-align: center;">
                <i class="fas fa-arrow-right" style="font-size: 24px; margin-bottom: 8px;"></i>
                <p style="font-weight: 600;">View All</p>
            </div>
        </div>
      `;
  }
}

// Helper function for icons
function getIcon(cat) {
  const c = cat ? cat.toLowerCase() : "";
  if (c === "dining") return ["fa-utensils", "icon-orange"];
  if (c === "groceries") return ["fa-shopping-basket", "icon-green"]; // Changed to green for groceries
  if (c === "shopping") return ["fa-shopping-bag", "icon-purple"];
  if (c === "transit") return ["fa-bus", "icon-blue"];
  if (c === "entertainment") return ["fa-film", "icon-red"];
  if (c.includes("bill") || c === "fees")
    return ["fa-file-invoice-dollar", "icon-gray"]; // Bills & Fees
  if (c === "gifts") return ["fa-gift", "icon-purple"];
  if (c === "beauty") return ["fa-spa", "icon-red"];
  if (c === "work") return ["fa-briefcase", "icon-blue"];
  if (c === "travel") return ["fa-plane", "icon-blue"];
  return ["fa-tag", "icon-gray"];
}

function formatTime(dateStr) {
  const d = new Date(dateStr);
  return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function renderTransactions(grouped) {
  const section = document.querySelector(".transactions");
  section.innerHTML = "<h2>Recent Transactions</h2>";

  for (const date in grouped) {
    section.innerHTML += `
            <h3 class="date-title">${new Date(date).toDateString()}</h3>
        `;

    grouped[date].forEach((txn) => {
      const [icon, colorClass] = getIcon(txn.category);

      section.innerHTML += `
            <div class="transaction">
                <div class="txn-left-content">
                    <div class="txn-icon-circle ${colorClass}">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="txn-details">
                        <strong>${txn.title}</strong>
                        <div class="txn-meta">
                            <span>${formatTime(txn.transaction_datetime)}</span>
                            <span>•</span>
                            <span>${txn.wallet_name}</span>
                        </div>
                    </div>
                </div>
                <div class="txn-right-content">
                    <span class="txn-amount ${txn.type === "expense" ? "negative" : "positive"}">
                        ${txn.type === "expense" ? "-" : "+"} Rs. ${parseFloat(txn.amount).toFixed(2)}
                    </span>
                    <span class="txn-tag">${txn.type}</span>
                    <div class="txn-actions" style="margin-top:4px;">
                        <i class="fas fa-pen" style="cursor:pointer; color:#64748b; margin-right:8px; font-size:12px;" onclick="editTransaction(${txn.id})"></i>
                        <i class="fas fa-trash" style="cursor:pointer; color:#ef4444; font-size:12px;" onclick="deleteTransaction(${txn.id})"></i>
                    </div>
                </div>
            </div>
            `;
    });
  }

  // Add "View All" button at the bottom
  section.innerHTML += `
    <div style="margin-top: 20px; text-align: center;">
      <a href="monthly_expenses.php" class="btn-primary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-list-ul"></i> View All Transactions
      </a>
    </div>
  `;
}

//To add/edit transaction
if (transactionForm) {
  transactionForm.addEventListener("submit", function (e) {
    e.preventDefault(); // stop page reload

    const formData = new FormData(transactionForm);
    const id = transactionIdInput.value;
    const url = id ? "api/actions/update_transaction.php" : "api/actions/add_transaction.php";
    if(id) {
        formData.append('wallet_id', formData.get('wallet'));
    }

    fetch(url, {
      method: "POST",
      headers: {
        "X-CSRF-TOKEN": formData.get("csrf_token"),
      },
      body: formData,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.success) {
          modal.classList.remove("show");
          loadDashboard(); // refresh wallets + transactions
        } else {
          alert(data.message || data.error || "Something went wrong");
        }
      })
      .catch((err) => {
        console.error(err);
        alert("Something went wrong");
      });
  });
}

// function to use for onclick edit option
window.editTransaction = function (id) {
  // Fetching transaction details AND all wallets
  Promise.all([
    fetch(`api/fetch/get_transaction.php?id=${id}`).then((res) => res.json()),
    fetch(`api/fetch/fetch_wallets.php`).then((res) => res.json()),
  ])
    .then(([txnData, walletData]) => {
      if (txnData.success && walletData.success) {
        const t = txnData.transaction;

        // Reset form first
        transactionForm.reset();

        // Populating wallets dropdown
        const walletSelect = document.getElementById("walletSelect");
        if (walletSelect) {
          walletSelect.innerHTML = '<option value="">Select wallet</option>';
          walletData.wallets.forEach((w) => {
            const opt = document.createElement("option");
            opt.value = w.id;
            opt.textContent = w.name;
            walletSelect.appendChild(opt);
          });
          walletSelect.value = t.wallet_id;
        }

        document.getElementById("transactionId").value = t.id;
        document.getElementById("title").value = t.title;
        document.getElementById("amount").value = t.amount;
        document.getElementById("categoryDropdown").value = t.category || "Dining";
        
        // Handling date and time splitting
        const dateInput = document.getElementById("date");
        const timeInput = document.getElementById("time");
        
        if (dateInput && t.transaction_datetime) {
            // Usually DB returns YYYY-MM-DD HH:MM:SS
            const [dateStr, timeStr] = t.transaction_datetime.split(' ');
            dateInput.value = dateStr;
            if (timeInput && timeStr) {
                timeInput.value = timeStr.substring(0, 5); // HH:mm
            }
        }

        // Setting type radio
        if (t.type === 'expense') {
            document.getElementById("typeExpense").checked = true;
        } else {
            document.getElementById("typeIncome").checked = true;
        }

        modalTitle.textContent = "Edit Transaction";
        submitBtn.textContent = "Update Transaction";
        modal.classList.add("show");
      } else {
        alert("Error fetching data");
      }
    })
    .catch((err) => {
      console.error(err);
      alert("Network error");
    });
};

window.deleteTransaction = function (id) {
  if (!confirm("Are you sure you want to delete this transaction?")) return;

  fetch("api/actions/delete_transaction.php", {
    method: "POST",
    headers: {
      "X-CSRF-TOKEN": document.querySelector('input[name="csrf_token"]').value,
    },
    body: JSON.stringify({ id: id }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        loadDashboard();
      } else {
        alert(data.error || "Failed to delete");
      }
    });
};

