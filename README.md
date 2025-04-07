# Magento 2 CoinGate Plugin

Sign up for CoinGate account at <https://coingate.com> for production and <https://sandbox.coingate.com> for testing (sandbox) environment.

Please note, that for "Test" mode you **must** generate separate API credentials on <https://sandbox.coingate.com>. API credentials generated on <https://coingate.com> will **not** work for "Test" mode.

## Installation via Composer

You can install Magento 2 CoinGate plugin via [Composer](http://getcomposer.org/). Run the following command in your terminal:

1. Go to your Magento 2 root folder.

2. Enter following commands to install plugin:

    ```bash
    composer require coingate/magento2-plugin
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable plugin:

    ```bash
    php bin/magento module:enable CoinGate_Merchant --clear-static-content
    php bin/magento setup:upgrade
    ```

## Plugin Configuration

Enable and configure CoinGate plugin in Magento Admin under `Stores / Configuration / Sales / Payment Methods / Bitcoin and Altcoins via CoinGate`.
