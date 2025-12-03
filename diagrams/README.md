# ShopSphere - UML Diagrams

This directory contains all PlantUML diagrams for the ShopSphere architecture.

## Diagrams

1. **system-architecture.puml** - Overall system architecture showing Azure components
2. **database-schema.puml** - Entity-Relationship diagram of the database schema
3. **component-diagram.puml** - Component diagram showing application layers
4. **sequence-login.puml** - Sequence diagram for user login flow
5. **sequence-wishlist.puml** - Sequence diagram for wishlist operations
6. **sequence-payment.puml** - Sequence diagram for checkout and payment flow
7. **sequence-admin-product.puml** - Sequence diagram for admin adding products
8. **deployment-diagram.puml** - Deployment architecture with Azure services
9. **class-diagram.puml** - Class diagram of PHP application structure
10. **use-case-diagram.puml** - Use case diagram for customer and admin users

## How to View

### Option 1: VS Code Extension
1. Install "PlantUML" extension by jebbs
2. Open any `.puml` file
3. Press `Alt+D` to preview

### Option 2: Online Viewer
Visit: http://www.plantuml.com/plantuml/uml/
- Copy the content of any `.puml` file
- Paste into the online editor
- View the rendered diagram

### Option 3: Generate Images
Install PlantUML locally:
```bash
# Install Java (required)
# Install Graphviz (required for some diagrams)
# Download plantuml.jar

# Generate PNG
java -jar plantuml.jar diagrams/*.puml

# Generate SVG
java -jar plantuml.jar -tsvg diagrams/*.puml
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
