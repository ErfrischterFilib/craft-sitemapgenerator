<?php

namespace filib\sitemapgenerator\helpers;

use craft\db\Migration;
use filib\sitemapgenerator\SitemapGenerator;

/**
 * Handles the installation of the plugin.
 */
class Update extends Migration
{
    public function updateSettings($newSettings): void
    {
        foreach ($newSettings as $key => $value) {

            $this->update('{{%sitemapgenerator}}', [
                'weight' => $value['weight'],
                'enabled' => isset($value['enabled']) ?: 0,
            ], 'id = ' . $key
            );
        }
        SitemapGenerator::getInstance()->loadSettingsFromDatabase();
    }
}