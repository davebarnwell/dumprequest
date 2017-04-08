# Dump HTTP request details to local files

Simple tool to dump HTTP requests to files, which can then be examined to see what data a HTTP
client is sending. Files are stored in /cached_requests directory

## Give it a quick go

In a terminal window cd into the public directory and run the following to run up a basic
web server on your machine on port 8080

        cd public
        php -S 127.0.0.1:8080
                
In another terminal window run the following which POSTs some test data to the the web server
you just started

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
