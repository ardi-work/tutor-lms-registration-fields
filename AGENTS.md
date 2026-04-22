# PROJECT KNOWLEDGE BASE

**Generated:** 2026-04-21
**Project:** Tutor LMS Custom Registration Fields

## OVERVIEW
WordPress plugin extending Tutor LMS registration form with admin-managed custom fields.

## STRUCTURE
```
tutor-custom-registration-fields.php  # Entry point
├── includes/
│   ├── functions.php               # Field CRUD, sanitization, validation
│   └── class-tcf-field.php         # Field class
├── admin/
│   └── admin-page.php             # Admin UI
└── frontend/
    └── frontend-hooks.php      # Registration form hooks
```

## WHERE TO LOOK
| Task | Location |
|------|----------|
| Field CRUD | `includes/functions.php` |
| Form rendering | `frontend/frontend-hooks.php` |
| Admin UI | `admin/admin-page.php` |
| Plugin init | `tutor-custom-registration-fields.php` |

## CONVENTIONS
- Prefix all functions: `tcf_`
- Constants: `TCF_*` (e.g., `TCF_VERSION`, `TCF_OPTION_KEY`)
- Option key: `tutor_custom_fields_settings`
- Field key format: lowercase + numbers + underscore only

## ANTI-PATTERNS
- No input validation on `$_POST` before `functions.php` calls
- No nonce verification in AJAX handlers

## COMMANDS
```
# No build/test commands (WordPress plugin)
# Activate in WordPress admin → Plugins → Activate
```

## NOTES
- Requires Tutor LMS to be installed
- Custom fields stored in WordPress options table
- Profile display requires Tutor LMS hooks (not fully implemented)