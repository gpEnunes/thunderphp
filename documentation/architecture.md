# ThunderPhp — Architecture

## Module tree

```
src/Thunder/
├── Core/           Application bootstrap and Front Controller
├── Routing/        URL-to-handler resolution with prefix groups
├── Http/           Request and Response value objects
├── Container/      Reflection-based IoC container
├── Middleware/      Middleware pipeline (Chain of Responsibility)
├── Validation/     28-rule validator with pipe syntax
├── Auth/           HS256 JWT encode/decode + AuthMiddleware
├── RateLimit/      Per-key request throttling
├── Database/
│   ├── Connection      PDO wrapper
│   ├── QueryBuilder    Fluent SQL builder
│   ├── Repository      Abstract CRUD base
│   ├── Migrator        Migration runner with rollback
│   └── Factory         Faker-backed test data factory
├── Events/         Observer dispatcher (listen / dispatch / subscribe)
├── Queue/          PostgreSQL-backed async job queue
├── Cache/          FileCache + ArrayCache behind CacheInterface
├── Log/            PSR-3 inspired file logger (8 levels)
├── Config/         Dot-notation config loader (config/*.php)
├── Console/        Artisan-style CLI runner
├── Error/          Global JSON exception handler
└── Exceptions/     HTTP exception types (404, 401, 403, 422, 429)
```

## Entry points

| Entry | File | Pattern |
|-------|------|---------|
| HTTP | `public/index.php` | Front Controller |
| CLI | `thunder` | CLI runner |

## HTTP request lifecycle

```
public/index.php
  │
  ├─ new Router()
  ├─ new Container()
  ├─ new Application($router, $container)
  ├─ $app->loadRoutes('routes/api.php')   ← registers all routes
  ├─ Request::fromGlobals()               ← parses $_SERVER, body, headers
  │
  ▼
Application::handle($request)
  │
  ├─ $router->resolve($method, $uri)      ← pattern match, extract params
  ├─ [$controllerClass, $method, $params]
  ├─ $container->make($controllerClass)   ← reflection autowiring
  │
  ▼
Controller::method($request, ...$params)
  │
  └─ Response::json([...], 200)->send()
```

When a middleware pipeline is used, it wraps the Application::handle call:

```
Pipeline->through([RateLimitMiddleware, AuthMiddleware])
        ->run(fn($req) => $app->handle($req))
```

Middleware executes innermost-first (array_reduce reverses the stack).

## Design patterns

| Pattern | Implementation |
|---------|---------------|
| Front Controller | `public/index.php` → `Application::handle()` |
| Strategy | `RouterInterface`, `CacheInterface`, `LoggerInterface`, `QueueInterface` |
| Value Object | `Request`, `Response` (immutable, no setters) |
| Dependency Injection | `Container` — reflection-based recursive autowiring |
| Builder | `QueryBuilder` — fluent chainable SQL construction |
| Repository + Data Mapper | `Repository` abstract base + `Connection` |
| Observer | `Events\Dispatcher` — listen / dispatch / subscribe |
| Command | `Queue\JobInterface`, `Console\Command` |
| Decorator | `CacheInterface` implementations (FileCache, ArrayCache) |
| Template Method | `Console\Command::handle()`, `Database\Factory` |
| Chain of Responsibility | `Middleware\Pipeline` — `array_reduce` chain |

## Namespace conventions

All framework classes live under `Thunder\<Module>`. Applications built on ThunderPhp use their own top-level namespace (e.g. `App\` for the metal store demo, `Trackier\` for trackier).

PSR-4 autoloading is configured in `composer.json`:
```json
"autoload": {
  "psr-4": {
    "Thunder\\": "src/Thunder/"
  }
}
```
