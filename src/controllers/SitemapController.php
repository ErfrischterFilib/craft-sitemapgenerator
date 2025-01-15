<?php

namespace filib\sitemapgenerator\controllers;

use Craft;
use craft\elements\Entry;
use craft\web\Controller;
use filib\sitemapgenerator\SitemapGenerator;

class SitemapController extends Controller
{
    protected array|int|bool $allowAnonymous = ['generate-sitemap'];
    protected array $activeSettings = [];

    public function actionGenerateSitemap()
    {
        $this->activeSettings = SitemapGenerator::$plugin->getLoadedSettings()['entrySettings'];
        $webroot = Craft::getAlias('@webroot'); // Webroot-Pfad abrufen
        $filePath = $webroot . DIRECTORY_SEPARATOR . 'sitemap.xml';

        $sitemapContent = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        $eligibleEntries = $this->getEligibleEntries();

        foreach ($eligibleEntries as $entry) {
            $sitemapContent .= '
<url>
<loc>' . $entry["loc"] . '</loc>
<priority>' . $entry["priority"] / 100 . '</priority>
<lastmod>' . $entry["lastmod"] . '</lastmod>
</url>';
        }

        $sitemapContent .= '</urlset>';

        file_put_contents($filePath, $sitemapContent);
        return $sitemapContent;
    }

    private function getEligibleEntries(): array
    {
        $activatedSections = array();
        foreach ($this->activeSettings as $section) {
            if (!empty($section['hasUrls']) && !empty($section['enabled'])) {
                $activatedSections[] = $section['handle'];
            }
        }
        $eligibleEntries = array();
        foreach ($activatedSections as $section) {
            $section = Craft::$app->entries->getSectionByHandle($section);
            $eligibleEntries[] = Entry::find()
                ->section($section->handle) // Filter auf die Section
                ->all(); // Alle Einträge holen
        }
        $urls = [];
        foreach ($eligibleEntries as $entries) {
            foreach ($entries as $entry) {
                $id = $entry->id;
                $entry = Entry::find()->id($id)->one();
                $url = [
                    'loc' => $entry->getUrl(),
                    'priority' => $this->activeSettings[$entry->section->id]['weight'],
                    'lastmod' => $entry->dateUpdated->format(DATE_W3C),
                ];
                $urls[] = $url;
            }
        }
        return $urls;
    }
}