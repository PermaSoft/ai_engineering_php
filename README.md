
# AI Engineering in PHP

This is a non-commercial demo of AI Engineering on the Symfony demo application.

We will:
- first install the symfony demo app
- check if and how it works
- install packmind and make it enforce synfomny best practice.
- devellop a feature to experience an AI Engineering workflow.

Here the original Symfony demo application's readme file:

After installing claude and packmind, 
I added context7 and packmind mcp servers in claude configuration.
there were both active when I ran this prompt:
```prompt-claude
I need you to think hard on creating Packmind standards to enforce best pratices of symfony development as defined in their [official website](https://symfony.com/doc/current/best_practices.html) the menu on the left of
  this site is a menu of links referencing all needed content. Note the actual directory is the content of the official symfony demo application implementing these best practices.
```

```prompt-claude
> I want separate standards for each area.
All areas are equal in priority.
I would like code examples form the current demo codebase, even if they should always be positive examples.
These standards should be grouped in a symfony package
> ```

It gave me standards, no recipies, no package.
I made a package named symfony in packmind UI.
at the root folder of the project, I ran 
```shell
packmind-cli pull symfony
```

```prompt-claude
The current project is the Symfony's official demo application, and I added packmind standards on top of it.
  But I need you to explore and analyse this project to update your memory files with everything you need to rebuild this application from scratch.
  To avoid context pollution, thins that are not always needed can be referenced from claude.md, detailled in an other file, so in following prompt, you may read them only on demand, like partial disclosure pattern.
```

After building technical guidance and functionnal documentation, I have erased all code.
Time has come to try to reproduce the whole application.

I started with that prompt:
```prompt-claude
based on memory files, you have functionnal description and technical guidelines to rebuild a full application.
  The process should start with a research of every functionnality you need to build and produce a plan for each of them that point to all needed data and only needed data.
  Then for each plan, produce a checklist of every thing you need to produce (doc, tests, code, etc.).
  between research and each plans, you ust be able to reset the context and continue with the next plan file. 
```


---

Symfony Demo Application
========================

The "Symfony Demo Application" is a reference application created to show how
to develop applications following the [Symfony Best Practices][1].

You can also learn about these practices in [the official Symfony Book][5].

Requirements
------------

  * PHP 8.2.0 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].

Installation
------------

There are 3 different ways of installing this project depending on your needs:

**Option 1.** [Download Symfony CLI][4] and use the `symfony` binary installed
on your computer to run this command:

```bash
symfony new --demo my_project
```

**Option 2.** [Download Composer][6] and use the `composer` binary installed
on your computer to run these commands:

```bash
# you can create a new project based on the Symfony Demo project...
composer create-project symfony/symfony-demo my_project

# ...or you can clone the code repository and install its dependencies
git clone https://github.com/symfony/demo.git my_project
cd my_project/
composer install
```

**Option 3.** Click the following button to deploy this project on Platform.sh,
the official Symfony PaaS, so you can try it without installing anything locally:

<p align="center">
<a href="https://console.platform.sh/projects/create-project?template=https://raw.githubusercontent.com/symfonycorp/platformsh-symfony-template-metadata/main/symfony-demo.template.yaml&utm_content=symfonycorp&utm_source=github&utm_medium=button&utm_campaign=deploy_on_platform"><img src="https://platform.sh/images/deploy/lg-blue.svg" alt="Deploy on Platform.sh" width="180px" /></a>
</p>

Usage
-----

There's no need to configure anything before running the application. There are
2 different ways of running this application depending on your needs:

**Option 1.** [Download Symfony CLI][4] and run this command:

```bash
cd my_project/
symfony serve
```

Then access the application in your browser at the given URL (<https://localhost:8000> by default).

**Option 2.** Use a web server like Nginx or Apache to run the application
(read the documentation about [configuring a web server for Symfony][3]).

On your local machine, you can run this command to use the built-in PHP web server:

```bash
cd my_project/
php -S localhost:8000 -t public/
```

Tests
-----

Execute this command to run tests:

```bash
cd my_project/
./bin/phpunit
```

[1]: https://symfony.com/doc/current/best_practices.html
[2]: https://symfony.com/doc/current/setup.html#technical-requirements
[3]: https://symfony.com/doc/current/setup/web_server_configuration.html
[4]: https://symfony.com/download
[5]: https://symfony.com/book
[6]: https://getcomposer.org/
