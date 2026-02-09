/**
 * Molochko theme – mobile menu, side panel, scroll to top. No Elementor.
 */
(function ($) {
	"use strict";

	$(document).ready(function () {
		panelMobileMenu();
		scrollToTop();
		submenuToggle();
	});

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
