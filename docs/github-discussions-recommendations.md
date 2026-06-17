# GitHub Discussions Recommendations

## Categories to create

| Category | Format | Purpose |
|---|---|---|
| Announcements | Announcement | Release notes, roadmap updates, and important maintenance notices. |
| Q&A | Question and answer | Usage questions that do not need code changes. |
| Ideas | Open-ended discussion | Early feature ideas before they become issues or RFCs. |
| Show and tell | Open-ended discussion | Examples of generated modules, package integrations, and community patterns. |
| RFCs | Open-ended discussion | Larger design proposals that may affect generated code or public APIs. |

## Support workflow

1. Start in Q&A with the LaravelBase version, Laravel version, PHP version, and
   command or API being used.
2. Include a minimal reproduction when generated code behaves unexpectedly.
3. Convert confirmed package bugs into issues using the bug report template.
4. Keep security reports out of Discussions and use GitHub Security Advisories
   or the maintainer email in `SECURITY.md`.

## Feature discussion workflow

1. Open an Ideas discussion with the Laravel workflow, pain point, and expected
   generated output.
2. Confirm whether the request belongs in LaravelBase core, documentation, or a
   consuming application's custom stubs.
3. Convert accepted proposals into feature request issues.
4. Link the issue from the discussion so implementation context stays connected.

## RFC workflow

1. Use RFCs for changes that affect public contracts, generated file structure,
   command options, package dependencies, or compatibility guarantees.
2. Include motivation, proposed API, generated code examples, alternatives, and
   backward compatibility notes.
3. Leave the RFC open long enough for maintainer and community feedback.
4. Move accepted RFCs into tracked issues and document implementation changes in
   `CHANGELOG.md`.
