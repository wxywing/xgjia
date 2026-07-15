(function() {
  var carousel = document.getElementById("heroCarousel");
  if (!carousel) return;
  var slides = carousel.querySelectorAll(".slide");
  var dots = carousel.querySelectorAll(".dots .dot");
  var prevBtn = carousel.querySelector(".arrow-prev");
  var nextBtn = carousel.querySelector(".arrow-next");
  var current = 0;
  var total = slides.length;
  var timer = null;
  var interval = 5000;

  function goTo(index) {
    slides[current].classList.remove("active");
    dots[current].classList.remove("active");
    current = ((index % total) + total) % total;
    slides[current].classList.add("active");
    dots[current].classList.add("active");
  }

  function next() { goTo(current + 1); }
  function prev() { goTo(current - 1); }

  function startAuto() {
    stopAuto();
    timer = setInterval(next, interval);
  }

  function stopAuto() {
    if (timer) { clearInterval(timer); timer = null; }
  }

  if (prevBtn) prevBtn.addEventListener("click", function(e) { e.preventDefault(); prev(); startAuto(); });
  if (nextBtn) nextBtn.addEventListener("click", function(e) { e.preventDefault(); next(); startAuto(); });

  dots.forEach(function(dot) {
    dot.addEventListener("click", function() {
      var idx = parseInt(this.getAttribute("data-index"));
      if (!isNaN(idx)) { goTo(idx); startAuto(); }
    });
  });

  carousel.addEventListener("mouseenter", stopAuto);
  carousel.addEventListener("mouseleave", startAuto);

  var touchStartX = 0;
  carousel.addEventListener("touchstart", function(e) { touchStartX = e.touches[0].clientX; });
  carousel.addEventListener("touchend", function(e) {
    var diff = touchStartX - e.changedTouches[0].clientX;
    if (Math.abs(diff) > 50) { diff > 0 ? next() : prev(); startAuto(); }
  });

  startAuto();
})();