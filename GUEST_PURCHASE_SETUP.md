# Guest Purchase Feature - Quick Setup

## Step 1: Apply Database Migration
Run the migration to create the guest_transactions table:
```bash
mysql -u your_username -p your_database < database/migration_add_guest_transactions.sql
```

## Step 2: Files Added/Modified

### New Files (3):
✅ `guest_checkout.php` - Guest information collection and product selection
✅ `guest_payment.php` - Paystack payment integration for guests
✅ `guest_verify_payment.php` - Payment verification and confirmation

### Modified Files (3):
✅ `index.php` - Added "Continue as Guest" button to home page
✅ `products.php` - Added "Guest Checkout" button to each product
✅ `css/style.css` - Added comprehensive styling for guest checkout

### Database Migration:
✅ `database/migration_add_guest_transactions.sql` - Guest transactions table

### Documentation:
✅ `GUEST_PURCHASE_GUIDE.md` - Complete implementation and testing guide

## Step 3: Test the Feature

### Quick Test:
1. Open your site in an incognito/private window
2. Go to home page
3. Click "Continue as Guest"
4. Select MTN Data
5. Enter guest phone: 024XXXXXXX
6. Enter guest email: test@example.com
7. Enter recipient: 024YYYYYYY
8. Enter amount: 1
9. Click "Continue to Payment"
10. Use Paystack test card: 4111111111111111

### Expected Flow:
- Guest fills in details ✅
- Order summary displays ✅
- Paystack payment popup appears ✅
- Payment verified ✅
- Success page shown ✅
- Email sent to guest ✅
- Transaction recorded in database ✅

## Key Features

✨ **No Account Required** - Users can purchase without registration
✨ **Same Payment Gateway** - Uses Paystack like logged-in users
✨ **Email Confirmation** - Guests receive order confirmation emails
✨ **Full Tracking** - All guest transactions recorded in database
✨ **Mobile Friendly** - Responsive design works on all devices
✨ **Flexible & Fixed Products** - Works with both data plans and fixed products
✨ **Exam Pin Support** - Guests can select exam types (WAEC, NECO, BECE)

## Database Table

The `guest_transactions` table tracks:
- Guest contact info (email, phone)
- Order details (product, amount, recipient)
- Payment reference
- Transaction status
- Timestamps

## Security

✅ Email validation
✅ Phone validation  
✅ Amount verification
✅ Paystack API integration
✅ Session cleanup after payment
✅ Server-side payment verification

## Next Steps

1. **Apply the database migration** - Create guest_transactions table
2. **Test the complete flow** - Use instructions in GUEST_PURCHASE_GUIDE.md
3. **Check email configuration** - Ensure confirmation emails are sent
4. **Monitor transactions** - Track guest purchases in admin panel

## Common Queries

### View all guest purchases:
```sql
SELECT * FROM guest_transactions ORDER BY created_at DESC;
```

### View guest revenue by product:
```sql
SELECT product_name, COUNT(*) as count, SUM(amount) as total
FROM guest_transactions WHERE status = 'completed'
GROUP BY product_name;
```

### Find repeat guests:
```sql
SELECT guest_email, COUNT(*) as purchases, SUM(amount) as total_spent
FROM guest_transactions WHERE status = 'completed'
GROUP BY guest_email HAVING purchases > 1;
```

---

**Status**: ✅ Ready to Deploy
**Last Updated**: January 17, 2026
