<?php

namespace filib\sitemapgenerator\models;

use craft\base\Model;

/**
 * Sitemap Generator settings
 */
class Settings extends Model
{
    public array $entrySettings = [];

    public function defineRules(): array
    {
        return [
            ['entrySettings', 'required'],
        ];
    }
}
