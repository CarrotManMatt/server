# Workflow engine developer guide

This app provides the infrastructure behind Nextcloud's **flows** UI.

The goal of this document is to give a developer who does not know the app a quick mental model of how the pieces fit together, and how to add new ones.

## Terminology

A few names are used for closely related things:

- **Flow**: the user-facing term used in the settings UI.
- **Workflow rule**: the stored configuration record in the backend/API.
- **Operation**: the action part of a flow, implemented by an app through `OCP\WorkflowEngine\IOperation`.

In practice, a **flow / workflow rule** is:

1. an **operation** to run,
2. for a given **entity**,
3. on one or more **events** exposed by that entity,
4. if all configured **checks** match,
5. within a given **scope**.

So when the UI says “flow”, think “a configured rule made of operation + entity/events + checks”.

## The core concepts

### Entity

An entity is the thing the rule is about.

Backend contract: `OCP\WorkflowEngine\IEntity`

An entity defines:

- a display name and icon for the UI,
- the events it supports,
- how runtime context is prepared for rule matching,
- whether a user is allowed to run user-scoped flows against the current context.

The built-in entity in this app is `OCA\WorkflowEngine\Entity\File`.
It exposes file-related events such as create, update, rename, delete, access, copy, and tag assignment.

A good way to think about an entity is: **it provides the event source and the subject that checks evaluate against**.

### Event

Events are declared by an entity via `IEntity::getEvents()`.
Each event has:

- a machine name (`IEntityEvent::getEventName()`),
- a display name (`IEntityEvent::getDisplayName()`).

A configured rule stores both the chosen entity class and the selected event names.
During validation, `Manager::validateEvents()` ensures the selected events really belong to the chosen entity.

### Check

A check is a condition that must evaluate to true for a rule to match.

Backend contract: `OCP\WorkflowEngine\ICheck`

A check defines:

- how to execute the condition (`executeCheck()`),
- how to validate configuration (`validateCheck()`),
- which entities it supports (`supportedEntities()`),
- which scopes it is available in (`isAvailableForScope()`).

Important details:

- A rule must contain at least one check.
- All checks of a rule must pass for the rule to match.
- `Manager::validateOperation()` rejects checks that do not implement `ICheck`, are not registered/resolvable, or are not allowed for the selected entity.

There are two useful specializations:

- `IEntityCheck`: the engine passes the entity subject to the check.
- `IFileCheck`: the engine additionally passes file storage/path information.

This app ships several built-in checks in `lib/Check/`, for example:

- file name,
- file MIME type,
- file size,
- file system tags,
- request URL,
- remote address,
- user agent,
- request time,
- user group membership.

### Operation

An operation is the action part of the rule.

Backend contract: `OCP\WorkflowEngine\IOperation`

An operation defines:

- how it appears in the UI (`getDisplayName()`, `getDescription()`, `getIcon()`),
- which scopes it supports (`isAvailableForScope()`),
- how its stored configuration is validated (`validateOperation()`),
- what happens when a matching event is received (`onEvent()`).

Important clarification: this app itself mainly provides the engine, entity, and checks. It does **not** ship general built-in operations today (`Manager::getBuildInOperators()` returns an empty list). In real installations, operations are usually contributed by other apps.

Example: `files_accesscontrol` registers `OCA\FilesAccessControl\Operation`, which blocks access to files when its configured checks match.

### Complex operation

Contract: `OCP\WorkflowEngine\IComplexOperation`

Use `IComplexOperation` when the operation cannot rely on the engine's normal “listen to entity events and call `onEvent()`” flow.

A complex operation is responsible for more of its own triggering logic. It provides `getTriggerHint()` so the UI can explain to the user when it becomes active.

Note: for complex operations, the configured event list may be empty, and that is accepted by `Manager::validateEvents()`.

### Specific operation

Contract: `OCP\WorkflowEngine\ISpecificOperation`

Use `ISpecificOperation` if the operation only makes sense for exactly one entity type. The operation returns that fixed entity class from `getEntityId()`, and the UI can avoid offering other entities.

## What a flow looks like in practice

A stored workflow rule contains roughly these pieces:

- `class`: the operation class,
- `entity`: the entity class,
- `events`: the selected entity event names,
- `checks`: the configured checks,
- `operation`: operation-specific configuration payload,
- `scope`: admin or user scope (stored separately in `flow_operations_scope`).

The admin/personal settings UI phrases this roughly as:

- **When** event X happens for entity Y,
- **and** check A matches,
- **and** check B matches,
- then perform **operation** Z.

## How matching works at runtime

The runtime path is:

1. `Application::registerRuleListeners()` reads configured operations/events.
2. For each configured event, it registers an event listener.
3. When the event fires, the engine creates an `IRuleMatcher`.
4. The entity prepares the matcher with runtime context via `IEntity::prepareRuleMatcher()`.
5. The operation receives `onEvent()`.
6. The operation asks the matcher for matching flows.
7. The matcher loads rules for the relevant scopes and evaluates all checks.
8. If a rule matches, the operation decides what to do with that result.

`RuleMatcher` is the component that actually evaluates the checks.
It only returns a match when **all** checks in the configured rule pass.

## Scope model

The engine supports two scopes through `OCP\WorkflowEngine\IManager`:

- `SCOPE_ADMIN`: global rules configured by admins,
- `SCOPE_USER`: personal rules configured by a user.

Things to keep in mind:

- operations and checks can opt into admin scope only, or support user scope too,
- the settings UI filters available operations/checks by the current scope,
- the matcher checks global scope first and may also expand to additional user scopes if the entity says the user is legitimized for the current context.

If you add a new entity/check/operation, be explicit about which scopes it supports.

## Concrete example

A typical file-based flow looks like this:

- **Entity**: `OCA\WorkflowEngine\Entity\File`
- **Event**: “File created”
- **Checks**:
  - `FileMimeType` is `application/pdf`
  - `UserGroupMembership` is `Legal`
- **Operation**: an operation provided by another app, for example access control

At runtime:

1. the file entity converts the incoming file event into matcher context,
2. the matcher loads all rules for the operation in the active scopes,
3. `FileMimeType` and `UserGroupMembership` are executed,
4. if both pass, the operation's logic applies.

## How to add a new entity

1. Implement `OCP\WorkflowEngine\IEntity`.
2. Return a clear name, icon, and list of supported events.
3. In `prepareRuleMatcher()`, provide the matcher with the subject/context that checks will need.
4. Implement `isLegitimatedForUserId()` if user-scoped flows can apply to contexts owned or shared with other users.
5. Register the entity when `RegisterEntitiesEvent` is dispatched.

Typical registration pattern in your app bootstrap:

- register an event listener for `OCP\WorkflowEngine\Events\RegisterEntitiesEvent`,
- inside the listener call `$event->registerEntity($yourEntity)`.

## How to add a new check

1. Implement `OCP\WorkflowEngine\ICheck`.
2. If the check needs the entity subject, also implement `IEntityCheck`.
3. If it needs file storage/path data, implement `IFileCheck`.
4. Validate operators and values in `validateCheck()`.
5. Restrict supported entities with `supportedEntities()` when needed.
6. Restrict scopes with `isAvailableForScope()` when needed.
7. Register the check on `RegisterChecksEvent`.

A good check is small and focused: one condition, one clear meaning.

## How to add a new operation

1. Implement `OCP\WorkflowEngine\IOperation`.
2. Provide display metadata for the settings UI.
3. Validate your serialized operation payload in `validateOperation()`.
4. Decide whether the operation is:
   - a normal operation (`IOperation`),
   - a complex operation (`IComplexOperation`),
   - a specific operation (`ISpecificOperation`),
   - or a combination such as `files_accesscontrol`, which is both complex and specific.
5. Register the operation on `RegisterOperationsEvent`.

Use a normal `IOperation` when the engine can trigger you directly from configured entity events.
Use `IComplexOperation` when your app needs to own the triggering mechanism itself.
Use `ISpecificOperation` when only one entity type makes sense.

## Optional UI integration for custom checks and operations

The backend contracts are enough to participate in matching, but apps can also improve the settings UI.

The frontend exposes:

- `window.OCA.WorkflowEngine.registerCheck(...)`
- `window.OCA.WorkflowEngine.registerOperator(...)`

Use these to provide custom editors, placeholders, validation helpers, colors, or web components for your check/operation configuration.

## Validation notes and caveats

- A rule without checks is invalid.
- Non-complex operations must have at least one selected event.
- A check can be valid globally but still be rejected for a specific entity if `supportedEntities()` does not include that entity class.
- Scope support is validated both in the UI and in backend validation.
- If a configured class cannot be resolved anymore, matching may skip it at runtime; validation is stricter when saving rules.

## Where to look in the code

Good starting points:

- `lib/Manager.php` — registration, validation, persistence, built-in checks/entities
- `lib/Service/RuleMatcher.php` — rule evaluation
- `lib/AppInfo/Application.php` — runtime event listener wiring
- `lib/Entity/File.php` — reference entity implementation
- `lib/Check/` — reference check implementations
- `src/components/Rule.vue` — how the UI presents a configured flow

If you are new to the app, start by reading `File`, one small check implementation, and one external operation such as `files_accesscontrol`'s `Operation` together. That gives the full picture quickly.
