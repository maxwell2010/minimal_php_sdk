# Minimal Decimal PHP SDK

Минимальный PHP SDK для работы с блокчейном Decimal. Содержит только необходимые функции без лишних зависимостей.

## Установка

1. Убедитесь, что у вас установлен PHP 7.4 или выше
2. Установите Composer
3. Установите зависимости:

```bash
composer install
```

## Быстрый старт

```php
<?php
require_once 'vendor/autoload.php';

use Decimal\MinimalSdk\MinimalSdk as SDK;

// Получение баланса DEL
$balance = SDK\getDelBalance("0x40900a48273644768c09183e00e43528c17a29f6");
echo "Баланс DEL: {$balance}\n";

// Создание нового кошелька
$wallet = SDK\createNewWallet(24);
echo "Адрес: {$wallet['address']}\n";
echo "Сид фраза: {$wallet['seed_phrase']}\n";

// Отправка DEL
$result = SDK\sendDel(
    seedPhrase: "ваша сид фраза",
    recipientAddress: "0x40900a48273644768c09183e00e43528c17a29f6",
    amount: 1.0,
    message: "Отправка из minimal_php_sdk created @Maxwell2019"
);

if ($result['success']) {
    echo "Транзакция успешна: {$result['tx_hash']}\n";
} else {
    echo "Ошибка: {$result['error']}\n";
}
```

## Основные функции

### Работа с кошельками

- `createNewWallet(int $wordCount = 24)` - Создание нового кошелька
- `getPrivateKeyFromSeed(string $seedPhrase)` - Получение приватного ключа из сид фразы
- `isValidAddress(string $address)` - Валидация адреса
- `isValidPrivateKey(string $privateKey)` - Валидация приватного ключа
- `isValidSeedPhrase(string $seedPhrase)` - Валидация сид фразы

### Работа с балансами

- `getDelBalance(string $address)` - Получение баланса DEL
- `getTokenBalanceBySymbol(string $symbol, string $walletAddress)` - Получение баланса токена по символу

### Отправка транзакций

- `sendDel(string $seedPhrase, string $recipientAddress, float $amount, string $message = "")` - Отправка DEL
- `sendTokenBySymbol(string $seedPhrase, string $tokenSymbol, string $recipientAddress, float $amount)` - Отправка токена
- `sendPrizeToWinner(string $seedPhrase, string $winnerWallet, string $coinSymbol, float $amount)` - Отправка приза

## Примеры использования

### Создание кошелька

```php
$wallet = SDK\createNewWallet(24);
echo "Новый адрес: {$wallet['address']}\n";
echo "Сид фраза: {$wallet['seed_phrase']}\n";
```

### Проверка баланса

```php
$address = "0x40900a48273644768c09183e00e43528c17a29f6";
$delBalance = SDK\getDelBalance($address);
echo "Баланс DEL: {$delBalance}\n";

$tokenInfo = SDK\getTokenBalanceBySymbol("fridaycoin", $address);
if ($tokenInfo) {
    echo "Баланс токена: {$tokenInfo['balance']} {$tokenInfo['symbol']}\n";
}
```

### Отправка DEL

```php
$result = SDK\sendDel(
    seedPhrase: "ваша сид фраза",
    recipientAddress: "0x40900a48273644768c09183e00e43528c17a29f6",
    amount: 5.0,
    message: "Отправка из minimal_php_sdk created @Maxwell2019"
);

if ($result['success']) {
    echo "Транзакция успешна!\n";
    echo "Хеш: {$result['tx_hash']}\n";
    echo "Блок: {$result['block_number']}\n";
    echo "Баланс после: {$result['balance_after']} DEL\n";
} else {
    echo "Ошибка: {$result['error']}\n";
}
```

### Отправка токена

```php
$result = SDK\sendTokenBySymbol(
    seedPhrase: "ваша сид фраза",
    tokenSymbol: "fridaycoin",
    recipientAddress: "0x40900a48273644768c09183e00e43528c17a29f6",
    amount: 100.0
);

if ($result['success']) {
    echo "Токен отправлен успешно!\n";
    echo "Хеш: {$result['tx_hash']}\n";
    echo "Баланс токена после: {$result['token_balance_after']}\n";
} else {
    echo "Ошибка: {$result['error']}\n";
}
```

## Тестирование

### Простой тест

```bash
php test_simple.php
```

### Полный пример

```bash
php example.php
```

### PHPUnit тесты

```bash
./vendor/bin/phpunit tests/
```

## Конфигурация

SDK использует следующие RPC URL по умолчанию:
- https://node.decimalchain.com/web3
- http://94.130.66.14/web3/
- http://168.119.212.76/web3/

Вы можете указать свой RPC URL при создании клиента:

```php
$client = new MinimalDecimalClient("https://your-rpc-url.com");
```

## Безопасность

⚠️ **ВАЖНО**: Никогда не храните сид фразы и приватные ключи в открытом виде в коде. Используйте переменные окружения или безопасное хранилище.

## Зависимости

- PHP 7.4+
- web3p/web3.php - для работы с Ethereum
- kornrunner/secp256k1 - для криптографических операций
- bip39/bip39 - для работы с мнемоническими фразами
- kornrunner/keccak - для хеширования

## Лицензия

MIT License

## Автор

Maxwell2019

## Поддержка

Для вопросов и поддержки создавайте issues в репозитории.
