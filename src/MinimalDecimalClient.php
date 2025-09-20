<?php

namespace Decimal\MinimalSdk;

use Web3\Web3;
use Web3\Contract;
use Web3\Utils;
use Web3\Eth;
use Web3\Personal;
use Web3\Net;
use Web3\Providers\HttpProvider;
use Web3\RequestManagers\HttpRequestManager;
use Exception;

/**
 * Минимальный клиент для работы с Decimal Chain
 */
class MinimalDecimalClient
{
    private $web3;
    private $eth;
    private $personal;
    private $net;
    
    // Константы для Decimal Chain
    const RPC_URLS = [
        "https://node.decimalchain.com/web3",
        "http://94.130.66.14/web3/",
        "http://168.119.212.76/web3/"
    ];
    
    const DECIMAL_RPC_URL = "http://94.130.66.14/web3/";
    const DECIMAL_CHAIN_ID = 75;
    const TOKEN_CENTER_ADDRESS = "0x9113ba675aa8f2ef0c068cee2cdabab95b6437fb";
    
    // Минимальный ABI для ERC-20 токенов
    const MINIMAL_ERC20_ABI = '[
        {
            "constant": true,
            "inputs": [],
            "name": "decimals",
            "outputs": [{"name": "", "type": "uint8"}],
            "type": "function"
        },
        {
            "constant": true,
            "inputs": [{"name": "_owner", "type": "address"}],
            "name": "balanceOf",
            "outputs": [{"name": "balance", "type": "uint256"}],
            "type": "function"
        },
        {
            "constant": false,
            "inputs": [
                {"name": "_to", "type": "address"},
                {"name": "_value", "type": "uint256"}
            ],
            "name": "transfer",
            "outputs": [{"name": "", "type": "bool"}],
            "type": "function"
        }
    ]';
    
    // ABI для Token Center
    const TOKEN_CENTER_ABI = '[
        {
            "constant": true,
            "inputs": [{"name": "symbol", "type": "string"}],
            "name": "tokens",
            "outputs": [{"name": "", "type": "address"}],
            "type": "function"
        }
    ]';
    
    public function __construct(string $rpcUrl = self::DECIMAL_RPC_URL)
    {
        $this->web3 = new Web3(new HttpProvider(new HttpRequestManager($rpcUrl, 30)));
        $this->eth = $this->web3->eth;
        $this->personal = $this->web3->personal;
        $this->net = $this->web3->net;
        
        // Проверяем подключение
        $this->checkConnection();
    }
    
    private function checkConnection(): void
    {
        $connected = false;
        $this->net->version(function ($err, $version) use (&$connected) {
            if ($err !== null) {
                throw new Exception("Не удалось подключиться к Decimal Chain: " . $err->getMessage());
            }
            $connected = true;
        });
        
        if (!$connected) {
            throw new Exception("Не удалось подключиться к Decimal Chain");
        }
    }
    
    /**
     * Получение баланса DEL
     */
    public function getDelBalance(string $address): float
    {
        $balance = 0;
        $this->eth->getBalance($address, function ($err, $result) use (&$balance) {
            if ($err !== null) {
                throw new Exception("Ошибка получения баланса DEL: " . $err->getMessage());
            }
            $balance = Utils::fromWei($result->value, 'ether');
        });
        
        return (float) $balance;
    }
    
    /**
     * Получение информации о токене по символу
     */
    public function getTokenInfo(string $symbol): ?array
    {
        try {
            $contract = new Contract($this->web3->provider, self::TOKEN_CENTER_ABI);
            $contract->at(self::TOKEN_CENTER_ADDRESS);
            
            $tokenAddress = null;
            $contract->call('tokens', $symbol, function ($err, $result) use (&$tokenAddress) {
                if ($err !== null) {
                    throw new Exception("Ошибка получения адреса токена: " . $err->getMessage());
                }
                $tokenAddress = $result[0];
            });
            
            if ($tokenAddress === "0x0000000000000000000000000000000000000000") {
                return null;
            }
            
            // Получаем информацию о токене
            $tokenContract = new Contract($this->web3->provider, self::MINIMAL_ERC20_ABI);
            $tokenContract->at($tokenAddress);
            
            $decimals = null;
            $tokenContract->call('decimals', function ($err, $result) use (&$decimals) {
                if ($err !== null) {
                    throw new Exception("Ошибка получения decimals токена: " . $err->getMessage());
                }
                $decimals = $result[0];
            });
            
            return [
                'address' => $tokenAddress,
                'symbol' => $symbol,
                'decimals' => (int) $decimals
            ];
        } catch (Exception $e) {
            error_log("Ошибка получения информации о токене {$symbol}: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Получение баланса токена
     */
    public function getTokenBalance(string $tokenAddress, string $walletAddress): float
    {
        try {
            $contract = new Contract($this->web3->provider, self::MINIMAL_ERC20_ABI);
            $contract->at($tokenAddress);
            
            $decimals = null;
            $contract->call('decimals', function ($err, $result) use (&$decimals) {
                if ($err !== null) {
                    throw new Exception("Ошибка получения decimals: " . $err->getMessage());
                }
                $decimals = $result[0];
            });
            
            $balance = null;
            $contract->call('balanceOf', $walletAddress, function ($err, $result) use (&$balance) {
                if ($err !== null) {
                    throw new Exception("Ошибка получения баланса токена: " . $err->getMessage());
                }
                $balance = $result[0];
            });
            
            return (float) $balance / (10 ** $decimals);
        } catch (Exception $e) {
            throw new Exception("Ошибка получения баланса токена: " . $e->getMessage());
        }
    }
    
    /**
     * Отправка DEL
     */
    public function sendDelTransaction(string $toAddress, float $amount, string $privateKey, string $message = ""): array
    {
        try {
            // Получаем адрес отправителя из приватного ключа
            $senderAddress = $this->getAddressFromPrivateKey($privateKey);
            
            // Проверяем баланс
            $balance = $this->getDelBalance($senderAddress);
            if ($balance < $amount) {
                return [
                    'success' => false,
                    'error' => "Недостаточно средств. Нужно: {$amount} DEL, есть: {$balance} DEL"
                ];
            }
            
            // Получаем nonce
            $nonce = $this->getNonce($senderAddress);
            
            // Получаем gas price
            $gasPrice = $this->getGasPrice();
            
            // Создаем транзакцию
            $txParams = [
                'from' => $senderAddress,
                'to' => $toAddress,
                'value' => Utils::toWei($amount, 'ether'),
                'gas' => '0x5208', // 21000
                'gasPrice' => $gasPrice,
                'nonce' => $nonce,
                'chainId' => '0x' . dechex(self::DECIMAL_CHAIN_ID)
            ];
            
            // Добавляем сообщение в data если есть
            if (!empty($message)) {
                $txParams['data'] = '0x' . bin2hex($message);
                $txParams['gas'] = '0x61a8'; // 25000
            }
            
            // Подписываем и отправляем транзакцию
            $signedTx = $this->signTransaction($txParams, $privateKey);
            $txHash = $this->sendRawTransaction($signedTx);
            
            // Ждем подтверждения
            $receipt = $this->waitForTransactionReceipt($txHash);
            
            if ($receipt['status'] === '0x1') {
                return [
                    'success' => true,
                    'tx_hash' => $txHash,
                    'block_number' => hexdec($receipt['blockNumber']),
                    'gas_used' => hexdec($receipt['gasUsed'])
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Транзакция не подтверждена'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Отправка токена
     */
    public function sendTokenTransaction(string $tokenAddress, string $toAddress, float $amount, string $privateKey): array
    {
        try {
            $senderAddress = $this->getAddressFromPrivateKey($privateKey);
            
            // Получаем информацию о токене
            $contract = new Contract($this->web3->provider, self::MINIMAL_ERC20_ABI);
            $contract->at($tokenAddress);
            
            $decimals = null;
            $contract->call('decimals', function ($err, $result) use (&$decimals) {
                if ($err !== null) {
                    throw new Exception("Ошибка получения decimals: " . $err->getMessage());
                }
                $decimals = $result[0];
            });
            
            $amountWei = (string) ($amount * (10 ** $decimals));
            
            // Проверяем баланс токена
            $balance = null;
            $contract->call('balanceOf', $senderAddress, function ($err, $result) use (&$balance) {
                if ($err !== null) {
                    throw new Exception("Ошибка получения баланса токена: " . $err->getMessage());
                }
                $balance = $result[0];
            });
            
            if ($balance < $amountWei) {
                return [
                    'success' => false,
                    'error' => "Недостаточно токенов. Нужно: {$amount}, есть: " . ($balance / (10 ** $decimals))
                ];
            }
            
            // Создаем данные для вызова transfer
            $transferData = $this->encodeTransferData($toAddress, $amountWei);
            
            // Получаем nonce и gas price
            $nonce = $this->getNonce($senderAddress);
            $gasPrice = $this->getGasPrice();
            
            // Создаем транзакцию
            $txParams = [
                'from' => $senderAddress,
                'to' => $tokenAddress,
                'value' => '0x0',
                'gas' => '0x186a0', // 100000
                'gasPrice' => $gasPrice,
                'nonce' => $nonce,
                'chainId' => '0x' . dechex(self::DECIMAL_CHAIN_ID),
                'data' => $transferData
            ];
            
            // Подписываем и отправляем транзакцию
            $signedTx = $this->signTransaction($txParams, $privateKey);
            $txHash = $this->sendRawTransaction($signedTx);
            
            // Ждем подтверждения
            $receipt = $this->waitForTransactionReceipt($txHash);
            
            if ($receipt['status'] === '0x1') {
                return [
                    'success' => true,
                    'tx_hash' => $txHash,
                    'block_number' => hexdec($receipt['blockNumber']),
                    'gas_used' => hexdec($receipt['gasUsed'])
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Транзакция не подтверждена'
                ];
            }
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Получение адреса из приватного ключа
     */
    private function getAddressFromPrivateKey(string $privateKey): string
    {
        // Убираем 0x если есть
        $privateKey = ltrim($privateKey, '0x');
        
        // Создаем публичный ключ из приватного
        $secp256k1 = new \kornrunner\Secp256k1();
        $publicKey = $secp256k1->publicKey($privateKey);
        
        // Получаем адрес из публичного ключа
        $address = '0x' . substr(Utils::sha3(substr($publicKey, 2)), -40);
        
        return Utils::toChecksumAddress($address);
    }
    
    /**
     * Получение nonce
     */
    private function getNonce(string $address): string
    {
        $nonce = null;
        $this->eth->getTransactionCount($address, 'pending', function ($err, $result) use (&$nonce) {
            if ($err !== null) {
                throw new Exception("Ошибка получения nonce: " . $err->getMessage());
            }
            $nonce = $result->value;
        });
        
        return $nonce;
    }
    
    /**
     * Получение gas price
     */
    private function getGasPrice(): string
    {
        $gasPrice = null;
        $this->eth->gasPrice(function ($err, $result) use (&$gasPrice) {
            if ($err !== null) {
                throw new Exception("Ошибка получения gas price: " . $err->getMessage());
            }
            $gasPrice = $result->value;
        });
        
        return $gasPrice;
    }
    
    /**
     * Подписание транзакции
     */
    private function signTransaction(array $txParams, string $privateKey): string
    {
        // Убираем 0x если есть
        $privateKey = ltrim($privateKey, '0x');
        
        // Создаем хеш транзакции для подписи
        $txHash = $this->getTransactionHash($txParams);
        
        // Подписываем хеш
        $secp256k1 = new \kornrunner\Secp256k1();
        $signature = $secp256k1->sign($txHash, $privateKey);
        
        // Создаем подписанную транзакцию
        $r = $signature['r'];
        $s = $signature['s'];
        $v = $signature['recoveryId'] + 27 + (2 * self::DECIMAL_CHAIN_ID);
        
        $signedTx = '0x' . 
            $this->rlpEncode([
                Utils::toHex($txParams['nonce']),
                Utils::toHex($txParams['gasPrice']),
                Utils::toHex($txParams['gas']),
                $txParams['to'],
                Utils::toHex($txParams['value']),
                $txParams['data'] ?? '0x',
                Utils::toHex($v),
                Utils::toHex($r),
                Utils::toHex($s)
            ]);
        
        return $signedTx;
    }
    
    /**
     * Получение хеша транзакции для подписи
     */
    private function getTransactionHash(array $txParams): string
    {
        $data = [
            Utils::toHex($txParams['nonce']),
            Utils::toHex($txParams['gasPrice']),
            Utils::toHex($txParams['gas']),
            $txParams['to'],
            Utils::toHex($txParams['value']),
            $txParams['data'] ?? '0x',
            Utils::toHex(self::DECIMAL_CHAIN_ID),
            '0x',
            '0x'
        ];
        
        $encoded = $this->rlpEncode($data);
        return Utils::sha3($encoded);
    }
    
    /**
     * Простая RLP кодировка (упрощенная версия)
     */
    private function rlpEncode(array $data): string
    {
        $result = '';
        foreach ($data as $item) {
            if (is_string($item)) {
                $item = ltrim($item, '0x');
                if (strlen($item) === 0) {
                    $item = '80';
                } elseif (strlen($item) === 1) {
                    $item = '0' . $item;
                }
                $result .= $item;
            }
        }
        return $result;
    }
    
    /**
     * Отправка сырой транзакции
     */
    private function sendRawTransaction(string $signedTx): string
    {
        $txHash = null;
        $this->eth->sendRawTransaction($signedTx, function ($err, $result) use (&$txHash) {
            if ($err !== null) {
                throw new Exception("Ошибка отправки транзакции: " . $err->getMessage());
            }
            $txHash = $result;
        });
        
        return $txHash;
    }
    
    /**
     * Ожидание подтверждения транзакции
     */
    private function waitForTransactionReceipt(string $txHash, int $timeout = 60): array
    {
        $startTime = time();
        $receipt = null;
        
        while (time() - $startTime < $timeout) {
            $this->eth->getTransactionReceipt($txHash, function ($err, $result) use (&$receipt) {
                if ($err === null && $result !== null) {
                    $receipt = $result;
                }
            });
            
            if ($receipt !== null) {
                return $receipt;
            }
            
            sleep(2);
        }
        
        throw new Exception("Таймаут ожидания подтверждения транзакции");
    }
    
    /**
     * Кодирование данных для вызова transfer
     */
    private function encodeTransferData(string $toAddress, string $amount): string
    {
        // Метод transfer(address,uint256) имеет сигнатуру 0xa9059cbb
        $methodId = 'a9059cbb';
        
        // Кодируем адрес (32 байта)
        $toAddressEncoded = str_pad(ltrim($toAddress, '0x'), 64, '0', STR_PAD_LEFT);
        
        // Кодируем amount (32 байта)
        $amountEncoded = str_pad($amount, 64, '0', STR_PAD_LEFT);
        
        return '0x' . $methodId . $toAddressEncoded . $amountEncoded;
    }
}
