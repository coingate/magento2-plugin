# Magento 2 CoinGate Plugin

CoinGate bitcoin payment gateway Magento 2 plugin.

Sign up for CoinGate account at <https://coingate.com> for production and <https://sandbox.coingate.com> for testing (sandbox) environment.

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

## Installation via Magento Marketplace

1. Go to https://marketplace.magento.com/coingate-magento2-plugin.html (you will have login and register on Magento Marketplace).

2. “Purchase” the plugin (it is free, but it is called a “Purchase” by Magento Marketplace).

3. Login to your Magento admin panel.

4. System > Web Setup Wizard > Component Manager.

5. Click "**Sign** in to sync your Magento Marketplace purchases" (to generate the public and private keys needed for signin - log in to your Magento Marketplace account, go to `My Account > My Access Keys > Magento 2` and click **Create A New Access Key**).

6. Click **Sync**, wait for Magento to sych your Marketplace purchases and click **Install**.

7. Click **Install** next to `coingate/magento2-plugin`.

8. Click **Start Readiness Check** > **Next** > **Create Backup** (optional) > **Next** > **Install**.

## Plugin Configuration

Enable and configure CoinGate plugin in Magento Admin under `Stores / Configuration / Sales / Payment Methods / Bitcoin via CoinGate`.
