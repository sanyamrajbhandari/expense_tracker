document.addEventListener("DOMContentLoaded", () => {
    loadWallets();
    setupModal();
});

function loadWallets(walletId = null) {
    const url = walletId 
        ? `../public/fetch_wallets.php?id=${walletId}` 
        : `../public/fetch_wallets.php`;

    fetch(url)
        .then((res) => res.json())
        .then((data) => {
            if (!data.success) {
                console.error("Failed to fetch wallets:", data.error);
                return;
            }

            // If we requested a specific ID but got no selected wallet, it might be invalid
            // In that case, just render whatever we have.
            renderWalletsList(data.wallets, data.selected_wallet);
            renderWalletDetails(data.selected_wallet, data.transactions);
        })
        .catch(console.error);
}

function renderWalletsList(wallets, selectedWallet) {
    const container = document.getElementById("walletsPageGrid");
    if (!container) return;
    container.innerHTML = "";

    if (wallets.length === 0) {
        container.innerHTML = "<p>No wallets found.</p>";
        return;
    }

    wallets.forEach((wallet) => {
        const card = document.createElement("div");
        // Ensure type consistency for comparison
        const isSelected = selectedWallet && String(wallet.id) === String(selectedWallet.id);
        
        card.className = `wallet-card ${isSelected ? 'selected' : ''}`;

        // Determine icon based on name
        let iconClass = "fa-wallet"; // default
        const lowerName = wallet.name.toLowerCase();
        if (lowerName.includes("cash")) iconClass = "fa-money-bill-wave";
        else if (lowerName.includes("bank") || lowerName.includes("account")) iconClass = "fa-university";
        else if (lowerName.includes("card")) iconClass = "fa-credit-card";
        else if (lowerName.includes("savings")) iconClass = "fa-piggy-bank";

        card.innerHTML = `
        <div class="wallet-icon">
            <i class="fas ${iconClass}"></i>
        </div>
        <div class="wallet-info">
            <h3>${wallet.name}</h3>
            <p class="balance">Rs. ${parseFloat(wallet.balance).toFixed(2)}</p>
        </div>
        ${isSelected ? '<div class="check-icon"><i class="fas fa-check-circle"></i></div>' : ''}
        `;

        // Use closure to capture the correct ID
        card.onclick = () => {
             loadWallets(wallet.id);
        };
        
        container.appendChild(card);
    });
}

function renderWalletDetails(wallet, transactions) {
    const detailsContainer = document.getElementById("walletDetails");
    if (!detailsContainer) return;

    if (!wallet) {
        detailsContainer.innerHTML = `<div class="empty-state">Select a wallet to view details</div>`;
        return;
    }

    let iconClass = "fa-wallet";
    const lowerName = wallet.name.toLowerCase();
    if (lowerName.includes("cash")) iconClass = "fa-money-bill-wave";
    else if (lowerName.includes("bank") || lowerName.includes("account")) iconClass = "fa-university";
    else if (lowerName.includes("card")) iconClass = "fa-credit-card";
    else if (lowerName.includes("savings")) iconClass = "fa-piggy-bank";

    let transactionsHtml = "";
    if (!transactions || transactions.length === 0) {
        transactionsHtml = "<p>No recent transactions.</p>";
    } else {
        const grouped = {};
        transactions.forEach(txn => {
            const d = new Date(txn.transaction_datetime).toDateString();
            if (!grouped[d]) grouped[d] = [];
            grouped[d].push(txn);
        });

        for (const date in grouped) {
            transactionsHtml += `<h3 class="date-header">${date}</h3>`;
            grouped[date].forEach(txn => {
                transactionsHtml += `
                <div class="transaction-item">
                    <div class="txn-info">
                        <strong>${txn.title}</strong>
                        <span class="txn-type">${txn.type}</span>
                    </div>
                    <div class="txn-amount ${txn.type === 'expense' ? 'negative' : 'positive'}">
                        ${txn.type === 'expense' ? '-' : '+'} Rs. ${parseFloat(txn.amount).toFixed(2)}
                    </div>
                </div>
                `;
            });
        }
    }

    detailsContainer.innerHTML = `
      <div class="detail-header">
          <h2>Wallet Details</h2>
          <div class="wallet-actions">
            <button class="edit-btn" onclick="editWallet(${wallet.id}, '${wallet.name}', ${wallet.balance})"><i class="fas fa-pen"></i> Edit</button>
            <button class="delete-btn" onclick="deleteWallet(${wallet.id})"><i class="fas fa-trash"></i> Delete</button>
          </div>
      </div>
      
      <div class="selected-wallet-card">
          <div class="sw-icon"><i class="fas ${iconClass}"></i></div>
          <div class="sw-info">
              <h3>${wallet.name}</h3>
              <p>ID: ${wallet.id}</p>
          </div>
          <div class="sw-balance">
              <h3>Rs. ${parseFloat(wallet.balance).toFixed(2)}</h3>
              <span class="status">ACTIVE</span>
          </div>
      </div>
  
      <div class="transaction-history">
          <h3>Transaction History</h3>
          <div class="txn-list">
              ${transactionsHtml}
          </div>
      </div>
    `;
}

function setupModal() {
    // Add Wallet Modal
    const minModal = document.getElementById("addWalletModal");
    const openBtn = document.getElementById("addWalletBtn");
    const closeBtn = document.getElementById("closeWalletModal");
    const cancelBtn = document.getElementById("cancelWalletBtn");
    const form = document.getElementById("addWalletForm");

    if (minModal && openBtn) {
        const openModal = () => minModal.classList.add("show");
        const closeModal = () => minModal.classList.remove("show");

        openBtn.onclick = openModal;
        if(closeBtn) closeBtn.onclick = closeModal;
        if(cancelBtn) cancelBtn.onclick = closeModal;

        minModal.onclick = (e) => {
            if (e.target === minModal) closeModal();
        };

        if(form) {
            form.onsubmit = (e) => {
                e.preventDefault();
                const formData = new FormData(form);

                fetch("../public/add_wallet.php", {
                    method: "POST",
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        closeModal();
                        form.reset();
                        loadWallets(); 
                    } else {
                        alert("Error: " + data.error);
                    }
                })
                .catch(console.error);
            };
        }
    }

    // Edit Wallet Modal (New)
    const editWModal = document.getElementById("editWalletModal");
    const closeEditWBtn = document.getElementById("closeEditWalletModal");
    const editWForm = document.getElementById("editWalletForm");

    if(editWModal && closeEditWBtn) {
        closeEditWBtn.onclick = () => editWModal.classList.remove("show");
        editWModal.onclick = (e) => { if (e.target === editWModal) editWModal.classList.remove("show"); };
    }

    if(editWForm) {
        editWForm.onsubmit = (e) => {
            e.preventDefault();
            const formData = new FormData(editWForm);
            
            fetch("../public/update_wallet.php", {
                method: "POST",
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    editWModal.classList.remove("show");
                    loadWallets(); // Refresh all
                } else {
                    alert(data.error || "Update failed");
                }
            });
        };
    }
}

// Global functions for inline onclicks
window.deleteWallet = function(id) {
    if(!confirm("Are you sure you want to delete this wallet? All associated transactions will be deleted!")) return;

    fetch("../public/delete_wallet.php", {
        method: "POST",
        body: JSON.stringify({id: id})
    })
    .then(res => res.json())
    .then(data => {
        if(data.success) {
            // Reload page or wallets
            loadWallets(); 
        } else {
            alert(data.error || "Delete failed");
        }
    });
};

window.editWallet = function(id, name, balance) {
    const modal = document.getElementById("editWalletModal");
    if(modal) {
        document.getElementById("editWalletId").value = id;
        document.getElementById("editWalletName").value = name;
        document.getElementById("editWalletBalance").value = balance;
        modal.classList.add("show");
    }
};
