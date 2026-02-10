# Configuration Guide

## Overview

Simple HTTP uses a JSON-based configuration system to manage application settings, dependency injection, middleware, and layouts. Configuration files are located in the `etc/` directory.

## Configuration Files

### Main Configuration: concept.json

The main configuration file that includes references to other configuration files.

**Location:** `etc/concept.json`

```json
{
    "global": {
        "layouts": "@include(etc/layouts.json)"
    },
    "singularity": "@include(etc/sdi.json)",
    "middleware": "@include(etc/middleware.json)",
    "event-bus": "--@include(etc/event-bus.json)"
}
```

#### Directives

- **@include(path)**: Include and merge the specified configuration file
- **--@include(path)**: Commented include (disabled)

### Dependency Injection: sdi.json

Configures the dependency injection container (Singularity).

**Location:** `etc/sdi.json`

```json
{
    "package": {
        "concept-labs/simple-http": {
            "preference": {
                "InterfaceName": {
                    "class": "Concrete\\Implementation\\ClassName"
                }
            }
        }
    }
}
```

#### Structure

- **package**: Package-specific configurations
- **preference**: Interface-to-implementation mappings

#### Default Preferences

```json
{
    "Concept\\Http\\AppInterface": {
        "class": "Concept\\SimpleHttp\\App\\SimpleHttpApp"
    },
    "Concept\\SimpleHttp\\Request\\SimpleRequestInterface": {
        "class": "Concept\\SimpleHttp\\Request\\SimpleRequest"
    },
    "Concept\\SimpleHttp\\Handler\\SimpleHandlerInterface": {
        "class": "Concept\\SimpleHttp\\Handler\\SimpleHandler"
    },
    "Concept\\SimpleHttp\\Response\\FlusherInterface": {
        "class": "Concept\\SimpleHttp\\Response\\Flusher"
    },
    "Concept\\SimpleHttp\\Layout\\LayoutBuilderInterface": {
        "class": "Concept\\SimpleHttp\\Layout\\LayoutBuilder"
    }
}
```

#### Custom Service Configuration

Add your own services to the DI container:

```json
{
    "package": {
        "your-vendor/your-package": {
            "preference": {
                "App\\Service\\UserServiceInterface": {
                    "class": "App\\Service\\UserService"
                },
                "App\\Repository\\UserRepositoryInterface": {
                    "class": "App\\Repository\\UserRepository",
                    "arguments": {
                        "connection": "@DatabaseConnection",
                        "config": "@AppConfig"
                    }
                }
            }
        }
    }
}
```

#### Argument Injection

Use `@` prefix to inject services:

```json
{
    "App\\Handler\\MyHandler": {
        "class": "App\\Handler\\MyHandler",
        "arguments": {
            "simpleRequest": "@SimpleRequestInterface",
            "appConfig": "@AppConfigInterface",
            "userService": "@UserServiceInterface",
            "logger": "@LoggerInterface"
        }
    }
}
```

### Middleware: middleware.json

Configures the HTTP middleware stack.

**Location:** `etc/middleware.json`

```json
{
    "middleware-name": {
        "preference": "Namespace\\MiddlewareClass",
        "priority": 100
    }
}
```

#### Structure

- **middleware-name**: Unique identifier for the middleware
- **preference**: Middleware class name
- **priority**: Execution order (lower numbers execute first)

#### Default Middleware

```json
{
    "concept-labs/simple-http:flusher": {
        "preference": "Concept\\SimpleHttp\\Response\\FlusherInterface",
        "priority": -1000
    },
    "concept-labs/simple-http:throwable": {
        "preference": "Concept\\SimpleHttp\\Response\\ThrowableInterface",
        "priority": -999
    },
    "concept-labs/simple-http:404": {
        "preference": "Concept\\SimpleHttp\\Response\\NotFound",
        "priority": -998
    }
}
```

#### Adding Custom Middleware

```json
{
    "app:authentication": {
        "preference": "App\\Middleware\\AuthMiddleware",
        "priority": 100
    },
    "app:cors": {
        "preference": "App\\Middleware\\CorsMiddleware",
        "priority": 200
    },
    "app:rate-limit": {
        "preference": "App\\Middleware\\RateLimitMiddleware",
        "priority": 300
    }
}
```

#### Priority Guidelines

- **-1000 to -900**: Framework-level middleware (flusher, error handling)
- **-100 to 100**: Authentication and security
- **100 to 500**: Application-level middleware
- **500 to 1000**: Logging and metrics

### Layouts: layouts.json

Configures layout templates and rendering engines.

**Location:** `etc/layouts.json`

```json
{
    "phtml": "@include(layouts/phtml.json)",
    "--blade": "@include(layouts/blade.json)",
    "--twig": "@include(layouts/twig.json)"
}
```

#### PHTML Layout Configuration

**Location:** `etc/layouts/phtml.json`

```json
{
    "template_path": "resources/template",
    "file_extension": ".phtml",
    "layouts": {
        "default": {
            "template": "page",
            "children": {
                "head": {
                    "template": "head"
                },
                "body": {
                    "template": "body",
                    "children": {
                        "header": {"template": "header"},
                        "main": {"template": "main"},
                        "footer": {"template": "footer"}
                    }
                }
            }
        }
    }
}
```

#### Configuration Options

- **template_path**: Base directory for templates
- **file_extension**: Template file extension (default: .phtml)
- **layouts**: Predefined layout structures
  - **template**: Template file name
  - **children**: Nested component definitions

#### Custom Layout

```json
{
    "layouts": {
        "admin": {
            "template": "admin-layout",
            "children": {
                "sidebar": {"template": "admin-sidebar"},
                "content": {"template": "admin-content"},
                "topbar": {"template": "admin-topbar"}
            }
        },
        "minimal": {
            "template": "minimal-layout"
        }
    }
}
```

## Environment-Specific Configuration

### Development Configuration

Create environment-specific config files:

**etc/config.dev.json:**
```json
{
    "app": {
        "debug": true,
        "cache": false,
        "log_level": "debug"
    },
    "database": {
        "host": "localhost",
        "port": 3306,
        "name": "myapp_dev"
    }
}
```

### Production Configuration

**etc/config.prod.json:**
```json
{
    "app": {
        "debug": false,
        "cache": true,
        "log_level": "error"
    },
    "database": {
        "host": "db.production.com",
        "port": 3306,
        "name": "myapp_prod"
    }
}
```

### Loading Environment Config

```php
<?php
$env = getenv('APP_ENV') ?: 'dev';
$configFile = __DIR__ . "/etc/config.{$env}.json";

if (file_exists($configFile)) {
    $config = json_decode(file_get_contents($configFile), true);
}
```

## Configuration Access in Code

### In Handlers

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class MyHandler extends SimpleHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        // Access configuration
        $config = $this->getAppConfig();
        
        // Get specific value
        $debug = $config->get('app.debug', false);
        $apiKey = $config->get('api.key');
        
        // Use configuration
        if ($debug) {
            // Debug mode logic
        }
        
        return $this->json(['debug' => $debug]);
    }
}
```

### In Services

```php
<?php
namespace App\Service;

use Concept\Http\App\Config\AppConfigInterface;

class ApiService
{
    public function __construct(
        private AppConfigInterface $config
    ) {}
    
    public function call(string $endpoint): array
    {
        $apiUrl = $this->config->get('api.base_url');
        $apiKey = $this->config->get('api.key');
        
        // Make API call
        $url = $apiUrl . $endpoint;
        // ...
    }
}
```

## Configuration Best Practices

### 1. Organize by Domain

```json
{
    "app": {
        "name": "My Application",
        "debug": false
    },
    "database": {
        "host": "localhost",
        "port": 3306
    },
    "cache": {
        "driver": "redis",
        "ttl": 3600
    },
    "mail": {
        "driver": "smtp",
        "host": "smtp.example.com"
    }
}
```

### 2. Use Environment Variables

Store sensitive data in environment variables:

```php
<?php
$config = [
    'database' => [
        'password' => getenv('DB_PASSWORD'),
        'host' => getenv('DB_HOST') ?: 'localhost'
    ],
    'api' => [
        'key' => getenv('API_KEY')
    ]
];
```

### 3. Validation

Validate critical configuration on startup:

```php
<?php
$requiredKeys = ['database.host', 'database.name', 'api.key'];

foreach ($requiredKeys as $key) {
    if (!$config->has($key)) {
        throw new \RuntimeException("Missing required config: {$key}");
    }
}
```

### 4. Type Safety

Use typed configuration classes:

```php
<?php
namespace App\Config;

class DatabaseConfig
{
    public function __construct(
        public readonly string $host,
        public readonly int $port,
        public readonly string $database,
        public readonly string $username,
        public readonly string $password
    ) {}
    
    public static function fromArray(array $config): self
    {
        return new self(
            $config['host'] ?? 'localhost',
            $config['port'] ?? 3306,
            $config['database'] ?? '',
            $config['username'] ?? '',
            $config['password'] ?? ''
        );
    }
}
```

### 5. Immutable Configuration

Make configuration immutable after initialization:

```php
<?php
class Config
{
    private array $data = [];
    private bool $locked = false;
    
    public function set(string $key, mixed $value): void
    {
        if ($this->locked) {
            throw new \RuntimeException('Configuration is locked');
        }
        $this->data[$key] = $value;
    }
    
    public function lock(): void
    {
        $this->locked = true;
    }
}
```

## Advanced Configuration

### Conditional Configuration

```json
{
    "app": {
        "features": {
            "beta": true,
            "analytics": true
        }
    }
}
```

```php
<?php
public function act(SimpleRequestInterface $request): static
{
    $features = $this->getAppConfig()->get('app.features', []);
    
    if ($features['beta'] ?? false) {
        // Enable beta features
    }
    
    return $this->json(['features' => $features]);
}
```

### Configuration Inheritance

```json
{
    "base": {
        "timeout": 30,
        "retries": 3
    },
    "api": {
        "@extends": "base",
        "url": "https://api.example.com"
    }
}
```

### Multi-Environment Setup

```
etc/
  ├── concept.json          # Main config
  ├── sdi.json             # DI config
  ├── middleware.json      # Middleware config
  ├── layouts.json         # Layouts config
  ├── environments/
  │   ├── dev.json         # Development overrides
  │   ├── staging.json     # Staging overrides
  │   └── prod.json        # Production overrides
```

## Configuration Caching

For production, cache parsed configuration:

```php
<?php
$cacheFile = __DIR__ . '/cache/config.php';

if (file_exists($cacheFile)) {
    $config = require $cacheFile;
} else {
    $config = $this->parseConfiguration();
    file_put_contents(
        $cacheFile,
        '<?php return ' . var_export($config, true) . ';'
    );
}
```

## Security Considerations

### 1. Sensitive Data

Never commit sensitive data to version control:

```gitignore
# .gitignore
etc/config.local.json
etc/secrets.json
.env
```

### 2. File Permissions

Restrict config file permissions:

```bash
chmod 600 etc/config.prod.json
chmod 600 etc/secrets.json
```

### 3. Validation

Validate configuration structure:

```php
<?php
$schema = [
    'required' => ['app', 'database'],
    'types' => [
        'app.debug' => 'boolean',
        'database.port' => 'integer'
    ]
];

$validator = new ConfigValidator($schema);
$validator->validate($config);
```

## Troubleshooting

### Configuration Not Loading

1. Check file permissions
2. Verify JSON syntax (use `json_decode()` with error checking)
3. Check file paths in @include directives
4. Ensure files exist

### Service Not Found in DI Container

1. Verify service is registered in sdi.json
2. Check class name spelling
3. Ensure package is loaded
4. Check preference mapping

### Middleware Not Executing

1. Verify middleware.json is included in concept.json
2. Check middleware class exists
3. Verify priority is set correctly
4. Ensure middleware implements correct interface

### Layout Not Rendering

1. Check layouts.json configuration
2. Verify template files exist
3. Check template_path setting
4. Ensure file_extension matches template files

## Configuration Examples

### Complete Application Config

```json
{
    "app": {
        "name": "My Application",
        "version": "1.0.0",
        "environment": "production",
        "debug": false,
        "timezone": "UTC"
    },
    "database": {
        "driver": "mysql",
        "host": "localhost",
        "port": 3306,
        "database": "myapp",
        "username": "dbuser",
        "charset": "utf8mb4"
    },
    "cache": {
        "driver": "redis",
        "host": "localhost",
        "port": 6379,
        "prefix": "myapp:",
        "ttl": 3600
    },
    "session": {
        "driver": "file",
        "lifetime": 7200,
        "path": "storage/sessions"
    },
    "mail": {
        "driver": "smtp",
        "host": "smtp.mailtrap.io",
        "port": 587,
        "from": {
            "address": "noreply@example.com",
            "name": "My Application"
        }
    },
    "logging": {
        "channel": "file",
        "path": "storage/logs/app.log",
        "level": "info"
    }
}
```

## Summary

- Configuration is JSON-based and modular
- Main config file: `etc/concept.json`
- DI configuration: `etc/sdi.json`
- Middleware configuration: `etc/middleware.json`
- Layout configuration: `etc/layouts.json`
- Use `@include()` to compose configuration
- Access config via `getAppConfig()` in handlers
- Keep sensitive data in environment variables
- Use environment-specific config files
- Validate and cache configuration for production
