# DRMS System Flow Chart Documentation

## System Overview
The DRMS (Driver Request Management System) is a waste collection management platform with three main user roles: Residents, Drivers, and Administrators.

## 1. User Authentication Flow

```mermaid
flowchart TD
    A[User Access] --> B{User Logged In?}
    B -->|No| C[Login Page]
    B -->|Yes| D[Role-Based Redirect]
    
    C --> E[Enter Credentials]
    E --> F[Validate Credentials]
    F -->|Invalid| G[Show Error]
    F -->|Valid| H[Create Session]
    
    H --> I{User Role}
    I -->|Admin| J[Admin Dashboard]
    I -->|Driver| K[Driver Dashboard]
    I -->|Resident| L[Resident Home]
    
    G --> C
    D --> I
```

## 2. Resident Request Flow

```mermaid
flowchart TD
    A[Resident Login] --> B[Resident Dashboard]
    B --> C[Submit Waste Request]
    
    C --> D[Fill Request Form]
    D --> E[Enter Details:<br/>- Waste Type<br/>- Preferred Date<br/>- Location<br/>- Notes<br/>- Urgency]
    
    E --> F[Submit Request]
    F --> G[Request Saved to Database]
    G --> H[Status: Pending]
    
    H --> I[Admin Notification]
    I --> J[Admin Review Required]
    
    J --> K{Admin Decision}
    K -->|Approve| L[Status: Approved]
    K -->|Reject| M[Status: Rejected]
    
    L --> N[Driver Assignment Available]
    M --> O[Resident Notified of Rejection]
    
    N --> P[Admin Assigns Driver]
    P --> Q[Status: Assigned]
    Q --> R[Driver Notified]
    R --> S[Resident Notified]
    
    S --> T[Driver Completes Task]
    T --> U[Status: Completed]
    U --> V[Resident Notified]
```

## 3. Admin Management Flow

```mermaid
flowchart TD
    A[Admin Login] --> B[Admin Dashboard]
    
    B --> C[View Dashboard Metrics]
    C --> D[Total Requests<br/>Pending Approvals<br/>Active Drivers<br/>Active Users]
    
    B --> E[Request Management]
    E --> F[View Recent Requests]
    F --> G{Request Status}
    
    G -->|Pending| H[Approve/Reject Options]
    G -->|Approved| I[Assign Driver Options]
    G -->|Assigned| J[Monitor Progress]
    
    H --> K{Admin Action}
    K -->|Approve| L[Status: Approved]
    K -->|Reject| M[Status: Rejected]
    
    I --> N[Driver Assignment]
    N --> O[Select Available Driver]
    O --> P[Assign Request]
    P --> Q[Status: Assigned]
    
    B --> R[Smart Scheduling]
    R --> S[Select Date]
    S --> T[Generate Schedule]
    T --> U[Geographic Clustering]
    U --> V[Route Optimization]
    V --> W[Driver Assignment]
    W --> X[Schedule Display]
```

## 4. Driver Task Management Flow

```mermaid
flowchart TD
    A[Driver Login] --> B[Driver Dashboard]
    B --> C[View Assigned Tasks]
    
    C --> D[Task List Display]
    D --> E[Task Details:<br/>- Customer Info<br/>- Location<br/>- Waste Type<br/>- Preferred Time]
    
    E --> F[Update Task Status]
    F --> G{Status Options}
    
    G -->|In Progress| H[Start Collection]
    G -->|Completed| I[Finish Collection]
    G -->|Missed| J[Mark as Missed]
    
    H --> K[Update Progress]
    I --> L[Mark Complete]
    J --> M[Notify Resident]
    
    L --> N[Resident Notification]
    M --> O[Reschedule Option]
    
    B --> P[Map View]
    P --> Q[Route Visualization]
    Q --> R[Navigation Support]
```

## 5. Smart Scheduling Algorithm Flow

```mermaid
flowchart TD
    A[Admin Initiates Scheduling] --> B[Select Target Date]
    B --> C[Fetch Pending Requests]
    
    C --> D[Geographic Clustering]
    D --> E[Group by Proximity<br/>2km radius]
    
    E --> F[Time Slot Assignment]
    F --> G[Morning: 8-12<br/>Afternoon: 12-4<br/>Evening: 4-8]
    
    G --> H[Driver Assignment]
    H --> I[Check Driver Availability]
    I --> J[Consider Capacity & Load]
    
    J --> K[Route Optimization]
    K --> L[Nearest Neighbor Algorithm]
    L --> M[Calculate Distances]
    
    M --> N[Generate Optimized Routes]
    N --> O[Estimate Fuel Savings]
    O --> P[Display Schedule]
    
    P --> Q[Admin Review]
    Q --> R[Manual Adjustments]
    R --> S[Finalize Schedule]
```

## 6. Notification System Flow

```mermaid
flowchart TD
    A[System Event] --> B{Event Type}
    
    B -->|New Request| C[Notify All Admins]
    B -->|Request Approved| D[Notify Resident]
    B -->|Request Rejected| E[Notify Resident]
    B -->|Driver Assigned| F[Notify Driver & Resident]
    B -->|Task Completed| G[Notify Resident]
    B -->|Task Missed| H[Notify Resident]
    
    C --> I[Create Notification Record]
    D --> I
    E --> I
    F --> I
    G --> I
    H --> I
    
    I --> J[Store in Database]
    J --> K[Display in User Dashboard]
    K --> L[Mark as Read/Unread]
```

## 7. Database Entity Relationships

```mermaid
erDiagram
    USERS ||--o{ REQUESTS : submits
    USERS ||--o{ NOTIFICATIONS : receives
    USERS ||--|| DRIVERS : has_driver_profile
    REQUESTS ||--o{ ASSIGNMENTS : gets_assigned
    DRIVERS ||--o{ ASSIGNMENTS : receives
    DRIVERS ||--o{ SCHEDULES : has_schedule
    
    USERS {
        int id PK
        string username
        string email
        string password
        enum role
        timestamp created_at
        decimal latitude
        decimal longitude
        text address
    }
    
    REQUESTS {
        int id PK
        int user_id FK
        string document
        enum status
        string location
        string waste_type
        datetime preferred_date
        text notes
        string urgency
        string resolved_address
        timestamp created_at
        timestamp updated_at
    }
    
    DRIVERS {
        int id PK
        int user_id FK
        string license_number
        string name
        string phone
        string vehicle_type
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
```

## 8. System Architecture Overview

```mermaid
flowchart TD
    subgraph "Frontend Layer"
        A[Resident Interface]
        B[Driver Interface]
        C[Admin Interface]
    end
    
    subgraph "Backend API Layer"
        D[Authentication API]
        E[Request Management API]
        F[Driver Management API]
        G[Scheduling API]
        H[Notification API]
    end
    
    subgraph "Business Logic Layer"
        I[User Management]
        J[Request Processing]
        K[Scheduling Algorithm]
        L[Notification System]
    end
    
    subgraph "Data Layer"
        M[MySQL Database]
        N[Session Management]
    end
    
    A --> D
    B --> D
    C --> D
    
    D --> E
    D --> F
    D --> G
    D --> H
    
    E --> J
    F --> J
    G --> K
    H --> L
    
    J --> M
    K --> M
    L --> M
    I --> M
    
    N --> M
```

## Key System Features

### 1. **Role-Based Access Control (RBAC)**
- **Residents**: Submit requests, view status, receive notifications
- **Drivers**: View assigned tasks, update status, access maps
- **Admins**: Approve/reject requests, assign drivers, generate schedules

### 2. **Smart Scheduling Algorithm**
- Geographic clustering by proximity
- Time slot optimization
- Driver capacity management
- Route optimization using nearest neighbor algorithm
- Fuel savings calculation

### 3. **Real-time Notifications**
- Email and in-app notifications
- Status change alerts
- Task assignment notifications
- Missed collection alerts

### 4. **Geographic Features**
- Location-based request processing
- Route visualization
- Distance calculations
- Map integration for drivers

### 5. **Reporting & Analytics**
- Dashboard metrics
- Request statistics
- Driver performance tracking
- Fuel efficiency reports

This flow chart documentation provides a comprehensive view of how the DRMS system operates, from user authentication to task completion, including all the major processes and interactions between different user roles and system components. 