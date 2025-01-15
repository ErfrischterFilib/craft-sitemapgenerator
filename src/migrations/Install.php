<?php

namespace filib\sitemapgenerator\migrations;

use Craft;
use craft\db\Migration;
use yii\base\InvalidRouteException;

/**
 * Handles the installation of the plugin.
 */
class Install extends Migration
{
    /**
     * @throws InvalidRouteException
     */
    public function safeUp(): void
    {
        if (!$this->db->tableExists('{{%sitemapgenerator}}')) {
            $this->createTable('{{%sitemapgenerator}}', [
                'id' => $this->integer()->notNull()->defaultValue(0)->unique(),
                'handle' => $this->string()->notNull(),
                'name' => $this->string()->notNull(),
                'weight' => $this->integer()->defaultValue(0),
                'enabled' => $this->boolean()->defaultValue(true),
                'hasUrls' => $this->boolean()->defaultValue(false)
            ]);
            $this->getEntryTypes();
        }
        if (Craft::$app->request->isConsoleRequest) {
            echo "Plugin initiated. Go to the Plugin Settings page to generate your Sitemap\n";
        } else {
            Craft::$app->getResponse()->redirect('/admin/settings/plugins/sitemapgenerator')->send();
        }
    }

    private function getEntryTypes(): void
    {
        $entryTypes = Craft::$app->entries->getAllSections();
        foreach ($entryTypes as $entryType) {
            $hasUrls = false;
            $settings = $entryType->getSiteSettings();
            foreach ($settings as $setting) {
                if ($setting->hasUrls) {
                    $hasUrls = true;
                    break;
                }
            }
            $this->insert('{{%sitemapgenerator}}', [
                'id' => $entryType->id,
                'handle' => $entryType->handle,
                'name' => $entryType->name,
                'weight' => 80,
                'enabled' => false,
                'hasUrls' => $hasUrls
            ]);
        }
    }

    public function safeDown(): bool
    {
        $this->dropTableIfExists('{{%sitemapgenerator}}');
        return true;
    }
}