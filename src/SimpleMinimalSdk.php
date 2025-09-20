<?php

/**
 * Упрощенная версия Minimal Decimal PHP SDK
 * Без внешних зависимостей для демонстрации
 */

class SimpleMinimalSdk
{
    // Константы для Decimal Chain
    const DECIMAL_RPC_URL = "http://94.130.66.14/web3/";
    const DECIMAL_CHAIN_ID = 75;
    const TOKEN_CENTER_ADDRESS = "0x9113ba675aa8f2ef0c068cee2cdabab95b6437fb";
    
    /**
     * Валидация адреса Ethereum
     */
    public static function isValidAddress(string $address): bool
    {
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
            return false;
        }
        return true;
    }
    
    /**
     * Валидация приватного ключа
     */
    public static function isValidPrivateKey(string $privateKey): bool
    {
        $privateKey = ltrim($privateKey, '0x');
        
        if (!preg_match('/^[a-fA-F0-9]{64}$/', $privateKey)) {
            return false;
        }
        
        return $privateKey !== str_repeat('0', 64);
    }
    
    /**
     * Простая валидация сид фразы (проверка количества слов)
     */
    public static function isValidSeedPhrase(string $seedPhrase): bool
    {
        $words = explode(' ', trim($seedPhrase));
        return count($words) >= 12 && count($words) <= 24;
    }
    
    /**
     * Генерация простого приватного ключа (для демонстрации)
     */
    public static function generatePrivateKey(): string
    {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Простое получение адреса из приватного ключа (упрощенная версия)
     */
    public static function getAddressFromPrivateKey(string $privateKey): string
    {
        $privateKey = ltrim($privateKey, '0x');
        
        // Упрощенная версия - в реальном проекте нужна полная криптография
        $hash = hash('sha256', $privateKey);
        $address = '0x' . substr($hash, 0, 40);
        
        return $address;
    }
    
    /**
     * Создание нового кошелька
     */
    public static function createNewWallet(int $wordCount = 24): array
    {
        $privateKey = self::generatePrivateKey();
        $address = self::getAddressFromPrivateKey($privateKey);
        
        // Генерируем простую сид фразу (для демонстрации)
        $words = [];
        for ($i = 0; $i < $wordCount; $i++) {
            $words[] = 'word' . ($i + 1);
        }
        $seedPhrase = implode(' ', $words);
        
        return [
            'address' => $address,
            'seed_phrase' => $seedPhrase,
            'private_key' => $privateKey
        ];
    }
    
    /**
     * Получение приватного ключа из сид фразы (упрощенная версия)
     */
    public static function getPrivateKeyFromSeed(string $seedPhrase): string
    {
        if (!self::isValidSeedPhrase($seedPhrase)) {
            throw new Exception("Неверная сид фраза");
        }
        
        // Упрощенная версия - в реальном проекте нужна полная BIP39
        return hash('sha256', $seedPhrase);
    }
    
    /**
     * Отправка DEL (мок версия)
     */
    public static function sendDel(string $seedPhrase, string $recipientAddress, float $amount, string $message = ""): array
    {
        try {
            // Валидация
            if (!self::isValidSeedPhrase($seedPhrase)) {
                return ['success' => false, 'error' => 'Неверная сид фраза'];
            }
            
            if (!self::isValidAddress($recipientAddress)) {
                return ['success' => false, 'error' => 'Неверный адрес получателя'];
            }
            
            if ($amount <= 0) {
                return ['success' => false, 'error' => 'Сумма должна быть больше 0'];
            }
            
            // Получаем приватный ключ и адрес отправителя
            $privateKey = self::getPrivateKeyFromSeed($seedPhrase);
            $senderAddress = self::getAddressFromPrivateKey($privateKey);
            
            // Мок транзакции
            $txHash = '0x' . bin2hex(random_bytes(32));
            $blockNumber = rand(1000000, 9999999);
            $gasUsed = rand(21000, 100000);
            
            return [
                'success' => true,
                'tx_hash' => $txHash,
                'block_number' => $blockNumber,
                'gas_used' => $gasUsed,
                'sender' => $senderAddress,
                'recipient' => $recipientAddress,
                'amount' => $amount,
                'message' => $message,
                'balance_after' => rand(0, 1000) // Мок баланса
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Отправка токена (мок версия)
     */
    public static function sendTokenBySymbol(string $seedPhrase, string $tokenSymbol, string $recipientAddress, float $amount): array
    {
        try {
            // Валидация
            if (!self::isValidSeedPhrase($seedPhrase)) {
                return ['success' => false, 'error' => 'Неверная сид фраза'];
            }
            
            if (!self::isValidAddress($recipientAddress)) {
                return ['success' => false, 'error' => 'Неверный адрес получателя'];
            }
            
            if ($amount <= 0) {
                return ['success' => false, 'error' => 'Сумма должна быть больше 0'];
            }
            
            // Получаем приватный ключ и адрес отправителя
            $privateKey = self::getPrivateKeyFromSeed($seedPhrase);
            $senderAddress = self::getAddressFromPrivateKey($privateKey);
            
            // Мок информации о токене
            $tokenInfo = [
                'address' => '0x' . bin2hex(random_bytes(20)),
                'symbol' => $tokenSymbol,
                'decimals' => 18
            ];
            
            // Мок транзакции
            $txHash = '0x' . bin2hex(random_bytes(32));
            $blockNumber = rand(1000000, 9999999);
            $gasUsed = rand(50000, 150000);
            
            return [
                'success' => true,
                'tx_hash' => $txHash,
                'block_number' => $blockNumber,
                'gas_used' => $gasUsed,
                'sender' => $senderAddress,
                'recipient' => $recipientAddress,
                'amount' => $amount,
                'token_info' => $tokenInfo,
                'token_balance_after' => rand(0, 10000) // Мок баланса токена
            ];
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Отправка приза победителю
     */
    public static function sendPrizeToWinner(string $seedPhrase, string $winnerWallet, string $coinSymbol, float $amount): array
    {
        if ($coinSymbol === "DEL") {
            return self::sendDel($seedPhrase, $winnerWallet, $amount, "Приз лотереи: {$amount} DEL");
        } else {
            return self::sendTokenBySymbol($seedPhrase, $coinSymbol, $winnerWallet, $amount);
        }
    }
    
    /**
     * Получение баланса DEL (мок версия)
     */
    public static function getDelBalance(string $address): float
    {
        if (!self::isValidAddress($address)) {
            throw new Exception("Неверный адрес");
        }
        
        // Мок баланса
        return rand(0, 10000) / 100;
    }
    
    /**
     * Получение баланса токена (мок версия)
     */
    public static function getTokenBalanceBySymbol(string $symbol, string $walletAddress): ?array
    {
        if (!self::isValidAddress($walletAddress)) {
            return null;
        }
        
        // Мок информации о токене
        return [
            'symbol' => $symbol,
            'address' => '0x' . bin2hex(random_bytes(20)),
            'decimals' => 18,
            'balance' => rand(0, 100000) / 100
        ];
    }
}
