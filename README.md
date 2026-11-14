# DevCard — Developer Portfolio Builder

DevCard is a PHP-based web application for creating, managing, and publishing professional developer portfolios.

Users can manage a portfolio profile and showcase projects with descriptions, technology stacks, GitHub repository links, and live demo links.

## Features

- User registration and login
- Authenticated user dashboard
- Portfolio profile management
- Project creation and management
- Public portfolio pages
- GitHub repository and live demo links
- Database-backed portfolio data

## Technology Stack

- **Backend:** PHP 8.2
- **Frontend:** HTML, CSS, JavaScript
- **Database:** TiDB Cloud (MySQL-compatible)
- **Database access:** PDO MySQL
- **Web server:** Apache
- **Containerization:** Docker

## Project Structure

| Path | Purpose |
|---|---|
| `index.php` | Application landing page |
| `register.php` | User registration |
| `login.php` | User login |
| `dashboard.php` | User dashboard |
| `portfolio.php` | Portfolio profile management |
| `projects.php` | Project management |
| `public.php` | Public portfolio page |
| `publish.php` | Portfolio publishing workflow |
| `app/auth.php` | Authentication and session helpers |
| `app/db.php` | Database connection |
| `assets/` | Frontend assets |
| `certs/tidb-ca.pem` | TiDB CA certificate |
| `Dockerfile` | Docker deployment configuration |

## Requirements

- PHP 8.2 or compatible
- Apache
- PDO MySQL extension
- TiDB Cloud or a compatible MySQL database
- Docker (optional)

## Environment Configuration

Create a local `.env` file using `.env.example` as a starting point:

```env
APP_ENV=development
APP_URL=http://localhost:8000

DB_HOST=your_database_host
DB_PORT=4000
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASS=your_database_password
DB_SSL=1
```

The application reads local `.env` settings and allows hosting-platform environment variables to take priority. Database TLS is enabled by default and uses the configured TiDB CA certificate.

Never commit real database credentials or private keys to GitHub.

## Getting Started

1. Clone the repository:

   ```bash
   git clone https://github.com/ayd-jamesjr/devcard.git
   cd devcard
   ```

2. Create your `.env` file using `.env.example`.
3. Configure your database connection details.
4. Provision the database tables required by the application. **A database schema file is not currently included in this repository**, so the schema must be prepared separately before the application can be fully initialized.
5. Ensure PHP 8.2, Apache, and the PDO MySQL extension are available.
6. Start the application using a compatible local PHP/Apache environment or Docker.

## Docker

The repository includes a Dockerfile based on PHP 8.2 with Apache and the PDO MySQL extension.

Build the image:

```bash
docker build -t devcard .
```

Run the container:

```bash
docker run -p 10000:10000 --env-file .env devcard
```

Make sure the required database environment variables and CA certificate are available to the container.

## Security Notes

- Keep `.env` files out of version control.
- Use HTTPS in deployed environments.
- Keep database TLS certificate verification enabled.
- Use strong database credentials.
- Do not expose private configuration or credentials in frontend code.

## Project Category

**Personal Project — Developer Tools & Portfolio Applications**

---

*Build your presence. Showcase your work. Grow your opportunities.*
