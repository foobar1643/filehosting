# Filehosting - light and easy filesharing app

## Features
* Anonymous file upload
* Downloads counter for each file
* For uploader - ability to delete a file from the app
* Thumbnails for images on the download page
* Application is available in multiple languages
* Player for audio and video files on the download page
* Additional info for images, audio and video files on the download page
* Ability to post comments for files without reloading the page
* Tree-like comments for files
* Fulltext search in files names
* Ability to set maximum file size for uploaded files through configuration file
* Simple command line interface for administrators, that allows to add/delete files and comments

## Used technologies
1. [Twitter Bootstrap]
2. [Slim] micro framework
3. [Twig] template engine
4. [jQuery] javascript library
5. [video.js] video player
6. [PHPUnit]
7. Composer compatible [GetId3]

## Requirements
1. Web server with [PHP] >=5.6 support.
2. [PostgreSQL] database.
3. [Sphinx] search engine.
4. [Composer] packet manager.
5. Cron

## Installation
1. Clone the repository using `git clone https://github.com/foobar1643/filehosting.git` command.
2. On your web server set `public` directory as a document root.
3. Install application dependencies using `composer install` command.
4. Configure pathing on your web server [as described here].
5. Set your database credentials in `config.ini` and `sphinx.conf`.
6. Import `filehosting.sql` into your database.
7. Edit your Sphinx configuration file or replace it with already configured `sphinx.conf` file.
8. Initialize search indexes with the `indexer --all` command.
9. In order to enable automatic reindexing add `cli-tools/reindex/reindex.sh` to your crontab.
10. For production usage, change `dispaly_errors` option to `0` in your `php.ini`

## Tests
To run the testsuite, you'll need phpunit.
```bash
$ phpunit
```

## Additional configuration
#### Configuring X-Sendfile
If your server has X-Sendfile module installed and configured, you can enable file downloading with the use of X-Sendfile.
To do that you'll need to set `enableXsendfile` option in `config.ini` to `1`. If you're using Nginx don't forget to set `storage` folder [as internal] in your `nginx.conf`.
Proper file downloading with the use of X-Sendfile guaranteed only for Apache and Nginx servers.
#### Configuring sphinx storage directories
If you want Sphinx to store its logs and indices in a different directory, you can specify the path using
`SPHINX_ROOT` environment variable. Default value is `/var/sphinx/`.

## Contributing
If you want to contribute a translation, please refer to [CONTRIBUTING] for details.

## License
This application is licensed under the MIT license. For more information refer to [License file].

[PHP]: <https://secure.php.net/>
[PHPUnit]: <https://phpunit.de/>
[Sphinx]: <http://sphinxsearch.com/>
[PostgreSQL]: <http://www.postgresql.org/>
[Composer]: <https://getcomposer.org/>
[GetId3]: <https://github.com/phansys/GetId3>
[jQuery]: <https://jquery.org/>
[video.js]: <http://videojs.com/>
[Twig]: <http://twig.sensiolabs.org/>
[Slim]: <http://www.slimframework.com/>
[Twitter Bootstrap]: <http://getbootstrap.com/>
[as described here]: <http://www.slimframework.com/docs/start/web-servers.html>
[as internal]: <https://nginx.org/en/docs/http/ngx_http_core_module.html#internal>
[CONTRIBUTING]: <https://github.com/foobar1643/filehosting/blob/master/CONTRIBUTING.md>
[License file]: <https://github.com/foobar1643/filehosting/blob/master/LICENSE.md>