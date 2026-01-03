# BACKEND CONTROLLER AUDIT - CLEAN ARCHITECTURE COMPLIANCE

**Date:** 2025-12-11  
**Status:** 🔴 **CRITICAL - MAJOR REFACTORING NEEDED**  
**Priority:** HIGH (Code Quality & Maintainability)

---

## 🎯 **AUDIT OBJECTIVE**

Identify controllers with excessive business logic that need refactoring to follow Clean Architecture principles:
- ✅ Controllers should ONLY handle HTTP requests/responses
- ✅ Business logic belongs in Services
- ✅ Data access belongs in Repositories
- ✅ Validation belongs in FormRequests

---

## 📊 **AUDIT RESULTS SUMMARY**

### **Overall Status:**

| Category | Count | Percentage |
|----------|-------|------------|
| **CLEAN** (Good practices) | 8 | 31% |
| **MIXED** (Partial refactoring needed) | 6 | 23% |
| **DIRTY** (Major refactoring needed) | 12 | 46% |

**CRITICAL:** 46% of controllers need major refactoring! ❌

---

## 🟢 **TIER 1: CLEAN CONTROLLERS** (Reference Examples)

These follow best practices - use as templates!

### **1. UserController.php** ⭐ **GOLD STANDARD**

**Why Clean:**
- ✅ Uses Service layer (`UserService`)
- ✅ Uses DTOs (`UserData`)
- ✅ Uses FormRequests (`CreateUserRequest`, `UpdateUserRequest`)
- ✅ NO direct Model queries
- ✅ Methods < 30 lines
- ✅ Clear separation of concerns

**Example Method:**
```php
public function store(CreateUserRequest $request)
{
    $userData = UserData::from($request->validated());
    $user = $this->userService->createUser($userData);
    
    return redirect()->route('users.index')
        ->with('success', 'User created');
}
```

**Lines:** 15 lines - PERFECT! ✅

---

### **2. FrequencyRulesController.php** ✅

**Status:** Recently refactored!

**Why Clean:**
- ✅ Uses `FrequencyRuleService`
- ✅ Uses `FrequencyRuleRepository`
- ✅ Uses FormRequests
- ✅ NO business logic in controller

---

### **3. TindakLanjut/TindakLanjutController.php** ✅

**Why Clean:**
- ✅ Delegates to `TindakLanjutService`
- ✅ Minimal logic

---

### **4. Auth/LoginController.php** ✅

**Why Clean:**
- ✅ Extends Laravel's built-in
- ✅ Minimal custom logic

---

### **5. Utility/FileController.php** ✅

**Why Clean:**
- ✅ Single responsibility (file serving)
- ✅ No complex business logic

---

### **6-8. Dashboard/DeveloperDashboardController.php**, etc. ✅

**Why Clean:**
- ✅ Use `StatisticsService`
- ✅ Presentation only

---

## 🟡 **TIER 2: MIXED CONTROLLERS** (Needs Partial Refactoring)

### **1. MasterData/SiswaController.php** ⚠️

**Status:** MOSTLY CLEAN but needs work

**Good:**
- ✅ Uses `SiswaService` for main CRUD
- ✅ Uses `FilterSiswaRequest`
- ✅ Uses `SiswaData` DTO

**Issues:**
```php
// Line 259+ (import() method)
❌ Direct Model queries
❌ Business logic in controller
❌ File handling in controller
```

**Recommendation:** 
- Extract import logic to `SiswaImportService`
- Move file handling to dedicated service

---

### **2-6. Dashboard Controllers** ⚠️

**Issues:**
- Some use Services, some don't
- Inconsistent patterns
- Mixed direct queries

**Example - WaliMuridDashboardController:**
```php
// Line 38 - Fixed but others similar
❌ Was: Direct join/sum query
✅ Now: Uses PelanggaranService
```

**Recommendation:**
- Unify all dashboard logic in `DashboardStatisticsService`

---

## 🔴 **TIER 3: DIRTY CONTROLLERS** (CRITICAL - Major Refactoring Needed)

### **🔴 1. MasterData/JurusanController.php** - **WORST OFFENDER**

**Lines:** 355 lines total  
**Violations:** SEVERE

#### **Critical Issues:**

**A. Inline Validation (should use FormRequest)**
```php
// Line 34
❌ $data = $request->validate([...]);
```

**B. Business Logic in Controller**
```php
// Lines 40-50: Kode generation & uniqueness check
❌ if (empty($data['kode_jurusan'])) {
    $data['kode_jurusan'] = $this->generateKode($data['nama_jurusan']);
    
    $base = $data['kode_jurusan'];
    $i = 1;
    while (Jurusan::where('kode_jurusan', $data['kode_jurusan'])->exists()) {
        $i++;
        $data['kode_jurusan'] = $base . $i;
    }
}
```

**C. Complex User Creation Logic**
```php
// Lines 56-88: Auto-create Kaprodi user
❌ if ($request->has('create_kaprodi')) {
    // 30+ lines of username generation
    // Password generation
    // User creation
    // Email generation
    // Assignment logic
}
```

**D. Direct Model Operations**
```php
// Line 53
❌ $jurusan = Jurusan::create($data);

// Line 66
❌ while (User::where('username', $username)->exists()) {
```

**E. Duplicate Code**
```php
// Same logic repeated in:
- store() method
- update() method
- generateKode() helper
```

#### **Required Refactoring:**

**CREATE:**
1. `app/Services/MasterData/JurusanService.php`
2. `app/Repositories/JurusanRepository.php`
3. `app/Http/Requests/MasterData/CreateJurusanRequest.php`
4. `app/Http/Requests/MasterData/UpdateJurusanRequest.php`
5. `app/Data/MasterData/JurusanData.php` (DTO)

**EXTRACT LOGIC:**
- Kode generation → `JurusanService::generateUniqueKode()`
- User creation → `UserService::createKaprodiForJurusan()`
- Validation → FormRequests
- Queries → Repository

**TARGET:**
- `store()` method: 355 lines → **15 lines** ✅
- `update()` method: Similar reduction

---

### **🔴 2. MasterData/KelasController.php** - **CRITICAL**

**Lines:** 256 lines  
**Violations:** SEVERE (Similar to JurusanController)

#### **Critical Issues:**

**Same problems as JurusanController:**

```php
// Lines 46-127: store() method - 80 LINES!
❌ Inline validation
❌ Kode generation logic
❌ nama_kelas generation with numbering
❌ Auto-create Wali Kelas user (30+ lines)
❌ Direct Model operations
```

**Example Horror:**
```php
// Lines 74-87: Find next class number
❌ $existing = Kelas::where('jurusan_id', $jurusan->id)
    ->where('nama_kelas', 'like', $base . '%')
    ->pluck('nama_kelas')
    ->toArray();

$max = 0;
foreach ($existing as $name) {
    if (preg_match('/\s+(\d+)$/', $name, $m)) {
        $num = intval($m[1]);
        if ($num > $max) $max = $num;
    }
}
$next = $max + 1;
$data['nama_kelas'] = $base . ' ' . $next;
```

**THIS IS BUSINESS LOGIC!** Should be in Service!

#### **Required Refactoring:**

**CREATE:**
1. `app/Services/MasterData/KelasService.php`
2. `app/Repositories/KelasRepository.php`
3. `app/Http/Requests/MasterData/CreateKelasRequest.php`
4. `app/Http/Requests/MasterData/UpdateKelasRequest.php`
5. `app/Data/MasterData/KelasData.php`

---

### **🔴 3. Pelanggaran/RiwayatPelanggaranController.php** ⚠️

**Issues:**

```php
// Lines 54-88: create() method
❌ $user = auth()->user();
❌ if ($user->hasRole('Wali Kelas')) {
    $kelas = \App\Models\Kelas::where('wali_kelas_user_id', $user->id)->first();
    // More logic...
}
```

**Should be:**
```php
$daftarSiswa = $this->pelanggaranService->getAvailableSiswaForUser(auth()->user());
```

**Already uses Service but has direct queries!**

---

### **🔴 4-12. Other Dirty Controllers**

| Controller | Main Issues | Priority |
|------------|-------------|----------|
| **JenisPelanggaranController** | No Service, direct CRUD | P2 |
| **Report/ReportController** | Complex queries in controller | P1 |
| **Rules/PembinaanInternalRulesController** | No Service layer | P2 |
| **Rules/RulesEngineSettingsController** | Mixed logic | P2 |
| **Report/ApprovalController** | Direct queries | P2 |
| **AdminDashboardController** | Some direct queries | P3 |
| **KaprodiDashboardController** | Direct queries | P3 |
| **WakaSaranaDashboardController** | LIKE queries in controller | P3 |

---

## 📋 **REFACTORING PRIORITY LIST**

### **PHASE 1: CRITICAL (This Sprint)**

**P0: JurusanController + KelasController**
- Most violations
- Duplicate code
- Complex business logic
- Template for similar controllers

**Estimated Effort:** 2-3 days  
**Files to Create:** 10 files (Services, Repos, Requests, DTOs)  
**Lines Reduced:** ~400 lines → ~100 lines

---

### **PHASE 2: HIGH (Next Sprint)**

**P1: Report Controllers**
- ReportController
- ApprovalController
- SiswaPerluPembinaanController

**Estimated Effort:** 1-2 days

---

### **PHASE 3: MEDIUM (Later)**

**P2: Rules Controllers**
- PembinaanInternalRulesController
- RulesEngineSettingsController

**P2: Master Data**
- JenisPelanggaranController

**Estimated Effort:** 2 days

---

### **PHASE 4: LOW (Optional)**

**P3: Dashboard Controllers**
- Unify statistics logic
- Extract to `DashboardStatisticsService`

**Estimated Effort:** 1 day

---

## 🎯 **REFACTORING BLUEPRINT**

### **For JurusanController:**

#### **Step 1: Create DTO**

```php
// app/Data/MasterData/JurusanData.php
class JurusanData extends Data
{
    public function __construct(
        public ?int $id,
        public string $nama_jurusan,
        public ?string $kode_jurusan,
        public ?int $kaprodi_user_id,
        public bool $create_kaprodi = false,
    ) {}
}
```

---

#### **Step 2: Create Repository**

```php
// app/Repositories/JurusanRepository.php
class JurusanRepository
{
    public function create(array $data): Jurusan
    {
        return Jurusan::create($data);
    }
    
    public function findByKode(string $kode): ?Jurusan
    {
        return Jurusan::where('kode_jurusan', $kode)->first();
    }
    
    public function generateUniqueKode(string $baseKode): string
    {
        $kode = $baseKode;
        $i = 1;
        
        while ($this->findByKode($kode)) {
            $kode = $baseKode . $i++;
        }
        
        return $kode;
    }
}
```

---

#### **Step 3: Create Service**

```php
// app/Services/MasterData/JurusanService.php
class JurusanService
{
    public function __construct(
        private JurusanRepository $jurusanRepo,
        private UserService $userService
    ) {}
    
    public function createJurusan(JurusanData $data): Jurusan
    {
        // Generate unique kode if empty
        if (empty($data->kode_jurusan)) {
            $data->kode_jurusan = $this->generateKodeFromNama($data->nama_jurusan);
            $data->kode_jurusan = $this->jurusanRepo->generateUniqueKode($data->kode_jurusan);
        }
        
        $jurusan = $this->jurusanRepo->create($data->toArray());
        
        // Auto-create Kaprodi if requested
        if ($data->create_kaprodi) {
            $kaprodi = $this->userService->createKaprodiForJurusan($jurusan);
            $this->jurusanRepo->assignKaprodi($jurusan->id, $kaprodi->id);
        }
        
        return $jurusan;
    }
    
    private function generateKodeFromNama(string $nama): string
    {
        // Business logic here
    }
}
```

---

#### **Step 4: Create FormRequest**

```php
// app/Http/Requests/MasterData/CreateJurusanRequest.php
class CreateJurusanRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'nama_jurusan' => ['required', 'string', 'max:191'],
            'kode_jurusan' => ['nullable', 'string', 'max:20', 'unique:jurusan'],
            'kaprodi_user_id' => ['nullable', 'exists:users,id'],
            'create_kaprodi' => ['nullable', 'boolean'],
        ];
    }
}
```

---

#### **Step 5: Refactor Controller**

**BEFORE (Lines 32-91 = 60 lines):**
```php
public function store(Request $request)
{
    $data = $request->validate([...]);
    
    if (empty($data['kode_jurusan'])) {
        // 10 lines of kode generation
    }
    
    $jurusan = Jurusan::create($data);
    
    if ($request->has('create_kaprodi')) {
        // 30+ lines of user creation
    }
    
    return redirect()->route('jurusan.index');
}
```

**AFTER (12 lines):**
```php
public function store(CreateJurusanRequest $request)
{
    $jurusanData = JurusanData::from($request->validated());
    $jurusan = $this->jurusanService->createJurusan($jurusanData);
    
    if ($request->create_kaprodi) {
        session()->flash('kaprodi_created', $this->jurusanService->getLastCreatedKaprodi());
    }
    
    return redirect()->route('jurusan.index')
        ->with('success', 'Jurusan berhasil dibuat.');
}
```

**Reduction:** 60 lines → 12 lines ✅

---

## ✅ **BENEFITS OF REFACTORING**

### **1. Testability** ✅
- Can unit test Services independently
- Can mock Repositories
- No HTTP dependencies in business logic

### **2. Reusability** ✅
- Service methods can be called from:
  * Controllers
  * Console commands
  * Jobs
  * APIs
  * Anywhere!

### **3. Maintainability** ✅
- Business logic in ONE place
- Easy to find and modify
- Clear separation of concerns

### **4. Consistency** ✅
- All CRUD operations follow same pattern
- Standardized error handling
- Unified validation

### **5. Code Quality** ✅
- Smaller methods
- Single responsibility
- DRY (Don't Repeat Yourself)
- Clean code principles

---

## 📊 **METRICS**

### **Current State:**

| Metric | Value | Target |
|--------|-------|--------|
| Avg lines per controller method | 45 | < 20 |
| Controllers with direct queries | 15 | 0 |
| Controllers with Services | 8 | 26 |
| Controllers with FormRequests | 6 | 20 |
| Code duplication | High | Low |

### **After Refactoring:**

| Metric | Current | Target | Improvement |
|--------|---------|--------|-------------|
| Avg lines/method | 45 | 18 | **60% reduction** |
| Direct queries | 15 | 0 | **100% elimination** |
| Services usage | 8 | 26 | **225% increase** |
| FormRequests | 6 | 20 | **233% increase** |

---

## 🎯 **SUMMARY**

### **Critical Findings:**

1. **JurusanController** - 355 lines, 15+ violations ❌
2. **KelasController** - 256 lines, 10+ violations ❌
3. **RiwayatPelanggaranController** - Partial violations ⚠️
4. **12 other controllers** - Various violations ⚠️

### **Immediate Actions:**

**THIS WEEK:**
- ✅ Refactor JurusanController (P0)
- ✅ Refactor KelasController (P0)

**NEXT WEEK:**
- ⏭️ Refactor Report controllers (P1)
- ⏭️ Refactor Rules controllers (P2)

**LATER:**
- Dashboard unification (P3)
- JenisPelanggaranController (P2)

---

**Status:** 🔴 **CRITICAL - ACTION REQUIRED**  
**Recommendation:** Start with JurusanController as template for others  
**Estimated Total Effort:** 2-3 weeks for complete refactoring  
**Impact:** MAJOR improvement in code quality and maintainability

---

**Audit Completed:** 2025-12-11  
**Next Review:** After Phase 1 completion
