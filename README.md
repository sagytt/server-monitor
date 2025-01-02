# Server Monitoring System

This is a Laravel-based application for monitoring servers' health. It supports HTTP, HTTPS, FTP, and SSH protocols and provides a RESTful API for managing servers and their monitoring data.

---

## Features

- Add, update, delete, and list servers.
- Monitor servers' health (status and latency).
- Customizable thresholds for health checks.
- Request history for servers.
- RESTful API endpoints for managing and accessing server data.
- Notifications for unhealthy servers.

---

## Prerequisites

- PHP 7.2 or higher
- Composer
- MySQL or another supported database
- Laravel 7.x
- A working mail server or Mailtrap for email notifications

---

## Installation

1. **Clone the Repository**
   ```bash
   git clone https://github.com/sagytt/server-monitoring.git
   cd server-monitoring
   ```

2. **Install Dependencies**
   ```bash
   composer install
   ```

3. **Set Up Environment Variables**
   Create a `.env` file based on `.env.example`:
   ```bash
   cp .env.example .env
   ```

   Update the `.env` file with your database and email configuration:
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=server_monitoring
   DB_USERNAME=root
   DB_PASSWORD=

   MAIL_MAILER=smtp
   MAIL_HOST=smtp.mailtrap.io
   MAIL_PORT=2525
   MAIL_USERNAME=your_mailtrap_username
   MAIL_PASSWORD=your_mailtrap_password
   MAIL_ENCRYPTION=null
   ```

4. **Generate Application Key**
   ```bash
   php artisan key:generate
   ```

5. **Run Migrations**
   Create the database tables:
   ```bash
   php artisan migrate
   ```

6. **Seed Test Data (Optional)**
   Add test servers to the database:
   ```bash
   php artisan db:seed --class=ServerSeeder
   ```

---

## Usage

1. **Start the Development Server**
   ```bash
   php artisan serve
   ```
   The application will be available at `http://127.0.0.1:8000`.

2. **Run the Scheduler**
   Laravel’s scheduler handles periodic health checks. Run it using:
   ```bash
   php artisan schedule:work
   ```

3. **API Documentation**
   - Base URL: `http://127.0.0.1:8000/api/v1`
   - Example Endpoints:
     - `GET /servers`: List all servers.
     - `POST /servers`: Create a new server.
     - `GET /servers/{id}`: View server details.
     - `PUT /servers/{id}`: Update a server.
     - `DELETE /servers/{id}`: Delete a server.
     - `GET /servers/{id}/history`: Get server request history.
     - `GET /servers/{id}/status/{timestamp}`: Check server status at a given time.

   Use Postman or any API client to interact with the endpoints.

---

## Deployment

1. **Set Up a Production Server**
   - Install PHP, Composer, and a web server (e.g., Nginx or Apache).
   - Set up a MySQL database.

2. **Deploy the Project**
   - Clone the repository to the server.
   - Follow the installation steps above.
   - Configure the web server to serve the Laravel application.

3. **Set Up Cron Jobs**
   Add this to the crontab to run the Laravel scheduler:
   ```bash
   * * * * * php /path-to-your-project/artisan schedule:run >> /dev/null 2>&1
   ```

---

## Troubleshooting

1. **Check Logs**
   Logs are stored in `storage/logs/laravel.log`.

2. **Common Issues**
   - Database connection errors: Verify `.env` database settings.
   - Mail delivery issues: Check your email configuration.

---


## License

This project is licensed under the MIT License.

