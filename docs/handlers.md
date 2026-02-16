# Handler Guide

## Overview

Handlers are the core components in Simple HTTP that process incoming requests and generate responses. They implement the PSR-15 RequestHandlerInterface and provide a clean, fluent API for building HTTP responses.

## Handler Hierarchy

```
RequestHandler (PSR-15)
    ↓
SimpleHandler (Abstract)
    ↓
LayoutableHandler (Abstract)
    ↓
PageHandler (Abstract)
```

## SimpleHandler

The base handler class that provides fundamental request/response handling capabilities.

### Key Features

- **PSR-15 Compliance**: Implements `RequestHandlerInterface`
- **Fluent API**: Chain methods for building responses
- **Type Safety**: Full PHP 8.2+ type hints
- **Abstraction**: Forces implementation of `act()` method

### Creating a Custom Handler

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class MyHandler extends SimpleHandler
{
    /**
     * Process the request and build response
     */
    public function act(SimpleRequestInterface $request): static
    {
        // Your business logic here
        $data = $this->processRequest($request);
        
        return $this->json($data);
    }
    
    private function processRequest(SimpleRequestInterface $request): array
    {
        // Implementation
        return ['status' => 'success'];
    }
}
```

### Response Building Methods

#### Status Code

```php
$this->status(200); // OK
$this->status(201); // Created
$this->status(400); // Bad Request
$this->status(404); // Not Found
$this->status(500); // Internal Server Error
```

#### Headers

```php
// Set a single header
$this->header('Content-Type', 'application/json');
$this->header('X-Custom-Header', 'value');

// Chain multiple headers
$this->status(200)
    ->header('Content-Type', 'application/json')
    ->header('Cache-Control', 'no-cache');
```

#### Body Content

```php
// Set response body
$this->body('<h1>Hello World</h1>');

// Chain with other methods
$this->status(200)
    ->header('Content-Type', 'text/html')
    ->body('<html><body>Content</body></html>');
```

#### JSON Response

```php
// Simple JSON response
$this->json(['message' => 'Success']);

// Complex data structure
$this->json([
    'status' => 'success',
    'data' => [
        'users' => $users,
        'total' => count($users)
    ],
    'meta' => [
        'page' => 1,
        'perPage' => 10
    ]
]);

// With status code
$this->status(201)->json(['id' => 123, 'created' => true]);
```

#### File Downloads

```php
// Basic download
$this->download('/path/to/file.pdf');

// Custom filename
$this->download('/path/to/file.pdf', 'custom-name.pdf');

// Custom content type
$this->download(
    '/path/to/file.csv',
    'export.csv',
    'text/csv'
);
```

#### Redirects

```php
// Basic redirect (302 by default)
return $this->redirect('/dashboard');

// Permanent redirect
return $this->redirect('/new-location', 301);

// Redirect to referer
return $this->redirectReferer();

// Redirect to referer with custom status
return $this->redirectReferer(303);
```

**Important**: Redirects only accept relative URLs for security. Absolute URLs with scheme/host will throw an exception.

### Accessing Request Data

```php
public function act(SimpleRequestInterface $request): static
{
    // Get query parameter
    $id = $request->getQueryParam('id');
    $page = $request->getQueryParam('page', 1); // with default
    
    // Get POST parameter
    $username = $request->getPostParam('username');
    
    // Get any parameter (POST takes precedence)
    $value = $request->getParam('key');
    
    // Get PSR-7 request
    $psrRequest = $request->getServerRequest();
    
    // Use request data
    return $this->json([
        'received' => [
            'id' => $id,
            'page' => $page,
            'username' => $username
        ]
    ]);
}
```

### Configuration Access

```php
public function act(SimpleRequestInterface $request): static
{
    // Access application configuration
    $config = $this->getAppConfig();
    
    $apiKey = $config->get('api.key');
    $debug = $config->get('app.debug', false);
    
    return $this->json([
        'debug' => $debug
    ]);
}
```

## LayoutableHandler

Extends SimpleHandler with layout rendering capabilities for HTML responses.

### Features

- Layout system integration
- Template rendering
- Context management
- Component hierarchy

### Basic Usage

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\LayoutableHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class ContentHandler extends LayoutableHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        // Get the layout builder
        $layout = $this->getLayout();
        
        // Set template
        $layout->setTemplate('page');
        
        // Set data for template
        $layout->setData('title', 'My Page');
        $layout->setData('content', 'Page content here');
        
        return $this;
    }
}
```

### Working with Layouts

```php
public function act(SimpleRequestInterface $request): static
{
    $layout = $this->getLayout();
    
    // Set main template
    $layout->setTemplate('page');
    
    // Add child components
    $layout->addChild('header', HeaderComponent::class);
    $layout->addChild('footer', FooterComponent::class);
    
    // Set data accessible in templates
    $layout->setData('pageTitle', 'Welcome');
    $layout->setData('userName', $request->getParam('user'));
    
    // Set data for specific child
    $layout->getChild('header')->setData('logo', '/images/logo.png');
    
    return $this;
}
```

### Template Structure

Templates are PHTML files that can access layout data and render child components:

**page.phtml:**
```php
<!DOCTYPE html>
<html>
    <head>
        <title><?= $this->getData('pageTitle') ?></title>
    </head>
    <body>
        <?= $this('.header') ?>
        <main>
            <?= $this->getData('content') ?>
        </main>
        <?= $this('.footer') ?>
    </body>
</html>
```

## PageHandler

Specialized handler for page-based applications. Extends LayoutableHandler with additional page-specific features.

### Features

- Optimized for HTML page rendering
- Automatic header management
- Full layout system support
- SEO-friendly response handling

### Usage

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class HomePageHandler extends PageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        // Build page layout
        $this->getLayout()
            ->setTemplate('home-page')
            ->setData('title', 'Home')
            ->setData('description', 'Welcome to our site');
        
        // Add components
        $this->getLayout()
            ->addChild('navigation', NavigationComponent::class)
            ->addChild('hero', HeroComponent::class)
            ->addChild('features', FeaturesComponent::class);
        
        return $this;
    }
}
```

## Advanced Patterns

### Handler with Dependency Injection

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;
use App\Service\UserService;
use Psr\Log\LoggerInterface;

class UserHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private UserService $userService,
        private LoggerInterface $logger
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $userId = $request->getParam('id');
        
        try {
            $user = $this->userService->find($userId);
            
            $this->logger->info("User retrieved", ['id' => $userId]);
            
            return $this->json([
                'user' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            $this->logger->error("Error retrieving user", [
                'id' => $userId,
                'error' => $e->getMessage()
            ]);
            
            return $this->status(500)->json([
                'error' => 'Internal server error'
            ]);
        }
    }
}
```

### RESTful API Handler

```php
<?php
namespace App\Handler\Api;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class ApiResourceHandler extends SimpleHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $method = $request->getServerRequest()->getMethod();
        
        return match($method) {
            'GET' => $this->handleGet($request),
            'POST' => $this->handlePost($request),
            'PUT' => $this->handlePut($request),
            'DELETE' => $this->handleDelete($request),
            default => $this->status(405)->json(['error' => 'Method not allowed'])
        };
    }
    
    private function handleGet(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        if ($id) {
            return $this->json(['resource' => $this->findResource($id)]);
        }
        
        return $this->json(['resources' => $this->listResources()]);
    }
    
    private function handlePost(SimpleRequestInterface $request): static
    {
        $data = json_decode(
            $request->getServerRequest()->getBody()->getContents(),
            true
        );
        
        $resource = $this->createResource($data);
        
        return $this->status(201)->json(['resource' => $resource]);
    }
    
    private function handlePut(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        $data = json_decode(
            $request->getServerRequest()->getBody()->getContents(),
            true
        );
        
        $resource = $this->updateResource($id, $data);
        
        return $this->json(['resource' => $resource]);
    }
    
    private function handleDelete(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        $this->deleteResource($id);
        
        return $this->status(204)->body('');
    }
}
```

### Form Handler with Validation

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class ContactFormHandler extends PageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $psrRequest = $request->getServerRequest();
        
        if ($psrRequest->getMethod() === 'POST') {
            return $this->handleSubmit($request);
        }
        
        return $this->showForm();
    }
    
    private function showForm(array $errors = [], array $values = []): static
    {
        $this->getLayout()
            ->setTemplate('contact-form')
            ->setData('errors', $errors)
            ->setData('values', $values);
        
        return $this;
    }
    
    private function handleSubmit(SimpleRequestInterface $request): static
    {
        $name = $request->getPostParam('name');
        $email = $request->getPostParam('email');
        $message = $request->getPostParam('message');
        
        $errors = $this->validate([
            'name' => $name,
            'email' => $email,
            'message' => $message
        ]);
        
        if (!empty($errors)) {
            return $this->showForm($errors, compact('name', 'email', 'message'));
        }
        
        // Process form
        $this->sendEmail($name, $email, $message);
        
        // Redirect to success page
        return $this->redirect('/contact/success');
    }
    
    private function validate(array $data): array
    {
        $errors = [];
        
        if (empty($data['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($data['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format';
        }
        
        if (empty($data['message'])) {
            $errors['message'] = 'Message is required';
        }
        
        return $errors;
    }
}
```

## Best Practices

### 1. Single Responsibility
Each handler should handle one specific route or resource:

```php
// Good
class UserProfileHandler extends PageHandler { }
class UserListHandler extends PageHandler { }

// Avoid
class UserHandler extends PageHandler { } // Too broad
```

### 2. Type Hints and Return Types
Always use strict typing:

```php
public function act(SimpleRequestInterface $request): static
{
    return $this->json(['data' => $this->getData()]);
}
```

### 3. Error Handling
Handle errors gracefully:

```php
public function act(SimpleRequestInterface $request): static
{
    try {
        $result = $this->riskyOperation();
        return $this->json(['result' => $result]);
    } catch (NotFoundException $e) {
        return $this->status(404)->json(['error' => 'Not found']);
    } catch (\Exception $e) {
        return $this->status(500)->json(['error' => 'Server error']);
    }
}
```

### 4. Configuration
Use configuration for environment-specific values:

```php
public function act(SimpleRequestInterface $request): static
{
    $apiUrl = $this->getAppConfig()->get('api.url');
    // Use $apiUrl
}
```

### 5. Dependency Injection
Inject services rather than creating them:

```php
// Good
public function __construct(
    SimpleRequestInterface $simpleRequest,
    AppConfigInterface $appConfig,
    private DatabaseService $db
) {
    parent::__construct($simpleRequest, $appConfig);
}

// Avoid
public function act(SimpleRequestInterface $request): static
{
    $db = new DatabaseService(); // Don't do this
}
```

## Testing Handlers

```php
<?php
namespace Tests\Handler;

use PHPUnit\Framework\TestCase;
use App\Handler\MyHandler;

class MyHandlerTest extends TestCase
{
    public function testHandlerReturnsJson(): void
    {
        $request = $this->createMock(SimpleRequestInterface::class);
        $config = $this->createMock(AppConfigInterface::class);
        
        $handler = new MyHandler($request, $config);
        $handler->act($request);
        
        $response = $handler->getResponse();
        
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertStringContainsString(
            'application/json',
            $response->getHeaderLine('Content-Type')
        );
    }
}
```

## Common Pitfalls

### 1. Not Returning $this
```php
// Wrong
public function act(SimpleRequestInterface $request): static
{
    $this->json(['data' => 'value']);
    // Missing return!
}

// Correct
public function act(SimpleRequestInterface $request): static
{
    return $this->json(['data' => 'value']);
}
```

### 2. Absolute URLs in Redirects
```php
// Wrong - throws exception
return $this->redirect('https://example.com/page');

// Correct
return $this->redirect('/page');
```

### 3. Forgetting to Call Parent Constructor
```php
// Wrong
public function __construct(
    SimpleRequestInterface $simpleRequest,
    AppConfigInterface $appConfig,
    private MyService $service
) {
    // Missing parent constructor call!
}

// Correct
public function __construct(
    SimpleRequestInterface $simpleRequest,
    AppConfigInterface $appConfig,
    private MyService $service
) {
    parent::__construct($simpleRequest, $appConfig);
}
```

## Summary

- **SimpleHandler**: Base for all handlers, provides core functionality
- **LayoutableHandler**: Adds layout rendering for HTML responses
- **PageHandler**: Specialized for page-based applications
- Use fluent API for building responses
- Handle errors gracefully
- Inject dependencies via constructor
- Follow single responsibility principle
