

# Task Management System

This project is a Task Management System built with: 
 - **Laravel 10**
 - **PostgreSQL**
 - **Docker**
 - **PEST**
 - **Laravel Pint**
 - **Laravel UI**
 - Service-Repository Pattern (Route -> Controller -> Service -> Repository -> Model)

## Features

-  Complete Task CRUD with states and priorities
-  Filtering by status, priority and categories
-  Sorting by task properties
-  Task categorization with many-to-many relationships
-  User authentication with Laravel UI
-  Notification system for task creation
-  Responsive interface with Bootstrap
-  Testing with PEST framework

## System Requirements

- Docker
- Docker compose

## Installation

### 1. Clone the Repository
```bash
git clone git@github.com:dantondelima/task-management-system.git
cd task-management-system
```

### 2. Give permission for files
```bash
chmod +x setup.sh 
chmod +x docker/app/docker-entrypoint.sh
```
### 3. Run setup script
```bash
./setup.sh
```

The application will be available at: **http://localhost:9005**

## Additional Configuration

### Mailpit Container (to receive e-mails locally)

```bash
docker compose -f docker/mailpit/docker-compose.yml up -d
```
The e-mail inbox will be available at: **http://localhost:8025**
### Queue Configuration (Optional)
For notification processing:
```bash
docker compose exec app sh -c "php artisan queue:listen"
```

## Test

### Run Tests
```bash
docker compose exec app sh -c "php artisan test"
```

## Lint
### Run Laravel Pint
```bash
docker compose exec app sh -c "./vendor/bin/pint"
```

## Main Features

### Task Management
- **States**: Pending, In Progress, Completed, Cancelled
- **Priorities**: Low, Medium, High, Urgent
- **Categories**: Group tasks by categories
- **Due dates**

### Categories Management
- **Categories** CRUD

### Notification System (e-mail)
- Automatic notifications for task creation
- Future support for other task-related notifications

