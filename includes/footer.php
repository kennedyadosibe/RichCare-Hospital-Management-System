<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-sm-4">
                <strong>RichCare Hospital</strong>
                <p>Digital hospital management for better patient care.</p>
            </div>
            <div class="col-sm-4 footer-links">
                <strong>Quick links</strong>
                <a href="index.php#about">About</a>
                <a href="index.php#services">Services</a>
                <a href="book.php">Book appointment</a>
                <a href="login.php">Staff portal</a>
            </div>
            <div class="col-sm-4 footer-contact">
                <strong>Emergency dials</strong>
                <p>112 General emergency</p>
                <p>193 Ambulance</p>
                <p>191 Police / 192 Fire</p>
            </div>
        </div>
    </div>
</footer>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<script src="assets/js/site.js?v=3"></script>
<script>
function toggleStaffPassword(event, selector) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }

    var button = event && event.target ? event.target.closest('[data-toggle-password]') : null;
    var input = document.querySelector(selector);
    if (!input) {
        return false;
    }

    var icon = button ? button.querySelector('.glyphicon') : null;

    if (input.type === 'password') {
        input.type = 'text';
        if (button) {
            button.setAttribute('aria-label', 'Hide password');
        }
        if (icon) {
            icon.classList.remove('glyphicon-eye-open');
            icon.classList.add('glyphicon-eye-close');
        }
    } else {
        input.type = 'password';
        if (button) {
            button.setAttribute('aria-label', 'Show password');
        }
        if (icon) {
            icon.classList.remove('glyphicon-eye-close');
            icon.classList.add('glyphicon-eye-open');
        }
    }

    return false;
}

document.addEventListener('click', function (event) {
    var button = event.target.closest('[data-toggle-password]');
    if (!button) {
        return;
    }

    toggleStaffPassword(event, button.getAttribute('data-toggle-password'));
});
</script>
</body>
</html>
