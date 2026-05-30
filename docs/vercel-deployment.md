# Vercel Deployment

This project is a CodeIgniter 4 PHP app, so Vercel runs it as a serverless PHP function through `api/index.php`.

## Required setup

1. Push the repository to GitHub.
2. In Vercel, import the GitHub repository.
3. Enable "Automatically expose System Environment Variables" in Vercel project settings.
4. Add the production environment variables from `.env.example`.
5. Use an external MySQL database. Vercel does not provide a persistent local database for PHP functions.
6. Run the migrations against the production database:

```bash
php spark migrate --all
```

## Important notes

- Set `CI_ENVIRONMENT=production` in Vercel.
- Set `APP_BASE_URL` to the Vercel production URL or your custom domain.
- Use `SESSION_DRIVER=database` and `SESSION_SAVE_PATH=ci_sessions` for reliable login sessions.
- Uploaded ID documents currently use CodeIgniter's local `WRITEPATH`. On Vercel this is temporary `/tmp` storage, so use persistent object storage before relying on uploads in production.
