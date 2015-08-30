Vote portal
===========

Installing
----------

The best way to install is using Composer. If you don't have Composer yet, download
it following [the instructions](http://doc.nette.org/composer). Then use command:

		composer require myiyk/vote vote
		cd vote
		chmod 777 temp log

Make directories `temp` and `log` writable. Navigate your browser
to the `www` directory and you will see a welcome page. PHP 5.4 allows
you run `php -S localhost:8888 -t www` to start the web server and
then visit `http://localhost:8888` in your browser.

It is CRITICAL that whole `app`, `log` and `temp` directories are NOT accessible
directly via a web browser! See [security warning](http://nette.org/security-warning).

License
-------
GPL 3.0
