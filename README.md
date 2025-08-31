# Wishes Mini E-Commerce Store

Wishes is a mini e-commerce web application that allows users to browse products, add items to their cart or wishlist, and complete purchases. It features user and admin authentication, category browsing, order management, and a responsive, modern UI.

## Features

- User registration and login
- Admin login and dashboard
- Browse products by category and subcategory
- Add products to cart and wishlist
- Responsive shopping cart and checkout flow
- User profile management and order history
- Admin product management (add, edit, delete)
- Newsletter subscription
- FAQ and contact form

## Project Structure

- `index.html` - Main landing page and single-page app container
- `styles.css` - Main stylesheet for all pages and components
- `script.js` - JavaScript for UI interactivity and cart logic
- `userlogin.php` - User authentication (login/register)
- `admin_login.php` - Admin authentication
- `admin_dashboard.php` - Admin dashboard and management
- `browse_products.php` - Product browsing for users
- `cart.php` - Cart and checkout page (PHP version)
- `payment.php` - Payment processing page
- `my_orders.php` - User order history
- `edit_profile.php` - User profile editing
- `userprofile.php` - User dashboard
- `myroom.php` - "Design My Room" feature
- `manage_products.php`, `manage_action.php` - Admin product management
- `*.sql` - Database schema and seed data

## Setup Instructions

1. **Clone or Download the Repository**

2. **Database Setup**
   - Import the SQL files (`MiniEcommerceStore.sql`, `admin.sql`, `category.sql`, `subcategory.sql`, `products.sql`) into your MySQL server.
   - Update database credentials in PHP files if needed.

3. **Run the Application**
   - Place all files in your web server's root directory (e.g., `htdocs` for XAMPP).
   - Access `index.html` in your browser.

4. **Admin Login**
   - Use credentials from `admin.sql` to log in as admin.

## Dependencies

- PHP 7.x or higher
- MySQL
- [Font Awesome](https://fontawesome.com/)
- [Swiper.js](https://swiperjs.com/) (for carousel)
- Modern browser (for best experience)

## Screenshots

_Add screenshots of the homepage, cart, checkout, admin dashboard, etc._

## License

This project is for educational purposes.

---
