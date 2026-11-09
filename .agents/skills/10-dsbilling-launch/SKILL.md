---
name: dsbilling-launch
description: >-
  Release readiness, Android APK/AAB packaging, ProGuard verification, and preflight audit for dsBilling.
  Use when compiling signed Android release bundles, verifying Keystore signatures (v1/v2),
  configuring ProGuard/R8 rules, auditing production assets, and ensuring release compliance.
---

# 10 dsbilling-launch — Release Readiness & Packaging Engine

## 1. Overview & Purpose
`dsbilling-launch` validates application build artifacts, release integrity, and compliance across both the Android Native application (`D:\dsbilling-mobile`) and Web production bundles (`D:\dsBilling`). It ensures zero uncompressed assets, verified cryptographic signatures, and full Google Play / Enterprise release readiness.

---

## 2. Activation Conditions
Activate this skill when:
- Compiling release artifacts: Android APK (`Genieacs.apk`) or App Bundle (`Genieacs.aab`).
- Verifying Android cryptographic signing keys and schemas (APK Signature Scheme v1 & v2).
- Auditing ProGuard / R8 obfuscation and minification rules (`app/proguard-rules.pro`).
- Validating version codes (`versionCode`) and version names (`versionName`).
- Running production release preflight audits.

---

## 3. Step-by-Step Execution Instructions

### Step 1: Android Release Packaging & Verification
1. Inspect `app/build.gradle.kts` release configuration:
   - Verify `compileSdk = 35`, `targetSdk = 35`, `minSdk = 24`.
   - Verify signing config points to release keystore via `local.properties`.
   - Verify `enableV1Signing = true` and `enableV2Signing = true`.
2. Verify ProGuard rules:
   - Ensure model classes (`DsBillingModels.kt`, `DeviceItem.kt`) are preserved from aggressive reflection stripping (`-keep class com.genieacs.client.data.model.** { *; }`).
   - Preserve Coroutines and Retrofit/OkHttp annotations.
3. Validate output artifacts:
   - Check APK file size (target: under 10MB; benchmark ~5.7MB).
   - Check AAB file size (target: under 8MB; benchmark ~5.1MB).

### Step 2: Web Production Asset Optimization
1. Run production Vite build:
   ```bash
   npm run build
   ```
2. Verify production asset manifest in `public/build/manifest.json`.
3. Check for oversized bundles or missing vendor chunks.

### Step 3: Production Security Preflight Audit
1. Confirm `android:usesCleartextTraffic="false"` in `AndroidManifest.xml`.
2. Confirm no test credentials or development URLs (`localhost`, `10.0.2.2`) exist in production release code.
3. Verify Sanctum token encrypted storage using `KeyStoreManager.kt`.

---

## 4. Required Input & Output Artifacts

### Input Artifacts:
- Tested codebase from `06-dsbilling-test`.
- Code review clearance from `07-dsbilling-diff`.
- Release keystore (`genieacs-release.jks` / `local.properties`).

### Output Artifacts:
- Signed Android Release APK (`Genieacs.apk`) or AAB (`Genieacs.aab`).
- **Release Preflight Report** (`docs/audit/RELEASE_PREFLIGHT_v<VERSION>.md`):
  - Checksum (SHA-256) of generated release artifacts.
  - Signing verification output (`apksigner verify`).
  - Production manifest checklist.

---

## 5. Quality Gates
- [ ] APK and AAB signatures verified with v1 and v2 schemes.
- [ ] ProGuard/R8 does not strip essential JSON serialization models.
- [ ] Web production assets compile without warnings or missing CSS files.
- [ ] Cleartext HTTP traffic blocked in release builds.

---

## 6. Safety Constraints
- Never commit private keystores (`.jks`), keystore passwords, or alias secrets to Git version control.
- Never deploy an unsigned debug build to production environments.

---

## 7. Handoff Requirements
Handoff output to `11-dsbilling-deploy` for production deployment and server rollout.
