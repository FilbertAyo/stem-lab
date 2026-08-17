/**
 * Prevent double-submit on mutating forms (avoids 419 CSRF races after session regenerate).
 * Opt out with data-allow-double-submit="1" on the form.
 */
(function () {
    function formSubmitControls(form) {
        return form.querySelectorAll(
            'button[type="submit"], input[type="submit"], button:not([type])'
        );
    }

    function lockFormSubmit(form, submitter) {
        form.dataset.submitting = '1';
        form.setAttribute('aria-busy', 'true');

        // Disabled submit buttons are omitted from POST data. Copy the clicked
        // button's name/value into a hidden field so Save draft vs Submit still works.
        if (submitter && submitter.name && form.contains(submitter)) {
            var proxy = form.querySelector('input[data-submitter-proxy="1"]');
            if (!proxy) {
                proxy = document.createElement('input');
                proxy.type = 'hidden';
                proxy.setAttribute('data-submitter-proxy', '1');
                form.appendChild(proxy);
            }
            proxy.name = submitter.name;
            proxy.value = submitter.value;
        }

        formSubmitControls(form).forEach(function (control) {
            if (control.disabled) {
                return;
            }
            control.disabled = true;
            control.setAttribute('aria-disabled', 'true');
        });
    }

    function unlockFormSubmit(form) {
        delete form.dataset.submitting;
        form.removeAttribute('aria-busy');

        formSubmitControls(form).forEach(function (control) {
            control.disabled = false;
            control.removeAttribute('aria-disabled');
        });
    }

    var lastSubmitter = null;

    document.addEventListener(
        'click',
        function (event) {
            var target = event.target;
            if (!(target instanceof Element)) {
                return;
            }

            var control = target.closest('button[type="submit"], input[type="submit"], button:not([type])');
            if (control && control.form) {
                lastSubmitter = control;
            }
        },
        true
    );

    document.addEventListener(
        'submit',
        function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            var method = (form.getAttribute('method') || 'GET').toUpperCase();
            if (method === 'GET') {
                return;
            }

            if (form.dataset.allowDoubleSubmit === '1') {
                return;
            }

            if (form.dataset.submitting === '1') {
                event.preventDefault();
                event.stopPropagation();
                return;
            }

            var submitter = event.submitter || (lastSubmitter && lastSubmitter.form === form ? lastSubmitter : null);
            lockFormSubmit(form, submitter);
            lastSubmitter = null;
        },
        true
    );

    // Unlock if a later handler (e.g. onsubmit confirm) cancels the submit.
    document.addEventListener(
        'submit',
        function (event) {
            var form = event.target;
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (event.defaultPrevented && form.dataset.submitting === '1') {
                unlockFormSubmit(form);
            }
        },
        false
    );

    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) {
            return;
        }

        document.querySelectorAll('form[data-submitting="1"]').forEach(unlockFormSubmit);
    });

    // Expose for programmatic form.submit() call sites that bypass the submit event.
    window.StemLabFormGuard = {
        lock: lockFormSubmit,
        unlock: unlockFormSubmit,
    };
})();

$(function () {

    // Header Scroll
    $(window).scroll(function () {
        if ($(window).scrollTop() >= 60) {
            $("header").addClass("fixed-header");
        } else {
            $("header").removeClass("fixed-header");
        }
    });


    // Featured Owl Carousel
    $('.featured-projects-slider .owl-carousel').owlCarousel({
        center: true,
        loop: true,
        margin: 30,
        nav: false,
        dots: false,
        autoplay: true,
        autoplayTimeout: 5000,
        autoplayHoverPause: false,
        smartSpeed: 1000,
        slideSpeed: 1000,
        responsive: {
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            1200: {
                items: 2
            }
        }
    })


    // Count
    $('.count').each(function () {
		$(this).prop('Counter', 0).animate({
			Counter: $(this).text()
		}, {
			duration: 1000,
			easing: 'swing',
			step: function (now) {
				$(this).text(Math.ceil(now));
			}
		});
	});


    // ScrollToTop
    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    const btn = document.getElementById("scrollToTopBtn");
    if (btn) {
        btn.addEventListener("click", scrollToTop);
    }

    window.onscroll = function () {
        const scrollBtn = document.getElementById("scrollToTopBtn");
        if (!scrollBtn) {
            return;
        }
        if (document.documentElement.scrollTop > 100 || document.body.scrollTop > 100) {
            scrollBtn.style.display = "flex";
        } else {
            scrollBtn.style.display = "none";
        }
    };


    // Aos
	AOS.init({
		once: true,
	});

});

