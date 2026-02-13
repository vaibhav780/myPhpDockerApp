# SQL Injection Demo Application Structure

- **login.php**: Intentionally vulnerable login form (for SQL injection demonstration)
- **dashboard.php**: Protected dashboard page (requires login)
- **logout.php**: Ends session and redirects to login
- **db.php**: SQLite DB setup and demo user creation
- **sqlinj_test.php**: Vulnerable login form for SQL injection testing

## How to Use

1. Open `sqlinj/login.php` to log in (admin/password or use SQL injection)
   - You can use SQL injection in `login.php` (e.g., username: `admin' -- ` and any password) to bypass authentication. This is intentionally vulnerable for demonstration.
2. After login, access `dashboard.php` and use `logout.php` to log out
3. For SQL injection testing, use `sqlinj_test.php`:
   - Try username: `admin' -- ` and any password to bypass login
   - The SQL in this form is intentionally vulnerable for demonstration

## Database
- SQLite DB file: `sqlinj/test.db` (auto-created)
- Table: `users` (fields: id, username, password)

**Warning:** This is for educational/demo purposes only. Do not use in production.
