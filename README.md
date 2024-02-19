# reactphp-framework-bridge-tun

# require

* php 8.1 +
* extension 
    * [linux-tun](https://github.com/wpjscc/pecl-tuntap)
    * [mac-utun](https://github.com/wpjscc/pecl-tuntap/tree/mac)

# install
```
composer create-project reactphp-framework/bridge-tun bridge-tun dev-master
```

# run

## server run

first generate config file

```
php index.php -u
```
    
then run server

```
php index.php -s 8010 ./tun.txt
```

## client run

uuid in the config file
```
php index.php -c server_ip:8010 xxxxx_uuid
```

# License
MIT
