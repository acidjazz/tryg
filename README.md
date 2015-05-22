
# tryg
Minimal PHP framework with some interesting features


## installation

1. Install [Composer](https://getcomposer.org/)
2. Install the [Packagist](https://packagist.org/packages/acidjazz/tryg)
```bash
php ~/composer.phar require acidjazz/tryg
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

### dev notes

refresh our test setup using tryg as a vendor
```bash
sudo pkill node;php ~/composer.phar update;rm -rf public/; cp -rp vendor/acidjazz/tryg/public/ public
```
