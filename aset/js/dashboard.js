/* HERO AUTO SLIDE */
let slideIndex = 0;
const slides = document.querySelectorAll(".hero img");
const dots = document.querySelectorAll(".dots span");

setInterval(() => {
    slides[slideIndex].classList.remove("active");
    dots[slideIndex].classList.remove("active");

    slideIndex = (slideIndex + 1) % slides.length;

    slides[slideIndex].classList.add("active");
    dots[slideIndex].classList.add("active");
}, 5000);

const newsContainer = document.getElementById("newsContainer");
const prev = document.getElementById("newsPrev");
const next = document.getElementById("newsNext");

const scrollStep = 340;

function updateNav() {
    prev.disabled = newsContainer.scrollLeft <= 0;
    next.disabled =
        newsContainer.scrollLeft + newsContainer.clientWidth >=
        newsContainer.scrollWidth - 5;
}

prev.addEventListener("click", () => {
    newsContainer.scrollBy({ left: -scrollStep, behavior: "smooth" });
});

next.addEventListener("click", () => {
    newsContainer.scrollBy({ left: scrollStep, behavior: "smooth" });
});

newsContainer.addEventListener("scroll", updateNav);
window.addEventListener("load", updateNav);

