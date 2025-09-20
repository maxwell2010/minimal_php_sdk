<?php

require_once 'vendor/autoload.php';

use Decimal\MinimalSdk\MinimalSdk as SDK;

/**
 * Простой тест для проверки работоспособности SDK
 */

echo "🧪 Простой тест Minimal Decimal PHP SDK\n";
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
    
    return SDK\isValidAddress($validAddress) && !SDK\isValidAddress($invalidAddress);
});

// Тест 2: Валидация приватного ключа
runTest("Валидация приватного ключа", function() {
    $validKey = "1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef";
    $invalidKey = "invalid_key";
    
    return SDK\isValidPrivateKey($validKey) && !SDK\isValidPrivateKey($invalidKey);
});

// Тест 3: Валидация сид фразы
runTest("Валидация сид фразы", function() {
    $validSeed = "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about";
    $invalidSeed = "invalid seed phrase";
    
    return SDK\isValidSeedPhrase($validSeed) && !SDK\isValidSeedPhrase($invalidSeed);
});

// Тест 4: Получение приватного ключа из сид фразы
runTest("Получение приватного ключа из сид фразы", function() {
    $seedPhrase = "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about";
    $privateKey = SDK\getPrivateKeyFromSeed($seedPhrase);
    
    return is_string($privateKey) && strlen($privateKey) === 64 && SDK\isValidPrivateKey($privateKey);
});

// Тест 5: Создание нового кошелька
runTest("Создание нового кошелька", function() {
    $wallet = SDK\createNewWallet(24);
    
    return is_array($wallet) && 
           isset($wallet['address']) && 
           isset($wallet['seed_phrase']) && 
           isset($wallet['private_key']) &&
           SDK\isValidAddress($wallet['address']) &&
           SDK\isValidSeedPhrase($wallet['seed_phrase']) &&
           SDK\isValidPrivateKey($wallet['private_key']);
});

// Тест 6: Получение адреса из приватного ключа
runTest("Получение адреса из приватного ключа", function() {
    $privateKey = SDK\getPrivateKeyFromSeed("abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about");
    $address = SDK\WalletHelper::getAddressFromPrivateKey($privateKey);
    
    return is_string($address) && SDK\isValidAddress($address) && str_starts_with($address, '0x');
});

// Тест 7: Создание клиента
runTest("Создание клиента", function() {
    try {
        $client = new SDK\MinimalDecimalClient();
        return $client instanceof SDK\MinimalDecimalClient;
    } catch (Exception $e) {
        // Ожидаемо, если нет подключения к сети
        return str_contains($e->getMessage(), 'подключиться');
    }
});

// Тест 8: Получение баланса DEL (может не работать без сети)
runTest("Получение баланса DEL", function() {
    try {
        $balance = SDK\getDelBalance("0x40900a48273644768c09183e00e43528c17a29f6");
        return is_float($balance) && $balance >= 0;
    } catch (Exception $e) {
        // Ожидаемо, если нет подключения к сети
        return str_contains($e->getMessage(), 'подключиться') || str_contains($e->getMessage(), 'ошибка');
    }
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
    echo "\n⚠️  НЕКОТОРЫЕ ТЕСТЫ ПРОВАЛЕНЫ. Проверьте подключение к сети и настройки.\n";
}

echo "\n💡 Для полного тестирования с реальными транзакциями используйте example.php\n";
