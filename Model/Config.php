<?php
declare(strict_types=1);

namespace Montikids\MessagePopup\Model;

/**
 * Provides access to the module config values
 */
class Config extends AbstractConfig
{
    /**
     * Config paths
     */
    private const XML_PATH_GENERAL_ENABLED = 'montikids_message_popup/general/enabled';
    private const XML_PATH_GENERAL_ENABLED_FRONTEND = 'montikids_message_popup/general/enabled_frontend';
    private const XML_PATH_GENERAL_ENABLED_ADMIN = 'montikids_message_popup/general/enabled_admin';
    private const XML_PATH_GENERAL_POOL_SIZE = 'montikids_message_popup/general/pool_size';

    private const XML_PATH_REPLACE_FRONTEND_ENABLED = 'montikids_message_popup/replace_frontend/enabled';
    private const XML_PATH_REPLACE_FRONTEND_ERROR = 'montikids_message_popup/replace_frontend/error';
    private const XML_PATH_REPLACE_FRONTEND_WARNING = 'montikids_message_popup/replace_frontend/warning';
    private const XML_PATH_REPLACE_FRONTEND_NOTICE = 'montikids_message_popup/replace_frontend/notice';
    private const XML_PATH_REPLACE_FRONTEND_SUCCESS = 'montikids_message_popup/replace_frontend/success';

    private const XML_PATH_REPLACE_ADMIN_ENABLED = 'montikids_message_popup/replace_admin/enabled';
    private const XML_PATH_REPLACE_ADMIN_ERROR = 'montikids_message_popup/replace_admin/error';
    private const XML_PATH_REPLACE_ADMIN_WARNING = 'montikids_message_popup/replace_admin/warning';
    private const XML_PATH_REPLACE_ADMIN_NOTICE = 'montikids_message_popup/replace_admin/notice';
    private const XML_PATH_REPLACE_ADMIN_SUCCESS = 'montikids_message_popup/replace_admin/success';

    /**
     * @inheritDoc
     */
    public function isEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigFlag(self::XML_PATH_GENERAL_ENABLED, $storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledFrontend(?int $storeId = null): bool
    {
        $result = $this->isEnabled() && $this->getStoreConfigFlag(self::XML_PATH_GENERAL_ENABLED_FRONTEND, $storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isEnabledAdmin(?int $storeId = null): bool
    {
        $result = $this->isEnabled() && $this->getStoreConfigFlag(self::XML_PATH_GENERAL_ENABLED_ADMIN, $storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return int
     */
    public function getPoolSize(?int $storeId = null): int
    {
        $result = (int)$this->getStoreConfigValue(self::XML_PATH_GENERAL_POOL_SIZE, $storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceModeEnabledFrontend(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigFlag(self::XML_PATH_REPLACE_FRONTEND_ENABLED, $storeId);
        $result = $result && $this->isEnabledFrontend($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceFrontendErrorsEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_FRONTEND_ERROR);
        $result = $result && $this->isReplaceModeEnabledFrontend($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceFrontendWarningsEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_FRONTEND_WARNING);
        $result = $result && $this->isReplaceModeEnabledFrontend($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceFrontendNoticesEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_FRONTEND_NOTICE);
        $result = $result && $this->isReplaceModeEnabledFrontend($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceFrontendSuccessMessagesEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_FRONTEND_SUCCESS);
        $result = $result && $this->isReplaceModeEnabledFrontend($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceModeEnabledAdmin(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigFlag(self::XML_PATH_REPLACE_ADMIN_ENABLED, $storeId);
        $result = $result && $this->isEnabledAdmin($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceAdminErrorsEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_ADMIN_ERROR);
        $result = $result && $this->isReplaceModeEnabledAdmin($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceAdminWarningsEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_ADMIN_WARNING);
        $result = $result && $this->isReplaceModeEnabledAdmin($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceAdminNoticesEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_ADMIN_NOTICE);
        $result = $result && $this->isReplaceModeEnabledAdmin($storeId);

        return $result;
    }

    /**
     * @param int|null $storeId
     * @return bool
     */
    public function isReplaceAdminSuccessMessagesEnabled(?int $storeId = null): bool
    {
        $result = $this->getStoreConfigValue(self::XML_PATH_REPLACE_ADMIN_SUCCESS);
        $result = $result && $this->isReplaceModeEnabledAdmin($storeId);

        return $result;
    }
}
