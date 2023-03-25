// Imports

import $ from "jquery";
import * as bootstrap from 'bootstrap';
import 'metismenu';

// Stylesheets

// import './assets/base.scss';
// import '../../themes/architect/src/base.scss';

$(document).ready(() => {

    // Sidebar Menu

    setTimeout(function () {
        $(".vertical-nav-menu").metisMenu();
    }, 100);

    // Search wrapper trigger

    $('.search-icon').click(function () {
        $(this).parent().parent().addClass('active');
    });

    $('.search-wrapper .btn-close').click(function () {
        $(this).parent().removeClass('active');
    });

    // Stop Bootstrap 5 Dropdown for closing on click inside

    $('.dropdown-menu').on('click', function (event) {
        var events = $._data(document, 'events') || {};
        events = events.click || [];
        for (var i = 0; i < events.length; i++) {
            if (events[i].selector) {

                if ($(event.target).is(events[i].selector)) {
                    events[i].handler.call(event.target, event);
                }

                $(event.target).parents(events[i].selector).each(function () {
                    events[i].handler.call(this, event);
                });
            }
        }
        event.stopPropagation(); //Always stop propagation
    });

    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // BS5 Tooltips

    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    $('.mobile-toggle-nav').click(function () {
        $(this).toggleClass('is-active');
        $('.app-container').toggleClass('sidebar-mobile-open');
    });

    $('.mobile-toggle-header-nav').click(function () {
        $(this).toggleClass('active');
        $('.app-header__content').toggleClass('header-mobile-open');
    });

    // Responsive

    var resizeClass = function () {
        var win = document.body.clientWidth;
        if (win < 1250) {
            $('.app-container').addClass('closed-sidebar-mobile closed-sidebar');
            $('.app-header').removeClass("heard-text-light bg-dark").addClass("bg-light header-text-dark");
        } else {
            $('.app-header').addClass("heard-text-light bg-dark").removeClass("bg-light header-text-dark");
            $('.app-container').removeClass('closed-sidebar-mobile closed-sidebar');
        }
    };


    $(window).on('resize', function () {
        resizeClass();
    });

    resizeClass();

});

