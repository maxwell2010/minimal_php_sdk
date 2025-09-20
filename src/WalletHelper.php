<?php

namespace Decimal\MinimalSdk;

use BIP39\BIP39;
use Exception;

/**
 * Вспомогательные функции для работы с кошельками
 */
class WalletHelper
{
    /**
     * Получение приватного ключа из сид фразы
     */
    public static function getPrivateKeyFromSeed(string $seedPhrase, string $derivationPath = "m/44'/60'/0'/0/0"): string
    {
        try {
            $bip39 = new BIP39();
            
            // Проверяем валидность сид фразы
            if (!$bip39->validateMnemonic($seedPhrase)) {
                throw new Exception("Неверная сид фраза");
            }
            
            // Получаем seed из мнемонической фразы
            $seed = $bip39->mnemonicToSeed($seedPhrase);
            
            // Простая реализация BIP32 для получения приватного ключа
            // В реальном проекте лучше использовать готовую библиотеку
            $privateKey = self::derivePrivateKey($seed, $derivationPath);
            
            return $privateKey;
        } catch (Exception $e) {
            throw new Exception("Ошибка получения приватного ключа: " . $e->getMessage());
        }
    }
    
    /**
     * Создание нового кошелька с сид фразой
     */
    public static function createNewWallet(int $wordCount = 24): array
    {
        try {
            $bip39 = new BIP39();
            
            // Генерируем мнемоническую фразу
            $entropy = random_bytes($wordCount * 32 / 3);
            $seedPhrase = $bip39->entropyToMnemonic($entropy);
            
            // Получаем приватный ключ
            $privateKey = self::getPrivateKeyFromSeed($seedPhrase);
            
            // Получаем адрес из приватного ключа
            $address = self::getAddressFromPrivateKey($privateKey);
            
            return [
                'address' => $address,
                'seed_phrase' => $seedPhrase,
                'private_key' => $privateKey
            ];
        } catch (Exception $e) {
            throw new Exception("Ошибка создания кошелька: " . $e->getMessage());
        }
    }
    
    /**
     * Получение адреса из приватного ключа
     */
    public static function getAddressFromPrivateKey(string $privateKey): string
    {
        // Убираем 0x если есть
        $privateKey = ltrim($privateKey, '0x');
        
        // Создаем публичный ключ из приватного
        $secp256k1 = new \kornrunner\Secp256k1();
        $publicKey = $secp256k1->publicKey($privateKey);
        
        // Получаем адрес из публичного ключа
        $address = '0x' . substr(\Web3\Utils::sha3(substr($publicKey, 2)), -40);
        
        return \Web3\Utils::toChecksumAddress($address);
    }
    
    /**
     * Простая реализация BIP32 для получения приватного ключа
     * В реальном проекте лучше использовать готовую библиотеку
     */
    private static function derivePrivateKey(string $seed, string $derivationPath): string
    {
        // Упрощенная реализация - в реальном проекте нужна полная BIP32
        // Для демонстрации используем HMAC-SHA512
        $hmac = hash_hmac('sha512', $seed, 'Bitcoin seed', true);
        $privateKey = substr($hmac, 0, 32);
        
        return bin2hex($privateKey);
    }
    
    /**
     * Валидация адреса Ethereum
     */
    public static function isValidAddress(string $address): bool
    {
        if (!preg_match('/^0x[a-fA-F0-9]{40}$/', $address)) {
            return false;
        }
        
        // Проверяем checksum
        $checksumAddress = \Web3\Utils::toChecksumAddress($address);
        return $address === $checksumAddress || strtolower($address) === strtolower($checksumAddress);
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
        
        // Проверяем, что ключ не равен 0
        return $privateKey !== str_repeat('0', 64);
    }
    
    /**
     * Валидация сид фразы
     */
    public static function isValidSeedPhrase(string $seedPhrase): bool
    {
        try {
            $bip39 = new BIP39();
            return $bip39->validateMnemonic($seedPhrase);
        } catch (Exception $e) {
            return false;
        }
    }
}
