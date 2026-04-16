<?php

declare(strict_types=1);
error_reporting(E_ALL & ~E_DEPRECATED);

require_once __DIR__ . '/vendor/autoload.php';

use dsbaars\nostr\Nip47\NwcClient;
use dsbaars\nostr\Nip47\NwcUri;
use dsbaars\nostr\Nip47\Exception\NwcException;

// ─── Load .env ──────────────────────────────────────────────────────────────

$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        putenv(trim($line));
    }
}

$aliceUri = getenv('ALICE_NWC_URI');
$bobUri   = getenv('BOB_NWC_URI');
if (!$aliceUri || !$bobUri) {
    echo "Error: Set ALICE_NWC_URI and BOB_NWC_URI in .env\n";
    exit(1);
}

$AMOUNT_MSATS = 1_000_000; // 1000 sats

// ─── Create clients ─────────────────────────────────────────────────────────

echo "Transfer Demo: Bob → Alice → Bob (1000 sats each way)\n";
echo "=====================================================\n\n";

try {
    $alice = new NwcClient(new NwcUri($aliceUri));
    $alice->setEncryption('nip44_v2');
    $bob = new NwcClient(new NwcUri($bobUri));
    $bob->setEncryption('nip44_v2');

    // ─── Starting balances ──────────────────────────────────────────────

    echo "Starting balances:\n";
    $aliceBal = $alice->getBalance();
    $bobBal   = $bob->getBalance();
    if (!$aliceBal->isSuccess() || !$bobBal->isSuccess()) {
        echo "  Failed to get balances\n";
        echo "  Alice: " . ($aliceBal->getErrorMessage() ?? 'ok') . "\n";
        echo "  Bob:   " . ($bobBal->getErrorMessage() ?? 'ok') . "\n";
        exit(1);
    }
    $aliceStart = $aliceBal->getBalance();
    $bobStart   = $bobBal->getBalance();
    echo "  Alice : " . number_format($aliceStart / 1000) . " sats\n";
    echo "  Bob   : " . number_format($bobStart / 1000) . " sats\n\n";

    // ─── Transfer 1: Bob pays Alice 1000 sats ───────────────────────────

    echo "Step 1: Alice creates invoice for 1000 sats\n";
    $invoice = $alice->makeInvoice($AMOUNT_MSATS, 'transfer-demo: bob->alice');
    if (!$invoice->isSuccess()) {
        echo "  FAILED: " . $invoice->getErrorMessage() . "\n";
        exit(1);
    }
    $bolt11 = $invoice->getInvoice();
    echo "  Invoice: " . substr($bolt11, 0, 40) . "...\n";

    echo "Step 2: Bob pays the invoice\n";
    $payment = $bob->payInvoice($bolt11);
    if (!$payment->isSuccess()) {
        echo "  FAILED: " . $payment->getErrorMessage() . "\n";
        exit(1);
    }
    echo "  Preimage : " . $payment->getPreimage() . "\n";
    echo "  Fees     : " . ($payment->getFeesPaid() ?? 0) . " msats\n\n";

    // ─── Transfer 2: Alice pays Bob 1000 sats ───────────────────────────

    echo "Step 3: Bob creates invoice for 1000 sats\n";
    $invoice2 = $bob->makeInvoice($AMOUNT_MSATS, 'transfer-demo: alice->bob');
    if (!$invoice2->isSuccess()) {
        echo "  FAILED: " . $invoice2->getErrorMessage() . "\n";
        exit(1);
    }
    $bolt11_2 = $invoice2->getInvoice();
    echo "  Invoice: " . substr($bolt11_2, 0, 40) . "...\n";

    echo "Step 4: Alice pays the invoice\n";
    $payment2 = $alice->payInvoice($bolt11_2);
    if (!$payment2->isSuccess()) {
        echo "  FAILED: " . $payment2->getErrorMessage() . "\n";
        exit(1);
    }
    echo "  Preimage : " . $payment2->getPreimage() . "\n";
    echo "  Fees     : " . ($payment2->getFeesPaid() ?? 0) . " msats\n\n";

    // ─── Final balances ─────────────────────────────────────────────────

    echo "Final balances:\n";
    $aliceBal = $alice->getBalance();
    $bobBal   = $bob->getBalance();
    $aliceEnd = $aliceBal->getBalance();
    $bobEnd   = $bobBal->getBalance();
    echo "  Alice : " . number_format($aliceEnd / 1000) . " sats";
    $aliceDiff = $aliceEnd - $aliceStart;
    echo " (" . ($aliceDiff >= 0 ? '+' : '') . number_format($aliceDiff / 1000) . " sats)\n";
    echo "  Bob   : " . number_format($bobEnd / 1000) . " sats";
    $bobDiff = $bobEnd - $bobStart;
    echo " (" . ($bobDiff >= 0 ? '+' : '') . number_format($bobDiff / 1000) . " sats)\n";

    echo "\nDone.\n";

} catch (NwcException $e) {
    echo "NWC error: " . $e->getMessage() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
