# 5-Minute Viva Sheet

## Organic Farming E-Commerce and Advisory Platform

### 1. One-Line Intro
This is a full-stack PHP + MySQL web application for organic product ordering, order tracking, support messaging, and farming chatbot guidance.

### 2. Why This Project?
- Static websites cannot handle secure login, persistent orders, or real transaction workflows.
- This project solves that with a normalized database and backend business logic.

### 3. Core Modules
1. Register/Login/Logout
2. Product Catalog + Filter
3. Cart + Checkout
4. Order Success + History
5. Contact Support
6. Chatbot + Chat Logs
7. DB Health Diagnostics

### 4. Tech Stack
- Frontend: PHP-rendered HTML, CSS, JavaScript
- Backend: PHP (PDO)
- Database: MySQL/MariaDB
- Environment: XAMPP

### 5. Most Important DB Tables
- `users`
- `products`
- `orders`
- `order_items`
- `contact_messages`
- `chat_logs`

### 6. Why DB Connectivity Is Important
- Enables persistent storage.
- Without DB, no login, no products, no orders, no history.

### 7. Frontend vs Backend
- Frontend: UI and user interaction.
- Backend: validation, business logic, session handling, DB operations.

### 8. Security Points (Say These in Viva)
1. Password hashing (`password_hash`, `password_verify`)
2. CSRF token validation on POST forms
3. Session-based route protection
4. Prepared statements against SQL injection

### 9. Checkout Transaction Logic
- Start transaction
- Insert order
- Insert order items
- Decrease stock
- Commit on success
- Rollback on failure

### 10. Normalization (Quick)
- 1NF: Atomic fields
- 2NF: Full dependency on keys
- 3NF: No transitive dependency

### 11. Testing Summary
- Manual functional tests
- UI interaction tests
- Security negative tests
- Automation for chatbot logic (`tests/chatbot_engine_test.php`)

### 12. Common Viva Questions and One-Line Answers
- Why MySQL? Reliable relational design with constraints and transactions.
- Why separate `orders` and `order_items`? Header-line decomposition avoids redundancy.
- What if DB fails? App fails gracefully and diagnostics are available via `db_health.php`.
- Why prepared statements? Prevent SQL injection.
- Why CSRF token? Prevent unauthorized form submission from third-party pages.

### 13. Final Conclusion Line
This project applies SDLC and DBMS concepts to deliver a secure, normalized, testable, and user-friendly organic commerce platform.
