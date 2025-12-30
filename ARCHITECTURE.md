# SMARTGRADE — System Architecture

## Overview
SMARTGRADE is a DepEd-compliant Academic Records and Grading Management System
designed for Philippine schools. It supports multi-school deployment, configurable
academic terms (semester / quarter / trimester), retake-aware grading, and official
document generation (SF9, SF10, certificates).

The system is implemented using:
- Plain PHP (no framework)
- MariaDB (XAMPP-compatible)
- File-based document storage (PDF)

The architecture prioritizes correctness, auditability, and long-term maintainability
over shortcuts or UI convenience.

---

## Core Design Principles

1. Academic records are **legal documents** and must be auditable.
2. No hard deletes of grades, enrollments, or generated documents.
3. Adviser is an **assignment**, not a role.
4. Academic rules are enforced at the **database level**, not only in PHP.
5. Generated documents are **immutable**.
6. The system must support **multiple schools** with different rules.

---

## User Roles

### ADMIN
- Full system control
- Manages users (Admin / Teacher / Student)
- Configures school settings (logo, principal, superintendent, grading rules)
- Overrides adviser assignments
- Generates, regenerates, and revokes documents
- Views audit logs and reports

### TEACHER
- Can exist without being an adviser
- Can create sections/classes
- Creator becomes adviser by default
- Can assign another adviser
- Encodes grades for assigned subjects

### ADVISER (Not a Role)
- Adviser is a teacher assignment per:
  - section
  - academic year
- Responsibilities:
  - Monitor academic standing
  - Review grades
  - Generate SF9, SF10, and certificates

### STUDENT
- Read-only access
- Can view grades
- Can download generated documents (watermarked)
- Cannot modify or regenerate anything

---

## Academic Model

- Supports multiple schools
- Academic Year → contains Academic Terms
- Academic Terms are configurable:
  - semester
  - quarter
  - trimester
- Enrollment is per academic term
- Students may retake the same subject multiple times
- Each attempt is tracked separately

---

## Document System

Supported documents:
- SF9 (Report Card)
- SF10 (Permanent Record)
- Certificates (Honors, Completion, etc.)

Documents:
- Generated from HTML templates
- Stored as PDF files
- Recorded in the database
- Snapshotted at generation time

Documents are never edited after generation.
Corrections require revocation and regeneration.

---

## File Storage
/storage/
/schools/{school_id}/students/{student_id}/
/sf9/
/sf10/
/certificates/


Only file paths and hashes are stored in the database.

---

## Security Model

- No hard deletes
- Immutable academic documents
- Audit logging for:
  - grade changes
  - document generation
  - document revocation
  - account actions
- Student document downloads are watermarked

---

## Design Philosophy

This system is designed to survive:
- audits
- staff turnover
- system migrations
- multi-school scaling

Correctness and traceability are always prioritized over speed or convenience.
