# Taskly

Taskly is a lightweight service marketplace built with PHP and SQLite. Users can publish services, discover and filter listings, contact service providers, simulate purchases, save favorites, and leave reviews.

## Features

- User registration, login, profiles, and account settings
- Service creation, editing, deletion, and image uploads
- Search and filters for category, price, and delivery time
- Favorites, internal messaging, simulated checkout, and reviews
- Admin dashboard for managing users, services, and categories

## Technology

- PHP with PDO
- SQLite
- HTML, CSS, and vanilla JavaScript
- No framework or package manager is required

## Run locally

### Requirements

- PHP with the `PDO_SQLite` extension enabled
- SQLite CLI (only needed to reset the database from the schema)

### Start the application

From the project root:

```bash
php -S localhost:9000
```

Open [http://localhost:9000](http://localhost:9000) in a browser.

The application uses `database/database3.db` by default. A sample database is included in the repository, so no setup is required for a first run.

To reset the database with the schema and seed data:

```bash
sqlite3 database/database3.db < database/database.sql
```

> The reset command deletes the existing tables and data before recreating them.

## Demo account

```text
Username: admin
Password: 12345678
```

## Project structure

```text
actions/      Request handlers and application actions
components/   Shared PHP components
database/     SQLite databases, schema, and data access code
docs/         Static assets and uploaded media
pages/        Application pages, JavaScript, and CSS
index.php     Application entry point
```
