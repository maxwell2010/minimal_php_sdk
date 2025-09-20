<?php

require_once 'vendor/autoload.php';

use Decimal\MinimalSdk\MinimalSdk as SDK;

/**
 * Пример использования Minimal Decimal PHP SDK
 */

echo "🚀 Пример использования Minimal Decimal PHP SDK\n";
echo str_repeat("=", 50) . "\n";

// Параметры для тестирования
$seedPhrase = "Ваша сид фраза"; // Замените на реальную сид фразу
$recipient = "0x40900a48273644768c09183e00e43528c17a29f6";

try {
    // Получаем приватный ключ и адрес
    $privateKey = SDK\getPrivateKeyFromSeed($seedPhrase);
    $senderAddress = SDK\WalletHelper::getAddressFromPrivateKey($privateKey);
    
    echo "📤 Адрес отправителя: {$senderAddress}\n";
    echo "📥 Адрес получателя: {$recipient}\n";
    
    // Проверяем баланс DEL
    echo "\n💰 Проверка баланса DEL...\n";
    $delBalance = SDK\getDelBalance($senderAddress);
    echo "   Баланс DEL: {$delBalance}\n";
    
    // Проверяем баланс токена
    echo "\n🪙 Проверка баланса токена...\n";
    $tokenInfo = SDK\getTokenBalanceBySymbol("fridaycoin", $senderAddress);
    if ($tokenInfo !== null) {
        echo "   Токен: {$tokenInfo['symbol']}\n";
        echo "   Адрес: {$tokenInfo['address']}\n";
        echo "   Баланс: {$tokenInfo['balance']}\n";
    } else {
        echo "   Токен fridaycoin не найден\n";
    }
    
    // Отправляем DEL
    echo "\n📤 Отправка DEL...\n";
    $delResult = SDK\sendDel(
        seedPhrase: $seedPhrase,
        recipientAddress: $recipient,
        amount: 5,
        message: "Отправка из minimal_php_sdk created @Maxwell2019"
    );

    if ($delResult['success']) {
        echo "   ✅ DEL отправлен успешно!\n";
        echo "   📋 Хеш: {$delResult['tx_hash']}\n";
        echo "   🔢 Блок: {$delResult['block_number']}\n";
        echo "   ⛽ Газ: {$delResult['gas_used']}\n";
        echo "   💰 Баланс после: {$delResult['balance_after']} DEL\n";
    } else {
        echo "   ❌ Ошибка отправки DEL: {$delResult['error']}\n";
    }
    
    // Отправляем токен
    echo "\n🪙 Отправка токена...\n";
    $tokenResult = SDK\sendTokenBySymbol(
        seedPhrase: $seedPhrase,
        tokenSymbol: "fridaycoin",
        recipientAddress: $recipient,
        amount: 11
    );
    
    if ($tokenResult['success']) {
        echo "   ✅ Токен отправлен успешно!\n";
        echo "   📋 Хеш: {$tokenResult['tx_hash']}\n";
        echo "   🔢 Блок: {$tokenResult['block_number']}\n";
        echo "   ⛽ Газ: {$tokenResult['gas_used']}\n";
        echo "   🪙 Баланс токена после: {$tokenResult['token_balance_after']}\n";
        echo "   📝 Информация о токене: {$tokenResult['token_info']['symbol']}\n";
    } else {
        echo "   ❌ Ошибка отправки токена: {$tokenResult['error']}\n";
    }
    
    echo "\n✅ Пример завершен успешно!\n";
    
} catch (Exception $e) {
    echo "❌ Ошибка в примере: " . $e->getMessage() . "\n";
    echo "Стек вызовов:\n" . $e->getTraceAsString() . "\n";
}

// Дополнительные примеры
echo "\n" . str_repeat("=", 50) . "\n";
echo "🔧 Дополнительные примеры:\n\n";

// Создание нового кошелька
echo "📝 Создание нового кошелька:\n";
try {
    $newWallet = SDK\createNewWallet(24);
    echo "   Адрес: {$newWallet['address']}\n";
    echo "   Сид фраза: {$newWallet['seed_phrase']}\n";
    echo "   Приватный ключ: {$newWallet['private_key']}\n";
} catch (Exception $e) {
    echo "   ❌ Ошибка создания кошелька: " . $e->getMessage() . "\n";
}

// Валидация
echo "\n🔍 Валидация:\n";
$testAddress = "0x40900a48273644768c09183e00e43528c17a29f6";
$testPrivateKey = "0x1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef";
$testSeedPhrase = "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about";

echo "   Валидный адрес: " . (SDK\isValidAddress($testAddress) ? "✅" : "❌") . "\n";
echo "   Валидный приватный ключ: " . (SDK\isValidPrivateKey($testPrivateKey) ? "✅" : "❌") . "\n";
echo "   Валидная сид фраза: " . (SDK\isValidSeedPhrase($testSeedPhrase) ? "✅" : "❌") . "\n";

echo "\n🎉 Все примеры выполнены!\n";
