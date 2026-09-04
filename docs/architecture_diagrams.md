# Architecture Diagrams

Dokumentasi ini mencakup ERD, Use Case, dan Class Diagram berdasarkan backend `be-project-management`.

## 1. Entity Relationship Diagram (ERD)

Representasi entitas dan relasi utama:

- `users`
- `projects`
- `tasks`
- `comments`
- `attachments`
- `notifications`
- `activities`

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar name
        varchar email
        varchar password
        enum role
        datetime email_verified_at
        varchar remember_token
        timestamp created_at
        timestamp updated_at
    }
    PROJECTS {
        bigint id PK
        varchar name
        text description
        enum status
        date start_date
        date end_date
        tinyint progress
        bigint owner_id FK
        timestamp created_at
        timestamp updated_at
    }
    TASKS {
        bigint id PK
        bigint project_id FK
        varchar title
        text description
        enum status
        enum priority
        tinyint progress
        date deadline
        bigint assignee_id FK
        integer story_points
        timestamp created_at
        timestamp updated_at
    }
    COMMENTS {
        bigint id PK
        bigint project_id FK
        bigint task_id FK
        bigint author_id FK
        text content
        timestamp created_at
        timestamp updated_at
    }
    ATTACHMENTS {
        bigint id PK
        bigint comment_id FK
        varchar name
        bigint size
        varchar path
        timestamp created_at
        timestamp updated_at
    }
    NOTIFICATIONS {
        bigint id PK
        bigint user_id FK
        varchar title
        text message
        enum kind
        boolean read
        timestamp created_at
        timestamp updated_at
    }
    ACTIVITIES {
        bigint id PK
        bigint actor_id FK
        text message
        timestamp created_at
        timestamp updated_at
    }

    USERS ||--o{ PROJECTS : owns
    PROJECTS ||--o{ TASKS : contains
    USERS ||--o{ TASKS : assigned_to
    PROJECTS ||--o{ COMMENTS : has
    TASKS ||--o{ COMMENTS : has
    USERS ||--o{ COMMENTS : authors
    COMMENTS ||--o{ ATTACHMENTS : has
    USERS ||--o{ NOTIFICATIONS : receives
    USERS ||--o{ ACTIVITIES : performs
```

## 2. Use Case Diagram

Aktor dan skenario utama berdasarkan API `routes/api.php` dan kontroler backend.

Aktor:
- Guest
- Authenticated User
- Team Member
- Project Manager
- Admin

Use case utama:
- Register
- Login
- Logout
- View profile
- View projects
- Create/update/delete projects
- Create/update/delete tasks
- Comment on projects/tasks
- View notifications
- Mark notifications read
- View activity feed
- Manage users (Admin only)

```mermaid
flowchart TB
    Guest[Guest]
    AuthUser[Authenticated User]
    TeamMember[Team Member]
    ProjectManager[Project Manager]
    Admin[Admin]

    UC_Register[[Register]]
    UC_Login[[Login]]
    UC_Logout[[Logout]]
    UC_ViewProfile[[View Profile]]
    UC_ViewProjects[[View Projects]]
    UC_ManageProjects[[Manage Projects]]
    UC_ManageTasks[[Manage Tasks]]
    UC_Comment[[Comment on Project/Task]]
    UC_ViewNotifications[[View Notifications]]
    UC_MarkNotificationsRead[[Mark Notifications Read]]
    UC_ViewActivities[[View Activity Feed]]
    UC_ManageUsers[[Manage Users]]

    Guest --> UC_Register
    Guest --> UC_Login
    AuthUser --> UC_Logout
    AuthUser --> UC_ViewProfile
    AuthUser --> UC_ViewProjects
    AuthUser --> UC_ViewActivities
    AuthUser --> UC_ViewNotifications
    TeamMember --> UC_ManageTasks
    TeamMember --> UC_Comment
    ProjectManager --> UC_ManageProjects
    ProjectManager --> UC_ManageTasks
    ProjectManager --> UC_Comment
    Admin --> UC_ManageUsers
    Admin --> UC_ManageProjects
    Admin --> UC_ManageTasks
    Admin --> UC_Comment
```

## 3. Class Diagram

Model domain utama dan relasi antar kelas.

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string password
        +string role
        +bool isAdmin()
        +bool canManageProjects()
        +bool canManageTasks()
        +bool canUpdateTaskStatus()
        +bool canComment()
    }

    class Project {
        +int id
        +string name
        +string description
        +string status
        +date start_date
        +date end_date
        +int progress
        +int owner_id
        +int getMembersAttribute()
        +int getProgressAttribute()
    }

    class Task {
        +int id
        +int project_id
        +string title
        +string description
        +string status
        +string priority
        +int progress
        +date deadline
        +int assignee_id
        +int story_points
    }

    class Comment {
        +int id
        +int project_id
        +int task_id
        +int author_id
        +string content
    }

    class Attachment {
        +int id
        +int comment_id
        +string name
        +int size
        +string path
        +string getUrlAttribute()
    }

    class Notification {
        +int id
        +int user_id
        +string title
        +string message
        +string kind
        +bool read
    }

    class Activity {
        +int id
        +int actor_id
        +string message
    }

    User "1" --> "0..*" Project : owns
    Project "1" --> "0..*" Task : contains
    User "1" --> "0..*" Task : assignedTasks
    Project "1" --> "0..*" Comment : comments
    Task "0..1" --> "0..*" Comment : comments
    User "1" --> "0..*" Comment : authoredComments
    Comment "1" --> "0..*" Attachment : attachments
    User "1" --> "0..*" Notification : notifications
    User "1" --> "0..*" Activity : activities
```

@enduml
```

## Catatan

- `comments` selalu terkait ke `project`; dapat juga terkait ke `task`.
- `tasks` dapat memiliki `assignee_id` nullable untuk tugas yang belum ditetapkan.
- `notifications` dibatasi kepada pengguna tertentu.
- `activities` mencatat tindakan dengan `actor_id` ke `users`.

Untuk merender diagram, gunakan ekstensi PlantUML atau generator PlantUML pada file Markdown ini.
