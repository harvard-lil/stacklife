#ShelfLife

Shelflife is a community-based wayfinding tool for navigating the vast resources of the combined Harvard Library System. It enables researchers, teachers, scholars, and students to find what they need and help others learn from them and their paths.

## Installation

### LibraryCloud

ShelLife is the frontend to LibraryCloud's backend. Install LibraryCloud.

### PHP and the web server

ShelfLife is written in PHP. PHP 5.3 or later is recommended.

It's probably easiest to get ShelfLife up and running using the Apache web server. We rely on rewrite rules in .htaccess. Be sure you're allowing for .htaccess in your httpd config file and that you have mod_php and mod_rewrite installed.

### Installation

Use the git clone command to get the latest version of ShelfLife:

    git clone  git://github.com/harvard-lil/shelflife.git

### ShelfLife Configuration

Configuration takes place in etc/sl_ini.php. Copy the example and edit the values:

    cd shelflife/etc
    cp sl_ini.example.php sl_ini.php

## License

Dual licensed under the MIT license (below) and [GPL license](http://www.gnu.org/licenses/gpl-3.0.html).

<small>
MIT License

Copyright (c) 2012 The Harvard Library Innovation Lab

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
</small>