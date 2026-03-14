# Architecture Notes

## Purpose
This document describes the architectural direction of the project in a reusable, framework-aware way.

It is intentionally abstract. The goal is to define placement rules and design constraints that remain valid even if concrete modules, features, or integrations change.

## Core Principles

### 1. Business behavior belongs to domain modules
If a piece of logic expresses business rules, state transitions, orchestration of a business flow, or feature-specific use cases, it should live in the module that owns that domain.

Domain modules are the default home for:
- entities and models
- domain services
- feature-specific orchestration
- feature-specific command handlers
- feature-specific console entry points
- tests and documentation for that feature

Rule:
- if code exists to serve one feature, keep it inside that feature's module

### 2. Keep feature code physically concentrated
A feature should be easy to understand by opening one module directory and seeing most of its moving parts in one place.

This usually means keeping together:
- models
- services
- controllers
- console controllers
- tests
- README

Benefits:
- lower discovery cost
- clearer ownership
- safer refactoring
- less accidental coupling across unrelated areas

### 3. Separate domain logic from infrastructure logic
Technical capabilities should not know domain models unless there is a strong reason.

Infrastructure code should focus on:
- external providers
- file handling
- normalization/transformation
- HTTP/API clients
- protocol adapters
- generic registries
- technical algorithms

Infrastructure code should avoid:
- feature-specific entities
- transport-specific assumptions
- business-state mutations

The domain layer should orchestrate infrastructure, not the other way around.

### 4. Use a service layer for reusable technical capabilities
If logic is reusable across features and is not tied to one module's business language, place it in a shared service layer.

Typical candidates:
- AI integrations
- speech/text processing
- normalization pipelines
- generic command infrastructure
- external provider clients

Service-layer code should be as framework-light as practical.

Goals:
- simpler tests
- easier reuse
- easier extraction into a separate package later

### 5. Use framework components as thin adapters
If a reusable service needs application configuration or application lifecycle integration, expose it through a framework component.

A component should usually do only this:
- read configuration
- instantiate the underlying service
- expose a stable application-level entry point

Heavy logic should stay in services, not in components.

Pattern:
- service layer contains the real implementation
- component layer adapts the service to the application container/config

Preferred lifecycle rule:
- register definitions early
- instantiate heavy or numerous objects lazily
- cache instantiated objects once created

This keeps bootstrap cheap while still avoiding repeated object creation during normal execution.

### 6. Prefer a shared command registry over channel-specific command logic
If multiple input channels can produce the same command text or intent, the command layer should not belong to one channel.

Instead:
- normalize input text
- send it into a shared command registry
- let registered commands decide whether they support the input
- parse and execute through the domain-owned command implementation

Benefits:
- one command path for multiple input channels
- no duplication of command logic
- commands live next to the domain they mutate
- easier extension with new channels later

Recommended registry behavior:
- command definitions may be registered during bootstrap
- command objects should be instantiated lazily on first real use
- instantiated command objects should then be cached and reused

This avoids paying object construction cost during application startup while still keeping runtime execution efficient.

### 7. Let modules bootstrap their own integrations
Modules should be able to register their own runtime integrations instead of forcing unrelated global config to know feature internals.

Examples of module-owned integrations:
- command registration
- event subscribers
- workflow hooks
- feature-specific app wiring

Use a shared bootstrap runner that:
- scans configured modules
- checks whether a module declares a bootstrap class
- runs that bootstrap class during application startup

Recommended bootstrap behavior:
- bootstrap should register definitions and integrations
- bootstrap should avoid eagerly constructing large graphs of runtime objects unless they are immediately needed

This keeps global config thin and shifts ownership back into the module.

## Layering Model

### Transport Layer
Responsibilities:
- receive external input
- normalize protocol-specific payloads
- authenticate requests if needed
- delegate into domain/application logic

Examples in principle:
- HTTP controllers
- webhook handlers
- bot adapters
- CLI entry points

Transport layer should stay thin.
It should not become the home of business rules.

### Domain Module Layer
Responsibilities:
- business entities
- business services
- feature workflows
- state transitions
- feature-owned commands

This is the main home for application behavior.

### Shared Service Layer
Responsibilities:
- reusable technical logic
- provider integrations
- framework-light services
- algorithmic helpers
- generic registries

This is the best candidate for future extraction into a separate package.

### Component Layer
Responsibilities:
- bind shared services into the application container
- provide config-driven wrappers
- expose stable entry points through the framework

Components should remain thin and predictable.

## Command Architecture

### Desired flow
1. an input channel receives text or produces normalized text
2. the text is passed to a shared command registry
3. registered commands evaluate whether they support the input
4. the matching command parses input into payload
5. the command executes through the owning domain
6. a structured execution result is returned

### Why this matters
This prevents command logic from being owned by one transport channel.

A command should not conceptually belong to:
- a chat adapter
- a webhook controller
- a voice-processing pipeline

It should belong to the domain whose state it changes.

## Bootstrap Architecture

### Desired flow
1. the application starts
2. a shared bootstrap component runs early
3. it inspects configured modules
4. if a module declares a bootstrap class, that class is executed
5. the module bootstrap registers feature-specific integrations

### Why this matters
Without this pattern, global config tends to accumulate feature-specific knowledge.

That causes:
- weaker module boundaries
- poorer ownership
- harder reuse
- more fragile refactoring

Module bootstrap shifts integration knowledge back to the module itself.

## Placement Rules

When adding code, ask these questions in order:

### 1. Is this business behavior for one feature?
Put it in that feature's module.

### 2. Is this reusable technical logic independent of one feature?
Put it in the shared service layer.

### 3. Does this only adapt a service to framework config/lifecycle?
Put it in the component layer.

### 4. Is this only about an external protocol or entry point?
Keep it in the transport layer.

### 5. Is this registration/wiring owned by one module?
Put it in that module's bootstrap class.

## Decision Heuristics

Use these quick rules when placement is unclear:

- if code mutates business state, it probably belongs in a domain module
- if code wraps an external provider or technical capability, it probably belongs in the shared service layer
- if code only exposes configuration-driven access to a service, it probably belongs in the component layer
- if code only receives or translates protocol-specific input, it probably belongs in the transport layer
- if code exists only to initialize one feature at startup, it probably belongs in that feature's bootstrap class
- if understanding a feature requires jumping across many unrelated directories, ownership is probably too fragmented

## Dependency Direction

The preferred dependency direction is:

- transport layer may depend on domain modules and components
- domain modules may depend on shared services and components
- components may depend on shared services
- shared services should not depend on transport-layer code
- shared services should avoid depending on domain entities unless there is a strong reason

Preferred flow:

- input enters through transport
- transport delegates to domain
- domain orchestrates shared services
- components expose configured access to shared services

This keeps business rules closer to the center and technical adapters closer to the edge.

## Anti-Patterns To Avoid

- putting reusable business logic in transport-specific modules
- allowing global console/controller directories to become a dumping ground for feature-specific workers
- making infrastructure services depend on domain entities
- registering feature-specific runtime behavior only in global config when the feature can own it
- duplicating command logic for each input channel
- placing heavy logic inside framework components
- leaking protocol concerns into the domain layer

## Exceptions

These rules are strong defaults, not absolute laws.

Reasonable exceptions can exist when:
- introducing a shared abstraction would add more complexity than it removes
- a feature is still highly experimental and not yet stable enough to extract cleanly
- performance or lifecycle constraints require a slightly less pure placement
- a framework limitation makes a cleaner dependency direction impractical

When making an exception:
- keep it explicit
- document why it exists
- keep the boundary narrow
- avoid turning a local exception into a project-wide pattern

## Practical Guidance

### When designing a new feature
- identify the owning domain first
- keep transport handling thin
- keep orchestration close to the domain
- push reusable technical behavior into shared services
- expose shared services through components only when framework integration is needed
- register module-owned integrations through module bootstrap

### When refactoring existing code
Good signs that code is in the wrong place:
- a transport controller contains business rules
- a shared service knows too much about one feature's entity model
- one feature requires reading many unrelated directories to understand it
- global config hardcodes feature internals
- the same command or workflow logic appears in multiple channels

### When in doubt
Default to stronger ownership and clearer locality:
- first try to keep code in the owning module
- only move it to shared infrastructure when reuse is real or clearly imminent
