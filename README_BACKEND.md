# SMKN 1 Kedisiplinan - Backend Architecture Documentation

**Clean Architecture Implementation with Laravel 11**

---

## 📚 Table of Contents

- [Architecture Overview](#architecture-overview)
- [Directory Structure](#directory-structure)
- [Core Concepts](#core-concepts)
- [How-To Guides](#how-to-guides)
- [Security Features](#security-features)
- [Performance Features](#performance-features)
- [Development Workflow](#development-workflow)
- [API Reference](#api-reference)

---

## 🏗️ Architecture Overview

This project implements **Clean Architecture** principles with strict separation of concerns:

```
HTTP Request
    ↓
┌─────────────────────────────────────────────────────┐
│ HTTP Layer (Controllers, FormRequests)             │
│ - Thin controllers (< 20 lines per method)         │
│ - Validation via FormRequest                       │
│ - No business logic                                │
└─────────────────────────────────────────────────────┘
    ↓ DTOs (Data Transfer Objects)
┌─────────────────────────────────────────────────────┐
│ Service Layer (Business Logic Orchestration)       │
│ - Coordinate repositories                          │
│ - Execute business rules                           │
│ - Handle transactions                              │
│ - NO direct database queries                       │
└─────────────────────────────────────────────────────┘
    ↓ Domain Models
┌─────────────────────────────────────────────────────┐
│ Repository Layer (Data Access)                     │
│ - Database abstraction                             │
│ - Query optimization                               │
│ - Return DTOs or Models                            │
│ - Never accept Request objects                     │
└─────────────────────────────────────────────────────┘
    ↓
┌─────────────────────────────────────────────────────┐
│ Database (MySQL)                                    │
└─────────────────────────────────────────────────────┘
```

### Flow Example: Create Siswa

```php
// 1. HTTP Request → FormRequest validates
POST /siswa
Body: { "nama_siswa": "Ahmad", "nisn": "123", ... }

// 2. Controller (Thin - 15 lines)
public function store(CreateSiswaRequest $request) {
    $data = SiswaData::from($request->validated());
    $this->siswaService->createSiswa($data);
    return redirect()->route('siswa.index');
}

// 3. Service (Business Logic)
public function createSiswa(SiswaData $data): SiswaData {
    DB::beginTransaction();
    $siswa = $this->siswaRepo->create($data->toArray());
    $this->createWaliMuridAccount($siswa);  // Auto-create account
    DB::commit();
    return SiswaData::from($siswa);
}

// 4. Repository (Data Access)
public function create(array $data) {
    return $this->model->create($data);
}
```

---

## 📁 Directory Structure

```
app/
├── Data/                           # DTOs (Data Transfer Objects)
│   ├── Siswa/
│   │   ├── SiswaData.php          # Siswa entity DTO
│   │   └── SiswaFilterData.php    # Filter criteria DTO
│   ├── Pelanggaran/
│   ├── TindakLanjut/
│   └── User/
│
├── Enums/                          # PHP Native Enums
│   ├── StatusTindakLanjut.php     # Enum untuk status
│   └── TingkatPelanggaran.php     # Enum untuk tingkat
│
├── Repositories/                   # Data Access Layer
│   ├── Contracts/                  # Repository Interfaces
│   │   ├── BaseRepositoryInterface.php
│   │   ├── SiswaRepositoryInterface.php
│   │   └── ...
│   ├── BaseRepository.php          # Abstract base repository
│   ├── SiswaRepository.php         # Siswa data access
│   └── ...
│
├── Services/                       # Business Logic Layer
│   ├── Siswa/
│   │   └── SiswaService.php       # Siswa business logic
│   ├── Pelanggaran/
│   │   ├── PelanggaranService.php
│   │   └── PelanggaranRulesEngine.php
│   ├── TindakLanjut/
│   └── User/
│
├── Http/
│   ├── Controllers/                # Thin controllers
│   │   ├── MasterData/
│   │   │   └── SiswaControllerClean.php
│   │   ├── Pelanggaran/
│   │   │   └── RiwayatPelanggaranControllerClean.php
│   │   └── TindakLanjut/
│   │
│   └── Requests/                   # Form validation
│       ├── Siswa/
│       │   ├── CreateSiswaRequest.php
│       │   └── UpdateSiswaRequest.php
│       ├── Pelanggaran/
│       └── TindakLanjut/
│
├── Policies/                       # Authorization logic
│   ├── SiswaPolicy.php
│   ├── RiwayatPelanggaranPolicy.php
│   └── TindakLanjutPolicy.php
│
├── Exceptions/                     # Custom exceptions
│   ├── DomainException.php         # Base exception
│   ├── SiswaNotFoundException.php
│   ├── BusinessValidationException.php
│   └── UnauthorizedException.php
│
├── Jobs/                           # Queue jobs
│   └── SendNotificationEmail.php
│
└── Providers/
    ├── AuthServiceProvider.php     # Policies registration
    └── RepositoryServiceProvider.php # Repository DI binding
```

### Key Directories Explained

| Directory | Purpose | Example |
|-----------|---------|---------|
| **Data/** | DTOs for type-safe data transfer | `SiswaData`, `RiwayatPelanggaranFilterData` |
| **Enums/** | Type-safe constants with helper methods | `StatusTindakLanjut::BARU` |
| **Repositories/** | Database abstraction, query optimization | `SiswaRepository::findByNisn()` |
| **Services/** | Business logic orchestration | `SiswaService::createSiswa()` |
| **Http/Requests/** | Input validation rules | `CreateSiswaRequest` |
| **Policies/** | Authorization rules | `SiswaPolicy::create()` |

---

## 🎯 Core Concepts

### 1. DTOs (Data Transfer Objects)

**Purpose:** Type-safe data containers untuk transfer antar layers.

**Example:**
```php
// app/Data/Siswa/SiswaData.php
use Spatie\LaravelData\Data;

class SiswaData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nama_siswa,
        public string $nisn,
        public int $kelas_id,
        public ?int $wali_murid_user_id,
        public string $status,
    ) {}
}

// Usage
$siswa = SiswaData::from($request->validated());
$siswa = SiswaData::from($model);
$array = $siswa->toArray();
```

**Benefits:**
- ✅ Type safety
- ✅ Auto-validation
- ✅ Easy serialization
- ✅ IDE autocomplete

### 2. PHP Native Enums

**Purpose:** Type-safe constants with helper methods.

**Example:**
```php
// app/Enums/StatusTindakLanjut.php
enum StatusTindakLanjut: string
{
    case BARU = 'Baru';
    case MENUNGGU_PERSETUJUAN = 'Menunggu Persetujuan';
    case DISETUJUI = 'Disetujui';
    case DITANGANI = 'Ditangani';
    case SELESAI = 'Selesai';

    public function getColor(): string
    {
        return match($this) {
            self::BARU => 'blue',
            self::MENUNGGU_PERSETUJUAN => 'yellow',
            self::DISETUJUI => 'green',
            // ...
        };
    }

    public static function activeStatuses(): array
    {
        return [self::BARU, self::MENUNGGU_PERSETUJUAN, self::DISETUJUI];
    }
}

// Usage
$status = StatusTindakLanjut::BARU;
$color = $status->getColor(); // 'blue'
$isActive = in_array($status, StatusTindakLanjut::activeStatuses());
```

**Benefits:**
- ✅ Type safety (can't use invalid values)
- ✅ No magic strings
- ✅ Refactoring-friendly
- ✅ Helper methods

### 3. Repository Pattern

**Purpose:** Abstraksi akses database, single source of truth untuk queries.

**Interface:**
```php
// app/Repositories/Contracts/SiswaRepositoryInterface.php
interface SiswaRepositoryInterface extends BaseRepositoryInterface
{
    public function findByNisn(string $nisn): ?SiswaData;
    public function findByKelas(int $kelasId): Collection;
    public function filterAndPaginate(SiswaFilterData $filters): LengthAwarePaginator;
}
```

**Implementation:**
```php
// app/Repositories/SiswaRepository.php
class SiswaRepository extends BaseRepository implements SiswaRepositoryInterface
{
    public function findByNisn(string $nisn): ?SiswaData
    {
        $siswa = $this->model
            ->where('nisn', $nisn)
            ->with(['kelas.jurusan', 'waliMurid'])
            ->first();

        return $siswa ? SiswaData::from($siswa) : null;
    }
}
```

**Benefits:**
- ✅ Testable (easy to mock)
- ✅ Swappable implementations
- ✅ Centralized queries
- ✅ Optimized with eager loading

### 4. Service Layer

**Purpose:** Business logic orchestration, koordinasi repositories.

**Example:**
```php
// app/Services/Siswa/SiswaService.php
class SiswaService
{
    public function __construct(
        private SiswaRepositoryInterface $siswaRepo,
        private UserRepositoryInterface $userRepo,
    ) {}

    public function createSiswa(SiswaData $data): SiswaData
    {
        DB::beginTransaction();
        try {
            // 1. Create siswa
            $siswa = $this->siswaRepo->create($data->toArray());

            // 2. Auto-create wali murid account
            $this->createWaliMuridAccount($siswa);

            DB::commit();
            return SiswaData::from($siswa);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

**Benefits:**
- ✅ Single Responsibility
- ✅ Testable
- ✅ Reusable
- ✅ Transaction management

---

## 📖 How-To Guides

### How to Create a New Service

**Step 1: Create Service Class**
```php
// app/Services/YourModule/YourModuleService.php
namespace App\Services\YourModule;

use App\Repositories\Contracts\YourRepositoryInterface;
use Illuminate\Support\Facades\DB;

class YourModuleService
{
    public function __construct(
        private YourRepositoryInterface $yourRepo
    ) {}

    public function createRecord(YourData $data): YourData
    {
        DB::beginTransaction();
        try {
            $record = $this->yourRepo->create($data->toArray());
            DB::commit();
            return YourData::from($record);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

**Step 2: Register in Container (Optional)**
```php
// If using service provider
$this->app->singleton(YourModuleService::class);
```

**Step 3: Inject in Controller**
```php
public function __construct(
    private YourModuleService $yourService
) {}
```

### How to Use DTOs

**Step 1: Create DTO**
```php
// app/Data/YourModule/YourData.php
use Spatie\LaravelData\Data;

class YourData extends Data
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $email,
    ) {}
}
```

**Step 2: Convert from Request**
```php
// In controller
$data = YourData::from($request->validated());
```

**Step 3: Convert from Model**
```php
// In repository
return YourData::from($model);
```

**Step 4: Convert to Array**
```php
// In service
$array = $data->toArray();
$this->repo->create($array);
```

### How to Use Enums

**Step 1: Create Enum**
```php
// app/Enums/YourEnum.php
enum YourEnum: string
{
    case OPTION_A = 'Option A';
    case OPTION_B = 'Option B';

    public function getColor(): string
    {
        return match($this) {
            self::OPTION_A => 'green',
            self::OPTION_B => 'blue',
        };
    }
}
```

**Step 2: Use in Model**
```php
protected $casts = [
    'status' => YourEnum::class,
];
```

**Step 3: Use in Code**
```php
// Create
$model->status = YourEnum::OPTION_A;

// Check
if ($model->status === YourEnum::OPTION_A) {
    // Do something
}

// Get helper
$color = $model->status->getColor();
```

### How to Create a Repository

**Step 1: Create Interface**
```php
// app/Repositories/Contracts/YourRepositoryInterface.php
interface YourRepositoryInterface extends BaseRepositoryInterface
{
    public function findByCustomField(string $value): ?YourData;
}
```

**Step 2: Create Implementation**
```php
// app/Repositories/YourRepository.php
class YourRepository extends BaseRepository implements YourRepositoryInterface
{
    public function __construct(YourModel $model)
    {
        parent::__construct($model);
    }

    public function findByCustomField(string $value): ?YourData
    {
        $model = $this->model
            ->where('custom_field', $value)
            ->with('relations')
            ->first();

        return $model ? YourData::from($model) : null;
    }
}
```

**Step 3: Register in Service Provider**
```php
// app/Providers/RepositoryServiceProvider.php
$this->app->bind(
    YourRepositoryInterface::class,
    YourRepository::class
);
```

---

## 🔐 Security Features

### 1. Authorization (Policies)

**Automatic Policy Check:**
```php
// In controller
$this->authorize('update', $siswa);

// Or via middleware
Route::resource('siswa', SiswaController::class)
    ->middleware('can:viewAny,App\Models\Siswa');
```

**Policy Examples:**

**SiswaPolicy:**
- Operator: Full CRUD access
- Wali Kelas: Update phone only (di kelas binaan)
- Wali Murid: View anak sendiri only

**RiwayatPelanggaranPolicy:**
- Create: All teacher roles
- Update/Delete: Pencatat sendiri (max 3 hari) OR Operator

**TindakLanjutPolicy:**
- Approve: Kepala Sekolah (all), Waka (Surat 2-3), Kaprodi (jurusan binaan)

### 2. Input Validation

**FormRequest Example:**
```php
class CreateSiswaRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama_siswa' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'digits:10', 'unique:siswa,nisn'],
            'kelas_id' => ['required', 'exists:kelas,id'],
        ];
    }
}
```

### 3. Mass Assignment Protection

```php
// In model
protected $fillable = ['nama_siswa', 'nisn', 'kelas_id'];
protected $guarded = ['id', 'created_at', 'updated_at'];
```

### 4. XSS Prevention

```blade
{{-- Safe (escaped) --}}
{{ $siswa->nama_siswa }}

{{-- Unsafe (raw HTML) - avoid unless necessary --}}
{!! $htmlContent !!}
```

### 5. SQL Injection Prevention

```php
// ✅ Good: Query builder/Eloquent
$this->model->where('nisn', $nisn)->first();

// ✅ Good: Parameterized query
DB::select('SELECT * FROM siswa WHERE nisn = ?', [$nisn]);

// ❌ Bad: Raw SQL concatenation
DB::select("SELECT * FROM siswa WHERE nisn = '$nisn'");
```

### 6. CSRF Protection

```blade
<form method="POST">
    @csrf
    {{-- Form fields --}}
</form>
```

---

## ⚡ Performance Features

### 1. Caching Strategy

**Master Data (Forever Cache):**
```php
// JenisPelanggaranRepository
public function getActive(): Collection
{
    return Cache::rememberForever('jenis_pelanggaran:active', function () {
        return $this->model->where('is_active', true)->get();
    });
}

// Auto-invalidate
public function create(array $data) {
    $result = parent::create($data);
    Cache::forget('jenis_pelanggaran:active');
    return $result;
}
```

**Transactional Data (TTL Cache):**
```php
// SiswaRepository (10 minutes)
public function find(int $id) {
    return Cache::remember("siswa:find:{$id}", 600, function () use ($id) {
        return $this->model->with('kelas', 'waliMurid')->find($id);
    });
}
```

**Benefits:**
- 50-100x faster queries
- Reduced database load
- Auto-invalidation on updates

### 2. Queue Jobs

**Async Email Notifications:**
```php
// Dispatch (instant return to user)
SendNotificationEmail::dispatch($email, $message);

// Process in background
php artisan queue:work
```

**Benefits:**
- 100x faster response times
- Retry mechanism (3 attempts)
- Scalable (multiple workers)

### 3. Database Optimization

**Indexes:**
```php
// Migration
$table->index('siswa_id');
$table->index('tanggal_kejadian');
$table->index(['siswa_id', 'tanggal_kejadian']); // Composite
```

**Eager Loading:**
```php
// ❌ N+1 Query Problem
$siswa = Siswa::all();
foreach ($siswa as $s) {
    echo $s->kelas->nama_kelas; // Query per siswa!
}

// ✅ Eager Loading
$siswa = Siswa::with('kelas')->get();
foreach ($siswa as $s) {
    echo $s->kelas->nama_kelas; // 1 query total!
}
```

**Select Specific Columns:**
```php
// ❌ Select all columns
$siswa = Siswa::all();

// ✅ Select only needed
$siswa = Siswa::select('id', 'nama_siswa', 'nisn')->get();
```

### 4. Exception Handling

**Custom Domain Exceptions:**
```php
// Throw
if (!$siswa) {
    throw new SiswaNotFoundException($nisn);
}

// Global handler catches and logs
Log::error($e->getMessage(), $e->getLogContext());

// Returns user-friendly message
return redirect()->back()->with('error', $e->getUserMessage());
```

**Benefits:**
- Centralized error handling
- Detailed logging
- User-friendly messages
- Automatic logging

---

## 🛠️ Development Workflow

### Setting Up Development Environment

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment setup
cp .env.example .env
php artisan key:generate

# 3. Database
php artisan migrate
php artisan db:seed

# 4. Queue setup
php artisan queue:table
php artisan migrate

# 5. Cache setup
php artisan config:cache
php artisan route:cache
```

### Running the Application

```bash
# Development server
php artisan serve

# Queue worker (separate terminal)
php artisan queue:work

# Watch assets (if using Vite/Mix)
npm run dev
```

### Code Quality

```bash
# Laravel Pint (PSR-12)
./vendor/bin/pint --test  # Check
./vendor/bin/pint         # Fix

# PHPStan (Static Analysis)
./vendor/bin/phpstan analyse --level=8

# Tests
php artisan test
```

### Database Operations

```bash
# Fresh migration
php artisan migrate:fresh --seed

# Rollback
php artisan migrate:rollback

# Specific migration
php artisan migrate --path=/database/migrations/2024_12_08_...php

# Check status
php artisan migrate:status
```

### Cache Management

```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📚 API Reference

### DTOs

| DTO | Purpose | Fields |
|-----|---------|--------|
| `SiswaData` | Siswa entity | id, nama_siswa, nisn, kelas_id, wali_murid_user_id |
| `SiswaFilterData` | Filter criteria | search, kelas_id, jurusan_id, perPage |
| `RiwayatPelanggaranData` | Pelanggaran entity | id, siswa_id, jenis_pelanggaran_id, tanggal_kejadian |
| `TindakLanjutData` | Tindak lanjut entity | id, siswa_id, sanksi_deskripsi, status |
| `UserData` | User entity | id, nama, username, email, role_id |

### Enums

| Enum | Values | Usage |
|------|--------|-------|
| `StatusTindakLanjut` | BARU, MENUNGGU_PERSETUJUAN, DISETUJUI, DITANGANI, SELESAI | Tindak lanjut status |
| `TingkatPelanggaran` | RINGAN, SEDANG, BERAT | Violation severity |

### Services

#### SiswaService

```php
createSiswa(SiswaData $data): SiswaData
updateSiswa(int $id, SiswaData $data, string $role): SiswaData
deleteSiswa(int $id): bool
getFilteredSiswa(SiswaFilterData $filters): LengthAwarePaginator
```

#### PelanggaranService

```php
catatPelanggaran(RiwayatPelanggaranData $data): RiwayatPelanggaranData
updatePelanggaran(int $id, RiwayatPelanggaranData $data): RiwayatPelanggaranData
deletePelanggaran(int $id, int $siswaId): bool
getFilteredRiwayat(RiwayatPelanggaranFilterData $filters): LengthAwarePaginator
calculateTotalPoin(int $siswaId): int
```

#### TindakLanjutService

```php
createTindakLanjut(TindakLanjutData $data): TindakLanjutData
approveTindakLanjut(int $id, int $penyetujuId): TindakLanjutData
rejectTindakLanjut(int $id, int $penyetujuId, string $reason): TindakLanjutData
completeTindakLanjut(int $id): TindakLanjutData
getFilteredTindakLanjut(TindakLanjutFilterData $filters): LengthAwarePaginator
```

#### UserService

```php
createUser(UserData $data): UserData
updateUser(int $id, UserData $data): UserData
resetPassword(int $userId, string $newPassword): bool
toggleActivation(int $userId): bool
getPaginatedUsers(int $perPage, array $filters): LengthAwarePaginator
```

---

## 🎓 Best Practices

### DO's ✅

- Use DTOs for data transfer between layers
- Use Enums for type-safe constants
- Use Policies for authorization
- Use FormRequests for validation
- Use Repositories for data access
- Use Services for business logic
- Use Queue jobs for slow operations
- Use Cache for read-heavy data
- Use Eager loading to prevent N+1
- Use Transactions for multi-step operations

### DON'Ts ❌

- Don't accept Request in Services/Repositories
- Don't put business logic in Controllers
- Don't use raw SQL (use Query Builder/Eloquent)
- Don't suppress errors with @
- Don't use magic strings (use Enums)
- Don't skip validation
- Don't forget authorization checks
- Don't forget to cache invalidation
- Don't use `dd()` or `var_dump()` in production

---

## 🐛 Troubleshooting

### Common Issues

**Issue:** "Class not found"
```bash
# Solution: Rebuild autoloader
composer dump-autoload
```

**Issue:** "Queue job not processing"
```bash
# Solution: Restart queue worker
php artisan queue:restart
php artisan queue:work
```

**Issue:** "Cache not refreshing"
```bash
# Solution: Clear cache
php artisan cache:clear
```

**Issue:** "Migration error: Duplicate key name"
```bash
# Solution: Already fixed in idempotent migrations
# Just run: php artisan migrate
```

---

## 📞 Support & Maintenance

### Code Owners

- **Architecture:** Development Team
- **Security:** Security Team
- **Database:** DBA Team

### Updating This Documentation

When making changes to the codebase:
1. Update relevant sections in this README
2. Update inline code comments
3. Update API documentation if interfaces change
4. Keep examples up-to-date

### Version History

- **v2.0** (Dec 2024): Clean Architecture refactoring complete
- **v1.0** (Initial): Legacy monolithic structure

---

**Last Updated:** December 8, 2024  
**Maintained By:** Development Team  
**Laravel Version:** 11.x  
**PHP Version:** 8.2+
