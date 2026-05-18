# PageTurner Bookstore - Performance Benchmarks

This document provides an overview of the performance benchmarks and scalability features implemented in the PageTurner Bookstore project.

## 🚀 Performance Targets

| Feature | Goal | Implementation |
|---------|------|----------------|
| Catalog Listing | < 200ms | Composite Indexes, Cursor Pagination |
| ISBN Lookup | < 50ms | Redis Caching (Tags), ISBN Index |
| Search | < 300ms | Laravel Scout (MySQL Full-Text) |
| Memory Usage | < 256MB | Chunked Processing, Raw DB Inserts |

## 🛠 Scalability Features

### 1. Database Optimization
- **Composite Indexes**: Optimized for category filtering and publication date sorting.
- **Covering Indexes**: Fast retrieval of essential book data for catalog listing.
- **Full-Text Indexing**: Powered by Laravel Scout for efficient book searches.
- **Read/Write Splitting**: Configured for horizontal scaling with read replicas.

### 2. Redis Caching Architecture
- **Intelligent Caching**: Redis-backed cache for ISBN lookups, bestsellers, and categories.
- **Cache Tagging**: Precise invalidation using Laravel's cache tags (`books`, `categories`).
- **Smart Invalidation**: `BookObserver` handles automatic cache flushing on data changes.
- **Cache Warmup**: `WarmCategoryCache` job ensures top categories are pre-loaded.

### 3. Efficient Data Handling
- **Cursor Pagination**: Replaced offset-based pagination for stable performance on large datasets (10K+ records).
- **Mass Seeding**: `MassBookSeeder` uses raw DB inserts and chunking (1000/batch) for high-speed population.
- **Memory Management**: Explicit garbage collection during heavy data operations.

## 📊 Benchmarking

You can run the built-in benchmarking command to analyze current performance:

```bash
php artisan benchmark:books --iterations=100
```

## 🧪 Performance Testing

The project includes automated load tests in `tests/Performance`:

```bash
php artisan test tests/Performance/BookCatalogLoadTest.php
```

## 🔧 Infrastructure Configuration

Ensure your `.env` is configured for optimal performance:

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
SCOUT_DRIVER=database
SCOUT_QUEUE=true
```
