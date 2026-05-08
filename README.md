# ThunderPhp

A PHP 8.3 framework built from scratch as a portfolio project to study and solidify software design patterns. No external framework dependencies — every component is hand-rolled, from the router to the JWT layer.

## Requirements

- PHP 8.3+
- PostgreSQL
- Composer
- `ext-pdo`, `ext-pdo_pgsql`

## Installation

```bash
git clone https://github.com/gpEnunes/thunderphp.git
cd thunderphp
composer install
cp .env.example .env  # configure your database credentials
```

## Running Tests

```bash
composer test
```

26 unit tests covering the router, validator, JWT, rate limiter, and logger.

---

## Architecture

### Entry Points

| File | Purpose |
|---|---|
| `public/index.php` | HTTP entry point — Front Controller pattern |
| `thunder` | CLI entry point — artisan-style command runner |

### Modules

#### Routing — `Thunder\Routing`
URL-to-handler mapping with dynamic route parameters and route groups.

```php
$router->get('/users', [UserController::class, 'index']);
$router->get('/users/{id}', [UserController::class, 'show']);
$router->post('/users', [UserController::class, 'store']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);

$router->group('/api', function (RouterInterface $router) {
    $router->get('/users', [UserController::class, 'index']);
});
```

#### HTTP — `Thunder\Http`
Immutable `Request` and `Response` value objects. `Request::fromGlobals()` parses JSON bodies automatically when `Content-Type: application/json`.

```php
$request->method();                // GET, POST, PUT...
$request->uri();                   // /users/42
$request->input('name');           // body field
$request->query('page', 1);        // query string field
$request->header('Authorization'); // request header

Response::json(['user' => $user], 200);
```

#### DI Container — `Thunder\Container`
Reflection-based IoC container with recursive autowiring.

```php
$container->bind(LoggerInterface::class, FileLogger::class);
$container->singleton(Connection::class, fn() => new Connection($config));
$container->make(UserController::class); // autowires all dependencies
```

#### Middleware Pipeline — `Thunder\Middleware`
Chains middleware via `array_reduce`. Implement `MiddlewareInterface` to create custom middleware.

```php
$pipeline = new Pipeline($request);
$response = $pipeline
    ->through([RateLimitMiddleware::class, AuthMiddleware::class])
    ->run(fn($req) => $app->handle($req));
```

#### Validation — `Thunder\Validation`
28 validation rules with pipe-separated rule strings.

```php
$validator = new Validator($request->body());
$validator->validate([
    'email'    => 'required|email',
    'password' => 'required|min:8|confirmed',
    'age'      => 'required|integer|between:18,99',
    'role'     => 'required|in:admin,user,guest',
]);
// throws ValidationException (HTTP 422) on failure
$validator->errors(); // ['email' => ['The email must be a valid email']]
```

Available rules: `required`, `string`, `numeric`, `integer`, `boolean`, `email`, `url`, `uuid`, `alpha`, `alpha_num`, `min`, `max`, `between`, `in`, `not_in`, `confirmed`, `regex`, `date`, `before`, `after`, `positive`, `negative`, `array`, `nullable`.

#### Authentication — `Thunder\Auth`
JWT authentication with HS256 signing. No external dependencies.

```php
$jwt = new Jwt($_ENV['APP_SECRET']);
$token = $jwt->encode(['sub' => $user->id, 'exp' => time() + 3600]);
$payload = $jwt->decode($token); // throws RuntimeException if invalid or expired
```

Protect routes by adding `AuthMiddleware` to the pipeline. It reads the `Authorization: Bearer <token>` header and returns HTTP 401 on failure.

#### Rate Limiting — `Thunder\RateLimit`
Per-key request throttling backed by any `CacheInterface` implementation.

```php
$limiter = new RateLimiter(new FileCache($path));
$limiter->hit("rate_limit:{$ip}", 60, 60); // 60 requests per 60 seconds
```

Add `RateLimitMiddleware` to the pipeline for automatic per-IP throttling. Returns HTTP 429 when the limit is exceeded.

#### Database — `Thunder\Database`
- **`Connection`** — PDO wrapper (database-agnostic)
- **`QueryBuilder`** — fluent SQL builder with selects, joins, aggregates, pagination, insert, update, delete
- **`Repository`** — abstract base for data access with `findAll()`, `findById()`, `create()`, `update()`, `delete()`
- **`Migrator`** — tracks and runs pending migrations, supports rollback
- **`Factory`** — abstract Faker-backed factory for generating test data

```php
$users = (new QueryBuilder($connection))
    ->table('users')
    ->select(['id', 'name', 'email'])
    ->where('active', '=', 1)
    ->orderBy('name')
    ->paginate(page: 1, perPage: 15);
```

#### Events — `Thunder\Events`
Observer pattern dispatcher.

```php
$dispatcher->listen('order.placed', fn($event) => sendEmail($event));
$dispatcher->dispatch('order.placed', $order);
$dispatcher->subscribe(OrderSubscriber::class);
```

#### Queue — `Thunder\Queue`
Serializes jobs to a PostgreSQL `jobs` table for async processing.

```php
$queue->push(new SendEmailJob($user));
$queue->pop(); // process next job
```

#### Cache — `Thunder\Cache`
Two implementations behind `CacheInterface`:
- **`FileCache`** — filesystem cache with TTL
- **`ArrayCache`** — in-memory cache for testing

```php
$cache->set('user:1', $user, ttl: 3600);
$cache->get('user:1');
$cache->remember('user:1', fn() => User::find(1), ttl: 3600);
$cache->forget('user:1');
```

#### Logger — `Thunder\Log`
PSR-3 inspired file logger with 8 log levels.

```php
$logger = new FileLogger(__DIR__ . '/storage/logs/thunder.log');
$logger->info('User logged in', ['id' => $user->id]);
$logger->error('Payment failed', ['order' => $orderId]);
```

Output format: `[2026-05-08 14:23:01] ERROR: Payment failed {"order":99}`

#### Config — `Thunder\Config`
Loads `config/*.php` files with dot-notation access.

```php
$config->get('database.host');
$config->get('app.debug', false);
$config->has('mail.driver');
```

#### CLI Runner — `Thunder\Console`
Extend `Command` and register it in `thunder` to add custom commands.

```php
class MigrateCommand extends Command
{
    protected string $name = 'migrate';
    protected string $description = 'Run pending migrations';

    public function handle(): void
    {
        // migration logic
    }
}
```

```bash
php thunder migrate
php thunder          # lists all available commands
```

#### Error Handling — `Thunder\Error`
Global exception handler returning JSON responses. Debug mode includes stack traces.

HTTP exceptions: `NotFoundException` (404), `ValidationException` (422), `UnauthorizedException` (401), `ForbiddenException` (403), `TooManyRequestsException` (429).

---

## Design Patterns

| Pattern | Where |
|---|---|
| Front Controller | `public/index.php`, `Application` |
| Strategy | `RouterInterface`, `CacheInterface`, `LoggerInterface` |
| Value Object | `Request`, `Response` |
| Dependency Injection | `Container` — reflection-based autowiring |
| Builder | `QueryBuilder` |
| Repository + Data Mapper | `Repository` |
| Observer | `Events\Dispatcher` |
| Command | `Queue`, `Console\Command` |
| Decorator | `CacheInterface` implementations |
| Template Method | `Console\Command`, `Database\Factory` |
| Chain of Responsibility | `Middleware\Pipeline` |

---

## License

MIT
