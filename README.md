# Task-Sphere Application Setup Instructions

This document provides instructions on how to set up the Task-Sphere application for development and production environments.

## Prerequisites

Before proceeding, ensure you have the following installed:

- **PHP:** Ensure your PHP version meets the requirements of your Laravel version.
- **Composer:** A dependency manager for PHP. Download and install it from [Composer website](https://getcomposer.org/).
- **Node.js and npm:** Required for compiling frontend assets. Task-Sphere uses Inertia.js for its frontend.

## Setup Instructions

Follow these steps to set up the Task-Sphere application:

### 1. Clone the Repository

Clone the Task-Sphere application's code from the repository:

```bash
git clone https://github.com/kamalsingh200238/enterprise_pro_task_sphere.git
cd enterprise_pro_task_sphere
```

### 2. Navigate to the Software Directory

All the commands in the following steps should be executed from the `software` directory:

```bash
cd software
```

### 3. Install Dependencies

Install the PHP dependencies using Composer:

```bash
composer install
```

Install the Node.js dependencies for Inertia.js frontend:

```bash
npm install
```

### 4. Configure the Environment

Copy the `.env.example` file to `.env`:

```bash
cp .env.example .env
```

Generate an application key. This is essential for encryption services in Laravel:

```bash
php artisan key:generate
```

This will update your `.env` file with an `APP_KEY` value, which is required for secure operation of the Task-Sphere application.

### 5. Configure SQLite Database

The Task-Sphere application is already configured to use SQLite, but you need to create the database file before running migrations:

```bash
# create the empty database file
touch database/database.sqlite
```

If you want to change the location of your SQLite database file, edit the `DB_DATABASE` parameter in your `.env` file:

```bash
# Default location
DB_DATABASE=database/database.sqlite

# Custom location example
DB_DATABASE=/var/www/data/tasksphere.sqlite
```

**Note:** Make sure the directory exists and has proper write permissions before changing the location.

### 6. Run Database Migrations

Run the database migrations to create the database schema:

```bash
php artisan migrate
```

This command will create the tables required by the Task-Sphere application in your SQLite database.

### 7. Seed the Database

Populate the database with initial data required by Task-Sphere:

```bash
php artisan db:seed
```

This will add default settings, user roles, and other necessary data to get you started.

### 8. Configure Application URL

Set the `APP_URL` parameter in your `.env` file to match where your Task-Sphere application will be accessible:

```bash
APP_URL=https://your-tasksphere-domain.com
```

The APP_URL setting is crucial for:

- Generating correct links in emails
- OAuth redirects working properly
- Asset loading
- Security features like CSRF protection

**For HTTP environments:**

```bash
APP_URL=http://your-domain.com
```

**For HTTPS environments (recommended for production):**

```bash
APP_URL=https://your-domain.com
FORCE_HTTPS=true
```

Make sure your APP_URL protocol (http:// or https://) matches your actual server configuration.

### 9. Configure Email

Task-Sphere is set up to send notifications via email, and you have the flexibility to use any mail provider you prefer. This is easily achieved by updating the email configuration settings within your application's `.env` file.

You can utilize the SMTP settings provided by your chosen email service. This includes popular services like SendGrid, Amazon SES, or any standard SMTP server. To do this, you'll typically need to sign up with the email provider and retrieve their specific SMTP credentials, which usually include the mail host, port, username, password, and encryption method.

For example, here's how the configuration would look in your `.env` file if you were using Mailgun's SMTP service, as provided in the documentation:

```bash
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your-mailgun-smtp-username
MAIL_PASSWORD=your-mailgun-smtp-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tasksphere@yourdomain.com
MAIL_FROM_NAME="Task-Sphere"
```

Simply replace the values for `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, and `MAIL_ENCRYPTION` with the corresponding settings from your chosen email provider. You can also adjust `MAIL_FROM_ADDRESS` and `MAIL_FROM_NAME` to your desired sending email address and name.
Task-Sphere is configured to use a mail service for sending notifications. You can easily change to any mail provider by updating your `.env` file.

### 10. Build Frontend Assets

Build the Inertia.js frontend assets:

```bash
# For production
npm run build
```

### 11. OAuth Setup

Okay, here is the updated explanation focusing on the redirect URI and the required configuration:

#### Setting up OAuth with Authentik

To allow users to log into Task-Sphere using their Authentik accounts, you need to configure both Authentik and Task-Sphere.

**1. Configure Authentik**

In your Authentik instance, you need to create an application and an associated OAuth2/OpenID provider for Task-Sphere.

- Log in to your Authentik Admin Interface.
- Navigate to **Applications > Providers**.
- Click **Create** and select **OAuth2/OpenID Provider**.
- Give the provider a descriptive name (e.g., "Task-Sphere Login").
- In the configuration, locate the **Redirect URIs** field. You **must** add the URL where Task-Sphere will be accessible, followed by `/auth/callback`. It is critical to use the correct scheme (`http` or `https`) that matches how you access your Task-Sphere instance.
  - If you access Task-Sphere via `http://localhost:8000`, add `http://localhost:8000/auth/callback`.
  - If you access Task-Sphere via `https://tasksphere.yourdomain.com`, add `https://tasksphere.yourdomain.com/auth/callback`.
  - In a hosted environment, this will typically look like `http://your-hosted-url/auth/callback` or `https://your-hosted-url/auth/callback`, replacing `your-hosted-url` with your actual domain or IP address and port if necessary.
- Under **Scope Mapping**, ensure that the `openid` scope is included and selected. The `openid` scope is the minimum required for Authentik to provide basic identity information for authentication.
- Save the provider.
- Navigate to **Applications > Applications**.
- Click **Create**.
- Give your application a name (e.g., "Task-Sphere Application").
- Link this application to the **Provider** you just created.
- Save the application.
- Go back to the **Providers** list and edit the provider you created for Task-Sphere. On this page, you will find the **Client ID** and **Client Secret** that Authentik has generated for this provider. **Copy these two values.**

**2. Configure Task-Sphere Environment Variables**

Now, open the `.env` file in your Task-Sphere project's root directory. Add or update the following lines using the information from your Authentik setup:

```dotenv
AUTHENTIK_CLIENT_ID=YOUR_AUTHENTIK_CLIENT_ID
AUTHENTIK_CLIENT_SECRET=YOUR_AUTHENTIK_CLIENT_SECRET
AUTHENTIK_REDIRECT_URI=http://your-hosted-url/auth/callback
AUTHENTIK_BASE_URL=http://localhost:9000
```

Here's what each variable is for:

- `AUTHENTIK_CLIENT_ID`: Paste the Client ID you copied from the Authentik provider details here.
- `AUTHENTIK_CLIENT_SECRET`: Paste the Client Secret you copied from the Authentik provider details here.
- `AUTHENTIK_REDIRECT_URI`: Set this to the exact same Redirect URI you configured in Authentik, including the correct `http` or `https` scheme and your application's host/domain. For example, `https://tasksphere.yourdomain.com/auth/callback`.
- `AUTHENTIK_BASE_URL`: Set this to the base URL of your Authentik instance (e.g., `https://your-authentik-domain.com` or `http://localhost:9000`). Task-Sphere uses this to initiate the login process via Authentik.

After saving the `.env` file, restart your Task-Sphere application's server or Octane process to load the new configuration. Task-Sphere should now present an option to log in via Authentik.

### 12. Queues

Task-Sphere, like many modern web applications, utilizes queues to handle tasks that can take time to complete, such as sending email notifications or processing logs. This approach prevents these operations from slowing down the user's request and keeps the application feeling fast and responsive.

**Why Queues are Necessary:**

When a user triggers an action that requires sending an email or logging extensive information, instead of performing that action immediately during the web request, the application places a small message (a "job") onto a queue. A separate background process, called a queue worker, is constantly listening to this queue. When the worker finds a new job, it picks it up and performs the necessary action (like sending the email or writing the log entry) asynchronously, without impacting the user's current interaction with the application.

If you do not have a queue worker running, the jobs for sending emails and processing logs will remain on the queue and will not be processed. Consequently, email notifications will not be sent, and logging might not function as expected.

**How to Run Queue Workers:**

To ensure that your email notifications and logging features work correctly, you need to start a queue worker process.

The simplest way to start a worker for development is using the following Artisan command:

```bash
php artisan queue:work
```

This command will start a worker that processes jobs from the default queue. It will continue running until you stop it (e.g., by pressing `Ctrl+C`).

**For Production Environments:**

For a production application, it is crucial to have a robust way to keep your queue workers running continuously and to automatically restart them if they fail. Manually running `php artisan queue:work` in production is not reliable.

The recommended approach for production is to use a process monitor like **Supervisor**. Supervisor is a client/server system that allows you to monitor and control a number of processes on UNIX-like operating systems. It can automatically start your queue worker, restart it if it crashes, and keep it running reliably in the background.

Setting up Supervisor involves configuring it to watch the `php artisan queue:work` command and manage its lifecycle. This ensures that your queues are always being processed, and your email and logging functionalities remain operational.

### 13. Serve the Application

You have two primary methods for running your Task-Sphere application: using the standard Laravel development server or leveraging Laravel Octane with FrankenPHP for enhanced performance.

#### Option 1: Standard Laravel Development Server

For typical development purposes, you can use the built-in PHP development server that comes with Laravel. This is the simplest way to get your application up and running quickly.

To start the standard development server, navigate to your Task-Sphere project's root directory in your terminal and run the following command:

```bash
php artisan serve
```

Upon execution, your Task-Sphere application will become accessible in your web browser at the address `http://localhost:8000`. This method is suitable for local development and testing.

#### Option 2: Laravel Octane with FrankenPHP (Recommended for Production)

For production environments, or if you require significantly improved performance during development, using Laravel Octane with FrankenPHP is highly recommended. Laravel Octane supercharges your application by keeping it bootstrapped in memory, drastically reducing the overhead on subsequent requests and leading to much faster response times. FrankenPHP is a modern application server for PHP built on Caddy, known for its performance and ease of use.

To set up Laravel Octane with FrankenPHP:

1. **Install Laravel Octane:**
   Add the Laravel Octane package to your project's dependencies using Composer by running this command in your terminal:

   ```bash
   composer require laravel/octane
   ```

1. **Set up Octane:**
   After installing the package, you need to configure Octane for your application. Run the installation command:

   ```bash
   php artisan octane:install
   ```

   During the installation process, you will be prompted to choose your preferred application server. Select `FrankenPHP` from the options provided.

1. **Start Task-Sphere with Octane:**
   Once installed and configured, you can start your Task-Sphere application using Octane with the following command:

   ```bash
   php artisan octane:start
   ```

   Running your application with Octane will provide a significant boost in speed and responsiveness due to its in-memory bootstrapping mechanism.

For production deployments using Laravel Octane, it's advisable to implement a method to ensure the Octane process stays running reliably. This can be achieved by setting up a systemd service or utilizing a process manager like Supervisor.

You can learn more about Laravel Octane from the following resources:

- [Learn more about Octane](https://www.youtube.com/watch?v=YGBvdAWt0W8)
- [Laravel Octane Documentation](https://laravel.com/docs/12.x/octane#main-content)
