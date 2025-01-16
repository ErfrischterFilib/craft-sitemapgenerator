<?php

namespace filib\sitemapgenerator;

use Craft;
use craft\base\Element;
use craft\base\Model;
use craft\base\Plugin;
use craft\db\Query;
use craft\elements\Entry;
use craft\events\ModelEvent;
use craft\helpers\ElementHelper;
use filib\sitemapgenerator\controllers\SitemapController;
use filib\sitemapgenerator\models\Settings;
use yii\base\Event;

/**
 * Sitemap Generator plugin
 *
 * @method static SitemapGenerator getInstance()
 * @method Settings getSettings()
 */
class SitemapGenerator extends Plugin
{
    public static SitemapGenerator $plugin;
    public bool $hasCpSettings = true;
    public array $loadedSettings = [];


    public function init(): void
    {
        parent::init();
        self::$plugin = $this;
        if ($this->isInstalled) {
            $this->loadSettingsFromDatabase();
            $this->registerEvents();
        }
    }

    public function loadSettingsFromDatabase(): void
    {
        $rows = (new Query())
            ->select(['id', 'handle', 'name', 'weight', 'enabled', 'hasUrls'])
            ->from('{{%sitemapgenerator}}')
            ->all();

        $entryTypes = [];

        foreach ($rows as $row) {
            $entryTypes[$row['id']] = [
                'id' => (string)$row['id'],
                'handle' => (string)$row['handle'],
                'name' => (string)$row['name'],
                'weight' => (int)$row['weight'],
                'enabled' => (bool)$row['enabled'],
                'hasUrls' => (bool)$row['hasUrls'],
            ];
        }

        $this->loadedSettings = [
            'entrySettings' => $entryTypes
        ];

        $this->setSettings($this->loadedSettings);
    }
    private function registerEvents(): void
    {
        Event::on(
            Entry::class,
            Element::EVENT_AFTER_SAVE,
            function (ModelEvent $e) {
                /* @var Entry $entry */
                $entry = $e->sender;

                if (ElementHelper::isDraftOrRevision($entry)) {
                    return;
                } else {
                    $generator = new SitemapController('settings', Craft::$app);
                    $generator->actionGenerateSitemap();
                }
            }
        );
    }

    public function afterInstall(): void
    {
        parent::afterInstall(); // TODO: Change the autogenerated stub

        $this->loadSettingsFromDatabase();
    }

    public function getLoadedSettings(): array
    {
        return $this->loadedSettings;
    }

    public function getSettingsResponse(): ?string
    {
        $this->loadSettingsFromDatabase();
        return Craft::$app->controller->renderTemplate('sitemapgenerator/settings.twig', [
            'entryTypes' => $this->getSettings()->entrySettings
        ]);
    }

    protected function createSettingsModel(): ?Model
    {
        return new Settings();
    }
}
