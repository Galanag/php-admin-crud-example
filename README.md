# php-admin-crud-example
## Admin Edit Demo

A minimal, safe demonstration of an admin panel where administrators can edit **users**, **branches**, and **categories**. The code is sanitized and uses an in‑memory SQLite database with dummy data.

### Features
- **Edit User** – Update user name, role, and branch.
- **Edit Branch** – Change branch name, code, and location.
- **Edit Category** – Modify name, type (income/expense), color, and icon.
- AJAX endpoint to fetch category details for editing.
- Simple modal forms for each edit operation.

### How to Run
1. Save the code as `index.php`.
2. Run with a PHP server:
   ```bash
   php -S localhost:8000
