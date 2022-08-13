<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Provides access to config values
 */
abstract class AbstractConfig
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param SerializerInterface $serializer
     * @param EncryptorInterface $encryptor
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        SerializerInterface $serializer,
        EncryptorInterface $encryptor
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->serializer = $serializer;
        $this->encryptor = $encryptor;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    abstract public function isEnabled(?int $storeId = null): bool;

    /**
     * Get config value for current store
     *
     * @param string $path
     * @param int|null $storeId
     * @return string|null
     */
    protected function getStoreConfigValue(string $path, ?int $storeId = null): ?string
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Returns config flag value
     *
     * @param string $path
     * @param int|null $storeId
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    protected function getStoreConfigFlag(string $path, ?int $storeId = null): bool
    {
        return $this->scopeConfig->isSetFlag($path, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Helper to simply get multiselect selected values as array
     *
     * @param string $path
     * @param int|null $storeId
     * @return array
     */
    protected function getMultiselectValue(string $path, ?int $storeId = null): array
    {
        $value = $this->getStoreConfigValue($path, $storeId);

        if (null === $value || '' === $value) {
            return [];
        }

        $result = explode(',', $value);
        array_walk($result, 'trim');

        return $result;
    }

    /**
     * Helper to simplify getting serialized values as an array
     *
     * @param string $path
     * @param int|null $storeId
     * @return array
     */
    protected function getSerializedValue(string $path, ?int $storeId = null): array
    {
        $jsonValue = $this->getStoreConfigValue($path, $storeId);

        try {
            $result = $this->serializer->unserialize($jsonValue);

            if (false === is_array($result)) {
                $result = [];
            }
        } catch (\Throwable $t) {
            $result = [];
        }

        return $result;
    }

    /**
     * Use it for encrypted values, such as API keys
     * Returns already decrypted value
     *
     * @param string $path
     * @param int|null $storeId
     * @return string
     */
    protected function getEncryptedValue(string $path, ?int $storeId = null): string
    {
        $value = (string)$this->getStoreConfigValue($path, $storeId);
        $result = $this->encryptor->decrypt($value);

        return $result;
    }
}
