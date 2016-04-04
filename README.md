# Magento 2 CoinGate Plugin

CoinGate bitcoin payment gateway Magento 2 plugin.

## Install

You can sign up for CoinGate account at <https://coingate.com> for production and <https://sandbox.coingate.com> for testing (sandbox) environment.

### via composer

1. Go to your Magento 2 root folder

2. Enter following commands to install module:

    ```bash
    composer config repositories.coingatemerchant git https://github.com/coingate/magento2-plugin.git
    composer require coingate/merchant
    ```
   Wait while dependencies are updated.

3. Enter following commands to enable module:

    ```bash
    php bin/magento module:enable CoinGate_Merchant --clear-static-content
    php bin/magento setup:upgrade
    ```

5. Enable and configure CoinGate in Magento Admin under `Stores / Configuretion / Payment Methods / CoinGate`
