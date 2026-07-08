# Brevo PrestaShop Plugin

PrestaShop plugin for seamless integration with Brevo (formerly Sendinblue) marketing platform. Supports PrestaShop 1.7.x, 8.x, and 9.x.

## Tech Stack

- **Language**: PHP 5.6+
- **Framework**: PrestaShop Module System
- **Database**: MySQL/MariaDB (via PrestaShop)
- **Testing**: PHPUnit 5.7
- **Frontend**: JavaScript, HTML, CSS
- **Key Dependencies**: 
  - ext-json, ext-curl, ext-pdo, ext-mysqli
  - PrestaShop Core APIs
- **External Integrations**:
  - Brevo Integration Microservice
  - Brevo Connector Integration
  - Brevo API (v3)

## Getting Started

### Prerequisites

- Docker and Docker Compose
- PHP 5.6+ with extensions: json, curl, pdo, mysqli
- Composer

### Setup

```bash
# Start PrestaShop and MariaDB containers
docker-compose up -d

# For macOS with M1 chip:
docker-compose -f docker-compose.yml -f docker-compose.mac-m1.yml up -d

# Install dependencies
composer install

# Access PrestaShop
# Main URL: http://localhost:82
# Install: http://localhost:82/install-local
# Admin: http://localhost:82/admin-local
```

### Install Plugin

Inside the `prestashop` container:

```bash
# Install
bin/console prestashop:module install sendinblue

# Uninstall
bin/console prestashop:module uninstall sendinblue

# Clear cache if needed
rm -Rf var/cache

# Clear logs if needed
rm -Rf var/logs
```

## Development Workflow

### Build

Production and staging builds:
```bash
make
```

Development build:
```bash
make build_dev developer=yourname
```

This creates versioned ZIP archives:
- `PS17_Sendinblue_v{version}_PRODUCTION.zip`
- `PS17_Sendinblue_v{version}_STAGING.zip`
- `PS17_Sendinblue_v{version}_BETA.zip`
- `PS17_Sendinblue_v{version}_DEVELOPER_{developer}.zip`

### Test

```bash
composer run tests
```

Or run inside container:
```bash
docker-compose exec -T prestashop ./modules/sendinblue/vendor/phpunit/phpunit/phpunit -c ./modules/sendinblue/phpunit.xml --coverage-text
```

### Lint/Format

No automated linting configured. CI runs DTSL quality checks via GitHub Actions.

## Project Structure

```
classes/              # Core PHP classes with PSR-4 autoloading
  webservice/         # Web service API implementations
controllers/          # PrestaShop controllers
services/             # Business logic services
  ConfigService.php   # Environment and configuration management
  ApiClientService.php # Brevo API client
  WebserviceService.php # REST API exposure
hooks/                # PrestaShop hook handlers
factories/            # Object factories
models/               # Data models
views/                # Templates (Smarty)
translations/         # i18n files
upgrade/              # Version upgrade scripts
tests/                # PHPUnit tests
  Unit/               # Unit tests
  mock/               # Test mocks
```

## Code Conventions

### Naming
- **Files**: PascalCase for classes (`CustomerService.php`)
- **Classes**: PascalCase with namespaces (`Sendinblue\Services\CustomerService`)
- **Methods**: camelCase
- **Constants**: UPPER_SNAKE_CASE

### PrestaShop Hooks

Register hooks in `sendinblue.php`:
```php
const SENDINBLUE_HOOKS = [
    'displayHeader',
    'actionCustomerAccountAdd',
    'actionOrderStatusUpdate',
    // ...
];
```

Implement in `factories/HooksFactory.php` or dedicated hook classes.

### Error Handling
- Use PrestaShop's error handling mechanisms
- Log errors appropriately for debugging
- Return proper HTTP status codes in web service responses

## Web Service API

The plugin exposes REST endpoints via PrestaShop's web service system:

- **Test Connection**: `/api/testConnection`
- **Configuration**: `/api/getConfiguration`, `/api/updateConfiguration`
- **Products**: `/api/getProducts`, `/api/getProductsCount`
- **Customers**: `/api/getCustomers`, `/api/getCustomersCount`
- **Orders**: `/api/getOrders`, `/api/getOrdersCount`
- **Categories**: `/api/getCategories`, `/api/getCategoriesCount`
- **Newsletter**: `/api/getNewsletterRecipients`, `/api/subscribe`, `/api/unsubscribe`

Access via `addWebserviceResources` hook in `WebserviceService.php`.

## Integration Points

### Brevo Microservices
- **Integration Portal**: `app.brevo.com/integrations` (production)
- **Plugin API**: `plugin.brevo.com/integrations/api` (production)
- **Brevo API**: `api.brevo.com` (production)
- **Marketing Automation**: `in-automate.brevo.com`, `sibautomation.com`

Environment-specific URLs are configured during build (see makefile).

### Event Tracking
- Customer lifecycle events (add, update)
- Order lifecycle (create, update, cancel, refund)
- Cart events (save, delete)
- Product changes (add, update, delete, stock changes)
- Category changes
- Newsletter subscriptions

## AI Boundaries

**Never Modify Without Review**:
- `services/ConfigService.php` - Environment URL configurations modified during build
- `services/ApiClientService.php` - Brevo API integration endpoints
- `upgrade/` - Database schema changes and version migrations
- Web service API contracts in `classes/webservice/` - Breaking changes affect microservice integration
- PrestaShop hook registrations in `sendinblue.php` - Core integration points

**Requires Manual Testing**:
- Event tracking hooks - Verify data sync with Brevo platform
- Order and customer synchronization logic
- SMTP configuration and email delivery
- Web service endpoints - Test with integration microservice

**Use Caution**:
- Build process in `makefile` - URL replacement logic is environment-specific
- Authentication and API key handling
- Customer data processing - Privacy and GDPR compliance
- Database queries - Performance implications for large stores

## Common Tasks

### Adding a New Hook

1. Add hook name to `SENDINBLUE_HOOKS` in `sendinblue.php`
2. Implement handler in `HooksFactory.php` or create new hook class
3. Call appropriate service method
4. Add tests in `tests/Unit/`

### Adding a Web Service Endpoint

1. Create method in `WebserviceService.php`
2. Register in `addWebserviceResources` hook
3. Implement business logic in appropriate service class
4. Document endpoint in README.md

## Troubleshooting

### Cache Issues
After installing/uninstalling the module, clear cache:
```bash
rm -Rf var/cache var/logs
```

### Docker Issues
Rebuild containers:
```bash
docker-compose down
docker-compose up -d --build
```

### Composer Autoload Issues
Regenerate autoload:
```bash
composer dump-autoload
```
