# AGENTS.md

This file provides guidance to AI agents (Claude Code, Codex, etc.) when working with code in this repository.

## Overview

This is a Symfony bundle (`xm/symfony-bundle`) that provides classes and services for XM Media Symfony projects. It implements Event Sourcing, CQRS (Command Query Responsibility Segregation), and GraphQL patterns using Prooph components.

## Development Commands

### Testing
- Run all tests: `composer test` or `vendor/bin/phpunit`
- Run tests with coverage: `composer test:coverage` (generates HTML coverage in `coverage/`)
- Run a single test: `vendor/bin/phpunit Tests/Path/To/TestFile.php`
- Run a single test method: `vendor/bin/phpunit --filter testMethodName`

### Code Style
- Check code style: `composer cs`
- Fix code style: `composer cs:fix`
- Code style config: `.php-cs-fixer.dist.php` (uses Symfony, PSR-2, and PHP 8.1 migration rules with strict types enabled)

### Static Analysis
- Run PHPStan: `composer static`
- Config: `phpstan.dist.neon`

### Refactoring
- Analyze with Rector (dry-run): `composer rector`
- Apply Rector changes: `composer rector:fix`
- Config: `rector.php`

## Architecture

### Event Sourcing & CQRS

This bundle implements event sourcing using Prooph Event Store:

**Aggregate Roots** (`EventSourcing/Aggregate/AggregateRoot.php`):
- Base class for domain models that emit events
- Must be created through static factories (constructor is protected)
- Use `EventProducerTrait` and `EventSourcedTrait`
- Events are stored in event streams and replayed to reconstruct state

**Domain Messages** (`Messaging/DomainMessage.php`):
- Base class for Commands and DomainEvents
- All messages include UUID, message name, timestamp, metadata, and payload
- Commands: Intent to change state (e.g., `AddUser`, `ChangeUser`)
- DomainEvents: Record that state changed (e.g., `UserWasAdded`, `UserWasChanged`)

**Projections** (`EventStore/Projection/`):
- Read models built from event streams
- Doctrine entities used for querying
- Projections listen to domain events and update read models

**Message Flow**:
1. GraphQL Mutation receives input
2. Creates and dispatches Command via Symfony Messenger
3. Command Handler loads Aggregate Root from repository
4. Aggregate Root applies business logic and emits Domain Event
5. Event is persisted to Event Store
6. Projection updates read model (Doctrine entity)
7. GraphQL Query reads from Doctrine entity

### Directory Structure

- `Model/` - Value Objects (Email, PhoneNumber, Address, etc.) and base classes (Entity, ValueObject, UuidId)
- `EventSourcing/` - Aggregate Root infrastructure and event sourcing components
- `Messaging/` - Command, DomainEvent, and Message base classes
- `EventStore/` - Event store persistence, metadata enrichers, and Messenger plugins
- `Doctrine/` - Custom Doctrine types and query builders
- `Infrastructure/` - Service implementations (GraphQL types, queries, email services, logging)
- `Maker/` - Symfony Maker commands (e.g., `make:model` generates complete CQRS/ES scaffolding)
- `Messenger/` - Symfony Messenger middleware for command/event handling
- `Security/` - Voters and security-related exceptions
- `Command/` - Symfony Console commands
- `DataProvider/` - Data providers for issuer, causation metadata
- `DataFixtures/` - Faker providers for test data
- `Util/` - Utility classes (StringUtil, PasswordStrength)
- `Tests/` - PHPUnit tests mirroring source structure

### Key Patterns

**Value Objects**: All value objects extend `ValueObject` interface. Examples include Email, PhoneNumber, PostalCode, Country, Province. They are immutable and contain validation logic.

**UUID Identifiers**: Model IDs extend `UuidId` and implement `UuidInterface`. They auto-generate UUIDs and provide type safety.

**Collections**: Use `ValueObjectCollection` for type-safe collections of value objects.

**GraphQL Integration**:
- Queries implement `Overblog\GraphQLBundle\Definition\Resolver\QueryInterface`
- Custom GraphQL types in `Infrastructure/GraphQl/Type/` (DateType, UuidType, GenderType, etc.)
- GraphQL config files generated in `config/graphql/types/`

**Session Expiry** (`Security/SessionExpiry.php`, `EventSubscriber/SessionExpirySubscriber.php`): on by default; disable with `xm_symfony.session_expiry: false`. Signs out users idle for longer than `framework.session.gc_maxlifetime`, since PHP's file session GC doesn't check a session's age when reading it. Every request by a signed in user extends the session, except routes with `_extend_session: false` in their defaults (`SessionExpiry::EXTEND_ATTRIBUTE`), eg an endpoint reporting the time remaining. Sessions with a remember-me cookie don't expire: `SessionExpiryPass` reads the cookie names from each firewall's remember-me config (the options of the `security.remember_me_handler` tagged services SecurityBundle creates — there's no public API for it), so nothing needs configuring. The services are only registered when enabled. It needs sessions & SecurityBundle, which every project using the bundle has.

**GraphQL Error Codes** (`EventSubscriber/GraphQlErrorSubscriber.php`, `Infrastructure/GraphQl/Error/`): Apollo style codes in a GraphQL error's `extensions.code`. Opt in: a project registers the subscriber itself (like `GraphQlContextInitialization`), since it changes what the frontend receives. The subscriber sets `UNAUTHENTICATED`/`FORBIDDEN` on Overblog's "Access denied to this field." (not signed in/signed in) & hides the message of a "Cannot query field" error from users who aren't signed in, except in debug (its "Did you mean …" suggestions reveal the schema). Other codes come from the `CodedUserError` thrown (`NotFoundError`, `LinkExpiredError`, `TooManyRequestsError`), which implements webonyx's `ProvidesExtensions`; add a code as a subclass returning it from `errorCode()`. `MAINTENANCE` (`MaintenanceGate::ERROR_CODE`, see Maintenance Mode) uses the same shape but isn't a `CodedUserError`: it's sent with a 503 before GraphQL runs, so the frontend receives it as a network error (Apollo's `networkError.result`), not in `graphQLErrors` of a normal response. The frontend's list of codes lives in each project.

**Maintenance Mode** (`Infrastructure/Service/Maintenance*.php`, `EventSubscriber/Maintenance*Subscriber.php`, `Command/MaintenanceCommand.php`): on by default; disable with `xm_symfony.maintenance: false`. It's on while the file `xm_symfony.maintenance.file` (default `%kernel.project_dir%/var/maintenance`, which must survive deploys) exists, so it's turned on & off without deploying: `app:maintenance on|off|status`, or a bare `touch` if the command won't run (an empty/unreadable file means on with the defaults). The file holds `MaintenanceSettings` as JSON: an optional message, end time (read & shown in `xm_symfony.maintenance.time_zone`, default PHP's), allowed IPs/CIDR ranges & the key. Running `on` while it's on updates it; `--allow-ip` adds, `--remove-ip`/`--reset-ips` remove, `--allow-my-ip` reads `SSH_CLIENT`. A new key is generated each time it's turned on (or with `--new-key`); the command shows its URL (`/?maintenance-key=…` on `framework.router.default_uri`). `on` also renders the page (`MaintenancePage`: `@XmSymfony/maintenance.html.twig`, standalone, polls & reloads once it's over; override in `templates/bundles/XmSymfonyBundle/`, extending `@!XmSymfony/…` to fill its blocks) to `<file>.html`; `off` deletes both. `MaintenanceGate` decides each request: allowed IPs (`getClientIp()`, so it depends on `framework.trusted_proxies`, or `mod_remoteip`) & requests with the key in the `X-Maintenance-Key` header or `maintenance-key` cookie get `_maintenance_bypass` set on the request (`BYPASS_ATTRIBUTE`) & carry on; the key in the query string sets the cookie (until the browser's closed) & redirects without it. Everyone else gets a 503, `X-Maintenance: 1`, `Retry-After` & `no-store`: the rendered page (or a plain fallback if the file was created by hand), or for `/graphql…` & requests accepting JSON a GraphQL style error with `extensions.code` `MAINTENANCE` & `extensions.until`. It only needs the autoloader, so the front controller should run it before the kernel's created, so it works even if the app won't boot: the Runtime sends a `Response` returned instead of the kernel — `return static fn (array $context): Kernel|Response => MaintenanceGate::handleGlobals(dirname(__DIR__).'/var/maintenance') ?? new Kernel(…);`. `MaintenanceSubscriber` (kernel.request 255: after `ValidateRequestListener`, before the session, routing & firewall, so no database) runs it too, for front controllers that don't & to set the bypass attribute, rendering the page if there's no file; it skips `/_` paths in debug. `MaintenanceWorkerSubscriber` pauses messenger workers when they start & between messages, waiting inside the worker (not exiting, so a supervisor doesn't restart it in a loop) until it's off; a stop signal (`ConsoleEvents::SIGNAL`) or `messenger:stop-workers` (read from `cache.messenger.restart_workers_signal`) still stops a paused worker.

**Metadata Enrichment**: Events are enriched with metadata (IP address, user agent, issuer, causation) via `EventStore/Metadata*Enricher.php` classes.

### Code Generation with `make:model`

The `AggregateRootMaker` (`Maker/AggregateRootMaker.php`) scaffolds a complete CQRS/ES model:

**Generated Components**:
- Aggregate Root class with ID and value objects
- Three Commands: Add, Change, Delete
- Three Domain Events: WasAdded, WasChanged, WasDeleted
- Command Handlers for each command
- Repository extending AggregateRepository
- Projection and ReadModel
- Doctrine Entity and Finder
- GraphQL types, queries (single, multiple, count), and mutations
- Complete test suite for all components
- GraphQL YAML configuration files

**After running `make:model`**, you must manually:
1. Add repository config to `config/packages/event_sourcing.yaml`
2. Add projection config to `config/packages/prooph_event_store.yaml`
3. Create event stream: `bin/console event-store:event-stream:create <stream_name>`
4. Update `App\Projection\Table` with new table constant
5. Update `App\Messenger\RunProjectionMiddleware` to map events to projections
6. Update GraphQL permissions
7. Regenerate GraphQL schema: `bin/console app:graphql:dump-schema`

## Important Conventions

**Strict Types**: All PHP files use `declare(strict_types=1);`

**Constructor Injection**: Services use constructor injection (readonly properties preferred for PHP 8.1+)

**Array Alignment**: Binary operator `=>` is aligned in arrays (enforced by php-cs-fixer)

**Testing**: PHPUnit 13 is used. Tests extend PHPUnit\Framework\TestCase and use Mockery for mocking. Test metadata uses attributes (e.g. `#[DataProvider]`), not doc-comment annotations, and data providers must be static.

**Namespace**: All code is under `Xm\SymfonyBundle\`