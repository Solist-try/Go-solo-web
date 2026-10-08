# Install Go Solo on shared hosting

The steward trial lives at **https://steward.gosolo.co.network**.

On that hosting account the database is `gosolo-313930486f`. The database user is the same name. In `config.php` on the server, set the host to `localhost`. The hostname in the MySQL panel does not resolve on the public internet, and MySQL is not open from outside the account, so the import is done in phpMyAdmin while you are logged into the panel.

Go Solo runs on ordinary PHP hosting. The copy you upload is the `php` folder in this repository. It does not need Node.js, Composer, Redis, Docker, or a separate database host.

You need:

- PHP 8.2 or newer
- MySQL or MariaDB
- Apache with `mod_rewrite` and `.htaccess` allowed

## 1. Create the database

In the hosting panel, open MySQL Databases and create a database and a user. Give that user every privilege on the database.

Write down four values from the panel:

- host
- database name
- user
- password

If the site and the database are on the same hosting account, the host is often `localhost`. If a connection with `localhost` fails, use the host name shown in the MySQL panel instead.

Keep the password in `config.php` on the server only. Do not put it in git, in a message, or in this file.

## 2. Import the tables

Open phpMyAdmin, select the new database, and import `sql/install.sql`.

That file creates the tables, the starting steward account, the three waypoints, the seed shelf, the reading room, and the words the site shows. It does not invent members, Out There stories, or campfire conversations.

Importing it again drops those tables and starts over. Do that only on a new site.

If the site is already installed and you are adding discussions, replies, and reading-room links, import `sql/update-rooms.sql` once instead. It adds tables and one story column. It does not delete stories, members, or the reading room. The script records `rooms-2026-10-08` in `schema_updates`. Importing that file again is safe. The site does not run this update on an ordinary page view.

If the site is already installed and you want each waypoint to keep its own sitting line, Food for Thought introduction, discussion prompt, button, and empty line, import `sql/update-content.sql` once as well. Shared page wording does not need that file. The script records `content-2026-10-08` in `schema_updates`. Importing it again is safe.

If the site is already installed and you want private conversations, import `sql/update-talk.sql` once as well. It adds the conversation tables, member blocks, and the three communication choices. Members who have not chosen yet stay on shared context. It does not delete stories, members, or the reading room. The script records `talk-2026-10-08` and `talk-choice-2026-10-08` in `schema_updates`. Importing it again is safe.

If the site is already installed and you want seed, skill swap, and introduction lifecycles, import `sql/update-life.sql` after `update-talk.sql`. It adds statuses, pause choices, life seasons, outcomes, and a history of status changes. Existing growing seeds stay active or resting. An introduction a steward had already approved becomes open, with both people treated as having agreed. It does not delete stories, members, or conversations. The script records `life-2026-10-08` in `schema_updates`. Importing it again is safe. Until it is imported, the new buttons stay hidden and the current pages keep working.

## 3. Upload the site

Upload the **contents** of the `php` folder into `public_html`, so `index.php` sits next to `public_html` itself, not inside another folder.

The upload includes `app`, `assets`, `sql`, `uploads`, `views`, `.htaccess`, `index.php`, and `config.example.php`.

On the server, copy `config.example.php` to `config.php`. For this account it should read:

```php
return [
    'db_host' => 'localhost',
    'db_name' => 'gosolo-313930486f',
    'db_user' => 'gosolo-313930486f',
    'db_pass' => 'the-password-from-the-mysql-panel',
    'site_url' => 'https://steward.gosolo.co.network',
];
```

Put the password from the MySQL panel in `db_pass`. Leave it only in this file on the server.

In the hosting panel, set **steward.gosolo.co.network** to PHP 8.2. Upload into the folder that subdomain uses. If the domain is still on an older PHP, the site will say so instead of showing a blank server error.

`app`, `sql`, and `config.php` are blocked from being opened in a browser. PHP can still read them.

Make `uploads/avatars`, `uploads/covers`, and `uploads/reading` writable by PHP.

## 4. Sign in and change the password

Open the site and log in.

- Email: `marge@gosolo.co.network`
- Password: `change-this-chair`

Then open Account and choose a new password. The Steward Desk reminds you while the starting password is still in use.

That account is the founder. From Steward Desk you can edit the house, members, seeds, matches, stories, the reading room, contact notes, and reports without changing code.

## 5. If the site lives in a subfolder

When `index.php` is in `public_html/gosolo/`, add this line under `RewriteEngine On` in `.htaccess`:

```
RewriteBase /gosolo/
```

Use the real folder name.

## Password reset mail

Forgot password sends mail with PHP `mail()`. Some hosts block that until the address is allowed. If a reset note never arrives, open the member in Steward Desk and set a new password there. Share it in a way you trust, and ask them to change it.

## What “active” means

The overview counts a member as active when their account is active and they have opened the site in the last 30 days.
