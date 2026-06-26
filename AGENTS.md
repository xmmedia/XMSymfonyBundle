# AGENTS.md

This file provides guidance to AI agents (Claude Code, Codex, etc.) when working with code in this repository.

## Overview

This is a Symfony bundle (`xm/symfony-bundle`) that provides classes and services for XM Media Symfony projects. It implements Event Sourcing, CQRS (Command Query Responsibility Segregation), and GraphQL patterns using Prooph components.

## Development Commands

### Testing
- Run all tests: `composer test` or `vendor/bin/simple-phpunit`
- Run tests with coverage: `composer test:coverage` (generates HTML coverage in `coverage/`)
- Run a single test: `vendor/bin/simple-phpunit Tests/Path/To/TestFile.php`
- Run a single test method: `vendor/bin/simple-phpunit --filter testMethodName`

### Code Style
- Check code style: `composer cs`
- Fix code style: `composer cs:fix`
- Code style config: `.php-cs-fixer.dist.php` (uses Symfony, PSR-2, and PHP 8.1 migration rules with strict types enabled)

### Static Analysis
- Run PHPStan: `composer static`
- Config: `phpstan.neon.dist`

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

**Testing**: PHPUnit 9.6 is used. Tests extend PHPUnit\Framework\TestCase and use Mockery for mocking.

**Namespace**: All code is under `Xm\SymfonyBundle\`