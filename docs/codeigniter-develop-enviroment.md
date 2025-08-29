# CodeIgniter 4 Development Environment

This project provides a complete development environment for CodeIgniter 4 using VS Code Dev Containers.

## Prerequisites

- Docker Desktop
- VS Code with the "Dev Containers" extension
- Git

## Getting Started

1. **Clone this repository:**
   ```bash
   git clone <your-repo-url>
   cd challege-ecomm-app
   ```

2. **Open in VS Code:**
   ```bash
   code .
   ```

3. **Reopen in Container:**
   - When VS Code opens, it will prompt you to "Reopen in Container"
   - Click "Reopen in Container" or use the command palette (Ctrl+Shift+P) and run "Dev Containers: Reopen in Container"

4. **Wait for the container to build:**
   - The first time will take a few minutes as it builds the Docker image
   - Subsequent starts will be much faster

## Creating a CodeIgniter 4 Project

Once the container is running, you can create a new CodeIgniter 4 project:

```bash
composer create-project codeigniter4/appstarter basic-crud-project
cd basic-crud-project
```

## Running the Application

1. **Start the development server:**
   ```bash
   php spark serve --host 0.0.0.0 --port 8080
   ```

2. **Access the application:**
   - Open your browser and go to `http://localhost:8080`
   - The application will be accessible from your host machine

## Development Features

This development environment includes:

- **PHP 8.2** with all required extensions for CodeIgniter 4
- **Composer** for dependency management
- **Xdebug** for debugging (port 9000)
- **VS Code extensions** for PHP development:
  - Intelephense (PHP language server)
  - PHP Debug (Xdebug integration)
  - Tailwind CSS IntelliSense
  - Prettier
  - TypeScript support

## PHP Extensions Included

- `intl` - Internationalization support
- `mysqli` - MySQL database support
- `pdo_mysql` - PDO MySQL driver
- `pdo_sqlite` - PDO SQLite driver
- `zip` - ZIP file support
- `gd` - Image processing
- `mbstring` - Multibyte string support
- `xml` - XML support
- `curl` - cURL support
- `opcache` - OPcache for performance

## Configuration

The environment is pre-configured with:

- Memory limit: 512M
- Upload max filesize: 50M
- Post max size: 50M
- Max execution time: 300 seconds
- Development environment variables

## Troubleshooting

### If you get permission errors:
The container runs as root user for full development permissions. If you encounter any permission issues, they should be minimal since you have full access.

### If Xdebug doesn't connect:
Make sure port 9000 is forwarded in the devcontainer configuration and your IDE is configured to listen on that port.

### If Composer fails:
Make sure all PHP extensions are properly installed. You can check with:
```bash
php -m
```

## Project Structure

```
challege-ecomm-app/
├── .devcontainer/
│   ├── devcontainer.json
│   ├── Dockerfile
│   └── .dockerignore
├── basic-crud-project/  # Your CodeIgniter 4 project
└── README.md
```

## Next Steps

After setting up your CodeIgniter 4 project:

1. Configure your database settings in `app/Config/Database.php`
2. Set up your environment variables in `.env`
3. Start building your application!

For more information about CodeIgniter 4, visit the [official documentation](https://codeigniter4.github.io/userguide/).
