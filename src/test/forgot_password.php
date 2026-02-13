<!DOCTYPE html>
<html>
<head><title>Forgot Password</title></head>
<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <p>Enter your email to receive a password reset link.</p>
        <form method="POST">
            <label for="email">Email</label>
            <input type="text" name="email" id="email" required>
            <button type="submit" id="form_submit">Retrieve password</button>
        </form>
        <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p id="message" style="color: green;">Internal Server Error: Email service not configured, but form submitted!</p>
        <?php endif; ?>
    </div>
</body>
</html>