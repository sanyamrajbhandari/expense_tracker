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

function renderTransactions(grouped, month) {
  const list = document.getElementById("transactionList");
  const title = document.getElementById("monthTitle");

  title.textContent = formatMonth(month || Object.keys(grouped)[0]);
  list.innerHTML = "";

  for (const date in grouped) {
    list.innerHTML += `<h3>${new Date(date).toDateString()}</h3>`;

    grouped[date].forEach((txn) => {
      list.innerHTML += `
        <div class="transaction">
          <div>
            <strong>${txn.title}</strong>
            <p>${txn.wallet_name}</p>
          </div>
          <span class="${txn.type === "expense" ? "negative" : "positive"}">
            ${txn.type === "expense" ? "-" : "+"} Rs. ${txn.amount}
          </span>
        </div>
      `;
    });
  }
}

function formatMonth(month) {
  const [y, m] = month.split("-");
  return new Date(y, m - 1).toLocaleString("default", {
    month: "long",
    year: "numeric",
  });
}
