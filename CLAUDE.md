# NPPC Website - Claude Code Context

> **Server:** 104.238.162.40 | **Admin:** /admin | **Deploy:** `cd /var/www/NPPC-Website && git pull origin main`

## Project Overview

Laravel 10 website for the **National Political Prisoner Coalition** (NPPC). Public-facing advocacy site + Filament 3 admin panel. Manages prisoner profiles, articles, events, petitions, podcasts, and more.

## Tech Stack

- **Backend:** PHP 8.3, Laravel 10, Filament 3.2
- **Frontend:** Blade templates, Livewire 3.5, Tailwind CSS 3.4, Vite 5
- **Database:** MySQL (UUIDs as primary keys on all models)
- **Payments:** Stripe (donations)
- **Admin:** Filament at `/admin` (requires `is_admin = true`)
- **Queue:** sync (runs inline, no worker needed)
- **Separate Vue app:** `vue/` directory (Ant Design Vue dashboard, built to `public/vue/`)

## Key Architecture Decisions

- All models extend `App\Models\Model` which uses UUID PKs (`$incrementing = false`, `$keyType = 'string'`)
- Base model sets `$guarded = []` -- models are unguarded by default
- `FormSubmission` and `CalendarEntry` extend Laravel's base Model directly (not the app's base)
- Slugs are generated via `App\Models\Concerns\HasSlug` trait (auto-generates on create)
- File uploads use the `public` disk -- always set `->disk('public')` on Filament FileUpload/ImageColumn

## Models & Relationships

### Prisoner System (core domain)
- **Prisoner** -- has many `PrisonerCase`, `PodcastEpisode`, `CalendarEntry`
- **PrisonerCase** -- belongs to `Prisoner`, belongs to `Institution`
- **Institution** -- has many `PrisonerCase`

### Content
- **Article** -- belongs to `Category`, belongs to `Author`, has Spatie tags
- **Page** -- self-referential parent/children (navigation hierarchy)
- **Topic** -- self-referential parent/children, has slug, published scope
- **HistoryEra** -- has many `HistoryTopic`
- **Event** -- has scopes: `upcoming`, `past`, `published`
- **PodcastEpisode** -- belongs to `Prisoner`
- **CalendarEntry** -- belongs to `Prisoner`

### Engagement
- **Petition** -- has many `PetitionSignature`
- **FormSubmission** -- stores arbitrary form data as JSON (`data` column)
- **EmailSubscriber** -- email signups

### Other
- **Staff** -- group field: `staff` or `board`
- **Product** -- store items, has `featured` and `published` scopes
- **Quote**, **Faq**, **Timeline**, **AnnualReport** -- simple content models
- **Author** -- has many `Article`
- **User** -- has `is_admin` boolean for Filament access

## Routes (routes/web.php)

| URL | Controller Method |
|-----|-------------------|
| `/` | `SiteController@home` |
| `/news/{slug}` | `SiteController@article` |
| `/search` | `SiteController@search` |
| `/prisoner/{slug}` | `SiteController@prisoner` |
| `/topics/{slug?}` | `SiteController@topics` |
| `/calendar` | `SiteController@calendar` |
| `/history` | `SiteController@history` |
| `/timeline` | `SiteController@timeline` |
| `/map` | `SiteController@map` |
| `/faq` | `SiteController@faq` |
| `/staff` | `SiteController@staff` |
| `/board-of-directors` | `SiteController@boardOfDirectors` |
| `/podcast` | `SiteController@podcast` |
| `/store` | `SiteController@store` |
| `/events` | `SiteController@events` |
| `/volunteer` | `SiteController@volunteer` |
| `/annual-report` | `SiteController@annualReport` |
| `/petition/{slug}` | `SiteController@petitionPage` |
| `POST /petition/{slug}/sign` | `SiteController@petitionSign` |
| `POST /form/{form}` | `FormSubmissionController@submit` (contact, volunteer) |
| `POST /sign-up` | Creates `EmailSubscriber` |
| `GET /donate-callback` | `DonateController@callback` |
| `/{slug}` | `SiteController@page` (catch-all) |

## Filament Admin Resources

AnnualReport, Article, Author, CalendarEntry, Category, ClaudeSession, ContactSubmission, EmailSubscriber, Event, Faq, HistoryEra, Institution, Page, Petition, PodcastEpisode, Prisoner, PrisonerCase, Product, Quote, Staff, Topic, VolunteerSubmission

## Artisan Commands

- `airtable:import` -- import prisoners from Airtable proxy
- `calendar:generate` -- auto-generate calendar entries from case dates
- `articles:import-from-site` -- scrape articles from live site
- `prisoners:download-photos` -- download photos from Airtable CDN
- `prisoners:generate-slugs` -- generate URL slugs for prisoners
- `prisoners:split-names` -- split full names into first/middle/last
- `prisoners:match-photos` -- match local photo files to prisoners

## File Structure

```
app/
  Console/Commands/     # Artisan commands
  Domains/              # Business logic (Stripe)
  Filament/Resources/   # Admin panel CRUD
  Http/Controllers/     # SiteController, DonateController, FormSubmissionController
  Jobs/                 # RunClaudeCode
  Livewire/             # Donation, ArticlesGrid
  Models/               # Eloquent models (25+)
  Services/             # ClaudeSessionService
resources/views/
  pages/                # Page templates (map, calendar, events, etc.)
  sections/             # Reusable sections (podcast-player, etc.)
  layout/               # Header, footer, nav
  livewire/             # Livewire component views
  filament/pages/       # Custom Filament views
database/migrations/    # All migrations (UUID PKs, no auto-increment)
config/claude.php       # Claude Code integration config
vue/                    # Separate Vue 3 dashboard app
```

## Common Tasks

### Adding data (prisoners, cases, articles, events, etc.)
**NEVER use migrations to insert data.** Migrations are only for schema changes (adding tables, columns, indexes). To add data, use Eloquent directly:

```php
// Example: adding a prisoner with a case
$prisoner = Prisoner::create([...]);
$institution = Institution::firstOrCreate(['name' => '...'], [...]);
PrisonerCase::create(['prisoner_id' => $prisoner->id, 'institution_id' => $institution->id, ...]);
```

This applies to all content: prisoners, cases, articles, events, pages, staff, quotes, FAQs, etc. Always use the model's `::create()` method or `::firstOrCreate()` to insert records.

### Adding content (articles, pages, events)
Use Eloquent `::create()`. Articles need a title and body (required). Pages use a slug-based routing system -- the `/{slug}` catch-all route serves DB pages.

### Working with images
Always use `Storage::url($path)` in Blade templates. In Filament, always add `->disk('public')` to FileUpload and ImageColumn components. Images are stored in `storage/app/public/` and symlinked to `public/storage/`.

### Environment variables
Key env vars: `STRIPE_SK`, `STRIPE_PK`, `RECAPTCHA_SECRET`, `MAPBOX_TOKEN`, `CLAUDE_BINARY`, `CLAUDE_REPO_PATH`. See `.env.example` for the full list.

## Coding Conventions

- PSR-12 style, enforced by Laravel Pint
- Final classes on most models and controllers
- UUID primary keys everywhere (never auto-increment)
- Rich text uses Filament TipTap editor
- Blade templates use `@extends('app')` with `@section('head')` and `@section('body')`
- Always escape output with `{{ }}` -- only use `{!! !!}` for trusted rich text from admin
- Git branches for Claude sessions: `claude/session-{id}-{timestamp}`
