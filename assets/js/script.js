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
