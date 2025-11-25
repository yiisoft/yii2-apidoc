Yii Framework 2 apidoc extension Change Log
===========================================

3.0.9 under development
-----------------------

- no changes in this release.


3.0.8 November 24, 2025
-----------------------

- Bug #311: Support `nikic/php-parser` v5 (mspirkov)
- Bug #312: Fix `MarkdownHighlightTrait::highlight()` behavior for PHP 8.3 (mspirkov)
- Bug #313: Fix deprecation error `Method deprecated, use ::getParameters()` (mspirkov)
- Bug #317: Fix `trim` deprecation errors `Passing null to parameter #1 ($string) of type string is deprecated` (mspirkov)
- Bug #318: Fix deprecation errors `mb_convert_encoding(): Handling HTML entities via mbstring is deprecated` (mspirkov)
- Bug #324: Raise minimum PHP version to 7.4 (mspirkov)
- Bug #325: Fix deprecation errors `The Default value will become of type Expression by default` (mspirkov)
- Bug #327: Fix deprecation errors `The expression value will become of type Expression by default` (mspirkov)
- Bug #329, #331: Fix getting parameter types from the `@method` annotation (mspirkov)
- Bug #334: Fix PHPDoc annotations throughout the code (mspirkov)
- Bug #335: Fix deprecation error `Using null as an array offset is deprecated, use an empty string instead` (mspirkov)
- Enh #319, #328: Determining types by type hints for properties, methods and params (mspirkov)
- Enh #320: Expand the list of built-in PHP types (mspirkov)
- Enh #331: Display `mixed` for parameters that do not have PHPDoc annotations or type hints (mspirkov)


3.0.7 February 13, 2025
-----------------------

- Bug #293: Logged errors and warnings were duplicated in some cases (rhertogh)


3.0.6 November 18, 2022
-----------------------

- Bug #288: Improve handling of trait inheritance - precedence, prevent methods' and properties' duplication (arogachev)


3.0.5 April 21, 2022
--------------------

- Bug #284: Fix empty visibility in methods and properties (arogachev)


3.0.4 March 29, 2022
--------------------

- Bug #282: Convert newlines to spaces and consider the first sentence ended only if the dot is followed by a space in `BaseDoc::extractFirstSentence()` (WinterSilence)


3.0.3 February 19, 2022
-----------------------

- Bug #281: Fix encoding in non English guides (arogachev)


3.0.2 February 16, 2022
-----------------------

- Bug #280: Do not cache `Context::getReflectionProject()` at instance level because it can cause apidoc cache to contain stale data (arogachev)


3.0.1 February 08, 2022
-----------------------

- Bug #278: Fix error: Call to a member function `getNamespaceAliases()` on null (arogachev)


3.0.0 January 14, 2022
----------------------

- Bug #34: Improved highlighting of PHP templates (arogachev)
- Bug #128: Fixed extracting of first sentence from the text containing backticks (arogachev)
- Bug #133: Fixed PHP Parser error with anonymous class (arogachev)
- Bug #148: Fixed processing of code containing uniform variable syntax (arogachev)
- Bug #155: Fixed processing of classes containing constants with visibility (arogachev)
- Bug #162: Fixed skipping some of PHP files / classes (arogachev)
- Bug #168: Fixed handling of inheritance (arogachev)
- Bug #179: Fixed incorrect output when string type hint is used in method parameters (arogachev)
- Bug #180: Fixed "All Classes" broken link (arogachev)
- Bug #197: Adapted fixing of Markdown links for multiple links (arogachev)
- Bug #199: Fixed processing of nullable return types (arogachev)
- Bug #203: Add PHP 8 compatibility, raise minimum PHP version to 7.2 (bizley, arogachev)
- Bug #210: Fixed invalid attempt to scan parent class of interface with `@inheritdoc` tag on a method (bizley)
- Bug #213: Fixed error: "Call to undefined method `phpDocumentor\Reflection\Php\Argument::getNode()`" (arogachev)
- Bug #218: Extended detection of `@inheritdoc` tag in `BaseDoc` (WinterSilence)
- Bug #239: Do not show a "virtual" / "magic" method's full description if it matches short description (arogachev)
- Bug #240: Fixed a bug when a "virtual" / "magic" property's full description was displayed instead of preview in 
  properties list (arogachev)
- Bug #241: Do not show a method's source code when it's empty (arogachev)
- Enh #18: Added pretty print for arrays (arogachev)
- Enh #36: Allow customizing "All classes url" and "Available since version" label for type in HTML API (arogachev)
- Enh #126: Resolve static return type (arogachev)
- Enh #134: Swapped listings package with minted for better code highlighting in PDF guide (arogachev)
- Enh #140: Added support for multiple "since" tags (arogachev)
- Enh #143: Do not include methods and properties marked as internal (arogachev)
- Enh #146: Updated `nikic/php-parser` version (bizley, arogachev)
- Enh #147: Added feature of viewing method source code without external links (arogachev)
- Enh #159: Added support to relative links within a repository in HTML API (arogachev)
- Enh #161: Render API link text in web guide (arogachev)
- Enh #196: Added support for PHPDoc inline links (arogachev)
- Enh #209: Added support for todos in properties and methods (arogachev)


2.1.6 May 05, 2021
------------------

- Bug #206: Fixed invalid path to `solarized-light.css` in `HighlightBundle` (bu4ak)


2.1.5 July 19, 2020
-------------------

- Bug #163: Do not stop on fatal errors during parsing source files (samdark)
- Bug #198: Add missing initialization of `$contexts` in `ApiMarkdownTrait::parseApiLinks()` (samdark)


2.1.4 May 02, 2020
------------------

- Enh #7, #132: Add support for `@property` and `@method` (samdark)


2.1.3 February 12, 2020
-----------------------

- Bug #145: Fixed broken API links on property/method docs that were pulled in with @inheritdoc (brandonkelly)
- Bug #187: Prevent getter/setter methods from affecting class-defined property docs (brandonkelly)
- Enh #137: @since tags are now propagated to inherited methods/properties in the same package (brandonkelly)
- Enh #185: Use HTTPS for www.php.net links (kamarton)


2.1.2 August 20, 2019
---------------------

- Bug #172: Upgraded highlight.js dependency to 9.13.1 (samdark)
- Bug #176: Prevent multiple TOC rendering in ApiMarkdown (machour)


2.1.1 November 14, 2018
-----------------------

- Bug #149: Fixed crash on wrongly formatted API links (cebe, santosh-1265)
- Bug #160: Fixed parsing of '{@inheritdoc}' tag (klimov-paul)
- Bug: Usage of deprecated `yii\base\Object` changed to `yii\base\BaseObject` allowing compatibility with PHP 7.2 (klimov-paul)
- Enh #38: Fixed display of default values given as octal or hex notation (hiqsol)
- Enh #152: Set `@bower` and `@npm` aliases dependent on the existing directories (ricpelo)
- Enh: Display TOC only if there is more than one headline (cebe)
- Enh: Extracted markdown code highlighting to a trait `MarkdownHighlightTrait` (cebe)
- Enh: Added "type" attribute to JSON renderer to keep information about whether an entry is a class, interface or trait (cebe)


2.1.0 November 22, 2016
-----------------------

- Enh #8: Updated PHP Parser dependency to from version 0.9 to 1.0 to resolve dependency conflicts with other libraries. This breaks the implementation of the `yii\apidoc\helpers\PrettyPrinter` class (cebe)


2.0.6 November 22, 2016
-----------------------

- Bug #5: Enable display of deprecated information for methods, properties, constants and events (cebe)
- Bug #12: Do not publish PHP files for `jssearch.js` asset (cebe)
- Bug #42: Fixed stopword filter in JS search index, which resulted in empty results for some words like `sort` (cebe)
- Bug #61: Fixed duplicate description when `@inheritdoc` is used for properties (cebe)
- Bug #62: Make `@inheritdoc` tag more robust (cebe, sasha-ch)
- Bug #65: Fixed handling of `YiiRequirementChecker` namespace and navigation (cebe)
- Bug #67: Use multibyte compatible function for `ucfirst()` in descriptions (cebe, samdark)
- Bug #68: Fixed crash on empty type in PHPdoc (cebe, itnelo)
- Bug #76: Fixed broken links with external urls (CedricYii)
- Bug #79: Fixed crash due to missing encoding specified in `mb_*` functions (cebe, dingzhihao)
- Enh #29: Added styling for bootstrap tables (cebe)
- Enh #117: Add support for `int` and `bool` types (rob006)
- Enh #118: Separate warnings and errors occurred on processing files (rob006)
- Enh: Moved the title page of the PDF template into a separate file for better customization (cebe)


2.0.5 March 17, 2016
--------------------

- Bug #25: Fixed encoding of HTML tags in method definition for params passed by reference (cebe)
- Bug #37: Fixed error when extending Interfaces that are not in the current code base (cebe)
- Bug #10470: Fixed TOC links for headlines which include links (cebe)
- Enh #13: Allow templates to be specified by class name (tom--)
- Enh #13: Added a JSON template to output the class structure as a JSON file (tom--)
- Enh: Added callback `afterMarkdownProcess()` to HTML Guide renderer (cebe)
- Enh: Added `getHeadings()` method to ApiMarkdown class (cebe)
- Enh: Added css class to Info, Warning, Note and Tip blocks (cebe)
- Chg #31: Hightlight.php library is now used for code highlighing, the builtin ApiMarkdown::hightligh() function is not used anymore (cebe)


2.0.4 May 10, 2015
------------------

- Bug #3: Interface documentation did not show inheritance (cebe)
- Enh: Added ability to set pageTitle from command line (unclead)


2.0.3 March 01, 2015
--------------------

- no changes in this release.


2.0.2 January 11, 2015
----------------------

- no changes in this release.


2.0.1 December 07, 2014
-----------------------

- Bug #5623: Fixed crash when a class contains a setter that has no arguments e.g. `setXyz()` (cebe)
- Bug #5899: Incorrect class listed as `definedBy` reference for properties (cebe)
- Bug: Guide and API renderer now work with relative paths/URLs (cebe)
- Enh: Guide generator now skips `images` directory if it does not exist instead of throwing an error (cebe)
- Enh: Made `--guidePrefix` option available as a command line option (cebe)


2.0.0 October 12, 2014
----------------------

- Chg: Updated cebe/markdown to 1.0.0 which includes breaking changes in its internal API (cebe)

2.0.0-rc September 27, 2014
---------------------------

- no changes in this release.


2.0.0-beta April 13, 2014
-------------------------

- Initial release.
