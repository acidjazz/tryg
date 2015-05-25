
# tryg
Minimal PHP/node MVC framework 

* Advanced debugger
![debugger](media/debugger.png)
* Complete [Jade](https://github.com/jadejs/jade) support
* Complete [Stylus](https://github.com/stylus/stylus) support
* Global configuration, parameters available in PHP, Stylus, and Jade
* API endpoint assistance
* Much more I will list later


## installation

1. Install [Composer](https://getcomposer.org/)
2. Install the specified [Packagist](https://packagist.org/packages/acidjazz/tryg) _acidjazz/tryg_
```bash
php ~/composer.phar require acidjazz/tryg
```
3. Copy the bundled setup template
```bash
cp -rp vendor/acidjazz/tryg/public .
```
4. Copy the provided package.json to install the required node modules, then use *npm* to install them
```bash
cp -rp vendor/acidjazz/tryg/package.json .
npm install
```
5. Point your web server to the public/ folder you've copied as the root, you're done!

### Nginx setup

Replace `/var/www/tryg/public` with the location of your setup template 

```nginx
server {

	listen 80;
	root /var/www/tryg/public;
	index index.php;
	server_name tryg;

	location / {
	if (!-e $request_filename) {
		rewrite ^(.*)$ /index.php;
	}

}

	location ~ \.php$ {
		fastcgi_split_path_info ^(.+\.php)(/.+)$;
		fastcgi_pass unix:/var/run/php5-fpm.sock;
		fastcgi_index index.php;
		include fastcgi_params;

		# dev env settings
		fastcgi_param PHP_VALUE "short_open_tag=on \n display_errors=off \n error_reporting=E_ALL";

		# prod env settings
		# fastcgi_param PHP_VALUE "short_open_tag=on \n display_errors=off \n error_reporting=E_ALL";
	}

}
```

### Apache setup

Create a .htaccess in your setup template root folder and make sure mod\_rewrite is activated

```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php
```

### dev notes

refresh our test setup using tryg as a vendor
```bash
sudo pkill node;php ~/composer.phar update;rm -rf public/; cp -rp vendor/acidjazz/tryg/public/ public
```
