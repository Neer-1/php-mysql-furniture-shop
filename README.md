# Furniture Shop — PHP & MySQL Coursework

Public author identifier: **Neer-1**. A Server-Side Web Technologies coursework prototype using PHP, MySQL, HTML, CSS and JavaScript. It provides catalogue browsing/search, registration/login, a per-user cart and administrator product management. It has no real checkout, orders, payment processing, stock reservation across carts or fulfilment. It is not production-grade commerce software.

## Run locally

See [INSTALLATION.md](INSTALLATION.md) for the tested environment and isolated setup. PHP does not load .env.example automatically. Never use original database dumps or real customer records. The synthetic schema seeds three sample products and zero user/admin accounts. Images are locally generated geometric placeholders, not furniture photographs or product representations.

Later remediation adds password verification/hashing, session rotation, CSRF protection, role/owner checks, input validation, escaped output, native-PDO parameter fixes and transaction-protected cart quantity changes. These changes do not describe the unmodified submitted coursework.

## Completed verification

The preserved isolated Windows run used PHP 8.5.12 with PDO MySQL and MySQL 8.0.45. HTTP checks passed registration/duplicate handling, login/session regeneration, CSRF and role boundaries, admin product CRUD, search/pagination, cart ownership and quantities, and explicit database rollback. Two independent PHP/MySQL connections exercised decrement races from quantities two and one; both passed. Scheduling may serialize those workers, so this is not proof of arbitrary concurrency safety. Browser login, visible AJAX cart summary and empty-cart removal passed. An initial expected-alert browser assertion was a driver expectation error; the actual summary behavior passed its follow-up check. Guarded disposable-schema/account cleanup and process shutdown were recorded.

The final candidate runtime files match both the source inventory and the actual successful test webroot byte-for-byte. No new database/server/browser campaign was run during packaging. The browser coverage is narrow; no comprehensive accessibility, cross-browser, deployment, performance or payment testing is claimed. No production hardening or universal security guarantee is implied.

## Privacy, assets and publication status

All 26 uncleared original images, private dumps/account records, credentials, academic/private documents, test logs and local environments are excluded. ASSET_REPLACEMENTS.json records hashes and provenance for the 26 local geometric replacements. No external CDN scripts, fonts, styles or embedded video remain. Generic social-site links are ordinary outbound links, not accounts or endorsements; clicking them leaves the local site.

See [PROVENANCE.md](PROVENANCE.md). The author explicitly confirmed A11 ownership, individual authorship with no collaborators, and university permission for public publication. GitHub publication was authorized on 2026-11-01. No LICENSE is supplied.
