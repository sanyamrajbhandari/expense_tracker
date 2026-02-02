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

// Data is now pre-escaped on the server using e() (htmlspecialchars)

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

if (openBtn) {
  openBtn.addEventListener("click", () => {
    modal.classList.add("show");
  });
}

if (closeBtn) {
  closeBtn.addEventListener("click", () => {
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
  // Only load dashboard if we are on the dashboard page (check for a specific element)
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

  // If there are more than 5, show "View All" card
  if (wallets.length > 5) {
      container.innerHTML += `
        <div class="card wallet view-all-card" onclick="window.location.href='api/wallets.php'" style="cursor: pointer; display: flex; align-items: center; justify-content: center; background: #eef2ff; color: #4f46e5;">
            <div style="text-align: center;">
                <i class="fas fa-arrow-right" style="font-size: 24px; margin-bottom: 8px;"></i>
                <p style="font-weight: 600;">View All</p>
            </div>
        </div>
      `;
  }
}

/* Helper for icons (Standardized) */
function getIcon(cat) {
    const c = cat ? cat.toLowerCase() : "";
    if (c === "dining") return ["fa-utensils", "icon-orange"];
    if (c === "groceries") return ["fa-shopping-basket", "icon-green"]; // Changed to green for groceries
    if (c === "shopping") return ["fa-shopping-bag", "icon-purple"];
    if (c === "transit") return ["fa-bus", "icon-blue"];
    if (c === "entertainment") return ["fa-film", "icon-red"];
    if (c.includes("bill") || c === "fees") return ["fa-file-invoice-dollar", "icon-gray"]; // Bills & Fees
    if (c === "gifts") return ["fa-gift", "icon-purple"];
    if (c === "beauty") return ["fa-spa", "icon-red"];
    if (c === "work") return ["fa-briefcase", "icon-blue"];
    if (c === "travel") return ["fa-plane", "icon-blue"];
    return ["fa-tag", "icon-gray"];
}

function formatTime(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
      <a href="api/monthly_expenses.php" class="btn-primary" style="text-decoration: none; display: inline-block;">
        <i class="fas fa-list-ul"></i> View All Transactions
      </a>
    </div>
  `;
}

//To add transaction
const transactionForm = document.getElementById("transactionForm");

if (transactionForm) {
  transactionForm.addEventListener("submit", function (e) {
    e.preventDefault(); // stop page reload

    const formData = new FormData(transactionForm);

    fetch("api/actions/add_transaction.php", {
      method: "POST",
      headers: {
          "X-CSRF-TOKEN": formData.get("csrf_token")
      },
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
}

/* ============================
   Edit / Delete Functionality (Dashboard)
   ============================ */

const editModal = document.getElementById("editTransactionModal");
const closeEditBtn = document.getElementById("closeEditModal");
const editForm = document.getElementById("editTransactionForm");

if(editModal && closeEditBtn) {
    closeEditBtn.addEventListener("click", () => editModal.classList.remove("show"));
    
    window.addEventListener("click", (e) => {
        if (e.target === editModal) editModal.classList.remove("show");
    });
}

// Global function to be called from inline onclick
window.editTransaction = function(id) {
    // 1. Fetch transaction details AND all wallets
    Promise.all([
        fetch(`api/fetch/get_transaction.php?id=${id}`).then(res => res.json()),
        fetch(`api/fetch/fetch_wallets.php`).then(res => res.json())
    ])
    .then(([txnData, walletData]) => {
        if(txnData.success && walletData.success) {
            const t = txnData.transaction;
            
            // Populate wallets dropdown
            const walletSelect = document.getElementById("editWallet");
            if(walletSelect) {
                walletSelect.innerHTML = "";
                walletData.wallets.forEach(w => {
                    const opt = document.createElement("option");
                    opt.value = w.id;
                    opt.textContent = w.name;
                    walletSelect.appendChild(opt);
                });
                walletSelect.value = t.wallet_id;
            }

            const editId = document.getElementById("editId");
            const editTitle = document.getElementById("editTitle");
            const editAmount = document.getElementById("editAmount");
            const editCategory = document.getElementById("editCategory");
            const txt = document.createElement("textarea");
            
            if(editId) editId.value = t.id;
            
            if(editTitle) {
                txt.innerHTML = t.title;
                editTitle.value = txt.value;
            }
            
            if(editAmount) editAmount.value = t.amount;
            
            if(editCategory) {
                txt.innerHTML = t.category || "Dining";
                editCategory.value = txt.value;
            }
            
            if(editModal) editModal.classList.add("show");
        } else {
            alert("Error fetching data");
        }
    })
    .catch(err => {
        console.error(err);
        alert("Network error");
    });
};

window.deleteTransaction = function(id) {
    if(!confirm("Are you sure you want to delete this transaction?")) return;

    fetch("api/actions/delete_transaction.php", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('input[name="csrf_token"]').value
        },
        body: JSON.stringify({id: id})
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
             location.reload(); 
        } else {
            alert(data.error || "Failed to delete");
        }
    });
};

if(editForm) {
    editForm.onsubmit = (e) => {
        e.preventDefault();
        const formData = new FormData(editForm);
        // Convert to JSON object
        const data = Object.fromEntries(formData.entries());

        fetch("api/actions/update_transaction.php", {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": data.csrf_token
            },
            body: JSON.stringify(data)
        })
        .then(res => res.json())
        .then(resData => {
            if(resData.success) {
                editModal.classList.remove("show");
                location.reload();
            } else {
                alert(resData.error || "Update failed");
            }
        });
    };
}
