# laravel5-podio

This is a fork-ish of the original [podio-php](http://podio.github.io/podio-php/) library. This version, however, is optimized (and will stay that way) for laravel 5.

# Differences

We include a `PodioSessionManager` class that will save user tokens and other OAuth data into a Laravel [Session](http://laravel.com/docs/5.0/session). We do have plans be able to implement your own session manager.
However, if you would like to implement it, we would happily accept your pull request *\*hint\** *\*hint\**
