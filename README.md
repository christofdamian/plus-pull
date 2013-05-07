plus-pull
=========

Simple script that checks pull requests on a repository on Github and pulls 
them if they satisfy certain requirements. 

By default these requirements are:

# the sum of +1 and -1 is at least 3
# there are no [B] blocker comments
# the request is mergeable
# (optionally) all build statuses are OK

