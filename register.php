<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register - ShopSphere</title>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Helvetica Neue', 'Arial', sans-serif; background: #0a0a0a; min-height: 100vh; display: flex; flex-direction: column; }
    .header { background: #000; color: #fff; padding: 25px 0; text-align: center; border-bottom: 1px solid #222; }
    .header h1 { font-size: 2em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; margin-bottom: 5px; }
    .header p { font-size: 0.9em; color: #999; letter-spacing: 2px; text-transform: uppercase; font-weight: 300; }
    .register-form { max-width: 650px; margin: 60px auto; padding: 60px 70px; background: linear-gradient(135deg, #111 0%, #1a1a1a 100%); border: 1px solid #333; box-shadow: 0 10px 40px rgba(0,0,0,0.5); }
    .form-title { text-align: center; margin-bottom: 50px; color: #fff; font-size: 1.8em; font-weight: 300; letter-spacing: 4px; text-transform: uppercase; }
    .form-group { margin-bottom: 30px; position: relative; }
    label { display: block; margin-bottom: 12px; color: #aaa; font-weight: 400; font-size: 11px; text-transform: uppercase; letter-spacing: 2.5px; }
    input[type="text"], input[type="email"], input[type="password"] { width: 100%; padding: 16px 0; border: none; border-bottom: 2px solid #333; box-sizing: border-box; font-size: 16px; background: transparent; color: #fff; transition: all 0.3s ease; font-weight: 300; }
    input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus { outline: none; border-bottom-color: #888; }
    input[type="text"]::placeholder, input[type="email"]::placeholder, input[type="password"]::placeholder { color: #444; }
    .btn { background: transparent; border: 2px solid #555; color: #fff; padding: 16px 20px; cursor: pointer; width: 100%; font-size: 12px; font-weight: 500; transition: all 0.4s ease; text-transform: uppercase; letter-spacing: 3px; margin-top: 15px; border-radius: 2px; }
    .btn:hover { background: #fff; color: #000; border-color: #fff; transform: translateY(-1px); box-shadow: 0 5px 20px rgba(255,255,255,0.2); }
    .error { background: linear-gradient(135deg, #1a0000 0%, #2a0000 100%); border-left: 3px solid #ff4444; color: #ffaaaa; padding: 16px 20px; margin-bottom: 30px; font-weight: 300; font-size: 13px; letter-spacing: 0.5px; border-radius: 2px; }
    .links { text-align: center; margin-top: 30px; padding-top: 30px; border-top: 1px solid #222; }
    .links a { color: #999; text-decoration: none; font-weight: 300; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; transition: all 0.3s ease; }
    .links a:hover { color: #fff; }
    .password-requirements { margin-top: 15px; padding: 15px; background: rgba(0,0,0,0.3); border-radius: 4px; border-left: 2px solid #333; }
    .requirement { padding: 6px 0; transition: all 0.3s ease; font-size: 12px; display: flex; align-items: center; }
    .requirement.valid { color: #6fd96f; }
    .requirement.invalid { color: #777; }
    .requirement::before { content: '✗ '; margin-right: 10px; font-weight: bold; font-size: 14px; }
    .requirement.valid::before { content: '✓ '; color: #6fd96f; }
    .password-strength { margin-top: 12px; height: 4px; background: #1a1a1a; border-radius: 3px; overflow: hidden; box-shadow: inset 0 1px 3px rgba(0,0,0,0.5); }
    .password-strength-bar { height: 100%; width: 0%; transition: all 0.4s ease; border-radius: 3px; }
    .password-strength-bar.weak { width: 33%; background: linear-gradient(90deg, #ff4444 0%, #ff6b6b 100%); }
    .password-strength-bar.medium { width: 66%; background: linear-gradient(90deg, #ffa500 0%, #ffd93d 100%); }
    .password-strength-bar.strong { width: 100%; background: linear-gradient(90deg, #4caf50 0%, #6fd96f 100%); box-shadow: 0 0 10px rgba(76, 175, 80, 0.5); }
  </style>
</head>
<body>
  <div class="header">
    <h1>ShopSphere</h1>
    <p>Create Your Account</p>
  </div>

  <div class="register-form">
    <h2 class="form-title">Register</h2>

    <!-- Display error message if present -->
    <?php if (isset($_GET['error'])): ?>
      <div class="error">
        <?php echo htmlspecialchars($_GET['error']); ?>
      </div>
    <?php endif; ?>

    <!-- Form submits to process_register.php -->
    <form method="post" action="process_register.php">
      <div class="form-group">
        <label>Full Name</label>
        <input type="text" name="name" required>
      </div>

      <div class="form-group">
        <label>Email Address</label>
        <input type="email" name="email" required>
      </div>

      <div class="form-group">
        <label>Password</label>
        <input type="password" name="password" id="password" required>
        <div class="password-strength">
          <div class="password-strength-bar" id="strengthBar"></div>
        </div>
        <div class="password-requirements">
          <div class="requirement" id="req-length">At least 8 characters</div>
          <div class="requirement" id="req-uppercase">One uppercase letter</div>
          <div class="requirement" id="req-lowercase">One lowercase letter</div>
          <div class="requirement" id="req-number">One number</div>
          <div class="requirement" id="req-special">One special character (!@#$%^&*)</div>
        </div>
      </div>

      <div class="form-group">
        <label>Confirm Password</label>
        <input type="password" name="confirm_password" id="confirmPassword" required>
      </div>

      <div class="form-group">
        <input type="submit" class="btn" value="Create Account">
      </div>
    </form>

    <div class="links">
      <a href="login.php">Sign In</a>
      <span style="color:#333;">|</span>
      <a href="index.php">Back to Home</a>
    </div>
  </div>

  <script>
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const strengthBar = document.getElementById('strengthBar');
    const requirements = {
      length: document.getElementById('req-length'),
      uppercase: document.getElementById('req-uppercase'),
      lowercase: document.getElementById('req-lowercase'),
      number: document.getElementById('req-number'),
      special: document.getElementById('req-special')
    };

    const validationRules = {
      length: (pwd) => pwd.length >= 8,
      uppercase: (pwd) => /[A-Z]/.test(pwd),
      lowercase: (pwd) => /[a-z]/.test(pwd),
      number: (pwd) => /[0-9]/.test(pwd),
      special: (pwd) => /[!@#$%^&*(),.?":{}|<>]/.test(pwd)
    };

    passwordInput.addEventListener('input', function() {
      const password = this.value;
      let validCount = 0;

      // Check each requirement
      for (let rule in validationRules) {
        const isValid = validationRules[rule](password);
        if (isValid) {
          requirements[rule].classList.add('valid');
          requirements[rule].classList.remove('invalid');
          validCount++;
        } else {
          requirements[rule].classList.add('invalid');
          requirements[rule].classList.remove('valid');
        }
      }

      // Update strength bar
      strengthBar.className = 'password-strength-bar';
      if (validCount <= 2) {
        strengthBar.classList.add('weak');
      } else if (validCount <= 4) {
        strengthBar.classList.add('medium');
      } else {
        strengthBar.classList.add('strong');
      }
    });

    // Form validation on submit
    document.querySelector('form').addEventListener('submit', function(e) {
      const password = passwordInput.value;
      const confirmPassword = confirmPasswordInput.value;

      // Check all requirements
      let allValid = true;
      for (let rule in validationRules) {
        if (!validationRules[rule](password)) {
          allValid = false;
          break;
        }
      }

      if (!allValid) {
        e.preventDefault();
        alert('Password must meet all requirements.');
        return false;
      }

      // Check if passwords match
      if (password !== confirmPassword) {
        e.preventDefault();
        alert('Passwords do not match.');
        return false;
      }
    });
  </script>
</body>
</html>
