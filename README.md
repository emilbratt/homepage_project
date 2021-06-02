<h3>My Own Website</h3>
<p>I am finally putting my project up for everyone to see/use</p>

<pre>
This is a project that is intended to teach me what components
make up a website and how the components interact with one and
another to provide features like sharing articles/posts or
uploading photos and presenting the content.
</pre>


<h3>About This Project</h3>

<pre>
It has been a work in progress since March 2021.
The first 2 months have been devoted to getting this website ready.
Basic backend functionality for the php scripts to render content.
Developing Html and CSS to shape the look and feel of the website.

I will continue to develope and maintain this webpage in the hopes
that it will grow into a website that I can be proud of.
That being said, many changes will happen during this project as I
keep learning more and more about web-technologies.

For now you can track my progress by reading the "project.txt"
file provided.

Things that I intend to work on and implement is written down in
the "todo.txt" file.


<h3>----------------FOR DEBIAN BUSTER STABLE----------------</h3>
WEB SERVER DEPENDENCIES
    $ sudo apt update
    $ sudo apt install apache2 -y
    $ sudo apt-get install php -y
    $ sudo apt install libapache2-mod-php -y
    $ sudo apt install sqlite3 -y
    $ sudo apt install php7.3-sqlite3 -y
        note:
            at this time in writing, php7.3-sqlite3 is the current php sqlite3 module
            provided in the repository for debian stable..
            ..changes in debian stable repos might occur
            ..try another version number if package is not found
            .. you can search the repository with the command provided command
                $ apt-cache search sqlite3 | grep php

IMAGE RESIZE SCRIPT DEPENDENCIES
    $ sudo apt update && sudo apt install python3-pip -y
    $ sudo python3 -m pip install --upgrade pip
    $ sudo python3 -m pip install --upgrade Pillow

ADD/CHANGE LINES IN PHP CONFIG -> php.ini
    extension=pdo_sqlite
    file_uploads = On ;enable file upload
    upload_max_filesize = 50M ;max allowed file size for uploading files
    post_max_size = 50M ;mainly used for uploading images
    max_input_time = 60 ;seconds a script is allow to run parsing upload data (files)
    memory_limit = 256M ;max amount of memory a single php script can consume

ADD WRITE PERMISSIONS FOR WEBSERVER
    $ sudo chown www-data /admin
    $ sudo chown www-data /images


<h3>----------------FOR RASPBERRY PI OS LITE----------------</h3>
WEB SERVER DEPENDENCIES
    $ sudo apt update
    $ sudo apt-get install apache2 -y
    $ sudo apt-get install php -y
    $ sudo apt install libapache2-mod-php -y
    $ sudo apt install sqlite3 -y
    $ sudo apt install php-sqlite3 -y
    $ sudo systemctl restart apache2
    $ a2enmod php

IMAGE SCRIPT DEPENDENCIES
    $ sudo apt update
    $ sudo apt install python3-pip -y
    $ sudo python3 -m pip install --upgrade pip
    $ sudo python3 -m pip install --upgrade Pillow
    $ sudo apt install libopenjp2-7
    $ sudo apt install libtiff5

ADD/CHANGE LINES IN PHP CONFIG -> php.ini
    extension=pdo_sqlite
    file_uploads = On ;enable file upload
    upload_max_filesize = 50M ;max allowed file size for uploading files
    post_max_size = 50M ;mainly used for uploading images
    max_input_time = 60 ;seconds a script is allow to run parsing upload data (files)
    memory_limit = 256M ;max amount of memory a single php script can consume

ADD WRITE PERMISSIONS FOR WEBSERVER
    $ sudo chown www-data /admin
    $ sudo chown www-data /images
</pre>
