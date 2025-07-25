# DRMS Logical Model Documentation

## 1. System Overview

The DRMS (Driver Request Management System) is a comprehensive waste collection management platform designed to streamline the process of waste collection requests, driver assignments, and route optimization. The system operates on a three-tier architecture with role-based access control.

## 2. Core Business Entities

### 2.1 User Management
- **Entity**: Users
- **Purpose**: Central user management for all system participants
- **Key Attributes**:
  - Authentication credentials (username, email, password)
  - Role-based access control (resident, driver, admin)
  - Geographic data (latitude, longitude, address)
  - Timestamp tracking

### 2.2 Request Management
- **Entity**: Requests
- **Purpose**: Core business process for waste collection requests
- **Key Attributes**:
  - Request details (waste type, location, preferred date)
  - Status tracking (Pending → Approved/Rejected → Assigned → Completed)
  - Priority and urgency levels
  - Customer notes and special instructions

### 2.3 Driver Management
- **Entity**: Drivers
- **Purpose**: Driver profile and capacity management
- **Key Attributes**:
  - Vehicle information (type, capacity, current load)
  - Contact information and availability status
  - Performance tracking metrics

### 2.4 Assignment Management
- **Entity**: Assignments
- **Purpose**: Linking requests to drivers for task execution
- **Key Attributes**:
  - Request-driver relationship
  - Assignment status tracking
  - Completion timestamps

## 3. Business Process Flows

### 3.1 Request Submission Process
```
1. Resident Authentication
   ↓
2. Request Form Completion
   - Waste type selection
   - Location specification
   - Preferred date/time
   - Urgency level
   - Additional notes
   ↓
3. Request Validation
   - Required field validation
   - Date/time validation
   - Location validation
   ↓
4. Database Storage
   - Create request record
   - Set initial status: 'Pending'
   - Generate unique request ID
   ↓
5. Notification Generation
   - Notify all admin users
   - Send confirmation to resident
```

### 3.2 Request Approval Process
```
1. Admin Dashboard Access
   ↓
2. Pending Request Review
   - View request details
   - Check resident information
   - Review location and timing
   ↓
3. Decision Making
   - Approve: Set status to 'Approved'
   - Reject: Set status to 'Rejected' with reason
   ↓
4. Status Update
   - Update request status
   - Record decision timestamp
   - Update request history
   ↓
5. Notification Dispatch
   - Notify resident of decision
   - If approved: Enable driver assignment
```

### 3.3 Driver Assignment Process
```
1. Approved Request Identification
   ↓
2. Available Driver Search
   - Check driver availability
   - Verify capacity constraints
   - Consider geographic proximity
   ↓
3. Driver Selection
   - Manual assignment by admin
   - Or automatic assignment by algorithm
   ↓
4. Assignment Creation
   - Create assignment record
   - Link request to driver
   - Set assignment status: 'Assigned'
   ↓
5. Notification System
   - Notify assigned driver
   - Update resident on assignment
   - Update request status
```

### 3.4 Task Execution Process
```
1. Driver Task Access
   ↓
2. Task Details Review
   - Customer information
   - Collection location
   - Waste type and quantity
   - Preferred collection time
   ↓
3. Route Planning
   - Access map integration
   - Calculate optimal route
   - Estimate travel time
   ↓
4. Task Execution
   - Update status: 'In Progress'
   - Navigate to collection point
   - Perform waste collection
   ↓
5. Task Completion
   - Update status: 'Completed'
   - Record completion timestamp
   - Update driver capacity
   ↓
6. Notification Dispatch
   - Notify resident of completion
   - Update admin dashboard
```

## 4. Smart Scheduling Algorithm

### 4.1 Algorithm Components

#### Geographic Clustering
- **Purpose**: Group nearby requests to minimize travel distance
- **Method**: 2km radius clustering using coordinate-based distance calculation
- **Benefits**: Reduced fuel consumption and travel time

#### Time Slot Optimization
- **Morning Slot**: 8:00 AM - 12:00 PM
- **Afternoon Slot**: 12:00 PM - 4:00 PM  
- **Evening Slot**: 4:00 PM - 8:00 PM
- **Logic**: Group requests by preferred time windows

#### Driver Assignment Logic
- **Capacity Management**: Track current load vs. vehicle capacity
- **Availability Check**: Verify driver status (active/inactive/busy)
- **Workload Balancing**: Distribute tasks evenly among available drivers

#### Route Optimization
- **Algorithm**: Nearest Neighbor approach
- **Distance Calculation**: Haversine formula for geographic distances
- **Fuel Savings**: Estimate based on optimized vs. unoptimized routes

### 4.2 Scheduling Process Flow
```
1. Date Selection
   ↓
2. Pending Request Collection
   - Filter requests by date
   - Exclude completed/rejected requests
   ↓
3. Geographic Clustering
   - Group requests by proximity
   - Create cluster centroids
   ↓
4. Time Slot Assignment
   - Assign clusters to time slots
   - Consider preferred times
   ↓
5. Driver Assignment
   - Match clusters to available drivers
   - Consider capacity constraints
   ↓
6. Route Optimization
   - Apply nearest neighbor algorithm
   - Calculate optimal routes
   ↓
7. Schedule Generation
   - Create schedule records
   - Calculate metrics (distance, duration, fuel savings)
   ↓
8. Schedule Display
   - Present optimized routes
   - Show driver assignments
   - Display efficiency metrics
```

## 5. Data Flow Architecture

### 5.1 Frontend to Backend Communication
```
Frontend (JavaScript) 
    ↓ (AJAX/Fetch API)
Backend API Endpoints
    ↓ (PHP Processing)
Business Logic Layer
    ↓ (Database Queries)
MySQL Database
    ↓ (Response Data)
Business Logic Layer
    ↓ (JSON Response)
Backend API Endpoints
    ↓ (HTTP Response)
Frontend (JavaScript)
```

### 5.2 Session Management Flow
```
1. User Login Request
   ↓
2. Credential Validation
   ↓
3. Session Creation
   - Generate session ID
   - Store user information
   - Set role-based permissions
   ↓
4. Session Storage
   - Store in PHP session
   - Set session timeout
   ↓
5. Request Processing
   - Validate session on each request
   - Check role-based access
   ↓
6. Session Termination
   - On logout or timeout
   - Clear session data
```

## 6. Security Model

### 6.1 Authentication Security
- **Password Hashing**: Bcrypt algorithm for secure password storage
- **Session Management**: Secure session handling with timeout
- **Input Validation**: Server-side validation for all user inputs
- **SQL Injection Prevention**: Prepared statements for database queries

### 6.2 Authorization Model
- **Role-Based Access Control (RBAC)**:
  - **Resident**: Submit requests, view own requests, receive notifications
  - **Driver**: View assigned tasks, update task status, access maps
  - **Admin**: Full system access, request management, driver assignment

### 6.3 Data Protection
- **Personal Data**: Minimal collection and secure storage
- **Location Data**: Encrypted storage and access control
- **Communication**: Secure API endpoints with validation

## 7. Notification System

### 7.1 Notification Types
1. **Request Status Changes**: Approval, rejection, assignment, completion
2. **Task Assignments**: Driver notifications for new assignments
3. **System Alerts**: Missed collections, schedule changes
4. **Reminders**: Upcoming collections, pending actions

### 7.2 Notification Delivery
- **In-App Notifications**: Real-time dashboard updates
- **Email Notifications**: Status change confirmations
- **SMS Notifications**: Critical alerts and reminders

## 8. Performance Considerations

### 8.1 Database Optimization
- **Indexing**: Primary keys, foreign keys, frequently queried fields
- **Query Optimization**: Efficient joins and filtering
- **Connection Pooling**: Reuse database connections

### 8.2 Caching Strategy
- **Session Caching**: User session data
- **Query Caching**: Frequently accessed data
- **Static Asset Caching**: CSS, JavaScript, images

### 8.3 Scalability Planning
- **Horizontal Scaling**: Multiple server instances
- **Database Scaling**: Read replicas for reporting
- **CDN Integration**: Static asset delivery

## 9. Error Handling and Logging

### 9.1 Error Categories
- **Authentication Errors**: Invalid credentials, session timeouts
- **Validation Errors**: Invalid input data, missing required fields
- **Database Errors**: Connection failures, query errors
- **Business Logic Errors**: Invalid state transitions, constraint violations

### 9.2 Logging Strategy
- **Error Logging**: Detailed error information for debugging
- **Audit Logging**: User actions and system events
- **Performance Logging**: Response times and resource usage

## 10. Integration Points

### 10.1 External Services
- **Mapping Services**: Route calculation and visualization
- **SMS Services**: Text message notifications
- **Email Services**: Email notifications and confirmations

### 10.2 API Endpoints
- **Authentication**: Login, logout, session management
- **Request Management**: CRUD operations for requests
- **Driver Management**: Driver assignment and status updates
- **Scheduling**: Route generation and optimization
- **Notifications**: Real-time notification delivery

This logical model provides a comprehensive understanding of the DRMS system's structure, processes, and interactions, serving as a foundation for system development, maintenance, and future enhancements. 