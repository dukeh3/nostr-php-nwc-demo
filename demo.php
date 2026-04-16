<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/vendor/autoload.php';

use dsbaars\nostr\Nip47\NwcClient;
use dsbaars\nostr\Nip47\NwcUri;
use dsbaars\nostr\Nip47\Exception\NwcException;

// ─── Load NWC URI from .env or environment ───────────────────────────────────

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        putenv(trim($line));
    }
}

$nwcUri = getenv('NWC_URI');
if (!$nwcUri || str_contains($nwcUri, 'REPLACE_ME')) {
    echo "Error: Set NWC_URI in .env (see .env.example)\n";
    exit(1);
}

// ─── Connect and query ───────────────────────────────────────────────────────

echo "nostr-php-nwc demo\n";
echo "==================\n\n";

try {
    $uri = new NwcUri($nwcUri);
    echo "Wallet pubkey : " . $uri->getWalletPubkey() . "\n";
    echo "Relay         : " . implode(', ', $uri->getRelays()) . "\n\n";

    $client = new NwcClient($uri);
    $client->setEncryption('nip44_v2');

    // 1. Get info
    echo "→ get_info\n";
    $info = $client->getWalletInfo();
    if ($info->isSuccess()) {
        $alias = $info->getAlias();
        if ($alias) echo "  Alias   : $alias\n";
        $network = $info->getNetwork();
        if ($network) echo "  Network : $network\n";
        echo "  Methods : " . implode(', ', $info->getMethods()) . "\n";
    } else {
        echo "  Failed: " . $info->getErrorMessage() . "\n";
    }

    // 2. Get balance
    echo "\n→ get_balance\n";
    $balance = $client->getBalance();
    if ($balance->isSuccess()) {
        echo "  Balance : " . number_format($balance->getBalanceInSats()) . " sats\n";
        echo "          : " . number_format($balance->getBalance()) . " msats\n";
    } else {
        echo "  Failed: " . $balance->getErrorMessage() . "\n";
    }

    echo "\nDone.\n";

} catch (NwcException $e) {
    echo "NWC error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
