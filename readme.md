# Multi Platform Template System for PHP

Official website: https://greencodestudio.github.io/mpts/

## Usage in code

### PHP

```bash
# Install the MPTS library using Composer
composer require mkrawczyk/mpts
```

```php
// Import necessary classes from the MPTS library
use MKrawczyk\Mpts\Environment;
use MKrawczyk\Mpts\Parser\XMLParser;

// Parse the MPTS template file
$template = XMLParser::Parse(file_get_contents(__DIR__ . '/file.mpts'));

// Create a new environment and set variables
$env = new Environment();
$env->variables = ['foo' => 'bar'];

// Execute the template with the environment and output the result
echo $template->executeToString($env);
```
