# 🎮 CraftInvite

> Personalized invitation system for Minecraft servers. Generate unique invite links for each player, complete with a 3D skin viewer, your server IP, and a Discord button.

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.0+-777BB4?style=flat-square&logo=php&logoColor=white"/>
  <img src="https://img.shields.io/badge/MySQL-MariaDB-4479A1?style=flat-square&logo=mysql&logoColor=white"/>
  <img src="https://img.shields.io/badge/License-MIT-green?style=flat-square"/>
  <img src="https://img.shields.io/badge/PRs-welcome-brightgreen?style=flat-square"/>
</p>

---

## ✨ What it does

Instead of sending a plain IP to your players, send them a **custom animated invitation letter** with:

- Their **Minecraft skin rendered in 3D** (interactive, rotatable)
- Your **server IP** with a one-click copy button
- A **Join Discord** button
- Their username on the envelope seal

Each invitation is tied to a **unique token**, so every link is personal and exclusive.

---

## 📸 Preview

| Closed envelope | Open invitation |
|---|---|
| Player sees a sealed letter with their name | Click reveals the 3D skin, server IP, and Discord button |

---

## 🚀 Quick Start

### Requirements

- PHP 8.0+
- MySQL or MariaDB
- A web server (Apache, Nginx, etc.)
- PDO PHP extension enabled

### Installation

**1. Clone the repository**

```bash
git clone https://github.com/your-username/craftinvite.git
cd craftinvite
```

**2. Import the database**

```bash
mysql -u your_user -p your_database < craftinvite.sql
```

**3. Configure your database connection**

Edit `config/db.php`:

```php
$host = 'localhost';
$db   = 'your_database_name';
$user = 'your_db_user';
$pass = 'your_db_password';
```

**4. Create the skins folder**

```bash
mkdir -p assets/skins
chmod 755 assets/skins
```

**5. Set up your admin password**

The default credentials after importing the SQL are:
- **User:** `Admin`
- **Password:** `Admin123`

⚠️ **Change this immediately.** Generate a new bcrypt hash:

```php
// Run this once in a temporary PHP file, then delete it
echo password_hash('your_new_password', PASSWORD_DEFAULT);
```

Then update the `password_hash` field in the `admins` table directly via phpMyAdmin or MySQL CLI.

**6. Log into your dashboard**

```
https://your-domain.com/admin/login.php
```

---

## 🎯 How to use

### Inviting a player

1. Log into the admin dashboard.
2. In the **Invite New Player** section, enter the player's Minecraft username.
3. Optionally upload a custom PNG skin file.
4. Click **Generate Token** — a unique invite link is created.
5. Copy the invite link from the players table and send it to your player.

### Updating server settings

In the **Server Configuration** section of the dashboard, you can change:
- The **server IP** shown on the invitation card
- The **Discord link** used by the join button

Changes take effect immediately for all invitation pages.

---

## 🗂️ Project Structure

```
craftinvite/
├── config/
│   ├── db.php              # PDO database connection
│   └── security.php        # CSRF, rate limiting, sanitization helpers
│
├── admin/
│   ├── auth_check.php      # Session authentication middleware
│   ├── dashboard.php       # Admin panel (manage players & settings)
│   ├── login.php           # Login form
│   ├── logout.php          # Secure session teardown
│   └── admin.js            # Copy invite link, skin preview
│
├── assets/
│   ├── img/                # Logo and favicon
│   └── skins/              # Uploaded player skins (PNG)
│
├── index.php               # Public invitation page (?t=TOKEN)
└── craftinvite.sql         # Database schema and initial data
```

---

## 🔐 Security

CraftInvite is built with security in mind out of the box:

| Threat | Protection |
|---|---|
| SQL Injection | PDO prepared statements throughout |
| XSS | `htmlspecialchars()` on all output |
| CSRF | Session token with `hash_equals()` on all POST forms |
| Brute Force | Rate limiting: 5 attempts per IP per 15 minutes |
| Session Fixation | `session_regenerate_id(true)` on successful login |
| Malicious uploads | Real MIME type check via `finfo` (PNG only) |
| Password storage | bcrypt via `password_hash()` / `password_verify()` |

---

## ⚙️ Customization

### Changing the server branding

Edit the CSS variables in `index.php` to match your server's colors:

```css
:root {
    --accent: #dca337;                     /* Main accent color */
    --Color1: rgba(254, 254, 7, 0.7);      /* Title glow color */
    --Color2: #72bd4cff;                   /* Copy button color */
}
```

### Using a custom logo

Replace `assets/img/logo.png` and `assets/img/favicon.ico` with your own server logo and favicon.

### Default skin fallback

If no skin is uploaded for a player, CraftInvite automatically fetches their skin from [Minotar](https://minotar.net/) using their Minecraft username.

---

## 🛠️ Tech Stack

- **Backend:** PHP 8, PDO/MySQL
- **Frontend:** Vanilla HTML5, CSS3, JavaScript (ES6)
- **3D Skin Rendering:** [skinview3d](https://github.com/bs-community/skinview3d)
- **Default Skins:** [Minotar API](https://minotar.net/)

---

## 🤝 Contributing

Contributions are welcome! Feel free to open an issue or submit a pull request.

Some ideas for improvements:
- [ ] Email delivery of invite links
- [ ] Invite expiration dates
- [ ] Multiple admin accounts
- [ ] Skin preview in the dashboard
- [ ] Dark/light theme toggle for the dashboard
- [ ] Per-player custom messages on the invitation card

---

## 📄 License

MIT License — free to use, modify, and distribute. See [LICENSE](LICENSE) for details.

---

*Built with ❤️ for the Minecraft community.*
