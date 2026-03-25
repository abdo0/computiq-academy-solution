# Real Estate Management System

<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Real Estate Management System

A comprehensive real estate management system built with Laravel and Filament PHP. This application provides essential real estate functionality including property management, client management, project tracking, and organizational structure.

### Key Features

- 🏠 **Property Management**: Complete property and client information management
- 👥 **Client Management**: Client and property relationship management
- 🏢 **Organizational Structure**: Branch, department, and user management
- 📋 **Project Tracking**: Project and task management for real estate operations
- 🌍 **Multi-Language**: Support for English, Arabic, and Kurdish
- 🛡️ **Role-Based Access**: Comprehensive permission system
- 📱 **Responsive Design**: Mobile-friendly interface
- 💬 **Real-time Chat**: Team communication system

## Quick Start

### Installation

```bash
# Clone the repository
git clone <repository-url> project-system
cd project-system

# Install dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Setup database
php artisan migrate --fresh --seed --force

# Build assets
npm run build

# Start the application
php artisan serve
```

## Architecture

### Database Design

The Project Management System uses a single database architecture:

- **Single Database**: Stores all application data including users, projects, tasks, and system configuration
- **Standard Laravel**: Uses Laravel's standard migration and database management
- **Simple Setup**: Easy installation and maintenance

### Database Structure

```
Database
├── users              # System users  
├── clients            # Client information
├── properties         # Property records
├── branches           # Branch information
├── departments        # Department information
├── projects           # Real estate projects
├── tasks              # Project tasks
├── countries          # Country reference data
├── currencies         # Currency reference data
├── genders            # Gender reference data
├── marital_statuses   # Marital status reference data
├── nationalities      # Nationality reference data
└── ... (other reference tables)
```

## Documentation

- 🌍 **[Translation System](TRANSLATION_SYSTEM.md)** - Multi-language implementation guide
- 🧪 **[Testing Guide](tests/README.md)** - Testing procedures and guidelines

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com/)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development/)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).