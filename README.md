# nostr-php-nwc-demo

Connect to a Lightning node and check your balance — using [nostr-php-nwc](https://github.com/dukeh3/nostr-php-nwc) and the Nostr Wallet Connect protocol (NIP-47).

## Prerequisites

- PHP 8.1+ with `gmp` and `curl` extensions
- Composer
- An NWC-compatible wallet (e.g. [ldk-controller](https://github.com/dukeh3/ldk-controller))

## Setup

```bash
composer install
cp .env.example .env
```

Edit `.env` with your NWC connection URI:

```
NWC_URI=nostr+walletconnect://SERVICE_PUBKEY?relay=wss%3A%2F%2Frelay.example.com&secret=YOUR_SECRET
```

## Run

```bash
php demo.php
```

Example output:

```
nostr-php-nwc demo
==================

Wallet pubkey : db0a960a68b14fcd4bf81b7a456e5d94e122f0416db6d3e9cd5c6f2c945e06d7
Relay         : ws://172.16.10.101:7777

→ get_info
  Alias   : alice
  Network : regtest
  Methods : get_info, get_balance, pay_invoice, make_invoice, list_transactions

→ get_balance
  Balance : 500,000 sats
          : 500,000,000 msats

Done.
```

## How It Works

The demo uses NWC (NIP-47) to talk to a Lightning node over a Nostr relay:

1. Parses the NWC URI to get the wallet's pubkey, relay URL, and client secret
2. Opens a WebSocket to the relay
3. Sends encrypted NWC requests (kind 23194) signed with the client key
4. Receives encrypted responses (kind 23195) from the wallet service
5. Decrypts and displays the results

No REST API, no API keys — just Nostr events over a relay.

## License

MIT
