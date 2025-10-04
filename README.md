# Prady Tec AppMarket

A comprehensive e-commerce platform for selling mobile applications (AAB and APK files) built with PHP.

## Features

### User Features
- **User Registration & Authentication**: Secure user registration and login system
- **App Catalog**: Browse apps by category with search and filtering
- **App Details**: Detailed app information with screenshots and reviews
- **Shopping Cart**: Add apps to cart and manage purchases
- **Checkout Process**: Secure checkout with multiple payment options
- **User Dashboard**: Manage purchased apps, orders, and account settings
- **Download Management**: Download purchased apps with download tracking
- **Reviews & Ratings**: Rate and review purchased apps

### Admin Features
- **Admin Dashboard**: Overview of platform statistics and recent activity
- **App Management**: Add, edit, and delete apps with file upload support
- **Category Management**: Organize apps into categories
- **Order Management**: View and manage customer orders
- **User Management**: Manage user accounts and permissions
- **Review Management**: Moderate app reviews and ratings

### Technical Features
- **Responsive Design**: Mobile-friendly interface using Bootstrap 5
- **File Upload**: Support for APK and AAB file uploads
- **Database Integration**: MySQL database with PDO
- **Security**: Password hashing, input sanitization, and SQL injection prevention
- **Session Management**: Secure user sessions
- **Payment Integration**: Ready for PayPal and Stripe integration

## Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependency management)

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd prady-tec-appmarket
   ```

2. **Database Setup**
   - Create a MySQL database named `prady_tec_appmarket`
   - Update database credentials in `config/database.php`
   - The database tables will be created automatically on first run

3. **File Permissions**
   ```bash
   chmod 755 uploads/
   chmod 755 uploads/apps/
   ```

4. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure PHP extensions are enabled: PDO, PDO_MySQL, fileinfo

5. **Access the Application**
   - Open your browser and navigate to the application URL
   - The first user to register will need to be manually promoted to admin

## Database Schema

### Tables
- **users**: User accounts and authentication
- **categories**: App categories
- **apps**: App information and metadata
- **orders**: Customer orders
- **order_items**: Individual items in orders
- **reviews**: App reviews and ratings
- **user_downloads**: Download tracking

## Configuration

### Database Configuration
Edit `config/database.php` to set your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'prady_tec_appmarket');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### File Upload Configuration
- App files are stored in `uploads/apps/`
- Maximum file size is determined by PHP settings
- Supported file types: .apk, .aab

## Usage

### For Users
1. Register an account or log in
2. Browse apps by category or search
3. Add apps to cart
4. Complete checkout process
5. Download purchased apps from dashboard

### For Admins
1. Log in with admin credentials
2. Access admin panel from user menu
3. Add new apps with file uploads
4. Manage categories, orders, and users
5. Monitor platform statistics

## Security Features

- Password hashing using PHP's `password_hash()`
- Input sanitization and validation
- SQL injection prevention with prepared statements
- XSS protection with `htmlspecialchars()`
- File upload validation
- Session security

## Payment Integration

The platform is ready for payment gateway integration:
- PayPal integration
- Stripe integration
- Demo payment mode for testing

## File Structure

```
prady-tec-appmarket/
├── admin/                 # Admin panel pages
├── assets/               # CSS, JS, and other assets
├── config/               # Configuration files
├── includes/             # Shared PHP files
├── pages/                # User-facing pages
├── uploads/              # File uploads directory
├── index.php             # Homepage
└── README.md             # This file
```

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and questions, please contact the development team or create an issue in the repository.

## Changelog

### Version 1.0.0
- Initial release
- User authentication system
- App catalog and management
- Shopping cart and checkout
- Admin panel
- File upload system
- Reviews and ratings
- Download management

## Roadmap

- [ ] Payment gateway integration
- [ ] Email notifications
- [ ] Advanced search filters
- [ ] App analytics
- [ ] Multi-language support
- [ ] API endpoints
- [ ] Mobile app
- [ ] Advanced admin features

## Credits

Developed by Prady Tec Team for mobile app distribution and sales.
