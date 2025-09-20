<?php

namespace Decimal\MinimalSdk;

use Exception;

/**
 * Минимальный Decimal SDK
 * 
 * Минимальный SDK для работы с блокчейном Decimal.
 * Содержит только необходимые функции без лишних зависимостей.
 */

// Глобальный клиент
$globalClient = null;

/**
 * Получение глобального клиента
 */
function getClient(): MinimalDecimalClient
{
    global $globalClient;
    if ($globalClient === null) {
        $globalClient = new MinimalDecimalClient();
    }
    return $globalClient;
}

/**
 * Получение баланса DEL
 */
function getDelBalance(string $address): float
{
    $client = getClient();
    return $client->getDelBalance($address);
}

/**
 * Получение баланса токена по символу
 */
function getTokenBalanceBySymbol(string $symbol, string $walletAddress): ?array
{
    $client = getClient();
    
    // Получаем информацию о токене
    $tokenInfo = $client->getTokenInfo($symbol);
    if ($tokenInfo === null) {
        return null;
    }
    
    // Получаем баланс
    $balance = $client->getTokenBalance($tokenInfo['address'], $walletAddress);
    
    return [
        'symbol' => $tokenInfo['symbol'],
        'address' => $tokenInfo['address'],
        'decimals' => $tokenInfo['decimals'],
        'balance' => $balance
    ];
}

/**
 * Отправка DEL с валидацией
 */
function sendDel(string $seedPhrase, string $recipientAddress, float $amount, string $message = ""): array
{
    $client = getClient();
    
    // Получаем приватный ключ
    $privateKey = WalletHelper::getPrivateKeyFromSeed($seedPhrase);
    
    // Отправляем
    $result = $client->sendDelTransaction($recipientAddress, $amount, $privateKey, $message);
    
    if ($result['success']) {
        // Проверяем баланс после отправки
        $senderAddress = WalletHelper::getAddressFromPrivateKey($privateKey);
        $balanceAfter = $client->getDelBalance($senderAddress);
        $result['balance_after'] = $balanceAfter;
    }
    
    return $result;
}

/**
 * Отправка токена по символу с валидацией
 */
function sendTokenBySymbol(string $seedPhrase, string $tokenSymbol, string $recipientAddress, float $amount): array
{
    $client = getClient();
    
    // Получаем информацию о токене
    $tokenInfo = $client->getTokenInfo($tokenSymbol);
    if ($tokenInfo === null) {
        return [
            'success' => false,
            'error' => "Токен {$tokenSymbol} не найден"
        ];
    }
    
    // Получаем приватный ключ
    $privateKey = WalletHelper::getPrivateKeyFromSeed($seedPhrase);
    
    // Отправляем
    $result = $client->sendTokenTransaction(
        $tokenInfo['address'], 
        $recipientAddress, 
        $amount, 
        $privateKey
    );
    
    if ($result['success']) {
        // Проверяем баланс после отправки
        $senderAddress = WalletHelper::getAddressFromPrivateKey($privateKey);
        $balanceAfter = $client->getTokenBalance($tokenInfo['address'], $senderAddress);
        $result['token_balance_after'] = $balanceAfter;
        $result['token_info'] = $tokenInfo;
    }
    
    return $result;
}

/**
 * Получение приватного ключа из сид фразы
 */
function getPrivateKeyFromSeed(string $seedPhrase, string $derivationPath = "m/44'/60'/0'/0/0"): string
{
    return WalletHelper::getPrivateKeyFromSeed($seedPhrase, $derivationPath);
}

/**
 * Создание нового кошелька с сид фразой
 */
function createNewWallet(int $wordCount = 24): array
{
    return WalletHelper::createNewWallet($wordCount);
}

/**
 * Отправка приза победителю
 */
function sendPrizeToWinner(string $seedPhrase, string $winnerWallet, string $coinSymbol, float $amount): array
{
    try {
        if ($coinSymbol === "DEL") {
            // Отправляем DEL
            $result = sendDel($seedPhrase, $winnerWallet, $amount, "Приз лотереи: {$amount} DEL");
        } else {
            // Отправляем токен
            $result = sendTokenBySymbol($seedPhrase, $coinSymbol, $winnerWallet, $amount);
        }
        
        return $result;
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => 'Ошибка отправки приза: ' . $e->getMessage()
        ];
    }
}

/**
 * Валидация адреса
 */
function isValidAddress(string $address): bool
{
    return WalletHelper::isValidAddress($address);
}

/**
 * Валидация приватного ключа
 */
function isValidPrivateKey(string $privateKey): bool
{
    return WalletHelper::isValidPrivateKey($privateKey);
}

/**
 * Валидация сид фразы
 */
function isValidSeedPhrase(string $seedPhrase): bool
{
    return WalletHelper::isValidSeedPhrase($seedPhrase);
}
