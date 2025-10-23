let cart = JSON.parse(localStorage.getItem("cart")) || [];

function updateCartCount() {
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

function calculateTotal() {
  let total = 0;
  cart.forEach((item) => {
    total += item.harga * item.quantity;
  });
  return total;
}

function displayTotal() {
  const totalAmount = document.getElementById("totalAmount");
  const checkoutContainer = document.querySelector(".checkout-container");

  if (cart.length === 0) {
    checkoutContainer.innerHTML = `
      <div class="empty-cart">
        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; color: #dee2e6;"></i>
        <p>Keranjang Anda kosong</p>
        <a href="index.html" class="back-link" style="margin-top: 1rem; display: inline-block;">
          <i class="fas fa-arrow-left"></i> Mulai Belanja
        </a>
      </div>
    `;
    return;
  }

  const total = calculateTotal();
  totalAmount.textContent = `Rp ${total.toLocaleString("id-ID")}`;
}

function processPayment(event) {
  event.preventDefault();

  const amountInput = document.getElementById("amount");
  const amount = parseFloat(amountInput.value);
  const total = calculateTotal();

  if (!amount || amount <= 0) {
    showResponse("error", "Masukkan jumlah pembayaran yang valid!");
    return;
  }

  if (amount < total) {
    showResponse(
      "error",
      `Jumlah pembayaran kurang! Kurang Rp ${(total - amount).toLocaleString(
        "id-ID"
      )}`
    );
    return;
  }

  if (amount > total) {
    const change = amount - total;
    showResponse(
      "success",
      `Pembayaran berhasil! Kembalian: Rp ${change.toLocaleString("id-ID")}`
    );
  } else {
    showResponse("success", "Pembayaran berhasil! Terima kasih.");
  }

  localStorage.removeItem("cart");
  cart = [];
  updateCartCount();
}

function showResponse(type, message) {
  const responseDiv = document.getElementById("response");
  const responseContent = document.getElementById("responseContent");

  responseContent.innerHTML = `
    <div class="response-message ${type}">
      <i class="fas fa-${
        type === "success" ? "check-circle" : "exclamation-circle"
      }"></i>
      <span>${message}</span>
    </div>
  `;

  responseDiv.style.display = "block";

  responseDiv.scrollIntoView({ behavior: "smooth" });
}

updateCartCount();
displayTotal();
