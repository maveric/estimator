# Laravel Estimator

> **Note:** This README was auto-generated from the project and the installation instructions have not been tested yet. For a detailed project overview and planning document, see [project-goals.txt](project-goals.txt).

A flexible, multi-tenant estimating software designed for professionals who need dynamic job estimation without detailed prints. Unlike traditional "take-off" software, this tool is perfect for small contractors and individuals needing a customizable estimation system.

## Features

- **Dynamic Estimation:** Create estimates without requiring predefined components for every variation
- **Customizable Packages:** Modify packages at the estimate level without affecting base components
- **Multi-tenant Support:** Perfect for companies with multiple departments or separate business units
- **Modern Interface:** Clean, responsive UI built with Laravel, Livewire, and Tailwind CSS

### Core Capabilities

- ✓ Dynamic packages and assemblies
- ✓ Multi-tenant infrastructure
- ✓ User roles and permissions
- ✓ Labor rate management
- ✓ Customizable materials and costs
- ✓ Flexible workflow system

## Getting Started

### Prerequisites

- Docker
- Docker Compose
- Git

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/maveric/estimator.git
   cd estimator
   ```

2. Copy the environment file:
   ```bash
   cp .env.example .env
   ```

3. Start the Docker containers:
   ```bash
   docker-compose up -d
   ```

4. Install dependencies:
   ```bash
   docker-compose exec app composer install
   ```

5. Generate application key:
   ```bash
   docker-compose exec app php artisan key:generate
   ```

6. Run migrations:
   ```bash
   docker-compose exec app php artisan migrate
   ```

## Usage

1. Access the application at `http://localhost`
2. Register a new account
3. Set up your company profile
4. Start creating estimates with:
   - Custom items
   - Predefined assemblies
   - Package templates

## Development

The project uses:
- Laravel (PHP Framework)
- Livewire with Tailwind CSS
- MySQL Database
- Docker for development and deployment

## Contributing

We welcome contributions! Please feel free to submit pull requests.

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Open a Pull Request

## License

This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0) - see the [LICENSE](LICENSE) file for details.

## Current Status

### In Progress
- Enhanced export functionality
- Additional UI/UX improvements
- Documentation updates

### Upcoming Features
- Version control for estimates
- Enhanced collaboration tools
- Supplier pricing integration
- AI-powered suggestions
- Offline support capabilities
