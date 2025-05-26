document.addEventListener('DOMContentLoaded', function() {
  // ---
  // Login Drawer Logic
  // ---
  var loginDrawer = document.getElementById('loginDrawer');
  if (loginDrawer) {
    loginDrawer.addEventListener('show.bs.offcanvas', function () {
      fetch('../auth/login.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('loginDrawerBody').innerHTML = html;
          // Add event for "Register here" link
          // Using a slight delay to ensure the content is rendered
          setTimeout(() => {
            var regLink = document.querySelector('#loginDrawerBody a[href="register.php"]');
            if (regLink) {
              regLink.addEventListener('click', function(e) {
                e.preventDefault();
                var loginOffcanvas = bootstrap.Offcanvas.getInstance(loginDrawer);
                loginOffcanvas.hide();
                setTimeout(() => {
                  var registerDrawer = document.getElementById('registerDrawer');
                  var registerOffcanvas = new bootstrap.Offcanvas(registerDrawer);
                  registerOffcanvas.show();
                }, 300); // Give time for login drawer to close
              });
            }

            // Add event for "Forgot Password" link within the login drawer
            var forgotPasswordLink = document.getElementById('openForgotPasswordDrawer');
            if (forgotPasswordLink) {
              forgotPasswordLink.addEventListener('click', function(e) {
                e.preventDefault();
                var loginOffcanvas = bootstrap.Offcanvas.getInstance(loginDrawer);
                if (loginOffcanvas) loginOffcanvas.hide();
                setTimeout(function() {
                  var forgotDrawer = document.getElementById('forgotPasswordDrawer');
                  var forgotOffcanvas = new bootstrap.Offcanvas(forgotDrawer);
                  forgotOffcanvas.show();
                }, 300); // Give time for login drawer to close
              });
            }
          }, 100);
        });
    });
  }

  // ---
  // Register Drawer Logic
  // ---
  var registerDrawer = document.getElementById('registerDrawer');
  if (registerDrawer) {
    registerDrawer.addEventListener('show.bs.offcanvas', function () {
      fetch('../auth/register.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('registerDrawerBody').innerHTML = html;
          // Add event for "Login here" link
          setTimeout(() => {
            var loginLink = document.querySelector('#registerDrawerBody #openLoginDrawer');
            if (loginLink) {
              loginLink.addEventListener('click', function(e) {
                e.preventDefault();
                var registerOffcanvas = bootstrap.Offcanvas.getInstance(registerDrawer);
                registerOffcanvas.hide();
                setTimeout(() => {
                  var loginDrawer = document.getElementById('loginDrawer');
                  var loginOffcanvas = new bootstrap.Offcanvas(loginDrawer);
                  loginOffcanvas.show();
                }, 300); // Give time for register drawer to close
              });
            }
          }, 100);
        });
    });
  }

  // ---
  // Forgot Password Drawer Logic
  // ---
  var forgotDrawer = document.getElementById('forgotPasswordDrawer');
  if (forgotDrawer) {
    forgotDrawer.addEventListener('show.bs.offcanvas', function () {
      fetch('../auth/forgot-password.php')
        .then(response => response.text())
        .then(html => {
          document.getElementById('forgotPasswordDrawerBody').innerHTML = html;
          // Add event for "Back to Login" link within the forgot password drawer
          setTimeout(() => {
            var backToLoginLink = document.getElementById('openLoginDrawerFromForgot');
            if (backToLoginLink) {
              backToLoginLink.addEventListener('click', function(e) {
                e.preventDefault();
                var forgotOffcanvas = bootstrap.Offcanvas.getInstance(forgotDrawer);
                if (forgotOffcanvas) forgotOffcanvas.hide();
                setTimeout(function() {
                  var loginDrawer = document.getElementById('loginDrawer');
                  var loginOffcanvas = new bootstrap.Offcanvas(loginDrawer);
                  loginOffcanvas.show();
                }, 300); // Give time for forgot drawer to close
              });
            }
          }, 100);
        });
    });
  }
});
