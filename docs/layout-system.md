# Layout System

## Overview

The Simple HTTP layout system provides a flexible, component-based approach to building HTML pages. It uses PHTML templates and a hierarchical component structure to create reusable and maintainable page layouts.

## Core Concepts

### Components
Components are the building blocks of layouts. Each component:
- Has a template file
- Can have child components
- Can hold data accessible in templates
- Can be rendered independently or as part of a hierarchy

### Templates
PHTML (PHP HTML) templates are PHP files that:
- Contain HTML markup with embedded PHP
- Access data through `$this->getData()`
- Render child components with `<?= $this('.child-name') ?>`
- Can include logic and loops

### Context
Context manages data flow between handlers and templates:
- Stores template variables
- Provides data to components
- Maintains component hierarchy

## LayoutBuilder

The `LayoutBuilder` is the main interface for working with layouts.

### Basic Usage

```php
use Concept\SimpleHttp\Handler\PageHandler;
use Concept\SimpleHttp\Request\SimpleRequestInterface;

class MyPageHandler extends PageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $layout = $this->getLayout();
        
        // Set the main template
        $layout->setTemplate('page');
        
        // Set data for the template
        $layout->setData('title', 'My Page');
        $layout->setData('description', 'Page description');
        
        return $this;
    }
}
```

### Setting Templates

```php
// Set template by name (looks in configured template paths)
$layout->setTemplate('page');

// Template file should be located at:
// resources/template/page.phtml
```

### Managing Data

```php
// Set single value
$layout->setData('title', 'My Title');

// Set multiple values
$layout->setData('title', 'My Title');
$layout->setData('content', 'Page content');
$layout->setData('author', 'John Doe');

// Access in template: <?= $this->getData('title') ?>
```

### Working with Child Components

```php
// Add a child component
$layout->addChild('header', HeaderComponent::class);
$layout->addChild('footer', FooterComponent::class);

// Add child with data
$header = $layout->addChild('header', HeaderComponent::class);
$header->setData('logo', '/images/logo.png');
$header->setData('menuItems', $menuItems);

// Get existing child
$header = $layout->getChild('header');
$header->setData('user', $currentUser);

// Remove a child
$layout->removeChild('header');

// Check if child exists
if ($layout->hasChild('sidebar')) {
    $layout->getChild('sidebar')->setData('widgets', $widgets);
}
```

## Component Hierarchy

### Typical Page Structure

```
page (root)
  ├── head
  │   ├── meta
  │   └── styles
  ├── body
  │   ├── header
  │   │   ├── logo
  │   │   └── navigation
  │   ├── main
  │   │   ├── sidebar
  │   │   └── content
  │   └── footer
  └── scripts
```

### Building a Hierarchy

```php
public function act(SimpleRequestInterface $request): static
{
    $layout = $this->getLayout();
    
    // Root template
    $layout->setTemplate('page');
    
    // Add top-level components
    $layout->addChild('head', HeadComponent::class);
    $layout->addChild('body', BodyComponent::class);
    
    // Add nested components
    $body = $layout->getChild('body');
    $body->addChild('header', HeaderComponent::class);
    $body->addChild('main', MainComponent::class);
    $body->addChild('footer', FooterComponent::class);
    
    // Add deeply nested components
    $main = $body->getChild('main');
    $main->addChild('sidebar', SidebarComponent::class);
    $main->addChild('content', ContentComponent::class);
    
    // Set data at various levels
    $layout->setData('pageTitle', 'My Site');
    $layout->getChild('head')->setData('title', 'My Page');
    $main->getChild('content')->setData('text', 'Content here');
    
    return $this;
}
```

## Templates

### Template Syntax

**page.phtml:**
```php
<!DOCTYPE html>
<html>
    <?= $this('.head') ?>
    <body>
        <?= $this('.body') ?>
    </body>
</html>
```

### Accessing Data

```php
<!-- Simple data -->
<h1><?= $this->getData('title') ?></h1>

<!-- With default value -->
<p><?= $this->getData('description', 'No description') ?></p>

<!-- Check if data exists -->
<?php if ($this->hasData('author')): ?>
    <p>By <?= $this->getData('author') ?></p>
<?php endif; ?>

<!-- Array data -->
<?php foreach ($this->getData('items', []) as $item): ?>
    <div><?= $item['name'] ?></div>
<?php endforeach; ?>
```

### Rendering Child Components

```php
<!-- Render a child component -->
<?= $this('.header') ?>

<!-- Equivalent to -->
<?= $this->render('.header') ?>

<!-- Check if child exists before rendering -->
<?php if ($this->hasChild('sidebar')): ?>
    <aside>
        <?= $this('.sidebar') ?>
    </aside>
<?php endif; ?>
```

### Complex Template Example

**layout.phtml:**
```php
<!DOCTYPE html>
<html lang="<?= $this->getData('lang', 'en') ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->getData('pageTitle') ?> - <?= $this->getData('siteName') ?></title>
    
    <?php if ($this->hasData('description')): ?>
        <meta name="description" content="<?= $this->getData('description') ?>">
    <?php endif; ?>
    
    <?= $this('.head') ?>
</head>
<body class="<?= $this->getData('bodyClass', '') ?>">
    <?= $this('.header') ?>
    
    <main class="main-content">
        <?php if ($this->hasChild('sidebar')): ?>
            <div class="container with-sidebar">
                <aside class="sidebar">
                    <?= $this('.sidebar') ?>
                </aside>
                <div class="content">
                    <?= $this('.main') ?>
                </div>
            </div>
        <?php else: ?>
            <div class="container">
                <?= $this('.main') ?>
            </div>
        <?php endif; ?>
    </main>
    
    <?= $this('.footer') ?>
    <?= $this('.scripts') ?>
</body>
</html>
```

## Custom Components

### Creating a Component Class

```php
<?php
namespace App\Layout\Component;

use Concept\SimpleHttp\Layout\Component\AbstractComponent;

class HeaderComponent extends AbstractComponent
{
    public function render(): string
    {
        $logo = $this->getData('logo', '/images/logo.png');
        $siteName = $this->getData('siteName', 'My Site');
        
        $html = '<header class="site-header">';
        $html .= '<div class="logo">';
        $html .= '<img src="' . htmlspecialchars($logo) . '" alt="' . htmlspecialchars($siteName) . '">';
        $html .= '</div>';
        
        // Render navigation child if exists
        if ($this->hasChild('navigation')) {
            $html .= $this->renderChild('navigation');
        }
        
        $html .= '</header>';
        
        return $html;
    }
    
    protected function renderChild(string $name): string
    {
        return (string) $this->getChild($name);
    }
}
```

### Component with Template

```php
<?php
namespace App\Layout\Component;

use Concept\SimpleHttp\Layout\Component\AbstractComponent;

class CardComponent extends AbstractComponent
{
    protected string $template = 'components/card';
    
    public function beforeRender(): void
    {
        // Prepare data before rendering
        $title = $this->getData('title', 'Untitled');
        $this->setData('title', ucfirst($title));
        
        // Add default CSS class
        if (!$this->hasData('cssClass')) {
            $this->setData('cssClass', 'card');
        }
    }
}
```

**components/card.phtml:**
```php
<div class="<?= $this->getData('cssClass') ?>">
    <h2 class="card-title"><?= $this->getData('title') ?></h2>
    
    <?php if ($this->hasData('image')): ?>
        <img src="<?= $this->getData('image') ?>" alt="<?= $this->getData('title') ?>">
    <?php endif; ?>
    
    <div class="card-content">
        <?= $this->getData('content', '') ?>
    </div>
    
    <?php if ($this->hasChild('footer')): ?>
        <div class="card-footer">
            <?= $this('.footer') ?>
        </div>
    <?php endif; ?>
</div>
```

### Using Custom Components

```php
public function act(SimpleRequestInterface $request): static
{
    $layout = $this->getLayout();
    
    // Add custom component
    $card = $layout->addChild('card', CardComponent::class);
    $card->setData('title', 'Welcome');
    $card->setData('content', 'Welcome to our site!');
    $card->setData('cssClass', 'card featured');
    
    // Add child to custom component
    $footer = $card->addChild('footer', CardFooterComponent::class);
    $footer->setData('text', 'Learn more →');
    
    return $this;
}
```

## Plugin System

Plugins transform component output after rendering.

### Creating a Plugin

```php
<?php
namespace App\Layout\Plugin;

class MinifyHtmlPlugin implements PluginInterface
{
    public function execute(string $content): string
    {
        // Remove extra whitespace
        $content = preg_replace('/\s+/', ' ', $content);
        $content = preg_replace('/>\s+</', '><', $content);
        
        return trim($content);
    }
}
```

### Built-in Plugins

#### UppercasePlugin

```php
<?php
namespace Concept\SimpleHttp\Layout\Component\Plugin;

class UppercasePlugin implements PluginInterface
{
    public function execute(string $content): string
    {
        return strtoupper($content);
    }
}
```

### Applying Plugins

```php
// Apply plugin to a component
$component = $layout->getChild('title');
$component->addPlugin(new UppercasePlugin());

// Multiple plugins (executed in order)
$component
    ->addPlugin(new TrimPlugin())
    ->addPlugin(new MinifyPlugin())
    ->addPlugin(new CachePlugin());
```

## Configuration

### Layout Configuration File

**etc/layouts.json:**
```json
{
    "default": {
        "template_path": "resources/template",
        "components": {
            "page": {
                "template": "page",
                "children": {
                    "head": {
                        "template": "head",
                        "children": {
                            "meta": {"template": "meta"},
                            "styles": {"template": "styles"}
                        }
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
}
```

### Loading Configuration

```php
public function act(SimpleRequestInterface $request): static
{
    // Layout is automatically configured from layouts.json
    $layout = $this->getLayout();
    
    // You can override or extend the configuration
    $layout->setTemplate('custom-page');
    
    return $this;
}
```

## Advanced Patterns

### Conditional Component Loading

```php
public function act(SimpleRequestInterface $request): static
{
    $layout = $this->getLayout();
    $layout->setTemplate('page');
    
    // Always add header and footer
    $layout->addChild('header', HeaderComponent::class);
    $layout->addChild('footer', FooterComponent::class);
    
    // Conditionally add sidebar
    if ($this->shouldShowSidebar($request)) {
        $sidebar = $layout->addChild('sidebar', SidebarComponent::class);
        $sidebar->setData('widgets', $this->getSidebarWidgets());
    }
    
    // Conditionally add user menu
    if ($this->isUserLoggedIn($request)) {
        $header = $layout->getChild('header');
        $userMenu = $header->addChild('user-menu', UserMenuComponent::class);
        $userMenu->setData('user', $this->getCurrentUser());
    }
    
    return $this;
}
```

### Data Inheritance

```php
public function act(SimpleRequestInterface $request): static
{
    $layout = $this->getLayout();
    
    // Set global data at root level
    $layout->setData('siteName', 'My Website');
    $layout->setData('currentYear', date('Y'));
    
    // Child components can access parent data
    $footer = $layout->addChild('footer', FooterComponent::class);
    // footer.phtml can use $this->getData('currentYear')
    
    return $this;
}
```

### Dynamic Component Selection

```php
public function act(SimpleRequestInterface $request): static
{
    $layout = $this->getLayout();
    
    // Select component based on user role
    $user = $this->getCurrentUser($request);
    $dashboardComponent = match($user->getRole()) {
        'admin' => AdminDashboardComponent::class,
        'editor' => EditorDashboardComponent::class,
        'viewer' => ViewerDashboardComponent::class,
        default => GuestDashboardComponent::class,
    };
    
    $layout->addChild('dashboard', $dashboardComponent);
    
    return $this;
}
```

### Reusable Layout Sections

```php
abstract class BasePageHandler extends PageHandler
{
    protected function setupLayout(): void
    {
        $layout = $this->getLayout();
        
        // Common layout structure
        $layout->setTemplate('page');
        $layout->addChild('head', HeadComponent::class);
        $layout->addChild('header', HeaderComponent::class);
        $layout->addChild('footer', FooterComponent::class);
        
        // Common data
        $layout->setData('siteName', 'My Site');
        $layout->getChild('head')->setData('charset', 'UTF-8');
    }
}

class HomePageHandler extends BasePageHandler
{
    public function act(SimpleRequestInterface $request): static
    {
        $this->setupLayout();
        
        // Add page-specific components
        $layout = $this->getLayout();
        $layout->addChild('hero', HeroComponent::class);
        $layout->addChild('features', FeaturesComponent::class);
        
        return $this;
    }
}
```

## Best Practices

### 1. Component Granularity
Create components at the right level of granularity:

```php
// Good - reusable components
$layout->addChild('button', ButtonComponent::class);
$layout->addChild('card', CardComponent::class);

// Avoid - too granular
$layout->addChild('button-text', TextComponent::class);
$layout->addChild('button-icon', IconComponent::class);
```

### 2. Data Naming
Use consistent, descriptive names:

```php
// Good
$layout->setData('pageTitle', 'Home');
$layout->setData('metaDescription', 'Welcome...');
$layout->setData('bodyClass', 'home-page');

// Avoid
$layout->setData('t', 'Home');
$layout->setData('desc', 'Welcome...');
$layout->setData('c', 'home-page');
```

### 3. Template Organization
Organize templates by function:

```
resources/
  template/
    layout/
      page.phtml
      simple.phtml
    components/
      header.phtml
      footer.phtml
      card.phtml
    pages/
      home.phtml
      about.phtml
```

### 4. XSS Prevention
Always escape output in templates:

```php
<!-- Good -->
<h1><?= htmlspecialchars($this->getData('title')) ?></h1>
<div><?= htmlspecialchars($this->getData('content'), ENT_QUOTES, 'UTF-8') ?></div>

<!-- For trusted HTML -->
<div><?= $this->getData('trustedHtml') ?></div>  <!-- Only if you control the source -->
```

### 5. Component Reusability
Design components for reuse:

```php
class AlertComponent extends AbstractComponent
{
    public function render(): string
    {
        $type = $this->getData('type', 'info'); // info, success, warning, error
        $message = $this->getData('message', '');
        $dismissible = $this->getData('dismissible', false);
        
        $html = '<div class="alert alert-' . $type . '">';
        $html .= htmlspecialchars($message);
        
        if ($dismissible) {
            $html .= '<button class="alert-close">×</button>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
}
```

### 6. Performance Considerations
```php
// Cache expensive operations
private array $cachedData = [];

public function render(): string
{
    if (!isset($this->cachedData['menu'])) {
        $this->cachedData['menu'] = $this->buildComplexMenu();
    }
    
    return $this->renderTemplate($this->cachedData['menu']);
}
```

## Troubleshooting

### Component Not Rendering

1. Check template file exists
2. Verify template path configuration
3. Ensure component was added to layout
4. Check for PHP errors in template

### Data Not Available in Template

1. Verify data was set: `$layout->setData('key', 'value')`
2. Check data key spelling
3. Ensure data was set before rendering
4. Check if data is on correct component level

### Child Component Issues

1. Verify child was added: `$layout->addChild('name', Component::class)`
2. Check child name in template matches: `<?= $this('.name') ?>`
3. Ensure child exists before accessing: `hasChild()` check

## Summary

- Use **LayoutBuilder** to manage layouts
- Create **custom components** for reusable UI elements
- Use **PHTML templates** for markup
- Organize components in a **hierarchy**
- Apply **plugins** for output transformation
- Follow **best practices** for security and reusability
- Configure layouts via **JSON configuration**
