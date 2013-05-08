plus-pull
=========
Simple script that checks pull requests on a repository on Github and pulls 
them if they satisfy certain requirements. 

By default these requirements are:

1. the sum of +1 and -1 is at least 3
2. there are no [B] blocker comments
3. the request is mergeable
4. (optionally) all build statuses are OK

install
-------

composer.phar create-project cdamian/plus-pull

Command Line Options
--------------------
*--pull* only with this option the request will be pulled

*--limit <number>* pull a maximum number of requests


Credits
-------
We are using a similar script at work, which was started by @adriacidre .
This is a complete rewrite though to make it easier to add further features.
