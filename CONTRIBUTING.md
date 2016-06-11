## Contributing translations
##### Extracting gettext strings from .twig files
To extract a translatable text strings from application templates, you can use `cli-tools/template-extractor.php` CLI script.
It will automatically make .php files from application templates and save it to the `translation-cache` directory.
After that, you can import application source code into any editor that works with GNU gettext (for example - [Poedit]).

[Poedit]: <https://poedit.net/>