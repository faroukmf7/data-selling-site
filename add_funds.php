<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Funds - FastData</title>
    <link rel="stylesheet" href="css/style.css">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <style>
        .nav-brand {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0px;
            width: 100%;
            border: none;
            margin: 0px 0px 10px 0px;
            border-radius: 0px;
            background-color: #2b2d42;
        }
    </style>
</head>

<body>

    <!-- Header section -->
    <div class="header">
        <div class="title">Flashdata</div>
        <button class="menu-toggle" id="menuToggle">
            <i class='bx bx-menu' style="font-size: 0.8em;"></i>
        </button>
        <ul class="nav-menu" id="navMenu">
            <li style=" display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            width: 100%;
            border: none;
            margin: 0px 0px 10px 0px;
            border-radius: 0px;
            background-color: #2b2d42;">
                <div>
                    <div><i class='bx bx-fast-forward' style="font-size: 1.5em;"></i></div>
                    <div style="font-size: 1.8em;">FlashData</div>
                </div>
                <div><i class='bx bx-x' style="font-size: 1.5em;"></i></div>
            </li>
                            <li><i class='bx bxs-dashboard nav-icon'></i><a href="dashboard.php">Dashboard</a></li>
                <li><i class='bx bx-receipt nav-icon'></i><a href="all_transactions.php">Transactions</a></li>
                        <li><i class='bx bx-home nav-icon'></i><a href="index.php">Home</a></li>
            <li><i class='bx bx-cabinet nav-icon'></i><a href="products.php">Products</a></li>
                            <li><i class='bx bx-message-square-error nav-icon'></i><a href="complaints.php">Complaints</a></li>
                                                <li class="nav-contact" style="margin: 20px 20px; padding: 10px 10px"><i class='bx bxl-whatsapp nav-icon' style="background-color: hsla(120, 89%, 45%, 0.60);"></i><a href="#">Join our community</a></li>
                                                <li> <i class='bx bxl-graphql nav-icon'></i><a href="admin/">Admin</a></li>
                                <li><i class='bx bx-log-out-circle nav-icon'></i> <a href="logout.php">Logout</a></li>
                    </ul>
    </div>

    <!-- Display flash messages -->
    
<div class="content">
    <div class="add-funds-container">
        <div class="funds-card">
            <h1>Add Funds to Wallet</h1>

            <div class="current-balance">
                <h3>Current Balance</h3>
                <div class="balance-display">GHS 561.67</div>
            </div>

            
            
            <form method="POST" action="">
                <div class="form-group">
                    <label for="amount">Amount to Add (GHS)</label>
                    <div class="amount-input-wrapper">
                        <input type="number" id="amount" name="amount" step="0.01" min="1" max="10000" required placeholder="Enter amount" autofocus>
                        <small>Min: GHS 1 | Max: GHS 10,000</small>
                    </div>
                </div>

                <!-- Quick amount buttons -->
                <div class="quick-amounts">
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 50">GHS 50</button>
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 100">GHS 100</button>
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 200">GHS 200</button>
                    <button type="button" class="quick-btn" onclick="document.getElementById('amount').value = 500">GHS 500</button>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 20px;">
                    <i class='bx bx-credit-card'></i> Add Funds with Paystack
                </button>
            </form>

            <div class="funds-info">
                <h4>Payment Information</h4>
                <ul>
                    <li>Funds will be added immediately after successful payment</li>
                    <li>No additional fees will be charged</li>
                    <li>You can use your balance to purchase data/airtime instantly</li>
                    <li>All transactions are secure and encrypted</li>
                </ul>
            </div>

            <div class="funds-actions">
                <a href="dashboard.php" class="btn-secondary">
                    <i class='bx bx-arrow-back'></i> Back to Dashboard
                </a>
                <a href="products.php" class="btn-secondary">
                    <i class='bx bx-shopping-bag'></i> View Products
                </a>
            </div>
        </div>

        <!-- Recent Wallet Transactions -->
        <div class="funds-transactions">
            <h3>Recent Transactions</h3>
                            <table class="transactions-list">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                                                <tr>
                            <td>Jan 16, 20:17</td>
                            <td>GHS 50.00</td>
                            <td><span class="status-badge status-successful">Successful</span></td>
                        </tr>
                                                <tr>
                            <td>Jan 16, 20:16</td>
                            <td>GHS 500.00</td>
                            <td><span class="status-badge status-successful">Successful</span></td>
                        </tr>
                                            </tbody>
                </table>
                    </div>
    </div>
</div>

<style>
.add-funds-container {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
}

.funds-card {
    background: white;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}

.funds-card h1 {
    margin-top: 0;
    margin-bottom: 30px;
    text-align: center;
    color: #2b2d42;
}

.current-balance {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    text-align: center;
}

.current-balance h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    opacity: 0.9;
}

.balance-display {
    font-size: 32px;
    font-weight: bold;
}

.amount-input-wrapper {
    display: flex;
    flex-direction: column;
}

.amount-input-wrapper small {
    margin-top: 5px;
    color: #666;
    font-size: 12px;
}

.quick-amounts {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 10px;
    margin: 20px 0;
}

.quick-btn {
    padding: 10px;
    border: 2px solid #ddd;
    background: white;
    border-radius: 5px;
    cursor: pointer;
    font-weight: 500;
    transition: all 0.3s;
}

.quick-btn:hover {
    border-color: #667eea;
    background: #f0f4ff;
}

.funds-info {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 5px;
    margin-top: 20px;
    width: 60vw;
}

.funds-info h4 {
    margin-top: 0;
    margin-bottom: 10px;
    color: #2b2d42;
}

.funds-info ul {
    margin: 0;
    padding-left: 20px;
    flex-direction: column;
}

.funds-info li {
    margin: 8px 0;
    color: #555;
    font-size: 14px;
}

.funds-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}

.funds-actions a {
    flex: 1;
    text-align: center;
}

.funds-transactions {
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.funds-transactions h3 {
    margin-top: 0;
    margin-bottom: 20px;
}

.transactions-list {
    width: 100%;
    border-collapse: collapse;
}

.transactions-list thead {
    background: #f8f9fa;
}

.transactions-list th {
    padding: 10px;
    text-align: left;
    font-weight: 600;
    border-bottom: 2px solid #eee;
}

.transactions-list td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}

@media (max-width: 768px) {
    .quick-amounts {
        grid-template-columns: repeat(2, 1fr);
    }

    .funds-actions {
        flex-direction: column;
    }
}

@media (min-width: 800px) {
    .add-funds-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 30px;
    }

    .funds-card {
        grid-column: 1;
    }

    .funds-transactions {
        grid-column: 2;
        height: fit-content;
    }
}
</style>

<div class="footer">
    <div class="footer-text">© 2026 FastData Inc.</div>
</div>

<!-- Fixed button for request-callback -->
<div class="fixed-button">
    <a href="login.php"><i class='bx bxs-phone'></i> Request-callback</a>
</div>

<script src="js/script.js"></script>
</body>

</html>