# LeanCRM - Portfolio CRM REST API

A complete Laravel 12 CRM REST API built as a portfolio demonstration. This API provides comprehensive customer relationship management functionality with multi-tenant architecture, role-based permissions, and modern Laravel features.

## 🚀 Features

- **Multi-tenant Architecture**: Complete team isolation with `team_id` scoping
- **Authentication**: Laravel Sanctum for API authentication
- **Authorization**: Role-based permissions using Spatie Laravel Permission
- **Resources**: User, Company, Contact, Deal, Activity, Note management
- **Deal Pipeline**: Complete deal lifecycle with stages (prospect → qualified → proposal → won/lost)
- **Advanced Querying**: Filtering, sorting, and pagination with Spatie Query Builder
- **Event System**: Deal stage change events with listeners and jobs
- **API Documentation**: Auto-generated documentation with Scramble
- **Testing**: Comprehensive feature tests included
- **Demo Data**: Rich seeded data for testing and demonstration
- **Access Control**: Configurable read/write permissions for public demos

## 📋 Requirements

- PHP 8.2+
- Composer
- MySQL/PostgreSQL
- Laravel 12.x
- Node.js & NPM (for frontend assets, if needed)

## 🛠 Installation & Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd LeanCRM
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure database** (update `.env` file)
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=leancrm
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seed the database**
   ```bash
   php artisan migrate --seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000`

## 🗄️ Demo Data & Seeders

The application comes with comprehensive seeders that create realistic demo data for testing and demonstration purposes.

### Available Demo Users

After running `php artisan migrate --seed`, you can use these demo accounts:

| Email | Password | Role | Description |
|-------|----------|------|-------------|
| `admin@team1.com` | `password123` | Admin | Team 1 Administrator |
| `admin@team2.com` | `password123` | Admin | Team 2 Administrator |
| `demo@leancrm.com` | `demo123` | Sales Rep | Demo Sales Representative |
| `manager@leancrm.com` | `demo123` | Manager | Demo Sales Manager |

### Seeded Data Includes

- **9 Users**: Admins, demo users, and additional team members
- **30 Companies**: Realistic companies with proper contact details
- **70+ Contacts**: Individual contacts linked to companies
- **80+ Deals**: Sales opportunities in various pipeline stages ($15k-$75k range)
- **200+ Activities**: Calls, meetings, emails, tasks, and follow-ups
- **150+ Notes**: Realistic CRM notes attached to deals

### Demo Data Structure

The seeders create:
- **5 Featured Demo Companies**: TechCorp Solutions, Global Marketing Inc, Startup Ventures LLC, etc.
- **Deal Pipeline Examples**: Prospects, qualified leads, proposals, won/lost deals
- **Realistic CRM Activities**: Discovery calls, proposal presentations, contract negotiations
- **Multi-team Isolation**: Data properly separated between Team 1 and Team 2

### Running Seeders

```bash
# Seed with existing database
php artisan db:seed

# Fresh migration with seeding (recommended for demo setup)
php artisan migrate:fresh --seed

# Run specific seeder
php artisan db:seed --class=CompanySeeder
```

## 🔒 Access Control & Public Demo Mode

The API includes configurable read/write access controls, perfect for public demonstrations while maintaining security.

### Environment Configuration

Add these variables to your `.env` file:

```env
# CRM Access Control
CRM_READ_ENABLED=true
CRM_WRITE_ENABLED=true
CRM_PUBLIC_DEMO_MODE=false
```

### Configuration Options

| Variable | Default | Description |
|----------|---------|-------------|
| `CRM_READ_ENABLED` | `true` | Enable/disable GET endpoints |
| `CRM_WRITE_ENABLED` | `true` | Enable/disable POST/PUT/DELETE endpoints |
| `CRM_PUBLIC_DEMO_MODE` | `false` | Special mode for public demonstrations |

### Usage Scenarios

**For Public Demo (Read-Only)**:
```env
CRM_READ_ENABLED=true
CRM_WRITE_ENABLED=false
CRM_PUBLIC_DEMO_MODE=true
```

**For Maintenance Mode**:
```env
CRM_READ_ENABLED=false
CRM_WRITE_ENABLED=false
CRM_PUBLIC_DEMO_MODE=false
```

**For Full Access**:
```env
CRM_READ_ENABLED=true
CRM_WRITE_ENABLED=true
CRM_PUBLIC_DEMO_MODE=false
```

When access is disabled, the API returns `503 Service Unavailable` with appropriate messages:
- Demo mode: "Write operations are disabled in demo mode. This is a read-only demonstration."
- Maintenance mode: "This service is temporarily unavailable for maintenance."

## 📖 API Documentation

The API is documented using **Scramble** and is available at:
```
http://localhost:8000/docs/api
```

Scramble automatically generates interactive API documentation from your controller annotations and request classes. The documentation includes:

- **Complete API Reference**: All endpoints with detailed descriptions
- **Request/Response Examples**: Real-world examples for each endpoint
- **Authentication Guide**: How to authenticate and use Bearer tokens
- **Interactive Testing**: "Try It" feature to test endpoints directly
- **Query Parameters**: Filtering, sorting, pagination documentation
- **Validation Rules**: Complete validation requirements
- **Response Schemas**: Detailed response structure documentation

### Accessing the Documentation

1. Start your Laravel development server: `php artisan serve`
2. Visit `http://localhost:8000/docs/api` in your browser
3. Browse through the organized sections:
   - **Authentication** - Register, login, logout endpoints
   - **Deal Management** - Complete CRUD for deals with pipeline
   - **Company Management** - Client company management
   - **Contact Management** - Individual contact management
   - **Activity Management** - Tasks and activities
   - **Note Management** - Notes attached to any resource
4. Use the interactive "Try It" buttons to test endpoints
5. View real-time request/response examples

## 🔐 Authentication

### Register a new user
```bash
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password",
    "password_confirmation": "password"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password"
  }'
```

**Response:**
```json
{
  "access_token": "1|abc123...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com"
  }
}
```

## 🏢 API Examples

### List Companies
```bash
curl -X GET http://localhost:8000/api/companies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

### Create a Company
```bash
curl -X POST http://localhost:8000/api/companies \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Acme Corporation",
    "email": "contact@acme.com",
    "phone": "+1-555-0123",
    "website": "https://acme.com"
  }'
```

### Create a Deal
```bash
curl -X POST http://localhost:8000/api/deals \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Website Redesign Project",
    "description": "Complete website overhaul with modern design",
    "amount": 15000.00,
    "stage": "prospect",
    "expected_close_date": "2024-12-31"
  }'
```

### Filter and Sort Deals
```bash
# Filter by stage and sort by amount
curl -X GET "http://localhost:8000/api/deals?filter[stage]=qualified&sort=-amount" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Include related models
curl -X GET "http://localhost:8000/api/deals?include=company,contact,user" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"
```

## 🏗 Architecture

### Models & Relationships

- **User**: System users with team association
- **Company**: Client companies
- **Contact**: Individual contacts within companies
- **Deal**: Sales opportunities with pipeline stages
- **Activity**: Tasks and activities linked to any model
- **Note**: Notes that can be attached to any model

### Multi-Tenancy

All models are scoped by `team_id` to ensure complete data isolation between teams. The API automatically filters all queries by the authenticated user's team.

### Event System

When a deal's stage changes, the system triggers:

1. **DealStageChanged Event**: Captures the stage change
2. **SendDealStageNotification Listener**: Queues notification job
3. **SendDealStageChangeNotification Job**: Handles notification delivery

## 🧪 Testing

Run the comprehensive test suite to ensure everything is working correctly:

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ApiEndpointsTest.php

# Run tests with coverage (requires Xdebug)
php artisan test --coverage

# Run tests with detailed output
php artisan test --verbose
```

### Test Coverage

The test suite includes **139+ assertions** covering:

- **Authentication Flow**: Registration, login, logout, and token validation
- **CRUD Operations**: Complete create, read, update, delete for all resources
- **Multi-tenant Isolation**: Ensures teams can't access each other's data
- **Authorization**: Policy-based access control verification  
- **Query Features**: Filtering, sorting, pagination, and includes
- **Validation**: Request validation for all endpoints
- **API Response Format**: Proper JSON structure and status codes
- **Error Handling**: 401 unauthorized, 403 forbidden, 404 not found responses

### Test Results Example

```
PASS  Tests\Feature\ApiEndpointsTest
✓ test authentication endpoints                                                                                                           
✓ test companies crud endpoints                                                                                                          
✓ test contacts crud endpoints                                                                                                           
✓ test deals crud endpoints                                                                                                              
✓ test activities crud endpoints                                                                                                         
✓ test notes crud endpoints                                                                                                              
✓ test unauthorized access returns 401                                                                                                   
✓ test multi tenancy isolation                                                                                                           
✓ test filtering and sorting                                                                                                             

Tests:  9 passed
Assertions: 139 passed
Time: 2.34s
```

## 📁 Project Structure

```
app/
├── Events/
│   └── DealStageChanged.php
├── Http/
│   ├── Controllers/Api/
│   │   ├── AuthController.php
│   │   └── DealController.php
│   ├── Requests/
│   │   ├── DealStoreRequest.php
│   │   └── DealUpdateRequest.php
│   └── Resources/
│       └── DealResource.php
├── Jobs/
│   └── SendDealStageChangeNotification.php
├── Listeners/
│   └── SendDealStageNotification.php
├── Models/
│   ├── Deal.php
│   ├── Company.php
│   └── Contact.php
└── Policies/
    └── DealPolicy.php
```

## 🛡 Security Features

- **API Token Authentication**: Secure token-based authentication
- **Authorization Policies**: Fine-grained permissions for all resources
- **Multi-tenant Isolation**: Complete data separation between teams
- **Input Validation**: Comprehensive request validation
- **Rate Limiting**: Built-in Laravel rate limiting

## 🔧 Configuration

### Queue Configuration

For production, configure a proper queue driver in `.env`:

```env
QUEUE_CONNECTION=redis
# or
QUEUE_CONNECTION=database
```

### Permissions Setup

The system uses Spatie Laravel Permission for role-based access control. Default roles and permissions are set up through seeders.

## 📈 Performance Features

- **Eager Loading**: Optimized database queries with relationship loading
- **Pagination**: Built-in pagination for all list endpoints
- **Database Indexing**: Proper database indexes for team-based queries
- **Query Optimization**: Efficient filtering and sorting with Query Builder

## 🤝 Contributing

This is a portfolio project, but feedback and suggestions are welcome! Feel free to open issues or submit pull requests.

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

---

