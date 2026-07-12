$(function () {
    function setActivePage(page) {
        $('.richcare-nav a[data-page]').removeClass('active-page');
        $('.richcare-nav a[data-page="' + page + '"]').addClass('active-page');
    }

    function updateActiveFromScroll() {
        var active = null;
        $('[id="about"], [id="services"], [id="contact"]').each(function () {
            if ($(this).offset().top - $(window).scrollTop() <= 120) {
                active = $(this).attr('id');
            }
        });

        if (active) {
            setActivePage(active);
        }
    }

    var path = window.location.pathname;

    if (path.indexOf('staff.php') !== -1 || path.indexOf('login.php') !== -1 || path.indexOf('patient.php') !== -1) {
        setActivePage('staff');
    } else if (path.indexOf('book.php') !== -1) {
        setActivePage('book');
    } else if (window.location.hash) {
        setActivePage(window.location.hash.replace('#', ''));
    } else if (path.indexOf('index.php') !== -1 || path.slice(-1) === '/') {
        setActivePage('home');
    }

    $('a[href*="#"]').on('click', function (event) {
        var target = $($(this).attr('href').split('#')[1] ? '#' + $(this).attr('href').split('#')[1] : '');
        if (target.length) {
            event.preventDefault();
            setActivePage(target.attr('id'));
            $('html, body').animate({ scrollTop: target.offset().top - 70 }, 450);
        }
    });

    $(document).on('click', '[data-toggle-password]', function () {
        var button = $(this);
        var input = $(button.data('toggle-password'));
        var icon = button.find('.glyphicon');

        if (!input.length) {
            return;
        }

        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            button.attr('aria-label', 'Hide password');
            icon.removeClass('glyphicon-eye-open').addClass('glyphicon-eye-close');
        } else {
            input.attr('type', 'password');
            button.attr('aria-label', 'Show password');
            icon.removeClass('glyphicon-eye-close').addClass('glyphicon-eye-open');
        }
    });

    if (path.indexOf('index.php') !== -1 || path.slice(-1) === '/') {
        $(window).on('scroll', updateActiveFromScroll);
        updateActiveFromScroll();
    }
});
