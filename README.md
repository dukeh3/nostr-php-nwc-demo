# nostr-php-nwc-demo

A minimal demo project showing how to use [nostr-php-nwc](https://github.com/dukeh3/nostr-php-nwc) to interact with a Lightning wallet via Nostr Wallet Connect (NIP-47).

## Prerequisites

- PHP 8.1+
- Composer
- An NWC-compatible wallet service (e.g. [ldk-controller](https://github.com/dukeh3/ldk-controller))

## Quick Start

```bash
composer install
cp .env.example .env
# Edit .env with your NWC connection URI
php demo.php
```

## What It Does

1. **Get Info** - Show wallet alias and supported methods
2. **Get Balance** - Display available sats
3. **Make Invoice** - Create a 100 sat invoice
4. **List Transactions** - Show recent payment history

## License

MIT
