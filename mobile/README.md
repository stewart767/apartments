# SmartNest Mobile Application (Flutter)

A multi-platform property, hostel, and apartment rental management application built with **Flutter**, connecting to the Laravel REST API (`/api/v1/`).

---

## 🌟 Architecture & Highlights

- **Shared Backend Business Logic:** 100% of double-booking prevention, availability calculations, contract generation, damage deductions, and payment verification are executed by Laravel services.
- **State Management:** Provider (`MultiProvider` & `ChangeNotifier`).
- **Network Layer:** Dio HTTP client with automatic Bearer Token injection and centralized exception handling.
- **Secure Storage:** `flutter_secure_storage` for encrypted token storage and `shared_preferences` for cached user session.
- **Design System:** Material 3 with customized Emerald/Indigo palettes, Inter/Outfit typography (`google_fonts`), Dark & Light mode support.

---

## 📱 Role-Based Mobile Flows

### 1. Tenant & Student Flow
- **Marketplace & Discovery:** Instant search, city filter, rental structure filter (hostel/bed, apartment/room, entire property).
- **Property Detail:** Image gallery, amenities breakdown, real-time room and bed availability.
- **Booking Application:** Room/bed picker, start & end date selector, price preview, notes.
- **Digital Contracts:** View active & pending contracts, terms & rules, digital signature/acceptance.
- **Invoices & Payments:** Outstanding invoice tracking, payment receipt photo upload (M-Pesa, Tigo Pesa, Airtel Money, Bank Wire, Cash).
- **Maintenance Tickets:** Issue reporting with camera/gallery photos, priority selector, status timeline.
- **Room Inventory Handover:** Digital inventory baseline condition inspection and confirmation.

### 2. Property Owner Flow
- **Analytics Dashboard:** Real-time occupancy rate %, total properties, pending applications, unverified payments.
- **Property Management:** Listings manager, unit/room/bed counts, rental structure configurations.
- **Booking Application Approvals:** Review applicants with student details, approve (auto-generates digital contract) or reject.
- **Payment Verification:** Review payment receipts, confirm as Paid or reject with reason.
- **Maintenance Management:** Assign contractor or staff to reported defects.
- **Financial & Occupancy Reports:** Real-time collection metrics, revenue breakdowns, maintenance resolution times.

### 3. Property Manager / Receptionist Flow
- **Operations Dashboard:** Today's arrivals and departures.
- **Digital Check-in:** Deposit verification, key handover details, room baseline condition logging, contract activation.
- **Digital Check-out:** Move-out condition comparison, damage charges deduction, deposit refunding, instant unit availability release.
- **Maintenance Triage:** Update ticket status to In Progress or Completed with resolution notes.

---

## 🚀 Getting Started & Setup

### Prerequisites
- [Flutter SDK](https://flutter.dev/docs/get-started/install) (version `>=3.10.0`)
- Android Studio / Xcode

### 1. Configure API Base URL
The API URL is defined in `lib/core/constants.dart`:
- **Android Emulator:** `http://10.0.2.2/apartments/public/api/v1` (Default)
- **iOS Simulator / Web:** `http://localhost/apartments/public/api/v1`
- **Physical Device:** Update with your local machine IP (e.g. `http://192.168.1.50/apartments/public/api/v1`) or production domain.

### 2. Install Dependencies
```bash
cd mobile
flutter pub get
```

### 3. Run the App
```bash
# Run on connected device or emulator
flutter run
```

---

## 🧪 Demo Credentials

| Role | Email | Password |
|---|---|---|
| **Tenant** | `tenant@smartnest.com` | `password` |
| **Owner** | `owner@smartnest.com` | `password` |
| **Property Manager** | `manager@smartnest.com` | `password` |
