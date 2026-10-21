# Practical 15 — GitHub Submission Checklist

**The source ZIP does not publish or commit to GitHub for you.** Run these commands from `C:\xampp\htdocs\practical15` after testing. Git Bash/PowerShell works if `git` is installed.

```bash
git init
git branch -M main
git add .
git status --short
git commit -m "Practical 15: integrate StudentHub modules, tests and deployment docs"
git remote add origin https://github.com/<your-account>/<your-repo>.git
git push -u origin main
```

If you already have an existing repository (for example your StudentHub repository), prefer **git clone / git pull** and copy the updated files there; do not blindly initialize over unrelated history. Replace `<your-account>/<your-repo>` with your actual URL. **No repository update has been performed by this ZIP.**

Before committing:

- [ ] `git status` shows no personal CSV / JSON student form submissions, `.env`, screenshots with credentials, local backups or real DB exports.
- [ ] `.gitignore` and folder structure are present; `README.md` can be read from GitHub.
- [ ] Project imports with instructions; all links work on localhost.
- [ ] Screenshots only show safe demo data, no passwords, real student identifiers or connection secrets.
- [ ] Verified test results are filled in `docs/TEST-REPORT.md`; don't claim tests were run when not.
- [ ] `git push` is successful and you can open the final repository URL.

**Final GitHub Repository URL:** __________________________
**Commit hash:** __________________________
**Submitted at:** __________________________
