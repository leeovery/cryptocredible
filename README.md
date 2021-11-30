# CryptoCredible
#### The missing crypto exchange data exporter

###### _tldr: run locally to export your crypto tx data from popular exchanges via api connections._

Every tried to export your transactions only to find out there's a load of data missing? 🙄 Well, this tool might be for you...

Run it locally to fetch your transaction data from popular crypto exchanges using their API connections. The tool then processes the data into a csv file with a standard format, regardless of the format the exchange provides. No missing data and all trades, deposits and withdrawals properly categorised.

What's better is the output is designed to work perfectly with the amazing open source tax software [BittyTax](https://github.com/BittyTax/BittyTax).

| Exchanges supported |
| -------------|
| Coinbase |
| Coinbase Pro |
| _More coming soon!_ |

## Installation

To run CryptoCredible you will need to install php locally on your system. 

For Mac user's this is easy. Install HomeBrew first using their instructions [here](https://brew.sh/). Then install php:

```bash
brew install php
```

Windows is a little more complex. A standalone phar file will be supported soon. For now, you could use Docker or [WSL2](https://www.sitepoint.com/wsl2/).

## Usage

``` php
// Usage description here
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email me@leeovery.com instead of using the issue tracker.

## Credits

- [Lee Overy](https://github.com/leeovery)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
