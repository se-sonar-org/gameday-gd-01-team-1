# Mini PHP Blog

An example blog app used to demonstrate SonarQube analysis.

- **Backend:** PHP + SQLite (PDO)
- **Frontend:** plain HTML + JavaScript

## Running locally

You only need PHP installed (8.x). From this folder:

```bash
php -S localhost:8000
```

Then open <http://localhost:8000> in your browser. A `blog.db` SQLite file
is created automatically on first run.

To publish a post, use the form at the bottom of the page with the admin
password (see `config.php`).

## Files

| File         | Purpose                                  |
|--------------|------------------------------------------|
| `index.html` | Page markup                              |
| `app.js`     | Frontend logic (fetch + render)          |
| `style.css`  | Styling                                  |
| `api.php`    | JSON API for listing / creating posts    |
| `db.php`     | Database connection + schema             |
| `config.php` | Configuration                            |
