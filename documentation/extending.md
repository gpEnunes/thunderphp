# ThunderPhp — Extending the Framework

## Overview

ThunderPhp is designed to be used as a foundation layer for PHP applications. The framework itself lives in `src/Thunder/` and is never modified by application code. Applications sit alongside it with their own namespace.

## Bootstrapping a new application

The minimum setup in `public/index.php`:

```php
require_once __DIR__ . '/../vendor/autoload.php';

$router    = new \Thunder\Routing\Router();
$container = new \Thunder\Container\Container();
$app       = new \Thunder\Core\Application($router, $container);

// Register your service bindings
$container->bind(\Thunder\Log\LoggerInterface::class, \Thunder\Log\FileLogger::class);
$container->singleton(\Thunder\Database\ConnectionInterface::class, \Thunder\Database\Connection::class);

// Load routes
$app->loadRoutes(__DIR__ . '/../routes/api.php');

// Handle request
$request  = \Thunder\Http\Request::fromGlobals();
$response = $app->handle($request);
$response->send();
```

## Namespace convention

Use a separate top-level namespace for the application layer. Never put application code inside `Thunder\`.

```
src/
├── Thunder/        ← framework (never touch)
└── YourApp/        ← your application namespace
    ├── Controllers/
    ├── Repositories/
    ├── Events/
    └── Jobs/
```

Register in `composer.json`:
```json
"autoload": {
  "psr-4": {
    "Thunder\\": "src/Thunder/",
    "YourApp\\": "src/YourApp/"
  }
}
```

## Creating a controller

Controllers are plain PHP classes. They receive the `Request` and any route params, and return a `Response`.

```php
namespace YourApp\Controllers;

use Thunder\Http\RequestInterface;
use Thunder\Http\Response;

class UserController
{
    public function __construct(
        private readonly \YourApp\Repositories\UserRepository $users
    ) {}

    public function show(RequestInterface $request, string $id): \Thunder\Http\ResponseInterface
    {
        $user = $this->users->findById((int) $id);
        return Response::json(['user' => $user]);
    }
}
```

The Container autowires the constructor — bind `UserRepository` in your bootstrap file if needed, or just let the container resolve it directly by class name.

## Creating a repository

Extend `Thunder\Database\Repository`:

```php
namespace YourApp\Repositories;

use Thunder\Database\Repository;

class UserRepository extends Repository
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        return $this->queryBuilder()->table($this->table)
            ->where('email', '=', $email)
            ->first();
    }
}
```

## Creating an event listener

```php
namespace YourApp\Listeners;

class SendWelcomeEmail
{
    public function handle(object $event): void
    {
        // $event->user
        mail($event->user['email'], 'Welcome!', 'Thanks for signing up.');
    }
}
```

Register in bootstrap:
```php
$dispatcher->listen(\YourApp\Events\UserRegistered::class, \YourApp\Listeners\SendWelcomeEmail::class);
```

## Creating a console command

```php
namespace YourApp\Console\Commands;

use Thunder\Console\Command;

class SeedUsersCommand extends Command
{
    protected string $name        = 'seed:users';
    protected string $description = 'Seed the users table with test data';

    public function handle(): void
    {
        // seeding logic
        $this->line('Users seeded.');
    }
}
```

Register in the `thunder` CLI entry point.

## Trackier (Phase 1) usage

Trackier Phase 1 adapts the ThunderPhp Core layer directly into its `backend/src/Core/` folder. The framework source is not installed as a Composer dependency — the Core files are copied and maintained as part of the Trackier repository.

Key conventions carried over:
- Same `Container` implementation (reflection-based autowiring)
- Same `Router` with group/prefix support
- Same `Middleware\Pipeline`
- Same PSR-inspired `LoggerInterface`
- Namespace: `Trackier\Core\` instead of `Thunder\`

When ThunderPhp adds a new feature or fixes a bug, it should be manually evaluated for backport into `Trackier\Core\`.
