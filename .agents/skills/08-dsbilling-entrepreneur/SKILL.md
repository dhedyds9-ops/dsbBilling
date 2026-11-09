---
name: dsbilling-entrepreneur
description: >-
  ISP business logic, financial ledger modeling, billing cycles, and revenue sharing for dsBilling.
  Use when designing or auditing subscription packages, invoice lifecycles, payment gateway
  integrations, reseller commissions, inventory valuation, or payroll calculations.
---

# 08 dsbilling-entrepreneur — ISP Commercial & Financial Engine

## 1. Overview & Purpose
`dsbilling-entrepreneur` governs the commercial, billing, and accounting operations of dsBilling. It ensures the mathematical accuracy and regulatory compliance of subscription recurring billing, prorated calculations, multi-tier reseller revenue sharing, payment gateway settlements, and inventory cost accounting.

---

## 2. Activation Conditions
Activate this skill when:
- Designing or modifying service profiles (`service_profiles`), bandwidth tiers, and pricing.
- Implementing invoice generation routines, prorated billing, or automated suspension rules.
- Configuring payment gateway webhooks (Midtrans, Xendit, Tripay, manual bank transfer).
- Calculating reseller commission splits and deposit balances.
- Managing inventory cost calculations (FIFO/Average) and technician payroll deductions.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Billing Engine & Invoice Lifecycle
1. Inspect recurring billing schedules and invoice generation jobs (`GenerateInvoicesJob`).
2. Verify invoice statuses: `unpaid`, `paid`, `cancelled`, `overdue`.
3. Ensure automated payment allocation creates a balancing `cash_transactions` and `journal_entries` record.
4. Enforce isolation: ensure invoice queries are scoped by `branch_id` and, where applicable, `reseller_id`.

### Step 2: Reseller Revenue Sharing Model
1. Calculate revenue shares based on `reseller_prices` configured on `service_profiles`.
2. Compute gross subscription revenue minus reseller cost of goods sold (COGS).
3. Ensure reseller deposit balances cannot go negative unless an authorized overdraft limit is explicitly defined.
4. Record transactions in `revenue_sharings` and `revenue_share_batches`.

### Step 3: Payment Gateway & Settlement Reconciliation
1. Verify signature validation on all incoming payment gateway webhooks.
2. Prevent double-spending or duplicate settlement processing using idempotent transaction keys.
3. Automatically trigger customer service reactivation upon verified payment.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Commercial requirements or pricing rules.
- Existing ledger services: `FinancialTransactionService.php`, `GeneralLedgerService.php`.
- Schema references: `invoices`, `payments`, `cash_transactions`, `revenue_sharings`.

### Output Artifacts:
- **Commercial & Financial Specification** (`docs/plans/BIZ_<MODULE_NAME>.md`):
  - Pricing formula and revenue share distribution breakdown.
  - Double-entry accounting impact (Debit Account / Credit Account).
  - Idempotency and reconciliation verification protocol.

---

## 5. Quality Gates
- [ ] Financial calculations use integer cents/rupiah or `bcmath` to avoid floating-point rounding errors.
- [ ] Every revenue or expense event generates double-entry balanced journal entries.
- [ ] Reseller balance mutations are atomic and wrapped in database transactions (`DB::transaction`).
- [ ] Webhook handlers reject unsigned or invalid payment gateway payloads.

---

## 6. Safety Constraints
- Never execute arbitrary credit adjustments directly via raw SQL without an audit trail.
- Do not bypass webhook signature validation, even in local sandbox testing.

---

## 7. Handoff Requirements
Handoff output to `05-dsbilling-backend` for financial service and database transaction implementation.
