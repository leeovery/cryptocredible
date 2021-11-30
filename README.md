# CryptoCredible
#### The missing crypto-exchange data exporter

###### _tldr: run locally to export your crypto tx data from popular exchanges via api connections._

Ever tried to export your transaction data only to find out there's a load of data missing, ...or that every exchange provides reports in totally different formats and styles? 🙄

Well, this tool is for you...

Install locally, and with a single command, CryptoCredible will safely and securely fetch **all** your transaction data, and output a csv file with nothing missing, in a single standard format.

What's better is the output is designed to work perfectly with the amazing open source tax software [BittyTax](https://github.com/BittyTax/BittyTax).

| Exchanges supported |
| -------------|
| Coinbase |
| Coinbase Pro |
| _More coming soon!_ |

## Installation

To run CryptoCredible you will need to install php locally on your system. 

#### Apple
For Mac users this is easy. Install HomeBrew first using their instructions [here](https://brew.sh/). Then install php by entering this command into a Terminal window:

```bash
brew install php
```

#### Windows
Windows is a little more complex. A standalone phar file will be supported soon, but for  now, you could use [Docker](https://docs.docker.com/desktop/windows/install/) or [WSL2](https://www.sitepoint.com/wsl2/).

## Usage

Open up a terminal window and run the command suitable for your exchange(s):

##### Coinbase 
``` php
php cryptocredible sync:coinbase
```

##### Coinbase Pro
``` php
php cryptocredible sync:coinbase-pro
```

### Options

Each command accepts the follow options:

| Argument | Shortcut | Description | default |
|---|---|---|---|
| --output-dir | -o | Provide a dir on local file system to output csv to | ./../ |
| --json | -j | Provide a json file rather than fetch txs via api. | n/a |
| --dump | n/a | Dump all the transactions fetched via the api into a json file. | n/a |

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
