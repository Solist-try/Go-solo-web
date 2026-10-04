# Go Solo

A calm home for people living independently.

Go Solo. Not Alone.

Go Solo helps people build bigger lives while living independently. Living alone is not the problem. Living on hold is.

The path is a Seed, then Out There, then Campfire, then a Waypoint, then another Seed. There are no popularity counts and no rankings.

## Stack

- Next.js App Router
- TypeScript
- Tailwind CSS
- shadcn/ui
- Supabase Auth, Postgres, and Storage

## Run the preview

```bash
npm install
npm run dev
```

Without Supabase environment variables, Go Solo keeps accounts in this browser and fills the room with a small cast of members so the loop can be walked immediately. Registration, email confirmation, password reset, onboarding, and every member action work in that preview.

## Connect Supabase

1. Create a Supabase project.
2. Copy `.env.example` to `.env.local` and add the project URL and anon key.
3. In the SQL editor, run `supabase/migrations/0001_init.sql`, then `supabase/seed.sql`.
4. In Authentication, enable email sign-in.
5. Add these redirect URLs:
   - `http://localhost:3000/auth/callback`
   - your production `/auth/callback`
6. Restart the dev server.

The app then uses Supabase Auth for registration, login, logout, email verification, and password reset. Profiles, interests, waypoints, seeds, SAME partnerships, skill swaps, Out There posts, campfire posts, comments, reactions, and notifications are stored in Postgres with row-level security. Profile photos go to the `avatars` bucket created by the migration.

Protected routes are enforced in `proxy.ts`. Member pages also wait until email verification and onboarding are complete.

## Product map

- `/` public home
- `/seeds` and `/waypoints` can be explored before joining
- `/register`, `/login`, `/verify-email`, `/forgot-password`, `/reset-password`
- `/onboarding`
- `/dashboard`, `/out-there`, `/campfire`, `/profile`, `/settings`
- `/experiences`, `/partnerships`, `/first-night-kits` are marked coming soon and stay closed

There are no follower counts, like counts, or rankings. Reactions are named: Inspired me, I relate, Interesting perspective.

## Steward desk

The preview includes a steward account:

- Email: `steward@gosolo.example`
- Password: `gosolo-steward`

Sign in, then open Steward. The desk reads the room, edits seeds and waypoints, moderates Out There and Campfire, and changes the public words. In the preview those words live in this browser. With Supabase, run `supabase/migrations/0002_admin.sql` and set `profiles.role` to `admin` for the steward.
