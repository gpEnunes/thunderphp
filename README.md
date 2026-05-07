# ThunderPhp ⚡

A PHP 8.3 framework built from scratch — no dependencies, no shortcuts.

Built as a learning project to deeply understand design patterns by implementing the internals of a real framework: routing, MVC, DI container, ORM, templating, queues, caching, and events — all from the ground up.

The application layer is a headless e-commerce backend (products, orders, payments, cart).

## Stack

- PHP 8.3
- PostgreSQL
- Pest (testing)

## Framework Modules

| Module | Pattern(s) |
|---|---|
| Router | Front Controller, Strategy |
| HTTP (Request/Response) | Value Object |
| DI Container | Factory, Reflection |
| QueryBuilder | Builder |
| ORM | Repository, DataMapper |
| Events | Observer |
| Queue | Command |
| Cache | Decorator |
| View | Template Method |

## Getting Started

```bash
composer install
cp .env.example .env
composer test
```

## Structure

```
src/Thunder/    # Framework internals
app/            # E-commerce application
public/         # Web root (single entry point)
tests/          # Pest test suite
```
