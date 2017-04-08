# Dump HTTP request details to local files

Simple tool to dump HTTP requests to files, which can then be examined to see what data a HTTP
client is sending. Files are stored in /cached_requests directory

## Installation

Copy the files to your web server note the web root should be set to the public folder with
the rest of the files above the web root. You'll also need to install [composer](https://getcomposer.org/download/)
I recommend installing it [globally](https://getcomposer.org/doc/00-intro.md#globally) so you dont have multiple copies
hanging around your system unless you specifically need to.
Once compose is installed in the projects root directory run ```composer install``` to pull in the projects
dependancies which are basically Slim, Twig, Yaml and Monolog.

## Give it a quick go locally with PHP's built in web server

In a terminal window cd into the public directory and run the following to run up a basic
web server on your machine on port 8080

        cd public
        php -S 127.0.0.1:8080
                
In another terminal window run the following which sends a PUT request with some form params

        curl -X PUT "http://127.0.0.1:8080/" \
            -H "Content-Type: application/x-www-form-urlencoded; charset=utf-8" \
            -H "X-Test-Header: test value" \
            -H "Accept: application/json" \
            --data-raw "p2"="v2" \
            --data-raw "pa1[]"="va1" \
            --data-raw "pa1[]"="va2" \
            --data-raw "p1"="v1"

You'll note the response from the request is a JSON structure as we set the Accept header to
*application/json* if not supplied or set to *text/html* an HTML response is given, if set to *text/xml* an XML response is given
            
Finally take a look in the cache directory at the *.txt files

         cd cached_requests
         more *.txt

## TODO very soon
- On GET request show recent requests and allow exploring older ones saved in files rather
  than having to look at the files in the terminal

## Nice TODO future features
- Live update the GET view as new POST/DELETE/PUT/OPTIONS requests come in
- Implement named endpoints to collect related requests together
