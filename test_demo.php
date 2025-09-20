<?php

require_once 'src/SimpleMinimalSdk.php';

/**
 * Демонстрационный тест Minimal Decimal PHP SDK
 * Работает без внешних зависимостей
 */

echo "🚀 Демонстрация Minimal Decimal PHP SDK\n";
echo str_repeat("=", 50) . "\n";

$testResults = [];
$testCount = 0;
$passedCount = 0;

function runTest($name, $testFunction) {
    global $testResults, $testCount, $passedCount;
    
    $testCount++;
    echo "Тест {$testCount}: {$name}... ";
    
    try {
        $result = $testFunction();
        if ($result) {
            echo "✅ ПРОЙДЕН\n";
            $passedCount++;
            $testResults[] = "✅ {$name}";
        } else {
            echo "❌ ПРОВАЛЕН\n";
            $testResults[] = "❌ {$name}";
        }
    } catch (Exception $e) {
        echo "❌ ОШИБКА: " . $e->getMessage() . "\n";
        $testResults[] = "❌ {$name} - " . $e->getMessage();
    }
}

// Тест 1: Валидация адреса
runTest("Валидация адреса", function() {
    $validAddress = "0x40900a48273644768c09183e00e43528c17a29f6";
    $invalidAddress = "invalid_address";
    
    return SimpleMinimalSdk::isValidAddress($validAddress) && !SimpleMinimalSdk::isValidAddress($invalidAddress);
});

// Тест 2: Валидация приватного ключа
runTest("Валидация приватного ключа", function() {
    $validKey = "1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef";
    $invalidKey = "invalid_key";
    
    return SimpleMinimalSdk::isValidPrivateKey($validKey) && !SimpleMinimalSdk::isValidPrivateKey($invalidKey);
});

// Тест 3: Валидация сид фразы
runTest("Валидация сид фразы", function() {
    $validSeed = "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about";
    $invalidSeed = "invalid seed phrase";
    
    return SimpleMinimalSdk::isValidSeedPhrase($validSeed) && !SimpleMinimalSdk::isValidSeedPhrase($invalidSeed);
});

// Тест 4: Создание нового кошелька
runTest("Создание нового кошелька", function() {
    $wallet = SimpleMinimalSdk::createNewWallet(24);
    
    return is_array($wallet) && 
           isset($wallet['address']) && 
           isset($wallet['seed_phrase']) && 
           isset($wallet['private_key']) &&
           SimpleMinimalSdk::isValidAddress($wallet['address']) &&
           SimpleMinimalSdk::isValidSeedPhrase($wallet['seed_phrase']) &&
           SimpleMinimalSdk::isValidPrivateKey($wallet['private_key']);
});

// Тест 5: Получение приватного ключа из сид фразы
runTest("Получение приватного ключа из сид фразы", function() {
    $seedPhrase = "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about";
    $privateKey = SimpleMinimalSdk::getPrivateKeyFromSeed($seedPhrase);
    
    return is_string($privateKey) && strlen($privateKey) === 64 && SimpleMinimalSdk::isValidPrivateKey($privateKey);
});

// Тест 6: Получение адреса из приватного ключа
runTest("Получение адреса из приватного ключа", function() {
    $privateKey = SimpleMinimalSdk::generatePrivateKey();
    $address = SimpleMinimalSdk::getAddressFromPrivateKey($privateKey);
    
    return is_string($address) && SimpleMinimalSdk::isValidAddress($address) && str_starts_with($address, '0x');
});

// Тест 7: Получение баланса DEL
runTest("Получение баланса DEL", function() {
    $balance = SimpleMinimalSdk::getDelBalance("0x40900a48273644768c09183e00e43528c17a29f6");
    return is_float($balance) && $balance >= 0;
});

// Тест 8: Отправка DEL
runTest("Отправка DEL", function() {
    $result = SimpleMinimalSdk::sendDel(
        "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about",
        "0x40900a48273644768c09183e00e43528c17a29f6",
        0.001,
        "Отправка из minimal_php_sdk created @Maxwell2019"
    );
    
    return is_array($result) && isset($result['success']) && $result['success'] === true;
});

// Тест 9: Отправка токена
runTest("Отправка токена", function() {
    $result = SimpleMinimalSdk::sendTokenBySymbol(
        "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about",
        "fridaycoin",
        "0x40900a48273644768c09183e00e43528c17a29f6",
        100
    );
    
    return is_array($result) && isset($result['success']) && $result['success'] === true;
});

// Тест 10: Отправка приза
runTest("Отправка приза", function() {
    $result = SimpleMinimalSdk::sendPrizeToWinner(
        "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about",
        "0x40900a48273644768c09183e00e43528c17a29f6",
        "DEL",
        1.0
    );
    
    return is_array($result) && isset($result['success']) && $result['success'] === true;
});

echo "\n" . str_repeat("=", 50) . "\n";
echo "📊 РЕЗУЛЬТАТЫ ТЕСТИРОВАНИЯ:\n";
echo "Всего тестов: {$testCount}\n";
echo "Пройдено: {$passedCount}\n";
echo "Провалено: " . ($testCount - $passedCount) . "\n";
echo "Процент успеха: " . round(($passedCount / $testCount) * 100, 2) . "%\n\n";

echo "📋 ДЕТАЛЬНЫЕ РЕЗУЛЬТАТЫ:\n";
foreach ($testResults as $result) {
    echo "   {$result}\n";
}

if ($passedCount === $testCount) {
    echo "\n🎉 ВСЕ ТЕСТЫ ПРОЙДЕНЫ УСПЕШНО!\n";
} else {
    echo "\n⚠️  НЕКОТОРЫЕ ТЕСТЫ ПРОВАЛЕНЫ.\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "🔧 ДЕМОНСТРАЦИЯ ФУНКЦИОНАЛЬНОСТИ:\n\n";

// Создание нового кошелька
echo "📝 Создание нового кошелька:\n";
$newWallet = SimpleMinimalSdk::createNewWallet(24);
echo "   Адрес: {$newWallet['address']}\n";
echo "   Сид фраза: {$newWallet['seed_phrase']}\n";
echo "   Приватный ключ: {$newWallet['private_key']}\n\n";

// Проверка баланса
echo "💰 Проверка баланса DEL:\n";
$balance = SimpleMinimalSdk::getDelBalance("0x40900a48273644768c09183e00e43528c17a29f6");
echo "   Баланс DEL: {$balance}\n\n";

// Отправка DEL
echo "📤 Отправка DEL:\n";
$delResult = SimpleMinimalSdk::sendDel(
    "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about",
    "0x40900a48273644768c09183e00e43528c17a29f6",
    5.0,
    "Отправка из minimal_php_sdk created @Maxwell2019"
);

if ($delResult['success']) {
    echo "   ✅ DEL отправлен успешно!\n";
    echo "   📋 Хеш: {$delResult['tx_hash']}\n";
    echo "   🔢 Блок: {$delResult['block_number']}\n";
    echo "   ⛽ Газ: {$delResult['gas_used']}\n";
    echo "   💰 Баланс после: {$delResult['balance_after']} DEL\n";
    echo "   📝 Сообщение: {$delResult['message']}\n";
} else {
    echo "   ❌ Ошибка отправки DEL: {$delResult['error']}\n";
}

echo "\n🪙 Отправка токена:\n";
$tokenResult = SimpleMinimalSdk::sendTokenBySymbol(
    "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about",
    "fridaycoin",
    "0x40900a48273644768c09183e00e43528c17a29f6",
    11
);

if ($tokenResult['success']) {
    echo "   ✅ Токен отправлен успешно!\n";
    echo "   📋 Хеш: {$tokenResult['tx_hash']}\n";
    echo "   🔢 Блок: {$tokenResult['block_number']}\n";
    echo "   ⛽ Газ: {$tokenResult['gas_used']}\n";
    echo "   🪙 Баланс токена после: {$tokenResult['token_balance_after']}\n";
    echo "   📝 Токен: {$tokenResult['token_info']['symbol']}\n";
} else {
    echo "   ❌ Ошибка отправки токена: {$tokenResult['error']}\n";
}

echo "\n🎉 Демонстрация завершена успешно!\n";
echo "💡 Это упрощенная версия SDK для демонстрации. Для полной функциональности установите зависимости через Composer.\n";
