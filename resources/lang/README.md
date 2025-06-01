This directory contains language translation files for PLA. PLA should automatically detect your language based on your
browser configuration, and if the language is not available it will fall back to the language used internally (English).

Language files are named by 2 letter iso language name (suffixed with .json) represent the translations for that
language.
Where a language is spoken in multiple countries, but has local country differences (eg: `en-US` vs `en-GB`,
or `zh-CN` vs `zh-TW`), then the language filename is suffixed with `-` and a two letter country, eg:

* `en.json` for English (General),
* `en-GB.json` for English (Great Britain),
* `zh-CN.json` for Chinese (China),
* `zh-TW.json` for Chinese (Taiwan), etc

The language file `zz.json` is an example language file, with each translated string prefixed with the letter "Z". Its
used to identify any default language text (in english) that is not in a translated configuration. Text strings enclosed
in `@lang()`, or `__()` functions are translatable to other languages.

If you want to update the language text for your language, then:

* If your language file **exists** (eg: `fr.json` for French), then:
  * Identify the missing tags (compare it to `zz.json`),
  * Insert the missing tags into the language file (eg: `fr.json` for French) - ensure you keep the file in English
    Alphabetical order.

* If your language file **doesnt** exist (eg; `fr.json` for French), then
  * Copy the default language file `zz.json` to `fr.json`
  * Translate the strings

The structure of the json files is:

```json
{
  "Untranslated string1": "Translated string1",
  "Untranslated string2": "Translated string2"
}
```

Some important notes:
* `Untranslated string` is the string as it appears in PLA, wrapped in either a `__()` or `@lang()` function, normally and english phrase
* `Translated string` is the translation for your language
* Each translated string must be comma terminated *EXCEPT* the last string

Please submit a pull request with your translations, so that others users can benefit from the translation.

If you find any strings that you are not translatable, or translated incorrectly, please submit a bug report.
