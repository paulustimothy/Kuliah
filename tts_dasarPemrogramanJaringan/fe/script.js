// Configuration
const API_BASE_URL = "http://localhost:5000/api";
const ITEMS_PER_PAGE = 10;

// Global state
let currentPage = 1;
let totalPages = 1;
let currentSearchQuery = "";
let books = [];
let currentBookId = null;

// DOM elements
const booksGrid = document.getElementById("booksGrid");
const loadingIndicator = document.getElementById("loadingIndicator");
const noBooksMessage = document.getElementById("noBooksMessage");
const pagination = document.getElementById("pagination");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const addBookBtn = document.getElementById("addBookBtn");
const addFirstBookBtn = document.getElementById("addFirstBookBtn");

// Modal elements
const bookModal = document.getElementById("bookModal");
const deleteModal = document.getElementById("deleteModal");
const bookForm = document.getElementById("bookForm");
const modalTitle = document.getElementById("modalTitle");
const closeModal = document.getElementById("closeModal");
const closeDeleteModal = document.getElementById("closeDeleteModal");
const cancelBtn = document.getElementById("cancelBtn");
const saveBtn = document.getElementById("saveBtn");
const cancelDeleteBtn = document.getElementById("cancelDeleteBtn");
const confirmDeleteBtn = document.getElementById("confirmDeleteBtn");
const deleteBookTitle = document.getElementById("deleteBookTitle");

// Toast container
const toastContainer = document.getElementById("toastContainer");

// Initialize the application
document.addEventListener("DOMContentLoaded", function () {
  initializeEventListeners();
  loadBooks();
});

// Event Listeners
function initializeEventListeners() {
  // Search functionalitys
  searchBtn.addEventListener("click", handleSearch);
  searchInput.addEventListener("keypress", function (e) {
    if (e.key === "Enter") {
      handleSearch();
    }
  });

  // Add book buttons
  addBookBtn.addEventListener("click", function () {
    openBookModal();
  });

  addFirstBookBtn.addEventListener("click", function () {
    openBookModal();
  });

  // Modal controls
  closeModal.addEventListener("click", closeBookModal);
  closeDeleteModal.addEventListener("click", closeDeleteModalFunc);
  cancelBtn.addEventListener("click", closeBookModal);
  cancelDeleteBtn.addEventListener("click", closeDeleteModalFunc);

  // Form submission
  bookForm.addEventListener("submit", handleFormSubmit);

  // Delete confirmation
  confirmDeleteBtn.addEventListener("click", handleDeleteConfirm);

  // Close modals when clicking outside
  bookModal.addEventListener("click", function (e) {
    if (e.target === bookModal) {
      closeBookModal();
    }
  });

  deleteModal.addEventListener("click", function (e) {
    if (e.target === deleteModal) {
      closeDeleteModalFunc();
    }
  });
}

// API Functions
async function fetchBooks(page = 1, searchQuery = "") {
  try {
    showLoading(true);

    let url = `${API_BASE_URL}/books?page=${page}&per_page=${ITEMS_PER_PAGE}`;
    if (searchQuery) {
      url += `&search=${encodeURIComponent(searchQuery)}`;
    }

    const response = await fetch(url);
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to fetch books");
    }

    return data.data;
  } catch (error) {
    console.error("Error fetching books:", error);
    showToast("Error loading books: " + error.message, "error");
    return null;
  } finally {
    showLoading(false);
  }
}

async function fetchBookById(id) {
  try {
    const response = await fetch(`${API_BASE_URL}/book/${id}`);
    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to fetch book");
    }

    return data.data;
  } catch (error) {
    console.error("Error fetching book:", error);
    showToast("Error loading book: " + error.message, "error");
    return null;
  }
}

async function createBook(bookData) {
  try {
    const response = await fetch(`${API_BASE_URL}/book`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(bookData),
    });

    const data = await response.json();

    if (!response.ok) {
      if (data.errors) {
        throw new Error(JSON.stringify(data.errors));
      }
      throw new Error(data.message || "Failed to create book");
    }

    return data.data;
  } catch (error) {
    console.error("Error creating book:", error);
    throw error;
  }
}

async function updateBook(id, bookData) {
  try {
    const response = await fetch(`${API_BASE_URL}/book/${id}`, {
      method: "PUT",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(bookData),
    });

    const data = await response.json();

    if (!response.ok) {
      if (data.errors) {
        throw new Error(JSON.stringify(data.errors));
      }
      throw new Error(data.message || "Failed to update book");
    }

    return data.data;
  } catch (error) {
    console.error("Error updating book:", error);
    throw error;
  }
}

async function deleteBook(id) {
  try {
    const response = await fetch(`${API_BASE_URL}/book/${id}`, {
      method: "DELETE",
    });

    const data = await response.json();

    if (!response.ok) {
      throw new Error(data.message || "Failed to delete book");
    }

    return true;
  } catch (error) {
    console.error("Error deleting book:", error);
    throw error;
  }
}

// UI Functions
function showLoading(show) {
  if (show) {
    loadingIndicator.classList.remove("hidden");
    booksGrid.classList.add("hidden");
    noBooksMessage.classList.add("hidden");
  } else {
    loadingIndicator.classList.add("hidden");
  }
}

function renderBooks(booksData) {
  books = booksData.bukus || [];
  totalPages = booksData.pages || 1;
  currentPage = booksData.current_page || 1;

  if (books.length === 0) {
    booksGrid.classList.add("hidden");
    noBooksMessage.classList.remove("hidden");
  } else {
    booksGrid.classList.remove("hidden");
    noBooksMessage.classList.add("hidden");

    booksGrid.innerHTML = books.map((book) => createBookCard(book)).join("");
  }

  renderPagination();
}

function createBookCard(book) {
  const publishedDate = book.published_date
    ? new Date(book.published_date).toLocaleDateString()
    : "N/A";
  const price = book.price ? `$${parseFloat(book.price).toFixed(2)}` : "N/A";
  const stock = book.stock !== null ? book.stock : "N/A";
  const genre = book.genre || "N/A";
  const description = book.description
    ? book.description.length > 100
      ? book.description.substring(0, 100) + "..."
      : book.description
    : "No description available";

  return `
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow">
            <div class="p-6">
                <!-- Header -->
                <div class="flex justify-between items-start mb-4">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 mb-1 line-clamp-2">${escapeHtml(
                          book.title
                        )}</h3>
                        <p class="text-sm text-gray-600">by ${escapeHtml(
                          book.author
                        )}</p>
                    </div>
                    <div class="flex gap-1 ml-2">
                        <button 
                            onclick="editBook(${book.id})" 
                            class="p-2 text-blue-400 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors"
                            title="Edit Book"
                        >
                            <i class="fas fa-edit text-sm"></i>
                        </button>
                        <button 
                            onclick="deleteBookConfirm(${
                              book.id
                            }, '${escapeHtml(book.title)}')" 
                            class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-md transition-colors"
                            title="Delete Book"
                        >
                            <i class="fas fa-trash text-sm"></i>
                        </button>
                    </div>
                </div>

                <!-- Details -->
                <div class="space-y-2 mb-4">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Published:</span>
                        <span class="text-gray-900 font-medium">${publishedDate}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Genre:</span>
                        <span class="text-gray-900 font-medium">${escapeHtml(
                          genre
                        )}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Price:</span>
                        <span class="text-gray-900 font-medium">${price}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Stock:</span>
                        <span class="text-gray-900 font-medium">${stock}</span>
                    </div>
                </div>

                <!-- Description -->
                <div class="text-sm text-gray-600 leading-relaxed">
                    ${escapeHtml(description)}
                </div>
            </div>
        </div>
    `;
}

function renderPagination() {
  if (totalPages <= 1) {
    pagination.innerHTML = "";
    return;
  }

  let paginationHTML = "";

  // Previous button
  paginationHTML += `
        <button 
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed ${
              currentPage === 1 ? "opacity-50 cursor-not-allowed" : ""
            }" 
            ${currentPage === 1 ? "disabled" : ""} 
            onclick="changePage(${currentPage - 1})"
        >
            <i class="fas fa-chevron-left"></i>
        </button>
    `;

  // Page numbers
  const startPage = Math.max(1, currentPage - 2);
  const endPage = Math.min(totalPages, currentPage + 2);

  if (startPage > 1) {
    paginationHTML += `
            <button 
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border-t border-b border-gray-300 hover:bg-gray-50" 
                onclick="changePage(1)"
            >
                1
            </button>
        `;
    if (startPage > 2) {
      paginationHTML += `<span class="px-3 py-2 text-sm text-gray-500 bg-white border-t border-b border-gray-300">...</span>`;
    }
  }

  for (let i = startPage; i <= endPage; i++) {
    paginationHTML += `
            <button 
                class="px-3 py-2 text-sm font-medium border-t border-b border-gray-300 hover:bg-gray-50 ${
                  i === currentPage
                    ? "text-blue-600 bg-blue-50 border-blue-300"
                    : "text-gray-500 bg-white"
                }" 
                onclick="changePage(${i})"
            >
                ${i}
            </button>
        `;
  }

  if (endPage < totalPages) {
    if (endPage < totalPages - 1) {
      paginationHTML += `<span class="px-3 py-2 text-sm text-gray-500 bg-white border-t border-b border-gray-300">...</span>`;
    }
    paginationHTML += `
            <button 
                class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border-t border-b border-gray-300 hover:bg-gray-50" 
                onclick="changePage(${totalPages})"
            >
                ${totalPages}
            </button>
        `;
  }

  // Next button
  paginationHTML += `
        <button 
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed ${
              currentPage === totalPages ? "opacity-50 cursor-not-allowed" : ""
            }" 
            ${currentPage === totalPages ? "disabled" : ""} 
            onclick="changePage(${currentPage + 1})"
        >
            <i class="fas fa-chevron-right"></i>
        </button>
    `;

  pagination.innerHTML = paginationHTML;
}

// Event Handlers
async function loadBooks() {
  const booksData = await fetchBooks(currentPage, currentSearchQuery);
  if (booksData) {
    renderBooks(booksData);
  }
}

function handleSearch() {
  currentSearchQuery = searchInput.value.trim();
  currentPage = 1;
  loadBooks();
}

function changePage(page) {
  if (page >= 1 && page <= totalPages && page !== currentPage) {
    currentPage = page;
    loadBooks();
  }
}

// Modal Functions
function openBookModal(bookId = null) {
  currentBookId = bookId;

  if (bookId) {
    modalTitle.textContent = "Edit Book";
    loadBookForEdit(bookId);
  } else {
    modalTitle.textContent = "Add New Book";
    resetForm();
  }

  bookModal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeBookModal() {
  bookModal.classList.add("hidden");
  document.body.style.overflow = "auto";
  resetForm();
  currentBookId = null;
}

function openDeleteModal(bookId, bookTitle) {
  currentBookId = bookId;
  deleteBookTitle.textContent = bookTitle;
  deleteModal.classList.remove("hidden");
  document.body.style.overflow = "hidden";
}

function closeDeleteModalFunc() {
  deleteModal.classList.add("hidden");
  document.body.style.overflow = "auto";
  currentBookId = null;
}

async function loadBookForEdit(bookId) {
  const book = await fetchBookById(bookId);
  if (book) {
    document.getElementById("title").value = book.title || "";
    document.getElementById("author").value = book.author || "";
    document.getElementById("publishedDate").value = book.published_date || "";
    document.getElementById("genre").value = book.genre || "";
    document.getElementById("price").value = book.price || "";
    document.getElementById("stock").value = book.stock || "";
    document.getElementById("description").value = book.description || "";
  }
}

function resetForm() {
  bookForm.reset();
  clearFormErrors();
}

function clearFormErrors() {
  const errorElements = document.querySelectorAll('[id$="Error"]');
  errorElements.forEach((element) => {
    element.classList.add("hidden");
    element.textContent = "";
  });

  const inputs = document.querySelectorAll("input, textarea");
  inputs.forEach((input) => {
    input.classList.remove(
      "border-red-300",
      "focus:border-red-500",
      "focus:ring-red-500"
    );
    input.classList.add(
      "border-gray-300",
      "focus:border-blue-500",
      "focus:ring-blue-500"
    );
  });
}

async function handleFormSubmit(e) {
  e.preventDefault();

  clearFormErrors();

  const formData = new FormData(bookForm);
  const bookData = {
    title: formData.get("title").trim(),
    author: formData.get("author").trim(),
    published_date: formData.get("published_date") || null,
    genre: formData.get("genre").trim() || null,
    price: formData.get("price") ? parseFloat(formData.get("price")) : null,
    stock: formData.get("stock") ? parseInt(formData.get("stock")) : null,
    description: formData.get("description").trim() || null,
  };

  // Basic validation
  let hasErrors = false;

  if (!bookData.title) {
    showFieldError("title", "Title is required");
    hasErrors = true;
  }

  if (!bookData.author) {
    showFieldError("author", "Author is required");
    hasErrors = true;
  }

  if (hasErrors) {
    return;
  }

  try {
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

    let result;
    if (currentBookId) {
      result = await updateBook(currentBookId, bookData);
      showToast("Book updated successfully!", "success");
    } else {
      result = await createBook(bookData);
      showToast("Book created successfully!", "success");
    }

    closeBookModal();
    loadBooks();
  } catch (error) {
    console.error("Error saving book:", error);

    try {
      const errorData = JSON.parse(error.message);
      Object.keys(errorData).forEach((field) => {
        showFieldError(field, errorData[field][0]);
      });
    } catch {
      showToast("Error saving book: " + error.message, "error");
    }
  } finally {
    saveBtn.disabled = false;
    saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Save Book';
  }
}

function showFieldError(fieldName, message) {
  const field = document.getElementById(fieldName);
  const errorElement = document.getElementById(fieldName + "Error");

  if (field && errorElement) {
    field.classList.remove(
      "border-gray-300",
      "focus:border-blue-500",
      "focus:ring-blue-500"
    );
    field.classList.add(
      "border-red-300",
      "focus:border-red-500",
      "focus:ring-red-500"
    );
    errorElement.textContent = message;
    errorElement.classList.remove("hidden");
  }
}

function deleteBookConfirm(bookId, bookTitle) {
  openDeleteModal(bookId, bookTitle);
}

async function handleDeleteConfirm() {
  if (!currentBookId) return;

  try {
    confirmDeleteBtn.disabled = true;
    confirmDeleteBtn.innerHTML =
      '<i class="fas fa-spinner fa-spin mr-2"></i>Deleting...';

    await deleteBook(currentBookId);
    showToast("Book deleted successfully!", "success");

    closeDeleteModalFunc();
    loadBooks();
  } catch (error) {
    console.error("Error deleting book:", error);
    showToast("Error deleting book: " + error.message, "error");
  } finally {
    confirmDeleteBtn.disabled = false;
    confirmDeleteBtn.innerHTML = '<i class="fas fa-trash mr-2"></i>Delete Book';
  }
}

function editBook(bookId) {
  openBookModal(bookId);
}

// Utility Functions
function escapeHtml(text) {
  const div = document.createElement("div");
  div.textContent = text;
  return div.innerHTML;
}

function showToast(message, type = "success") {
  const toast = document.createElement("div");
  toast.className = `max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden transform transition-all duration-300 ease-in-out translate-x-full`;

  const icon =
    type === "success"
      ? "fas fa-check-circle text-green-400"
      : "fas fa-exclamation-circle text-red-400";
  const bgColor = type === "success" ? "bg-green-50" : "bg-red-50";
  const borderColor =
    type === "success" ? "border-green-200" : "border-red-200";

  toast.innerHTML = `
        <div class="p-4 border-l-4 ${borderColor} ${bgColor}">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="${icon}"></i>
                </div>
                <div class="ml-3 w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900">${escapeHtml(
                      message
                    )}</p>
                </div>
                <div class="ml-4 flex-shrink-0 flex">
                    <button class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;

  toastContainer.appendChild(toast);

  // Trigger animation
  setTimeout(() => {
    toast.classList.remove("translate-x-full");
  }, 100);

  // Remove toast after 5 seconds
  setTimeout(() => {
    toast.classList.add("translate-x-full");
    setTimeout(() => {
      if (toast.parentNode) {
        toast.parentNode.removeChild(toast);
      }
    }, 300);
  }, 5000);
}
