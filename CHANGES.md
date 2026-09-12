# Manage Pages Admin UI — What Changed

Apply on top of your current merged project (the one with the `route()`/
Ziggy fixes and layout fixes already applied).

## Files to REPLACE

- `app/Http/Controllers/Admin/PageController.php` — added `index()`,
  `create()`, `store()`; `edit()`/`preview()`/`updateDraft()`/`publish()`
  behavior is unchanged except `publish()` now redirects to
  `admin.pages.index` instead of just `back()`.
- `app/Policies/PagePolicy.php` — unchanged, included for completeness.
- `resources/js/Layouts/AdminLayout.vue` — "Pages" is now a real link
  with active-state highlighting.
- `resources/js/Pages/Admin/Pages/Builder.vue` — added a "← Pages" link
  in the toolbar.

## Files to ADD

- `app/Http/Requests/Admin/StorePageRequest.php`
- `resources/js/Pages/Admin/Pages/Index.vue`
- `resources/js/Pages/Admin/Pages/Create.vue`
- `tests/Feature/PagesAdminUiTest.php`

## Routes to update

In `routes/web.php`, replace the existing 4-line Page Builder block
(inside the `role:...` group) with the contents of `routes/admin-pages.php`
in this zip — it's the same routes plus the 3 new ones (`index`, `create`,
`store`), in a cleaner order.

## The complete flow this enables

```
/admin/dashboard → Pages (sidebar, now a real link)
  → /admin/pages (list, search, empty state)
    → Create Page → /admin/pages/create
      → submit → redirects straight to /admin/pages/{id}/builder
        → add sections, Save Draft
        → Preview → /admin/pages/{id}/preview → "Back to Builder"
        → Publish → redirects to /admin/pages (list)
          → guest site now shows the published page
```
