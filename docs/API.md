# SmartNest REST API Documentation (Version 1.0)

Base URL: `http://<your-host>/apartments/public/api/v1`

All responses follow a consistent, standardized envelope:

### Successful Response Format
```json
{
  "success": true,
  "message": "Operation completed successfully.",
  "data": { ... }
}
```

### Error Response Format
```json
{
  "success": false,
  "message": "Validation failed / Error explanation",
  "errors": {
    "field_name": ["Specific validation error"]
  }
}
```

---

## 1. Authentication & Profile

### `POST /auth/register`
Register a new Tenant, Student Tenant, or Property Owner.

**Request Body (JSON):**
```json
{
  "name": "Juma Bakari",
  "email": "juma@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "tenant", // "tenant" or "owner"
  "phone": "+255712345678",
  "whatsapp": "+255712345678",
  "is_student": true,
  "student_reg_number": "BCS/2026/012",
  "institution_id": 1
}
```

**Response (201 Created):**
```json
{
  "success": true,
  "message": "Registration successful.",
  "data": {
    "user": {
      "id": 14,
      "name": "Juma Bakari",
      "email": "juma@example.com",
      "role": "tenant",
      "phone": "+255712345678"
    },
    "token": "1|sanctum_plain_text_token..."
  }
}
```

---

### `POST /auth/login`
Authenticate user with email and password.

**Request Body (JSON):**
```json
{
  "email": "tenant@smartnest.com",
  "password": "password",
  "device_name": "Flutter Mobile App"
}
```

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Login successful.",
  "data": {
    "user": {
      "id": 2,
      "name": "Tenant User",
      "email": "tenant@smartnest.com",
      "role": "tenant"
    },
    "token": "2|sanctum_plain_text_token..."
  }
}
```

---

### `GET /auth/profile`
Retrieve authenticated user profile with role-specific details.  
**Headers:** `Authorization: Bearer <token>`

---

### `PUT /auth/profile` or `POST /auth/profile` (Multipart with Avatar)
Update personal information, phone numbers, or upload profile avatar.

---

### `POST /auth/fcm-token`
Register device FCM token for push notifications.

---

### `POST /auth/logout`
Revoke active Sanctum token and invalidate mobile session.

---

## 2. Public Property Marketplace

### `GET /properties`
Search and filter property listings.

**Query Parameters:**
- `search`: Keyword for property name, description, address
- `city`: Filter by city (e.g. `Dar es Salaam`)
- `rent_structure`: `per_bed`, `per_room`, `entire_property`, `mixed`
- `min_price`: Minimum rental price
- `max_price`: Maximum rental price
- `institution_id`: Proximity to college or university
- `amenities`: Comma-separated amenity IDs (e.g. `1,2,5`)
- `page`: Pagination page number

---

### `GET /properties/{id}`
Retrieve complete property details, amenities, building layout, available rooms, and bed configurations.

---

### `GET /locations/cities`
List of all active property cities.

---

### `GET /institutions`
List of registered universities and colleges.

---

### `GET /amenities`
List of all amenities (WiFi, AC, Generator, Security, Water, etc.).

---

## 3. Tenant Endpoints (`/api/v1/tenant/*`)
*Requires `role: tenant` Bearer Token.*

### `GET /tenant/dashboard`
Returns tenant active contracts, pending applications, unpaid invoices, and active maintenance tickets.

### `GET /tenant/applications`
List all booking applications submitted by the tenant.

### `POST /tenant/applications`
Submit a new booking application.  
*Guarded against double booking & race conditions using DB lock transactions.*

**Request Body:**
```json
{
  "property_id": 1,
  "room_id": 3,
  "bed_id": 5,
  "start_date": "2026-10-01",
  "end_date": "2027-04-01",
  "tenant_notes": "First semester student move-in."
}
```

### `DELETE /tenant/applications/{id}`
Cancel a pending booking application.

### `GET /tenant/contracts`
List all tenancy agreements and contracts.

### `GET /tenant/contracts/{id}`
Retrieve full contract details, terms, rules, and checkout inspection records.

### `POST /tenant/contracts/{id}/accept`
Digitally sign and accept tenancy contract terms.

### `GET /tenant/contracts/{id}/pdf`
Download PDF version of tenancy contract.

### `GET /tenant/invoices`
List rent, deposit, and utility invoices.

### `GET /tenant/payments`
List payment records and verification statuses.

### `POST /tenant/payments` (Multipart Form)
Upload bank slip or mobile money transaction screenshot.

**Form Fields:**
- `contract_id`: ID of contract
- `invoice_id`: (Optional) ID of invoice
- `amount`: Numeric amount (e.g. `150000`)
- `payment_method`: `mpesa`, `tigopesa`, `airtelmoney`, `bank_transfer`, `cash`
- `reference`: (Optional) Transaction reference code
- `receipt`: Image file (JPG/PNG/PDF)

### `GET /tenant/maintenance`
List reported maintenance tickets with status and resolution notes.

### `POST /tenant/maintenance` (Multipart Form)
Report a new maintenance issue with photo attachments.

**Form Fields:**
- `property_id`: ID of property
- `title`: Short summary of the defect
- `description`: Detailed description
- `priority`: `low`, `medium`, `high`, `emergency`
- `images[]`: Array of photo files (up to 5 images)

### `GET /tenant/assets`
List assigned room inventory and appliances.

### `POST /tenant/assets/{id}/confirm`
Confirm handover condition of assigned room asset upon move-in.

---

## 4. Property Owner Endpoints (`/api/v1/owner/*`)
*Requires `role: owner` or `role: super_admin` Bearer Token.*

### `GET /owner/dashboard`
Aggregated overview: Total properties, occupancy rate %, pending applications count, unverified payments count.

### `GET /owner/properties`
List all properties owned by the authenticated user.

### `POST /owner/properties` (Multipart Form)
Publish a new property listing with images and amenities.

### `GET /owner/properties/{id}/hierarchy`
Fetch complete structure: Buildings -> Floors -> Rooms -> Beds.

### `POST /owner/buildings`
Add a building to a property.

### `POST /owner/floors`
Add a floor to a building.

### `POST /owner/rooms`
Add a room to a floor with capacity and rental rate.

### `POST /owner/beds`
Add a bed to a room with bed number and rate.

### `GET /owner/applications`
List prospective tenant booking applications. Filter by `status` or `property_id`.

### `POST /owner/applications/{id}/status`
Approve or reject a booking application.  
*Approving automatically creates a digital contract and notifies the tenant.*

**Request Body:**
```json
{
  "status": "approved", // or "rejected"
  "owner_notes": "Welcome to Alpha Plaza!"
}
```

### `GET /owner/contracts`
List all contracts across owner properties.

### `GET /owner/payments`
List payment submissions awaiting verification.

### `POST /owner/payments/{id}/verify`
Verify or reject a tenant payment receipt.

**Request Body:**
```json
{
  "status": "paid", // or "rejected"
  "rejection_reason": null
}
```

### `GET /owner/maintenance`
List maintenance tickets across properties.

### `POST /owner/maintenance/{id}/assign`
Assign maintenance ticket to a staff member or contractor.

### `GET /owner/assets`
List registered property inventory assets.

### `POST /owner/assets`
Register new asset (furniture, appliance, electronics).

### `GET /owner/reports`
Financial summaries, monthly collection breakdown, occupancy statistics, and maintenance turnaround times.

---

## 5. Property Manager Endpoints (`/api/v1/manager/*`)
*Requires `role: property_manager` Bearer Token (strictly scoped to assigned properties).*

### `GET /manager/dashboard`
Operational daily dashboard: Today's arrivals, today's departures, active tickets.

### `POST /manager/checkin`
Execute digital tenant check-in, issue keys, verify deposit, and record initial room condition.

**Request Body:**
```json
{
  "contract_id": 1,
  "check_in_date": "2026-09-21",
  "deposit_received": 50000,
  "room_condition_notes": "Room in excellent condition.",
  "key_handover_details": { "keys": "Key #102" }
}
```

### `POST /manager/checkout`
Execute digital tenant check-out, inspect inventory, calculate damage charges, refund security deposit, and release room/bed availability.

**Request Body:**
```json
{
  "contract_id": 1,
  "check_out_date": "2026-09-21",
  "damage_charges": 10000,
  "deposit_refunded": 40000,
  "outstanding_balance_settled": true,
  "final_inspection_notes": "Minor wall scuff deduction applied."
}
```

### `GET /manager/maintenance`
List assigned maintenance tickets.

### `POST /manager/maintenance/{id}/status`
Update maintenance ticket progress (`in_progress`, `completed`, `cancelled`) with resolution notes.

---

## 6. Notifications (`/api/v1/notifications/*`)

### `GET /notifications`
List all user notifications.

### `GET /notifications/unread-count`
Get count of unread notifications for badge counter.

### `POST /notifications/{id}/read`
Mark specific notification as read.

### `POST /notifications/mark-all-read`
Mark all notifications as read.
