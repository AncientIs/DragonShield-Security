/* ============================================
   DragonShield Security — Main JavaScript
   ============================================ */

document.addEventListener('DOMContentLoaded', function () {

  /* -------------------------------------------
     1. MOBILE NAVIGATION
     ------------------------------------------- */
  const hamburger = document.querySelector('.hamburger');
  const mobileMenu = document.querySelector('.mobile-menu');
  const mobileOverlay = document.querySelector('.mobile-overlay');

  if (hamburger && mobileMenu && mobileOverlay) {
    hamburger.addEventListener('click', function () {
      hamburger.classList.toggle('active');
      mobileMenu.classList.toggle('open');
      mobileOverlay.classList.toggle('active');
      document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
    });

    mobileOverlay.addEventListener('click', function () {
      hamburger.classList.remove('active');
      mobileMenu.classList.remove('open');
      mobileOverlay.classList.remove('active');
      document.body.style.overflow = '';
    });

    // Close mobile menu on link click
    mobileMenu.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        hamburger.classList.remove('active');
        mobileMenu.classList.remove('open');
        mobileOverlay.classList.remove('active');
        document.body.style.overflow = '';
      });
    });
  }

  /* -------------------------------------------
     2. SCROLL-TRIGGERED FADE-IN ANIMATIONS
     ------------------------------------------- */
  var fadeElements = document.querySelectorAll('.fade-in');

  if (fadeElements.length > 0 && 'IntersectionObserver' in window) {
    var fadeObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          fadeObserver.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1,
      rootMargin: '0px 0px -40px 0px'
    });

    fadeElements.forEach(function (el) {
      fadeObserver.observe(el);
    });
  } else {
    // Fallback: show all elements immediately
    fadeElements.forEach(function (el) {
      el.classList.add('visible');
    });
  }

  /* -------------------------------------------
     3. STARRY BACKGROUND GENERATION (CSS-based)
     ------------------------------------------- */
  var starsBg = document.querySelector('.stars-bg');
  if (starsBg) {
    var starCount = 80;
    for (var i = 0; i < starCount; i++) {
      var star = document.createElement('div');
      star.classList.add('star');
      star.style.left = Math.random() * 100 + '%';
      star.style.top = Math.random() * 100 + '%';
      var size = Math.random() * 2.5 + 0.5;
      star.style.width = size + 'px';
      star.style.height = size + 'px';
      star.style.setProperty('--duration', (Math.random() * 4 + 2) + 's');
      star.style.animationDelay = (Math.random() * 5) + 's';
      starsBg.appendChild(star);
    }
  }

  /* -------------------------------------------
     4. PORTAL CANVAS ANIMATION (Home page hero)
     ------------------------------------------- */
  var portalCanvas = document.getElementById('portal-canvas');
  if (portalCanvas) {
    var ctx = portalCanvas.getContext('2d');
    var particles = [];
    var portalParticleCount = 120;

    function resizeCanvas() {
      var parent = portalCanvas.parentElement;
      portalCanvas.width = parent.offsetWidth;
      portalCanvas.height = parent.offsetHeight;
    }

    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    // Create portal particles
    function createParticle() {
      var centerX = portalCanvas.width / 2;
      var centerY = portalCanvas.height / 2;
      var angle = Math.random() * Math.PI * 2;
      var radius = Math.random() * 200 + 50;
      return {
        x: centerX + Math.cos(angle) * radius,
        y: centerY + Math.sin(angle) * radius,
        angle: angle,
        radius: radius,
        speed: Math.random() * 0.008 + 0.002,
        size: Math.random() * 2 + 0.5,
        opacity: Math.random() * 0.6 + 0.2,
        hue: Math.random() > 0.5 ? 190 : 175 // electric blue or cyan
      };
    }

    for (var j = 0; j < portalParticleCount; j++) {
      particles.push(createParticle());
    }

    function animatePortal() {
      ctx.clearRect(0, 0, portalCanvas.width, portalCanvas.height);
      var centerX = portalCanvas.width / 2;
      var centerY = portalCanvas.height / 2;

      // Draw subtle portal glow
      var gradient = ctx.createRadialGradient(centerX, centerY, 0, centerX, centerY, 250);
      gradient.addColorStop(0, 'rgba(0, 212, 255, 0.04)');
      gradient.addColorStop(0.5, 'rgba(108, 99, 255, 0.02)');
      gradient.addColorStop(1, 'rgba(0, 0, 0, 0)');
      ctx.fillStyle = gradient;
      ctx.fillRect(0, 0, portalCanvas.width, portalCanvas.height);

      // Animate particles in orbit
      particles.forEach(function (p) {
        p.angle += p.speed;
        p.x = centerX + Math.cos(p.angle) * p.radius;
        p.y = centerY + Math.sin(p.angle) * p.radius;

        ctx.beginPath();
        ctx.arc(p.x, p.y, p.size, 0, Math.PI * 2);
        ctx.fillStyle = 'hsla(' + p.hue + ', 100%, 60%, ' + p.opacity + ')';
        ctx.fill();
      });

      requestAnimationFrame(animatePortal);
    }

    animatePortal();
  }

  /* -------------------------------------------
     5. FAQ ACCORDION (Contact page)
     ------------------------------------------- */
  var faqItems = document.querySelectorAll('.faq-item');
  faqItems.forEach(function (item) {
    var question = item.querySelector('.faq-question');
    if (question) {
      question.addEventListener('click', function () {
        var isOpen = item.classList.contains('open');

        // Close all other FAQ items
        faqItems.forEach(function (otherItem) {
          otherItem.classList.remove('open');
        });

        // Toggle current item
        if (!isOpen) {
          item.classList.add('open');
        }
      });
    }
  });

  /* -------------------------------------------
     6. CONTACT FORM VALIDATION & SUBMISSION
     ------------------------------------------- */
  var contactForm = document.getElementById('contact-form');
  var successModal = document.getElementById('form-success-modal');

  if (contactForm) {
    contactForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var isValid = true;

      // Clear previous errors
      contactForm.querySelectorAll('.error').forEach(function (el) {
        el.classList.remove('error');
      });
      contactForm.querySelectorAll('.error-message').forEach(function (el) {
        el.style.display = 'none';
      });

      // Validate required fields
      var requiredFields = contactForm.querySelectorAll('[required]');
      requiredFields.forEach(function (field) {
        if (!field.value.trim()) {
          isValid = false;
          field.classList.add('error');
          var errorMsg = field.parentElement.querySelector('.error-message');
          if (errorMsg) {
            errorMsg.textContent = 'This field is required.';
            errorMsg.style.display = 'block';
          }
        }
      });

      // Validate email format
      var emailField = contactForm.querySelector('input[type="email"]');
      if (emailField && emailField.value.trim()) {
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailPattern.test(emailField.value.trim())) {
          isValid = false;
          emailField.classList.add('error');
          var emailError = emailField.parentElement.querySelector('.error-message');
          if (emailError) {
            emailError.textContent = 'Please enter a valid email address.';
            emailError.style.display = 'block';
          }
        }
      }

      // Validate select fields
      var serviceSelect = contactForm.querySelector('#service-interest');
      if (serviceSelect && serviceSelect.value === '') {
        isValid = false;
        serviceSelect.classList.add('error');
        var selectError = serviceSelect.parentElement.querySelector('.error-message');
        if (selectError) {
          selectError.textContent = 'Please select a service.';
          selectError.style.display = 'block';
        }
      }

      if (isValid && successModal) {
        successModal.classList.add('active');
        contactForm.reset();
      }
    });
  }

  // Close success modal
  if (successModal) {
    var closeModalBtn = successModal.querySelector('.close-modal');
    if (closeModalBtn) {
      closeModalBtn.addEventListener('click', function () {
        successModal.classList.remove('active');
      });
    }

    successModal.addEventListener('click', function (e) {
      if (e.target === successModal) {
        successModal.classList.remove('active');
      }
    });
  }

  /* -------------------------------------------
     7. ANIMATED COUNTERS (Case Studies page)
     ------------------------------------------- */
  var counterElements = document.querySelectorAll('.counter');
  if (counterElements.length > 0 && 'IntersectionObserver' in window) {
    var counterObserver = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.5 });

    counterElements.forEach(function (el) {
      counterObserver.observe(el);
    });
  }

  function animateCounter(el) {
    var target = parseInt(el.getAttribute('data-target'), 10);
    var suffix = el.getAttribute('data-suffix') || '';
    var duration = 2000;
    var startTime = null;

    function step(timestamp) {
      if (!startTime) startTime = timestamp;
      var progress = Math.min((timestamp - startTime) / duration, 1);
      var eased = 1 - Math.pow(1 - progress, 3); // ease-out cubic
      var current = Math.floor(eased * target);
      el.textContent = current + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.textContent = target + suffix;
      }
    }

    requestAnimationFrame(step);
  }

  /* -------------------------------------------
     8. NAVBAR SCROLL EFFECT
     ------------------------------------------- */
  var navbar = document.querySelector('.navbar');
  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 50) {
        navbar.style.background = 'rgba(10, 14, 39, 0.98)';
        navbar.style.borderBottomColor = 'rgba(0, 212, 255, 0.15)';
      } else {
        navbar.style.background = 'rgba(10, 14, 39, 0.92)';
        navbar.style.borderBottomColor = 'rgba(0, 212, 255, 0.1)';
      }
    });
  }

});
