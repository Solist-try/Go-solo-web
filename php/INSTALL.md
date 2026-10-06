# Install Go Solo on shared hosting

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

## 3. Upload the site

Upload the **contents** of the `php` folder into `public_html`, so `index.php` sits next to `public_html` itself, not inside another folder.

The upload includes `app`, `assets`, `sql`, `uploads`, `views`, `.htaccess`, `index.php`, and `config.example.php`.

On the server, copy `config.example.php` to `config.php` and fill in the four database values:

```php
return [
    'db_host' => 'localhost',
    'db_name' => 'the-name-from-the-panel',
    'db_user' => 'the-user-from-the-panel',
    'db_pass' => 'the-password-from-the-panel',
];
```

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
