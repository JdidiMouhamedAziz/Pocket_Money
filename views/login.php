<?php
session_start();
$authError = $_SESSION['auth_error'] ?? '';
$authSuccess = $_SESSION['auth_success'] ?? '';
unset($_SESSION['auth_error'], $_SESSION['auth_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Finzo – Auth</title>
  <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600&display=swap" rel="stylesheet"/>
  <style>
   

    /* PAGE SWITCHER */
    
  </style>
  <link rel="stylesheet" href="/pocket_money/public/css/login.css">
  <link rel="stylesheet" href="/pocket_money/public/css/style.css">
  <link rel="stylesheet" href="/pocket_money/public/css/nav.css">
</head>
<body>
   
  <div class="page-tabs">
    <button class="tab-btn active" onclick="showPage('register')">Register</button>
    <button class="tab-btn" onclick="showPage('login')">Sign In</button>
    <a class="tab-btn backBtn" href="/pocket_money/views/components/landingPage.php">Back To Home</a>
  </div>

  <!-- ── REGISTER ── -->
  <div class="auth-card active" id="register">
    <!-- Left: logo -->
    <div class="logo-panel">
      <div class="logo-icon"><span>$</span></div>
      <h2>Finzo</h2>
      <p>Join 12,000+ people tracking their finances smarter</p>
    </div>
    <!-- Right: form -->
    <div class="form-panel">
      <h1>Create your account</h1>
      <?php if ($authError): ?>
        <div class="auth-alert auth-error"><?= htmlspecialchars($authError, ENT_QUOTES, 'UTF-8') ?></div>
      <?php elseif ($authSuccess): ?>
        <div class="auth-alert auth-success"><?= htmlspecialchars($authSuccess, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <form method="post" action="../controllers/authController.php">
        <input type="hidden" name="action" value="register" />
        <div class="form-row">
          <div class="form-group">
            <label for="reg-fname">First name</label>
            <input type="text" id="reg-fname" name="firstName" placeholder="Sara" required />
          </div>
          <div class="form-group">
            <label for="reg-lname">Last name</label>
            <input type="text" id="reg-lname" name="lastName" placeholder="Amara" required />
          </div>
        </div>
      <div class="form-group">
        <label for="reg-email">Email</label>
        <input type="email" id="reg-email" name="email" placeholder="sara@exemple.com" required />
      </div>
      <div class="form-row">
        <div class="form-group">
          <label for="reg-pw">Password</label>
          <input type="password" id="reg-pw" name="password" placeholder="••••••••" required />
        </div>
        <div class="form-group">
          <label for="reg-cpw">Confirm password</label>
          <input type="password" id="reg-cpw" name="confirmPassword" placeholder="••••••••" required />
        </div>
      </div>
      <div class="checkbox-row">
        <input type="checkbox" id="reg-agree" name="agree" checked required />
        <label for="reg-agree">I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>. I consent to receiving budget alerts and notifications.</label>
      </div>
      <button class="btn-submit" type="submit">Create my account</button>
      </form>
      <div class="form-footer">
        Already have an account? <a href="#" onclick="showPage('login'); return false;">Sign in →</a>
      </div>
    </div>
  </div>

  <!-- ── LOGIN ── -->
  <div class="auth-card" id="login">
    <!-- Left: form -->
    <div class="form-panel">
      <h1>Welcome back</h1>
      <?php if ($authError): ?>
        <div class="auth-alert auth-error"><?= htmlspecialchars($authError, ENT_QUOTES, 'UTF-8') ?></div>
      <?php elseif ($authSuccess): ?>
        <div class="auth-alert auth-success"><?= htmlspecialchars($authSuccess, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>
      <form method="post" action="../controllers/authController.php">
        <input type="hidden" name="action" value="login" />
        <button class="btn-google" type="button">
        <svg class="g-logo" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
          <path fill="#EA4335" d="M24 9.5c3.1 0 5.6 1.1 7.6 2.9l5.6-5.6C33.6 3.5 29.1 1.5 24 1.5 14.9 1.5 7.2 7.1 4.1 15l6.6 5.1C12.3 14 17.7 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.5 24.5c0-1.6-.1-3.1-.4-4.5H24v8.5h12.7c-.6 3-2.3 5.5-4.8 7.2l7.5 5.8c4.3-4 6.8-9.9 7.1-17z"/>
          <path fill="#FBBC05" d="M10.7 28.6A14.5 14.5 0 0 1 9.5 24c0-1.6.3-3.1.8-4.6L3.7 14.3A22.5 22.5 0 0 0 1.5 24c0 3.6.8 7 2.3 10l6.9-5.4z"/>
          <path fill="#34A853" d="M24 46.5c5.1 0 9.4-1.7 12.5-4.6l-7.5-5.8c-1.7 1.1-3.8 1.8-6 1.8-6.3 0-11.7-4.5-13.3-10.5l-6.6 5.1C7.2 40.9 14.9 46.5 24 46.5z"/>
        </svg>
        Continue with Google
      </button>
      <div class="divider">or continue with email</div>
      <div class="form-group">
        <label for="login-email">Email</label>
        <input type="email" id="login-email" name="email" placeholder="email@exemple.com" required />
      </div>
      <div class="form-group">
        <label for="login-pw">Password</label>
        <input type="password" id="login-pw" name="password" placeholder="••••••••••••" required />
      </div>
      <div class="forgot-link"><a href="#">Forgot password?</a></div>
      <button class="btn-submit" type="submit">Sign in to Finzo</button>
      </form>
      <div class="form-footer">
        Don't have an account? <a href="#" onclick="showPage('register'); return false;">Create one free →</a>
      </div>
    </div>
    <!-- Right: logo -->
    <div class="logo-panel">
      <div class="logo-icon"><span>$</span></div>
      <h2>Finzo</h2>
      <p>Track every dirham and never miss a budget limit again.</p>
    </div>
  </div>

  <script>
    function showPage(page) {
      document.querySelectorAll('.auth-card').forEach(c => c.classList.remove('active'));
      document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
      document.getElementById(page).classList.add('active');
      document.querySelectorAll('.tab-btn').forEach(b => {
        if (b.textContent.toLowerCase().includes(page === 'register' ? 'reg' : 'sign')) b.classList.add('active');
      });
      // fix tab labels
      const tabs = document.querySelectorAll('.tab-btn');
      tabs[0].classList.toggle('active', page === 'register');
      tabs[1].classList.toggle('active', page === 'login');
    }
  </script>
</body>
</html>