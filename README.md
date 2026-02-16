# Simple HTTP

A lightweight, modern PHP HTTP application framework built on PSR standards, designed to simplify web application development while maintaining flexibility and power.

## 🚀 Why Simple HTTP?

Simple HTTP provides a streamlined approach to building PHP web applications with:

- **PSR-Compliant**: Built on PSR-7 (HTTP Messages) and PSR-15 (HTTP Handlers) standards
- **Easy Request Handling**: Intuitive request/response abstractions with powerful handler system
- **Flexible Layouts**: Component-based layout system with template support
- **Minimal Configuration**: Get started quickly with sensible defaults
- **Modern PHP**: Requires PHP 8.2+ with full type safety and modern features
- **Extensible**: Plugin system and middleware support for customization

## ✨ Key Features

### Powerful Request Handlers
- **SimpleHandler**: Abstract base for creating custom request handlers
- **PageHandler**: Specialized handler for page-based applications with layout support
- **LayoutableHandler**: Handler with integrated layout rendering capabilities

### Flexible Response Building
Build responses with an elegant fluent API:
```php
$handler->status(200)
    ->header('Content-Type', 'application/json')
    ->json(['message' => 'Success']);
```

### Layout System
Component-based layout system with:
- Template rendering with PHTML templates
- Nested components and layouts
- Context management for template variables
- Plugin support for template transformations

### Built-in Utilities
- JSON response helpers
- File download support
- Redirect helpers (including referer redirects)
- Header management utilities

## 📦 Installation

Install via Composer:

```bash
composer require concept-labs/simple-http
```

### Requirements
- PHP 8.2 or higher
- Composer

## 🎯 Quick Start

### Basic Handler Example

```php
<?php
use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class HelloWorldHandler extends SimpleHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        return $this->json([
            'message' => 'Hello, World!',
            'timestamp' => time()
        ]);
    }
}
```

### Page Handler with Layout

```php
<?php
use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class HomePageHandler extends PageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $this->getLayout()
            ->setTemplate('page')
            ->setData('title', 'Welcome Home');
            
        return $this;
    }
}
```

### JSON API Endpoint

```php
<?php
use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class ApiHandler extends SimpleHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $data = [
            'status' => 'success',
            'data' => ['id' => 1, 'name' => 'Example']
        ];
        
        return $this->status(200)->json($data);
    }
}
```

## 📚 Documentation

Comprehensive technical documentation is available in the [`docs/`](./docs) directory:

- **[Architecture Overview](./docs/architecture.md)** - System architecture and design principles
- **[Handler Guide](./docs/handlers.md)** - Complete guide to creating and using handlers
- **[Layout System](./docs/layout-system.md)** - Layout and template system documentation
- **[API Reference](./docs/api-reference.md)** - Detailed API documentation
- **[Configuration](./docs/configuration.md)** - Configuration options and setup
- **[Examples & Tutorials](./docs/examples.md)** - Real-world examples and tutorials

## 🔧 Configuration

Simple HTTP uses a JSON-based configuration system. Configuration files are located in the `etc/` directory:

- `sdi.json` - Dependency injection configuration
- `layouts.json` - Layout system configuration
- `middleware.json` - Middleware configuration
- `concept.json` - Main application configuration

See the [Configuration Guide](./docs/configuration.md) for detailed information.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit issues and pull requests.

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🔗 Related Projects

- [concept-labs/http](https://github.com/Concept-Labs/http) - Core HTTP library
- [concept-labs/http-session](https://github.com/Concept-Labs/http-session) - Session management

## 👨‍💻 Author

**Viktor Halytskyi**  
Email: concept.galitsky@gmail.com

---

Built with ❤️ by [Concept Labs](https://github.com/Concept-Labs)
