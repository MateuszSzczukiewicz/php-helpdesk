# IT Helpdesk Ticket System

A lightweight and efficient **Ticket System** designed to manage technical support requests. This project was developed using **Native PHP** (Vanilla PHP) without any frameworks, focusing on core web development concepts, security, and database relations.

## About the Project

This application serves as a communication bridge between users (reporting issues) and the IT support team (Administrators). It allows for real-time tracking of ticket status and efficient workflow management.

**Developed as a final university project.**

## Key Features

### User Panel
* **Authentication:** Secure Registration and Login system.
* **Create Tickets:** Submission form with category selection (e.g., Hardware, Software, Network).
* **Dashboard:** View personal ticket history and current status (New, In Progress, Resolved, Cancelled).
* **Management:** Ability to view ticket details and cancel tickets before they are processed.

### Admin Dashboard
* **Ticket Overview:** View all support tickets from all users in one place.
* **Status Management:** Update ticket lifecycle (New, In Progress, Resolved, Cancelled).
* **Detailed View:** Access full ticket information including author details and description.
* **User Oversight:** Differentiate between admin and user roles.

## Tech Stack

The project relies on the classic **LAMP** stack (Linux, Apache, MySQL, PHP).

* **Backend:** PHP 8+ (Procedural style with PDO Library).
* **Database:** MariaDB / MySQL (Relational Database).
* **Frontend:** HTML5, CSS3 (Clean, responsive design).
* **Environment Configuration:** `.env` file for secure credential management.
* **Security Best Practices:**
    * **Prepared Statements** (SQL Injection prevention).
    * **`password_hash()`** (Bcrypt password encryption).
    * **`htmlspecialchars()`** (XSS protection).
    * **Session-based authentication** with role-based access control.
    * **Environment variables** for sensitive data (no hardcoded credentials).

## Database Structure

The system is built on a relational database consisting of three core tables:

1.  **`users`**: Stores user credentials and roles (`admin` or `user`).
    - Fields: id, username, email, password (hashed), role, created_at
    
2.  **`categories`**: Dictionary table for issue types.
    - Fields: id, name
    - Default categories: Hardware, Software, Internet/Network, Printers, Other
    
3.  **`tickets`**: Main entity linking users to categories with ticket details.
    - Fields: id, user_id, category_id, title, description, status, created_at
    - Status values: `new`, `in_progress`, `resolved`, `cancelled`



## Installation & Setup

### Prerequisites
- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.2+
- Web server (Apache, Nginx) or PHP built-in server

### Steps

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/MateuszSzczukiewicz/php-helpdesk.git
    cd php-helpdesk
    ```

2.  **Create and configure database:**
    ```bash
    mysql -u root -p
    ```
    ```sql
    CREATE DATABASE helpdesk_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
    USE helpdesk_db;
    -- Import your database schema here
    ```

3.  **Configure Environment Variables:**
    Copy the example environment file and update it with your database credentials:
    ```bash
    cp .env.example .env
    ```
    
    Edit `.env` file with your credentials:
    ```env
    DB_HOST=localhost
    DB_NAME=helpdesk_db
    DB_USER=your_username
    DB_PASS=your_password
    ```

4.  **Run Migration (if database needs English conversion):**
    ```bash
    mysql -u your_username -p helpdesk_db < migrate_to_english.sql
    ```

5.  **Start the application:**
    
    **Option A: PHP Built-in Server (Development)**
    ```bash
    php -S localhost:8000
    ```
    
    **Option B: Apache/Nginx**
    Point your DocumentRoot to the project folder and configure virtual host.

6.  **Access the application:**
    Open your browser and navigate to:
    - `http://localhost:8000` (PHP built-in server)
    - or your configured virtual host

### Default Test Credentials

After setting up the database, you can create test users or use:

**Regular User:**
- Username: `testuser`
- Password: `test123`

**Administrator:**
- Username: `admin`
- Password: `admin123`

## Project Structure

```
php-helpdesk/
├── admin_panel.php          # Admin dashboard - view all tickets
├── admin_ticket.php         # Admin ticket management
├── create_ticket.php        # Create new ticket form
├── db.php                   # Database connection
├── env_loader.php           # Environment variable loader
├── index.php                # User dashboard
├── login.php                # Login page
├── logout.php               # Logout handler
├── register.php             # User registration
├── view_ticket.php          # View ticket details
├── style.css                # Application styles
├── .env                     # Environment configuration (not in git)
├── .env.example             # Example environment file
├── .gitignore               # Git ignore rules
└── README.md                # This file
```

## Features Overview

### User Workflow
1. Register a new account
2. Login to the system
3. Create support tickets with categories
4. View ticket status in real-time
5. Cancel tickets if needed (only when status is "New")

### Admin Workflow
1. Login with admin credentials
2. View all tickets from all users
3. Manage ticket statuses (New → In Progress → Resolved)
4. View detailed ticket information including user details

## Security Features

- **Environment Variables:** Sensitive data stored in `.env` file (excluded from version control)
- **Password Security:** Bcrypt hashing with `password_hash()`
- **SQL Injection Prevention:** PDO prepared statements
- **XSS Protection:** Output escaping with `htmlspecialchars()`
- **Session Security:** Role-based access control
- **CSRF Protection:** Form validation and session checks

## Development

This project was developed as a university final project to demonstrate:
- Core PHP programming without frameworks
- Database design and relationships
- Security best practices
- MVC-like separation of concerns
- User authentication and authorization

## License

This project is open source and available for educational purposes.

---

**Author:** Mateusz Szczukiewicz  
**Repository:** https://github.com/MateuszSzczukiewicz/php-helpdesk
