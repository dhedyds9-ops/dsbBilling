---
name: dsbilling-build
description: >-
  Frontend engineering, Livewire components, and Android Native UI implementation for dsBilling.
  Use when writing Blade views, Livewire components, Tailwind CSS styling, or Android Kotlin
  Activities, BottomSheets, ViewBinding, and Material Design 3 layouts.
---

# 04 dsbilling-build — Frontend & Mobile UI Implementation Engine

## 1. Overview & Purpose
`dsbilling-build` handles the client-facing implementation for dsBilling across both the Web frontend (Laravel Livewire 3/4, Blade, Tailwind CSS) and Android Mobile application (Kotlin, Android ViewBinding, Material Components).

---

## 2. Activation Conditions
Activate this skill when:
- Writing or updating Blade templates, Livewire components, or JavaScript assets.
- Implementing Android XML layouts, Kotlin Activities, Fragments, or BottomSheetDialogs.
- Integrating native device features (Camera barcode scanning, GPS coordinates, Canvas signature).
- Compiling frontend assets via Vite or building Android APKs via Gradle.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Web Frontend Implementation (`D:\dsBilling`)
1. Create or edit Blade templates in `resources/views/`.
2. Implement Livewire components in `app/Livewire/` with strictly typed properties and action methods.
3. Apply Tailwind CSS utility classes adhering to dsBilling tokens (`bg-slate-900`, `text-primary-600`, etc.).
4. Use Alpine.js for lightweight UI state (dropdowns, tabs, modals).

### Step 2: Android Native UI Implementation (`D:\dsbilling-mobile`)
1. Build native XML layout files in `app/src/main/res/layout/` using `ViewBinding`.
2. Implement Activities and BottomSheets in `ui/` and `ui/dialog/`.
3. Adhere to Kotlin Coroutines (`lifecycleScope.launch`) and Dispatchers (`Dispatchers.Main` / `Dispatchers.IO`) for background rendering.
4. Integrate native input widgets:
   - Barcode scanning via camera intent or ZXing integration.
   - Canvas signature view via `SignatureView.kt`.
   - GPS coordinate acquisition via `FusedLocationProviderClient`.

### Step 3: Frontend Compilation & Validation
1. For Web: Validate syntax and run asset bundling: `npm run build`.
2. For Mobile: Verify layout rendering and resource ID consistency.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- UI/UX specifications from `03-dsbilling-design`.
- API endpoints contract from `02-dsbilling-architect`.

### Output Artifacts:
- Implemented Blade / Livewire files in `resources/views/` and `app/Livewire/`.
- Implemented Kotlin and XML files in `D:\dsbilling-mobile\app\src\main\`.
- Compilation confirmation log.

---

## 5. Quality Gates
- [ ] Web assets compile cleanly with `npm run build` without Vite/Tailwind errors.
- [ ] No raw string hardcoding in Android layouts (use `@string/` resource references).
- [ ] ViewBinding null-safety strictly respected in Kotlin classes.
- [ ] Zero layout clipping on compact mobile screens (320dp - 360dp width).

---

## 6. Safety Constraints
- Do not introduce memory leaks in Android (nullify ViewBinding references in `onDestroyView`).
- Do not modify production compiled assets manually.
- Do not add unapproved node_modules or gradle dependencies.

---

## 7. Handoff Requirements
Handoff output to `06-dsbilling-test` for UI, E2E, and component test verification.
