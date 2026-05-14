# Mid-Level PHP Laravel + Livewire + Alpine.js — Purchase Entry Module

**Project Goal**: Build a complete Purchase Entry Module with dynamic form, role-based permissions, and legacy data migration.

**Time Limit**: 24 hours

---

## Project Requirements

### Part 1 — Setup
- Use **latest stable Laravel**
- Install **Livewire** + **Alpine.js**
- Use Laravel Breeze or Jetstream (optional) for auth scaffolding
- Set up proper folder structure

### Part 2 — Database Schema

**Tables to create**:

1. `items` (id, name)
2. `brands` (id, name)
3. `purchases` (id, total, created_at, updated_at)
4. `purchase_items` (id, purchase_id, item_id, brand_id, qty, price)

**Relationships**:
- Purchase `hasMany` PurchaseItem
- PurchaseItem `belongsTo` Purchase, Item, Brand
- Item `hasMany` PurchaseItem
- Brand `hasMany` PurchaseItem

---

### Part 3 — Purchase Form (Livewire Component)

Create a **dynamic purchase form** with:

- Add/remove rows dynamically
- Each row contains:
  - Item (select or searchable)
  - Brand (select)
  - Quantity
  - Price per unit
- **Live total calculation** (using Alpine + Livewire)
- **Prevent duplicate** `item + brand` combinations in one purchase
- Real-time validation (show errors instantly)
- Use `wire:entangle` + Alpine.js for smooth reactivity

**Features**:
- Form validation using Laravel rules
- Auto-calculate line total and grand total
- Clean, modern UI

---

### Part 4 — Role-Based Permissions

**Roles**:
- **Admin**: Full access (Create, Read, Update, Delete purchases + Run migration)
- **User**: Read-only (View purchases only)

Use **Laravel Gates** or **Spatie Permission** package (recommended).

Protect:
- Routes
- Livewire components/actions
- Buttons (hide/show based on role)

---

### Part 5 — Legacy Data Migration

**Legacy Data**:
```php
$legacyPurchases = [
    [
        'item_name' => 'Sugar',
        'brand_name' => 'ABC',
        'qty' => 10,
        'price' => 100,
    ],
    // Add more entries as needed for testing
];
