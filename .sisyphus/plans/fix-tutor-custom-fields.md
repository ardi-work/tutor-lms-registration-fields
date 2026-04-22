# Plan: Fix Tutor LMS Custom Fields Not Displaying

## TL;DR

> **Quick Summary**: Fix hook yang salah - Tutor LMS 3.x menggunakan `tutor_student_reg_form_end` bukan `tutor_register_form_fields`
> 
> **Deliverables**: Custom fields muncul di registration form
> - Hook diperbaiki
> - Fields akan tampil

---

## Context

### Problem
Custom fields tidak muncul di halaman register meskipun:
- Tutor LMS 3.9 sudah terinstall
- Fields sudah dibuat di admin panel (Tutor LMS → Custom Fields)
- Tidak ada error PHP

### Root Cause
Plugin menggunakan hook `tutor_register_form_fields` yang sudah deprecated/tidak ada di Tutor LMS 3.x. Berdasarkan dokumentasi resmi Themeum, hook yang benar adalah:
- `tutor_student_reg_form_end` - untuk menampilkan field di student registration
- `tutor_student_reg_form_start` - alternatif (field di awal form)

---

## Work Objectives

### Core Objective
Custom fields muncul di Tutor LMS student registration form

### Definition of Done
- [ ] Custom fields muncul di `[tutor_student_registration_form]`
- [ ] Fields bisa di-submit dan tersimpan

---

## TODOs

- [ ] 1. Fix hook registration di frontend-hooks.php

  **What to do**:
  - Ganti `add_action('tutor_register_form_fields', 'tcf_render_registration_fields')` 
  - Menjadi `add_action('tutor_student_reg_form_end', 'tcf_render_registration_fields')`
  - Tambah juga `add_action('tutor_student_reg_form_start', 'tcf_render_registration_fields')` untuk cover semua case

  **Acceptance Criteria**:
  - [ ] Buka halaman register, custom fields muncul

- [ ] 2. Fix tutor() helper function

  **What to do**:
  - Function `tutor()` di baris 188-192 selalu return `false`
  - Ini bisa cause issue dengan `tutor()->utils->add_flash()`
  - Comment out atau hapus function ini jika Tutor LMS sudah menyediakan

  **Acceptance Criteria**:
  - [ ] Tidak ada error PHP terkait tutor() function

- [ ] 3. Test Submit dan Simpan

  **What to do**:
  - Test registrasi student dengan custom fields
  - Verifikasi data tersimpan ke user meta

  **QA Scenarios**:
  ```
  Scenario: Register student dengan custom field
    Tool: Browser (manual)
    Steps:
      1. Buka halaman register
      2. Isi form register + custom field
      3. Klik register
      4. Login sebagai student baru
      5. Cek profile untuk custom field value
    Expected Result: Custom field value tersimpan dan tampil di profile
  ```

---

## Commit Strategy

- **1**: `fix: use correct Tutor LMS 3.x hooks for registration form`
  - frontend/frontend-hooks.php

---

## Success Criteria

- Custom fields muncul di registration form Tutor LMS 3.9
- Data tersimpan dengan benar ke user meta
