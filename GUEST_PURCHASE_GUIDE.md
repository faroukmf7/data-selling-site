# Guest Purchase Feature - Implementation Guide

## Overview
The Guest Purchase feature allows customers to buy data, airtime, and exam pins without creating an account. This feature increases conversion rates and provides a frictionless shopping experience.

## What's Been Added

### New Files Created

#### 1. **guest_checkout.php**
- Handles guest customer information collection
- Validates guest email and phone number
- Calculates order totals based on product selection
- Supports flexible data plans and fixed products
- Stores order details in session for payment processing

**Key Features:**
- Email validation
- Phone number validation
- Product-specific form fields (exam type for exam pins)
- Order summary display
- Real-time price calculation for flexible plans

#### 2. **guest_payment.php**
- Displays payment details before Paystack integration
- Shows guest order summary
- Integrates Paystack for payment processing
- Generates unique payment references for guest orders
- Uses GUEST_ prefix for guest payment references

**Key Features:**
- Secure payment gateway
- Guest-friendly interface
- Clear payment information display
- Paystack inline payment popup

#### 3. **guest_verify_payment.php**
- Verifies payment with Paystack API
- Records guest transaction in database
- Sends confirmation email to guest
- Displays order confirmation
- Clears session data after successful payment

**Key Features:**
- Amount verification
- Transaction recording in guest_transactions table
- Automated email confirmation
- Success page with order details

### Database Changes

#### New Table: guest_transactions
```sql
CREATE TABLE `guest_transactions` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `reference` VARCHAR(100) NOT NULL UNIQUE,
  `guest_email` VARCHAR(100) NOT NULL,
  `guest_phone` VARCHAR(20) NOT NULL,
  `recipient_number` VARCHAR(20) NOT NULL,
  `product_id` INT,
  `amount` DECIMAL(10, 2) NOT NULL,
  `product_name` VARCHAR(255),
  `network` VARCHAR(50),
  `category` VARCHAR(50),
  `data_amount` DECIMAL(10, 2),
  `exam_type` VARCHAR(100),
  `status` ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE SET NULL,
  INDEX idx_reference (reference),
  INDEX idx_guest_email (guest_email),
  INDEX idx_guest_phone (guest_phone),
  INDEX idx_status (status),
  INDEX idx_created_at (created_at)
);
```

**To apply the migration:**
```bash
# Import the migration file in your database
mysql -u username -p database_name < database/migration_add_guest_transactions.sql
```

### Modified Files

#### 1. **index.php**
- Added visible "Continue as Guest" button in Quick Purchase section
- Non-logged-in users now see guest purchase option
- "Continue as Guest" links to products.php
- Clear call-to-action for guest shoppers

#### 2. **products.php**
- Added "Guest Checkout" button for each product
- Guest checkout buttons visible only to non-logged-in users
- Links to guest_checkout.php with product_id parameter
- Removed login requirement from purchase forms
- Simplified form submission for logged-in users

### CSS Styling

Added comprehensive CSS styles in `css/style.css` for:
- Guest checkout form styling
- Order summary display
- Payment information layout
- Success page design
- Responsive design for mobile devices
- Guest option buttons and styling
- Info boxes with security information

## User Flow

### For Guest Users:

1. **Home Page (index.php)**
   - User sees "Quick Purchase" section
   - User clicks "Continue as Guest" or "Buy Now" on any product

2. **Products Page (products.php)**
   - User sees "Guest Checkout" button for each product
   - User clicks "Guest Checkout" for desired product

3. **Guest Checkout (guest_checkout.php)**
   - User enters phone number and email
   - User enters recipient phone number
   - User enters quantity/amount for flexible products
   - User sees order summary
   - User clicks "Continue to Payment"

4. **Guest Payment (guest_payment.php)**
   - User reviews order summary
   - User clicks "Pay with Paystack"
   - Paystack popup appears
   - User completes payment

5. **Payment Verification (guest_verify_payment.php)**
   - System verifies payment with Paystack
   - Transaction recorded in guest_transactions table
   - Confirmation email sent to guest
   - Success page displayed with order reference

### For Logged-In Users:
- Users continue to use existing checkout flow
- No changes to user experience
- Can still make purchases as usual

## Database Tracking

### Guest Transactions Table Fields:

| Field | Purpose |
|-------|---------|
| `reference` | Unique payment reference (GUEST_timestamp_random) |
| `guest_email` | Customer email for confirmation |
| `guest_phone` | Customer phone for contact |
| `recipient_number` | Phone number receiving the service |
| `product_id` | Product purchased |
| `amount` | Transaction amount in GHS |
| `product_name` | Name of product purchased |
| `network` | Network (MTN, Airtel-Tigo, Telecel) |
| `category` | Category (data, airtime, exam_pin) |
| `data_amount` | For flexible plans - amount purchased |
| `exam_type` | For exam pins - exam type (WAEC, NECO, BECE) |
| `status` | Transaction status (pending, completed, failed, refunded) |
| `created_at` | Timestamp of purchase |
| `updated_at` | Last update timestamp |

## API Integration

### Paystack Integration
- Uses Paystack Public Key for frontend
- Uses Paystack Secret Key for API verification
- Payment references prefixed with `GUEST_` for easy identification
- Amount verified server-side to prevent tampering

## Email Notifications

Guests receive confirmation emails with:
- Transaction reference number
- Product details
- Recipient phone number
- Amount paid
- Expected delivery information
- Support contact information

## Security Considerations

1. **Email Validation**: All guest emails are validated
2. **Phone Validation**: Guest phone numbers are validated
3. **Amount Verification**: Server-side verification of payment amounts
4. **Session Management**: Session data cleared after successful payment
5. **Paystack Integration**: Uses secure API endpoints

## Testing Guest Purchases

### Test Scenario 1: Flexible Data Plan
1. Go to home page
2. Click "Continue as Guest"
3. Select MTN Data or similar flexible product
4. Enter details:
   - Guest Phone: Your phone number
   - Email: test@example.com
   - Recipient: Different phone number
   - Amount: 2 (GB)
5. Click "Continue to Payment"
6. Use Paystack test card: 4111111111111111

### Test Scenario 2: Fixed Product (Airtime)
1. Go to products page
2. Find airtime product
3. Click "Guest Checkout"
4. Enter details and proceed to payment

### Test Scenario 3: Exam PIN
1. Go to products page
2. Find exam PIN product
3. Click "Guest Checkout"
4. Select exam type (WAEC/NECO/BECE)
5. Complete payment

## Admin Analytics

To track guest purchases, admins can query:

```sql
-- Total guest transactions
SELECT COUNT(*) as total_transactions FROM guest_transactions;

-- Guest revenue
SELECT SUM(amount) as total_revenue FROM guest_transactions WHERE status = 'completed';

-- Popular products among guests
SELECT product_name, COUNT(*) as purchases, SUM(amount) as revenue
FROM guest_transactions
WHERE status = 'completed'
GROUP BY product_name
ORDER BY purchases DESC;

-- Guest emails for marketing
SELECT DISTINCT guest_email FROM guest_transactions WHERE status = 'completed';
```

## Troubleshooting

### Issue: Guest checkout button not appearing
- Ensure user is NOT logged in
- Clear browser cookies/session
- Check if browser JavaScript is enabled

### Issue: Payment verification fails
- Verify Paystack keys are correctly configured in config.php
- Check that Paystack API is accessible
- Verify payment amount matches order amount

### Issue: Email not sent
- Check email configuration in includes/functions.php
- Verify server has mail() function enabled
- Check server error logs

## Future Enhancements

1. **Guest Account Conversion**: Offer guest users option to create account with their order
2. **Guest Analytics Dashboard**: Admin panel to view guest purchase statistics
3. **Guest Support Tickets**: Allow guests to track orders without account
4. **Repeat Guest Offers**: Track repeat guests and offer loyalty discounts
5. **Guest Newsletter Signup**: Collect email addresses for marketing
6. **SMS Notifications**: Send delivery notifications via SMS
7. **Guest Order History**: Create temporary order history page with email/phone verification

## Support

For issues or questions about the guest purchase feature, refer to the migration file and ensure:
1. Guest transactions table is created
2. Paystack keys are configured
3. Email function is working
4. Database user has INSERT permissions on guest_transactions table
