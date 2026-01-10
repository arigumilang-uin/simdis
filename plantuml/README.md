# 📊 PlantUML Diagrams

## Sistem Informasi Kedisiplinan Siswa SMK Negeri 1

### Deskripsi

Folder ini berisi diagram UML dalam format PlantUML (.puml) yang dapat di-render menggunakan:

-   **PlantUML Extension** di VS Code
-   **PlantUML Online Server**: https://www.plantuml.com/plantuml
-   **Local PlantUML JAR** dengan Java + Graphviz

---

## 📁 Daftar Diagram (20 Files)

### 1. Use Case Diagram

| File                 | Deskripsi                                                          |
| -------------------- | ------------------------------------------------------------------ |
| `01_usecase_v2.puml` | Diagram lengkap dengan 8 aktor dan 24 use cases (Satellite Layout) |

### 2. Class Diagram

| File               | Deskripsi                                              |
| ------------------ | ------------------------------------------------------ |
| `02_class_v2.puml` | 16 Eloquent models dengan atribut, methods, dan relasi |

### 3. Sequence Diagrams (5 Files)

| File                           | Deskripsi                                    |
| ------------------------------ | -------------------------------------------- |
| `03_sequence_login.puml`       | Proses login & autentikasi                   |
| `03_sequence_catat.puml`       | Proses catat pelanggaran dengan rules engine |
| `03_sequence_approval.puml`    | Proses approval tindak lanjut                |
| `03_sequence_pembinaan.puml`   | Proses pembinaan internal siswa ✨ **NEW**   |
| `03_sequence_cetak_surat.puml` | Proses cetak surat panggilan ✨ **NEW**      |

### 4. Activity Diagrams (3 Files)

| File                         | Deskripsi                              |
| ---------------------------- | -------------------------------------- |
| `04_activity_catat.puml`     | Workflow catat pelanggaran             |
| `04_activity_approval.puml`  | Workflow approval tindak lanjut        |
| `04_activity_pembinaan.puml` | Workflow pembinaan internal end-to-end |

### 5. State Diagrams (3 Files)

| File                         | Deskripsi                                   |
| ---------------------------- | ------------------------------------------- |
| `05_state_tindaklanjut.puml` | Lifecycle TindakLanjut (4 states + Ditolak) |
| `05_state_pembinaan.puml`    | Lifecycle PembinaanStatus (3 states)        |
| `05_state_siswa_user.puml`   | Lifecycle Siswa & User                      |

### 6. Deployment Diagram

| File                    | Deskripsi                         |
| ----------------------- | --------------------------------- |
| `06_deployment_v2.puml` | Production environment dengan VPS |

### 7. Component Diagram

| File                   | Deskripsi                           |
| ---------------------- | ----------------------------------- |
| `07_component_v2.puml` | Arsitektur aplikasi Laravel 3-layer |

### 8. ERD (Entity Relationship Diagram) ✨ NEW

| File             | Deskripsi                         |
| ---------------- | --------------------------------- |
| `08_erd_v2.puml` | Struktur database dengan 16 tabel |

### 9. Package Diagram ✨ NEW

| File                 | Deskripsi                           |
| -------------------- | ----------------------------------- |
| `09_package_v2.puml` | Struktur folder & namespace Laravel |

### 10. Flowcharts (3 Files) - Subfolder `flowcharts/`

| File                        | Deskripsi                         |
| --------------------------- | --------------------------------- |
| `flowchart_pencatatan.puml` | Alur pencatatan pelanggaran siswa |
| `flowchart_approval.puml`   | Alur approval tindak lanjut       |
| `flowchart_pembinaan.puml`  | Alur pembinaan internal           |

---

## 📊 Ringkasan

| Kategori   | Jumlah Files | Status          |
| ---------- | ------------ | --------------- |
| Use Case   | 1            | ✅ Lengkap      |
| Class      | 1            | ✅ Lengkap      |
| Sequence   | 5            | ✅ Lengkap      |
| Activity   | 3            | ✅ Lengkap      |
| State      | 3            | ✅ Lengkap      |
| Deployment | 1            | ✅ Lengkap      |
| Component  | 1            | ✅ Lengkap      |
| ERD        | 1            | ✅ NEW          |
| Package    | 1            | ✅ NEW          |
| Flowchart  | 3            | ✅ Lengkap      |
| **TOTAL**  | **20**       | ✅ **COMPLETE** |

---

## 🚀 Cara Render

### Opsi 1: VS Code Extension (Recommended)

1. Install extension **"PlantUML"** by jebbs
2. Pastikan Java & Graphviz terinstall
3. Buka file `.puml`
4. Tekan `Alt + D` untuk preview
5. Tekan `Ctrl + Shift + P` → "PlantUML: Export Current Diagram"
6. Pilih format **SVG** untuk kualitas terbaik

### Opsi 2: Command Line

```bash
# Pastikan Java & Graphviz terinstall
java -jar plantuml.jar filename.puml

# Render semua file ke SVG
java -jar plantuml.jar -tsvg docs/plantuml/*.puml

# Render dengan high DPI
java -jar plantuml.jar -tpng -Sdpi=300 docs/plantuml/*.puml
```

### Opsi 3: Online Server

1. Buka https://www.plantuml.com/plantuml
2. Copy-paste isi file `.puml`
3. Diagram akan auto-render

---

## 📋 Catatan Styling

Semua diagram menggunakan skinparam standar:

-   `shadowing false` - Tampilan modern tanpa shadow
-   `linetype ortho` - Garis tegak lurus
-   Font: Segoe UI
-   Color scheme:
    -   Primary: #1976D2 (Blue)
    -   Success: #4CAF50 (Green)
    -   Warning: #FF8F00 (Amber)
    -   Error: #F44336 (Red)
    -   Background: #FEFEFE (Off-white)

---

## 📁 Struktur Folder

```
docs/plantuml/
├── 01_usecase_v2.puml
├── 02_class_v2.puml
├── 03_sequence_login.puml
├── 03_sequence_catat.puml
├── 03_sequence_approval.puml
├── 03_sequence_pembinaan.puml      ✨ NEW
├── 03_sequence_cetak_surat.puml    ✨ NEW
├── 04_activity_catat.puml
├── 04_activity_approval.puml
├── 04_activity_pembinaan.puml
├── 05_state_tindaklanjut.puml
├── 05_state_pembinaan.puml
├── 05_state_siswa_user.puml
├── 06_deployment_v2.puml
├── 07_component_v2.puml
├── 08_erd_v2.puml                  ✨ NEW
├── 09_package_v2.puml              ✨ NEW
├── README.md
└── flowcharts/
    ├── flowchart_pencatatan.puml
    ├── flowchart_approval.puml
    ├── flowchart_pembinaan.puml
    └── README.md
```

---

**Dibuat untuk Sistem Informasi Kedisiplinan Siswa SMK Negeri 1**  
**Terakhir diupdate: 27 Desember 2024**
