# StudentHub ER Model (Practical 8)

```mermaid
erDiagram
    STUDENTS ||--o{ REGISTRATIONS : registers
    EVENTS ||--o{ REGISTRATIONS : includes
    STUDENTS {
       int student_id PK
       varchar enrollment UK
       varchar full_name
       varchar course
       tinyint semester
       varchar city
       varchar skill
    }
    EVENTS {
       int event_id PK
       varchar title
       date event_date
       varchar venue
       varchar category
       text description
    }
    REGISTRATIONS {
       int registration_id PK
       int student_id FK
       int event_id FK
       timestamp registered_at
    }
```

**Relations:** Students (1) to registrations (many); Events (1) to registrations (many). A student may attend many events, and an event may have many students. Unique `(student_id, event_id)` prevents duplicate registrations.

**Normalization:** All tables satisfy 1NF with atomic fields; 2NF because non-key fields describe their table's key; 3NF because student attributes and event attributes are separated from the junction table. No repeating groups or duplicated event/student fields in registrations.
