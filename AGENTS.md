# AGENTS.md

## Project Rules For Future Agents

These rules apply to the RichCare Hospital project.

## Git Workflow

- Always check whether Git is enabled before starting project work.
- If `.git` is missing, initialize Git for the project.
- Use `main` as the official stable code branch.
- Use `dev` as the testing and active development branch.
- Make code changes on `dev` first.
- Commit completed work to `dev` with a clear message.
- Do not merge or overwrite `main` without explicit user approval.
- Before changing files, check `git status` and avoid reverting user work.

## Documentation Workflow

- Every new feature must have a matching feature document in `docs/features/`.
- Each feature document should include:
  - Feature name
  - Purpose
  - Files changed
  - Database changes, if any
  - How to test it
  - Current status
- Keep project progress notes in `progress/`.
- Update progress after every meaningful feature, fix, or testing pass.

## Documentation And Research

- When clarification is needed, read official online documentation first.
- Prefer official sources for PHP, MySQL, Bootstrap, JavaScript, browser APIs, and any third-party library.
- If the project uses a local pattern, follow the local pattern unless official documentation shows a safer approach.

## Testing

- Run PHP syntax checks after PHP edits.
- Test live XAMPP pages after syncing files to `C:\xampp\htdocs\RichcarexHospital`.
- Check mobile responsiveness for dashboard changes.
- Record completed testing in the relevant feature document or progress note.

## Branch Meaning

- `dev`: active build and testing branch.
- `main`: stable official project code after the user accepts tested work.
