# Guest Purchase Flow Diagram

## Complete User Journey

```
┌─────────────────────────────────────────────────────────────────────────┐
│                        HOME PAGE (index.php)                            │
│                     Non-logged-in User Sees:                            │
│  ┌──────────────────────────────────────────────────────────────────┐  │
│  │ "Quick Purchase" Section                                         │  │
│  │ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐                 │  │
│  │ │ MTN Data    │ │ Airtel Data  │ │ Telecel Data│                 │  │
│  │ │ [Product]   │ │ [Product]   │ │ [Product]   │                 │  │
│  │ └─────────────┘ └─────────────┘ └─────────────┘                 │  │
│  │                                                                  │  │
│  │ ┌────────────────────────────────────────────────────────────┐ │  │
│  │ │         Guest Option (if NOT logged in)                   │ │  │
│  │ │  [Continue as Guest]         [Login/Register]            │ │  │
│  │ └────────────────────────────────────────────────────────────┘ │  │
│  └──────────────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────────────┘
                                    │
                          ┌─────────┴──────────┐
                          │                    │
                          ▼                    ▼
        ┌─────────────────────────────┐    ┌─────────────────┐
        │  Continue as Guest Button   │    │  Products Page  │
        │  OR Product Direct Click    │    │  (products.php) │
        └─────────────┬───────────────┘    └─────────┬───────┘
                      │                              │
                      │                 ┌────────────┴────────────┐
                      │                 │                         │
                      └────────────┬────────────────┐     ┌────────▼──────┐
                                   │                │     │               │
                                   ▼                │     │  Not Logged?  │
                    ┌────────────────────────┐     │     │               │
                    │   PRODUCTS PAGE        │     │     └───────┬───────┘
                    │   (products.php)       │     │             │
                    │                        │     │    [Guest Checkout]
                    │ Each Product Shows:    │     │    Button Appears
                    │ ┌────────────────────┐ │     │             │
                    │ │ Product Details    │ │     │             │
                    │ │ Price              │ │     │             │
                    │ │ [Buy Now] button   │ │     │             │
                    │ │ [Guest Checkout]   │─┼─────┴─────────────┘
                    │ │ (if NOT logged in) │ │
                    │ └────────────────────┘ │
                    └────────────────────────┘
                                   │
                                   │ User clicks [Guest Checkout]
                                   │
                                   ▼
        ┌─────────────────────────────────────────────────────────┐
        │         GUEST CHECKOUT PAGE                             │
        │         (guest_checkout.php)                            │
        │                                                         │
        │  LEFT SIDE - FORM:              RIGHT SIDE - SUMMARY: │
        │  ┌─────────────────────────┐   ┌──────────────────┐   │
        │  │ Guest Phone Number  [  ] │   │ Order Summary    │   │
        │  │ Guest Email         [  ] │   │ ────────────────│   │
        │  │ Recipient Number    [  ] │   │ Product: ...     │   │
        │  │ Data Amount (GB)    [  ] │   │ Network: ...     │   │
        │  │ Exam Type           [▼] │   │ Category: ...    │   │
        │  │                         │   │ Total: GHS ...   │   │
        │  │ [Continue Payment]      │   │                  │   │
        │  │ [Back to Products]      │   │ Benefits:        │   │
        │  │                         │   │ • No account     │   │
        │  └─────────────────────────┘   │ • Quick checkout │   │
        │                                 │ • Email receipt  │   │
        │                                 └──────────────────┘   │
        └─────────────────────────────────────────────────────────┘
                                   │
                          ┌────────┴────────┐
                          │                 │
              ┌───────────▼────────┐  ┌─────▼────────────┐
              │ Validation Failed  │  │ Validation OK    │
              └─────────┬──────────┘  └─────┬────────────┘
                        │                   │
                        │                   ▼
                   [Error Message]  ┌──────────────────────┐
                        │           │   GUEST PAYMENT PAGE  │
                        │           │   (guest_payment.php) │
                        │           │                      │
                        │           │ Payment Details:     │
                        │           │ ──────────────────  │
                        │           │ Order: ...           │
                        │           │ Recipient: ...       │
                        │           │ Email: ...           │
                        │           │ Amount: GHS ...      │
                        │           │ Reference: GUEST_... │
                        │           │                      │
                        │           │ [Pay with Paystack]  │
                        │           │ [Cancel]             │
                        │           └────────┬─────────────┘
                        │                    │
                        │     ┌──────────────┼──────────────┐
                        │     │              │              │
                        │     ▼              ▼              ▼
                        │ ┌──────────┐  ┌──────────┐  ┌──────────┐
                        │ │ Cancelled│  │ Pending  │  │ Success  │
                        │ │          │  │          │  │          │
                        │ └────┬─────┘  └────┬─────┘  └────┬─────┘
                        │      │             │             │
                        │      │             │             │
                    [Retry]    │      [Retry if needed] [Verify]
                        │      │             │             │
                        └──────┴─────────────┴─────────────┘
                                   │
                                   │
                                   ▼
        ┌─────────────────────────────────────────────────┐
        │    PAYSTACK INLINE PAYMENT POPUP                │
        │    ┌───────────────────────────────────────┐    │
        │    │   FastData Payment                    │    │
        │    │                                       │    │
        │    │   Amount: GHS [amount]                │    │
        │    │   Email: [guest_email]                │    │
        │    │                                       │    │
        │    │   [Enter Card Details]                │    │
        │    │                                       │    │
        │    │   [4111 1111 1111 1111] [Test Card]  │    │
        │    │   [MM/YY] [CVC]                       │    │
        │    │                                       │    │
        │    │   [Pay Now]  [Cancel]                 │    │
        │    └───────────────────────────────────────┘    │
        └─────────────────────────────────────────────────┘
                                   │
                    ┌──────────────┴──────────────┐
                    │                             │
                    ▼ Payment Successful          ▼ Payment Failed
        ┌─────────────────────────────┐  ┌─────────────────────┐
        │  VERIFY PAYMENT PAGE        │  │ Payment Failed      │
        │  (guest_verify_payment.php) │  │                     │
        │                             │  │ [Retry Payment]     │
        │  Server-side Verification:  │  │ [Cancel]            │
        │  1. Verify with Paystack    │  │                     │
        │  2. Check amount            │  └─────────────────────┘
        │  3. Record transaction      │           │
        │  4. Send email              │           │
        │  5. Clear session           │      [Back to Products]
        │                             │
        │  Database Update:           │
        │  INSERT guest_transactions  │
        │  {                          │
        │    reference: GUEST_...     │
        │    guest_email: ...         │
        │    guest_phone: ...         │
        │    recipient: ...           │
        │    product_id: ...          │
        │    amount: ...              │
        │    status: 'completed'      │
        │    created_at: NOW()        │
        │  }                          │
        │                             │
        │  Email Sent To Guest        │
        │                             │
        └────────────┬────────────────┘
                     │
                     ▼
        ┌─────────────────────────────────────┐
        │    SUCCESS PAGE                     │
        │    (guest_verify_payment.php)       │
        │                                     │
        │  ✓ Payment Successful!              │
        │                                     │
        │  Order Details:                     │
        │  Reference: GUEST_...               │
        │  Product: ...                       │
        │  Amount Paid: GHS ...               │
        │  Recipient: ...                     │
        │                                     │
        │  Email sent to: guest@email.com     │
        │  Service will be delivered soon     │
        │                                     │
        │  [Back to Home]                     │
        │  [Buy More]                         │
        │                                     │
        │  Contact Support for Issues         │
        └─────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
    [Home Page]            [Products Page]
    Back to Shopping      Purchase Again
```

## Database Transaction Flow

```
┌──────────────────────────────────────────────────────────┐
│         GUEST PURCHASES DATABASE STORAGE                 │
│                                                          │
│  guest_transactions TABLE:                              │
│  ┌────────────────────────────────────────────────────┐ │
│  │ id          │ Auto-increment ID                    │ │
│  │ reference   │ GUEST_1705512345_654321 (unique)    │ │
│  │ guest_email │ user@example.com                     │ │
│  │ guest_phone │ 024XXXXXXX                           │ │
│  │ recipient   │ 027YYYYYYY                           │ │
│  │ product_id  │ 5 (FK to products)                   │ │
│  │ amount      │ 25.50 (GHS)                          │ │
│  │ product_name│ 2GB Data Bundle                      │ │
│  │ network     │ MTN                                  │ │
│  │ category    │ data                                 │ │
│  │ data_amount │ 2 (GB)                               │ │
│  │ exam_type   │ NULL (or WAEC/NECO/BECE)            │ │
│  │ status      │ 'completed'                          │ │
│  │ created_at  │ 2026-01-17 14:30:45                 │ │
│  │ updated_at  │ 2026-01-17 14:30:45                 │ │
│  └────────────────────────────────────────────────────┘ │
│                                                          │
│  Indexes for Performance:                               │
│  • idx_reference (for payment verification)             │
│  • idx_guest_email (for email lookups)                  │
│  • idx_guest_phone (for phone lookups)                  │
│  • idx_status (for transaction filtering)               │
│  • idx_created_at (for date range queries)              │
└──────────────────────────────────────────────────────────┘
```

## Session Management

```
SESSION FLOW:
┌────────────────────────────────────────────┐
│ Start: User clicks Guest Checkout          │
└────────────────────────────────────────────┘
                    │
                    ▼
    ┌───────────────────────────────────┐
    │ Session Created: store guest form │
    │ $_SESSION['current_order'] = [    │
    │   'is_guest' => true,             │
    │   'guest_email' => ...,           │
    │   'guest_phone' => ...,           │
    │   'product_id' => ...,            │
    │   ... other fields                │
    │ ]                                 │
    └───────────────────────────────────┘
                    │
                    ▼
    ┌───────────────────────────────────┐
    │ Payment Processing:               │
    │ $_SESSION['payment_reference'] =  │
    │   'GUEST_...'                     │
    │ $_SESSION['payment_amount'] =     │
    │   [amount in kobo]                │
    └───────────────────────────────────┘
                    │
                    ▼
    ┌───────────────────────────────────┐
    │ Verification Success:             │
    │ Session cleared                   │
    │ unset($_SESSION['current_order']) │
    │ unset($_SESSION['payment_...'])   │
    └───────────────────────────────────┘
```

## Data Flow Diagram

```
GUEST INPUT → VALIDATION → ORDER CREATION → PAYMENT PROCESSING → CONFIRMATION
    │              │              │                    │               │
    ├─ Guest Email ├─ Email Valid ├─ Store Order   ├─ Paystack API ├─ Email Sent
    ├─ Guest Phone ├─ Phone Valid ├─ Product Check ├─ Verify Amt   ├─ DB Insert
    ├─ Recipient   ├─ Recipient   ├─ Calc Total    ├─ Authorize    ├─ Success Page
    ├─ Amount      ├─ Amount Range├─ Create Session├─ Charge Card  └─ Redirect
    └─ Product     └─ Product Exist└─ Redirect      └─ Record Txn
```

---

**Guest Purchase System Architecture**
Created: January 17, 2026
