let products = [
  {
    id_product: 1,
    nama: "Kopi Golda",
    harga: 5000,
    stok: 200,
    gambar_produk: "images/kopigolda.jpeg",
  },
  {
    id_product: 2,
    nama: "Teh Pucuk",
    harga: 4000,
    stok: 300,
    gambar_produk: "images/tehpucuk.jpeg",
  },
  {
    id_product: 3,
    nama: "Susu Bendera",
    harga: 12000,
    stok: 25,
    gambar_produk: "images/susubendera.jpeg",
  },
  {
    id_product: 4,
    nama: "Air Mineral",
    harga: 4000,
    stok: 50,
    gambar_produk: "images/airmineral.jpeg",
  },
  {
    id_product: 5,
    nama: "Cimory",
    harga: 10000,
    stok: 150,
    gambar_produk: "images/cimory.jpeg",
  },
  {
    id_product: 6,
    nama: "Coca cola",
    harga: 9000,
    stok: 100,
    gambar_produk: "images/cocacola.png",
  },
  {
    id_product: 7,
    nama: "Milo",
    harga: 3000,
    stok: 120,
    gambar_produk: "images/milo.jpg",
  },
  {
    id_product: 8,
    nama: "You C 1000",
    harga: 8000,
    stok: 180,
    gambar_produk: "images/youc1000.png",
  },
];

let cardContainer = document.querySelector(".products-container");
let cart = JSON.parse(localStorage.getItem("cart")) || [];

function updateCartCount() {
  const cartCount = document.getElementById("cartCount");
  if (cartCount) {
    cartCount.textContent = cart.length;
  }
}

function renderProducts() {
  if (!cardContainer) return;

  let cardHtml = "";

  products.forEach((product) => {
    const isInCart = cart.some(
      (item) => item.id_product === product.id_product
    );

    cardHtml += `
      <div class="card">
        <img src="${product.gambar_produk}"/>
        <h3>${product.nama}</h3>
        <p class="harga">Rp ${product.harga.toLocaleString("id-ID")}</p>
        <p class="stok">Stok: ${product.stok}</p>
        <button 
          class="cart" 
          onclick="addToCart(${product.id_product})"
          ${isInCart ? "disabled" : ""}
        >
          ${isInCart ? "Sudah di Keranjang" : "Tambah ke Keranjang"}
        </button>
      </div>
    `;
  });

  cardContainer.innerHTML = cardHtml;
}

function addToCart(id_product) {
  const product = products.find((p) => p.id_product === id_product);
  if (!product) return;

  const existingItem = cart.find((item) => item.id_product === id_product);
  if (existingItem) {
    alert(`${product.nama} sudah ada di keranjang`);
    return;
  }

  cart.push({ ...product, quantity: 1 });
  localStorage.setItem("cart", JSON.stringify(cart));
  updateCartCount();
  renderProducts();
  alert(`${product.nama} berhasil ditambahkan ke keranjang`);
}

updateCartCount();
renderProducts();
