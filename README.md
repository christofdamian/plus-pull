[![Build Status](https://travis-ci.org/christofdamian/plus-pull.svg?branch=master)](https://travis-ci.org/christofdamian/plus-pull)

plus-pull
=========
Simple script that checks pull requests on a repository on Github and pulls 
them if they satisfy certain requirements. 

By default these requirements are:

1. the sum of +1 and -1 is at least 3
2. there are no [B] blocker comments
3. the request is mergeable
4. (optionally) all build statuses are OK

Install
-------
composer.phar create-project cdamian/plus-pull

Command Line Usage
------------------

Check pull requests:

    check [-p|--pull] [-l|--limit="..."] [config-file]

    Arguments:
     config-file           Path of the yaml configuration file (default: "config.yml")

    Options:
     --pull (-p)           Pull the request if all conditions are met
     --limit (-l)          Maximum numbers of pull (default: 1)

Create a new github authorization token:

    token:create [--note="..."]

    Options:
     --note                Note for the authorization token on github (default: "plus-push")


Configuration File
------------------

With the config file you can set the github authorization username and 
password or an github authorizisation token.

The other section sets the repository owner and name and you can change
the defaults for needed votes, if status will be checked and voter 
whitelist.

You have also the option to automatically add labels for keywords found
in the comments. In the example below we will add the label 'blocked'
with the given color if we see '[B]' in one of the comments. The label
will be created if it doesn't exist.

```yaml
authorization:
    username: christofdamian
    password: secret
    token: githubtoken

repositories:
    -
        username: christofdamian
        name: test
        status: true
        required: 3
        whitelist: [ christofdamian ]
        labels:
            -
                name: blocked
                color: eb6420
                hook: [B]
```

Credits
-------
We are using a similar script at work, which was started by @adriacidre .
This is a complete rewrite though to make it easier to add further features.
