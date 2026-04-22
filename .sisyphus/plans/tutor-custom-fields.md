# Tutor LMS Custom Registration Fields - Work Plan

## TL;DR

> **Quick Summary**: WordPress plugin yang memungkinkan admin Tutor LMS menambahkan kolom data kustom ke formulir pendaftaran siswa tanpa coding.
> 
> **Deliverables**:
> - Plugin WordPress lengkap (tutor-custom-registration-fields.php)
> - Halaman admin di Tutor > Custom Fields
> - Custom fields terintegrasi dengan form registrasi Tutor LMS
> - Data tersimpan di wp_usermeta
> 
> **Estimated Effort**: Medium
> **Parallel Execution**: YES - 3 waves
> **Critical Path**: Plugin scaffolding → Admin CRUD → Frontend hooks → Display

---

## Context

### Original Request
Membuat plugin WordPress untuk Tutor LMS yang memberikan kemampuan kepada admin untuk menambahkan kolom data kustom pada formulir pendaftaran siswa tanpa coding.

### Interview Summary
**Key Discussions**:
- Plugin type: WordPress plugin baru (standalone)
- Test strategy: Manual QA only (tidak perlu unit test)
- Admin UI: Custom page dengan sidebar menu "Tutor > Custom Fields"
- Backend: Tutor LMS hooks (`tutor_register_form_fields`, `tutor_save_extra_profile_fields`, `tutor_show_extra_profile_fields`)
- Select format: `value=Label` per baris
- Display: Tutor LMS student profile saja (bukan wp-admin)

**Research Findings**:
- Tutor LMS hooks tersedia untuk inject custom fields
- Menyimpan configuration di wp_options sebagai array
- User meta untuk data user dengan meta_key yang指定

### Metis Review
**Identified Gaps** (addressed):
- Input sanitization: Wajib untuk prevent XSS/SQL Injection
- Nonce verification: Di setiap form submission admin
- Error handling: Untuk form validation di frontend

---

## Work Objectives

### Core Objective
Memberikan kemampuan bagi admin Tutor LMS untuk membuat, melihat, dan menghapus kolom data kustom yang muncul di formulir registrasi siswa.

### Concrete Deliverables
- [ ] File utama plugin: `tutor-custom-registration-fields.php`
- [ ] Halaman admin di wp-admin (Tutor > Custom Fields)
- [ ] Tabel daftar field yang sudah dibuat
- [ ] Form tambah field baru
- [ ] Custom fields muncul di registration form Tutor LMS
- [ ] Validasi server untuk field Required
- [ ] Data tersimpan di wp_usermeta
- [ ] Tampilan di student profile Tutor LMS

### Definition of Done
- [ ] Admin dapat membuat field baru dari dashboard
- [ ] Field muncul di form registrasi secara real-time
- [ ] Field Required tidak mengizinkan registrasi jika kosong
- [ ] Data tersimpan dan bisa diubah dari admin
- [ ] Siswa dapat melihat data mereka di profil

### Must Have
- Input sanitization untuk semua input user
- Nonce verification untuk form admin
- Server-side validation untuk Required fields
- Delete confirmation dengan pop-up

### Must NOT Have (Guardrails)
- File upload functionality (hanya input teks/pilihan)
- Multi-language support
- User role management
- Modifikasi tabel WordPress (gunakan wp_options/wp_usermeta)

---

## Verification Strategy

### Test Decision
- **Infrastructure exists**: NO
- **Automated tests**: None
- **Framework**: N/A
- **QA Policy**: Manual verification via browser

### QA Policy
Every task MUST include agent-executed QA scenarios.
Evidence saved to `.sisyphus/evidence/task-{N}-{scenario-slug}.{ext}`.

- **WordPress Admin**: Use Playwright - Navigate admin pages, fill forms, submit, verify DOM
- **Frontend**: Use Playwright - Navigate registration page, fill fields, submit, verify data

---

## Execution Strategy

### Parallel Execution Waves

Wave 1 (Foundation - plugin structure):
├── Task 1: Plugin scaffolding + header + i18n setup
├── Task 2: Class structure + autoloader
├── Task 3: Admin menu registration
├── Task 4: Main admin page template
└── Task 5: Settings/options handler

Wave 2 (Core features - CRUD + Storage):
├── Task 6: Field CRUD functions (create, read, delete)
├── Task 7: Field model/class dengan validasi
├── Task 8: Admin form handler (save/update)
├── Task 9: Delete field handler dengan nonce
├── Task 10: Admin table display dengan Delete confirm
└── Task 11: AJAX handler untuk delete

Wave 3 (Frontend Integration - Tutor LMS):
├── Task 12: Register form hook injection
├── Task 13: Save handler untuk registration
├── Task 14: Validation untuk Required fields
├── Task 15: Profile display hook
├── Task 16: Edit profile hook

Wave FINAL (Reviews):
├── Task F1: Plan compliance audit (oracle)
├── Task F2: Code quality review (security + patterns)
├── Task F3: Manual testing scenarios
└── Task F4: Scope fidelity check

---

## TODOs

- [ ] 1. **Plugin Scaffolding - Main File Setup**

  **What to do**:
  - Buat file utama plugin: `tutor-custom-registration-fields.php`
  - WP Plugin header comment (Name, Version, Description, Author, License, Text Domain)
  - Plugin version constant
  - Plugin directory path constants
  - Basic plugin activation/deactivation hooks
  - Load plugin text domain for i18n

  **Must NOT do**:
  - Jangan include functionality di main file langsung
  - Jangan buat hardcoded paths - gunakan plugin_dir_path()

  **Recommended Agent Profile**:
  > Category: quick (plugin scaffolding adalah template work)
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 2-5)
  - **Blocks**: Task 2-5
  - **Blocked By**: None (start immediately)

  **References**:
  - WP Plugin Handbook: https://developer.wordpress.org/plugins/the-basics/header-requirements/
  - Text domain standard: nama plugin dengan dashes

  **Acceptance Criteria**:
  - [ ] File plugin muncul di WP Admin Plugins list
  - [ ] Plugin dapat diaktifkan tanpa error
  - [ ] Version tercetak benar di header

  **QA Scenarios**:
  ```
  Scenario: Plugin muncul di WordPress Plugins list
    Tool: Playwright
    Preconditions: WordPress terinstall, plugin diupload
    Steps:
      1. Navigate ke wp-admin/plugins.php
      2. Scroll dan cari "Tutor LMS Custom Registration Fields"
      3. Verify plugin name terlihat
      4. Verify "Activate" button ada
    Expected Result: Plugin terlihat dan bisa diaktifkan
    Evidence: .sisyphus/evidence/task-1-plugin-list.png
  ```

- [ ] 2. **Class Structure - Autoloader**

  **What to do**:
  - Buat struktur folder: `/includes/`, `/admin/`, `/frontend/`
  - Buat autoloader function
  - Buat base class untuk setiap module
  - Setup pluginmu() helper function untuk access instance

  **Must NOT do**:
  - Jangan include semua class di satu file
  - Jangan gunakan require_once everywhere

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 1, 3-5)
  - **Blocks**: Tasks 6-16
  - **Blocked By**: Task 1

  **References**:
  - PSR-4 style autoloading patterns
  - Plugin folder structure best practices

  **Acceptance Criteria**:
  - [ ] Autoloader dapat load class dengan benar
  - [ ] Folder structure ada: includes/, admin/, frontend/

  **QA Scenarios**:
  ```
  Scenario: Folder structure ter-create dengan benar
    Tool: Bash
    Preconditions: None
    Steps:
      1. List folder plugin
      2. Verify include/, admin/, frontend/ ada
    Expected Result: 3 folder ada
    Evidence: .sisyphus/evidence/task-2-structure.txt
  ```

- [ ] 3. **Admin Menu Registration**

  **What to do**:
  - Register admin menu menggunakan add_menu_page()
  - Tambahkan submenu di bawah "Tutor" menu
  - Menu title: "Custom Fields"
  - Menu slug: 'tutor-custom-fields'
  - Capability: 'manage_options'

  **Must NOT do**:
  - Jangan buat top-level menu (membuang space)
  - Jangan gunakan capability yang terlalu rendah

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 1-2, 4-5)
  - **Blocks**: Task 10
  - **Blocked By**: Task 2

  **References**:
  - WP add_submenu_page() documentation
  - Tutor LMS menu slug: 'tutor'

  **Acceptance Criteria**:
  - [ ] Menu "Custom Fields" muncul di bawah Tutor menu
  - [ ] Klik menu membuka halaman yang benar

  **QA Scenarios**:
  ```
  Scenario: Menu muncul di bawah Tutor
    Tool: Playwright
    Preconditions: Plugin aktif, Tutor LMS aktif
    Steps:
      1. Login sebagai admin
      2. Hover menu "Tutor"
      3. Verify "Custom Fields" submenu ada
      4. Klik dan verify halaman terbuka
    Expected Result: Menu terlihat dan functional
    Evidence: .sisyphus/evidence/task-3-menu.png
  ```

- [ ] 4. **Admin Page Template**

  **What to do**:
  - Buat main admin page HTML template
  - Layout: Kiri form, Kanan tabel
  - Include WP admin header/footer
  - Add inline styles untuk layout
  - Add nonce field untuk form

  **Must NOT do**:
  - Jangan include JS langsung di HTML (gunakan enqueue)
  - Jangan hardcode WP version

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 1-3, 5)
  - **Blocks**: None
  - **Blocked By**: Task 3

  **References**:
  - WP admin page template structure
  - Layout CSS Grid atau Flexbox

  **Acceptance Criteria**:
  - [ ] Page load tanpa error
  - [ ] Layout dua kolom (form + table)
  - [ ] Responsive di mobile

  **QA Scenarios**:
  ```
  Scenario: Admin page tercopy dengan benar
    Tool: Playwright
    Preconditions: Plugin aktif
    Steps:
      1. Navigate ke Tutor > Custom Fields
      2. Verify page title "Custom Fields"
      3. Verify form di kiri, table di kanan
      4. Resize window ke mobile size
    Expected Result: Layout responsive
    Evidence: .sisyphus/evidence/task-4-admin.png
  ```

- [ ] 5. **Settings/Options Handler**

  **What to do**:
  - Setup option key: 'tutor_custom_fields_settings'
  - Default options structure
  - Getter/setter functions
  - Sanitization callback

  **Must NOT do**:
  - Jangan simpan plain text passwords
  - Jangan gunakan serialize() untuk data dari user

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 1 (with Task 1-4)
  - **Blocks**: Tasks 6-9
  - **Blocked By**: Task 2

  **References**:
  - WP get_option(), update_option() documentation
  - Array storage pattern

  **Acceptance Criteria**:
  - [ ] Options dapat dibaca/ditulis
  - [ ] Default value berfungsi jika belum ada

  **QA Scenarios**:
  ```
  Scenario: Options handler berfungsi
    Tool: Bash (via WP-CLI atau curl)
    Preconditions: Plugin aktif
    Steps:
      1. Check option di database (SELECT * FROM wp_options WHERE option_name = 'tutor_custom_fields_settings')
      2. Verify option ada atau create default
    Expected Result: Option available
    Evidence: .sisyphus/evidence/task-5-options.txt
  ```

- [ ] 6. **Field CRUD Functions**

  **What to do**:
  - Function untuk create field: tcf_create_field($field_data)
  - Function untuk get all fields: tcf_get_fields()
  - Function untuk get single field: tcf_get_field($field_key)
  - Function untuk delete field: tcf_delete_field($field_key)
  - Validate field key format (lowercase, angka, underscore)

  **Must NOT do**:
  - Jangan buat field dengan key yang sama
  - Jangan izinkan uppercase di field key

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 7-11)
  - **Blocks**: Task 8, 9
  - **Blocked By**: Task 5

  **References**:
  - Array field storage: ['field_key' => [...data]]
  - Regex for key validation: `/^[a-z0-9_]+$/`

  **Acceptance Criteria**:
  - [ ] tcf_create_field() menyimpan field ke option
  - [ ] tcf_get_fields() return semua field
  - [ ] tcf_delete_field() hapus field
  - [ ] Field key validation berfungsi

  **QA Scenarios**:
  ```
  Scenario: Field CRUD berfungsi
    Tool: Playwright
    Preconditions: None
    Steps:
      1. Navigate ke Custom Fields page
      2. Create test field: field_key="test_field", label="Test Field", type="text"
      3. Verify field muncul di table
      4. Delete field
      5. Verify field dihapus
    Expected Result: CRUD operations work
    Evidence: .sisyphus/evidence/task-6-crud.png
  ```

- [ ] 7. **Field Model/Class**

  **What to do**:
  - Buat class TCFField untuk representasi field
  - Properties: key, label, type, placeholder, required, meta_key, etc.
  - Method: validate(), to_array(), get_input_html()
  - Static: get_field_types()

  **Must NOT do**:
  - Jangan buat class terlalu besar
  - Jangan hardcode semua field types

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 6, 8-11)
  - **Blocks**: Task 12-16
  - **Blocked By**: Task 6

  **References**:
  - Field types: text, email, number, date, textarea, select

  **Acceptance Criteria**:
  - [ ] TCFField class dapat diinstansiasi
  - [ ] validate() return true/false
  - [ ] get_field_types() return array semua type

  **QA Scenarios**:
  ```
  Scenario: Field model berfungsi
    Tool: Bash (PHP unit test manual)
    Preconditions: None
    Steps:
      1. Include plugin file
      2. Create new TCFField dengan data test
      3. Call validate() - harus return false (empty required)
      4. Set required=true, lalu validate() - harus return false (still empty)
      5. Set value, lalu validate() - harus return true
    Expected Result: Model logic works
    Evidence: .sisyphus/evidence/task-7-model.txt
  ```

- [ ] 8. **Admin Form Handler**

  **What to do**:
  - Handle POST dari form tambah field
  - Validate semua input
  - Sanitize semua input
  - Verify nonce
  - Redirect dengan message sukses/error

  **Must NOT do**:
  - Jangan trustinput langsung tanpa sanitization
  - Jangan redirect tanpa verification

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 6-7, 9-11)
  - **Blocks**: None
  - **Blocked By**: Task 7

  **References**:
  - WP sanitize_text_field(), sanitize_key()
  - Nonce verification: wp_verify_nonce()

  **Acceptance Criteria**:
  - [ ] Form submit redirect ke page yang sama
  - [ ] Success message jika berhasil
  - [ ] Error message jika gagal validasi

  **QA Scenarios**:
  ```
  Scenario: Admin form submission berhasil
    Tool: Playwright
    Preconditions: None
    Steps:
      1. Navigate ke Custom Fields page
      2. Fill form: field_key="nik", label="NIK", type="text", required=true
      3. Submit form
      4. Verify redirect ke page sama
      5. Verify success message
      6. Verify field baru di table
    Expected Result: Field created successfully
    Evidence: .sisyphus/evidence/task-8-form.png
  ```

- [ ] 9. **Delete Field Handler**

  **What to do**:
  - Handle delete request dengan GET parameter
  - Verify nonce
  - Verify field exists sebelum delete
  - Redirect dengan message

  **Must NOT do**:
  - Jangan izinkan delete tanpa nonce
  - Jangan delete field yang tidak ada

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 6-8, 10-11)
  - **Blocks**: None
  - **Blocked By**: Task 8

  **Acceptance Criteria**:
  - [ ] Delete dengan nonce valid berfungsi
  - [ ] Delete tanpa nonce ditolak

  **QA Scenarios**:
  ```
  Scenario: Delete field berfungsi
    Tool: Playwright
    Preconditions: Ada field test_field
    Steps:
      1. Navigate ke Custom Fields page
      2. Klik delete pada test_field
      3. Verify pop-up confirmation muncul
      4. Confirm delete
      5. Verify field dihapus dari table
    Expected Result: Field deleted with confirmation
    Evidence: .sisyphus/evidence/task-9-delete.png
  ```

- [ ] 10. **Admin Table Display**

  **What to do**:
  - Loop melalui semua fields dari option
  - Generate HTML table row per field
  - Tampilkan: Label, Key, Type, Required, Display Options
  - Action buttons: Edit, Delete

  **Must NOT do**:
  - Jangan tampilkan field key di plaintext jika sensitif
  - Jangan gunakan echo langsung di dalam table generation

  **Recommended Agent Profile**:
  - **Category**: `quick`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 6-9, 11)
  - **Blocks**: None
  - **Blocked By**: Task 8

  **Acceptance Criteria**:
  - [ ] Table menampilkan semua field
  - [ ] Kolom sesuai dengan specification
  - [ ] Empty state jika belum ada field

  **QA Scenarios**:
  ```
  Scenario: Table display berfungsi
    Tool: Playwright
    Preconditions: Ada 2 field dibuat
    Steps:
      1. Navigate ke Custom Fields page
      2. Verify table dengan 2 row
      3. Verify kolom: Label, Key, Type, Required, Actions
      4. Verify empty state message jika tidak ada field
    Expected Result: Table displays correctly
    Evidence: .sisyphus/evidence/task-10-table.png
  ```

- [ ] 11. **AJAX Delete Handler**

  **What to do**:
  - Register AJAX handler untuk wp_ajax_tcf_delete_field
  - Verify nonce dan capability
  - Delete field dan return JSON response
  - Handle error cases

  **Must NOT do**:
  - Jangan izinkan AJAX tanpa nonce
  - Jangan return HTML di JSON response

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 2 (with Task 6-10)
  - **Blocks**: None
  - **Blocked By**: Task 9

  **Acceptance Criteria**:
  - [ ] AJAX call berhasil hapus field
  - [ ] Return format JSON yang benar
  - [ ] Error handling berfungsi

  **QA Scenarios**:
  ```
  Scenario: AJAX delete berfungsi
    Tool: Playwright
    Preconditions: Ada field test_field
    Steps:
      1. Klik delete button
      2. Confirm pada pop-up
      3. Verify AJAX call via network tab
      4. Verify JSON response success
      5. Verify field removed dari DOM
    Expected Result: AJAX works correctly
    Evidence: .sisyphus/evidence/task-11-ajax.json
  ```

- [ ] 12. **Register Form Hook Injection**

  **What to do**:
  - Hook ke 'tutor_register_form_fields'
  - Loop semua fields yang aktif untuk registration
  - Generate input HTML per field type
  - Include field attributes: required, placeholder, etc.

  **Must NOT do**:
  - Jangan hardcode fields
  - Jangan inject fields yang tidak aktif

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Task 13-16)
  - **Blocks**: Task 13
  - **Blocked By**: Task 10

  **References**:
  - Tutor LMS hook: tutor_register_form_fields

  **Acceptance Criteria**:
  - [ ] Fields muncul di registration page Tutor LMS
  - [ ] Setiap field type dirender dengan benar
  - [ ] Required attribute tercopy

  **QA Scenarios**:
  ```
  Scenario: Custom fields muncul di registration form
    Tool: Playwright
    Preconditions: Ada 2 custom field dibuat
    Steps:
      1. Navigate ke Tutor LMS registration page
      2. Verify custom fields terlihat
      3. Verify field labels benar
      4. Verify placeholder terlihat
      5. Verify required fields ada asterisk
    Expected Result: Fields visible in registration
    Evidence: .sisyphus/evidence/task-12-register.png
  ```

- [ ] 13. **Save Handler - Registration**

  **What to do**:
  - Hook ke 'tutor_save_extra_profile_fields'
  - Loop semua fields
  - Save ke wp_usermeta menggunakan meta_key
  - Handle multiple values untuk select

  **Must NOT do**:
  - Jangan simpan tanpa sanitization
  - Jangan overwrite fields yang tidak di-manage plugin

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Task 12, 14-16)
  - **Blocks**: Task 14
  - **Blocked By**: Task 12

  **References**:
  - WP update_user_meta()
  - Tutor LMS hook: tutor_save_extra_profile_fields

  **Acceptance Criteria**:
  - [ ] Data tersimpan ke wp_usermeta setelah registration
  - [ ] meta_key sesuai dengan specification

  **QA Scenarios**:
  ```
  Scenario: Data tersimpan setelah registration
    Tool: Playwright + Bash
    Preconditions: Field "nik" dibuat
    Steps:
      1. Register student baru dengan NIK="1234567890"
      2. Check wp_usermeta: SELECT * FROM wp_usermeta WHERE meta_key='nik'
      3. Verify meta_value="1234567890"
    Expected Result: Data saved correctly
    Evidence: .sisyphus/evidence/task-13-save.json
  ```

- [ ] 14. **Validation - Required Fields**

  **What to do**:
  - Hook ke 'tutor_registration_validation' atau filter
  - Validasi setiap field Required
  - Add error message sesuai dengan specification
  - Stop registration jika gagal

  **Must NOT do**:
  - Jangan izinkan submit tanpa validasi
  - Jangan tunjukkan error message yang暴露 implementation details

  **Recommended Agent Profile**:
  - **Category**: `unspecified-high`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Task 12-13, 15-16)
  - **Blocks**: None
  - **Blocked By**: Task 13

  **Acceptance Criteria**:
  - [ ] Required field kosong trigger error
  - [ ] Error messagecustom ditampilkan
  - [ ] Registration blocked jika validation gagal

  **QA Scenarios**:
  ```
  Scenario: Required validation berfungsi
    Tool: Playwright
    Preconditions: Field "nik" dibuat Required
    Steps:
      1. Navigate ke registration page
      2. Fill semua field kecuali NIK
      3. Submit form
      4. Verify error message muncul
      5. Verify registration tidak berhasil
    Expected Result: Validation blocks submission
    Evidence: .sisyphus/evidence/task-14-validation.png
  ```

- [ ] 15. **Profile Display Hook**

  **What to do**:
  - Hook ke 'tutor_show_extra_profile_fields'
  - Loop fields yang memiliki display option "profile"
  - Generate display HTML (label + value)
  - Retrieve data dari wp_usermeta

  **Must NOT do**:
  - Jangan tampilkan field yang tidak diaktifkan untuk display
  - Jangan tampilkan data sensitif jika tidak perlu

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Task 12-14, 16)
  - **Blocks**: None
  - **Blocked By**: Task 14

  **References**:
  - Tutor LMS hook: tutor_show_extra_profile_fields

  **Acceptance Criteria**:
  - [ ] Fields muncul di student profile
  - [ ] Display label dan value benar
  - [ ] Empty state jika data tidak ada

  **QA Scenarios**:
  ```
  Scenario: Fields ditampilkan di student profile
    Tool: Playwright
    Preconditions: Student dengan data profile
    Steps:
      1. Login sebagai student
      2. Navigate ke profile page
      3. Verify custom fields terlihat
      4. Verify label dan value benar
    Expected Result: Profile display works
    Evidence: .sisyphus/evidence/task-15-profile.png
  ```

- [ ] 16. **Edit Profile Hook**

  **What to do**:
  - Hook ke 'tutor_edit_profile_fields' (atau hook yang sesuai)
  - Generate editable input fields
  - Pre-fill dengan nilai dari database
  - Handle save seperti registration

  **Must NOT do**:
  - Jangan izinkan edit untuk readonly fields
  - Jangan update jika tidak ada perubahan

  **Recommended Agent Profile**:
  - **Category**: `visual-engineering`
  - **Skills**: []
  - **Skills Evaluated but Omitted**: none

  **Parallelization**:
  - **Can Run In Parallel**: YES
  - **Parallel Group**: Wave 3 (with Task 12-15)
  - **Blocks**: None
  - **Blocked By**: Task 15

  **Acceptance Criteria**:
  - [ ] Fields editable di profile page
  - [ ] Pre-filled values dari database
  - [ ] Save berfungsi setelah edit

  **QA Scenarios**:
  ```
  Scenario: Edit profile berfungsi
    Tool: Playwright
    Preconditions: Student dengan existing data
    Steps:
      1. Navigate ke edit profile page
      2. Verify fields pre-filled
      3. Ubah nilai field
      4. Save profile
      5. Verify nilai baru tersimpan
    Expected Result: Edit profile works
    Evidence: .sisyphus/evidence/task-16-edit.png
  ```

---

## Final Verification Wave

- [ ] F1. **Plan Compliance Audit** — `oracle`
  Read the plan end-to-end. Verify implementation matches all specifications: every feature in "Must Have" is implemented, "Must NOT Have" is not present.
  
  Output: `Must Have [N/N] | Must NOT Have [N/N] | VERDICT: APPROVE/REJECT`

- [ ] F2. **Code Quality Review** — `unspecified-high`
  Review all code for: sanitization, nonce verification, escaping, security issues, AI slop patterns (excessive comments, over-abstraction).
  
  Output: `Security [PASS/FAIL] | Patterns [N issues] | VERDICT`

- [ ] F3. **Manual Testing Scenarios** — `unspecified-high` (+ `playwright`)
  Execute all QA scenarios from tasks 1-16. Verify every deliverable works as expected.
  
  Output: `Scenarios [N/N pass] | Issues [N] | VERDICT`

- [ ] F4. **Scope Fidelity Check** — `deep`
  Verify no scope creep: all functionality in specification is implemented, nothing beyond specification is added.
  
  Output: `Scope [CLEAN/N issues] | VERDICT`

---

## Commit Strategy

- **1**: `Initial commit - Plugin scaffolding and class structure` - tutor-custom-registration-fields.php, includes/, admin/, frontend/
- **2**: `Add admin dashboard with CRUD operations` - admin page, form handler, table display
- **3**: `Add frontend integration with Tutor LMS` - registration form hooks, save handler, validation
- **4**: `Add profile display and edit functionality` - profile hooks, display/edit fields

---

## Success Criteria

### Verification Commands
```bash
# Plugin aktif dan functional
Visit wp-admin/plugins.php - Verify plugin active
Visit wp-admin/admin.php?page=tutor-custom-fields - Verify admin page loads

# Field creation
Create field via admin form
Verify field appears in table
Verify delete works with confirmation

# Frontend integration
Visit Tutor LMS registration page - Verify custom fields visible
Register with required field empty - Verify error shown
Register success - Verify data in wp_usermeta

# Profile
Visit student profile - Verify custom fields display
Edit profile - Verify fields editable and save works
```

### Final Checklist
- [ ] All "Must Have" present
- [ ] All "Must NOT Have" absent
- [ ] Input sanitization on all user inputs
- [ ] Nonce verification on all form submissions
- [ ] Server-side validation for required fields
- [ ] Delete confirmation pop-up works
- [ ] Custom fields appear in registration form
- [ ] Data saved to wp_usermeta correctly
- [ ] Fields display in student profile
- [ ] Fields editable in profile edit page