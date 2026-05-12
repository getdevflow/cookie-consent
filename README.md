## Description
Cookie Consent helps you comply with the EU regulations regarding the usage of website cookies.

> __Requires__ Devflow Version: 2.x

> __Tested Up To:__ 2.0.0

> __Requires PHP:__ 8.4+

> __Stable Tag:__ 2.0.0

> __License:__ GPLv2-only

## Features
- Set banner position
- Set banner layout
- Choose design from color palette
- Learn more link (defaults to cookiesandyou.com)
- Choose compliance type
- Custom text
- Custom attributes

## Localization
Portuguese, Chines (Simplified), German, English, Spanish, French, Italian Japanese, and Russian

## Composer Installation
1. Start a new shell session.
2. Navigate to the root of your install, run the following command ```composer require getdevflow/cookie-consent```.

In your own presentation layer for a frontend, you will need to add the `App\Shared\Helpers\cms_head` function before the closing `head` tag, and the add `App\Shared\Helpers\cms_footer` function before the closing `body` tag.

## Changelog

### 2.0.0
- Api change for enqueue functions.
- Updates for new Devflow v2.

### 1.0.0
- Initial release
