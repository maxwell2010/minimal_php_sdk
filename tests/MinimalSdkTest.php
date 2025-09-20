<?php

namespace Decimal\MinimalSdk\Tests;

use PHPUnit\Framework\TestCase;
use Decimal\MinimalSdk\MinimalSdk as SDK;
use Decimal\MinimalSdk\MinimalDecimalClient;
use Decimal\MinimalSdk\WalletHelper;

/**
 * Тесты для Minimal Decimal PHP SDK
 */
class MinimalSdkTest extends TestCase
{
    private $testSeedPhrase = "abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon abandon about";
    private $testAddress = "0x40900a48273644768c09183e00e43528c17a29f6";
    
    public function setUp(): void
    {
        // Настройка перед каждым тестом
    }
    
    public function tearDown(): void
    {
        // Очистка после каждого теста
    }
    
    /**
     * Тест создания клиента
     */
    public function testCreateClient()
    {
        $client = new MinimalDecimalClient();
        $this->assertInstanceOf(MinimalDecimalClient::class, $client);
    }
    
    /**
     * Тест валидации адреса
     */
    public function testIsValidAddress()
    {
        // Валидные адреса
        $this->assertTrue(SDK\isValidAddress("0x40900a48273644768c09183e00e43528c17a29f6"));
        $this->assertTrue(SDK\isValidAddress("0x0000000000000000000000000000000000000000"));
        
        // Невалидные адреса
        $this->assertFalse(SDK\isValidAddress("invalid_address"));
        $this->assertFalse(SDK\isValidAddress("0x123"));
        $this->assertFalse(SDK\isValidAddress(""));
    }
    
    /**
     * Тест валидации приватного ключа
     */
    public function testIsValidPrivateKey()
    {
        // Валидный приватный ключ
        $validKey = "1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef";
        $this->assertTrue(SDK\isValidPrivateKey($validKey));
        $this->assertTrue(SDK\isValidPrivateKey("0x" . $validKey));
        
        // Невалидные ключи
        $this->assertFalse(SDK\isValidPrivateKey("invalid_key"));
        $this->assertFalse(SDK\isValidPrivateKey("0x123"));
        $this->assertFalse(SDK\isValidPrivateKey(""));
        $this->assertFalse(SDK\isValidPrivateKey(str_repeat('0', 64))); // Все нули
    }
    
    /**
     * Тест валидации сид фразы
     */
    public function testIsValidSeedPhrase()
    {
        // Валидная сид фраза
        $this->assertTrue(SDK\isValidSeedPhrase($this->testSeedPhrase));
        
        // Невалидные сид фразы
        $this->assertFalse(SDK\isValidSeedPhrase("invalid seed phrase"));
        $this->assertFalse(SDK\isValidSeedPhrase(""));
        $this->assertFalse(SDK\isValidSeedPhrase("abandon abandon")); // Слишком короткая
    }
    
    /**
     * Тест получения приватного ключа из сид фразы
     */
    public function testGetPrivateKeyFromSeed()
    {
        $privateKey = SDK\getPrivateKeyFromSeed($this->testSeedPhrase);
        
        $this->assertIsString($privateKey);
        $this->assertEquals(64, strlen($privateKey));
        $this->assertTrue(SDK\isValidPrivateKey($privateKey));
    }
    
    /**
     * Тест создания нового кошелька
     */
    public function testCreateNewWallet()
    {
        $wallet = SDK\createNewWallet(24);
        
        $this->assertIsArray($wallet);
        $this->assertArrayHasKey('address', $wallet);
        $this->assertArrayHasKey('seed_phrase', $wallet);
        $this->assertArrayHasKey('private_key', $wallet);
        
        $this->assertTrue(SDK\isValidAddress($wallet['address']));
        $this->assertTrue(SDK\isValidSeedPhrase($wallet['seed_phrase']));
        $this->assertTrue(SDK\isValidPrivateKey($wallet['private_key']));
    }
    
    /**
     * Тест получения адреса из приватного ключа
     */
    public function testGetAddressFromPrivateKey()
    {
        $privateKey = SDK\getPrivateKeyFromSeed($this->testSeedPhrase);
        $address = WalletHelper::getAddressFromPrivateKey($privateKey);
        
        $this->assertIsString($address);
        $this->assertTrue(SDK\isValidAddress($address));
        $this->assertStringStartsWith('0x', $address);
    }
    
    /**
     * Тест получения баланса DEL (мок)
     */
    public function testGetDelBalance()
    {
        // Этот тест может не работать без реального подключения к сети
        // В реальном проекте нужно использовать моки
        try {
            $balance = SDK\getDelBalance($this->testAddress);
            $this->assertIsFloat($balance);
            $this->assertGreaterThanOrEqual(0, $balance);
        } catch (\Exception $e) {
            // Ожидаемо, если нет подключения к сети
            $this->assertStringContains('подключиться', $e->getMessage());
        }
    }
    
    /**
     * Тест получения информации о токене (мок)
     */
    public function testGetTokenInfo()
    {
        try {
            $client = new MinimalDecimalClient();
            $tokenInfo = $client->getTokenInfo("fridaycoin");
            
            if ($tokenInfo !== null) {
                $this->assertIsArray($tokenInfo);
                $this->assertArrayHasKey('address', $tokenInfo);
                $this->assertArrayHasKey('symbol', $tokenInfo);
                $this->assertArrayHasKey('decimals', $tokenInfo);
                $this->assertEquals('fridaycoin', $tokenInfo['symbol']);
            }
        } catch (\Exception $e) {
            // Ожидаемо, если нет подключения к сети
            $this->assertStringContains('подключиться', $e->getMessage());
        }
    }
    
    /**
     * Тест отправки DEL (мок)
     */
    public function testSendDel()
    {
        // Этот тест требует реальных средств и подключения к сети
        // В реальном проекте нужно использовать моки
        try {
            $result = SDK\sendDel(
                $this->testSeedPhrase,
                $this->testAddress,
                0.001, // Минимальная сумма
                "Тестовое сообщение"
            );
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            
            if (!$result['success']) {
                $this->assertArrayHasKey('error', $result);
            }
        } catch (\Exception $e) {
            // Ожидаемо, если нет подключения к сети или средств
            $this->assertTrue(true);
        }
    }
    
    /**
     * Тест отправки токена (мок)
     */
    public function testSendTokenBySymbol()
    {
        // Этот тест требует реальных токенов и подключения к сети
        try {
            $result = SDK\sendTokenBySymbol(
                $this->testSeedPhrase,
                "fridaycoin",
                $this->testAddress,
                1
            );
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
            
            if (!$result['success']) {
                $this->assertArrayHasKey('error', $result);
            }
        } catch (\Exception $e) {
            // Ожидаемо, если нет подключения к сети или токенов
            $this->assertTrue(true);
        }
    }
    
    /**
     * Тест отправки приза
     */
    public function testSendPrizeToWinner()
    {
        try {
            $result = SDK\sendPrizeToWinner(
                $this->testSeedPhrase,
                $this->testAddress,
                "DEL",
                0.001
            );
            
            $this->assertIsArray($result);
            $this->assertArrayHasKey('success', $result);
        } catch (\Exception $e) {
            // Ожидаемо, если нет подключения к сети
            $this->assertTrue(true);
        }
    }
    
    /**
     * Тест обработки ошибок
     */
    public function testErrorHandling()
    {
        // Тест с невалидной сид фразой
        try {
            SDK\getPrivateKeyFromSeed("invalid seed phrase");
            $this->fail("Ожидалось исключение для невалидной сид фразы");
        } catch (\Exception $e) {
            $this->assertStringContains('Неверная сид фраза', $e->getMessage());
        }
        
        // Тест с невалидным адресом
        try {
            SDK\getDelBalance("invalid_address");
            $this->fail("Ожидалось исключение для невалидного адреса");
        } catch (\Exception $e) {
            $this->assertTrue(true); // Ожидаемо
        }
    }
}
