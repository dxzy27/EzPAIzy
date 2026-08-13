# EzPAIzy

A web + mobile learning platform for Islamic education. Built with **Laravel** (web/backend) and **Flutter** (mobile app).

## Features

- Teachers create quizzes, flashcards, and upload learning materials
- Students take quizzes, study flashcards, save notes, and track progress
- AI quiz generation (Google Gemini & GPT-4o-mini via OpenRouter)
- Expert system that detects each student's learning style (VARK) and personalises content
- Three roles: **Admin**, **Teacher**, **Student**

## Tech Stack

- **Backend:** PHP 8.1+, Laravel 10, MySQL, Laravel Sanctum
- **Frontend:** Blade + Vite
- **Mobile:** Flutter (Android & iOS)
- **AI:** OpenRouter → Google Gemini 2.5 Flash / GPT-4o-mini
- **Email:** SendGrid

## Installation

```bash
git clone https://github.com/dxzy27/EzPAIzy.git
cd EzPAIzy

composer install
npm install

cp .env.example .env
# Fill in DB credentials and API keys in .env

php artisan key:generate
php artisan migrate --seed
php artisan storage:link

npm run dev
```

If using Laragon, the site will be at `http://ezpaizy.test`. Otherwise run `php artisan serve`.

## Mobile App

```bash
cd ezpaizy_app
flutter pub get
flutter run
```

Update the base URL in `lib/` to point to your Laravel server before running.

## Tests

```bash
php artisan test
```

## License

For educational use.
