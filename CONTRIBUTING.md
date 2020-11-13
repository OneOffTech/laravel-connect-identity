# How to contribute to Connect Identity for Laravel

All Contributions to this project are most welcome, and can take many forms such as detailed bug reports, documentation, tests, features and patches. 
Note that all contributions are managed by [OneOffTech](https://www.oneofftech.xyz), which can decide on the acceptance of the contribution.


## Bugs reports

To encourage active collaboration, we strongly encourages pull requests, not just bug reports. "Bug reports" may also be sent in the form of a pull request containing a failing test.

However, if you file a bug report, should be as [GitHub issue](https://github.com/OneOffTech/laravel-connect-identity/issues) and should contain a title and a clear description of the problem. You should also include as much relevant information as possible and a code sample that demonstrates the issue. The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

For security issues please refer to our [Security policy](./SECURITY.md).

## Support Questions

If you need help feel free to create a GitHub issue labeled **Question**. We will try to reply as fast as we can, but
don't expect a same day reply.

For security issues please refer to our [Security policy](./SECURITY.md).

## Development

Contributions are managed via GitHub [pull requests](https://github.com/OneOffTech/laravel-connect-identity/pulls). 

To prepare one: 

- [fork the Connect Identity for Laravel](https://github.com/OneOffTech/laravel-connect-identity/fork) into your own GitHub repository;
- Base your branch on the `master` branch;
- Always make a new branch for your work, no matter how small. This makes it easy for others to take just that one set of changes from your repository, in case you have multiple unrelated changes floating around;
 - A corollary: don’t submit unrelated changes in the same branch/pull request! The maintainer shouldn’t have to reject your awesome bugfix because the feature you put in with it needs more review;
- Add unit tests;
- Run `./vendor/bin/php-cs-fixer fix` to ensure the coding standard policies are applied.

Then you'll be able to commit and push your work. Once you are done, Github allows you to create a pull request and propose your changes to the original repository. Make sure you target your pull request to the `master` branch and cite the respective issue if present.


## Documentation

Documentation is located in the [readme.md](./README.md) file. 
We hope it will get big enough to require a `docs` folder.

Any contribution on improving the documentation is highly appreciated and a good way to become a welcomed contributor.

## Contributor License Agreement

The [Contributor License Agreement](./CLA.md) specifies the way how copyright of your contribution is handled. Please include in the comment on your pull request a statement like the following:

> I'd like to contribute `feature X|bugfix Y|docs|something else` to Connect Identity for Laravel. I confirm that my contributions to Connect Identity for Laravel will be compatible with the Connect Identity for Laravel Contributor License Agreement at the time of contribution.