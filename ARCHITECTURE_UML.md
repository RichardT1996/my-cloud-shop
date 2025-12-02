# ShopSphere - UML Architecture Diagrams

## 1. System Architecture Diagram

```mermaid
graph TB
    subgraph "Client Layer"
        Browser[Web Browser]
    end
    
    subgraph "Azure Web App"
        WebApp[PHP Web Application<br/>rt-shopsphere.azurewebsites.net]
        
        subgraph "PHP Pages"
            Home[index.php]
            Auth[login.php / register.php]
            Catalog[catalog.php]
            Wishlist[wishlist.php]
            Cart[cart.php]
            Checkout[checkout.php]
            Orders[my_orders.php]
            Admin[admin_dashboard.php]
            Users[view_users.php]
            AdminOrders[admin_orders.php]
        end
    end
    
    subgraph "Azure Functions (Sweden Central)"
        AuthFunc[Authentication Function<br/>shopsphere-authentication]
        WishlistFunc[Wishlist Function<br/>shopsphere-wishlist]
        PaymentFunc[Payment Function<br/>shopsphere-payment]
        ImageFunc[Image Upload Function<br/>image-upload]
    end
    
    subgraph "Azure Storage"
        BlobStorage[Blob Storage<br/>shopspherestore<br/>Container: images]
    end
    
    subgraph "Azure SQL Server"
        SQLServer[SQL Server<br/>shopspshere-dbserver]
        Database[(Database: shopspheredb)]
    end
    
    Browser -->|HTTPS| WebApp
    WebApp -->|API Calls| AuthFunc
    WebApp -->|API Calls| WishlistFunc
    WebApp -->|API Calls| PaymentFunc
    Admin -->|API Calls| ImageFunc
    
    AuthFunc -->|Query/Insert| Database
    WishlistFunc -->|Query/Insert/Delete| Database
    PaymentFunc -->|Query/Insert| Database
    ImageFunc -->|Upload Images| BlobStorage
    
    WebApp -->|Direct SQL| Database
    SQLServer -->|Contains| Database
    
    style WebApp fill:#2c3e50,stroke:#fff,color:#fff
    style AuthFunc fill:#27ae60,stroke:#fff,color:#fff
    style WishlistFunc fill:#27ae60,stroke:#fff,color:#fff
    style PaymentFunc fill:#27ae60,stroke:#fff,color:#fff
    style ImageFunc fill:#27ae60,stroke:#fff,color:#fff
    style Database fill:#e74c3c,stroke:#fff,color:#fff
    style BlobStorage fill:#3498db,stroke:#fff,color:#fff
```

## 2. Database Schema (ER Diagram)

```mermaid
erDiagram
    shopusers ||--o{ orders : places
    shopusers ||--o{ cart : has
    shopusers ||--o{ wishlist : maintains
    watches ||--o{ cart : contains
    watches ||--o{ wishlist : includes
    watches ||--o{ order_items : included_in
    orders ||--|{ order_items : contains
    
    shopusers {
        int id PK
        nvarchar name
        nvarchar email UK
        nvarchar password
        datetime created_at
        datetime updated_at
    }
    
    watches {
        int id PK
        nvarchar name
        nvarchar brand
        decimal price
        nvarchar description
        nvarchar image_url
        datetime created_at
    }
    
    wishlist {
        int id PK
        int user_id FK
        int watch_id FK
        datetime added_at
    }
    
    cart {
        int id PK
        int user_id FK
        int watch_id FK
        int quantity
        datetime added_at
    }
    
    orders {
        int id PK
        int user_id FK
        decimal total_amount
        nvarchar status
        nvarchar shipping_address
        nvarchar payment_status
        datetime created_at
        datetime updated_at
    }
    
    order_items {
        int id PK
        int order_id FK
        int watch_id FK
        int quantity
        decimal price_at_time
    }
```

## 3. Component Diagram

```mermaid
graph TB
    subgraph "Presentation Layer"
        UI[Web UI - HTML/CSS/JavaScript]
    end
    
    subgraph "Application Layer - PHP Web App"
        SessionMgmt[Session Management]
        AuthPages[Authentication Pages]
        CustomerPages[Customer Pages]
        AdminPages[Admin Pages]
        DBConfig[db_config.php]
    end
    
    subgraph "API Layer - Azure Functions"
        LoginAPI[Login API]
        GetWishlist[Get Wishlist API]
        AddWishlist[Add to Wishlist API]
        RemoveWishlist[Remove from Wishlist API]
        ProcessPayment[Process Payment API]
        UploadImage[Upload Image API]
    end
    
    subgraph "Data Access Layer"
        SQLSRVDriver[PHP sqlsrv Driver]
        PyODBC[Python pyodbc Driver]
    end
    
    subgraph "Data Layer"
        AzureSQL[Azure SQL Database]
        BlobStore[Azure Blob Storage]
    end
    
    UI --> AuthPages
    UI --> CustomerPages
    UI --> AdminPages
    
    AuthPages --> SessionMgmt
    CustomerPages --> SessionMgmt
    AdminPages --> SessionMgmt
    
    AuthPages --> LoginAPI
    CustomerPages --> GetWishlist
    CustomerPages --> AddWishlist
    CustomerPages --> RemoveWishlist
    CustomerPages --> ProcessPayment
    AdminPages --> UploadImage
    
    AuthPages --> DBConfig
    CustomerPages --> DBConfig
    AdminPages --> DBConfig
    
    DBConfig --> SQLSRVDriver
    LoginAPI --> PyODBC
    GetWishlist --> PyODBC
    AddWishlist --> PyODBC
    RemoveWishlist --> PyODBC
    ProcessPayment --> PyODBC
    
    SQLSRVDriver --> AzureSQL
    PyODBC --> AzureSQL
    UploadImage --> BlobStore
```

## 4. Sequence Diagram - User Login Flow

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant LoginPage as login.php
    participant ProcessLogin as process_login.php
    participant AuthFunc as Authentication Function
    participant Database as Azure SQL Database
    
    User->>Browser: Enter credentials
    Browser->>LoginPage: Display login form
    User->>LoginPage: Submit email & password
    LoginPage->>ProcessLogin: POST credentials
    ProcessLogin->>AuthFunc: POST /api/login
    AuthFunc->>Database: Query shopusers table
    Database-->>AuthFunc: Return user record
    AuthFunc->>AuthFunc: Verify password hash
    alt Authentication Success
        AuthFunc-->>ProcessLogin: 200 OK + user data
        ProcessLogin->>ProcessLogin: Create session
        ProcessLogin-->>Browser: Redirect to index.php
        Browser->>User: Display home page
    else Authentication Failed
        AuthFunc-->>ProcessLogin: 401 Unauthorized
        ProcessLogin-->>Browser: Redirect to login with error
        Browser->>User: Display error message
    end
```

## 5. Sequence Diagram - Add to Wishlist Flow

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant Catalog as catalog.php
    participant WishlistAPI as Wishlist Function
    participant Database as Azure SQL Database
    
    User->>Browser: Browse catalog
    Browser->>Catalog: Load page
    Catalog->>WishlistAPI: GET /api/get_wishlist?user_id=X
    WishlistAPI->>Database: SELECT FROM wishlist
    Database-->>WishlistAPI: Return wishlist items
    WishlistAPI-->>Catalog: Return JSON
    Catalog->>Browser: Display with heart icons
    
    User->>Browser: Click heart icon
    Browser->>WishlistAPI: POST /api/add_to_wishlist
    Note over Browser,WishlistAPI: {user_id: X, watch_id: Y}
    WishlistAPI->>Database: INSERT INTO wishlist
    Database-->>WishlistAPI: Success
    WishlistAPI-->>Browser: 200 OK
    Browser->>User: Update heart icon (filled)
```

## 6. Sequence Diagram - Checkout & Payment Flow

```mermaid
sequenceDiagram
    actor User
    participant Browser
    participant Cart as cart.php
    participant Checkout as checkout.php
    participant PaymentAPI as Payment Function
    participant Database as Azure SQL Database
    
    User->>Browser: View cart
    Browser->>Cart: Display cart items
    User->>Cart: Click "Proceed to Checkout"
    Cart->>Checkout: Redirect
    Checkout->>Database: Load cart items
    Database-->>Checkout: Return cart data
    
    User->>Checkout: Enter shipping address
    User->>Checkout: Enter payment details
    Checkout->>PaymentAPI: POST /api/process_payment
    Note over Checkout,PaymentAPI: {user_id, cart_items, total, address, payment_details}
    
    PaymentAPI->>PaymentAPI: Validate payment
    PaymentAPI->>Database: BEGIN TRANSACTION
    PaymentAPI->>Database: INSERT INTO orders
    PaymentAPI->>Database: INSERT INTO order_items
    PaymentAPI->>Database: DELETE FROM cart
    PaymentAPI->>Database: COMMIT TRANSACTION
    Database-->>PaymentAPI: Success
    
    PaymentAPI-->>Checkout: 200 OK + order_id
    Checkout-->>Browser: Redirect to order_confirmation.php
    Browser->>User: Display confirmation
```

## 7. Sequence Diagram - Admin Add Product Flow

```mermaid
sequenceDiagram
    actor Admin
    participant Browser
    participant AdminDash as admin_dashboard.php
    participant ProcessAdmin as admin_process.php
    participant ImageAPI as Image Upload Function
    participant BlobStorage as Azure Blob Storage
    participant Database as Azure SQL Database
    
    Admin->>Browser: Click "Add New Watch"
    Browser->>AdminDash: Open modal
    Admin->>AdminDash: Fill form & select image
    Admin->>Browser: Click "Add Watch"
    
    Browser->>ImageAPI: POST /api/upload_image
    Note over Browser,ImageAPI: {image: base64, filename}
    ImageAPI->>ImageAPI: Decode base64
    ImageAPI->>BlobStorage: PUT blob (REST API)
    BlobStorage-->>ImageAPI: 201 Created
    ImageAPI-->>Browser: Return blob URL
    
    Browser->>ProcessAdmin: POST with watch data
    Note over Browser,ProcessAdmin: {name, brand, price, description, image_url}
    ProcessAdmin->>Database: INSERT INTO watches
    Database-->>ProcessAdmin: Success
    ProcessAdmin-->>Browser: Redirect with success message
    Browser->>AdminDash: Reload page
    Browser->>Admin: Display new watch
```

## 8. Deployment Diagram

```mermaid
graph TB
    subgraph "Azure Cloud - UK West Region"
        WebNode[Azure App Service<br/>PHP 8.x Runtime<br/>rt-shopsphere.azurewebsites.net]
    end
    
    subgraph "Azure Cloud - Sweden Central Region"
        FuncNode1[Azure Function App<br/>Python 3.12<br/>shopsphere-authentication]
        FuncNode2[Azure Function App<br/>Python 3.12<br/>shopsphere-wishlist]
        FuncNode3[Azure Function App<br/>Python 3.12<br/>shopsphere-payment]
        FuncNode4[Azure Function App<br/>Python 3.12<br/>image-upload]
    end
    
    subgraph "Azure Storage"
        StorageNode[Storage Account<br/>shopspherestore<br/>Blob Container: images]
        FuncStorage[Storage Account<br/>Function Runtime Storage]
    end
    
    subgraph "Azure SQL"
        SQLNode[SQL Server<br/>shopspshere-dbserver<br/>Database: shopspheredb]
    end
    
    subgraph "GitHub"
        Repo[Repository<br/>RichardT1996/my-cloud-shop<br/>Branch: shopsphere]
    end
    
    subgraph "CI/CD Pipeline"
        Actions[GitHub Actions Workflows]
    end
    
    Repo -->|git push| Actions
    Actions -->|Deploy PHP App| WebNode
    Actions -->|Deploy Function 1| FuncNode1
    Actions -->|Deploy Function 2| FuncNode2
    Actions -->|Deploy Function 3| FuncNode3
    Actions -->|Deploy Function 4| FuncNode4
    
    WebNode -.->|HTTPS API Calls| FuncNode1
    WebNode -.->|HTTPS API Calls| FuncNode2
    WebNode -.->|HTTPS API Calls| FuncNode3
    WebNode -.->|HTTPS API Calls| FuncNode4
    
    WebNode -->|SQL Connection| SQLNode
    FuncNode1 -->|SQL Connection| SQLNode
    FuncNode2 -->|SQL Connection| SQLNode
    FuncNode3 -->|SQL Connection| SQLNode
    
    FuncNode4 -->|Blob API| StorageNode
    FuncNode1 -.->|Runtime Storage| FuncStorage
    FuncNode2 -.->|Runtime Storage| FuncStorage
    FuncNode3 -.->|Runtime Storage| FuncStorage
    FuncNode4 -.->|Runtime Storage| FuncStorage
    
    WebNode -->|Read Images| StorageNode
    
    style WebNode fill:#2c3e50,stroke:#fff,color:#fff
    style FuncNode1 fill:#27ae60,stroke:#fff,color:#fff
    style FuncNode2 fill:#27ae60,stroke:#fff,color:#fff
    style FuncNode3 fill:#27ae60,stroke:#fff,color:#fff
    style FuncNode4 fill:#27ae60,stroke:#fff,color:#fff
    style SQLNode fill:#e74c3c,stroke:#fff,color:#fff
    style StorageNode fill:#3498db,stroke:#fff,color:#fff
    style Actions fill:#f39c12,stroke:#fff,color:#fff
```

## 9. Class Diagram - PHP Application Structure

```mermaid
classDiagram
    class DatabaseConfig {
        +string DB_HOST
        +string DB_NAME
        +string DB_USER
        +string DB_PASS
        +getConnection() resource
    }
    
    class Session {
        +int user_id
        +string user_name
        +string user_email
        +start() void
        +destroy() void
        +isAdmin() bool
        +isAuthenticated() bool
    }
    
    class User {
        +int id
        +string name
        +string email
        +string password
        +datetime created_at
        +login() bool
        +register() bool
        +logout() void
    }
    
    class Watch {
        +int id
        +string name
        +string brand
        +decimal price
        +string description
        +string image_url
        +getAll() array
        +getById() Watch
        +create() bool
        +update() bool
        +delete() bool
    }
    
    class Wishlist {
        +int id
        +int user_id
        +int watch_id
        +datetime added_at
        +getByUser() array
        +add() bool
        +remove() bool
    }
    
    class Cart {
        +int id
        +int user_id
        +int watch_id
        +int quantity
        +datetime added_at
        +getByUser() array
        +add() bool
        +updateQuantity() bool
        +remove() bool
        +clear() bool
    }
    
    class Order {
        +int id
        +int user_id
        +decimal total_amount
        +string status
        +string shipping_address
        +string payment_status
        +datetime created_at
        +create() bool
        +getByUser() array
        +updateStatus() bool
        +cancel() bool
    }
    
    class OrderItem {
        +int id
        +int order_id
        +int watch_id
        +int quantity
        +decimal price_at_time
    }
    
    User "1" --> "*" Wishlist : has
    User "1" --> "*" Cart : has
    User "1" --> "*" Order : places
    Watch "1" --> "*" Wishlist : in
    Watch "1" --> "*" Cart : in
    Watch "1" --> "*" OrderItem : ordered
    Order "1" --> "*" OrderItem : contains
    
    Session ..> User : manages
    DatabaseConfig ..> User : provides connection
    DatabaseConfig ..> Watch : provides connection
    DatabaseConfig ..> Wishlist : provides connection
    DatabaseConfig ..> Cart : provides connection
    DatabaseConfig ..> Order : provides connection
```

## 10. Use Case Diagram

```mermaid
graph TB
    subgraph "ShopSphere Use Cases"
        subgraph "Customer Use Cases"
            UC1[Register Account]
            UC2[Login]
            UC3[Browse Catalog]
            UC4[Add to Wishlist]
            UC5[Remove from Wishlist]
            UC6[Add to Cart]
            UC7[Update Cart Quantity]
            UC8[Checkout]
            UC9[Make Payment]
            UC10[View Orders]
            UC11[Cancel Order]
        end
        
        subgraph "Admin Use Cases"
            UC12[Manage Products]
            UC13[Add New Watch]
            UC14[Edit Watch Details]
            UC15[Delete Watch]
            UC16[Upload Images]
            UC17[View All Users]
            UC18[Manage Orders]
            UC19[Update Order Status]
        end
    end
    
    Customer((Customer))
    Admin((Admin))
    
    Customer --> UC1
    Customer --> UC2
    Customer --> UC3
    Customer --> UC4
    Customer --> UC5
    Customer --> UC6
    Customer --> UC7
    Customer --> UC8
    Customer --> UC9
    Customer --> UC10
    Customer --> UC11
    
    Admin --> UC2
    Admin --> UC12
    Admin --> UC13
    Admin --> UC14
    Admin --> UC15
    Admin --> UC16
    Admin --> UC17
    Admin --> UC18
    Admin --> UC19
    
    UC8 -.includes.-> UC9
    UC13 -.includes.-> UC16
    UC14 -.includes.-> UC16
```

## Architecture Summary

### Technology Stack
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Backend**: PHP 8.x with sqlsrv extension
- **Serverless**: Azure Functions (Python 3.12) with pyodbc
- **Database**: Azure SQL Database
- **Storage**: Azure Blob Storage
- **CI/CD**: GitHub Actions
- **Hosting**: Azure App Service (PHP) + Azure Functions

### Key Design Patterns
1. **MVC Pattern**: Separation of concerns in PHP application
2. **API Gateway Pattern**: Azure Functions as microservices
3. **Repository Pattern**: Database access abstraction
4. **Session-based Authentication**: PHP session management
5. **REST API**: Stateless communication between web app and functions

### Security Features
- SQL injection prevention (parameterized queries)
- Password hashing (bcrypt)
- Session management for authentication
- HTTPS encryption
- Azure SQL firewall rules
- CORS configuration on Functions

### Scalability Features
- Serverless Azure Functions (auto-scaling)
- Azure SQL Database (scalable tiers)
- Blob Storage for static assets
- Stateless API design
- Connection pooling
