<?php

namespace App\Commands;

use App\Exchanges\Coinbase\Facades\Coinbase;
use App\Managers\TransactionProcessManager;

class SyncCoinbase extends AbstractSyncCommand
{
    protected string $exchangeName = 'Coinbase';

    public function registerHandlers()
    {
        $this
            ->registerGetTransactionsHandler(function () {
                $wallets = $this->runTask('Get wallets', function () {
                    return Coinbase::fetchWallets();
                });

                return $this->runTask('Get transactions by wallet', function () use ($wallets) {
                    return Coinbase::fetchTransactionsByWallet($wallets);
                });
            })
            ->registerProcessTransactionsHandler(function ($transactions) {
                return TransactionProcessManager::coinbase()->process($transactions);
            });
    }
}
