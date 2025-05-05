<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## Laravel 11 + Vue 3 + Bootstrap 5 CRUD Application

This repository contains a basic CRUD application built with Laravel 11, Vue 3, and Bootstrap 5. This guide will walk you through the installation process to set up the project.

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

-   [Simple, fast routing engine](https://laravel.com/docs/routing).
-   [Powerful dependency injection container](https://laravel.com/docs/container).
-   Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
-   Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
-   Database agnostic [schema migrations](https://laravel.com/docs/migrations).
-   [Robust background job processing](https://laravel.com/docs/queues).
-   [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Installation Steps

1.  Clone the Repository
    First, clone the repository to your local machine:

        git clone https://github.com/Sajid-al-islam/employee_management.git
        cd employee_management

2.  Install PHP Dependencies
    Run the following command to install all PHP dependencies:

        composer install

3.  Install JavaScript Dependencies
    Next, install the JavaScript dependencies, including Vue 3 and Bootstrap 5:

        npm install

4.  Configure the Environment
    Copy the example .env.example file to create your own .env file:
    cp .env.example .env
    Open the .env file and update the following lines with your database credentials and other configuration:

        DB_CONNECTION=mysql
        DB_HOST=127.0.0.1
        DB_PORT=3306
        DB_DATABASE=your_database_name
        DB_USERNAME=your_database_user
        DB_PASSWORD=your_database_password

5.  Generate Application Key
    To ensure your application’s security, you need to generate an application key:

        php artisan key:generate

6.  Run Database Migrations
    Run the following command to create the necessary database tables:

        php artisan migrate --seed

7.  Compile Frontend Assets
    Compile the frontend assets (CSS and JavaScript) using Vite:

        npm run dev

    For production:

        npm run build

8.  Serve the Application
    You can now run the application locally using the following command:

        php artisan serve

By default, the application will be accessible at http://127.0.0.1:8000.

9.  Vue 3 Setup
    Vue 3 is already integrated into the project using vite for asset bundling. Vue files are located in the resources/js directory.
    Make sure your resources/js/app.js looks like this:

         import { createApp } from 'vue';
         import App from './components/App.vue';

// Import Bootstrap CSS

        import 'bootstrap/dist/css/bootstrap.min.css';

// Optional: Import Bootstrap JS (for features like modals and tooltips)

    import 'bootstrap/dist/js/bootstrap.bundle.min.js';
    createApp(App).mount('#app');

The entry point for Vue components is in the Blade template located in resources/views/welcome.blade.php:

    <div id="app"></div>

Make sure the @vite directive is present in welcome.blade.php:

    @vite(['resources/css/app.css', 'resources/js/app.js'])

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

-   **[Vehikl](https://vehikl.com/)**
-   **[Tighten Co.](https://tighten.co)**
-   **[WebReinvent](https://webreinvent.com/)**
-   **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
-   **[64 Robots](https://64robots.com)**
-   **[Curotec](https://www.curotec.com/services/technologies/laravel/)**
-   **[Cyber-Duck](https://cyber-duck.co.uk)**
-   **[DevSquad](https://devsquad.com/hire-laravel-developers)**
-   **[Jump24](https://jump24.co.uk)**
-   **[Redberry](https://redberry.international/laravel/)**
-   **[Active Logic](https://activelogic.com)**
-   **[byte5](https://byte5.de)**
-   **[OP.GG](https://op.gg)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
