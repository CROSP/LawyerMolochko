/**
 * Molochko theme – mobile menu, side panel, scroll to top. No Elementor.
 */
(function ($) {
	"use strict";

	$(document).ready(function () {
		panelMobileMenu();
		scrollToTop();
		submenuToggle();
		heroCustomSlider();
		caseStudiesCarousel();
		blogSectionCarousel();
		reviewsSectionCarousel();
		lawTalkCarousel();
		consultationPopup();
	});

	// Consultation popup: open on .js-consultation-popup, close on backdrop/close btn/Escape
	function consultationPopup() {
		var $popup = $("#consultation-popup");
		if (!$popup.length) return;

		$(document).on("click", ".js-consultation-popup", function (e) {
			if ($(this).attr("href") === "#consultation-popup" || $(this).data("popup") === "consultation") {
				e.preventDefault();
				$popup.removeClass("is-closed").attr("aria-hidden", "false");
				$("body").addClass("consultation-popup-open");
				$popup.find(".molochko-consultation-popup-dialog").focus();
			}
		});

		function closeConsultationPopup() {
			$popup.addClass("is-closed").attr("aria-hidden", "true");
			$("body").removeClass("consultation-popup-open");
		}

		$(document).on("click", "[data-close=\"consultation\"]", function (e) {
			e.preventDefault();
			closeConsultationPopup();
		});

		$popup.on("click", function (e) {
			if (e.target === this) closeConsultationPopup();
		});

		$(document).on("keydown", function (e) {
			if (e.key === "Escape" && $popup.attr("aria-hidden") === "false") {
				closeConsultationPopup();
			}
		});
	}

	// Custom hero slider (ACF hero_slides): Slick full-width
	function heroCustomSlider() {
		var $slides = $(".molochko-hero-slides");
		if (!$slides.length || !$.fn.slick) return;
		if ($slides.hasClass("slick-initialized")) return;
		$slides.slick({
			slidesToShow: 1,
			slidesToScroll: 1,
			autoplay: true,
			autoplaySpeed: 5000,
			infinite: true,
			arrows: true,
			dots: true,
			fade: true,
			adaptiveHeight: false,
			prevArrow: '<button type="button" class="slick-prev" aria-label="Попередній"><i class="zmdi zmdi-chevron-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next" aria-label="Наступний"><i class="zmdi zmdi-chevron-right"></i></button>'
		});
	}

	// Case Studies: Slick carousel (only when block and Slick exist)
	function caseStudiesCarousel() {
		var $carousel = $(".molochko-case-studies .mcs-carousel");
		var $track = $carousel.find(".mcs-track");
		if (!$track.length || !$.fn.slick) return;
		if ($track.hasClass("slick-initialized")) return;
		$track.slick({
			slidesToShow: 2,
			slidesToScroll: 1,
			infinite: true,
			arrows: true,
			dots: true,
			adaptiveHeight: false,
			appendArrows: $carousel.find(".mcs-carousel-nav"),
			prevArrow: '<button type="button" class="slick-prev mcs-slick-prev" aria-label="Попередній"><i class="zmdi zmdi-chevron-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next mcs-slick-next" aria-label="Наступний"><i class="zmdi zmdi-chevron-right"></i></button>',
			responsive: [
				{ breakpoint: 1024, settings: { slidesToShow: 2 } },
				{ breakpoint: 768, settings: { slidesToShow: 1, dots: true } },
				{ breakpoint: 480, settings: { slidesToShow: 1 } }
			]
		});
	}

	// Blog section: Slick carousel (front page)
	function blogSectionCarousel() {
		var $carousel = $(".molochko-blog-section .blog-section-carousel");
		var $track = $carousel.find(".blog-section-track");
		if (!$track.length || !$.fn.slick) return;
		if ($track.hasClass("slick-initialized")) return;
		$track.slick({
			slidesToShow: 2,
			slidesToScroll: 1,
			infinite: true,
			arrows: true,
			dots: true,
			adaptiveHeight: false,
			appendArrows: $carousel.find(".blog-section-carousel-nav"),
			prevArrow: '<button type="button" class="slick-prev blog-section-slick-prev" aria-label="Попередній"><i class="zmdi zmdi-chevron-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next blog-section-slick-next" aria-label="Наступний"><i class="zmdi zmdi-chevron-right"></i></button>',
			responsive: [
				{ breakpoint: 1024, settings: { slidesToShow: 2 } },
				{ breakpoint: 768, settings: { slidesToShow: 1, dots: true } },
				{ breakpoint: 480, settings: { slidesToShow: 1 } }
			]
		});
	}

	// Reviews section: Slick carousel
	function reviewsSectionCarousel() {
		var $carousel = $(".molochko-reviews .molochko-reviews-carousel");
		var $track = $carousel.find(".molochko-reviews-track");
		if (!$track.length || !$.fn.slick) return;
		if ($track.hasClass("slick-initialized")) return;
		if ($track.children(".molochko-review-slide").length < 2) return;
		$track.slick({
			slidesToShow: 2,
			slidesToScroll: 1,
			infinite: true,
			arrows: true,
			dots: true,
			adaptiveHeight: true,
			appendArrows: $carousel.find(".molochko-reviews-carousel-nav"),
			prevArrow: '<button type="button" class="slick-prev molochko-reviews-prev" aria-label="Попередній"><i class="zmdi zmdi-chevron-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next molochko-reviews-next" aria-label="Наступний"><i class="zmdi zmdi-chevron-right"></i></button>',
			responsive: [
				{ breakpoint: 1024, settings: { slidesToShow: 2 } },
				{ breakpoint: 768, settings: { slidesToShow: 1, dots: true } },
				{ breakpoint: 480, settings: { slidesToShow: 1 } }
			]
		});
	}

	// Law Talk: Slick carousel for reels/tiktok embeds
	function lawTalkCarousel() {
		var $wrap = $(".molochko-law-talk .lt-carousel-wrap");
		var $carousel = $wrap.find(".lt-carousel");
		var $track = $carousel.find(".lt-track");
		if (!$track.length || !$.fn.slick) return;
		if ($track.children(".lt-slide").length < 2) return;
		if ($track.hasClass("slick-initialized")) return;
		$track.slick({
			slidesToShow: 3,
			slidesToScroll: 1,
			infinite: true,
			arrows: true,
			dots: true,
			adaptiveHeight: true,
			appendArrows: $carousel.find(".lt-carousel-nav"),
			prevArrow: '<button type="button" class="slick-prev lt-slick-prev" aria-label="Попередній"><i class="zmdi zmdi-chevron-left"></i></button>',
			nextArrow: '<button type="button" class="slick-next lt-slick-next" aria-label="Наступний"><i class="zmdi zmdi-chevron-right"></i></button>',
			responsive: [
				{ breakpoint: 1024, settings: { slidesToShow: 2 } },
				{ breakpoint: 768, settings: { slidesToShow: 1, dots: true } },
				{ breakpoint: 480, settings: { slidesToShow: 1 } }
			]
		});
	}

	$(document).on("click", closePanelOnClick);
	$(document).on("click", ".pxl-close", closePanelOnCloseClick);

	$(window).on("scroll", function () {
		scrollToTop();
	});

	// Mobile: open/close side panel
	function panelMobileMenu() {
		$(document).on("click", ".btn-nav-mobile", function (e) {
			e.preventDefault();
			e.stopPropagation();
			var target = $(this).attr("data-target");
			if (!target) return;
			var $panel = $(target);
			var isOpen = $panel.hasClass("open");
			$(this).toggleClass("cliked");
			$panel.toggleClass("open");
			$("body").toggleClass("side-panel-open");
			$panel.attr("aria-hidden", isOpen);
			$(this).attr("aria-expanded", !isOpen);
			if (!isOpen) {
				$panel.find(".pxl-close").focus();
			}
		});
	}

	// Close panel when clicking overlay (outside)
	function closePanelOnClick(e) {
		var target = $(e.target);
		if (target.hasClass("btn-nav-mobile")) return;
		if (target.closest(".pxl-hidden-template").length) return;
		if (!$("body").hasClass("side-panel-open")) return;
		$(".btn-nav-mobile").removeClass("cliked").attr("aria-expanded", "false");
		$(".pxl-hidden-template").removeClass("open").attr("aria-hidden", "true");
		$("body").removeClass("side-panel-open");
	}

	// Close panel when clicking .pxl-close
	function closePanelOnCloseClick(e) {
		e.preventDefault();
		e.stopPropagation();
		$(this).closest(".pxl-hidden-template").removeClass("open").attr("aria-hidden", "true");
		$(".btn-nav-mobile").removeClass("cliked").attr("aria-expanded", "false");
		$("body").removeClass("side-panel-open");
	}

	// Mobile: submenu toggle (if we add toggle spans)
	function submenuToggle() {
		$(".pxl-mobile-menu .menu-item-has-children > a").after(
			'<span class="mobile-sub-toggle" aria-label="Toggle submenu"></span>'
		);
		$(document).on("click", ".mobile-sub-toggle", function () {
			$(this).siblings(".sub-menu").slideToggle();
			$(this).toggleClass("open");
		});
	}

	// Back to top
	function scrollToTop() {
		var st = $(window).scrollTop();
		var $btn = $(".pxl-scroll-top");
		if (!$btn.length) return;
		if (st > 400) {
			$btn.addClass("on");
		} else {
			$btn.removeClass("on");
		}
	}

	$(".pxl-scroll-top").on("click", function (e) {
		e.preventDefault();
		$("html, body").animate({ scrollTop: 0 }, 400);
	});
})(jQuery);
