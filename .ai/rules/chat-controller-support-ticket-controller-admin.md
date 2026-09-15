---
paths:
  - 'app/Services/{ChatService,SupportTicketService}.php,app/Http/Controllers/{ChatController,SupportTicketController,Admin/{ChatController,SupportController}}.php'
---

# Chat Controller Support Ticket Controller Admin

## Chat/Support are shared controllers with per-role view sections, no websockets
Chat and Support are single flat controllers (`ChatController`, `SupportTicketController`) used by customer/business/saler alike — `buyer_id`/`seller_id`/`user_id` just mean "whoever is on that side," not a role. Each computes `layoutFor()` (`layouts.customer`/`layouts.business`/`layouts.saler`), `routePrefix` (`account.`/`business.`/`saler.`), and `section` (`account-content` for the customer layout since `layouts.customer` wraps `layouts.app` and yields that section name; plain `content` for business/saler which extend `layouts.app`-style directly) and passes all three to the view — `resources/views/chat/*.blade.php` and `support/*.blade.php` use `@section($section)` (a dynamic Blade section name) so one file serves all three portals. Don't hardcode `@section('content')` in those shared views — it silently renders nothing on the customer layout.

Chat has no websockets/Pusher — `chat.poll` (`GET /chat/{conversation}/poll/{afterId}`, registered flat outside any portal prefix) is polled via vanilla `fetch()`+`setInterval` from `chat/show.blade.php`'s inline script and marks messages read as a side effect. `chat.store`/`chat.poll`/`chat.attachment`/`chat.start` route names are NOT portal-prefixed (registered once under the general `auth` middleware group) — only `messages.index`/`chat.show`/`support.*` are prefixed per-portal to match each layout's nav placeholders.
