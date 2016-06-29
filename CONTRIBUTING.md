## Contributing a translation
#### Extracting .po translation file from the app
To extract a translatable .po file from the application, you'll need to use `cli-tools/extractor/extract.sh` bash script.
It will automatically extract every translatable string from the application source code and save it to the `locale` directory.
After that, you can import `translation-strings.po` file into any editor that works with GNU gettext (for example - [Poedit]) and start translating.

[Poedit]: <https://poedit.net/>