# Admin Guide

Welcome to the MezaTech dashboard. This guide covers the basic workflows that admins rely on.

## Requirements
- PHP 8.2+  
- Composer dependencies installed (`composer install`)  
- Node/npm if you are building the front-end assets (`npm install && npm run build`)
- `.env` configured with API tokens (`BEST_REPAIR_API_TOKEN`, database, etc.)

## Start & Development
1. Run `php artisan serve` (or configure your local webserver) to host the dashboard.
2. Build assets when needed: `npm run build` or `npm run dev`.
3. Use `php artisan migrate` only if you are keeping the local SQLite tables synchronized — most data comes from the external API.

## Key Admin Tasks
- **Products & Variants**: Create/update in Filament under Pricing Management. Select tags from the dropdown to reuse existing tag records. Add feature combinations when editing variants so the external API receives `variantFeatures`.
- **Parts**: Open the Parts section, pick a product filter before saving, and use the metadata field for extra pricing information (JSON).
- **Filters**: Products, variants, and parts tables depend on the product filter in the header. Pick the right product before trying to load associated records.
- **Widgets/Charts**: The dashboard exposes charts such as “Products by Variant”. Use the dropdown in that widget to change the product and load its variants.

## Modules / Resources
- **Brands**: Manage repair brands. Only name and optional image are required; upload a clean logo so it can display in tables.
- **Conditions**: Define condition tiers with name, description, and price modifier. Higher modifiers scale repair pricing in reports.
- **Features**: Add high-level variant dimensions (e.g., color, capacity). Provide a slug for integration stability and annotate with a short description.
- **Tags**: Maintain tag list for reused keywords. Use the slug/description for quick reference when reading API responses.
- **Products**: Product forms expect brand, condition, pricing, and tags. The tag field now offers a searchable multi-select backed by the `tags` catalog.
- **Variants**: Choose the product first, then set prices, stock, and associate feature/value pairs. Each variant persists via the `variantFeatures` repeater so the API understands the combination.
- **Variant Features**: Placeholder resource managed mostly by API sync. You can inspect the table but editing usually happens via Feature/Variant forms.
- **Parts**: Link each part to a product, set numeric price/type, and optionally include structured metadata in the `info` JSON field; Filament casts it automatically.
- **Users**: Admins are handled via Filament’s standard user resource; simply assign roles/credentials as needed.

## Troubleshooting
- API calls may fail if `BEST_REPAIR_API_TOKEN` is stale. Refresh the token and restart the server.
- Clear cached Tableau/Sushi data by deleting the cached SQLite files in `storage/framework/cache` if the dashboard shows stale records.
- Check `storage/logs/laravel.log` for any HTTP errors from calls to `bestrepairegypt.com`.

## Maintenance Tips
- Reset filters or tables with `php artisan filament:reset` if live view components misbehave.
- When linking new API features, follow the `openapi.yaml` spec in the repo for parameter and response structure.
- Keep dependencies fresh with `composer update`/`npm update` and rerun `npm run build`.

## Contacts
- Backend/API: [Your primary backend contact/email]
- Filament UI: [Filament support or maintainer]

## Roles & Permissions

The dashboard implements a Role-Based Access Control (RBAC) system using Filament Shield.

### Role Definitions

| Role | Description | Access Level |
| :--- | :--- | :--- |
| **Super Admin** | Full System Access | Can view, create, update, and delete EVERYTHING. Includes User & Role management. |
| **Editor** | Business Manager | Can view, create, update, and delete all Business Resources (Products, Brands, etc.). **No access** to Users, Roles, or Permissions. |
| **Analyst** | Data Specialist | Can view all Business Resources and Analytics Data. **Read-only** access. |
| **Viewer** | Standard User | Can view all Business Resources. **Read-only** access. **No access** to Analytics. |

### Default Users (Development)

| Role | Email | Password |
| :--- | :--- | :--- |
| Super Admin | `admin@mezatech.com` | `password` |
| Editor | `editor@mezatech.com` | `password` |
| Analyst | `analyst@mezatech.com` | `password` |
| Viewer | `viewer@mezatech.com` | `password` |

