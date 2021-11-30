# CryptoCredible
#### The missing crypto-exchange data exporter

###### _tldr: run locally to export your crypto tx data from popular exchanges via api connections._

Ever tried to export your crypto transactions from an exchange only to find out there's missing data? 🙄<br>
...or that the file format is completely different from every other exchange? 🙄<br>
...or that they didn't account for the fees, so your tax is now too high? 🙄

Well, this tool is for you...

Install locally, and with a single command, CryptoCredible will safely and securely fetch **all** your transaction data, and output a csv file with nothing missing, in a single standard format.

What's better is the output is designed to work perfectly with the amazing open source tax software [BittyTax](https://github.com/BittyTax/BittyTax).

#### Supported exchanges
* [Coinbase](https://github.com/leeovery/cryptocredible/tree/develop#coinbase)
* [Coinbase Pro](https://github.com/leeovery/cryptocredible/tree/develop#coinbase-pro)
* _...with more coming soon!_

## Installation

To run CryptoCredible you will first need to install php locally on your system. 

#### Apple
For Mac users this is easy. 

Install HomeBrew using their instructions [here](https://brew.sh/), then install php by entering this command into a Terminal window:

```bash
brew install php
```

#### Windows
Windows is a little more complex. 

A standalone phar file will be supported soon, but for  now, you could use [Docker](https://docs.docker.com/desktop/windows/install/) or [WSL2](https://www.sitepoint.com/wsl2/).

## Usage

First you need to configure your api connections with your crypto exchange(s) - see below. 

Once that's done, open up a terminal window and run the command suitable for your exchange(s):

#### Coinbase 

Login into your [Coinbase](https://www.coinbase.com/settings/api) account to create new api credentials, selecting all the permissions with `:read` in them. Paste the credentials into the `.env` file at the root of the project directory using the following structure:

```bash
COINBASE_API_KEY=[api key here]
COINBASE_API_SECRET=[api secret here]
```

Then run this command in a terminal window from the root directory of this project:

``` php
php cryptocredible sync:coinbase
```

#### Coinbase Pro

Login into your [Coinbase Pro](https://pro.coinbase.com/profile/api) account and create new **view** api credentials. Paste them into the `.env` file at the root of the project directory:

```bash
COINBASE_PRO_API_KEY=[api secret here]
COINBASE_PRO_API_SECRET=[api secret here]
COINBASE_PRO_API_PASSPHRASE=[api passphrase here]
```

Then run this command in a terminal window from the root directory of this project:

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

### Is it safe?

Yes, but you are using it without warranty, and at your own risk. Having said that, here are some strong points which make this safe: 
* It runs locally on your own computer, and you have total control over the data and even the code.
* The exchange connections are configured via the `.env` file using **read-only** api credentials. These credentials never leave your system in a usable format.
* The code is open-source, so feel free to poke around and ensure it's safe. Other people have probably poked around too. Safety in numbers. 

_Trust but verify._

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
