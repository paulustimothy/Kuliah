let cart = JSON.parse(localStorage.getItem("cart")) || [];
const cartContainer = document.querySelector(".cart-items");
const cartSummary = document.getElementById("cartSummary");

function updateCartCount() {
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

function updateQuantity(index, change) {
  const newQuantity = cart[index].quantity + change;
  if (newQuantity <= 0) {
    removeFromCart(index);
    return;
  }

  cart[index].quantity = newQuantity;
  localStorage.setItem("cart", JSON.stringify(cart));
  tampilCart();
}

function tampilCart() {
  if (!cartContainer) return;

  cartContainer.innerHTML = "";
  let total = 0;

  if (cart.length === 0) {
    cartContainer.innerHTML = `
      <div class="empty-cart">
        <i class="fas fa-shopping-cart" style="font-size: 3rem; margin-bottom: 1rem; color: #dee2e6;"></i>
        <p>Keranjang Anda kosong</p>
        <a href="index.html" class="back-link" style="margin-top: 1rem; display: inline-block;">
          <i class="fas fa-arrow-left"></i> Mulai Belanja
        </a>
      </div>
    `;
    if (cartSummary) {
      cartSummary.innerHTML = "";
    }
    return;
  }

  cart.forEach((item, index) => {
    const itemTotal = item.harga * item.quantity;
    total += itemTotal;

    cartContainer.innerHTML += `
      <div class="cart-item">
        <img src="${item.gambar_produk}"/>
        <h3>${item.nama}</h3>
        <p class="harga">Rp ${item.harga.toLocaleString("id-ID")}</p>
        <div class="quantity-controls">
          <button onclick="updateQuantity(${index}, -1)" class="qty-btn">-</button>
          <span class="quantity">${item.quantity}</span>
          <button onclick="updateQuantity(${index}, 1)" class="qty-btn">+</button>
        </div>
        <p class="subtotal">Subtotal: Rp ${itemTotal.toLocaleString(
          "id-ID"
        )}</p>
        <button onclick="removeFromCart(${index})" class="remove-btn">
          <i class="fas fa-trash"></i> Hapus
        </button>
      </div>
    `;
  });

  if (cartSummary) {
    cartSummary.innerHTML = `
      <div class="total">
        <div class="total-items">Total Item: ${cart.length}</div>
        <div class="total-price">Total Harga: Rp ${total.toLocaleString(
          "id-ID"
        )}</div>
        <a href="checkout.html"><button class="checkout-btn">
        <i class="fas fa-credit-card"></i> Checkout
      </button></a>
        </div>
    `;
  }
}

function removeFromCart(index) {
  const itemName = cart[index].nama;
  if (
    confirm(`Apakah Anda yakin ingin menghapus ${itemName} dari keranjang?`)
  ) {
    cart.splice(index, 1);
    localStorage.setItem("cart", JSON.stringify(cart));
    updateCartCount();
    tampilCart();
    alert(`${itemName} telah dihapus dari keranjang`);
  }
}

updateCartCount();
tampilCart();
