# SMARTGRADE — Developer Rules (Non-Negotiable)

These rules must be followed at all times.
Any implementation that violates these rules is considered incorrect.

---

## 1. Academic Data Integrity
- NEVER hard delete:
  - students
  - enrollments
  - grades
  - academic documents
- Use status flags or soft deletes only.

---

## 2. Adviser Logic
- Adviser is NOT a role.
- Adviser is a teacher assignment per:
  - section
  - academic year
- Do not convert adviser into an ENUM or role column.

---

## 3. Retake Policy
- A student may retake the same subject.
- Each retake must:
  - create a new enrollment
  - increment attempt_no
- Never overwrite old grades or attempts.

---

## 4. Document Immutability
- Generated documents are immutable.
- Never edit or overwrite a generated file.
- Corrections require:
  1. Revoke old document
  2. Generate a new document
- Always preserve historical records.

---

## 5. Student Access Rules
Students:
- ✅ View
- ✅ Download
- ❌ Modify
- ❌ Regenerate

All student-downloaded documents MUST include the watermark:
“Official Copy – Student View”

---

## 6. Multi-School Rule
- All core tables must be school-aware.
- Never assume a single global school.

---

## 7. Database-First Enforcement
- Enforce academic rules in the database whenever possible.
- PHP must not be the only line of defense.

---

## 8. Audit Logging
Audit logs are required for:
- grade changes
- document generation
- document revocation
- account actions
