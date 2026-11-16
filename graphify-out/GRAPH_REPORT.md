# Graph Report - akreditasi-client  (2026-11-16)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 1833 nodes · 4201 edges · 216 communities (20 shown, 196 thin omitted)
- Extraction: 91% EXTRACTED · 9% INFERRED · 0% AMBIGUOUS · INFERRED: 366 edges (avg confidence: 0.85)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `c87e214b`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- ReviewerController
- AppServiceProvider.php
- TridharmaService
- AkreditasiService
- Illuminate\View\View
- composer.json
- Illuminate\Http\Client\Request
- PenelitianPkmController
- web.php
- MataKuliahController.php
- TestCase
- Illuminate\Http\JsonResponse
- Illuminate\Database\Schema\Blueprint
- Illuminate\Http\RedirectResponse
- AkademikService
- bootstrap/app.php
- DosenShowTest
- .put
- SertifikasiController
- TenagaKependidikanController
- Illuminate\Http\Request
- TenagaKependidikanController.php
- AGENTS.md
- ImportPdaTendikCommand
- CLAUDE.md
- package.json
- KerjaSamaController
- KurikulumController
- NilaiMahasiswaController
- PrestasiMahasiswaController
- TenagaKependidikanTest
- TenagaKependidikanService
- CplController.php
- DosenController
- .post
- MahasiswaController
- PrestasiMahasiswaController.php
- PrestasiMahasiswaTest
- SertifikasiPageTest
- KelasController
- PendidikanController.php
- MataKuliahTest
- AngkatanController
- MahasiswaTest
- MataKuliahController
- PermissionController
- TahunAkademikController
- ReviewerDashboardTest
- ImportPdaTendikCommand.php
- CplTest
- KelasTest
- KegiatanMahasiswaController
- .sendRequest
- NilaiMahasiswaTest
- AuthController
- UppsController
- PendidikanPageTest
- InstrumenController
- LembagaAkreditasiController
- LaporanAkhirController
- PerguruanTinggiController
- KerjaSamaTest
- IndikatorController
- KriteriaController
- SubkriteriaController
- AngkatanKelasController
- SpmiController
- luaran/index.blade.php
- README.md
- .put
- DashboardTest
- SidebarTriDharmaTest
- AkademikEndpointOptimizationTest
- PendidikanTendikSyncTest
- ExampleTest
- penelitian-pkm/create.blade.php
- penelitian-pkm/edit.blade.php
- app.js
- angkatan/index.blade.php
- angkatan/show.blade.php
- cpl/index.blade.php
- views/dashboard.blade.php
- dosen/index.blade.php
- dosen/show.blade.php
- kegiatan-mahasiswa/create.blade.php
- kegiatan-mahasiswa/edit.blade.php
- kegiatan-mahasiswa/index.blade.php
- kegiatan-mahasiswa/show.blade.php
- kelas/index.blade.php
- kelas/show.blade.php
- kerja-sama/index.blade.php
- kerja-sama/show.blade.php
- kurikulum/create.blade.php
- kurikulum/edit.blade.php
- kurikulum/index.blade.php
- kurikulum/show.blade.php
- mahasiswa/index.blade.php
- jenis-prestasi.blade.php
- mahasiswa/show.blade.php
- mata-kuliah/index.blade.php
- mata-kuliah/show.blade.php
- nilai-mahasiswa/index.blade.php
- kaprodi/dashboard.blade.php
- laporan-akhir.blade.php
- plotting.blade.php
- project.blade.php
- project-detail.blade.php
- tindak-lanjut-spmi.blade.php
- validasi-monitoring.blade.php
- assessment-detail.blade.php
- assessments.blade.php
- reviewer/dashboard.blade.php
- bukti-dokumen.blade.php
- superadmin/dashboard.blade.php
- indikator.blade.php
- instrumen.blade.php
- kriteria.blade.php
- lembaga-akreditasi.blade.php
- lembar-kerja.blade.php
- rubrik.blade.php
- standar-spmi.blade.php
- subkriteria.blade.php
- pendidikan/index.blade.php
- penelitian-pkm/index.blade.php
- penelitian-pkm/show.blade.php
- perguruan-tinggi/index.blade.php
- permissions/index.blade.php
- prestasi-mahasiswa/index.blade.php
- prestasi-mahasiswa/show.blade.php
- prodi/index.blade.php
- roles/create.blade.php
- roles/edit.blade.php
- roles/index.blade.php
- form.blade.php
- sertifikasi/index.blade.php
- sertifikasi/show.blade.php
- tindak-lanjut.blade.php
- tahun-akademik/detail.blade.php
- tahun-akademik/index.blade.php
- kelas.blade.php
- tenaga-kependidikan/create.blade.php
- tenaga-kependidikan/edit.blade.php
- tenaga-kependidikan/index.blade.php
- tenaga-kependidikan/show.blade.php
- anggota.blade.php
- users/index.blade.php

## God Nodes (most connected - your core abstractions)
1. `AkademikService` - 216 edges
2. `AkreditasiService` - 126 edges
3. `TridharmaService` - 107 edges
4. `Controller` - 52 edges
5. `TestCase` - 44 edges
6. `PenelitianPkmController` - 33 edges
7. `TenagaKependidikanController` - 29 edges
8. `ImportPdaTendikCommand` - 29 edges
9. `MahasiswaController` - 25 edges
10. `SertifikasiController` - 24 edges

## Surprising Connections (you probably didn't know these)
- `{closure#1}()` --calls--> `TridharmaService`  [EXTRACTED]
  app/Providers/AppServiceProvider.php → app/Services/TridharmaService.php
- `{closure#2}()` --calls--> `TenagaKependidikanService`  [EXTRACTED]
  app/Providers/AppServiceProvider.php → app/Services/TenagaKependidikanService.php
- `ReviewerController` --inherits--> `Controller`  [EXTRACTED]
  app/Http/Controllers/ReviewerController.php → app/Http/Controllers/Controller.php
- `ReviewerService` --references--> `AkademikService`  [EXTRACTED]
  app/Services/ReviewerService.php → app/Services/AkademikService.php
- `ReviewerService` --references--> `AkreditasiService`  [EXTRACTED]
  app/Services/ReviewerService.php → app/Services/AkreditasiService.php

## Import Cycles
- None detected.

## Communities (216 total, 196 thin omitted)

### Community 0 - "ReviewerController"
Cohesion: 0.06
Nodes (5): {closure#1}(), {closure#2}(), {closure#8}(), ReviewerController, ReviewerService

### Community 1 - "AppServiceProvider.php"
Cohesion: 0.06
Nodes (11): Kualifikasi, Sertifikasi, TenagaKependidikan, User, AppServiceProvider, {closure#1}(), {closure#2}(), {closure#3}() (+3 more)

### Community 5 - "composer.json"
Cohesion: 0.04
Nodes (48): pestphp/pest-plugin, php-http/discovery, autoload, autoload-dev, psr-4, psr-4, config, allow-plugins (+40 more)

### Community 6 - "Illuminate\Http\Client\Request"
Cohesion: 0.08
Nodes (40): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), {closure#1}(), {closure#2}() (+32 more)

### Community 8 - "PenelitianPkmController"
Cohesion: 0.11
Nodes (3): {closure#1}(), {closure#5}(), PenelitianPkmController

### Community 9 - "web.php"
Cohesion: 0.07
Nodes (5): BuktiDokumenController, DashboardController, LembarKerjaController, StandarSpmiController, Controller

### Community 10 - "MataKuliahController.php"
Cohesion: 0.06
Nodes (3): {closure#24}(), {closure#25}(), {closure#5}()

### Community 11 - "TestCase"
Cohesion: 0.09
Nodes (5): AkreditasiIntegrationTest, ExampleTest, LaporanAkhirTest, SertifikasiTendikSyncTest, TestCase

### Community 13 - "Illuminate\Database\Schema\Blueprint"
Cohesion: 0.10
Nodes (12): {closure#1}(), {closure#2}(), {closure#3}(), {closure#1}(), {closure#2}(), {closure#1}(), {closure#2}(), {closure#3}() (+4 more)

### Community 14 - "Illuminate\Http\RedirectResponse"
Cohesion: 0.11
Nodes (3): ProdiController, RoleController, UserController

### Community 16 - "bootstrap/app.php"
Cohesion: 0.13
Nodes (8): {closure#1}(), {closure#2}(), EnsureAkademikAuthenticated, EnsureUserPermission, EnsureUserRole, {closure#1}(), {closure#2}(), {closure#3}()

### Community 24 - "AGENTS.md"
Cohesion: 0.09
Nodes (21): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+13 more)

### Community 26 - "CLAUDE.md"
Cohesion: 0.09
Nodes (21): APIs & Eloquent Resources, Application Structure & Architecture, Artisan, Conventions, Deployment, Do Things the Laravel Way, Documentation Files, Foundational Context (+13 more)

### Community 27 - "package.json"
Cohesion: 0.13
Nodes (20): devDependencies, concurrently@^10.0.3, laravel-vite-plugin@^3.1, tailwindcss@^4.0.0, @tailwindcss/vite@^4.0.0, vite@^8.0.0, optionalDependencies, @laravel/multiplex@^0.4.1 (+12 more)

### Community 36 - "DosenController"
Cohesion: 0.16
Nodes (7): {closure#1}(), {closure#2}(), {closure#3}(), {closure#4}(), {closure#5}(), {closure#6}(), DosenController

### Community 43 - "PendidikanController.php"
Cohesion: 0.14
Nodes (6): {closure#2}(), {closure#3}(), {closure#4}(), {closure#2}(), {closure#4}(), {closure#5}()

### Community 50 - "ReviewerDashboardTest"
Cohesion: 0.14
Nodes (3): {closure#1}(), {closure#2}(), ReviewerDashboardTest

### Community 59 - "PendidikanPageTest"
Cohesion: 0.18
Nodes (3): {closure#1}(), {closure#2}(), PendidikanPageTest

### Community 71 - "luaran/index.blade.php"
Cohesion: 0.25
Nodes (7): luaran._form_actions, luaran._form_buku, luaran._form_hki, luaran._form_jurnal, luaran._form_produk, luaran._form_seminar, layouts.app

### Community 72 - "README.md"
Cohesion: 0.25
Nodes (7): About Laravel, Agentic Development, Code of Conduct, Contributing, Learning Laravel, License, Security Vulnerabilities

## Knowledge Gaps
- **79 isolated node(s):** `layouts.app`, `layouts.app`, `layouts.app`, `layouts.app`, `layouts.app` (+74 more)
  These have ≤1 connection - possible missing edges or undocumented components. (Counts symbols only; 636 node(s) total have ≤1 connection when file, concept and rationale nodes are included.)
- **196 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `AkademikService` connect `AkademikService` to `ReviewerController`, `Illuminate\View\View`, `Illuminate\Http\Client\Request`, `Illuminate\Http\Client\Response`, `PenelitianPkmController`, `MataKuliahController.php`, `Illuminate\Http\RedirectResponse`, `bootstrap/app.php`, `.put`, `.show`, `KurikulumController`, `NilaiMahasiswaController`, `PrestasiMahasiswaController`, `.post`, `CplController.php`, `MahasiswaController`, `PrestasiMahasiswaController.php`, `KelasController`, `AngkatanController`, `MataKuliahController`, `PermissionController`, `TahunAkademikController`, `KegiatanMahasiswaController`, `.sendRequest`, `AuthController`, `UppsController`, `MahasiswaController.php`, `PerguruanTinggiController`, `AngkatanKelasController`, `KegiatanMahasiswaController.php`, `KurikulumController.php`?**
  _High betweenness centrality (0.128) - this node is a cross-community bridge._
- **What connects `layouts.app`, `layouts.app`, `layouts.app` to the rest of the system?**
  _79 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `ReviewerController` be split into smaller, more focused modules?**
  _Cohesion score 0.05858585858585859 - nodes in this community are weakly interconnected._
- **Why does `TestCase` connect `TestCase` to `KerjaSamaTest`, `TenagaKependidikanTest`, `Illuminate\Http\Client\Request`, `PrestasiMahasiswaTest`, `SertifikasiPageTest`, `DashboardTest`, `MataKuliahTest`, `Illuminate\Database\Schema\Blueprint`, `MahasiswaTest`, `AkademikEndpointOptimizationTest`, `PendidikanTendikSyncTest`, `DosenShowTest`, `ReviewerDashboardTest`, `SidebarTriDharmaTest`, `CplTest`, `KelasTest`, `NilaiMahasiswaTest`, `PendidikanPageTest`?**
  _High betweenness centrality (0.116) - this node is a cross-community bridge._
- **Should `AppServiceProvider.php` be split into smaller, more focused modules?**
  _Cohesion score 0.055904961565338925 - nodes in this community are weakly interconnected._
- **Why does `TridharmaService` connect `TridharmaService` to `ReviewerController`, `AppServiceProvider.php`, `TenagaKependidikanService`, `DosenController`, `.post`, `PenelitianPkmController`, `.put`, `TridharmaService.php`, `PendidikanController.php`, `Illuminate\Http\JsonResponse`, `SertifikasiController`, `Illuminate\Http\Request`, `KerjaSamaController`?**
  _High betweenness centrality (0.044) - this node is a cross-community bridge._
- **Should `TridharmaService` be split into smaller, more focused modules?**
  _Cohesion score 0.09796806966618288 - nodes in this community are weakly interconnected._