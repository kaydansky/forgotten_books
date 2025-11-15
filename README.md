# Forgotten Books - Queue Processing System

A PHP-based web application for managing book digitization workflows and queue processing.

## Overview

Forgotten Books is a comprehensive system designed to manage the digitization process of books, including user management, work queue processing, and role-based access control for different types of workers (proofreaders, editors, coordinators, etc.).

## Features

- **User Authentication & Authorization**: Role-based access control with user registration, login, and profile management
- **Queue Management**: Book processing workflow with different stages and worker assignments
- **Worker Roles**: Support for multiple worker types including:
  - Proofreaders
  - Layout Editors
  - Image Editors
  - Cover Artists
  - Coordinators
  - Supervisors
- **Book Management**: Track books through various processing stages with metadata
- **Statistics & Reporting**: User statistics and system analytics
- **File Upload System**: Handle book files and associated resources

## Requirements

- PHP 7.4+
- MySQL/MariaDB
- Composer
- Web server (Apache/Nginx)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd forgotten_books
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Database Setup**
   - Create a MySQL database
   - Import the database schema from `Database/MySQL.sql`
   - Update database configuration in `config/config.php`

4. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure `.htaccess` is properly configured for URL rewriting

## Project Structure

```
forgotten_books/
├── config/              # Configuration files
├── Database/            # Database schema and examples
├── public/              # Public assets (CSS, JS, fonts)
├── src/                 # Application source code
│   ├── Autocomplete/    # Autocomplete functionality
│   ├── Binder/          # Data binding components
│   ├── DB/              # Database connection
│   ├── DI/              # Dependency injection
│   ├── Domain/          # Business logic modules
│   ├── Emailer/         # Email functionality
│   ├── Helpers/         # Utility classes
│   ├── Output/          # Response handling
│   └── Router/          # Request routing
├── templates/           # HTML templates
├── vendor/              # Composer dependencies
└── index.php           # Application entry point
```

## Key Components

### Domain Modules
- **Login/Register**: User authentication system
- **Queue**: Book processing workflow management
- **Users**: User management and administration
- **Statistics**: Analytics and reporting
- **Profile**: User profile management

### Dependencies
- `delight-im/auth`: Authentication library
- `delight-im/db`: Database abstraction layer
- `phpmailer/phpmailer`: Email functionality

## Configuration

Update `config/config.php` with your environment-specific settings:
- Database connection parameters
- Email server configuration
- Application-specific constants

## Usage

1. **Access the application** through your web browser
2. **Register** a new user account or login with existing credentials
3. **Navigate** through different modules based on your assigned role
4. **Process books** through the queue system according to your worker type

## Development

The application follows a modular architecture with:
- PSR-4 autoloading
- Dependency injection container
- Template-based rendering
- Role-based access control

## License

Proprietary - All rights reserved

## Author

Alexander Kaydansky - kaydansky@gmail.com