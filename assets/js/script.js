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
  console.log("BRUDDA");

  option.value = category;
  option.textContent = category;

  categorySelect.appendChild(option);
});
