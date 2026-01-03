# PEMBINAAN INTERNAL - AUDIT & FIX PLAN

**Date:** 2025-12-12 02:45
**Issue:** Pembinaan Internal Rules tidak berjalan/ditampilkan

---

## 🐛 MASALAH DITEMUKAN

### **1. NO NOTIFICATION SENT** ❌

**Current Flow:**
```
Catat Pelanggaran
↓
PelanggaranService::catatPelanggaran()
↓
rulesEngine->processBatch()  ← Hanya handle frequency rules + surat
↓
❌ TIDAK ada notifikasi pembinaan internal!
```

**Expected Flow:**
```
Catat Pelanggaran
↓
processBatch() → Evaluate frequency + create surat (if needed)
↓
✅ Evaluate pembinaan internal recommendation
↓
✅ Send notification to pembina
```

---

### **2. NO UI DISPLAY** ❌

**Check locations:**
- ❌ Halaman siswa detail
- ❌ Dashboard pembina
- ❌ Notification center

**Pembinaan rekomendasi HANYA dipanggil di:**
- `getStatistikSiswa()` → For data retrieval
- NOT displayed in UI!

---

## 📋 TEST CASE (User Provided)

**Setup:**
- Jenis Pelanggaran A: min=1, max=1, poin=25
- 5 Pembinaan Internal Rules:
  1. 15-30 poin → Wali Kelas
  2. 31-60 poin → Wali Kelas + Kaprodi
  3. 61-80 poin → Wali Kelas + Kaprodi + Waka
  4. 81-110 poin → Semua pembina
  5. 111-130 poin → Semua pembina + "dipulangkan"

**Expected Behavior:**
| Freq | Total Poin | Rule Match | Notified |
|------|------------|------------|----------|
| 1x | 25 | Rule 1 | Wali Kelas |
| 2x | 50 | Rule 2 | Wali Kelas + Kaprodi |
| 3x | 75 | Rule 3 | Wali Kelas + Kaprodi + Waka |
| 4x | 100 | Rule 4 | All pembina |
| 5x | 125 | Rule 5 | All pembina |

**Current Behavior:**
| Freq | Total Poin | Rule Match | Notified |
|------|------------|------------|----------|
| 1x | 25 | Rule 1 | ❌ NOBODY |
| 2x | 50 | Rule 2 | ❌ NOBODY |
| ALL | ALL | ALL | ❌ NOBODY |

---

## ✅ SOLUTION PLAN

### **Fix 1: Add Pembinaan Internal Notification**

**Location:** `PelanggaranService::catatPelanggaran()`

**Add after processBatch():**
```php
// Step 3: Check pembinaan internal & notify pembina
$totalPoin = $this->rulesEngine->hitungTotalPoinAkumulasi($data->siswa_id);
$pembinaanRekomendasi = $this->rulesEngine->getPembinaanInternalRekomendasi($totalPoin);

if (!empty($pembinaanRekomendasi['pembina_roles'])) {
    // Send notification to recommended pembina
    $this->notificationService->notifyPembinaanInternal(
        $createdRiwayat->siswa,
        $pembinaanRekomendasi
    );
}
```

---

### **Fix 2: Create Notification Method**

**Location:** `NotificationService`

**New method:**
```php
public function notifyPembinaanInternal(Siswa $siswa, array $rekomendasi): void
{
    $pembina = $this->getUsersByRoles($rekomendasi['pembina_roles'], $siswa);
    
    foreach ($pembina as $user) {
        Notification::create([
            'user_id' => $user->id,
            'type' => 'pembinaan_internal',
            'title' => "Siswa Perlu Pembinaan Internal",
            'message' => sprintf(
                "Siswa %s (Total: %d poin) memerlukan pembinaan: %s",
                $siswa->nama,
                $rekomendasi['total_poin'],
                $rekomendasi['keterangan']
            ),
            'data' => [
                'siswa_id' => $siswa->id,
                'total_poin' => $rekomendasi['total_poin'],
                'range_text' => $rekomendasi['range_text'],
                'keterangan' => $rekomendasi['keterangan'],
            ],
        ]);
    }
}
```

---

### **Fix 3: Display in UI**

**Location 1: Siswa Detail Page**

Add pembinaan recommendation display:
```blade
@if($statistik['pembinaan_rekomendasi']['pembina_roles'])
    <div class="alert alert-warning">
        <h5>🔔 Rekomendasi Pembinaan Internal</h5>
        <p><strong>Range:</strong> {{ $statistik['pembinaan_rekomendasi']['range_text'] }}</p>
        <p><strong>Pembina:</strong> 
            @foreach($statistik['pembinaan_rekomendasi']['pembina_roles'] as $role)
                <span class="badge badge-primary">{{ $role }}</span>
            @endforeach
        </p>
        <p><strong>Keterangan:</strong> {{ $statistik['pembinaan_rekomendasi']['keterangan'] }}</p>
    </div>
@endif
```

**Location 2: Dashboard Pembina**

Show list of students needing pembinaan:
```php
// Controller
$siswaPerluPembinaan = $rulesEngine->getSiswaPerluPembinaan(
    poinMin: 15,  // From pembinaan rules
    poinMax: 130
);
```

---

## 🔧 IMPLEMENTATION STEPS

### **Step 1: Add Notification Call** ⭐⭐⭐⭐⭐

**File:** `app/Services/Pelanggaran/PelanggaranService.php`

**Changes:**
1. Inject `NotificationService`
2. Calculate total poin after processBatch
3. Get pembinaan rekomendasi
4. Send notification if applicable

---

### **Step 2: Create Notification Method** ⭐⭐⭐⭐

**File:** `app/Services/NotificationService.php`

**New method:** `notifyPembinaanInternal()`

**Logic:**
1. Get pembina users by roles
2. Create notification for each
3. Send email/push (optional)

---

### **Step 3: Update UI** ⭐⭐⭐

**Files:**
- `resources/views/siswa/show.blade.php`
- `resources/views/dashboards/[role].blade.php`

**Display:**
1. Pembinaan recommendation on siswa page
2. List of students needing pembinaan on dashboard

---

## 📊 VERIFICATION CHECKLIST

**After Implementation:**

- [ ] Catat pelanggaran 1x → Notifikasi ke Wali Kelas
- [ ] Catat pelanggaran 2x → Notifikasi ke Wali Kelas + Kaprodi
- [ ] Catat pelanggaran 3x → Notifikasi ke pembina sesuai rule
- [ ] Display rekomendasi di halaman siswa
- [ ] Display list di dashboard pembina
- [ ] Notifikasi muncul di notification center

---

## 🎯 PRIORITY

**HIGH PRIORITY** - Feature critical untuk sistem pembinaan

**Impact:**
- Pembina tidak tahu siswa perlu pembinaan
- Sistem pembinaan internal completely non-functional
- User expectation not met

**Effort:** Medium (2-3 files to modify)

---

## 💡 ADDITIONAL ENHANCEMENTS

### **Optional: Pembinaan History**

Create `pembinaan_internal` table to track:
- When recommendation was made
- Which pembina were notified
- Actions taken by pembina

### **Optional: Escalation**

If siswa stays at same recommendation level for X days:
- Auto-escalate to higher pembina
- Send reminder notifications

---

**Status:** ❌ **NOT IMPLEMENTED**  
**Next:** Implement Fix 1, 2, 3 in sequence
