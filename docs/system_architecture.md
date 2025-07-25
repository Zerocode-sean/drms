# DRMS System Architecture Documentation

## 1. High-Level System Architecture

```mermaid
graph TB
    subgraph "Client Layer"
        A[Resident Web Interface]
        B[Driver Mobile Interface]
        C[Admin Dashboard]
    end
    
    subgraph "Presentation Layer"
        D[HTML/CSS/JavaScript]
        E[Responsive Design]
        F[Progressive Web App]
    end
    
    subgraph "Application Layer"
        G[PHP Backend]
        H[API Endpoints]
        I[Business Logic]
        J[Session Management]
    end
    
    subgraph "Data Layer"
        K[MySQL Database]
        L[File Storage]
        M[Session Storage]
    end
    
    subgraph "External Services"
        N[Mapping API]
        O[SMS Gateway]
        P[Email Service]
    end
    
    A --> D
    B --> D
    C --> D
    
    D --> G
    E --> G
    F --> G
    
    G --> H
    H --> I
    I --> J
    
    I --> K
    I --> L
    J --> M
    
    I --> N
    I --> O
    I --> P
```

## 2. Detailed Component Architecture

### 2.1 Frontend Components

```mermaid
graph LR
    subgraph "Frontend Architecture"
        A[User Interface Layer]
        B[Presentation Logic]
        C[Data Communication]
        
        subgraph "UI Components"
            D[Authentication Forms]
            E[Dashboard Views]
            F[Request Forms]
            G[Task Management]
            H[Map Integration]
        end
        
        subgraph "JavaScript Modules"
            I[Authentication Module]
            J[Request Management]
            K[Driver Interface]
            L[Admin Dashboard]
            M[Notification Handler]
        end
        
        subgraph "Styling"
            N[CSS Framework]
            O[Responsive Design]
            P[Theme System]
        end
    end
    
    A --> D
    A --> E
    A --> F
    A --> G
    A --> H
    
    B --> I
    B --> J
    B --> K
    B --> L
    B --> M
    
    C --> N
    C --> O
    C --> P
```

### 2.2 Backend API Architecture

```mermaid
graph TB
    subgraph "API Gateway"
        A[Request Router]
        B[Authentication Middleware]
        C[Rate Limiting]
        D[Input Validation]
    end
    
    subgraph "Core API Services"
        E[User Management API]
        F[Request Management API]
        G[Driver Management API]
        H[Scheduling API]
        I[Notification API]
    end
    
    subgraph "Business Logic Services"
        J[User Service]
        K[Request Service]
        L[Driver Service]
        M[Scheduler Service]
        N[Notification Service]
    end
    
    subgraph "Data Access Layer"
        O[User Repository]
        P[Request Repository]
        Q[Driver Repository]
        R[Assignment Repository]
        S[Schedule Repository]
    end
    
    A --> B
    B --> C
    C --> D
    
    D --> E
    D --> F
    D --> G
    D --> H
    D --> I
    
    E --> J
    F --> K
    G --> L
    H --> M
    I --> N
    
    J --> O
    K --> P
    L --> Q
    M --> R
    N --> S
```

## 3. Database Architecture

### 3.1 Database Schema Overview

```mermaid
erDiagram
    USERS {
        int id PK
        varchar username UK
        varchar email UK
        varchar password
        enum role
        decimal latitude
        decimal longitude
        text address
        timestamp created_at
    }
    
    REQUESTS {
        int id PK
        int user_id FK
        varchar document
        enum status
        varchar location
        varchar waste_type
        datetime preferred_date
        text notes
        varchar urgency
        varchar resolved_address
        timestamp created_at
        timestamp updated_at
    }
    
    DRIVERS {
        int id PK
        int user_id FK
        varchar license_number
        varchar name
        varchar phone
        varchar vehicle_type
        decimal capacity
        decimal current_load
        enum status
        boolean is_active
        timestamp created_at
    }
    
    ASSIGNMENTS {
        int id PK
        int request_id FK
        int driver_id FK
        enum status
        timestamp assigned_at
        timestamp completed_at
    }
    
    SCHEDULES {
        int id PK
        date date
        int driver_id FK
        json route_data
        decimal total_distance
        int estimated_duration
        enum status
        timestamp created_at
    }
    
    NOTIFICATIONS {
        int id PK
        int user_id FK
        text message
        boolean is_read
        timestamp created_at
    }
    
    USERS ||--o{ REQUESTS : submits
    USERS ||--o{ NOTIFICATIONS : receives
    USERS ||--|| DRIVERS : has_driver_profile
    REQUESTS ||--o{ ASSIGNMENTS : gets_assigned
    DRIVERS ||--o{ ASSIGNMENTS : receives
    DRIVERS ||--o{ SCHEDULES : has_schedule
```

### 3.2 Database Indexing Strategy

```sql
-- Primary Indexes
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);

CREATE INDEX idx_requests_user_id ON requests(user_id);
CREATE INDEX idx_requests_status ON requests(status);
CREATE INDEX idx_requests_created_at ON requests(created_at);
CREATE INDEX idx_requests_preferred_date ON requests(preferred_date);

CREATE INDEX idx_drivers_user_id ON drivers(user_id);
CREATE INDEX idx_drivers_status ON drivers(status);
CREATE INDEX idx_drivers_is_active ON drivers(is_active);

CREATE INDEX idx_assignments_request_id ON assignments(request_id);
CREATE INDEX idx_assignments_driver_id ON assignments(driver_id);
CREATE INDEX idx_assignments_status ON assignments(status);

CREATE INDEX idx_notifications_user_id ON notifications(user_id);
CREATE INDEX idx_notifications_is_read ON notifications(is_read);
CREATE INDEX idx_notifications_created_at ON notifications(created_at);
```

## 4. Security Architecture

### 4.1 Security Layers

```mermaid
graph TB
    subgraph "Security Architecture"
        A[Network Security]
        B[Application Security]
        C[Data Security]
        D[Access Control]
        
        subgraph "Network Layer"
            E[HTTPS/TLS]
            F[Firewall]
            G[Rate Limiting]
        end
        
        subgraph "Application Layer"
            H[Input Validation]
            I[SQL Injection Prevention]
            J[XSS Protection]
            K[CSRF Protection]
        end
        
        subgraph "Data Layer"
            L[Password Hashing]
            M[Data Encryption]
            N[Secure Storage]
        end
        
        subgraph "Access Control"
            O[Session Management]
            P[Role-Based Access]
            Q[Authentication]
        end
    end
    
    A --> E
    A --> F
    A --> G
    
    B --> H
    B --> I
    B --> J
    B --> K
    
    C --> L
    C --> M
    C --> N
    
    D --> O
    D --> P
    D --> Q
```

## 5. Deployment Architecture

### 5.1 Production Environment

```mermaid
graph TB
    subgraph "Load Balancer"
        A[HAProxy/Nginx]
    end
    
    subgraph "Web Servers"
        B[Apache/PHP Server 1]
        C[Apache/PHP Server 2]
        D[Apache/PHP Server N]
    end
    
    subgraph "Database Layer"
        E[MySQL Master]
        F[MySQL Slave 1]
        G[MySQL Slave 2]
    end
    
    subgraph "Storage"
        H[File Storage]
        I[Session Storage]
        J[Cache Storage]
    end
    
    subgraph "External Services"
        K[CDN]
        L[Email Service]
        M[SMS Gateway]
        N[Mapping API]
    end
    
    A --> B
    A --> C
    A --> D
    
    B --> E
    C --> E
    D --> E
    
    E --> F
    E --> G
    
    B --> H
    C --> H
    D --> H
    
    B --> I
    C --> I
    D --> I
    
    B --> J
    C --> J
    D --> J
    
    A --> K
    B --> L
    C --> M
    D --> N
```

### 5.2 Development Environment

```mermaid
graph LR
    subgraph "Development Stack"
        A[Local Development]
        B[Version Control]
        C[Testing Environment]
        D[Staging Environment]
        
        subgraph "Local Setup"
            E[XAMPP/WAMP]
            F[Git Repository]
            G[Local Database]
        end
        
        subgraph "Testing"
            H[Unit Tests]
            I[Integration Tests]
            J[User Acceptance Tests]
        end
        
        subgraph "Staging"
            K[Staging Server]
            L[Test Database]
            M[External Service Mocks]
        end
    end
    
    A --> E
    A --> F
    A --> G
    
    B --> H
    B --> I
    B --> J
    
    C --> K
    C --> L
    C --> M
```

## 6. Performance Architecture

### 6.1 Caching Strategy

```mermaid
graph TB
    subgraph "Caching Layers"
        A[Browser Cache]
        B[CDN Cache]
        C[Application Cache]
        D[Database Cache]
        
        subgraph "Browser Level"
            E[Static Assets]
            F[API Responses]
            G[Session Data]
        end
        
        subgraph "CDN Level"
            H[Images]
            I[CSS/JS Files]
            J[Static Content]
        end
        
        subgraph "Application Level"
            K[Session Cache]
            L[Query Results]
            M[Configuration]
        end
        
        subgraph "Database Level"
            N[Query Cache]
            O[Result Cache]
            P[Connection Pool]
        end
    end
    
    A --> E
    A --> F
    A --> G
    
    B --> H
    B --> I
    B --> J
    
    C --> K
    C --> L
    C --> M
    
    D --> N
    D --> O
    D --> P
```

## 7. Scalability Considerations

### 7.1 Horizontal Scaling

```mermaid
graph TB
    subgraph "Scalability Strategy"
        A[Load Distribution]
        B[Database Scaling]
        C[Service Scaling]
        
        subgraph "Load Balancing"
            D[Round Robin]
            E[Least Connections]
            F[IP Hash]
        end
        
        subgraph "Database Scaling"
            G[Read Replicas]
            H[Sharding]
            I[Connection Pooling]
        end
        
        subgraph "Service Scaling"
            J[Microservices]
            K[API Gateway]
            L[Service Discovery]
        end
    end
    
    A --> D
    A --> E
    A --> F
    
    B --> G
    B --> H
    B --> I
    
    C --> J
    C --> K
    C --> L
```

## 8. Monitoring and Logging

### 8.1 Monitoring Architecture

```mermaid
graph LR
    subgraph "Monitoring Stack"
        A[Application Monitoring]
        B[Infrastructure Monitoring]
        C[Business Metrics]
        
        subgraph "Application Metrics"
            D[Response Times]
            E[Error Rates]
            F[User Sessions]
        end
        
        subgraph "Infrastructure Metrics"
            G[Server Resources]
            H[Database Performance]
            I[Network Latency]
        end
        
        subgraph "Business Metrics"
            J[Request Volume]
            K[Driver Efficiency]
            L[Customer Satisfaction]
        end
    end
    
    A --> D
    A --> E
    A --> F
    
    B --> G
    B --> H
    B --> I
    
    C --> J
    C --> K
    C --> L
```

## 9. Technology Stack

### 9.1 Frontend Technologies
- **HTML5**: Semantic markup and structure
- **CSS3**: Styling and responsive design
- **JavaScript (ES6+)**: Client-side interactivity
- **Fetch API**: Asynchronous data communication
- **Leaflet.js**: Map integration and visualization

### 9.2 Backend Technologies
- **PHP 8.0+**: Server-side programming
- **MySQL 8.0**: Relational database management
- **Apache/Nginx**: Web server
- **JSON**: Data interchange format
- **Session Management**: PHP sessions

### 9.3 Development Tools
- **Git**: Version control
- **XAMPP/WAMP**: Local development environment
- **Composer**: Dependency management (if applicable)
- **PHPUnit**: Unit testing framework

### 9.4 External Services
- **Mapping APIs**: Route calculation and visualization
- **SMS Gateway**: Text message notifications
- **Email Services**: Email notifications
- **CDN**: Content delivery network

## 10. Future Architecture Considerations

### 10.1 Microservices Migration
- **Service Decomposition**: Break monolithic application into microservices
- **API Gateway**: Centralized API management
- **Service Discovery**: Dynamic service registration and discovery
- **Container Orchestration**: Docker and Kubernetes deployment

### 10.2 Cloud Migration
- **Cloud Hosting**: AWS, Azure, or Google Cloud Platform
- **Serverless Architecture**: Function-as-a-Service for specific operations
- **Managed Services**: Database, caching, and storage services
- **Auto-scaling**: Automatic resource scaling based on demand

### 10.3 Advanced Features
- **Real-time Communication**: WebSocket integration for live updates
- **Mobile Applications**: Native iOS and Android apps
- **IoT Integration**: Smart waste bin sensors and monitoring
- **Machine Learning**: Predictive analytics and route optimization

This architecture documentation provides a comprehensive technical overview of the DRMS system, including current implementation details and future scalability considerations. 