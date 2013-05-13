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
    
     check [-p|--pull] [-l|--limit="..."] [config-file]

    Arguments:
     config-file           Path of the yaml configuration file (default: "config.yml")

    Options:
     --pull (-p)           Pull the request if all conditions are met
     --limit (-l)          Maximum numbers of pull (default: 1)
     --help (-h)           Display this help message.
     --quiet (-q)          Do not output any message.
     --verbose (-v)        Increase verbosity of messages.
     --version (-V)        Display this application version.
     --ansi                Force ANSI output.
     --no-ansi             Disable ANSI output.
     --no-interaction (-n) Do not ask any interactive question.

Configuration File
------------------

With the config file you can set the github authorization username and 
password.
The other section sets the repository owner and name and you can change
the defaults for needed votes, if status will be checked and voter 
whitelist.

```yaml
authorization:
    username: christofdamian
    password: secret

repository:
    username: christofdamian
    name: test
    status: true
    required: 3
    whitelist: [ christofdamian ]
```

Credits
-------
We are using a similar script at work, which was started by @adriacidre .
This is a complete rewrite though to make it easier to add further features.
