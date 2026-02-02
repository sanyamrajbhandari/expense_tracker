document.addEventListener("DOMContentLoaded", () => {
  const now = new Date();
  const currentMonth =
    now.getFullYear() + "-" + String(now.getMonth() + 1).padStart(2, "0");

  loadMonth(currentMonth);
});

function loadMonth(month = null) {
  const url = month
    ? `../public/fetch_monthly_expenses.php?month=${month}`
    : `../public/fetch_monthly_expenses.php`;

  fetch(url)
    .then((res) => res.json())
    .then((data) => {
      if (!data.success) return;

      renderMonths(data.months, month);
      renderTransactions(data.transactions, month);
    });
}

function renderMonths(months, activeMonth) {
  const container = document.getElementById("monthList");
  container.innerHTML = "";

  months.forEach((month) => {
    const btn = document.createElement("button");
    btn.textContent = formatMonth(month);
    btn.className = month === activeMonth ? "active" : "";

    btn.onclick = () => loadMonth(month);
    container.appendChild(btn);
  });
}

/* Global store for current transactions to filter */
let allTransactions = [];

function renderTransactions(grouped, month) {
  // Store flattened transactions for search if this is the first render or update
  // But wait, 'grouped' is passed in. We might need to flatten it or just filter it.
  // Actually, let's store the original full grouped object or flat list if possible.
  // For simplicity, let's keep 'grouped' as the source of truth for display BUT we need 'allTransactions' for filtering.
  // Since 'grouped' is by date, it's hard to filter flatly.
  // Let's assume we receive 'data.transactions' from fetch which IS grouped.
  // We need to support filtering.
  
  // Update the title
  const title = document.getElementById("monthTitle");
  title.textContent = formatMonth(month || Object.keys(grouped)[0]);

  // Update count
  // Calculate total items
  let count = 0;
  for(let date in grouped) count += grouped[date].length;
  document.getElementById("totalItems").textContent = `${count} total items`;

  // Provide data for potential re-rendering if we implement local search
  // We will attach the search listener elsewhere, but we need the data access.
  // Let's store currentGroupedData globally for this module
  window.currentGroupedData = grouped; 
  
  renderGrouped(grouped);
}

function renderGrouped(grouped) {
    const list = document.getElementById("transactionList");
    list.innerHTML = "";

    if (Object.keys(grouped).length === 0) {
        list.innerHTML = "<p style='text-align:center; margin-top:30px; color:#94a3b8;'>No transactions found</p>";
        return;
    }

    // Helper for icons
    const getIcon = (cat) => {
        const c = cat ? cat.toLowerCase() : "";
        if (c === "dining") return ["fa-utensils", "icon-orange"];
        if (c === "groceries") return ["fa-shopping-basket", "icon-green"]; 
        if (c === "shopping") return ["fa-shopping-bag", "icon-purple"];
        if (c === "transit") return ["fa-bus", "icon-blue"];
        if (c === "entertainment") return ["fa-film", "icon-red"];
        if (c.includes("bill") || c === "fees") return ["fa-file-invoice-dollar", "icon-gray"];
        if (c === "gifts") return ["fa-gift", "icon-purple"];
        if (c === "beauty") return ["fa-spa", "icon-red"]; 
        if (c === "work") return ["fa-briefcase", "icon-blue"];
        if (c === "travel") return ["fa-plane", "icon-blue"];

        if(c.includes("food") || c.includes("dining")) return ["fa-utensils", "icon-orange"];
        if(c.includes("grocer")) return ["fa-shopping-basket", "icon-green"];
        if(c.includes("transit") || c.includes("uber") || c.includes("travel")) return ["fa-car", "icon-blue"];
        if(c.includes("shopping")) return ["fa-shopping-bag", "icon-purple"];
        if(c.includes("rent") || c.includes("bill")) return ["fa-home", "icon-green"];
        if(c.includes("coffee")) return ["fa-mug-hot", "icon-orange"];
        if(c.includes("tech") || c.includes("apple")) return ["fa-laptop", "icon-purple"];
        return ["fa-tag", "icon-gray"];
    };

    const formatTime = (dateStr) => {
        const d = new Date(dateStr);
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    };

    for (const date in grouped) {
        // Sort dates desc? keys might be unsorted. Usually backend sorts.
        
        list.innerHTML += `<h3>${new Date(date).toDateString()}</h3>`;

        grouped[date].forEach((txn) => {
            const [icon, colorClass] = getIcon(txn.category);
            
            list.innerHTML += `
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
}

function formatMonth(month) {
    if(!month) return "Loading...";
    const [y, m] = month.split("-");
    const date = new Date(y, m - 1);
    return date.toLocaleString("default", { month: "long", year: "numeric" });
}

// Search Logic
document.getElementById("searchInput").addEventListener("input", (e) => {
    const query = e.target.value.toLowerCase();
    const data = window.currentGroupedData || {};
    
    // Filter the grouped data
    const filtered = {};
    
    for (const date in data) {
        const matches = data[date].filter(txn => 
            txn.title.toLowerCase().includes(query)
        );
        if (matches.length > 0) {
            filtered[date] = matches;
        }
    }
    
    renderGrouped(filtered);
});

/* ============================
   Edit / Delete Functionality
   ============================ */

document.addEventListener("DOMContentLoaded", () => {
    const editModal = document.getElementById("editTransactionModal");
    const closeEditBtn = document.getElementById("closeEditModal");
    const editForm = document.getElementById("editTransactionForm");

    if (closeEditBtn && editModal) {
        closeEditBtn.onclick = () => editModal.classList.remove("show");
    }

    if (editModal) {
        window.addEventListener("click", (e) => {
            if (e.target === editModal) editModal.classList.remove("show");
        });
    }

    if (editForm) {
        editForm.onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(editForm);
            const data = Object.fromEntries(formData.entries());

            fetch("../public/update_transaction.php", {
                method: "POST",
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(resData => {
                if (resData.success) {
                    editModal.classList.remove("show");
                    location.reload();
                } else {
                    alert(resData.error || "Update failed");
                }
            })
            .catch(err => {
                console.error("Update failed:", err);
                alert("An error occurred during update");
            });
        };
    }
});

// Global function to be called from inline onclick
window.editTransaction = function(id) {
    const editModal = document.getElementById("editTransactionModal");
    if (!editModal) {
        console.error("Edit modal not found");
        return;
    }

    console.log("editTransaction called with ID:", id); // DEBUG
    if (!id) {
        alert("Invalid ID");
        return;
    }

    // 1. Fetch details
    fetch(`../public/get_transaction.php?id=${id}`)
    .then(res => res.json())
    .then(data => {
        console.log("Fetch result:", data); // DEBUG
        if (data.success) {
            const t = data.transaction;
            document.getElementById("editId").value = t.id;
            document.getElementById("editTitle").value = t.title;
            document.getElementById("editAmount").value = t.amount;
            document.getElementById("editCategory").value = t.category || "Dining";

            editModal.classList.add("show");
        } else {
            alert("Error fetching transaction: " + (data.error || "Unknown error"));
        }
    })
    .catch(err => {
        console.error("Fetch failed:", err);
        alert("Failed to fetch transaction details");
    });
};

window.deleteTransaction = function(id) {
    if (!confirm("Are you sure you want to delete this transaction?")) return;

    fetch("../public/delete_transaction.php", {
        method: "POST",
        body: JSON.stringify({ id: id })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.error || "Failed to delete");
        }
    })
    .catch(err => {
        console.error("Delete failed:", err);
        alert("An error occurred during deletion");
    });
};
