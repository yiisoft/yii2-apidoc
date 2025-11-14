<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">API documentation generator for Yii 2</h1>
    <br>
</p>

This extension provides an API documentation generator for the [Yii framework 2.0](https://www.yiiframework.com).

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yiisoft/yii2-apidoc/v/stable.png)](https://packagist.org/packages/yiisoft/yii2-apidoc)
[![Total Downloads](https://poser.pugx.org/yiisoft/yii2-apidoc/downloads.png)](https://packagist.org/packages/yiisoft/yii2-apidoc)
[![Build Status](https://github.com/yiisoft/yii2-apidoc/workflows/build/badge.svg)](https://github.com/yiisoft/yii2-apidoc/actions)


Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/download/).

Either run

```
composer require --prefer-dist yiisoft/yii2-apidoc:"~3.0.6"
```

The above command may not work on an existing project due to version conflicts that need to be resolved, so it
is preferred to add the package manually to the `require` section of your composer.json:

```json
"yiisoft/yii2-apidoc": "~3.0.6"
```

afterwards run `composer update`. You may also run `composer update yiisoft/yii2-apidoc cebe/markdown` if you
want to avoid updating unrelated packages.


Usage
-----
This extension creates executable at `/vendor/bin`. Please do change directory to that directory if you  do not want to use full path i.e `/vendor/bin/apidoc` and use just the executable name as with below examples.

This extension offers two commands:

1)`api` to generate class API documentation. [phpDocumentor](https://www.phpdoc.org/) is used as a base framework
  so refer to its guide for the syntax.

The output of `help api` command (i.e `apidoc help api`):

```
DESCRIPTION

Renders API documentation files


USAGE

apidoc api <sourceDirs> <targetDir> [...options...]

- sourceDirs (required): array

- targetDir (required): string


OPTIONS

--appconfig: string
  custom application configuration file path.
  If not set, default application configuration is used.

--color: boolean, 0 or 1
  whether to enable ANSI color in the output.
  If not set, ANSI color will only be enabled for terminals that support it.

--exclude: string|array
  files to exclude.

--guide: string
  url to where the guide files are located

--guide-prefix: string (defaults to 'guide-')
  prefix to prepend to all guide file names.

--help, -h: boolean, 0 or 1 (defaults to 0)
  whether to display help information about current command.

--interactive: boolean, 0 or 1 (defaults to 1)
  whether to run the command interactively.

--page-title: string

--repo-url: string
  Repository url (e.g. "https://github.com/yiisoft/yii2"). Optional, used for resolving relative links
  within a repository (e.g. "[docs/guide/README.md](docs/guide/README.md)"). If you don't have such links you can
  skip this. Otherwise, skipping this will cause generation of broken links because they will be not resolved and
  left as is.

--silent-exit-on-exception: boolean, 0 or 1
  if true - script finish with `ExitCode::OK` in case of exception.
  false - `ExitCode::UNSPECIFIED_ERROR`.
  Default: `YII_ENV_TEST`

--template: string (defaults to 'bootstrap')
  template to use for rendering
```

2)`guide` to render nice HTML pages from markdown files such as the yii guide.

The output of `help guide` command (i.e `apidoc help guide`):

```
DESCRIPTION

Renders API documentation files


USAGE

apidoc guide <sourceDirs> <targetDir> [...options...]

- sourceDirs (required): array

- targetDir (required): string


OPTIONS

--api-docs: string
  path or URL to the api docs to allow links to classes and properties/methods.

--appconfig: string
  custom application configuration file path.
  If not set, default application configuration is used.

--color: boolean, 0 or 1
  whether to enable ANSI color in the output.
  If not set, ANSI color will only be enabled for terminals that support it.

--exclude: string|array
  files to exclude.

--guide-prefix: string (defaults to 'guide-')
  prefix to prepend to all output file names generated for the guide.

--help, -h: boolean, 0 or 1 (defaults to 0)
  whether to display help information about current command.

--interactive: boolean, 0 or 1 (defaults to 1)
  whether to run the command interactively.

--page-title: string

--silent-exit-on-exception: boolean, 0 or 1
  if true - script finish with `ExitCode::OK` in case of exception.
  false - `ExitCode::UNSPECIFIED_ERROR`.
  Default: `YII_ENV_TEST`

--template: string (defaults to 'bootstrap')
  template to use for rendering
```

Simple usage for stand-alone class documentation:

    vendor/bin/apidoc api source/directory ./output

Simple usage for stand-alone guide documentation:

    vendor/bin/apidoc guide source/docs ./output

Note that in order to generate a proper index file, the `README.md` file containing links to guide sections must be 
present. An example of such file can be found 
in the [yii2 repository](https://raw.githubusercontent.com/yiisoft/yii2/master/docs/guide/README.md).

You can combine them to generate class API and guide documentation in one place:

    # generate API docs
    vendor/bin/apidoc api source/directory ./output
    # generate the guide (order is important to allow the guide to link to the apidoc)
    vendor/bin/apidoc guide source/docs ./output

By default, the `bootstrap` template will be used. You can choose a different template with the `--template=name` parameter.
Currently, there is only the `bootstrap` template available.

You may also add the `yii\apidoc\commands\ApiController` and `GuideController` to your console application command map
and run them inside your application's console app.

### Generating docs from multiple sources

The apidoc generator can use multiple directories, so you can generate docs for your application and include the yii framework
docs to enable links between your classes and framework classes. This also allows `@inheritdoc` to work
for your classes that extend from the framework.
Use the following command to generate combined api docs:

    ./vendor/bin/apidoc api ./vendor/yiisoft/yii2,. docs/json --exclude="docs,vendor"
    
This will read the source files from `./vendor/yiisoft/yii2` directory and `.` which is the current directory (you may replace this with the location of your code if it is not in the current working directory).

### Advanced usage

The following script can be used to generate API documentation and guide in different directories and also multiple guides
in different languages (like it is done on yiiframework.com):

```sh
#!/bin/sh

# set these paths to match your environment
YII_PATH=~/dev/yiisoft/yii2
APIDOC_PATH=~/dev/yiisoft/yii2/extensions/apidoc
OUTPUT=yii2docs

cd $APIDOC_PATH
./apidoc api $YII_PATH/framework/,$YII_PATH/extensions $OUTPUT/api --guide=../guide-en --guidePrefix= --interactive=0
./apidoc guide $YII_PATH/docs/guide    $OUTPUT/guide-en --apiDocs=../api --guidePrefix= --interactive=0
./apidoc guide $YII_PATH/docs/guide-ru $OUTPUT/guide-ru --apiDocs=../api --guidePrefix= --interactive=0
# repeat the last line for more languages
```

### Creating a PDF of the guide

Prerequisites:

- `pdflatex`.
- [Pygments](https://pygments.org/).
- GNU `make`.

Generation:

```
vendor/bin/apidoc guide source/docs ./output --template=pdf
cd ./output
make pdf
```

If all runs without errors the PDF will be `guide.pdf` in the `output` dir.

Special Markdown Syntax
-----------------------

We have a special Syntax for linking to a class in the API documentation.
See the [code style guide](https://github.com/yiisoft/yii2/blob/master/docs/internals/core-code-style.md#markdown) for details.

Generating documentation for your own project
---------------------------------------------

To generate API documentation for your own project, use the "project" template and specify the README.md of your repository using the "readmeUrl" parameter

    apidoc api YOUR_REPO_PATH OUTPUT_PATH --exclude="vendor" --template="project"  --readmeUrl="file://YOUR_REPO_PATH/README.md" --pageTitle="TITLE_OF_YOUR_DOCS"

To also include links to the Yii2 documentation, you can do
    
    apidoc api "YOUR_REPO_PATH,vendor/yiisoft/yii2" OUTPUT_PATH --exclude="vendor" --template="project"  --readmeUrl="file://YOUR_REPO_PATH/README.md" --pageTitle="TITLE_OF_YOUR_DOCS"


Creating your own templates
---------------------------

TBD

Using the model layer
---------------------

TBD
