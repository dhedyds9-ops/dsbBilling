---
name: dsbilling-brand
description: >-
  White-labeling, tenant branding, document templates, and customer touchpoint design for dsBilling.
  Use when customizing ISP brand identities, tenant logos, invoice PDF designs, thermal
  receipt formats, hotspot voucher templates, and automated WhatsApp/Telegram message templates.
---

# 09 dsbilling-brand — White-Labeling & Tenant Brand Identity Engine

## 1. Overview & Purpose
`dsbilling-brand` manages multi-tenant white-labeling and visual brand identity across all customer-facing touchpoints in dsBilling. It ensures each tenant ISP can project a distinct, professional corporate identity through dynamic logos, invoice templates, Wi-Fi voucher printouts, and automated messaging.

---

## 2. Activation Conditions
Activate this skill when:
- Designing or customizing PDF invoice layouts (`barryvdh/laravel-dompdf`).
- Creating or editing thermal print receipt templates (58mm/80mm ESC/POS format).
- Authoring hotspot voucher cards and physical scratch-card printing grids.
- Setting up tenant-specific branding assets (app icons, login page hero banners, portal color themes).
- Formatting outbound notification templates for WhatsApp, Telegram, or SMS.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Multi-Tenant Asset Sourcing
1. Ensure brand assets are stored in tenant-isolated directories:
   `storage/app/public/tenants/{tenant_slug}/branding/`
2. Configure dynamic logo URL resolution with fallback to default dsBilling assets.
3. Validate uploaded image formats (PNG, SVG, WebP) and size limits (max 2MB).

### Step 2: Document Template Authoring
1. **Invoice PDF (`resources/views/pdf/invoice.blade.php`):**
   - Clean, professional corporate header with tenant logo, tax ID (NPWP), and company contact info.
   - Itemized service breakdown, due date, payment instructions (Bank transfer / QRIS).
   - Consistent typography using DomPDF-compatible font sets (DejaVu Sans, Helvetica).
2. **Thermal Receipt (`resources/views/receipts/thermal.blade.php`):**
   - Monospace layout formatted for 58mm / 80mm ESC/POS thermal printers.
   - Customer payment receipt, invoice number, cashier/agent name, timestamp.
3. **Hotspot Voucher Grid:**
   - Visual cards containing username, password, QR code for auto-login, validity period, and price.

### Step 3: Notification Message Formatting
1. Author WhatsApp and Telegram message templates with variables (`{{customer_name}}`, `{{invoice_amount}}`, `{{due_date}}`, `{{payment_link}}`).
2. Include polite, clear Indonesian ISP customer service phrasing.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Tenant brand guide or customization requirements.
- Helper scripts: `app/Helpers/voucher_template_helpers.php`.
- PDF engine: `barryvdh/laravel-dompdf`.

### Output Artifacts:
- Blade templates in `resources/views/pdf/`, `resources/views/receipts/`, `resources/views/vouchers/`.
- Notification formatting classes in `app/Services/Notifications/`.
- Visual preview or sample PDF generation verification.

---

## 5. Quality Gates
- [ ] PDF templates render cleanly without memory exhaustion or page-overflow glitches.
- [ ] Thermal receipt formatting wraps properly within 32-column (58mm) and 48-column (80mm) boundaries.
- [ ] No hardcoded company names; all branding fields resolve dynamically from tenant settings.
- [ ] Storage paths strictly respect tenant isolation.

---

## 6. Safety Constraints
- Do not store uncompressed large images (e.g., > 10MB) in public tenant directories.
- Avoid external CSS or fonts in PDF templates that require outbound internet access at render time.

---

## 7. Handoff Requirements
Handoff output to `04-dsbilling-build` or `05-dsbilling-backend` for integration into billing and notification queues.
