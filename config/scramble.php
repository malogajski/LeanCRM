<?php

return [
    /*
     * Your API path. By default, all routes starting with this path will be added to the docs.
     * If you need to change this behavior, you can add your custom routes resolver using `Scramble::routes()`.
     */
    'api_path'    => 'api',

    /*
     * Your API domain. By default, app domain is used. This is also a part of the default API routes
     * matcher, so when implementing your own, make sure you use this config if needed.
     */
    'api_domain'  => null,

    /*
     * The path where your OpenAPI specification will be exported.
     */
    'export_path' => 'api.json',

    'info'                            => [
        /*
         * API version.
         */
        'version'     => env('API_VERSION', '1.0.0'),

        /*
         * Description rendered on the home page of the API documentation (`/docs/api`).
         */
        'description' => '
# LeanCRM REST API Documentation

Welcome to the LeanCRM API documentation. This is a complete CRM REST API built with Laravel 8, featuring:

## Key Features
- **Multi-tenant Architecture**: Complete team-based data isolation
- **Authentication**: Laravel Sanctum token-based authentication
- **Authorization**: Role-based permissions with Spatie Laravel Permission
- **Advanced Querying**: Filtering, sorting, and pagination support
- **Event System**: Automated notifications for deal stage changes

## Getting Started

### 1. Authentication
All API endpoints (except auth) require authentication via Bearer token:
```
Authorization: Bearer YOUR_ACCESS_TOKEN
```

### 2. Register/Login
- **POST /api/auth/register** - Create new user account
- **POST /api/auth/login** - Get access token

### 3. Available Resources
- **Companies** - Client companies management
- **Contacts** - Individual contacts within companies
- **Deals** - Sales opportunities with pipeline stages
- **Activities** - Tasks and activities linked to any resource
- **Notes** - Notes that can be attached to any resource

### 4. Query Features
All list endpoints support:
- **Filtering**: `?filter[field]=value`
- **Sorting**: `?sort=field` or `?sort=-field` (descending)
- **Including Relations**: `?include=relation1,relation2`
- **Pagination**: `?page[size]=15&page[number]=2`

### 5. Deal Pipeline Stages
- `prospect` - Initial contact/lead
- `qualified` - Qualified opportunity
- `proposal` - Proposal sent
- `won` - Deal closed successfully
- `lost` - Deal lost

### 6. Multi-tenancy
All data is automatically scoped to your team. You can only access data belonging to your team.

## Support
For questions or issues, please refer to the GitHub repository or contact support.
        ',
    ],

    /*
     * Customize Stoplight Elements UI
     */
    'ui'                              => [
        /*
         * Define the title of the documentation's website. App name is used when this config is `null`.
         */
        'title'                     => null,

        /*
         * Define the theme of the documentation. Available options are `light`, `dark`, and `system`.
         */
        'theme'                     => 'light',

        /*
         * Hide the `Try It` feature. Enabled by default.
         */
        'hide_try_it'               => false,

        /*
         * Hide the schemas in the Table of Contents. Enabled by default.
         */
        'hide_schemas'              => false,

        /*
         * URL to an image that displays as a small square logo next to the title, above the table of contents.
         */
        'logo'                      => '',

        /*
         * Use to fetch the credential policy for the Try It feature. Options are: omit, include (default), and same-origin
         */
        'try_it_credentials_policy' => 'include',

        /*
         * There are three layouts for Elements:
         * - sidebar - (Elements default) Three-column design with a sidebar that can be resized.
         * - responsive - Like sidebar, except at small screen sizes it collapses the sidebar into a drawer that can be toggled open.
         * - stacked - Everything in a single column, making integrations with existing websites that have their own sidebar or other columns already.
         */
        'layout'                    => 'responsive',
    ],

    /*
     * The list of servers of the API. By default, when `null`, server URL will be created from
     * `scramble.api_path` and `scramble.api_domain` config variables. When providing an array, you
     * will need to specify the local server URL manually (if needed).
     *
     * Example of non-default config (final URLs are generated using Laravel `url` helper):
     *
     * ```php
     * 'servers' => [
     *     'Live' => 'api',
     *     'Prod' => 'https://scramble.dedoc.co/api',
     * ],
     * ```
     */
    'servers'                         => null,

    /**
     * Determines how Scramble stores the descriptions of enum cases.
     * Available options:
     * - 'description' – Case descriptions are stored as the enum schema's description using table formatting.
     * - 'extension' – Case descriptions are stored in the `x-enumDescriptions` enum schema extension.
     *
     * @see https://redocly.com/docs-legacy/api-reference-docs/specification-extensions/x-enum-descriptions
     * - false - Case descriptions are ignored.
     */
    'enum_cases_description_strategy' => 'description',

    'middleware' => [
        'web',
        // RestrictedDocsAccess::class, // Disabled for development
    ],

    'extensions' => [],
];
