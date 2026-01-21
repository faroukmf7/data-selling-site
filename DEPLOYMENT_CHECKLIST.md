# FastData - Pre-Deployment Checklist

## Overview
This checklist contains all the steps you need to complete before deploying FastData to your web hosting provider.

---

## Phase 1: Pre-Deployment Setup (Before Upload)

### 1. Database Preparation
- [ ] Export your local database
  - Use PHPMyAdmin or command line: `mysqldump -u root -p fastdata > fastdata_backup.sql`
- [ ] Verify all tables exist:
  - [ ] users
  - [ ] products
  - [ ] orders
  - [ ] transactions
  - [ ] complaints
  - [ ] reset_tokens (if password reset is enabled)

### 2. Configuration Updates (`includes/config.php`)

Update the following values with your hosting provider's details:

```php
// Change these values:
define('DB_HOST', 'your_hosting_db_host');      // Usually localhost or your host's address
define('DB_NAME', 'your_database_name');         // Database name
define('DB_USER', 'your_db_username');          // Database user
define('DB_PASS', 'your_db_password');          // Database password
define('SITE_URL', 'https://yourdomain.com');   // Your production domain (HTTPS)
```

- [ ] Update database credentials
- [ ] Update SITE_URL to your production domain
- [ ] Ensure SITE_URL uses HTTPS (if SSL is available)
- [ ] Remove `/fastdata` from SITE_URL if it's in the root directory
- [ ] Verify config.php is not publicly accessible

### 3. Security Configuration

#### Remove Debug Files
- [ ] Delete or secure `phpinfo_test.php`
- [ ] Remove any test files or comments with sensitive information

#### PHP Error Handling
- [ ] Create `.htaccess` file to disable error display:
```apache
php_flag display_errors Off
php_flag log_errors On
```

#### Sensitive Files Protection
- [ ] Verify `includes/config.php` is not directly accessible via browser
- [ ] Add to `.htaccess`:
```apache
<FilesMatch "\.php$">
    Deny from all
</FilesMatch>
<Files "index.php">
    Allow from all
</Files>
```

### 4. Directory Permissions Setup
- [ ] Create `images/` directory if it doesn't exist
- [ ] Set permissions:
  - [ ] images/: 755 (drwxr-xr-x)
  - [ ] All PHP files: 644 (-rw-r--r--)
  - [ ] All directories: 755 (drwxr-xr-x)

### 5. Payment Gateway Configuration

#### Paystack Setup
- [ ] Verify you have Paystack merchant account
- [ ] Get your Paystack API keys:
  - [ ] Public Key (test/live)
  - [ ] Secret Key (test/live)
- [ ] Check all payment-related files for hardcoded test keys
  - [ ] `paystack_payment.php`
  - [ ] `verify_payment.php`
  - [ ] `guest_payment.php`
  - [ ] Any other payment files

#### Update Paystack Keys
If keys are hardcoded in files, update them:
- [ ] Replace test keys with live keys
- [ ] Ensure callback URLs are set correctly in Paystack dashboard
  - Callback URL: `https://yourdomain.com/verify_payment.php`

### 6. Email Configuration (`includes/functions.php`)

Update the sendEmail function:
```php
function sendEmail($recipient_email, $recipient_name, $subject, $body) {
    $from_email = 'noreply@yourdomain.com';     // Update this
    $from_name = 'FastData Support';             // Update if needed
    // ... rest of function
}
```

- [ ] Update "from" email address to your domain email
- [ ] Test email sending after deployment
- [ ] Verify your hosting allows mail() function (or use SMTP)

### 7. SSL/HTTPS Setup
- [ ] Check if your hosting provides free SSL (Let's Encrypt)
- [ ] Enable SSL certificate
- [ ] Update all hardcoded URLs to use HTTPS
- [ ] Update SITE_URL in config.php to use `https://`
- [ ] Force HTTPS redirect in `.htaccess`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

---

## Phase 2: Upload to Hosting

### 8. File Upload
- [ ] Connect to hosting via FTP/SFTP
- [ ] Upload all files **except**:
  - [ ] `.git/` directory (if using Git)
  - [ ] `node_modules/` (if any)
  - [ ] `DEPLOYMENT_CHECKLIST.md` (optional)
  - [ ] Any `.env` files with sensitive data
- [ ] Verify all files uploaded correctly
- [ ] Check file permissions are preserved

### 9. Database Migration
- [ ] Create database on hosting server
- [ ] Create database user with proper permissions
- [ ] Import your backup:
  - Via PHPMyAdmin: Import from file
  - Via command line: `mysql -u user -p database_name < fastdata_backup.sql`
- [ ] Verify all tables imported successfully
- [ ] Test database connection

### 10. Directory Structure Verification
After upload, verify:
- [ ] `/css/` directory exists with all CSS files
- [ ] `/js/` directory exists with all JS files
- [ ] `/includes/` directory exists with all config files
- [ ] `/admin/` directory exists with all admin files
- [ ] `/database/` directory exists with migration files
- [ ] `/images/` directory exists and is writable
- [ ] `index.php` is accessible at root

---

## Phase 3: Post-Deployment Testing

### 11. Basic Functionality Tests

#### User Authentication
- [ ] Test user registration
  - [ ] Create new account
  - [ ] Verify email validation (if implemented)
  - [ ] Test login with new account
- [ ] Test login with existing account
- [ ] Test password reset functionality
- [ ] Test logout functionality

#### Product & Shopping
- [ ] Browse products page
- [ ] View product details
- [ ] Search for products
- [ ] Test filters

#### Transactions & Payments
- [ ] Test adding funds to wallet
- [ ] Test Paystack payment flow (use test cards first)
- [ ] Verify transaction is recorded in database
- [ ] Test viewing all transactions
- [ ] Test transaction filters and search

#### Complaints System
- [ ] Submit a complaint
- [ ] View complaint list
- [ ] Filter complaints
- [ ] Search complaints
- [ ] Verify complaint appears in admin panel

#### Admin Features (if applicable)
- [ ] Login as admin
- [ ] View admin dashboard
- [ ] Access products management
- [ ] Access orders management
- [ ] Access complaints management
- [ ] Test updating complaint status
- [ ] Verify all admin filters work

### 12. Performance & Security Tests

#### Performance
- [ ] Check page load times
- [ ] Verify images load correctly
- [ ] Test on mobile device
- [ ] Test all pages on different screen sizes
- [ ] Check CSS and JS files load properly

#### Security
- [ ] Verify no sensitive files are publicly accessible
- [ ] Test that error messages don't expose system info
- [ ] Verify HTTPS is working
- [ ] Test CSRF protection (if implemented)
- [ ] Verify SQL injection protection (parameterized queries)

#### Database
- [ ] Verify database connection is working
- [ ] Check all tables are accessible
- [ ] Verify user data is persisted correctly
- [ ] Test transaction logging

### 13. Error Handling
- [ ] Check logs for any errors
  - [ ] Check `/error_log` file
  - [ ] Check server error logs via hosting panel
- [ ] Fix any errors found
- [ ] Verify error display is disabled for users

### 14. Backup
- [ ] Create a backup of the uploaded files
- [ ] Create a backup of the database
- [ ] Store backups securely outside of web root

---

## Phase 4: Post-Deployment Configuration

### 15. Analytics & Monitoring (Optional)
- [ ] Set up Google Analytics if needed
- [ ] Set up error monitoring (Sentry, etc.) if needed
- [ ] Set up uptime monitoring

### 16. Documentation
- [ ] Update README.md with production domain
- [ ] Document any custom configurations
- [ ] Create admin user guide if needed
- [ ] Document backup procedures

### 17. Go-Live Preparation
- [ ] Notify stakeholders of launch
- [ ] Create user documentation
- [ ] Plan marketing/announcement if applicable
- [ ] Set up support email if needed

---

## Important Reminders

⚠️ **Critical Security Points:**
- Never commit `config.php` with real credentials to git
- Always use HTTPS in production
- Keep sensitive API keys private
- Regularly backup your database
- Monitor error logs regularly
- Keep PHP and all software updated

⚠️ **Testing:**
- Use Paystack test keys first to verify payment flow
- Test all user flows before announcing to users
- Test on multiple devices and browsers
- Verify all email functions work

⚠️ **Maintenance:**
- Set up regular database backups
- Monitor server disk space
- Check error logs regularly
- Keep an eye on transaction success rates

---

## Rollback Plan

If something goes wrong:
1. Restore from backup files via FTP
2. Restore database from backup: `mysql -u user -p database < backup.sql`
3. Check error logs to identify the issue
4. Fix and redeploy

---

## Post-Launch Monitoring

### Week 1
- [ ] Monitor error logs daily
- [ ] Verify all payments are processing correctly
- [ ] Check database is growing as expected
- [ ] Monitor server performance

### Weekly
- [ ] Backup database
- [ ] Review error logs
- [ ] Check for any user-reported issues
- [ ] Monitor transaction success rates

### Monthly
- [ ] Create full system backup
- [ ] Review analytics
- [ ] Check server security
- [ ] Update any outdated packages

---

## Support & Help

If you encounter issues:
1. Check error logs first
2. Verify database connection
3. Test API endpoints
4. Review hosting provider's documentation
5. Contact hosting support if infrastructure issue

---

## Deployment Completion Checklist

- [ ] All configuration files updated
- [ ] All files uploaded
- [ ] Database migrated and verified
- [ ] All testing completed
- [ ] No errors in logs
- [ ] HTTPS enabled
- [ ] Backups created
- [ ] Monitoring set up
- [ ] Documentation updated
- [ ] Go-live approval from team

---

**Date Deployed:** ________________

**Deployed By:** ________________

**Domain:** ________________

**Notes:** 
_____________________________________________________________________________

_____________________________________________________________________________

---

**Good luck with your deployment! 🚀**
