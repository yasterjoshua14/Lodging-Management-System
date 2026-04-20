# Lodging Management System

A midterm-ready Lodging Management System built with CodeIgniter 4 using the MVC pattern. The project includes authentication plus working CRUD modules for rooms, tenants, and bookings.

## Features

- User registration, login, and logout
- Dashboard with room, tenant, and booking summaries
- Room management with type, capacity, nightly price, and status
- Tenant management with identity and emergency contact details
- Booking management with room assignment, stay dates, status, and total amount
- Booking overlap validation to prevent duplicate active stays for one room

## Project Structure

- `app/Controllers` contains MVC controllers for authentication and modules
- `app/Models` contains the database models
- `app/Views` contains the shared layout and module pages
- `app/Filters` contains route protection for guest-only and authenticated pages
- `app/Database/Migrations` contains the database schema
- `app/Database/Seeds/LodgingSeeder.php` contains demo data
- `docs/system-proposal.md` contains the project proposal
- `docs/erd.md` contains the ERD in Mermaid format

## Setup

1. Update these values in `.env` if not configure:

```ini
CI_ENVIRONMENT = development
app.baseURL = 'http://localhost:8080/'

database.default.hostname = localhost
database.default.database = lodging_management
database.default.username = root
database.default.password =
database.default.DBDriver = MySQLi
database.default.port = 3306
```

3. Create the database in MySQL or MariaDB.
4. Run the migrations:

```bash
php spark migrate
```

5. Optionally load demo data:

```bash
php spark db:seed LodgingSeeder
```

6. Start the development server:

```bash
php spark serve
```

## Customer Account

To ran customer account go to http://localhost:8080, use the following account:

- Email: `maria@example.com`
- Password: `password123`

## Admin Account

To ran admin account type http://localhost:8080/admin, use the following account or create your own account under log in page.

- Email: `admin@lodging.test`
- Password: `password123`

## Requirements

- PHP 8.2+
- MySQL / MariaDB
- PHP extensions: `intl`, `mbstring`, `mysqli`
