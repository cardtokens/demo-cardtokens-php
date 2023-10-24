# demo-cardtokens-php

## Introduction
This example shows how to create a token towards the Cardtokens API, create a cryptogram, get status and delete the Token. 

You can run this code directly using a predefined apikey, merchantid and certificate. You can also get a FREE test account and inject with your own apikey, merchantid and certificate. Just visit https://www.cardtokens.io

## Steps to use this example code on Ubuntu

### Clone repo
```bash
git clone https://github.com/cardtokens/demo-cardtokens-php.git
```

### Navigate to folder locally
```bash
cd demo-cardtokens-php
```

### Install PHP
#### Start with update
```bash
sudo apt-get update
sudo apt-get upgrade
```

#### Install php8.0
```bash
sudo apt install php8.0
```

#### Install php openssl
```bash
sudo apt install php8.0-openssl
```

#### Run the program
```bash
php cardtokens.php
```