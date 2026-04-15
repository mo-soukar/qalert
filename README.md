# 🚨 QAlert (Laravel Queue Monitor)

A simple, expressive tool to monitor your Laravel background jobs and
get notified on failures instantly via Telegram and other channels.

------------------------------------------------------------------------

## 🚀 Quick Start

### 1. Install via Composer

Run the following command in your terminal:

``` bash
composer require soukar/qalert
```

------------------------------------------------------------------------

### 2. Publish Configuration

Publish the config file to your project's config directory to customize
channels and tokens:

``` bash
php artisan vendor:publish --tag=qalert
```

------------------------------------------------------------------------

### 3. Configure your Environment (.env)

Add your credentials to your `.env` file to activate the alerts:

``` env
# Master switch to enable/disable alerts
QALERT_ENABLED=true

# Telegram Bot Credentials
TELEGRAM_BOT_TOKEN=your_bot_token_here
TELEGRAM_CHAT_ID=your_chat_id_here

# Project name to show in the alert message
APP_NAME="My Awesome Service"
```

------------------------------------------------------------------------
## 📝 Custom Logging Channel

You can now use **QAlert** as a standard Laravel logging channel. This allows you to send manual logs or specific application errors directly to your configured channels (like Telegram).

### Setup Logging
Add the `qalert` driver to your `config/logging.php` file:

```php
'channels' => [
    // ... other channels
    
    'qalert' => [
        'driver' => 'qalert',
    ],
],
```
---------------------------------------------------------------
## ⚙️ Configuration File (`config/qalert.php`)

After publishing, you can fine-tune the behavior of the package:

  ------------------------------------------------------------------------------
Key                   Description                    Default
  --------------------- ------------------------------ -------------------------
`enabled`             Globally enable or disable     `true`
notifications.

`project`             The project name that appears  `APP_NAME`
in the alert title.

`channels.telegram`   Stores the bot token and chat  `.env`
ID.

`default_channel`     The fallback channel if none   `telegram`
is specified.
  ------------------------------------------------------------------------------

------------------------------------------------------------------------

## 🛠 How it Works

QAlert automatically listens for the `JobFailed` event in Laravel.

When a job fails:

-   It gathers the job details and the exception message.
-   It processes the event through the configured channels in the
    `ChannelManager`.
-   It sends a formatted notification to each active channel (e.g.,
    Telegram).

------------------------------------------------------------------------

## 💓 Queue Heartbeat (Stay Alive)

QAlert isn't just for failed jobs; it also ensures your Queue Workers are actually running. It uses a **Heartbeat** system that leverages Laravel's default Scheduling system.

### How to activate it:
1. Ensure your Laravel Scheduler is running (`* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1`).
2. QAlert will automatically schedule a "Ping" job every hour (or your custom interval).
3. If the Heartbeat job isn't processed within the expected timeframe, you'll receive a **"Queue Stalled"** alert on Telegram.

This ensures that even if the `supervisor` stops or the connection to `Redis/Database` is lost, you'll still be the first to know.

------------------------------------------------------------------------

## 🤝 Contributing

Contributions are welcome!\
If you'd like to add a new channel (Slack, Discord, SMS), feel free to
open a Pull Request.

------------------------------------------------------------------------

## ❤️ Author

Developed with ❤️ by **Mohammad Soukar**
