# Architecture Overview

## Introduction

Simple HTTP is a lightweight PHP HTTP application framework built on top of PSR standards. It provides an abstraction layer for handling HTTP requests and responses while maintaining flexibility and extensibility.

## Core Components

### 1. Application Layer

#### SimpleHttpApp
The main application class that extends `HttpApp` from the concept-labs/http package. It serves as the entry point for the application and manages the dependency injection container.

**Key Features:**
- PSR-7 compliant HTTP message handling
- Dependency injection via Singularity container
- Middleware support
- Route handling

#### SimpleAppFactory
Factory class responsible for creating and configuring the SimpleHttpApp instance. It sets up the dependency injection container and applies configuration.

**Usage:**
```php
$factory = new SimpleAppFactory();
$app = $factory->create();
```

#### Bootstrap
The Bootstrap class initializes the application environment and prepares the application for execution. It extends the base Bootstrap from the concept-labs/http package.

### 2. Handler Layer

Handlers are the core components that process HTTP requests and generate responses. Simple HTTP provides three levels of handler abstraction:

#### SimpleHandler (Abstract Base)
The foundational handler class that provides:
- Request/response lifecycle management
- PSR-15 RequestHandler implementation
- Response building methods (status, headers, body)
- Helper methods for common response types (JSON, downloads, redirects)

**Key Methods:**
- `act(SimpleRequestInterface $request): static` - Abstract method to be implemented by subclasses
- `json(array $data): static` - Return JSON response
- `redirect(string $url, int $statusCode = 302): ResponseInterface` - Redirect to URL
- `redirectReferer(int $statusCode = 302): ResponseInterface` - Redirect to referrer
- `download(string $filename, ?string $as = null, string $contentType): static` - File download

#### LayoutableHandler (Abstract)
Extends SimpleHandler with layout rendering capabilities:
- Integration with the Layout system
- Template rendering support
- Layout context management

#### PageHandler (Abstract)
Extends LayoutableHandler specifically for page-based applications:
- Optimized for HTML page rendering
- Automatic content-type header management
- Full layout system integration

### 3. Request Layer

#### SimpleRequest
Wraps PSR-7 ServerRequest with convenient helper methods:
- Parameter access (GET, POST, headers)
- Request method checking
- URL and path information
- Session integration

**Interface:**
```php
interface SimpleRequestInterface
{
    public function getParam(string $name, mixed $default = null): mixed;
    public function getPostParam(string $name, mixed $default = null): mixed;
    public function getQueryParam(string $name, mixed $default = null): mixed;
    public function getServerRequest(): ServerRequestInterface;
    public function setServerRequest(ServerRequestInterface $request): static;
}
```

### 4. Layout System

The layout system provides a flexible way to build HTML pages using components and templates.

#### LayoutBuilder
Manages layout construction and rendering:
- Component tree management
- Template loading and rendering
- Context data management
- Plugin system for template transformations

#### Layout Components
- **AbstractComponent**: Base class for all layout components
- **Plugin System**: Transform component output (e.g., UppercasePlugin)
- **Context**: Manages template variables and data

#### Templates
Templates are PHTML files that can:
- Access context data via `$this`
- Render child components: `<?= $this('.child-name') ?>`
- Use PHP for logic and loops

**Example Template:**
```php
<html>
    <?= $this('.head') ?>
    <body>
        <h1><?= $this->getData('title') ?></h1>
        <?= $this('.body-main') ?>
    </body>
</html>
```

### 5. Response Layer

#### Flusher
Handles the output of responses to the client:
- Buffer management
- Header sending
- Body output
- Error handling

#### Response Handlers
- **NotFound**: Generates 404 responses
- **Throwable**: Handles exceptions and errors

### 6. Utility Layer

#### HeaderUtilInterface
Defines constants for common HTTP headers and content types:
- `HEADER_CONTENT_TYPE`
- `HEADER_CONTENT_DISPOSITION`
- `HEADER_X_CONTENT_TYPE_OPTIONS`
- `CONTENT_TYPE_JSON`
- `CONTENT_TYPE_HTML`
- `CONTENT_TYPE_OCTET_STREAM`

## Request Lifecycle

1. **Bootstrap**: Application is bootstrapped and initialized
2. **Routing**: Incoming request is matched to a route
3. **Handler Creation**: Appropriate handler is instantiated via DI container
4. **Request Processing**: Handler's `handle()` method is called
5. **Action Execution**: Handler's `act()` method processes the request
6. **Response Building**: Handler builds the response using fluent API
7. **Layout Rendering** (if applicable): Layout system renders templates
8. **Response Flushing**: Response is sent to the client

```
Request → Bootstrap → Router → Handler → act() → Response → Flusher → Client
                                    ↓
                            Layout System (optional)
```

## Dependency Injection

Simple HTTP uses a Singularity container for dependency injection. Configuration is stored in `etc/sdi.json`:

```json
{
    "services": {
        "HandlerClass": {
            "class": "Namespace\\HandlerClass",
            "arguments": ["@SimpleRequest", "@AppConfig"]
        }
    }
}
```

## Middleware System

Middleware can be configured in `etc/middleware.json` to process requests before they reach handlers:

```json
{
    "middleware": [
        {
            "class": "Namespace\\AuthMiddleware",
            "priority": 100
        }
    ]
}
```

## Configuration

Configuration is managed through JSON files in the `etc/` directory:

- **concept.json**: Main configuration file that includes other configs
- **sdi.json**: Dependency injection configuration
- **layouts.json**: Layout definitions and templates
- **middleware.json**: Middleware stack configuration

## Extension Points

### Custom Handlers
Create custom handlers by extending SimpleHandler, LayoutableHandler, or PageHandler:

```php
class MyHandler extends SimpleHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        // Your logic here
        return $this->json(['result' => 'success']);
    }
}
```

### Custom Components
Create layout components by extending AbstractComponent:

```php
class MyComponent extends AbstractComponent
{
    public function render(): string
    {
        // Component rendering logic
        return '<div>' . $this->getData('content') . '</div>';
    }
}
```

### Plugins
Create template transformation plugins:

```php
class MyPlugin implements PluginInterface
{
    public function execute(string $content): string
    {
        // Transform content
        return $content;
    }
}
```

## Best Practices

1. **Extend appropriate handler**: Choose SimpleHandler for APIs, PageHandler for HTML pages
2. **Use dependency injection**: Inject dependencies through constructor
3. **Type safety**: Leverage PHP 8.2+ type declarations
4. **Separation of concerns**: Keep business logic separate from HTTP concerns
5. **Immutable responses**: Use fluent API to build responses
6. **Layout hierarchy**: Structure layouts with proper parent-child relationships
7. **Error handling**: Use try-catch in handlers and return appropriate error responses

## Security Considerations

1. **Input validation**: Always validate user input in handlers
2. **Output encoding**: Template system should encode output to prevent XSS
3. **Headers**: Use appropriate security headers (X-Content-Type-Options, etc.)
4. **Redirects**: Validate redirect URLs (only relative paths allowed by default)
5. **File downloads**: Validate file paths to prevent directory traversal

## Performance Tips

1. **Lazy loading**: Components are rendered only when needed
2. **Buffer management**: Flusher handles output buffering efficiently
3. **Dependency resolution**: Container resolves dependencies on-demand
4. **Template caching**: Consider implementing template caching for production
5. **Middleware ordering**: Order middleware by priority to optimize request processing
