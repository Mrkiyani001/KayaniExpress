# 🏗️ KayaniExpress — Microservices Future Plan
> Current monolith ko extend karna — Remaining services as Microservices

---

## 🧠 Mental Model (Yeh Samjho Pehle)

### Monolith vs Microservices

```
CURRENT (Monolith — Yeh Project):
──────────────────────────────────
Browser → Laravel App
              ├── AuthController
              ├── OrderController
              ├── CouponController
              └── Sab ka ek hi Database

TARGET (Hybrid — Monolith + New Microservices):
──────────────────────────────────────────────────────
Browser → API Gateway (Nginx Port 80)
              ├── /api/auth/*        → Monolith (existing) :8000
              ├── /api/order/*       → Monolith (existing) :8000
              ├── /api/notification/*→ notification-service :8006  🔴 NEW
              ├── /api/wallet/*      → wallet-service       :8007  🔴 NEW
              ├── /api/promotions/*  → promotion-service    :8008  🔴 NEW
              └── /api/analytics/*  → analytics-service    :8009  🔴 NEW
```

### RabbitMQ Kya Hai? (Simple Explanation)

```
Sochlo RabbitMQ ek POST OFFICE hai:

order-service → "Order place hua" letter likhao → POST OFFICE mein daalo
                                                          │
                                           ┌──────────────┤
                                           │              │
                                    notification    wallet-service
                                    -service        "seller ko pay karo"
                                    "email bhejo"

→ Publisher:  Letter likhne wala (Monolith/order-service)
→ Exchange:   Post office ka sorting room
→ Queue:      Specific mailbox (har service ka alag)
→ Consumer:   Letter padhne wala (notification-service, wallet-service)
```

---

## 🗂️ Service Map

| Service | Port | Status | Database | Responsibility |
|---------|------|--------|----------|----------------|
| Monolith (existing) | 8000 | ✅ Done | `e-commerce` | Auth, Orders, Products, Coupons, Reviews |
| notification-service | 8001 | ✅ Done | `db_notifications` | Email/DB notifications |
| wallet-service | 8007 | 🔴 Remaining | `db_wallet` | Seller payouts, withdrawals |
| promotion-service | 8008 | 🔴 Remaining | `db_promotions` | Flash sales, Banners |
| analytics-service | 8009 | 🔴 Remaining | `db_analytics` | Dashboards, Reports |
| api-gateway | 80 | 🔴 Remaining | — | Nginx routing |

---

## 📬 Events Flow (Monolith → New Services)

```
MONOLITH (Publisher)                    NEW SERVICES (Consumers)
────────────────────────────────────────────────────────────────

✅ Order Placed
   → Publish: order.placed     ──────► notification-service (email buyer)
                                ──────► wallet-service (add pending payout)

✅ Order Delivered
   → Publish: order.delivered  ──────► notification-service (notify buyer)
                                ──────► wallet-service (release withdrawable)

✅ Order Cancelled
   → Publish: order.cancelled  ──────► notification-service (notify buyer)
                                ──────► wallet-service (reverse pending)

✅ Shop Approved (by admin)
   → Publish: shop.approved    ──────► notification-service (welcome email)
                                ──────► wallet-service (create wallet record)

✅ User Registered
   → Publish: user.registered  ──────► notification-service (OTP/welcome)
```

---

## 📋 Implementation Order

```
Step 1: Docker + RabbitMQ Setup       ← Foundation (sab ka zaruri)
Step 2: Monolith mein Events Add karo ← Bridge between old & new
Step 3: notification-service banao    ← Simplest service (yahan se shuru)
Step 4: wallet-service banao          ← Medium complexity
Step 5: promotion-service banao       ← Simple CRUD (no events needed)
Step 6: analytics-service banao       ← Last (complex aggregations)
Step 7: Nginx API Gateway             ← Unify sab ko
```

---

## 🐋 Step 1: Docker + RabbitMQ Infrastructure

### Hint: Project ke parallel folder mein rakho
```
kayaniexpress-microservices/        ← New parent folder
├── E-commerce/                     ← Existing monolith (yahi project)
├── notification-service/           ← New Laravel project
├── wallet-service/                 ← New Laravel project
├── promotion-service/              ← New Laravel project
├── analytics-service/              ← New Laravel project
├── api-gateway/                    ← Nginx config
└── docker-compose.yml              ← Sab ko chalane wala
```

### `docker-compose.yml` (parent folder mein):
```yaml
version: '3.8'

services:
  # ── Message Broker ──────────────────────────
  rabbitmq:
    image: rabbitmq:3-management
    container_name: kayani_rabbitmq
    ports:
      - "5672:5672"    # Services isse connect karti hain (AMQP)
      - "15672:15672"  # Browser se monitor: http://localhost:15672
    environment:
      RABBITMQ_DEFAULT_USER: kayani
      RABBITMQ_DEFAULT_PASS: secret
    volumes:
      - rabbitmq_data:/var/lib/rabbitmq

  # ── Redis (Caching Only — Not for messaging) ──
  redis:
    image: redis:alpine
    ports:
      - "6379:6379"

  # ── Per-Service Databases ─────────────────────
  db-notifications:
    image: mysql:8
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: db_notifications
    ports:
      - "3307:3306"   # 3307 taake existing MySQL (3306) se clash na ho

  db-wallet:
    image: mysql:8
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: db_wallet
    ports:
      - "3308:3306"

  db-promotions:
    image: mysql:8
    environment:
      MYSQL_ROOT_PASSWORD: root
      MYSQL_DATABASE: db_promotions
    ports:
      - "3309:3306"

volumes:
  rabbitmq_data:
```

### Commands:
```bash
docker compose up -d
# Check karo: http://localhost:15672 (user: kayani, pass: secret)
```

> ⚠️ **Hint:** Ports 5672 (AMQP) aur 15672 (UI) dono open honi chahiye.
> 5672 = Services ke liye, 15672 = Tum browser mein dekh sako.

---

## 🔌 Step 2: Monolith Mein RabbitMQ Events Add Karo

### Package install karo:
```bash
composer require vladimir-yuldashev/laravel-queue-rabbitmq
composer require php-amqplib/php-amqplib
```

### `.env` mein add karo:
```env
RABBITMQ_HOST=127.0.0.1
RABBITMQ_PORT=5672
RABBITMQ_VHOST=/
RABBITMQ_LOGIN=kayani
RABBITMQ_PASSWORD=secret
```

### Kahan Events Publish Karne Hain (Monolith mein):

| File | Method | Event Publish Karo |
|------|--------|-------------------|
| `OrderService.php` | `processOrder()` end mein | `order.placed` |
| `OrderRepo.php` | `update_delivery_status()` mein | `order.delivered` |
| `OrderRepo.php` | `cancel_order()` mein | `order.cancelled` |
| `ShopService/Repo` | Shop approve hone ke baad | `shop.approved` |

### Event Publish karne ka Pattern:
```php
// ⚠️ Zaruri: try-catch lagao — RabbitMQ down ho to order fail na ho
try {
    $connection = new AMQPStreamConnection(
        env('RABBITMQ_HOST'), env('RABBITMQ_PORT'),
        env('RABBITMQ_LOGIN'), env('RABBITMQ_PASSWORD')
    );
    $channel = $connection->channel();
    $channel->exchange_declare('order.events', 'topic', false, true, false);
    
    $message = new AMQPMessage(
        json_encode([
            'event'    => 'order.placed',
            'order_id' => $order->id,
            'user_id'  => $order->user_id,
            'order_no' => $order->order_no,
            'amount'   => $order->grand_total,
            'timestamp'=> now()->toISOString(),
        ]),
        ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]
    );
    
    $channel->basic_publish($message, 'order.events', 'order.placed');
    $channel->close();
    $connection->close();
    
} catch (\Exception $e) {
    Log::error('RabbitMQ publish failed', ['error' => $e->getMessage()]);
    // ⚠️ Exception rethrow mat karo — business logic affect na ho
}
```

---

## 🔔 Step 3: Notification Service

### Create karo:
```bash
composer create-project laravel/laravel notification-service
cd notification-service
composer require vladimir-yuldashev/laravel-queue-rabbitmq
composer require php-amqplib/php-amqplib
```

### Database:
```php
// notifications table
$table->unsignedBigInteger('user_id');    // Kis user ko
$table->string('type');                   // 'order_placed', 'order_delivered'
$table->string('title');                  // "Order Placed!"
$table->text('body');                     // "Your order #123 has been placed"
$table->json('data')->nullable();         // Extra info (order_no, amount)
$table->timestamp('read_at')->nullable(); // null = unread
```

### RabbitMQ Consumer Command:
```bash
php artisan make:command ConsumeOrderEvents
```

```php
// Consumer ka pattern:
protected $signature = 'consume:order-events';

public function handle()
{
    $this->info('Listening for order events...');
    
    // Connect karo
    $channel->exchange_declare('order.events', 'topic', false, true, false);
    [$queue] = $channel->queue_declare('notification.order.queue', false, true, false, false);
    $channel->queue_bind($queue, 'order.events', 'order.*');  // order.* = sab order events
    
    $channel->basic_consume($queue, '', false, false, false, false, function($msg) {
        $data = json_decode($msg->body, true);
        
        match($data['event']) {
            'order.placed'    => $this->handleOrderPlaced($data),
            'order.delivered' => $this->handleOrderDelivered($data),
            default           => null
        };
        
        $msg->ack();  // ⚠️ Zaruri! Warna message dobara dobara aata rahega
    });
    
    while ($channel->is_consuming()) {
        $channel->wait();  // Infinite loop — hamesha sun-ta rahega
    }
}
```

### APIs:
```
GET  /api/notifications              → sabki list (paginated)
POST /api/notifications/mark-read   → { ids: [1,2,3] }
GET  /api/notifications/unread-count → { count: 5 }
```

> ⚠️ **Hint:** Consumer manually restart nahi karna chahiye. Production mein **Supervisor** use karo:
> ```ini
> [program:notification-consumer]
> command=php artisan consume:order-events
> autostart=true
> autorestart=true
> ```

---

## 💰 Step 4: Wallet Service

### Tables:
```php
// seller_wallets (Har approved shop ka)
$table->unsignedBigInteger('shop_id')->unique();
$table->decimal('total_balance', 12, 2)->default(0);
$table->decimal('withdrawable_balance', 12, 2)->default(0);  // Withdraw ho sakta
$table->decimal('pending_balance', 12, 2)->default(0);       // Order process mein

// wallet_transactions
$table->unsignedBigInteger('wallet_id');
$table->enum('type', ['credit', 'debit']);
$table->decimal('amount', 12, 2);
$table->string('reference');     // Order no
$table->string('note')->nullable();

// withdrawal_requests
$table->unsignedBigInteger('wallet_id');
$table->decimal('amount', 12, 2);
$table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
$table->json('bank_details');    // Account number, bank name etc
```

### Events Jo Sunegi:
```
order.placed    → seller ki pending_balance mein seller_payout add karo
order.delivered → pending_balance → withdrawable_balance shift karo
order.cancelled → pending_balance undo karo
shop.approved   → wallet record create karo
```

### APIs:
```
GET  /api/wallet/balance          → { total, withdrawable, pending }
GET  /api/wallet/transactions     → history (paginated)
POST /api/wallet/withdraw         → { amount, bank_details }
GET  /api/admin/wallet/all        → sab sellers ka
POST /api/admin/wallet/approve    → withdrawal approve karo
```

> ⚠️ **Hint:** Wallet service ko user ka naam/email nahi pata hoga.
> Event mein hi include karo monolith se:
> ```json
> { "shop_id": 5, "seller_payout": 850, "order_no": "ORD-123" }
> ```

---

## 🎯 Step 5: Promotion Service

### Yeh service sirf CRUD hai — koi events consume nahi karti

### Tables:
```php
// flash_sales
$table->string('name');
$table->timestamp('starts_at');
$table->timestamp('ends_at');
$table->enum('status', ['active', 'inactive'])->default('inactive');

// flash_sale_products
$table->unsignedBigInteger('flash_sale_id');
$table->unsignedBigInteger('product_id');   // Product-service se ID store karo
$table->decimal('discount_value', 8, 2);
$table->enum('discount_type', ['fixed', 'percentage']);
$table->integer('stock_limit')->nullable();

// banners
$table->string('title');
$table->string('image');
$table->string('link')->nullable();
$table->enum('position', ['home_top', 'home_middle', 'sidebar']);
$table->boolean('is_active')->default(true);
$table->integer('sort_order')->default(0);
```

### APIs:
```
GET  /api/promotions/flash-sales           → Active flash sales
GET  /api/promotions/banners?position=home → Home page banners
POST /api/admin/promotions/flash-sale      → Create
PUT  /api/admin/promotions/flash-sale      → Update
DELETE /api/admin/promotions/flash-sale    → Delete
POST /api/admin/promotions/banner          → CRUD banners
```

> ⚠️ **Hint:** `product_id` store hoga lekin product data nahi.
> Frontend ko product name chahiye to product-service se HTTP call karo ya event pe sync karo.

---

## 📊 Step 6: Analytics Service

### Events Jo Sunegi:
```
order.placed    → orders count, revenue track
order.cancelled → cancellation rate
```

### APIs:
```
GET /api/analytics/admin/dashboard
    → { total_orders, total_revenue, total_users, top_sellers }

GET /api/analytics/seller/dashboard
    → { my_orders, my_earnings, pending_payouts }

GET /api/analytics/admin/revenue?from=2026-01-01&to=2026-12-31
    → Revenue by date range
```

> ⚠️ **Hint:** Pehle simple MySQL use karo aggregations ke liye.
> Jab data bohat zyada ho jaye tab ClickHouse ya MongoDB shift karo.

---

## 🌐 Step 7: Nginx API Gateway

```nginx
# api-gateway/nginx.conf

upstream monolith_app    { server 127.0.0.1:8000; }
upstream notification_svc { server 127.0.0.1:8006; }
upstream wallet_svc       { server 127.0.0.1:8007; }
upstream promotion_svc    { server 127.0.0.1:8008; }
upstream analytics_svc    { server 127.0.0.1:8009; }

server {
    listen 80;

    # Existing monolith routes (as-is)
    location /api/auth/        { proxy_pass http://monolith_app; }
    location /api/order/       { proxy_pass http://monolith_app; }
    location /api/product/     { proxy_pass http://monolith_app; }
    location /api/coupon/      { proxy_pass http://monolith_app; }
    location /api/review/      { proxy_pass http://monolith_app; }

    # New microservices
    location /api/notifications/ { proxy_pass http://notification_svc; }
    location /api/wallet/         { proxy_pass http://wallet_svc; }
    location /api/promotions/     { proxy_pass http://promotion_svc; }
    location /api/analytics/      { proxy_pass http://analytics_svc; }
}
```

---

## ✅ Complete Checklist

### Phase 1 — Infrastructure
- [x] Docker Desktop install (agar nahi)
- [x] Parent folder banao: `kayaniexpress-microservices/`
- [x] `docker-compose.yml` banao (RabbitMQ + Redis + DBs)
- [x] `docker compose up -d` run karo
- [x] RabbitMQ UI check: http://localhost:15672

### Phase 2 — Monolith Events
- [x] `composer require vladimir-yuldashev/laravel-queue-rabbitmq`
- [x] `composer require php-amqplib/php-amqplib`
- [x] `.env` mein RabbitMQ settings add karo
- [x] `PublishRabbitMQEvent` + `SendRabbitMQMessageListener` banao
- [x] `AppServiceProvider` mein Event register karo
- [x] `OrderService` mein `order.placed` event publish karo
- [x] `OrderRepo` mein `order.cancelled` aur `order.delivered` events add karo
- [ ] `shop.approved` event add karo (ShopService mein)
- [x] Test: Order place karo → RabbitMQ UI mein message check karo ✅

### Phase 3 — Notification Service ✅ COMPLETE
- [x] `notification-service` folder mein fresh Laravel project
- [x] RabbitMQ packages install (`php-amqplib`)
- [x] `notifications` table migration + model
- [x] `ConsumeOrderEvents` Artisan command (topic exchange, order.* routing)
- [x] `NotificationHandleService` (handleOrderPlaced, Delivered, Cancelled, Shipped)
- [x] `NotificationJob` — DB mein save, with DB::transaction + failed() handler
- [x] APIs: `/api/notification/get`, `/mark-as-read`, `/get-unread-count`
- [x] Microservice auth (X-Microservice-Secret header)
- [x] Test: Order place → RabbitMQ → Consumer → NotificationJob → DB ✅

### Phase 4 — Wallet Service
- [ ] Fresh Laravel project
- [ ] `seller_wallets`, `wallet_transactions`, `withdrawal_requests` migrations
- [ ] Consumer: `order.placed` → seller pending_balance update
- [ ] Consumer: `order.delivered` → pending → withdrawable shift
- [ ] Consumer: `order.cancelled` → pending_balance undo
- [ ] Consumer: `shop.approved` → wallet record create
- [ ] APIs: balance, transactions, withdraw, admin approval

### Phase 5 — Promotion Service
- [ ] Fresh Laravel project
- [ ] `flash_sales`, `flash_sale_products`, `banners` migrations
- [ ] CRUD APIs

### Phase 6 — Analytics Service
- [ ] Fresh Laravel project
- [ ] Consumer: `order.placed` → aggregate
- [ ] Admin + Seller dashboard APIs

### Phase 7 — Nginx Gateway
- [ ] `api-gateway/nginx.conf` banao
- [ ] Test: Sab routes sahi service pe jaa rahay hain?

---

## 🚨 Common Mistakes (Pehli Baar Log Yeh Karte Hain)

| ❌ Mistake | ✅ Sahi Tarika |
|-----------|--------------|
| Consumer mein `$msg->ack()` bhool jana | Message queue mein ruka rahega — hamesha ACK karo |
| Publisher mein try-catch na lagana | RabbitMQ down ho to order fail ho jayega |
| Ek service ka DB doosri service directly access kare | **Kabhi nahi!** Events ya HTTP API use karo |
| JWT secret alag alag services mein alag rakhna | Ek hi secret sab share karein (`.env`) |
| Consumer manually restart karna | Supervisor ya Docker restart policy use karo |
| Event mein user email nahi bhejni | Consumer ko user info chahiye — publisher mein include karo |

---

## 🔑 Key Principles (Yaad Rakhna)

1. **Services ek dusray ka DB directly access nahi karengi** (ever)
2. **Data fetch (price, stock) → HTTP API call** (sync)
3. **Side effects (email, payout, notify) → RabbitMQ** (async)
4. **Publisher mein failure = sirf log, order fail nahi hoga**
5. **Consumer mein failure = NACK + retry (RabbitMQ handle karega)**
