// Mobile Navigation Toggle
document.addEventListener("DOMContentLoaded", function () {
  const navToggle = document.getElementById("navToggle");
  const mainNav = document.getElementById("mainNav");

  if (navToggle && mainNav) {
    navToggle.addEventListener("click", function (e) {
      e.preventDefault();
      mainNav.classList.toggle("open");
      const isOpen = mainNav.classList.contains("open");
      navToggle.setAttribute("aria-expanded", isOpen);
    });

    // Tutup menu saat klik di luar
    document.addEventListener("click", function (e) {
      if (!navToggle.contains(e.target) && !mainNav.contains(e.target)) {
        mainNav.classList.remove("open");
        navToggle.setAttribute("aria-expanded", "false");
      }
    });
  }

  // Auto-hide flash messages
  const flash = document.querySelector(".flash");
  if (flash) {
    setTimeout(() => {
      flash.style.opacity = "0";
      flash.style.transition = "opacity 0.3s";
      setTimeout(() => flash.remove(), 300);
    }, 3000);
  }

  // Smooth scroll untuk anchor links
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener("click", function (e) {
      e.preventDefault();
      const target = document.querySelector(this.getAttribute("href"));
      if (target) {
        target.scrollIntoView({
          behavior: "smooth",
          block: "start",
        });
      }
    });
  });
});
