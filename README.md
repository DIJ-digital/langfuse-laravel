## Langfuse PHP - A Laravel Facade for the PHP Langfuse API package.
This package provides a wrapper around the [langfuse-php](https://github.com/DIJ-digital/langfuse-php) package, allowing you to easily integrate Langfuse into your Laravel applications. It uses as few dependencies as possible.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/dij-digital/langfuse-laravel/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/dij-digital/langfuse-laravel/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/dij-digital/langfuse-laravel.svg?style=flat-square)](https://packagist.org/packages/dij-digital/langfuse-laravel)

### It supports the following features:
- Getting a text prompt
- Getting a chat prompt
- Compiling a text prompt
- Compiling a chat prompt
- Create a text prompt
- Create a chat prompt
- Fallbacks for prompt fetching when an error occurs
- Fallbacks for prompt fetching when no prompt is found

> **Requires [PHP 8.4](https://php.net/releases/)**

⚡️ Install the package using **Composer**:
```bash  
composer require dij-digital/langfuse-laravel  
```  

🤙 Modern codebase , refactoring and static analysis in one command
```bash  
composer codestyle  
```  
🚀 Run the entire test suite:
```bash  
composer test  
```  

### How to use this package
Add the following env keys to your projects `.env` file:
```dotenv
LANGFUSE_BASE_URI=https://cloud.langfuse.com
LANGFUSE_PUBLIC_KEY=
LANGFUSE_SECRET_KEY=
```

```php
use DIJ\Langfuse\Laravel\Facades\Langfuse;

Langfuse::prompt()->text('promptName')->compile(['key' => 'value']);
Langfuse::prompt()->text('promptName')->compile(['key' => 'value']);
Langfuse::prompt()->chat('chatName')->compile(['key' => 'value']);
Langfuse::prompt()->list();
Langfuse::prompt()->create('promptName', 'text', PromptType::TEXT);
```

**Langfuse Laravel** was created by **[Tycho Engberink](https://dij.digital)** under the **[MIT license](https://opensource.org/licenses/MIT)**.
