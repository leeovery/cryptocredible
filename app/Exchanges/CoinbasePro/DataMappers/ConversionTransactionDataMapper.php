<?php

namespace App\Exchanges\CoinbasePro\DataMappers;

use App\Contracts\TransactionDataMapper;
use App\ValueObjects\Transaction;

final class ConversionTransactionDataMapper implements TransactionDataMapper
{
    public function execute(Transaction $transaction): Transaction
    {
        $txJson = json_encode($transaction->getRawData());
        abort(404,
            "You have discovered a transaction type that we have no data for.\n\nPlease raise a new issue here (https://github.com/leeovery/cryptocredible/issues) and paste in the following message.\n\nPlease add the `conversion` transaction type for the CoinbasePro exchange.\n\nTransaction dump: {$txJson}"
        );
    }
}
