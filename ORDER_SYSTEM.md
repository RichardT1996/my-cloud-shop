# Order Management System - Implementation Complete

## Overview
Complete order management system with checkout, order tracking, and admin controls.

## Database Tables

### orders
- **order_id** (INT, PRIMARY KEY, IDENTITY)
- **user_id** (INT, FOREIGN KEY to users)
- **order_number** (NVARCHAR(50), UNIQUE) - Format: ORD-YYYYMMDD-HASH
- **status** (NVARCHAR(20)) - pending, processing, shipped, delivered, cancelled
- **total_amount** (DECIMAL(10,2))
- **payment_method** (NVARCHAR(50)) - credit_card, debit_card, paypal, klarna
- **payment_status** (NVARCHAR(20)) - pending, completed, failed
- **shipping_name** (NVARCHAR(100))
- **shipping_address** (NVARCHAR(255))
- **shipping_city** (NVARCHAR(100))
- **shipping_postal_code** (NVARCHAR(20))
- **shipping_phone** (NVARCHAR(20))
- **order_date** (DATETIME, DEFAULT GETDATE())

### order_items
- **item_id** (INT, PRIMARY KEY, IDENTITY)
- **order_id** (INT, FOREIGN KEY to orders)
- **watch_id** (INT, FOREIGN KEY to watches)
- **brand** (NVARCHAR(100))
- **model** (NVARCHAR(100))
- **quantity** (INT)
- **price** (DECIMAL(10,2))
- **subtotal** (DECIMAL(10,2))

## Customer Features

### Checkout Process (checkout.php)
1. **Cart Validation** - Redirects if cart is empty
2. **Shipping Address Form**
   - Full Name
   - Street Address
   - City
   - Postal Code
   - Phone Number
3. **Payment Method Selection**
   - Credit Card
   - Debit Card
   - PayPal
   - Klarna (Buy Now, Pay Later)
4. **Order Summary Sidebar**
   - Lists all items with quantities and prices
   - Shows subtotal and total
5. **Payment Processing**
   - Integrates with Azure Function App
   - Endpoint: https://payments-enfzg2cue2ddggb6.norwayeast-01.azurewebsites.net/api/process_payment
   - Generates unique order number
6. **Order Creation**
   - Saves order to database
   - Saves all order items
   - Clears user's cart
7. **Confirmation**
   - Redirects to order_confirmation.php

### Order Confirmation (order_confirmation.php)
- Success message with checkmark icon
- Displays order number prominently
- Shows order details (total, payment method, payment status, order status)
- Links to "View My Orders" and "Continue Shopping"

### Order History (my_orders.php)
- Lists all user's orders in reverse chronological order
- Shows order number, date, status badge, total amount, item count, payment status
- Status badges color-coded:
  - **Pending**: Yellow (#f1c40f)
  - **Processing**: Blue (#3498db)
  - **Shipped**: Purple (#9b59b6)
  - **Delivered**: Green (#27ae60)
  - **Cancelled**: Red (#e74c3c)
- Actions per order:
  - **View Details** - Opens modal with full order information
  - **Cancel Order** - Available for pending/processing orders only

### Order Details Modal
- Complete order information
- Shipping address
- Payment method and status
- Line items with quantities and prices
- Total amount

### Order Cancellation (cancel_order.php)
- Customers can cancel orders in "pending" or "processing" status
- Updates order status to "cancelled"
- Shows success/error message

## Admin Features

### Admin Order Management (admin_orders.php)
Accessible only to admin@gmail.com

#### Statistics Dashboard
- Total Orders
- Pending Orders (yellow)
- Processing Orders (blue)
- Shipped Orders (purple)
- Delivered Orders (green)
- Total Revenue (£)

#### Order Table
Displays all customer orders with:
- Order number
- Customer name and email
- Order date
- Number of items
- Total amount
- Status dropdown (admin can change)
- View Details button

#### Status Management
- Admin can update order status via dropdown
- Available statuses: pending → processing → shipped → delivered
- Can also mark as cancelled
- Changes immediately reflected on customer side

#### Order Details View
- Complete order information
- Customer details
- Shipping information
- Payment details
- Line items with individual prices

### Status Update (update_order_status.php)
- Admin-only endpoint
- Validates new status
- Updates order in database
- Returns success/failure response

### Admin Order Details (get_admin_order_details.php)
- Admin-only endpoint
- Fetches complete order information
- Returns order and items as JSON

## Navigation Updates
Added "My Orders" link to all customer-facing pages:
- index.php - Home page CTA buttons
- catalog.php - User navigation bar
- wishlist.php - User navigation bar
- cart.php - User navigation bar
- checkout.php - Has its own navigation
- my_orders.php - Active link highlighted

Admin pages have link to "admin_orders.php" in navigation.

## Payment Integration

### Azure Function App
**Endpoint**: https://payments-enfzg2cue2ddggb6.norwayeast-01.azurewebsites.net/api/process_payment

**Request Method**: POST

**Request Payload**:
```json
{
  "order_number": "ORD-20241231-abc123",
  "amount": 12499.99,
  "payment_method": "credit_card",
  "user_id": 5
}
```

**Response**:
```json
{
  "success": true,
  "transaction_id": "txn_xyz789",
  "message": "Payment processed successfully"
}
```

### Order Number Generation
Format: `ORD-YYYYMMDD-HASH`
Example: `ORD-20241231-a1b2c3`
- Uses current date
- Adds 6-character random hash for uniqueness

## Database Connections

### Primary Database (Write Operations)
- Server: mycardiffmet1.database.windows.net
- Database: myDatabase
- User: myadmin
- Used for: INSERT, UPDATE, DELETE operations

### Replica Database (Read Operations)
- Server: mydatabase-replica.database.windows.net
- Database: myDatabase
- User: myadmin
- Used for: SELECT operations (catalog, wishlist viewing)
- **Note**: Read-only - cannot perform write operations

## Security
- All pages require authentication (session-based)
- Admin pages check for admin@gmail.com email
- SQL queries use parameterized statements (sqlsrv_query with params array)
- Order cancellation validated (must belong to user, must be pending/processing)
- Admin status updates validated (must be admin user)

## User Experience
- Professional dark theme matching existing site design
- Luxury aesthetics with clean typography
- Hover effects on all interactive elements
- Responsive button styling
- Clear status indicators with color coding
- Modal dialogs for detailed views
- Success/error messages for all actions

## Files Created/Modified

### New Files
1. **setup_orders.sql** - Database schema
2. **checkout.php** - Checkout page
3. **order_confirmation.php** - Order success page
4. **my_orders.php** - Customer order history
5. **admin_orders.php** - Admin order management
6. **get_order_details.php** - Customer order details API
7. **cancel_order.php** - Order cancellation API
8. **get_admin_order_details.php** - Admin order details API
9. **update_order_status.php** - Admin status update API

### Modified Files
1. **cart.php** - Added checkout button functionality
2. **index.php** - Added "My Orders" link
3. **catalog.php** - Added "My Orders" link
4. **wishlist.php** - Added "My Orders" link

## Testing Checklist
- [ ] Place test order through checkout
- [ ] Verify order appears in my_orders.php
- [ ] Test order details modal
- [ ] Cancel a pending order
- [ ] Admin view all orders
- [ ] Admin update order status
- [ ] Verify payment Function App integration
- [ ] Check cart clears after order
- [ ] Test with different payment methods
- [ ] Verify order status color coding

## Next Steps (Optional Enhancements)
1. Email notifications when order status changes
2. Order search/filter functionality
3. Export orders to CSV for admin
4. Customer order tracking with delivery timeline
5. Print invoice functionality
6. Refund/return request system
7. Order notes/comments for admin
8. Real-time status updates using WebSockets
