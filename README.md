# Sky mate digital (Vite) + Next.js Email API

This repo is a Vite + React site. The contact form auto-sends email via a **Next.js API route** (no Express).

## Run the website (Vite)

```bash
npm run dev
```

## Run the email API server (Next.js)

The API listens on `http://localhost:4000/api/contact`.

```bash
# Windows PowerShell example
$env:SMTP_HOST="mail.skymatedigital.com"
$env:SMTP_PORT="465"
$env:SMTP_USER="info@skymatedigital.com"
$env:SMTP_PASS="<your-smtp-password>"
$env:SMTP_FROM="info@skymatedigital.com"     # optional
$env:SMTP_TO="info@skymatedigital.com"         # optional 

npm run email:server
```

If you get `EADDRINUSE` on port `4000`, that means the email API is already running in another terminal.  
Use the existing running server, or stop it first:

```bash
taskkill /PID <pid> /F
```

## Frontend config (optional)

If you want to change the API URL the frontend calls, set:

- `VITE_EMAIL_API_URL` (example: `http://localhost:4000/api/contact`)
