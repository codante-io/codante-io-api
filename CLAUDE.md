# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is the Codante.io API backend, a Laravel-based educational platform that provides workshops, mini-projects, and learning tracks for programming education. The platform serves as a comprehensive learning management system with features including user management, challenges, certificates, subscriptions, and content delivery.

## Development Commands

### Essential Commands

- `php artisan serve` - Start development server
- `php artisan test` - Run PHPUnit tests (configured for Feature tests in `tests/Feature/`)
- `composer run pint` or `./vendor/bin/pint` - Run Laravel Pint code formatter (PSR-12 standards)
- `php artisan migrate` - Run database migrations
- `php artisan db:seed` - Run database seeders
- `php artisan queue:work` - Process job queues
- `php artisan cache:clear` - Clear application cache

### Build & Deploy

- `composer install` - Install PHP dependencies
- `npm install` - Install Node.js dependencies (Puppeteer for browser automation)
- Deployment managed via Deployer PHP (`deploy.php`) to production server

## Architecture Overview

### Core Structure

- **Laravel 10.x** with PHP 8.1+ requirement
- **MVC Architecture** with clean separation of concerns
- **API-First Design** with Sanctum authentication
- **Multi-tenant Educational Platform** supporting workshops, challenges, and tracks

### Key Models & Domains

- **User Management**: Users, Instructors, Subscriptions, Plans
- **Content Delivery**: Workshops, Challenges, Lessons, Tracks, TrackItems
- **Community Features**: Comments, Reactions, Certificates
- **Learning Progress**: ChallengeUser, WorkshopUser, lesson completion tracking
- **Content Management**: BlogPosts, Tags, Testimonials

### Services Layer

Important service classes in `app/Services/`:

- `ChallengeRepository.php` - Challenge data operations
- `Discord.php` - Discord integration and notifications
- `ExpiredPlanService.php` - Subscription management
- `VimeoThumbnailService.php` - Video content handling

### Authentication & Authorization

- **Laravel Sanctum** for API token management
- **GitHub OAuth** integration via Laravel Socialite
- **Role-based access** with admin and user permissions
- **Backpack CRUD** for admin panel functionality

### Database Architecture

- **Polymorphic relationships** via `Trackable` interface for flexible content organization
- **Pivot tables** for many-to-many relationships (ChallengeUser, WorkshopUser, etc.)
- **Soft deletes** implemented on major entities
- **MySQL database** with comprehensive migration system

### API Design

- **RESTful API** structure with resource controllers
- **API Resources** for consistent JSON responses
- **Request validation** using Form Requests
- **Middleware-based** authentication and permission checks

## Development Guidelines

### Code Style

- Follow **PSR-12** coding standards (enforced by Laravel Pint)
- Use **strict typing**: `declare(strict_types=1)`
- Controllers and Models should be **final classes**
- Prefer **dependency injection** and service classes over fat controllers

### Key Patterns

- **Repository pattern** for data access (see `ChallengeRepository`)
- **Event-driven architecture** with Laravel Events/Listeners
- **Observer pattern** for model lifecycle management
- **Service classes** for complex business logic

### Testing

- PHPUnit configuration in `phpunit.xml`
- Focus on **Feature tests** in `tests/Feature/`
- Test database: `codante_test`
- Current test coverage includes major controllers and services

### Third-Party Integrations

- **Backpack CRUD** for admin interface
- **Pagarme** for payment processing (Brazilian market)
- **GitHub API** for repository management
- **Discord webhooks** for community notifications
- **Vimeo** for video content
- **Laravel Pulse** for application monitoring

### Package Management

- **Composer** for PHP dependencies
- Key packages: Backpack, Sanctum, Socialite, Image Intervention, Spatie packages
- **NPM** for Node.js dependencies (primarily Puppeteer)

## Important Notes

- This is a **Brazilian-focused platform** (Portuguese language in many areas)
- **Educational domain** with specific requirements for progress tracking and certification
- **Production deployment** uses Supervisor for queue management
- **Horizon** may be used for queue monitoring (check for Redis configuration)
- **Backup system** configured via Spatie Laravel Backup
