# Contributing to Malnu Backend

Thank you for your interest in contributing to Malnu Backend! We welcome contributions from the community and appreciate your help in improving this school management system.

## Table of Contents

- [Getting Started](#getting-started)
- [Development Workflow](#development-workflow)
- [Code Style and Standards](#code-style-and-standards)
- [Commit Messages](#commit-messages)
- [Pull Request Process](#pull-request-process)
- [Testing](#testing)
- [Code Review](#code-review)

## Getting Started

### Prerequisites

Before contributing, ensure you have the following installed:

- **PHP 8.2+** - Required by HyperVel framework
- **Composer 2.x** - PHP dependency manager
- **Node.js 18+** - Required for frontend
- **npm/yarn** - JavaScript package manager
- **Docker & Docker Compose** - For containerized development
- **Redis** - For caching and sessions
- **Git** - Version control

### Setting Up Your Development Environment

1. **Fork the repository**
   ```bash
   # Fork the repository on GitHub and clone your fork
   git clone https://github.com/YOUR_USERNAME/malnu-backend.git
   cd malnu-backend
   ```

2. **Add upstream remote**
   ```bash
   git remote add upstream https://github.com/sulhicmz/malnu-backend.git
   ```

3. **Install backend dependencies**
   ```bash
   composer install
   ```

4. **Install frontend dependencies**
   ```bash
   cd frontend
   npm install
   cd ..
   ```

5. **Configure environment variables**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

   Edit `.env` file with your configuration. See [DEVELOPER_GUIDE.md](docs/DEVELOPER_GUIDE.md) for detailed setup instructions.

6. **Start Docker services**
   ```bash
   docker-compose up -d mysql redis
   ```

7. **Run database migrations**
   ```bash
   php artisan migrate
   ```

8. **Run database seeders**
   ```bash
   php artisan db:seed
   ```

9. **Start the backend server**
   ```bash
   php artisan start
   ```

The application will be available at `http://localhost:9501`

For more detailed setup instructions, see the [Developer Guide](docs/DEVELOPER_GUIDE.md).

## Development Workflow

### Branching Strategy

We use a feature branch workflow:

1. **Create a new branch** from `main` for your work:
   ```bash
   git checkout main
   git pull upstream main
   git checkout -b feature/your-feature-name
   # Or for bug fixes:
   git checkout -b fix/your-bug-fix-name
   ```

2. **Branch naming conventions**:
   - Features: `feature/feature-name` or `feature/issue-number-feature-name`
   - Bug fixes: `fix/bug-name` or `fix/issue-number-bug-name`
   - Documentation: `docs/documentation-update`
   - Refactoring: `refactor/component-name`

3. **Make your changes** and commit them with clear messages (see [Commit Messages](#commit-messages))

4. **Push your branch** to your fork:
   ```bash
   git push origin feature/your-feature-name
   ```

5. **Create a Pull Request** on GitHub (see [Pull Request Process](#pull-request-process))

### Keeping Your Branch Up to Date

Regularly sync your feature branch with the upstream main branch:

```bash
git fetch upstream
git rebase upstream/main
```

Resolve any conflicts that arise before pushing.

## Code Style and Standards

### PHP Coding Standards

We follow the **PSR-12** coding standard with some project-specific conventions:

#### General Guidelines

- Use **4 spaces** for indentation (no tabs)
- Maximum line length: **120 characters**
- Declare strict types: `declare(strict_types=1);`
- Use type hints for all function parameters and return values
- Use short array syntax: `[]` instead of `array()`

#### Naming Conventions

- **Classes**: `PascalCase` - e.g., `User`, `AuthService`, `PasswordValidator`
- **Methods**: `camelCase` - e.g., `getUser()`, `validatePassword()`
- **Variables**: `camelCase` - e.g., `$userId`, `$userData`
- **Constants**: `UPPER_SNAKE_CASE` - e.g., `CACHE_PREFIX`, `DEFAULT_TTL`
- **Database tables**: `snake_case` - e.g., `users`, `password_reset_tokens`

#### Code Formatting

We use **PHP CS Fixer** to enforce code style. Before committing, run:

```bash
# Check for style issues
vendor/bin/php-cs-fixer fix --dry-run --diff

# Auto-fix style issues
vendor/bin/php-cs-fixer fix
```

Run code style check on changed files:
```bash
composer cs-diff
```

#### Static Analysis

We use **PHPStan** for static analysis. Run before committing:

```bash
composer analyse
```

Fix any errors or warnings reported by PHPStan.

### JavaScript/TypeScript Coding Standards

For frontend code, we follow standard JavaScript/TypeScript conventions:

- Use **2 spaces** for indentation
- Use `const` and `let` instead of `var`
- Prefer arrow functions for callbacks
- Follow ESLint rules configured in the project

### Documentation Standards

- Use **Markdown** format for all documentation
- Include clear descriptions of what the code does
- Add docblocks for complex functions and classes:
  ```php
  /**
   * Validates user password against security requirements.
   *
   * @param string $password The password to validate
   * @return array Array of validation errors (empty if valid)
   */
  public function validatePassword(string $password): array
  ```

## Commit Messages

Write clear, descriptive commit messages following this format:

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat`: A new feature
- `fix`: A bug fix
- `docs`: Documentation changes
- `style`: Code style changes (formatting, etc.)
- `refactor`: Code refactoring without feature changes
- `perf`: Performance improvements
- `test`: Adding or updating tests
- `chore`: Maintenance tasks, build process, etc.

### Examples

**Good commit message:**
```
feat(auth): Implement password complexity validation

Add uppercase, lowercase, number, and special character requirements
for user passwords. Also include common password blacklist.

Fixes #351
```

**Another example:**
```
fix(auth): Resolve token blacklist MD5 vulnerability

Replace MD5 hashing with SHA-256 for cryptographically secure
token blacklist cache keys.

Closes #429
```

### Subject Line Guidelines

- Use imperative mood: "Add feature" not "Added feature"
- Don't end with a period
- Keep it under 50 characters when possible
- Reference issue numbers at the end: `Fixes #123`, `Closes #456`

## Pull Request Process

### Before Creating a PR

1. **Ensure your code passes all tests**:
   ```bash
   composer test
   ```

2. **Run code style checks**:
   ```bash
   vendor/bin/php-cs-fixer fix
   composer analyse
   ```

3. **Update documentation** if needed
   - Add/update API documentation
   - Update relevant guide files
   - Add comments for complex code

4. **Rebase your branch** on latest `main`:
   ```bash
   git fetch upstream
   git rebase upstream/main
   ```

### Creating a Pull Request

1. Go to your fork on GitHub
2. Click "New Pull Request"
3. Select your feature branch
4. Target the `main` branch
5. Fill in the PR template

### Pull Request Title

Use the same format as commit messages:
- `feat: Add feature description`
- `fix: Fix bug description`

### Pull Request Description

Include the following sections:

**Summary**
Brief description of what the PR does

**Changes**
- List of major changes
- New files added
- Files modified
- Files deleted

**Testing**
- How the changes were tested
- Test coverage added
- Manual testing performed

**Breaking Changes**
List any breaking changes and migration steps

**Related Issues**
Reference the issue being resolved: `Fixes #123` or `Closes #456`

### Pull Request Checklist

Before submitting, ensure:

- [ ] Code follows project style guidelines
- [ ] Tests pass locally (`composer test`)
- [ ] Code style checks pass (`composer cs-fix`, `composer analyse`)
- [ ] Self-review completed
- [ ] Comments added for complex code
- [ ] Documentation updated
- [ ] No merge conflicts with main branch
- [ ] Commit messages follow guidelines

### Review Process

1. **Automated checks** will run on your PR:
   - Tests
   - Code style
   - Static analysis
   - Security audits

2. **Code review** by maintainers:
   - Address review comments promptly
   - Be open to feedback and suggestions
   - Update your PR based on review feedback

3. **Approval and merge**:
   - Once approved and all checks pass, maintainers will merge
   - Your PR will be merged into the `main` branch

## Testing

### Running Tests

Run the full test suite:
```bash
composer test
```

Run specific test files:
```bash
vendor/bin/co-phpunit tests/Feature/AuthServiceTest.php
```

### Writing Tests

We expect comprehensive test coverage:

1. **Unit tests** for services and business logic
2. **Feature tests** for API endpoints and workflows
3. **Integration tests** for complex workflows

#### Test Organization

```
tests/
├── Unit/           # Unit tests for individual classes
├── Feature/         # Feature tests for API endpoints
└── bootstrap.php    # Test bootstrap file
```

#### Test Guidelines

- Write descriptive test names: `test_user_can_reset_password_with_valid_token()`
- Use factory classes to create test data
- Clean up after tests (rollback database transactions)
- Test both success and failure scenarios
- Aim for meaningful assertions, don't just assert `true`

### Test Coverage

We aim for **80%+ code coverage**. Ensure your changes include:
- Tests for new features
- Updated tests for refactored code
- Tests for edge cases and error conditions

## Code Review

### As a Reviewer

When reviewing code:

1. **Check for correctness** - Does it work as intended?
2. **Verify security** - Are there security vulnerabilities?
3. **Review performance** - Are there performance concerns?
4. **Check code style** - Does it follow project standards?
5. **Test coverage** - Are tests adequate?
6. **Documentation** - Is the code documented?

### As a Contributor

When receiving review feedback:

1. **Be respectful** - Assume good intentions
2. **Ask questions** - Clarify if you don't understand feedback
3. **Make changes** - Address review comments promptly
4. **Learn from it** - Use feedback to improve your skills

## Getting Help

If you need help:

1. Check existing [documentation](docs/)
2. Review [open issues](https://github.com/sulhicmz/malnu-backend/issues)
3. Join discussions in the issue tracker
4. Ask questions in issues with the `question` label

## Reporting Security Issues

For security vulnerabilities, please see our [Security Policy](SECURITY.md) for responsible disclosure guidelines.

## License

By contributing, you agree that your contributions will be licensed under the [MIT License](LICENSE).

---

Thank you for contributing to Malnu Backend! 🎉