# Chat Application Backend

A modern and scalable chat application backend built with PHP and Slim Framework, featuring user authentication, group management, and real-time messaging capabilities.

## Features

- 🔐 JWT-based Authentication
- 👥 User Management
- 👥 Group Management
- 💬 Real-time Messaging
- 📝 Message History
- 🔍 Search Functionality
- 🛡️ Input Validation
- 📦 PSR-4 Autoloading
- 🧪 Unit Testing Support

## Requirements

- PHP 8.0 or higher
- SQLite3 extension
- Composer
- PDO extension
- JSON extension

## Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd chat-app
```

2. Install dependencies:
```bash
composer install
```

3. Start the development server:
```bash
composer start
```

4. The API will be available at `http://localhost:8080`

## API Endpoints

### User Endpoints
- `GET /api/users` - Get all users
- `POST /api/users` - Create a new user
  ```json
  {
      "username": "yourUsername"
  }
  ```
- `GET /api/users/{id}` - Get user information

### Group Endpoints
- `GET /api/groups` - List all groups
- `POST /api/groups` - Create a new group
  ```json
  {
      "name": "Group Name"
  }
  ```
- `GET /api/groups/{id}` - Get group information
- `POST /api/groups/{id}/join` - Join a group

### Message Endpoints
- `GET /api/groups/{groupId}/messages` - List all messages in a group
- `POST /api/groups/{groupId}/messages` - Send a message to a group
  ```json
  {
      "content": "Hello, world!"
  }
  ```

## Authentication

Authentication is done via tokens. To authenticate requests, include an `Authorization` header with a Bearer token:

```
Authorization: Bearer <your-token>
```

A token is automatically generated and provided when creating a user.

## Database

The application uses SQLite as its database. The database file is stored in the `database` directory and is automatically created when needed.

## Testing

Run the test suite:
```bash
composer test
```

## Project Structure

```
chat-app/
├── public/             # Public directory
│   └── index.php      # Application entry point
├── src/               # Source code
│   ├── Controllers/   # Controller classes
│   ├── Models/        # Model classes
│   ├── Services/      # Service classes
│   ├── dependencies.php
│   └── routes.php
├── tests/             # Test files
├── database/          # Database files
├── vendor/            # Composer dependencies
├── .gitignore         # Git ignore rules
├── composer.json      # Composer configuration
└── README.md          # Project documentation
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

