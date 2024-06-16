# Questionnaire Application

This Laravel application manages questionnaires for physics and chemistry exams. It allows admins to create, list, send invitations for, and collect responses to questionnaires.

## Setup Instructions

1. **Clone the repository:**
   ```bash
   git clone <repository-url>
   cd questionnaire-app
   composer install
   ```
2. **Setup Environment variable** 
    - copy .env.example and save as .env
    - configure mail

    ```bash
    php artisan key:generate
    php artisan migrate --seed
    php artisan serve
    ```
3. Access the application: Open http://localhost:8000 in your web browser.

4. For more info: https://laravel.com/docs/11.x
