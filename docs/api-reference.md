# API Reference

## Table of Contents

- [Handler Classes](#handler-classes)
- [Request Classes](#request-classes)
- [Response Classes](#response-classes)
- [Layout Classes](#layout-classes)
- [Utility Classes](#utility-classes)

## Handler Classes

### SimpleHandler

**Namespace:** `Concept\SimpleHttp\Handler`

Abstract base class for all request handlers.

#### Constructor

```php
public function __construct(
    protected SimpleRequestInterface $simpleRequest,
    protected AppConfigInterface $appConfig
)
```

#### Methods

##### act()

```php
abstract public function act(SimpleRequestInterface $request): static
```

Process the request and build response. Must be implemented by subclasses.

**Parameters:**
- `$request` (SimpleRequestInterface): The request object

**Returns:** `static` - The handler instance for method chaining

---

##### handle()

```php
public function handle(ServerRequestInterface $request): ResponseInterface
```

PSR-15 RequestHandler implementation. Handles the incoming request.

**Parameters:**
- `$request` (ServerRequestInterface): PSR-7 request

**Returns:** `ResponseInterface` - PSR-7 response

---

##### status()

```php
public function status(int $code): static
```

Set HTTP status code for the response.

**Parameters:**
- `$code` (int): HTTP status code (e.g., 200, 404, 500)

**Returns:** `static` - The handler instance for method chaining

**Example:**
```php
$this->status(200); // OK
$this->status(404); // Not Found
```

---

##### header()

```php
public function header(string $name, string $value): static
```

Add or replace a header in the response.

**Parameters:**
- `$name` (string): Header name
- `$value` (string): Header value

**Returns:** `static` - The handler instance for method chaining

**Example:**
```php
$this->header('Content-Type', 'application/json');
$this->header('Cache-Control', 'no-cache');
```

---

##### body()

```php
public function body(string $body): static
```

Set the response body content.

**Parameters:**
- `$body` (string): Body content

**Returns:** `static` - The handler instance for method chaining

**Example:**
```php
$this->body('<h1>Hello World</h1>');
```

---

##### application()

```php
public function application(string $type, string $body): static
```

Set content type and body in one call.

**Parameters:**
- `$type` (string): Content-Type header value
- `$body` (string): Body content

**Returns:** `static` - The handler instance for method chaining

**Example:**
```php
$this->application('application/xml', $xmlString);
```

---

##### json()

```php
public function json(array $data): static
```

Set JSON response with appropriate content type.

**Parameters:**
- `$data` (array): Data to encode as JSON

**Returns:** `static` - The handler instance for method chaining

**Throws:** `JsonException` if encoding fails

**Example:**
```php
$this->json(['status' => 'success', 'data' => $items]);
```

---

##### download()

```php
public function download(
    string $filename,
    ?string $as = null,
    string $contentType = HeaderUtilInterface::CONTENT_TYPE_OCTET_STREAM
): static
```

Prepare response for file download.

**Parameters:**
- `$filename` (string): Path to the file
- `$as` (string|null): Download filename (defaults to basename of $filename)
- `$contentType` (string): MIME type (defaults to octet-stream)

**Returns:** `static` - The handler instance for method chaining

**Example:**
```php
$this->download('/path/to/file.pdf', 'report.pdf', 'application/pdf');
```

---

##### redirect()

```php
public function redirect(string $url, int $statusCode = 302): ResponseInterface
```

Redirect to a relative URL.

**Parameters:**
- `$url` (string): Relative URL path (must not include scheme or host)
- `$statusCode` (int): HTTP redirect status code (default: 302)

**Returns:** `ResponseInterface` - The response object

**Throws:** `InvalidArgumentException` if URL is absolute

**Example:**
```php
return $this->redirect('/dashboard');
return $this->redirect('/login', 301); // Permanent redirect
```

---

##### redirectReferer()

```php
public function redirectReferer(int $statusCode = 302): ResponseInterface
```

Redirect to the referer URL.

**Parameters:**
- `$statusCode` (int): HTTP redirect status code (default: 302)

**Returns:** `ResponseInterface` - The response object

**Throws:** `RuntimeException` if no referer found

**Example:**
```php
return $this->redirectReferer();
```

---

##### getSimpleRequest()

```php
public function getSimpleRequest(): SimpleRequestInterface
```

Get the simple request wrapper.

**Returns:** `SimpleRequestInterface` - The request wrapper

---

##### getAppConfig()

```php
protected function getAppConfig(): AppConfigInterface
```

Get application configuration.

**Returns:** `AppConfigInterface` - Configuration object

---

### LayoutableHandler

**Namespace:** `Concept\SimpleHttp\Handler`

**Extends:** `SimpleHandler`

Abstract handler with layout rendering capabilities.

#### Methods

##### getLayout()

```php
public function getLayout(): LayoutBuilderInterface
```

Get the layout builder instance.

**Returns:** `LayoutBuilderInterface` - Layout builder

**Example:**
```php
$layout = $this->getLayout();
$layout->setTemplate('page');
```

---

### PageHandler

**Namespace:** `Concept\SimpleHttp\Handler`

**Extends:** `LayoutableHandler`

Specialized handler for HTML pages.

#### Methods

Inherits all methods from `LayoutableHandler` and `SimpleHandler`.

---

## Request Classes

### SimpleRequest

**Namespace:** `Concept\SimpleHttp\Request`

**Implements:** `SimpleRequestInterface`

Wrapper for PSR-7 ServerRequest with convenience methods.

#### Methods

##### getParam()

```php
public function getParam(string $name, mixed $default = null): mixed
```

Get request parameter (POST takes precedence over GET).

**Parameters:**
- `$name` (string): Parameter name
- `$default` (mixed): Default value if parameter not found

**Returns:** `mixed` - Parameter value or default

---

##### getPostParam()

```php
public function getPostParam(string $name, mixed $default = null): mixed
```

Get POST parameter.

**Parameters:**
- `$name` (string): Parameter name
- `$default` (mixed): Default value if parameter not found

**Returns:** `mixed` - Parameter value or default

---

##### getQueryParam()

```php
public function getQueryParam(string $name, mixed $default = null): mixed
```

Get query string parameter.

**Parameters:**
- `$name` (string): Parameter name
- `$default` (mixed): Default value if parameter not found

**Returns:** `mixed` - Parameter value or default

---

##### getServerRequest()

```php
public function getServerRequest(): ServerRequestInterface
```

Get the underlying PSR-7 request.

**Returns:** `ServerRequestInterface` - PSR-7 request object

---

##### setServerRequest()

```php
public function setServerRequest(ServerRequestInterface $request): static
```

Set the PSR-7 request.

**Parameters:**
- `$request` (ServerRequestInterface): PSR-7 request object

**Returns:** `static` - Request instance for method chaining

---

## Response Classes

### Flusher

**Namespace:** `Concept\SimpleHttp\Response`

**Implements:** `FlusherInterface`

Handles response output to client.

#### Methods

##### flush()

```php
public function flush(ResponseInterface $response): void
```

Send response to client.

**Parameters:**
- `$response` (ResponseInterface): PSR-7 response to send

**Example:**
```php
$flusher = new Flusher();
$flusher->flush($response);
```

---

### NotFound

**Namespace:** `Concept\SimpleHttp\Response`

Handles 404 Not Found responses.

#### Methods

##### handle()

```php
public function handle(ServerRequestInterface $request): ResponseInterface
```

Create 404 response.

**Parameters:**
- `$request` (ServerRequestInterface): PSR-7 request

**Returns:** `ResponseInterface` - 404 response

---

### Throwable

**Namespace:** `Concept\SimpleHttp\Response`

**Implements:** `ThrowableInterface`

Handles exception and error responses.

#### Methods

##### handle()

```php
public function handle(
    ServerRequestInterface $request,
    \Throwable $throwable
): ResponseInterface
```

Create error response from throwable.

**Parameters:**
- `$request` (ServerRequestInterface): PSR-7 request
- `$throwable` (Throwable): Exception or error

**Returns:** `ResponseInterface` - Error response

---

## Layout Classes

### LayoutBuilder

**Namespace:** `Concept\SimpleHttp\Layout`

**Implements:** `LayoutBuilderInterface`

Manages layout construction and rendering.

#### Methods

##### setTemplate()

```php
public function setTemplate(string $template): static
```

Set the template file for the layout.

**Parameters:**
- `$template` (string): Template name (without .phtml extension)

**Returns:** `static` - Layout builder for method chaining

**Example:**
```php
$layout->setTemplate('page');
```

---

##### addChild()

```php
public function addChild(string $name, string $componentClass): ComponentInterface
```

Add a child component to the layout.

**Parameters:**
- `$name` (string): Child component name
- `componentClass` (string): Component class name

**Returns:** `ComponentInterface` - The created component

**Example:**
```php
$header = $layout->addChild('header', HeaderComponent::class);
```

---

##### getChild()

```php
public function getChild(string $name): ComponentInterface
```

Get a child component by name.

**Parameters:**
- `$name` (string): Child component name

**Returns:** `ComponentInterface` - The component

**Throws:** `RuntimeException` if child not found

---

##### hasChild()

```php
public function hasChild(string $name): bool
```

Check if a child component exists.

**Parameters:**
- `$name` (string): Child component name

**Returns:** `bool` - True if child exists

---

##### removeChild()

```php
public function removeChild(string $name): static
```

Remove a child component.

**Parameters:**
- `$name` (string): Child component name

**Returns:** `static` - Layout builder for method chaining

---

##### setData()

```php
public function setData(string $key, mixed $value): static
```

Set data for the layout.

**Parameters:**
- `$key` (string): Data key
- `$value` (mixed): Data value

**Returns:** `static` - Layout builder for method chaining

**Example:**
```php
$layout->setData('title', 'My Page');
```

---

##### getData()

```php
public function getData(string $key, mixed $default = null): mixed
```

Get data from the layout.

**Parameters:**
- `$key` (string): Data key
- `$default` (mixed): Default value if key not found

**Returns:** `mixed` - Data value or default

---

##### hasData()

```php
public function hasData(string $key): bool
```

Check if data exists.

**Parameters:**
- `$key` (string): Data key

**Returns:** `bool` - True if data exists

---

##### render()

```php
public function render(): string
```

Render the layout and all child components.

**Returns:** `string` - Rendered HTML

---

### AbstractComponent

**Namespace:** `Concept\SimpleHttp\Layout\Component`

Base class for layout components.

#### Properties

```php
protected string $template = ''; // Template name
```

#### Methods

##### render()

```php
abstract public function render(): string
```

Render the component. Must be implemented by subclasses.

**Returns:** `string` - Rendered HTML

---

##### setData()

```php
public function setData(string $key, mixed $value): static
```

Set component data.

**Parameters:**
- `$key` (string): Data key
- `$value` (mixed): Data value

**Returns:** `static` - Component for method chaining

---

##### getData()

```php
public function getData(string $key, mixed $default = null): mixed
```

Get component data.

**Parameters:**
- `$key` (string): Data key
- `$default` (mixed): Default value

**Returns:** `mixed` - Data value or default

---

##### hasData()

```php
public function hasData(string $key): bool
```

Check if data exists.

**Parameters:**
- `$key` (string): Data key

**Returns:** `bool` - True if data exists

---

##### addChild()

```php
public function addChild(string $name, string $componentClass): ComponentInterface
```

Add a child component.

**Parameters:**
- `$name` (string): Child name
- `$componentClass` (string): Component class

**Returns:** `ComponentInterface` - The created component

---

##### getChild()

```php
public function getChild(string $name): ComponentInterface
```

Get a child component.

**Parameters:**
- `$name` (string): Child name

**Returns:** `ComponentInterface` - The component

---

##### hasChild()

```php
public function hasChild(string $name): bool
```

Check if child exists.

**Parameters:**
- `$name` (string): Child name

**Returns:** `bool` - True if child exists

---

##### addPlugin()

```php
public function addPlugin(PluginInterface $plugin): static
```

Add a plugin to transform output.

**Parameters:**
- `$plugin` (PluginInterface): Plugin instance

**Returns:** `static` - Component for method chaining

---

### Context

**Namespace:** `Concept\SimpleHttp\Layout\Context`

**Implements:** `ContextInterface`

Manages template context data.

#### Methods

##### set()

```php
public function set(string $key, mixed $value): void
```

Set context data.

**Parameters:**
- `$key` (string): Data key
- `$value` (mixed): Data value

---

##### get()

```php
public function get(string $key, mixed $default = null): mixed
```

Get context data.

**Parameters:**
- `$key` (string): Data key
- `$default` (mixed): Default value

**Returns:** `mixed` - Data value or default

---

##### has()

```php
public function has(string $key): bool
```

Check if data exists.

**Parameters:**
- `$key` (string): Data key

**Returns:** `bool` - True if data exists

---

## Utility Classes

### HeaderUtilInterface

**Namespace:** `Concept\SimpleHttp\Util`

Defines HTTP header and content type constants.

#### Constants

##### Headers

```php
const HEADER_CONTENT_TYPE = 'Content-Type';
const HEADER_CONTENT_DISPOSITION = 'Content-Disposition';
const HEADER_X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
```

##### Content Types

```php
const CONTENT_TYPE_JSON = 'application/json';
const CONTENT_TYPE_HTML = 'text/html; charset=utf-8';
const CONTENT_TYPE_XML = 'application/xml';
const CONTENT_TYPE_OCTET_STREAM = 'application/octet-stream';
const CONTENT_TYPE_PDF = 'application/pdf';
const CONTENT_TYPE_TEXT = 'text/plain; charset=utf-8';
```

**Usage:**
```php
use Concept\SimpleHttp\Util\HeaderUtilInterface;

$this->header(
    HeaderUtilInterface::HEADER_CONTENT_TYPE,
    HeaderUtilInterface::CONTENT_TYPE_JSON
);
```

---

## Application Classes

### SimpleHttpApp

**Namespace:** `Concept\SimpleHttp\App`

**Extends:** `HttpApp`

Main application class.

#### Methods

Inherits all methods from `HttpApp` (from concept-labs/http package).

---

### SimpleAppFactory

**Namespace:** `Concept\SimpleHttp\App`

**Extends:** `AppFactory`

Factory for creating SimpleHttpApp instances.

#### Methods

##### create()

```php
public function create(array $args = []): AppInterface
```

Create and configure application instance.

**Parameters:**
- `$args` (array): Optional configuration arguments

**Returns:** `AppInterface` - Configured application

**Example:**
```php
$factory = new SimpleAppFactory();
$app = $factory->create();
```

---

### Bootstrap

**Namespace:** `Concept\SimpleHttp`

**Extends:** `\Concept\Http\Bootstrap`

Application bootstrap class.

#### Methods

Inherits all methods from base Bootstrap class.

---

## Interfaces

### SimpleHandlerInterface

**Namespace:** `Concept\SimpleHttp\Handler`

```php
interface SimpleHandlerInterface extends RequestHandlerInterface
{
    public function act(SimpleRequestInterface $request): static;
    public function getSimpleRequest(): SimpleRequestInterface;
    public function status(int $code): static;
    public function header(string $name, string $value): static;
    public function body(string $body): static;
    public function json(array $data): static;
    public function download(string $filename, ?string $as = null, string $contentType): static;
    public function redirect(string $url, int $statusCode = 302): ResponseInterface;
    public function redirectReferer(int $statusCode = 302): ResponseInterface;
}
```

---

### LayoutableHandlerInterface

**Namespace:** `Concept\SimpleHttp\Handler`

```php
interface LayoutableHandlerInterface extends SimpleHandlerInterface
{
    public function getLayout(): LayoutBuilderInterface;
}
```

---

### PageHandlerInterface

**Namespace:** `Concept\SimpleHttp\Handler`

```php
interface PageHandlerInterface extends LayoutableHandlerInterface
{
    // Inherits all methods from LayoutableHandlerInterface
}
```

---

### SimpleRequestInterface

**Namespace:** `Concept\SimpleHttp\Request`

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

---

### LayoutBuilderInterface

**Namespace:** `Concept\SimpleHttp\Layout`

```php
interface LayoutBuilderInterface
{
    public function setTemplate(string $template): static;
    public function addChild(string $name, string $componentClass): ComponentInterface;
    public function getChild(string $name): ComponentInterface;
    public function hasChild(string $name): bool;
    public function removeChild(string $name): static;
    public function setData(string $key, mixed $value): static;
    public function getData(string $key, mixed $default = null): mixed;
    public function hasData(string $key): bool;
    public function render(): string;
}
```

---

### ComponentInterface

**Namespace:** `Concept\SimpleHttp\Layout\Component`

```php
interface ComponentInterface
{
    public function render(): string;
    public function setData(string $key, mixed $value): static;
    public function getData(string $key, mixed $default = null): mixed;
    public function hasData(string $key): bool;
    public function addChild(string $name, string $componentClass): self;
    public function getChild(string $name): self;
    public function hasChild(string $name): bool;
    public function addPlugin(PluginInterface $plugin): static;
}
```

---

### PluginInterface

**Namespace:** `Concept\SimpleHttp\Layout\Component`

```php
interface PluginInterface
{
    public function execute(string $content): string;
}
```

---

## Type Hints and Standards

Simple HTTP follows these standards:

- **PSR-7**: HTTP Messages (Request/Response)
- **PSR-15**: HTTP Server Request Handlers
- **PSR-4**: Autoloading
- **PHP 8.2+**: Uses modern PHP features including:
  - Constructor property promotion
  - Union types
  - Mixed type
  - Named arguments

## Error Handling

All methods may throw standard PHP exceptions:

- `\InvalidArgumentException`: Invalid arguments passed
- `\RuntimeException`: Runtime errors
- `\JsonException`: JSON encoding/decoding errors
- `\Throwable`: General errors and exceptions

Always wrap risky operations in try-catch blocks:

```php
try {
    return $this->json($data);
} catch (\JsonException $e) {
    return $this->status(500)->body('JSON encoding error');
}
```
