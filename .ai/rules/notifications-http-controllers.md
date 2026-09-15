---
paths:
  - 'app/Notifications/*.php,app/Http/Controllers/{NotificationController,NotificationPreferenceController}.php'
---

# Notifications Http Controllers

## All notifications route mail through ChannelsFromPreference — never hardcode via()
Every notification class uses `App\Notifications\Concerns\ChannelsFromPreference` and its `via()` must read `return $this->channels($notifiable, NotificationCategory::X);` — never `return ['mail', 'database'];` directly. The trait checks `$notifiable->wantsEmailFor($category)` (opt-out model: no `notification_preferences` row means email stays on) and drops the `mail` channel when the user disabled that category at `/notification-preferences`. `database` is always included — that's what powers the notification bell/center, it's never user-disable-able.

When adding a new notification class: add/reuse a `NotificationCategory` case, `use ChannelsFromPreference;`, and write `via()` using `$this->channels(...)` from day one — retrofitting 16 existing classes after the fact (this session) required editing every one of them individually because a shell-based batch edit silently dropped the `use` import lines (the two `use App\Enums\NotificationCategory;` / `use App\Notifications\Concerns\ChannelsFromPreference;` statements) while the trait-usage and body substitutions succeeded — `php -l` doesn't catch a missing `use` import (that's a runtime "class not found", not a parse error), so verify with `class_uses()` in tinker, not just syntax linting, after any bulk edit to PHP class bodies.

The bell/notification center itself (`resources/views/components/dashboard-topbar.blade.php` for admin/business/saler/verifier, `resources/views/layouts/partials/public-header.blade.php` for customer/guest pages) queries `auth()->user()->unreadNotifications()` directly inline in the Blade view — it is NOT passed in from every controller (that would require touching dozens of controllers). `NotificationController`/`NotificationPreferenceController` compute `layoutFor()`/`sectionFor()` the same way `ChatController`/`SupportTicketController` do (see the chat/support rule file) so one `notifications/index.blade.php` and one `notification-preferences/edit.blade.php` serve every portal.
