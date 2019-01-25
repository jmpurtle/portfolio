# Portfolio

This is the publically available version of my portfolio's codebase. This is an example of an earlier Magnus implementation and the code is in the process of being rebuilt in the Magnus repository.

## Usage

This is pretty straightforward, clone the repository to your machine and have your local webserver (XAMPP, MAMP, etc) point to the html directory as document root.

On Apache, you'll need to add this to your server configuration:
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

On Nginx, you'll need:
location / {
	try_files $uri $uri/ /index.php?$args;
}
location ~ \.php$ {
	include snippets/fastcgi-php.conf;
	fastcgi_pass unix:/var/run/php/php7.2-fpm.sock;
}

This will enable the router to do its job.

A SQL file has been included to build the leads database for the contact form. Run this script in MySQL to set it up. Create a database user and replace the credentials in app/appEnv.php with that user's information.