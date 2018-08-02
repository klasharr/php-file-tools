# PHP file tools

Currently this repository contains some classes and a simple webservice script to return the last modified files under a directory (recursively) in json format. 

I wrote this to make it easier to publish sailing race results to the website of my local sailing club. The idea is that we  publish results via FTP to a separate 'results' subdomain, then pull in the latest results list via this web service into the website. The main website runs on Drupal 7 and I wrote a module to consume the web service and display the data, check out my php-drupal7-ssc repository.

## Live Example

Race results files are published to a subdomain here: [http://results.swanagesailingclub.org.uk/](http://results.swanagesailingclub.org.uk/)

The list webservice running code in this repository is here: [http://results.swanagesailingclub.org.uk/list/](http://results.swanagesailingclub.org.uk/list/)

## Installation

You'll need composer to install dependencies. https://getcomposer.org

## Support

Feel free to create an issue, or contact me: [https://klaus.blog/contact/](https://klaus.blog/contact/)
