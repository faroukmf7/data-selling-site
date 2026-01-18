# Guest Purchase Implementation Checklist

## Database Setup
- [ ] Run migration: `database/migration_add_guest_transactions.sql`
- [ ] Verify `guest_transactions` table created
- [ ] Test INSERT permissions on guest_transactions

## File Verification
- [ ] `guest_checkout.php` - Created and accessible
- [ ] `guest_payment.php` - Created and accessible
- [ ] `guest_verify_payment.php` - Created and accessible
- [ ] `index.php` - Updated with guest options
- [ ] `products.php` - Updated with guest checkout buttons
- [ ] `css/style.css` - Guest styles added
- [ ] `includes/functions.php` - No changes needed (already has sanitize, redirect functions)

## Configuration Checks
- [ ] PAYSTACK_PUBLIC_KEY is set in config.php
- [ ] PAYSTACK_SECRET_KEY is set in config.php
- [ ] Mail function is enabled on server
- [ ] Email sending works (test with existing features)

## Frontend Testing - Home Page
- [ ] Not logged in user sees "Continue as Guest" button
- [ ] Logged in user does NOT see guest option
- [ ] "Continue as Guest" button links to products.php
- [ ] Styling looks good on mobile and desktop

## Frontend Testing - Products Page
- [ ] Each product has "Guest Checkout" button
- [ ] Buttons only show for non-logged-in users
- [ ] "Guest Checkout" button links to guest_checkout.php with product_id
- [ ] Logged in users see normal "Buy Now" button

## Frontend Testing - Guest Checkout
- [ ] Page loads with product details
- [ ] Form fields display correctly:
  - [ ] Guest Phone Number
  - [ ] Guest Email Address
  - [ ] Recipient Phone Number
  - [ ] Data amount (for flexible products)
  - [ ] Exam type (for exam pins)
- [ ] Form validation works:
  - [ ] Email must be valid
  - [ ] Phone numbers required
  - [ ] Amount validation for flexible plans
- [ ] Order summary displays correctly
- [ ] Price calculation correct (for flexible plans)
- [ ] Submit button works
- [ ] Back button returns to products

## Frontend Testing - Guest Payment
- [ ] Payment details display correctly
- [ ] Guest email shows correctly
- [ ] Amount shows correctly
- [ ] Reference number shown
- [ ] Paystack button appears
- [ ] Paystack popup opens on button click
- [ ] Redirect to verification after payment

## Frontend Testing - Payment Verification
- [ ] Success page displays after valid payment
- [ ] Transaction reference shows correctly
- [ ] Order details display correctly
- [ ] "Back to Home" button works
- [ ] "Buy More" button works
- [ ] Success message appears
- [ ] Support contact info shows

## Backend Testing - Payment Processing
- [ ] Paystack verification API works
- [ ] Amount verification passes
- [ ] Transaction recorded in guest_transactions table
- [ ] Transaction status set to 'completed'
- [ ] Email sent to guest address
- [ ] Session cleared after payment
- [ ] Failed payments handled gracefully

## Backend Testing - Database
- [ ] Guest transaction records created
- [ ] All fields populated correctly:
  - [ ] reference (GUEST_ prefix)
  - [ ] guest_email
  - [ ] guest_phone
  - [ ] recipient_number
  - [ ] product_id
  - [ ] amount
  - [ ] product_name
  - [ ] network
  - [ ] category
  - [ ] data_amount (for flexible)
  - [ ] exam_type (for exam pins)
  - [ ] status
  - [ ] created_at

## Email Testing
- [ ] Confirmation email received at guest email
- [ ] Email contains:
  - [ ] Transaction reference
  - [ ] Product name
  - [ ] Recipient number
  - [ ] Amount paid
  - [ ] Expected delivery info
  - [ ] Support contact

## Security Testing
- [ ] Email validation prevents invalid emails
- [ ] Phone validation requires proper format
- [ ] Session cleared after successful payment
- [ ] Payment amounts verified server-side
- [ ] SQL injection prevention (use of prepared statements)
- [ ] XSS prevention (use of htmlspecialchars)

## Responsive Design Testing
- [ ] Guest checkout form on mobile (stacks correctly)
- [ ] Order summary on mobile
- [ ] Buttons sized appropriately for touch
- [ ] Text readable on small screens
- [ ] Forms accessible on mobile browsers
- [ ] Paystack works on mobile

## Cross-Browser Testing
- [ ] Chrome
- [ ] Firefox
- [ ] Safari
- [ ] Edge
- [ ] Mobile browsers

## Performance Testing
- [ ] Guest checkout page loads quickly
- [ ] No JavaScript errors in console
- [ ] No SQL errors in error log
- [ ] Database queries optimized
- [ ] CSS loads correctly

## Integration Testing
- [ ] Guest purchases don't affect logged-in users
- [ ] Logged-in checkout still works
- [ ] Admin panel still works
- [ ] Dashboard still works
- [ ] No conflicts with existing code

## Deployment Checklist
- [ ] All files uploaded to production server
- [ ] Migration applied to production database
- [ ] PAYSTACK keys verified for correct environment
- [ ] Email configuration tested in production
- [ ] Test payment made and verified
- [ ] Error logs checked
- [ ] Success page confirmation received

## Documentation
- [ ] README.md updated (optional)
- [ ] GUEST_PURCHASE_GUIDE.md reviewed
- [ ] GUEST_PURCHASE_SETUP.md reviewed
- [ ] Team trained on feature
- [ ] Support documentation updated

## Post-Launch
- [ ] Monitor guest transactions regularly
- [ ] Track conversion rates
- [ ] Monitor error logs for issues
- [ ] Collect user feedback
- [ ] Plan feature enhancements
- [ ] Update admin dashboard if needed

## Notes
- Guest transactions are separate from user accounts
- Guests receive order confirmation via email only
- No account created for guests
- All guest data stored in guest_transactions table
- Can query guest data for marketing/analytics

## Rollback Plan (if needed)
1. Delete guest_checkout.php
2. Delete guest_payment.php
3. Delete guest_verify_payment.php
4. Revert index.php changes
5. Revert products.php changes
6. Revert css/style.css changes
7. Keep database migration (won't hurt)

---

**Last Updated**: January 17, 2026
**Status**: Ready for Testing
