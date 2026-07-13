# CAMS - Client Assisted Management System

A comprehensive management system with multi-role support and OWWA database integration.

## Features

### Multiple Role Selection
- Users can be assigned multiple roles simultaneously (Admin, Superadmin, Employee)
- Checkbox-based role selection in user creation and editing
- Role-based access control with backward compatibility
- Display of multiple role badges in user management

### OWWA Database Integration
- Automatic user import from OWWA database (`owwarebs_owwa.dtr_employeescopy`)
- First-time login with default password (`password123`)
- Auto-import and account creation for OWWA users
- Manual import functionality for bulk user synchronization
- Username and email authentication support

### User Management
- Create, edit, and delete users
- Role-based permissions
- User status management (active/inactive)
- Password hashing and security

### Authentication & Security
- Session-based authentication
- Session timeout (30 minutes)
- Password hashing using PHP's `password_hash()`
- SQL injection prevention using prepared statements
- Role-based access control

## Installation

### Prerequisites
- PHP 7.0 or higher
- MySQL/MariaDB
- Web server (Apache/NginX)
- XAMPP (for local development)

### Database Setup

1. **Create the CAMS database:**
   ```bash
   mysql -u root -p < database.sql
   ```

2. **Run the migration script to create the user_roles table:**
   ```
   http://localhost/cams/setup/create_user_roles_table.php
   ```

3. **Configure database connections:**
   Edit `config/database.php` to match your database credentials:
   ```php
   define('CAMS_DB_HOST', 'localhost');
   define('CAMS_DB_USERNAME', 'root');
   define('CAMS_DB_PASSWORD', '');
   define('CAMS_DB_NAME', 'cams');

   define('OWWA_DB_HOST', 'localhost');
   define('OWWA_DB_USERNAME', 'root');
   define('OWWA_DB_PASSWORD', '');
   define('OWWA_DB_NAME', 'owwarebs_owwa');
   ```

### Directory Structure

```
cams/
├── admin/
│   ├── dashboard.php
│   └── layout.php
├── auth/
│   ├── auth_check.php
│   └── logout.php
├── config/
│   └── database.php
├── Employee/
│   ├── dashboard.php
│   └── layout.php
├── Superadmin/
│   ├── create_user.php
│   ├── dashboard.php
│   ├── edit_user.php
│   ├── layout.php
│   └── users.php
├── setup/
│   ├── create_user_roles_table.php
│   └── import_owwa_users.php
├── database.sql
├── index.php
└── README.md
```

## Usage

### Default Users

After installation, these default users are available (password: `password123`):

- **Superadmin**: superadmin@cams.com
- **Admin**: admin@cams.com
- **Employee**: john@cams.com, jane@cams.com

### First-Time Login for OWWA Users

1. Users from the OWWA database can log in using their `code_name` or `email`
2. Use the default password: `password123`
3. The system will automatically import them to CAMS
4. They will be assigned the `employee` role by default
5. After first login, they can change their password

### Manual User Import

Superadmins can manually import all users from the OWWA database:

1. Login as Superadmin
2. Navigate to Users Management
3. Click "Import from OWWA" button
4. Confirm the import
5. Review the import summary

### Creating Users

1. Login as Superadmin
2. Navigate to Users Management
3. Click "Create User"
4. Fill in user details
5. Select multiple roles using checkboxes
6. Set user status
7. Click "Create User"

### Editing Users

1. Login as Superadmin
2. Navigate to Users Management
3. Click "Edit" next to the user
4. Modify user details
5. Update role assignments
6. Optionally change password
7. Click "Update User"

## Role Permissions

### Superadmin
- Full access to all features
- Create, edit, and delete users
- Import users from OWWA database
- Manage all system settings

### Admin
- Access to admin dashboard
- View and manage assigned resources
- Limited user management (configurable)

### Employee
- Access to employee dashboard
- View assigned tasks and cases
- Limited system access

## Database Schema

### users Table
```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'superadmin', 'employee') NOT NULL DEFAULT 'employee',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('active', 'inactive') DEFAULT 'active'
);
```

### user_roles Table
```sql
CREATE TABLE user_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    role ENUM('admin', 'superadmin', 'employee') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_role (user_id, role)
);
```

### OWWA Integration Table
The system integrates with the external OWWA database table:
```sql
dtr_employeescopy (
    code_name VARCHAR(100),
    email VARCHAR(255),
    fullname VARCHAR(255)
)
```

## Security Features

- **Password Hashing**: All passwords are hashed using PHP's `password_hash()`
- **SQL Injection Prevention**: All database queries use prepared statements
- **Session Management**: Secure session handling with timeout
- **Role-Based Access Control**: Users can only access features based on their roles
- **Input Validation**: All user inputs are validated and sanitized

## Troubleshooting

### Login Issues

**Problem**: User cannot log in
**Solution**: 
- Verify database credentials in `config/database.php`
- Check if user exists in CAMS database
- For OWWA users, ensure they use `password123` for first login
- Check if user_roles table exists (run migration script)

### Import Issues

**Problem**: Import from OWWA fails
**Solution**:
- Verify OWWA database credentials
- Check if `dtr_employeescopy` table exists
- Ensure table has `code_name`, `email`, and `fullname` columns

### Role Display Issues

**Problem**: Multiple roles not showing correctly
**Solution**:
- Run migration script to create `user_roles` table
- Check database for existing role assignments
- Verify `auth_check.php` is updated

## Development

### Adding New Features

1. Create feature branch
2. Implement changes
3. Test thoroughly
4. Update documentation
5. Commit changes

### Code Style

- Follow PSR-12 coding standards
- Use prepared statements for all database queries
- Implement proper error handling
- Add comments for complex logic

## Support

For issues or questions:
- Check the troubleshooting section
- Review database schema
- Verify configuration files
- Check PHP error logs

## License

Copyright © 2021-2026 CAMS. All rights reserved.

## Version

Current Version: 1.0.0
