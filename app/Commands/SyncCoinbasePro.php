<?php

namespace App\Commands;

use App\Exchanges\CoinbasePro\Facades\CoinbasePro;
use App\Managers\TransactionProcessManager;

class SyncCoinbasePro extends AbstractSyncCommand
{
    protected string $exchangeName = 'Coinbase Pro';

    public function registerHandlers()
    {
        $this
            ->registerGetTransactionsHandler(function () {
                $wallets = $this->runTask('Get wallets', function () {
                    return CoinbasePro::fetchWallets();
                });

                return $this->runTask('Get transactions by wallet', function () use ($wallets) {
                    return CoinbasePro::fetchTransactionsByWallet($wallets);
                });
            })
            ->registerProcessTransactionsHandler(function ($transactions) {
                return TransactionProcessManager::coinbasePro()->process($transactions);
            });
    }
}
