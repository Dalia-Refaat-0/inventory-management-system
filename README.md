<div align="center">

# 🏭 Inventory Management System

[![PHP](https://img.shields.io/badge/PHP-8.1+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![Redis](https://img.shields.io/badge/Redis-Cache-DC382D?style=flat-square&logo=redis&logoColor=white)](https://redis.io)

</div>

---

## Table of Contents

- [API Collection](#api-collection)
- [Technical Implementation](#technical-implementation)
- [Architectural Patterns](#architectural-patterns)
- [Performance Optimizations](#performance-optimizations)
- [Testing Strategy](#testing-strategy)
- [Project Structure](#project-structure)
- [Getting Started](#getting-started)

---

## API Collection

Postman collection and environment files are available in the project root:
- `inventory-management-system.postman_collection.json`
- `inventory-management-system.postman_environment.json`

## Technical Implementation

### Core Technologies

| Layer | Technology | Purpose |
|:------|:-----------|:--------|
| **Authentication** | Laravel Sanctum | Token-based API authentication |
| **Validation** | Spatie Laravel Data | DTO validation using attributes |
| **Caching** | Redis | Tag-based invalidation with stampede prevention |
| **Pagination** | Cursor Pagination | Scalable pagination for large datasets |
| **Filtering** | Pipeline Pattern | Composable and extensible query filters |
| **Domain Logic** | Value Objects | SKU enforcement via custom Eloquent Cast |
| **State Management** | PHP 8.1 Enums | Transfer status validation |
| **Query Layer** | Custom Builders | Reusable query scopes |
| **Data Access** | Repository Pattern | Clean abstraction with caching decorators |
| **Errors** | Custom Exceptions | Self-rendering HTTP responses |
| **Events** | Laravel Queue System | Asynchronous event listeners |
| **Concurrency** | Pessimistic Locking | Prevent race conditions during transfers |

---

## Architectural Patterns

### Repository + Decorator Pattern

Caching repositories wrap Eloquent repositories, enabling transparent caching without modifying core data access logic.

---

### Pipeline Pattern

Filters are implemented as composable, single-responsibility classes:

| Filter | Description |
|:-------|:------------|
| `Search` | Full-text search across fields |
| `MinPrice` | Minimum price filtering |
| `MaxPrice` | Maximum price filtering |
| `WarehouseFilter` | Filter by warehouse location |

> ✅ **Benefit:** New filters can be added without modifying existing logic.

---

### Value Object — SKU

Encapsulates SKU validation rules and enforces domain invariants during object construction.

| Component | Purpose |
|:----------|:--------|
| `SKU` | Value object with validation logic |
| `SKUCast` | Custom Eloquent cast for serialization |

---

### State Management — TransferStatus Enum

Transfer state is controlled using PHP 8.1 Enum.

> ✅ **Benefit:** Prevents invalid transitions and enforces business rules.

---

### DTOs (Data Transfer Objects)

Implemented using **Spatie Laravel Data**:

| DTO | Purpose |
|:----|:--------|
| `InventoryFilterData` | Search and filter parameters |
| `TransferStockData` | Transfer request validation |
| `StockTransferCreationData` | Transfer creation payload |
| `WarehouseInventoryData` | Warehouse inventory response |

> ✅ **Benefit:** Ensures validated and typed data before reaching business logic.

---

### Concurrency Handling

Stock transfers are protected using multiple safeguards:

| Mechanism | Purpose |
|:----------|:--------|
| Database Transactions | Ensure atomicity |
| Pessimistic Locking | Prevent concurrent modifications |
| Atomic Quantity Updates | Maintain data consistency |

> ✅ **Benefit:** Prevents race conditions and inconsistent stock states.

---

## Performance Optimizations

| Optimization | Implementation |
|:-------------|:---------------|
| **Cursor Pagination** | Scalable results for large datasets |
| **Tag-based Redis Caching** | Precise cache invalidation |
| **Cache Stampede Prevention** | Lock-based cache population |
| **Strategic Database Indexes** | Optimized query execution |
| **Eager Loading** | Prevents N+1 queries |
| **Custom Query Builders** | Reusable, optimized scopes |

---

## Default Credentials

<details>
<summary>📌 Click to reveal test credentials</summary>
<br>

| Field | Value |
|:------|:------|
| Email | `test@example.com` |
| Password | `password` |

> ⚠️ **Warning:** These are seeded test credentials.

</details>

---

## Testing Strategy

| Test Type | Coverage |
|:----------|:---------|
| **Unit Tests** | Services, Value Objects, Enums |
| **Feature Tests** | API Endpoints, Request/Response |

---

## Project Structure

```text
app/
├── Builders/
│   ├── InventoryItemBuilder.php
│   ├── StockBuilder.php
│   └── WarehouseBuilder.php
├── Casts/
│   └── SKUCast.php
├── Contracts/
│   ├── InventoryItemRepositoryInterface.php
│   ├── StockRepositoryInterface.php
│   ├── StockTransferRepositoryInterface.php
│   └── WarehouseRepositoryInterface.php
├── DTOs/
│   ├── InventoryFilterData.php
│   ├── TransferStockData.php
│   ├── StockTransferCreationData.php
│   └── WarehouseInventoryData.php
├── Enums/
│   └── TransferStatus.php
├── Events/
│   └── LowStockDetected.php
├── Exceptions/
│   └── InsufficientStockException.php
├── Http/
│   ├── Controllers/
│   └── Resources/
├── Listeners/
│   └── NotifyWarehouseManager.php
├── Models/
│   ├── InventoryItem.php
│   ├── Stock.php
│   ├── StockTransfer.php
│   ├── User.php
│   └── Warehouse.php
├── QueryFilters/
│   ├── Filter.php
│   ├── MaxPrice.php
│   ├── MinPrice.php
│   ├── Search.php
│   └── WarehouseFilter.php
├── Repositories/
│   ├── Decorators/
│   │   ├── CachingInventoryItemRepository.php
│   │   ├── CachingStockRepository.php
│   │   └── CachingWarehouseRepository.php
│   └── Eloquent/
│       ├── EloquentInventoryItemRepository.php
│       ├── EloquentStockRepository.php
│       ├── EloquentStockTransferRepository.php
│       └── EloquentWarehouseRepository.php
├── Services/
│   ├── StockTransferService.php
│   └── Cache/
│       ├── CacheKeyGenerator.php
│       └── CacheService.php
└── ValueObjects/
    └── SKU.php
```

---

## Getting Started

```bash
git clone https://github.com/Dalia-Refaat-0/inventory-management-system.git
cd inventory-management-system
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```
