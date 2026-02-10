# Examples and Tutorials

## Table of Contents

- [Getting Started](#getting-started)
- [Building a Simple API](#building-a-simple-api)
- [Creating a Web Application](#creating-a-web-application)
- [Form Handling](#form-handling)
- [File Operations](#file-operations)
- [Authentication](#authentication)
- [RESTful API](#restful-api)
- [Advanced Patterns](#advanced-patterns)

## Getting Started

### Hello World Handler

The simplest possible handler that returns a JSON response:

```php
<?php
namespace App\Handler;

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

### Hello World Page

A simple page handler that renders HTML:

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class HelloPageHandler extends PageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $this->getLayout()
            ->setTemplate('hello')
            ->setData('message', 'Hello, World!');
        
        return $this;
    }
}
```

**Template: resources/template/hello.phtml**

```php
<!DOCTYPE html>
<html>
<head>
    <title>Hello World</title>
</head>
<body>
    <h1><?= htmlspecialchars($this->getData('message')) ?></h1>
</body>
</html>
```

## Building a Simple API

### Basic CRUD API

#### List All Items

```php
<?php
namespace App\Handler\Api;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;
use App\Repository\ItemRepository;

class ItemListHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private ItemRepository $itemRepository
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $page = (int) $request->getQueryParam('page', 1);
        $perPage = (int) $request->getQueryParam('per_page', 20);
        
        $items = $this->itemRepository->paginate($page, $perPage);
        $total = $this->itemRepository->count();
        
        return $this->json([
            'items' => $items,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ]
        ]);
    }
}
```

#### Get Single Item

```php
<?php
class ItemDetailHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private ItemRepository $itemRepository
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        if (!$id) {
            return $this->status(400)->json([
                'error' => 'Item ID is required'
            ]);
        }
        
        $item = $this->itemRepository->find($id);
        
        if (!$item) {
            return $this->status(404)->json([
                'error' => 'Item not found'
            ]);
        }
        
        return $this->json(['item' => $item]);
    }
}
```

#### Create Item

```php
<?php
class ItemCreateHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private ItemRepository $itemRepository,
        private ItemValidator $validator
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $data = json_decode(
            $request->getServerRequest()->getBody()->getContents(),
            true
        );
        
        $errors = $this->validator->validate($data);
        
        if (!empty($errors)) {
            return $this->status(422)->json([
                'error' => 'Validation failed',
                'details' => $errors
            ]);
        }
        
        $item = $this->itemRepository->create($data);
        
        return $this->status(201)->json([
            'item' => $item,
            'message' => 'Item created successfully'
        ]);
    }
}
```

#### Update Item

```php
<?php
class ItemUpdateHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private ItemRepository $itemRepository,
        private ItemValidator $validator
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        $item = $this->itemRepository->find($id);
        
        if (!$item) {
            return $this->status(404)->json([
                'error' => 'Item not found'
            ]);
        }
        
        $data = json_decode(
            $request->getServerRequest()->getBody()->getContents(),
            true
        );
        
        $errors = $this->validator->validate($data);
        
        if (!empty($errors)) {
            return $this->status(422)->json([
                'error' => 'Validation failed',
                'details' => $errors
            ]);
        }
        
        $item = $this->itemRepository->update($id, $data);
        
        return $this->json([
            'item' => $item,
            'message' => 'Item updated successfully'
        ]);
    }
}
```

#### Delete Item

```php
<?php
class ItemDeleteHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private ItemRepository $itemRepository
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        $item = $this->itemRepository->find($id);
        
        if (!$item) {
            return $this->status(404)->json([
                'error' => 'Item not found'
            ]);
        }
        
        $this->itemRepository->delete($id);
        
        return $this->status(204)->body('');
    }
}
```

## Creating a Web Application

### Multi-Page Application

#### Base Page Handler

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

abstract class BasePageHandler extends PageHandler
{
    protected function setupPage(string $title, string $template): void
    {
        $layout = $this->getLayout();
        
        // Set main template
        $layout->setTemplate('layout');
        
        // Add common components
        $layout->addChild('head', HeadComponent::class);
        $layout->addChild('header', HeaderComponent::class);
        $layout->addChild('footer', FooterComponent::class);
        
        // Set common data
        $layout->setData('siteName', 'My Website');
        $layout->setData('pageTitle', $title);
        
        // Set head data
        $head = $layout->getChild('head');
        $head->setData('title', $title . ' - My Website');
        $head->setData('charset', 'UTF-8');
        
        // Add main content component
        $layout->addChild('main', ContentComponent::class);
        $layout->getChild('main')->setTemplate($template);
    }
}
```

#### Home Page

```php
<?php
class HomePageHandler extends BasePageHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private PostRepository $postRepository
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $this->setupPage('Home', 'home');
        
        // Get latest posts
        $posts = $this->postRepository->getLatest(5);
        
        // Set page-specific data
        $this->getLayout()->setData('posts', $posts);
        
        // Add hero section
        $hero = $this->getLayout()->addChild('hero', HeroComponent::class);
        $hero->setData('title', 'Welcome to My Website');
        $hero->setData('subtitle', 'Building amazing things with PHP');
        
        return $this;
    }
}
```

#### About Page

```php
<?php
class AboutPageHandler extends BasePageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $this->setupPage('About Us', 'about');
        
        $this->getLayout()->setData('content', [
            'mission' => 'Our mission statement...',
            'team' => $this->getTeamMembers(),
            'values' => ['Innovation', 'Quality', 'Customer Focus']
        ]);
        
        return $this;
    }
    
    private function getTeamMembers(): array
    {
        return [
            ['name' => 'John Doe', 'role' => 'CEO'],
            ['name' => 'Jane Smith', 'role' => 'CTO']
        ];
    }
}
```

## Form Handling

### Contact Form

#### Handler

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class ContactFormHandler extends PageHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private EmailService $emailService
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $psrRequest = $request->getServerRequest();
        
        if ($psrRequest->getMethod() === 'POST') {
            return $this->handleSubmit($request);
        }
        
        return $this->showForm();
    }
    
    private function showForm(
        array $errors = [],
        array $values = [],
        string $message = ''
    ): static {
        $this->getLayout()
            ->setTemplate('contact-form')
            ->setData('errors', $errors)
            ->setData('values', $values)
            ->setData('message', $message);
        
        return $this;
    }
    
    private function handleSubmit(SimpleRequestInterface $request): static
    {
        $name = $request->getPostParam('name', '');
        $email = $request->getPostParam('email', '');
        $subject = $request->getPostParam('subject', '');
        $message = $request->getPostParam('message', '');
        
        $values = compact('name', 'email', 'subject', 'message');
        
        // Validate
        $errors = $this->validate($values);
        
        if (!empty($errors)) {
            return $this->showForm($errors, $values);
        }
        
        // Send email
        try {
            $this->emailService->send([
                'to' => 'contact@example.com',
                'from' => $email,
                'subject' => "Contact Form: {$subject}",
                'body' => "From: {$name}\nEmail: {$email}\n\n{$message}"
            ]);
            
            // Redirect to success page
            return $this->redirect('/contact/success');
        } catch (\Exception $e) {
            return $this->showForm(
                ['general' => 'Failed to send message. Please try again.'],
                $values
            );
        }
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
        
        if (empty($data['subject'])) {
            $errors['subject'] = 'Subject is required';
        }
        
        if (empty($data['message'])) {
            $errors['message'] = 'Message is required';
        } elseif (strlen($data['message']) < 10) {
            $errors['message'] = 'Message must be at least 10 characters';
        }
        
        return $errors;
    }
}
```

#### Template

**resources/template/contact-form.phtml:**

```php
<!DOCTYPE html>
<html>
<head>
    <title>Contact Us</title>
    <style>
        .error { color: red; }
        .form-group { margin-bottom: 1rem; }
    </style>
</head>
<body>
    <h1>Contact Us</h1>
    
    <?php if ($this->getData('message')): ?>
        <div class="message"><?= htmlspecialchars($this->getData('message')) ?></div>
    <?php endif; ?>
    
    <form method="POST" action="/contact">
        <div class="form-group">
            <label for="name">Name:</label>
            <input 
                type="text" 
                id="name" 
                name="name" 
                value="<?= htmlspecialchars($this->getData('values')['name'] ?? '') ?>"
            >
            <?php if (isset($this->getData('errors')['name'])): ?>
                <div class="error"><?= htmlspecialchars($this->getData('errors')['name']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= htmlspecialchars($this->getData('values')['email'] ?? '') ?>"
            >
            <?php if (isset($this->getData('errors')['email'])): ?>
                <div class="error"><?= htmlspecialchars($this->getData('errors')['email']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="subject">Subject:</label>
            <input 
                type="text" 
                id="subject" 
                name="subject" 
                value="<?= htmlspecialchars($this->getData('values')['subject'] ?? '') ?>"
            >
            <?php if (isset($this->getData('errors')['subject'])): ?>
                <div class="error"><?= htmlspecialchars($this->getData('errors')['subject']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea 
                id="message" 
                name="message" 
                rows="5"
            ><?= htmlspecialchars($this->getData('values')['message'] ?? '') ?></textarea>
            <?php if (isset($this->getData('errors')['message'])): ?>
                <div class="error"><?= htmlspecialchars($this->getData('errors')['message']) ?></div>
            <?php endif; ?>
        </div>
        
        <button type="submit">Send Message</button>
    </form>
</body>
</html>
```

## File Operations

### File Upload Handler

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class FileUploadHandler extends SimpleHandler
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif'];
    private const MAX_SIZE = 5 * 1024 * 1024; // 5MB
    
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private FileStorage $storage
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $psrRequest = $request->getServerRequest();
        $uploadedFiles = $psrRequest->getUploadedFiles();
        
        if (!isset($uploadedFiles['file'])) {
            return $this->status(400)->json([
                'error' => 'No file uploaded'
            ]);
        }
        
        $file = $uploadedFiles['file'];
        
        // Validate
        if ($file->getError() !== UPLOAD_ERR_OK) {
            return $this->status(400)->json([
                'error' => 'Upload failed'
            ]);
        }
        
        if ($file->getSize() > self::MAX_SIZE) {
            return $this->status(400)->json([
                'error' => 'File too large'
            ]);
        }
        
        $mimeType = $file->getClientMediaType();
        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return $this->status(400)->json([
                'error' => 'Invalid file type'
            ]);
        }
        
        // Save file
        $filename = $this->generateFilename($file);
        $path = $this->storage->save($file, $filename);
        
        return $this->status(201)->json([
            'filename' => $filename,
            'path' => $path,
            'size' => $file->getSize(),
            'type' => $mimeType
        ]);
    }
    
    private function generateFilename($file): string
    {
        $extension = pathinfo($file->getClientFilename(), PATHINFO_EXTENSION);
        return uniqid() . '.' . $extension;
    }
}
```

### File Download Handler

```php
<?php
namespace App\Handler;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class FileDownloadHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private FileStorage $storage
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $filename = $request->getParam('filename');
        
        if (!$filename) {
            return $this->status(400)->json([
                'error' => 'Filename is required'
            ]);
        }
        
        // Prevent directory traversal
        if (strpos($filename, '..') !== false || strpos($filename, '/') !== false) {
            return $this->status(400)->json([
                'error' => 'Invalid filename'
            ]);
        }
        
        $path = $this->storage->getPath($filename);
        
        if (!file_exists($path)) {
            return $this->status(404)->json([
                'error' => 'File not found'
            ]);
        }
        
        // Determine MIME type
        $mimeType = mime_content_type($path);
        
        // Set file content
        $content = file_get_contents($path);
        
        return $this
            ->download($path, $filename, $mimeType)
            ->body($content);
    }
}
```

## Authentication

### Login Handler

```php
<?php
namespace App\Handler\Auth;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class LoginHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private AuthService $authService,
        private SessionManager $session
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $username = $request->getPostParam('username');
        $password = $request->getPostParam('password');
        
        if (!$username || !$password) {
            return $this->status(400)->json([
                'error' => 'Username and password are required'
            ]);
        }
        
        $user = $this->authService->authenticate($username, $password);
        
        if (!$user) {
            return $this->status(401)->json([
                'error' => 'Invalid credentials'
            ]);
        }
        
        // Create session
        $this->session->set('user_id', $user->getId());
        $this->session->set('username', $user->getUsername());
        
        return $this->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail()
            ]
        ]);
    }
}
```

### Logout Handler

```php
<?php
class LogoutHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private SessionManager $session
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $this->session->destroy();
        
        return $this->json(['message' => 'Logout successful']);
    }
}
```

### Protected Handler

```php
<?php
abstract class ProtectedHandler extends SimpleHandler
{
    protected function requireAuth(): ?ResponseInterface
    {
        if (!$this->session->has('user_id')) {
            return $this->status(401)->json([
                'error' => 'Authentication required'
            ])->getResponse();
        }
        
        return null;
    }
    
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // Set request
        $this->setRequest($request);
        
        // Check authentication
        $authResponse = $this->requireAuth();
        if ($authResponse) {
            return $authResponse;
        }
        
        // Continue with normal handling
        return parent::handle($request);
    }
}
```

## RESTful API

### Complete REST Resource

```php
<?php
namespace App\Handler\Api;

use Concept\SimpleHttp\Handler\SimpleHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class ResourceHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private ResourceRepository $repository,
        private ResourceValidator $validator
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $psrRequest = $request->getServerRequest();
        $method = $psrRequest->getMethod();
        
        return match($method) {
            'GET' => $this->handleGet($request),
            'POST' => $this->handlePost($request),
            'PUT', 'PATCH' => $this->handlePut($request),
            'DELETE' => $this->handleDelete($request),
            default => $this->status(405)->json([
                'error' => 'Method not allowed',
                'allowed' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE']
            ])
        };
    }
    
    private function handleGet(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        if ($id) {
            return $this->getOne($id);
        }
        
        return $this->getAll($request);
    }
    
    private function getOne(string $id): static
    {
        $resource = $this->repository->find($id);
        
        if (!$resource) {
            return $this->status(404)->json(['error' => 'Resource not found']);
        }
        
        return $this->json(['data' => $resource]);
    }
    
    private function getAll(SimpleRequestInterface $request): static
    {
        $page = (int) $request->getQueryParam('page', 1);
        $perPage = (int) $request->getQueryParam('per_page', 20);
        $sort = $request->getQueryParam('sort', 'created_at');
        $order = $request->getQueryParam('order', 'desc');
        
        $resources = $this->repository->paginate($page, $perPage, $sort, $order);
        $total = $this->repository->count();
        
        return $this->json([
            'data' => $resources,
            'meta' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'pages' => ceil($total / $perPage)
            ]
        ]);
    }
    
    private function handlePost(SimpleRequestInterface $request): static
    {
        $data = $this->getJsonData($request);
        
        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            return $this->status(422)->json([
                'error' => 'Validation failed',
                'details' => $errors
            ]);
        }
        
        $resource = $this->repository->create($data);
        
        return $this->status(201)->json([
            'data' => $resource,
            'message' => 'Resource created'
        ]);
    }
    
    private function handlePut(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        if (!$id) {
            return $this->status(400)->json(['error' => 'ID is required']);
        }
        
        $resource = $this->repository->find($id);
        if (!$resource) {
            return $this->status(404)->json(['error' => 'Resource not found']);
        }
        
        $data = $this->getJsonData($request);
        
        $errors = $this->validator->validate($data);
        if (!empty($errors)) {
            return $this->status(422)->json([
                'error' => 'Validation failed',
                'details' => $errors
            ]);
        }
        
        $resource = $this->repository->update($id, $data);
        
        return $this->json([
            'data' => $resource,
            'message' => 'Resource updated'
        ]);
    }
    
    private function handleDelete(SimpleRequestInterface $request): static
    {
        $id = $request->getParam('id');
        
        if (!$id) {
            return $this->status(400)->json(['error' => 'ID is required']);
        }
        
        $resource = $this->repository->find($id);
        if (!$resource) {
            return $this->status(404)->json(['error' => 'Resource not found']);
        }
        
        $this->repository->delete($id);
        
        return $this->status(204)->body('');
    }
    
    private function getJsonData(SimpleRequestInterface $request): array
    {
        $body = $request->getServerRequest()->getBody()->getContents();
        return json_decode($body, true) ?? [];
    }
}
```

## Advanced Patterns

### API Rate Limiting

```php
<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    public function __construct(
        private CacheInterface $cache,
        private ResponseFactoryInterface $responseFactory,
        private int $maxRequests = 60,
        private int $windowSeconds = 60
    ) {}
    
    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {
        $ip = $request->getServerParams()['REMOTE_ADDR'] ?? 'unknown';
        $key = "rate_limit:{$ip}";
        
        $requests = (int) $this->cache->get($key, 0);
        
        if ($requests >= $this->maxRequests) {
            $response = $this->responseFactory->createResponse(429);
            $response->getBody()->write(json_encode([
                'error' => 'Too many requests'
            ]));
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Retry-After', (string) $this->windowSeconds);
        }
        
        $this->cache->set($key, $requests + 1, $this->windowSeconds);
        
        $response = $handler->handle($request);
        
        return $response
            ->withHeader('X-RateLimit-Limit', (string) $this->maxRequests)
            ->withHeader('X-RateLimit-Remaining', (string) ($this->maxRequests - $requests - 1));
    }
}
```

### Content Negotiation

```php
<?php
class ContentNegotiationHandler extends SimpleHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $accept = $request->getServerRequest()->getHeaderLine('Accept');
        
        $data = $this->getData();
        
        if (str_contains($accept, 'application/xml')) {
            return $this->respondXml($data);
        }
        
        if (str_contains($accept, 'text/csv')) {
            return $this->respondCsv($data);
        }
        
        // Default to JSON
        return $this->json($data);
    }
    
    private function respondXml(array $data): static
    {
        $xml = $this->arrayToXml($data);
        return $this->application('application/xml', $xml);
    }
    
    private function respondCsv(array $data): static
    {
        $csv = $this->arrayToCsv($data);
        return $this->application('text/csv', $csv)
            ->download('export.csv', 'export.csv', 'text/csv');
    }
}
```

### Caching Strategy

```php
<?php
class CachedDataHandler extends SimpleHandler
{
    public function __construct(
        SimpleRequestInterface $simpleRequest,
        AppConfigInterface $appConfig,
        private CacheInterface $cache,
        private DataRepository $repository
    ) {
        parent::__construct($simpleRequest, $appConfig);
    }
    
    public function act(SimpleRequestInterface $request): static
    {
        $cacheKey = 'data:' . $request->getParam('id');
        
        // Try to get from cache
        $data = $this->cache->get($cacheKey);
        
        if ($data === null) {
            // Cache miss - get from repository
            $data = $this->repository->find($request->getParam('id'));
            
            // Store in cache for 1 hour
            $this->cache->set($cacheKey, $data, 3600);
        }
        
        return $this->json(['data' => $data, 'cached' => $data !== null]);
    }
}
```

## Summary

This guide provided practical examples for:
- Building APIs (CRUD operations)
- Creating web applications
- Handling forms and file uploads
- Implementing authentication
- Building RESTful services
- Advanced patterns (rate limiting, caching, content negotiation)

For more information, refer to the other documentation files:
- [Architecture Overview](./architecture.md)
- [Handler Guide](./handlers.md)
- [Layout System](./layout-system.md)
- [API Reference](./api-reference.md)
- [Configuration](./configuration.md)
