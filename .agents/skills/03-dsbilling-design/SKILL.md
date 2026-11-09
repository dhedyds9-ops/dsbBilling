---
name: dsbilling-design
description: >-
  UI/UX design system enforcement, wireframing, and component specification for dsBilling.
  Use when designing Web interfaces (Tailwind CSS, Livewire, Alpine.js) or Android Mobile
  screens (Material Design 3, BottomSheets, native XML layouts) ensuring visual parity.
---

# 03 dsbilling-design — UI/UX & Design System Engine

## 1. Overview & Purpose
`dsbilling-design` enforces visual integrity, responsive usability, and aesthetic parity across dsBilling's dual interfaces:
1. **Web Portals:** Blade templates styled with Tailwind CSS, Lucide icons, and Livewire/Alpine.js for NOC, Customer Portal, Backoffice, and Warehouse.
2. **Android Mobile:** Pure Native Google Material Design 3 (M3) layouts for Technician, Reseller, and Customer applications.

---

## 2. Activation Conditions
Activate this skill when:
- Designing new views, dialogs, forms, or interactive cards.
- Aligning Android mobile layouts with Material Design 3 standards.
- Preserving Customer Portal and NOC high-contrast operational dashboards.
- Refactoring legacy popups (e.g., eliminating `AlertDialog`) into modern `BottomSheetDialogFragment`.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Design System Token Alignment
1. **Color Palette Enforcement:**
   - Primary Brand Blue: `#0875BA` / `primary-600`
   - Deep Slate Header / Sidebar: `#0F172A` / `slate-900`
   - Success Emerald: `#0E9F6E` / `emerald-600`
   - Danger Rose / Alert: `#E02424` / `rose-600`
   - Warning Amber / Fiber Loss: `#B45309` / `amber-600`
2. **Typography & Geometry:**
   - Web: Inter / Sans-serif typography, rounded-xl (12-16px) cards, subtle borders (`border-slate-200 dark:border-slate-800`).
   - Android: Roboto / M3 typography, `MaterialCardView` with 14–16dp corner radius, 1dp stroke (`@color/genie_border`).

### Step 2: Portal-Specific Styling Rules
1. **NOC Monitoring:** High-contrast dark mode support, real-time status pulses, compact tabular data for router/OLT link states.
2. **Customer Portal:** Warm, accessible, mobile-responsive layout for invoices, payment statuses, and speed tests.
3. **Technician & Reseller Mobile:** High-sunlight outdoor visibility, large touch targets (minimum 48dp), bottom-oriented navigation (`BottomNavigationView` + `BottomSheetDialogFragment`), zero WebView dependencies for transactional forms.

### Step 3: Layout & Component Specification
1. Specify XML layout hierarchies for Android (`CoordinatorLayout`, `NestedScrollView`, `MaterialButton`, `TextInputEditText`).
2. Specify Blade component structures with Alpine.js reactivity and Livewire wire:model bindings.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Architectural specifications from `02-dsbilling-architect`.
- Existing UI audit benchmarks (`docs/audit/DSBILLING_STAFF_FINAL_PRODUCTION_VALIDATION.md`).

### Output Artifacts:
- **UI/UX Design Specification** (`docs/plans/DESIGN_<FEATURE_NAME>.md`):
  - Screen hierarchy and layout wireframe specs.
  - Color, elevation, and component token usage.
  - Interactive states (loading, disabled, empty, error, success).
  - Accessibility and outdoor readability considerations.

---

## 5. Quality Gates
- [ ] No WebView introduced for core native Android forms.
- [ ] Touch targets adhere to minimum 48x48dp guidelines.
- [ ] Color tokens match dsBilling brand guidelines.
- [ ] Customer Portal and NOC visual styling remain preserved and intact.

---

## 6. Safety Constraints
- Do not introduce heavy third-party UI libraries without explicit architectural approval.
- Do not override existing global Tailwind base styles without testing all portal layouts.

---

## 7. Handoff Requirements
Handoff output to `04-dsbilling-build` with:
1. Detailed XML layout specifications for Android.
2. Blade and Tailwind component mockups for Web.
