# POS/Inventory Architecture Review & Implementation Checklist

## Executive summary
You’re on a solid architectural path: strong type safety, DTOs via Spatie Data, enums for domain states, lean models, and early adoption of an Actions pattern. The schema covers core POS/Inventory concepts and is production-minded (money as minor units, pivoted store stock). To harden this for real-world POS workloads (concurrency, stock integrity, accounting traceability), focus next on centralizing domain workflows in transaction-wrapped Actions, polymorphic relations for Payments/StockMovements, and a proper inventory layer/batch model for FIFO/FEFO.

## Key strengths
- Separation of concerns: Models are thin; Actions are HTTP-free; DTOs for response shaping; policies and permissions in place.
- Domain primitives: Enums for statuses/types with UX helpers; DTOs are typed, lazy, and predictable.
- Schema coverage: Products, taxes, purchases, sales, transfers, stock movements, store stock; minor-unit money; composite key on store_stock.
- Testing posture: Factories across domain, action tests scaffolded, resources/DTO tests present.

## Risks and gaps
- Business rules not yet centralized in Actions for core flows (sale, purchase, transfer, adjustments, payments).
- Payments and StockMovements rely on ad-hoc references rather than relational links.
- Batch/FIFO handled via remaining_quantity on PurchaseItem; workable but brittle under edits/returns.
- Inconsistent onDelete/nullability for created_by/updated_by and foreign keys.
- Mixed response strategies (Resources + Data) and timestamp formats.

## Recommendations
### Architecture and actions
- Centralize workflows in single-responsibility, transaction-wrapped Actions:
  - CreateSale: validate stock, price/tax calc, Sale + Items, decrement StoreStock, create StockMovements (out), optional Payment(s), emit SaleCreated event.
  - ReceivePurchase: Purchase + Items, increment StoreStock, StockMovements (in), optional Payments to Supplier, emit PurchaseReceived.
  - TransferStock: validate availability, decrement from/increment to, paired StockMovements, emit StockTransferred.
  - AdjustStock: reason-coded adjustments, movements only, optional audit entries.
  - ApplyPayment: typed target, reconcile totals and balances, link to Moneybox, emit PaymentApplied.
- Always use DB::transaction and row-level locks (select for update) on StoreStock (and future InventoryLayers) to prevent oversell under concurrency.
- Emit domain events for each action.

### Data modeling and relationships
- Payments: replace related_id + type with proper polymorphic relation (related_type, related_id). Add morphMap aliases.
- StockMovements: add polymorphic source relation (source_type, source_id); keep human-readable reference as denormalized string if desired.
- Inventory layers/batches: introduce inventory_layers (or product_batches) to track received layers with remaining_qty, batch_number, expiry_date, unit_cost, store_id. Reference layers from SaleItem consumption and returns for FIFO/FEFO.
- Foreign keys: define a consistent policy:
  - Master data deletions allowed? Then nullable FKs + nullOnDelete.
  - Otherwise restrictOnDelete and never delete users/master data.
- Indexes: add compound indexes on high-traffic queries and future-proof with check constraints (enforced in MySQL/PG later).

### Models and guards
- Prefer guarded = [] in models with Actions owning attribute control, or define precise fillable; choose one and apply consistently.
- Add reusable scopes (forStore, withStatus, active).
- Keep models free of business logic; push to Actions/services.

### DTOs and responses
- Standardize output strategy: either Spatie Data for all Inertia/API responses or Resources consistently; prefer one.
- Use ISO 8601 timestamps everywhere and format in the UI.
- Consider input Data objects (validated DTOs) for Action signatures (CreateSaleInputData, etc.) to enforce boundaries.

### Testing strategy
- Action tests as the core suite:
  - Invariants: no negative stock, correct totals/taxes, FIFO allocations.
  - Concurrency: simultaneous sales on same product/store.
  - Returns, partial consumption, expired batches.
- Scenario factories: StoreWithStock, PurchaseWithItems, SaleWithItems, TransferScenario.
- Serialization tests: enums and lazy relations behave predictably.

### Operational hardening
- Soft deletes on master data (Product, Category, Brand, Unit, Client, Supplier, Store) and policies for deleting users.
- Events + listeners for receipts, notifications, external sync.
- Reference/number generators centralized with DB uniqueness and retries.
- Settings for tax policy (inclusive/exclusive), currency, default store, number formats; cache these.

## Implementation checklist
### Actions and workflows
- [ ] CreateSale action with transaction, stock validation, tax/discount calc, Sale/SaleItem persistence, StoreStock decrement, StockMovements (out), optional Payments, SaleCreated event.
- [ ] ReceivePurchase action with transaction, Purchase/PurchaseItem, StoreStock increment, StockMovements (in), optional Payments, PurchaseReceived event.
- [ ] TransferStock action with transaction, paired StockMovements, StoreStock from/to updates, StockTransferred event.
- [ ] AdjustStock action with reason-coded changes and audit trail.
- [ ] ApplyPayment action with polymorphic link to target, reconciliation, Moneybox integration, PaymentApplied event.
- [ ] Concurrency: row locks on StoreStock (and InventoryLayers once added).

### Schema changes
- [x] Payments: add related_type, related_id (polymorphic); backfill from existing fields; drop legacy type fields.
- [x] StockMovements: add source_type, source_id (polymorphic); migrate references.
- [x] Introduce inventory_layers/product_batches:
      product_id, store_id, batch_number, expiry_date, unit_cost, received_qty, remaining_qty, received_at
      unique(product_id, store_id, batch_number, expiry_date)
- [x] Remove remaining_quantity from PurchaseItem once layers adopted; migrate data.
- [x] Align FK onDelete policies; make user FKs nullable if user deletion is allowed; otherwise restrict.
- [x] Add/verify supporting indexes on frequent filters and orderings.

### Models and domain services
- [ ] Choose guarded = [] vs fillable and apply uniformly.
- [ ] Add scopes for common filters (status, store, date range).
- [ ] Number/reference generator service with collision retry.

### DTOs and API/Inertia
- [ ] Standardize on Spatie Data (or Resources) for all outbound responses.
- [ ] Ensure ISO 8601 timestamps; remove mixed formatting.
- [ ] Add input Data DTOs for Actions for clear boundaries and typing.

### Testing
- [ ] Action tests for all workflows, including edge cases and concurrency.
- [ ] Scenario factories (StoreWithStock, PurchaseWithItems, etc.).
- [ ] Serialization tests for DTOs/Resources and enums.

### Operations and settings
- [ ] Soft deletes on master data and policy coverage across aggregates.
- [ ] Domain events wired to listeners (receipt generation, notifications).
- [ ] Settings for tax behavior, default store, currency, and formatting with caching.
- [ ] Observers or Action fill for created_by/updated_by consistently.
