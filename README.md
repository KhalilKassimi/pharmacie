# PharmaPlus - Pharmacy Management System

PharmaPlus is a web-based application designed to streamline pharmacy operations. It provides a comprehensive suite of tools for managing inventory, sales, customers, and suppliers, all from a user-friendly dashboard.

## ✨ Features

-   **Dashboard**: Get a quick overview of key metrics, including total medicines, low-stock alerts, pending orders, and daily revenue.
-   **Inventory Management**: Add, edit, and track medicines. Set minimum stock levels to receive low-stock notifications.
-   **Sales Management**: Process sales, view sales history, and manage sale details.
-   **Client Management**: Keep a record of customer information.
-   **Supplier Management**: Manage supplier details and track orders.
-   **Order Management**: Create and manage purchase orders from suppliers.
-   **User Management**: Role-based access control with different permissions for administrators and pharmacists.
-   **Security**: Includes features to track login attempts for enhanced security.

## 💻 Technologies Used

-   **Backend**: PHP
-   **Database**: MySQL
-   **Frontend**: HTML, CSS, JavaScript

## 🚀 Getting Started

Follow these instructions to set up the project on your local machine.

### Prerequisites

-   A local web server environment (e.g., XAMPP, WAMP, MAMP)
-   PHP
-   MySQL

### Installation

1.  **Clone the repository** to your web server's root directory (e.g., `htdocs` in XAMPP).
    ```sh
    git clone https://github.com/your-username/your-repo-name.git
    ```
2.  **Start your web server** (Apache and MySQL).
3.  **Run the setup script** by navigating to `http://localhost/pharma/setup.php` in your browser. This will create the `pharmacies_db` database and all necessary tables.
4.  After the setup is complete, you can delete or rename the `setup.php` file for security.

### Usage

1.  Navigate to the application's login page at `http://localhost/pharma/login.php`.
2.  Log in with the default administrator credentials:
    -   **Username**: `admin`
    -   **Password**: `admin123`
3.  You will be redirected to the main dashboard, where you can start managing your pharmacy.
