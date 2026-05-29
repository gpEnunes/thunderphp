# ThunderPhp — Module API Reference

## Routing — `Thunder\Routing`

```php
$router->get('/users', [UserController::class, 'index']);
$router->post('/users', [UserController::class, 'store']);
$router->put('/users/{id}', [UserController::class, 'update']);
$router->patch('/users/{id}', [UserController::class, 'update']);
$router->delete('/users/{id}', [UserController::class, 'destroy']);

// Route groups (prefix stacks — nested groups are supported)
$router->group('/api', function (RouterInterface $router) {
    $router->get('/users', [UserController::class, 'index']);
    $router->group('/admin', function (RouterInterface $router) {
        $router->get('/stats', [AdminController::class, 'stats']);
    });
});
```

Route params use `{name}` syntax. `resolve()` returns `['handler' => [...], 'params' => ['id' => '42']]`. Throws `NotFoundException` on no match.

---

## HTTP — `Thunder\Http`

```php
// Request — built from globals in index.php
$request = Request::fromGlobals();
$request->method();                // 'GET', 'POST', etc.
$request->uri();                   // '/users/42'
$request->input('name');           // body field (JSON auto-decoded)
$request->query('page', 1);        // query string with default
$request->header('Authorization'); // request header
$request->body();                  // full body array

// Response
Response::json(['user' => $user], 200)->send();
Response::json(['error' => 'Not found'], 404)->send();
```

`Request` is immutable. `Content-Type: application/json` bodies are decoded automatically.

---

## Container — `Thunder\Container`

```php
// Bind interface to concrete
$container->bind(LoggerInterface::class, FileLogger::class);

// Singleton — created once, cached for subsequent make() calls
$container->singleton(Connection::class, Connection::class);

// Resolve with recursive autowiring
$controller = $container->make(UserController::class);
// → reflection reads constructor, recursively resolves each type-hinted param
```

Singletons are stored in `$instances[]` after first resolution. Works only with type-hinted constructor params — no primitives.

---

## Middleware — `Thunder\Middleware`

```php
$pipeline = new Pipeline($request);
$response = $pipeline
    ->through([RateLimitMiddleware::class, AuthMiddleware::class])
    ->run(fn($req) => $app->handle($req));
```

Implement `MiddlewareInterface`:

```php
class MyMiddleware implements MiddlewareInterface
{
    public function handle(RequestInterface $request, callable $next): ResponseInterface
    {
        // before
        $response = $next($request);
        // after
        return $response;
    }
}
```

Execution order: last in `through()` array executes first (array_reduce reverses).

---

## Validation — `Thunder\Validation`

```php
$validator = new Validator($request->body());
$validator->validate([
    'email'    => 'required|email',
    'password' => 'required|min:8|confirmed',
    'age'      => 'required|integer|between:18,99',
    'role'     => 'required|in:admin,user,guest',
    'bio'      => 'nullable|string|max:500',
]);
// throws ValidationException (HTTP 422) on any failure
$validator->errors(); // ['email' => ['The email must be a valid email']]
```

**All 24 rules:** `required` `string` `numeric` `integer` `boolean` `email` `url` `uuid` `alpha` `alpha_num` `min` `max` `between` `in` `not_in` `confirmed` `regex` `date` `before` `after` `positive` `negative` `array` `nullable`

`nullable` short-circuits all subsequent rules when value is `null`. Non-required fields with `null` values silently skip all rules.

---

## Authentication — `Thunder\Auth`

```php
$jwt = new Jwt($_ENV['APP_SECRET']);

// Encode — pass any payload
$token = $jwt->encode(['sub' => $user->id, 'exp' => time() + 3600]);

// Decode — throws RuntimeException on invalid signature or expiry
$payload = $jwt->decode($token);
// ['sub' => 42, 'exp' => 1748000000]
```

Algorithm: HS256 (HMAC-SHA256), no external dependencies. `AuthMiddleware` reads `Authorization: Bearer <token>`, returns HTTP 401 on failure. Add it to the pipeline to protect routes.

---

## Rate Limiting — `Thunder\RateLimit`

```php
$limiter = new RateLimiter(new FileCache($storagePath));

// hit(key, maxRequests, windowSeconds)
$limiter->hit("rate_limit:{$ip}", 60, 60); // 60 req/min — throws TooManyRequestsException (429)
```

`RateLimitMiddleware` wraps this automatically using the client IP. Backed by any `CacheInterface` implementation.

---

## Database — `Thunder\Database`

### Connection
```php
$conn = new Connection([
    'driver' => 'pgsql', 'host' => '127.0.0.1',
    'port' => '5432', 'database' => 'thunder',
    'username' => 'postgres', 'password' => 'secret',
]);
$stmt = $conn->prepare('SELECT * FROM users WHERE id = ?');
$conn->execute($stmt, [42]);
```

### QueryBuilder
```php
$qb = new QueryBuilder($connection);

// SELECT
$users = $qb->table('users')
    ->select('id', 'name', 'email')
    ->where('active', '=', 1)
    ->whereIn('role', ['admin', 'user'])
    ->join('orders', 'users.id', '=', 'orders.user_id')
    ->orderBy('name')
    ->limit(15)->offset(0)
    ->get();               // returns array of associative arrays

$user = $qb->table('users')->where('id', '=', 42)->first(); // or null

// Aggregates
$qb->table('orders')->count();
$qb->table('orders')->max('total');
$qb->table('orders')->min('total');
$qb->table('orders')->avg('total');
$qb->table('orders')->sum('total');

// Write
$qb->table('users')->insert(['name' => 'Alice', 'email' => 'alice@example.com']);
$qb->table('users')->where('id', '=', 42)->update(['name' => 'Bob']);
$qb->table('users')->where('id', '=', 42)->delete();
```

### Repository (abstract base)
```php
class UserRepository extends Repository
{
    protected string $table = 'users';
}

$repo = new UserRepository($connection);
$repo->findAll();
$repo->findById(42);
$repo->create(['name' => 'Alice', 'email' => 'alice@example.com']);
$repo->update(42, ['name' => 'Bob']);
$repo->delete(42);
```

### Migrator
```php
// Migrations live in database/migrations/*.sql
// Tracked in migrations table (created by 002_create_migrations_table.sql)
$migrator = new Migrator($connection, 'database/migrations');
$migrator->run();      // runs pending migrations
$migrator->rollback(); // reverts the last batch
```

### Factory (abstract base)
```php
class UserFactory extends Factory
{
    protected string $model = User::class;

    public function definition(): array
    {
        return [
            'name'  => $this->faker->name(),
            'email' => $this->faker->email(),
        ];
    }
}

UserFactory::make();        // single instance
UserFactory::make(10);      // 10 instances
```

---

## Events — `Thunder\Events`

```php
// Register listener (class with handle() method)
$dispatcher->listen(OrderPlaced::class, SendConfirmationEmail::class);
$dispatcher->listen(OrderPlaced::class, ReduceStock::class);

// Dispatch — calls handle() on each registered listener
$dispatcher->dispatch(new OrderPlaced($order));

// Subscriber (one class registers multiple listeners)
$dispatcher->subscribe(OrderSubscriber::class);
// OrderSubscriber::subscribe(Dispatcher $d) { $d->listen(...); }
```

Listeners are resolved with `new $listenerClass()` — no container injection. If you need dependencies in listeners, inject them via the constructor and bind to the container.

---

## Queue — `Thunder\Queue`

```php
// Push a job (serializes to the `jobs` PostgreSQL table)
$queue->push(new SendEmailJob($user));

// Process the next job (called by a worker script or CLI command)
$queue->pop();
```

Implement `JobInterface`:
```php
class SendEmailJob implements JobInterface
{
    public function __construct(private readonly User $user) {}

    public function handle(): void
    {
        // send email logic
    }
}
```

Requires the `jobs` table from `database/migrations/001_create_jobs_table.sql`.

---

## Cache — `Thunder\Cache`

```php
// FileCache — persistent, TTL-based
$cache = new FileCache(__DIR__ . '/storage/cache');

// ArrayCache — in-memory, for tests
$cache = new ArrayCache();

$cache->set('user:42', $user, ttl: 3600);
$cache->get('user:42');                         // null if expired or missing
$cache->remember('user:42', fn() => findUser(42), ttl: 3600); // get or set
$cache->forget('user:42');
$cache->has('user:42');                         // bool
```

Both implement `CacheInterface`. Swap between them via the Container binding.

---

## Logger — `Thunder\Log`

```php
$logger = new FileLogger(__DIR__ . '/storage/logs/thunder.log');

$logger->debug('Cache miss', ['key' => 'user:42']);
$logger->info('User logged in', ['id' => $user->id]);
$logger->warning('Rate limit approaching', ['ip' => $ip]);
$logger->error('Payment failed', ['order' => $orderId]);
$logger->critical('DB connection lost');
```

Output format: `[2026-05-29 14:23:01] ERROR: Payment failed {"order":99}`

8 levels: `debug` `info` `notice` `warning` `error` `critical` `alert` `emergency`.

---

## Config — `Thunder\Config`

```php
// Loads all files under config/*.php automatically
$config->get('database.host');          // config/database.php → ['host' => ...]
$config->get('app.debug', false);       // with default
$config->has('mail.driver');            // bool
```

Config files return plain PHP arrays. No caching — reloaded per request.

---

## Console — `Thunder\Console`

```php
class MigrateCommand extends Command
{
    protected string $name        = 'migrate';
    protected string $description = 'Run pending migrations';

    public function handle(): void
    {
        // implementation
    }
}
```

Register in `thunder` (CLI entry point), then run:
```bash
php thunder migrate
php thunder          # lists all registered commands
```

---

## Error Handling — `Thunder\Error`

`ErrorHandler` is registered as the global exception handler in `public/index.php`. All unhandled exceptions return JSON:

```json
{"error": "Not found"}
{"error": "Validation failed", "details": {"email": ["The email field is required"]}}
```

When `APP_DEBUG=true`, stack traces are included in the response.

**HTTP exception classes:**

| Class | Status |
|-------|--------|
| `NotFoundException` | 404 |
| `UnauthorizedException` | 401 |
| `ForbiddenException` | 403 |
| `ValidationException` | 422 |
| `TooManyRequestsException` | 429 |
