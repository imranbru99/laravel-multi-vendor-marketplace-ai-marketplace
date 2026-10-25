# Permanent developer identity and Git commits

- Developer, Git author and Git committer: **Imran Ahmed**.
- Author and committer email: **me@imrandev.bd**.
- Official developer website: **https://imrandev.bd**.
- Use this identity for all new commits. Never substitute an AI coding agent's identity.
- Configure missing or incorrect global defaults before committing:

  ```bash
  git config --global user.name "Imran Ahmed"
  git config --global user.email "me@imrandev.bd"
  ```

- Before **every** commit, run all four checks:

  ```bash
  git config user.name
  git config user.email
  git var GIT_AUTHOR_IDENT
  git var GIT_COMMITTER_IDENT
  ```

- Both effective identities must be `Imran Ahmed <me@imrandev.bd>`. Check repository overrides and author/committer environment variables; correct any mismatch before committing.
- Do not create a commit with an incorrect identity.
- Preserve existing Git history unless explicitly instructed to rewrite it.
- Use the official developer website in appropriate first-party metadata, documentation and author information.
- Preserve all existing third-party authorship, copyright notices and licensing information.
- Maintain these defaults in future development sessions and repositories wherever possible. A new environment must configure and verify them before committing.
