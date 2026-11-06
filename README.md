# Relay

**An Internal IT Service & Repair Ticketing Management System**

Relay is a web-based application designed to streamline IT device servicing and repair operations through a centralized ticketing system.

Built with Laravel, Relay helps service teams manage customers, devices, repair requests, technician assignments, and service progress within a structured workflow.

## Overview

Relay provides an internal workspace for administrators and technicians to manage the complete lifecycle of IT service requests, from device intake and diagnosis to repair completion and handover.

Each service request is represented by a unique ticket, allowing the team to track its status, assigned technician, and activity history.

The system is intended for internal service operations. Customers do not require an account to use the service.

## Key Features

- **Role-Based Access Control** — Separate access and interfaces for administrators and technicians.
- **Dashboard** — Role-specific overview of service operations and assigned tickets.
- **Customer Management** — Create, update, search, and manage customer records.
- **Device Management** — Register customer devices and maintain their information.
- **Ticket Categories** — Organize service requests by issue or service type.
- **Service Ticket Management** — Create, assign, update, filter, and track service tickets.
- **Automatic Ticket Numbering** — Generate unique ticket identifiers such as `RLY-2026-0001`.
- **Technician Workspace** — View assigned tickets and their details.
- **Activity History** — Record ticket creation, assignment, and status changes.

### Planned Features

- Technician diagnosis and repair notes
- Service status updates
- Customer approval and repair cost estimation
- Service costs and payment tracking
- Device handover records
- Reports and analytics

## Service Workflow

Relay follows a structured ticket lifecycle:

1. **Received** — The administrator registers the customer, device, and reported issue.
2. **Diagnosing** — The assigned technician inspects the device and identifies the problem.
3. **Awaiting Approval** — The service estimate awaits customer confirmation when required.
4. **Repairing** — The technician performs the approved repair.
5. **Ready for Pickup** — The repaired device is ready to be returned.
6. **Completed** — The service is finalized and the ticket is closed.

Additional statuses include **On Hold** for temporarily paused work and **Cancelled** for discontinued requests.

## User Roles

### Administrator

Administrators manage service operations, including customer and device records, ticket categories, ticket creation, technician assignments, and ticket monitoring.

### Technician

Technicians access their assigned service tickets, review device information, and follow the progress of repair requests. Diagnosis, repair notes, and status management are part of the planned technician workflow.

## Tech Stack

| Technology | Purpose |
|---|---|
| Laravel 12 | Backend framework |
| PHP | Server-side development |
| MySQL | Relational database |
| Blade | Server-rendered views |
| Tailwind CSS 4 | User interface styling |
| Alpine.js | Lightweight frontend interactivity |
| Vite | Frontend asset bundling |
| Chart.js | Planned analytics visualization |

## Interface Design

Relay uses a modern, minimal dashboard interface with a dark visual theme.

The design combines charcoal backgrounds, slate accents, lime-green highlights, rounded components, and compact icon-based navigation to provide a consistent workspace for service operations.

The administrator and technician interfaces are tailored to their respective responsibilities.

## Installation

### Prerequisites

Make sure the following are installed:

- PHP and Composer
- Node.js and npm
- MySQL
- Git

### 1. Clone the repository

```bash
git clone https://github.com/arsanara11/relay-it-ticketing.git
cd relay-it-ticketing
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Configure the environment

Create an environment configuration file:

**Windows (Command Prompt):**

```bash
copy .env.example .env
```

**macOS / Linux:**

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 4. Configure the database

Create a MySQL database named `relay_db`, then update the database configuration in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=relay_db
DB_USERNAME=root
DB_PASSWORD=
```

Adjust the credentials according to your local environment.

### 5. Run migrations

```bash
php artisan migrate
```

### 6. Seed initial data

If the database seeders are configured, run:

```bash
php artisan db:seed
```

Demo administrator and technician accounts may require additional environment configuration, depending on the project's seeder setup.

### 7. Start the application

Run the Laravel development server:

```bash
php artisan serve
```

In a separate terminal, start Vite:

```bash
npm run dev
```

Open the application at:

```text
http://127.0.0.1:8000
```

## Project Structure

```text
app/
├── Http/
│   └── Controllers/
│       ├── Admin/
│       └── Technician/
├── Models/
└── Services/

database/
├── migrations/
└── seeders/

resources/
├── css/
├── js/
└── views/
    ├── admin/
    ├── auth/
    ├── components/
    ├── layouts/
    └── technician/

routes/
└── web.php
```

## Collaboration

Relay is developed as a collaborative academic project.

Contributors are encouraged to work on separate feature branches and submit pull requests before merging changes into the `main` branch.

Example:

```bash
git switch main
git pull origin main
git switch -c feature/your-feature
```

After implementing and testing the feature:

```bash
git add .
git commit -m "feat: implement your feature"
git push -u origin feature/your-feature
```

Create a pull request on GitHub for review and integration.

## Development Status

**In Active Development**

The project currently includes the core administrative management modules, service ticket management, and the initial technician workspace.

Additional technician workflows, billing functionality, and reporting features are planned for upcoming development stages.

## License

No license has been specified for this repository.

---

**Relay** — *A more organized way to manage IT service operations.*