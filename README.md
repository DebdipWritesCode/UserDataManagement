# User Data Management API

## Overview
This project involves building a set of APIs that manage user data, interact with a database, and handle email notifications. The APIs will handle operations such as uploading user data, viewing user data, backing up the database, and restoring the database.

## Language & Framework
- **Language**: PHP
- **Framework**: Symfony
- **Database**: MySQL

## API Documentation

### 1. Upload and Store Data API
**Endpoint**: `POST /api/upload`  
**Method**: `POST`  
**Request Body**:  
- **File**: The `data.csv` file containing user information.

**Response**:
- **200 OK**: Data uploaded successfully and email sent to users.
- **400 Bad Request**: If the file is not uploaded or has an invalid format.
- **403 Forbidden**: If the user is not an admin.

### 2. View Data API
**Endpoint**: `GET /api/users`  
**Method**: `GET`  
**Response**:
- **200 OK**: Returns a JSON array with all the users' details from the database.

### 3. Backup Database API
**Endpoint**: `GET /api/backup`  
**Method**: `GET`  
**Response**:
- **200 OK**: Backup file generated successfully.
- **403 Forbidden**: If the user is not an admin.

### 4. Restore Database API
**Endpoint**: `POST /api/restore`  
**Method**: `POST`  
**Request Body**:  
- **username**: The username of the admin user requesting the restore.

**Response**:
- **200 OK**: Database restored successfully.
- **400 Bad Request**: If the username is missing.
- **403 Forbidden**: If the user is not an admin.
- **500 Internal Server Error**: If the restore process fails.

## CSV File Format

To upload user data, create a `data.csv` file containing the following columns:
- `name`
- `email`
- `username`
- `address`
- `role`

Example CSV data:

```
John Doe,john.doe@example.com,johndoe,123 Main St,USER
Jane Smith,jane.smith@example.com,janesmith,456 Elm St,ADMIN
Michael Johnson,michael.j@example.com,mjohnson,789 Pine St,USER
Emily Davis,emily.d@example.com,emilydavis,101 Oak St,ADMIN
David Brown,david.b@example.com,davidbrown,202 Maple St,USER
Sarah Wilson,sarah.w@example.com,sarahwilson,303 Birch St,USER
Daniel Lee,daniel.l@example.com,daniellee,404 Cedar St,ADMIN
Jessica Martinez,jessica.m@example.com,jessicam,505 Walnut St,USER
Paul Garcia,paul.g@example.com,paulgarcia,606 Ash St,USER
Laura Clark,laura.c@example.com,lauraclark,707 Cherry St,ADMIN
```

### Admin Role
- Admins can perform critical actions like uploading data, backing up, and restoring the database. Only users with the role `ADMIN` can execute these actions.

## Setup Instructions

1. **Clone the Repository**:
   - Clone this repository to your local machine.

   ```bash
   git clone https://github.com/DebdipWritesCode/UserDataManagement.git
   ```

2. **Install Dependencies**:
   - Install PHP dependencies using Composer.

   ```bash
   composer install
   ```

3. **Database Configuration**:
   - Update the `.env` file with your MySQL credentials.
   
   ```
   DATABASE_URL="mysql://<username>:<password>>@127.0.0.1:3306/<db_name>?serverVersion=8.0"
   DATABASE_USER=<username>
   DATABASE_PASSWORD=<password>
   DATABASE_NAME=<db_name>
   ```

4. **Create the Database**:
   - Create the MySQL database and run migrations.

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Start the Symfony Server**:

   ```bash
   symfony serve
   ```

   The application will now be available at `http://localhost:8000`.

## Testing the API
You can test the API using any API client (like Postman) or using `curl` commands.

### Example cURL for Upload:

```bash
curl -X POST -F "file=@/path/to/data.csv" http://localhost:8000/api/upload
```

### Example cURL for Backup:

```bash
curl http://localhost:8000/api/backup
```

### Example cURL for Restore:

```bash
curl -X POST http://localhost:8000/api/restore -d "username=admin_username"
```

## License
This project is licensed under the MIT License.