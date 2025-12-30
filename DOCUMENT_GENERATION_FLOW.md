# Document Generation Flow (SF9, SF10, Certificates)

## Purpose
To generate official academic documents that are immutable, auditable,
and safe from tampering.

---

## Flow Overview

1. Permission check
2. Load active template
3. Fetch academic data
4. Create immutable snapshot
5. Render HTML
6. Apply watermark (student view only)
7. Generate PDF
8. Save file metadata
9. Save snapshot
10. Write audit log

---

## Key Rules

- Students can never generate documents
- Generated documents are never edited
- Regeneration requires revocation
- Student copies are always watermarked:
  “Official Copy – Student View”

---

## Snapshot Requirement

Snapshot must include:
- student identity
- grades used
- honors
- academic year / term
- school officials
- generation timestamp

Snapshots are stored as JSON and never modified.

# document_templates/ SPECS (HTML → PDF READY)
/document_templates
  /sf9.html
  /sf10.html
  /certificate_honors.html
  /partials
    header.html
    footer.

## Common Template Rule
Inline CSS only (PDF-safe)
No JS required
Placeholders use {{ }}

## sf9.html

<h2>STUDENT REPORT CARD (SF9)</h2>

<p>Student: {{student_name}}</p>
<p>LRN: {{lrn}}</p>
<p>School Year: {{school_year}}</p>
<p>Semester: {{semester}}</p>

<table>
  <tr>
    <th>Subject</th>
    <th>Final Grade</th>
  </tr>
  {{subjects_rows}}
</table>

<p>General Average: {{gwa}}</p>

<footer>
  {{watermark}}
</footer>

## certificate_honors.html (Your hardcoded sample → normalized)

<h1>ACADEMIC EXCELLENCE AWARD</h1>

<p>This certificate is presented to</p>

<h2>{{student_name}}</h2>

<p>
For outstanding performance as
<strong>{{honor_type}}</strong>
during {{school_year}}
</p>

<p>
Given this {{date_issued}} at {{school_name}}
</p>

<p>{{principal_name}}</p>
<p>School Head</p>

## ERD (TEXTUAL + DIAGRAM-READY)

SCHOOLS
 ├── USERS
 │    ├── TEACHERS
 │    └── STUDENTS
 │
 ├── SCHOOL_YEARS
 │    └── SEMESTERS
 │
 ├── SUBJECTS
 │
 └── SECTIONS
      ├── SECTION_SUBJECTS
      │     └── ENROLLMENTS
      │           └── GRADES
      │
      └── ADVISER (TEACHER)

STUDENTS
 ├── ENROLLMENTS
 ├── HONORS
 └── DOCUMENTS

USERS
 └── AUDIT_LOGS

Key ERD Notes (for diagram tools)
    ENROLLMENTS resolves student × subject × semester × attempt
    DOCUMENTS is versioned, never overwritten
    Adviser is assignment, not a role
    Retakes = new enrollment row with attempt_no + 1

